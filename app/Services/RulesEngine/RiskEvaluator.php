<?php

namespace App\Services\RulesEngine;

use App\Models\EvaluacionRiesgo;
use App\Models\ReglaRiesgo;
use App\Models\UmbralRiesgo;
use App\Models\VersionConfiguracionRiesgo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Evalúa el riesgo de una pasantía real y guarda el resultado.
 *
 * 1. RiskContextBuilder arma el contexto con los datos de la pasantía.
 * 2. RulesEngine aplica las reglas activas y clasifica la puntuación.
 * 3. La evaluación y el detalle de cada regla se guardan juntos en una
 *    transacción, con la fecha y la versión de configuración usada.
 *
 * Cada llamada crea una evaluación nueva: las anteriores no se modifican
 * y quedan como historial de la pasantía.
 */
class RiskEvaluator
{
    public const ESTADO_CORRECTA = 'Correcta';

    public const ESTADO_ERROR_CONFIGURACION = 'Error de configuración';

    // Máximo que admiten las columnas decimal(5, 2) de puntuación.
    private const PUNTUACION_MAXIMA = 999.99;

    public function __construct(
        private RiskContextBuilder $contextBuilder,
        private RulesEngine $rulesEngine
    ) {
    }

    /**
     * Evaluar una pasantía en progreso.
     *
     * Si la configuración de reglas o umbrales es inválida, se guarda
     * una evaluación con estado "Error de configuración", sin nivel de
     * riesgo ni detalles, para que nunca aparente un riesgo calculado.
     *
     * @throws \RuntimeException si la pasantía no se puede evaluar
     *         (no está en progreso, no tiene carrera o fecha final).
     */
    public function evaluar(int $idPasantia): EvaluacionRiesgo
    {
        // Los errores de la pasantía se lanzan antes de guardar nada.
        $contexto = $this->contextBuilder->build($idPasantia);

        return $this->guardar($idPasantia, $contexto, $this->versionActual());
    }

    /**
     * Evaluar una pasantía solo si algo cambió desde su última
     * evaluación: sus documentos, los días que faltan o la configuración
     * de reglas y umbrales. Si nada cambió, devuelve la última, así el
     * historial solo guarda cambios reales.
     *
     * @throws \RuntimeException si la pasantía no se puede evaluar.
     */
    public function evaluarSiCambio(int $idPasantia): EvaluacionRiesgo
    {
        $contexto = $this->contextBuilder->build($idPasantia);
        $version = $this->versionActual();

        $ultima = EvaluacionRiesgo::where('idPasantia', $idPasantia)
            ->latest('idEvaluacionRiesgo')
            ->first();

        // Comparación flexible: el JSON guardado convierte 25.0 en 25.
        if (
            $ultima
            && $ultima->versionConfiguracion === $version->versionConfiguracion
            && $ultima->contextoEvaluado == $contexto
        ) {
            return $ultima;
        }

        return $this->guardar($idPasantia, $contexto, $version);
    }

    private function guardar(
        int $idPasantia,
        array $contexto,
        VersionConfiguracionRiesgo $version
    ): EvaluacionRiesgo {
        return DB::transaction(function () use ($idPasantia, $contexto, $version) {

            $datosBase = [
                'idPasantia' => $idPasantia,
                'fechaEvaluacion' => now(),
                'versionConfiguracion' => $version->versionConfiguracion,
                'contextoEvaluado' => $contexto,
            ];

            try {
                $resultado = $this->rulesEngine->matchRules($contexto);
                $this->validarResultado($resultado);
            } catch (InvalidArgumentException $e) {
                return EvaluacionRiesgo::create($datosBase + [
                    'puntuacionTotal' => null,
                    'nivelRiesgo' => null,
                    'estadoEvaluacion' => self::ESTADO_ERROR_CONFIGURACION,
                    'errorConfiguracion' => $e->getMessage(),
                ]);
            }

            $evaluacion = EvaluacionRiesgo::create($datosBase + [
                'puntuacionTotal' => $resultado['puntuacionTotal'],
                'nivelRiesgo' => $resultado['nivelRiesgo'],
                'estadoEvaluacion' => self::ESTADO_CORRECTA,
                'errorConfiguracion' => null,
            ]);

            foreach ($resultado['reglasEvaluadas'] as $regla) {
                $evaluacion->detalles()->create([
                    'idReglaRiesgo' => $regla['idReglaRiesgo'],
                    'cumplida' => $regla['cumplida'],
                    'puntuacionAplicada' => $regla['puntuacionAplicada'],
                    'resultado' => $this->describirResultado($regla),
                    'detalleCondiciones' => [
                        'conector' => $regla['conector'],
                        'condiciones' => $regla['condiciones'],
                    ],
                ]);
            }

            return $evaluacion->load('detalles');
        });
    }

