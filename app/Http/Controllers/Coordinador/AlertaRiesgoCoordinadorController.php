<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CoordinacionAcademica;
use App\Models\EvaluacionRiesgo;
use App\Models\Pasantia;
use App\Models\UmbralRiesgo;
use App\Services\RulesEngine\RiskEvaluator;
use App\Services\RulesEngine\RiskRecommendations;
use PDOException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Alertas y riesgos: muestra el último nivel de riesgo de cada pasantía
 * en progreso de la carrera, su detalle y el historial de evaluaciones.
 */
class AlertaRiesgoCoordinadorController extends Controller
{
    public const SIN_EVALUAR = 'Sin evaluar';

    public const CON_ERROR = 'Error';

    public function __construct(
        private RiskEvaluator $evaluador
    ) {
    }

    /**
     * Obtener la coordinación del usuario autenticado.
     */
    private function obtenerCoordinacion(): CoordinacionAcademica
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario && $usuario->rol === 'Coordinador',
            403,
            'No tienes permiso para acceder a esta sección.'
        );

        return CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();
    }

    private function pasantiasDeCarrera(CoordinacionAcademica $coordinacion)
    {
        return Pasantia::whereHas(
            'practicaProfesional.estudiante.planEstudios',
            function ($consulta) use ($coordinacion) {
                $consulta->where('idCarrera', $coordinacion->idCarrera);
            }
        );
    }

    /**
     * Lista de pasantías en progreso con su último nivel de riesgo.
     */
    public function index(Request $request)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $niveles = $this->niveles();

        $pasantias = $this->pasantiasDeCarrera($coordinacion)
            ->where('estado', 'En progreso')
            ->with([
                'practicaProfesional.estudiante.usuario',
                'practicaProfesional.estudiante.planEstudios.carrera',
                'ultimaEvaluacionRiesgo.detalles.regla',
            ])
            ->orderByDesc('idPasantia')
            ->get()
            ->each(function (Pasantia $pasantia) {
                $evaluacion = $pasantia->ultimaEvaluacionRiesgo;

                $pasantia->nivelActual = match (true) {
                    $evaluacion === null => self::SIN_EVALUAR,
                    $evaluacion->estadoEvaluacion !== RiskEvaluator::ESTADO_CORRECTA => self::CON_ERROR,
                    default => $evaluacion->nivelRiesgo,
                };

                $pasantia->detallePrincipal = $evaluacion
                    ? $this->reglasCumplidas($evaluacion)->first()
                    : null;
            });

        // Los conteos se calculan antes de filtrar.
        $conteos = $pasantias->countBy('nivelActual');

        $nivel = (string) $request->query('nivel', '');
        $opcionesNivel = $niveles->pluck('nivel')->merge([self::SIN_EVALUAR, self::CON_ERROR]);

        if ($opcionesNivel->contains($nivel)) {
            $pasantias = $pasantias->where('nivelActual', $nivel)->values();
        } else {
            $nivel = '';
        }

        $buscar = trim((string) $request->query('buscar', ''));

        if ($buscar !== '') {
            $termino = mb_strtolower($buscar);

            $pasantias = $pasantias
                ->filter(function (Pasantia $pasantia) use ($termino) {
                    $estudiante = $pasantia->practicaProfesional?->estudiante;

                    $texto = mb_strtolower(implode(' ', [
                        $estudiante?->usuario?->nombre,
                        $estudiante?->usuario?->apellido,
                        $estudiante?->credencial,
                        $estudiante?->planEstudios?->carrera?->nombre,
                        $pasantia->empresa,
                    ]));

                    return str_contains($texto, $termino);
                })
                ->values();
        }

        return view('coordinador.alertas', [
            'pasantias' => $pasantias,
            'niveles' => $niveles,
            'conteos' => $conteos,
            'filtros' => ['nivel' => $nivel, 'buscar' => $buscar],
        ]);
    }

    /**
     * Detalle del riesgo de una pasantía y su historial de evaluaciones.
     */
    public function show($idPasantia)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $pasantia = $this->pasantiasDeCarrera($coordinacion)
            ->where('idPasantia', $idPasantia)
            ->with([
                'practicaProfesional.estudiante.usuario',
                'practicaProfesional.estudiante.planEstudios.carrera',
                'tutor.usuario',
            ])
            ->firstOrFail();

        $evaluaciones = $pasantia->evaluacionesRiesgo()
            ->with('detalles.regla')
            ->orderByDesc('idEvaluacionRiesgo')
            ->get();

        $evaluacion = $evaluaciones->first();

        return view('coordinador.alertas-detalle', [
            'pasantia' => $pasantia,
            'evaluacion' => $evaluacion,
            'evaluaciones' => $evaluaciones,
            'reglasCumplidas' => $evaluacion ? $this->reglasCumplidas($evaluacion) : collect(),
            'recomendaciones' => $evaluacion?->contextoEvaluado
                ? RiskRecommendations::para($evaluacion->contextoEvaluado)
                : [],
            'niveles' => $this->niveles(),
        ]);
    }

    /**
     * Evaluar ahora todas las pasantías en progreso de la carrera.
     */
    public function evaluarTodas()
    {
        $coordinacion = $this->obtenerCoordinacion();

        $ids = $this->pasantiasDeCarrera($coordinacion)
            ->where('estado', 'En progreso')
            ->orderBy('idPasantia')
            ->pluck('idPasantia');

        if ($ids->isEmpty()) {
            return redirect()
                ->route('coordinador.alertas')
                ->with('success', 'No hay pasantías en progreso para evaluar.');
        }

        $evaluadas = 0;
        $avisos = [];

        foreach ($ids as $id) {
            try {
                $evaluacion = $this->evaluador->evaluar($id);
            } catch (PDOException $e) {
                // Un fallo de base de datos no es un aviso de la pasantía.
                throw $e;
            } catch (RuntimeException $e) {
                $avisos[] = "Pasantía #{$id}: {$e->getMessage()}";

                continue;
            }

            if ($evaluacion->estadoEvaluacion === RiskEvaluator::ESTADO_CORRECTA) {
                $evaluadas++;
            } else {
                // Un error de configuración afecta a todas por igual.
                return redirect()
                    ->route('coordinador.alertas')
                    ->with('error', 'No se pudo calcular el riesgo: ' . $evaluacion->errorConfiguracion
                        . ' Corrígelo en "Reglas parametrizables".');
            }
        }

        return redirect()
            ->route('coordinador.alertas')
            ->with('success', $evaluadas === 1
                ? 'Se evaluó 1 pasantía.'
                : "Se evaluaron {$evaluadas} pasantías.")
            ->with('avisos', $avisos);
    }

    /**
     * Evaluar ahora una pasantía.
     */
    public function evaluar($idPasantia)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $pasantia = $this->pasantiasDeCarrera($coordinacion)
            ->where('idPasantia', $idPasantia)
            ->firstOrFail();

        $redireccion = redirect()->route('coordinador.alertas.show', $pasantia->idPasantia);

        try {
            $evaluacion = $this->evaluador->evaluar($pasantia->idPasantia);
        } catch (PDOException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            return $redireccion->with('error', 'No se pudo evaluar: ' . $e->getMessage());
        }

        if ($evaluacion->estadoEvaluacion !== RiskEvaluator::ESTADO_CORRECTA) {
            return $redireccion->with('error', 'No se pudo calcular el riesgo: ' . $evaluacion->errorConfiguracion
                . ' Corrígelo en "Reglas parametrizables".');
        }

        return $redireccion->with('success', 'Evaluación actualizada.');
    }

    /**
     * Niveles activos ordenados de menor a mayor, con su color.
     */
    private function niveles(): Collection
    {
        $umbrales = UmbralRiesgo::where('activo', true)
            ->orderBy('limiteInferior')
            ->get();

        $ultimo = $umbrales->count() - 1;

        return $umbrales->values()->map(fn (UmbralRiesgo $umbral, int $i) => [
            'nivel' => $umbral->nivel,
            'color' => match (true) {
                $i === 0 => 'verde',
                $i === $ultimo => 'rojo',
                default => 'ambar',
            },
        ]);
    }

    /**
     * Reglas cumplidas de una evaluación, de la que más puntos aportó
     * a la que menos.
     */
    private function reglasCumplidas(EvaluacionRiesgo $evaluacion): Collection
    {
        return $evaluacion->detalles
            ->where('cumplida', true)
            ->sortByDesc(fn ($detalle) => (float) $detalle->puntuacionAplicada)
            ->values();
    }
}
