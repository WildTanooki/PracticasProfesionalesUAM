<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\EvaluacionRiesgo;
use App\Models\Pasantia;
use App\Models\PracticaProfesional;
use App\Models\UmbralRiesgo;
use App\Services\GestionDocumentalService;
use App\Services\RulesEngine\RiskEvaluator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDOException;
use RuntimeException;

/**
 * Dashboard del estudiante: sus prácticas profesionales, la pasantía
 * más reciente con su avance de horas, sus alertas y el estado de los
 * documentos de esa pasantía.
 */
class DashboardController extends Controller
{
    // Días antes de la fecha final en los que se avisa al estudiante.
    private const DIAS_AVISO_FECHA_FINAL = 15;

    public function __construct(
        private GestionDocumentalService $gestionDocumental,
        private RiskEvaluator $evaluador
    ) {
    }

    public function index()
    {
        $usuario = auth()->user();

        // Obtener el estudiante y su plan de estudios.
        $estudiante = $usuario->estudiante()
            ->with('planEstudios')
            ->firstOrFail();

        $plan = $estudiante->planEstudios;

        abort_if(!$plan, 403, 'El estudiante no tiene un plan asignado.');

        // Crear únicamente las prácticas que todavía falten.
        DB::transaction(function () use ($estudiante, $plan) {
            $practicas = PracticaProfesional::where(
                'cif',
                $estudiante->credencial
            )->get();

            $cantidadExistente = $practicas->count();
            $cantidadRequerida = (int) $plan->cantidadPracticasRequeridas;

            for (
                $numero = $cantidadExistente + 1;
                $numero <= $cantidadRequerida;
                $numero++
            ) {
                PracticaProfesional::create([
                    'nombrePractica' => 'Práctica Profesional ' . $numero,
                    'horasRequeridas' => $plan->horasPorPractica,
                    'estadoPractica' => 'Pendiente',
                    'cif' => $estudiante->credencial,
                ]);
            }
        });

        // Obtener todas las prácticas del estudiante.
        $practicas = PracticaProfesional::where(
            'cif',
            $estudiante->credencial
        )
            ->orderBy('idPractica')
            ->get();

        // Solo la pasantía más reciente del estudiante.
        $pasantia = Pasantia::with([
                'practicaProfesional.estudiante.planEstudios',
                'tutor.usuario',
            ])
            ->whereHas('practicaProfesional', function ($consulta) use ($estudiante) {
                $consulta->where('cif', $estudiante->credencial);
            })
            ->orderByDesc('idPasantia')
            ->first();

        $avance = null;
        $documentos = collect();
        $resumenDocumental = null;
        $riesgo = null;
        $alertas = collect();

        if ($pasantia) {
            $avance = $this->avance($pasantia);

            $documentos = Documento::with(['formato', 'ultimaVersion'])
                ->where('idPasantia', $pasantia->idPasantia)
                ->get()
                ->sortByDesc(fn (Documento $documento) => $documento->ultimaVersion?->fechaEnvio)
                ->values();

            $formatos = $this->gestionDocumental
                ->formatosRequeridos($pasantia)
                ->orderBy('nombreFormato')
                ->get();

            $resumenDocumental = $this->resumenDocumental($formatos, $documentos);
            $riesgo = $this->riesgo($pasantia);

            $alertas = $this->alertas(
                $pasantia,
                $avance,
                $resumenDocumental,
                $documentos,
                $riesgo
            );
        }

        // El carrusel abre en la práctica de la pasantía más reciente.
        $indicePracticaActual = $pasantia
            ? max(0, (int) $practicas->search(
                fn ($practica) => $practica->idPractica === $pasantia->idPractica
            ))
            : 0;

        return view('estudiante.home', [
            'usuario' => $usuario,
            'estudiante' => $estudiante,
            'plan' => $plan,
            'practicas' => $practicas,
            'indicePracticaActual' => $indicePracticaActual,
            'pasantia' => $pasantia,
            'avance' => $avance,
            'documentos' => $documentos,
            'resumenDocumental' => $resumenDocumental,
            'riesgo' => $riesgo,
            'alertas' => $alertas,
            'gestionDocumental' => $this->gestionDocumental,
        ]);
    }