    /**
     * Versión de la configuración activa. Si las reglas, condiciones o
     * umbrales cambiaron desde la última evaluación, se registra una
     * versión nueva con una copia de la configuración.
     */
    public function versionActual(): VersionConfiguracionRiesgo
    {
        $configuracion = $this->configuracionActiva();

        $huella = hash(
            'sha256',
            json_encode($configuracion, JSON_UNESCAPED_UNICODE)
        );

        // Se busca primero: en MySQL un INSERT rechazado por el UNIQUE
        // consume un número del AUTO_INCREMENT y dejaría saltos en las
        // versiones.
        $existente = VersionConfiguracionRiesgo::where('huella', $huella)->first();

        if ($existente) {
            return $existente;
        }

        // createOrFirst evita versiones duplicadas si dos evaluaciones
        // registran la misma configuración al mismo tiempo.
        return VersionConfiguracionRiesgo::createOrFirst(
            ['huella' => $huella],
            ['configuracion' => $configuracion]
        );
    }

    /**
     * Datos de las reglas y umbrales activos que influyen en el resultado,
     * en un orden fijo para que la misma configuración dé la misma huella.
     */
    private function configuracionActiva(): array
    {
        $reglas = ReglaRiesgo::with('condiciones')
            ->where('activa', true)
            ->orderBy('prioridad')
            ->orderBy('idReglaRiesgo')
            ->get()
            ->map(fn (ReglaRiesgo $regla) => [
                'idReglaRiesgo' => $regla->idReglaRiesgo,
                'nombre' => $regla->nombre,
                'puntuacion' => (string) $regla->puntuacion,
                'prioridad' => $regla->prioridad,
                'variablePuntuacion' => $regla->variablePuntuacion,
                'condiciones' => $regla->condiciones
                    ->map(fn ($condicion) => [
                        'variable' => $condicion->variable,
                        'operador' => $condicion->operador,
                        'parametro' => $condicion->parametro,
                        'tipoParametro' => $condicion->tipoParametro,
                        'conector' => $condicion->conector,
                        'orden' => $condicion->orden,
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();

        $umbrales = UmbralRiesgo::where('activo', true)
            ->orderBy('limiteInferior')
            ->orderBy('idUmbralRiesgo')
            ->get()
            ->map(fn (UmbralRiesgo $umbral) => [
                'nivel' => $umbral->nivel,
                'limiteInferior' => (string) $umbral->limiteInferior,
                'limiteSuperior' => $umbral->limiteSuperior !== null
                    ? (string) $umbral->limiteSuperior
                    : null,
            ])
            ->all();

        return [
            'reglas' => $reglas,
            'umbrales' => $umbrales,
        ];
    }

    /**
     * Comprobar que el resultado del motor se puede guardar tal cual.
     */
    private function validarResultado(array $resultado): void
    {
        if ($resultado['puntuacionTotal'] > self::PUNTUACION_MAXIMA) {
            throw new InvalidArgumentException(
                'La puntuación total supera el máximo permitido ('
                . self::PUNTUACION_MAXIMA . '). Revisa las puntuaciones de las reglas.'
            );
        }

        foreach ($resultado['reglasEvaluadas'] as $regla) {
            if (abs($regla['puntuacionAplicada']) > self::PUNTUACION_MAXIMA) {
                throw new InvalidArgumentException(
                    "La puntuación de la regla '{$regla['nombre']}' "
                    . 'supera el máximo permitido.'
                );
            }
        }
    }

    private function describirResultado(array $regla): string
    {
        if (! $regla['cumplida']) {
            return 'No se cumplió la regla.';
        }

        return 'Se cumplió la regla y aportó '
            . number_format($regla['puntuacionAplicada'], 2)
            . ' puntos.';
    }
}