    /**
     * Horas aprobadas, pendientes y restantes de la pasantía.
     *
     * @return array{requeridas: int, aprobadas: int, pendientes: int, restantes: int, porcentaje: int}
     */
    private function avance(Pasantia $pasantia): array
    {
        $horas = $pasantia->registrosDeHoras()
            ->selectRaw('estadoHoras, SUM(horasIngresadas) as total')
            ->groupBy('estadoHoras')
            ->pluck('total', 'estadoHoras');

        $requeridas = max(0, (int) $pasantia->horasRequeridas);
        $aprobadas = (int) ($horas['Aprobada'] ?? 0);

        return [
            'requeridas' => $requeridas,
            'aprobadas' => $aprobadas,
            'pendientes' => (int) ($horas['Pendiente'] ?? 0),
            'restantes' => max(0, $requeridas - $aprobadas),
            'porcentaje' => $requeridas > 0
                ? (int) min(100, floor($aprobadas * 100 / $requeridas))
                : 0,
        ];
    }

    /**
     * Cuántos formatos requeridos están aprobados, en revisión, por
     * corregir, rechazados o sin entregar.
     */
    private function resumenDocumental(Collection $formatos, Collection $documentos): array
    {
        $porFormato = $documentos->keyBy('idFormato');

        $faltantes = $formatos
            ->reject(fn ($formato) => $porFormato->has($formato->idFormato))
            ->pluck('nombreFormato')
            ->values();

        $requeridos = $formatos->pluck('idFormato');
        $estados = $porFormato->only($requeridos->all())->countBy('estadoArchivo');

        $total = $formatos->count();
        $aprobados = (int) ($estados['Aprobado'] ?? 0);

        return [
            'total' => $total,
            'aprobados' => $aprobados,
            'enRevision' => (int) ($estados['Pendiente'] ?? 0),
            'aCorregir' => (int) ($estados['A corregir'] ?? 0),
            'rechazados' => (int) ($estados['Rechazado'] ?? 0),
            'faltantes' => $faltantes,
            'porcentaje' => $total > 0 ? (int) floor($aprobados * 100 / $total) : 0,
        ];
    }

    /**
     * Último nivel de riesgo de la pasantía. Si está en progreso se
     * vuelve a evaluar (solo se guarda si algo cambió), igual que en la
     * pantalla de alertas del coordinador.
     *
     * @return array{nivel: string, color: string, reglas: Collection}|null
     */
    private function riesgo(Pasantia $pasantia): ?array
    {
        if ($pasantia->estado === 'En progreso') {
            try {
                $this->evaluador->evaluarSiCambio($pasantia->idPasantia);
            } catch (PDOException $e) {
                throw $e;
            } catch (RuntimeException $e) {
                // Sin configuración de riesgo válida: se usa la última
                // evaluación guardada, si existe.
                report($e);
            }
        }

        $evaluacion = EvaluacionRiesgo::with('detalles.regla')
            ->where('idPasantia', $pasantia->idPasantia)
            ->latest('idEvaluacionRiesgo')
            ->first();

        if (
            $evaluacion === null
            || $evaluacion->estadoEvaluacion !== RiskEvaluator::ESTADO_CORRECTA
            || $evaluacion->nivelRiesgo === null
        ) {
            return null;
        }

        $niveles = UmbralRiesgo::where('activo', true)
            ->orderBy('limiteInferior')
            ->pluck('nivel')
            ->values();

        $posicion = $niveles->search($evaluacion->nivelRiesgo);
        $ultimo = $niveles->count() - 1;

        return [
            'nivel' => $evaluacion->nivelRiesgo,
            'color' => match (true) {
                $posicion === 0 => 'verde',
                $posicion === $ultimo => 'rojo',
                default => 'ambar',
            },
            'reglas' => $evaluacion->detalles
                ->where('cumplida', true)
                ->sortByDesc(fn ($detalle) => (float) $detalle->puntuacionAplicada)
                ->values(),
        ];
    }

    /**
     * Alertas que el estudiante debe atender en su pasantía más reciente,
     * de la más urgente a la menos urgente.
     *
     * Cada alerta: titulo, detalle, etiqueta, tono (rojo|ambar|azul) y
     * enlace opcional.
     */
    private function alertas(
        Pasantia $pasantia,
        array $avance,
        array $resumen,
        Collection $documentos,
        ?array $riesgo
    ): Collection {
        $alertas = collect();
        $enlaceDocumentos = route('estudiante.documentos', ['pasantia' => $pasantia->idPasantia]);
        $activa = $pasantia->estado === 'En progreso';

        foreach ($documentos->where('estadoArchivo', 'Rechazado') as $documento) {
            $alertas->push([
                'titulo' => 'Documento rechazado: ' . ($documento->formato?->nombreFormato ?? 'Documento'),
                'detalle' => $documento->observacion
                    ?: 'Comunícate con Coordinación Académica.',
                'etiqueta' => 'Rechazado',
                'tono' => 'rojo',
                'enlace' => route('estudiante.documentos.show', $documento->idDocumento),
            ]);
        }

        if ($activa && $pasantia->fechaFinEstimada && $avance['restantes'] > 0) {
            $dias = (int) today()->diffInDays($pasantia->fechaFinEstimada->copy()->startOfDay(), false);

            if ($dias < 0) {
                $alertas->push([
                    'titulo' => 'La fecha final de la pasantía ya pasó',
                    'detalle' => 'Aún faltan ' . $avance['restantes'] . ' h por completar. Habla con tu tutor y con Coordinación Académica.',
                    'etiqueta' => 'Urgente',
                    'tono' => 'rojo',
                    'enlace' => null,
                ]);
            } elseif ($dias <= self::DIAS_AVISO_FECHA_FINAL) {
                $alertas->push([
                    'titulo' => $dias === 0
                        ? 'Hoy es el último día de tu pasantía'
                        : 'Quedan ' . $dias . ($dias === 1 ? ' día' : ' días') . ' para la fecha final',
                    'detalle' => 'Te faltan ' . $avance['restantes'] . ' h por completar.',
                    'etiqueta' => 'Fecha próxima',
                    'tono' => 'ambar',
                    'enlace' => null,
                ]);
            }
        }

        foreach ($documentos->where('estadoArchivo', 'A corregir') as $documento) {
            $alertas->push([
                'titulo' => 'Corregir ' . ($documento->formato?->nombreFormato ?? 'documento'),
                'detalle' => $documento->observacion ?: 'Envía una versión corregida.',
                'etiqueta' => 'A corregir',
                'tono' => 'ambar',
                'enlace' => route('estudiante.documentos.show', $documento->idDocumento),
            ]);
        }

        if ($resumen['faltantes']->isNotEmpty()) {
            $alertas->push([
                'titulo' => 'Subir documentación pendiente',
                'detalle' => 'Falta: ' . $resumen['faltantes']->join(', ', ' y ') . '.',
                'etiqueta' => 'Pendiente de atención',
                'tono' => 'ambar',
                'enlace' => $enlaceDocumentos,
            ]);
        }

        // Reglas de riesgo que se cumplieron en la última evaluación.
        if ($riesgo && $riesgo['color'] !== 'verde') {
            foreach ($riesgo['reglas'] as $detalle) {
                $alertas->push([
                    'titulo' => $detalle->regla?->nombre ?? 'Regla de riesgo',
                    'detalle' => $detalle->regla?->descripcion,
                    'etiqueta' => 'Riesgo ' . mb_strtolower($riesgo['nivel']),
                    'tono' => $riesgo['color'] === 'rojo' ? 'rojo' : 'ambar',
                    'enlace' => null,
                ]);
            }
        }

        if ($avance['pendientes'] > 0) {
            $alertas->push([
                'titulo' => $avance['pendientes'] . ' h esperan aprobación',
                'detalle' => 'Tu tutor debe revisar las horas que registraste.',
                'etiqueta' => 'En revisión',
                'tono' => 'azul',
                'enlace' => route('estudiante.pasantias.show', $pasantia->idPasantia),
            ]);
        }

        return $alertas;
    }
}
