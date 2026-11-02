<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\Pasantia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calcula y guarda el estado de una pasantía a partir de sus horas
 * aprobadas, sus documentos y su fecha de finalización.
 *
 * - Culminada: cumplió las horas y todos los documentos están aprobados.
 * - Pendiente de revisión: cumplió las horas y entregó todos los
 *   documentos, pero Coordinación Académica aún debe revisar alguno.
 * - Culminada con incumplimiento: pasó la fecha de finalización sin
 *   completar las horas o sin entregar toda la documentación.
 * - En progreso: todavía está dentro del período y le falta algo.
 *
 * Solo se recalculan las pasantías que ya están en uno de estos cuatro
 * estados. Las que tienen otro estado (por ejemplo, Pendiente o
 * Cancelada) no se tocan.
 */
class EstadoPasantiaService
{
    public const EN_PROGRESO = 'En progreso';

    public const CULMINADA = 'Culminada';

    public const CULMINADA_CON_INCUMPLIMIENTO = 'Culminada con incumplimiento';

    public const PENDIENTE_REVISION = 'Pendiente de revisión';

    // Estados que el sistema administra de forma automática.
    public const ESTADOS_AUTOMATICOS = [
        self::EN_PROGRESO,
        self::PENDIENTE_REVISION,
        self::CULMINADA,
        self::CULMINADA_CON_INCUMPLIMIENTO,
    ];

    public function __construct(
        private GestionDocumentalService $gestionDocumental
    ) {
    }

    /**
     * Recalcular y guardar el estado. Devuelve el estado resultante.
     */
    public function actualizar(Pasantia|int $pasantia, ?Carbon $hoy = null): string
    {
        $id = $pasantia instanceof Pasantia ? $pasantia->idPasantia : $pasantia;

        return DB::transaction(function () use ($id, $hoy) {
            $pasantia = Pasantia::with('practicaProfesional.estudiante.planEstudios')
                ->lockForUpdate()
                ->findOrFail($id);

            if (! in_array($pasantia->estado, self::ESTADOS_AUTOMATICOS, true)) {
                return (string) $pasantia->estado;
            }

            $horasAprobadas = $pasantia->horasAprobadas;
            $nuevoEstado = $this->calcular($pasantia, $horasAprobadas, $hoy);

            $pasantia->estado = $nuevoEstado;
            $pasantia->horasAcumuladas = $horasAprobadas;

            if ($pasantia->isDirty()) {
                $pasantia->save();
            }

            return $nuevoEstado;
        });
    }

    /**
     * Recalcular todas las pasantías con estado automático. Devuelve
     * cuántas cambiaron de estado.
     */
    public function actualizarTodas(?Carbon $hoy = null): int
    {
        $cambios = 0;

        Pasantia::whereIn('estado', self::ESTADOS_AUTOMATICOS)
            ->select(['idPasantia', 'estado'])
            ->chunkById(100, function ($pasantias) use (&$cambios, $hoy) {
                foreach ($pasantias as $pasantia) {
                    if ($this->actualizar($pasantia->idPasantia, $hoy) !== $pasantia->estado) {
                        $cambios++;
                    }
                }
            }, 'idPasantia');

        return $cambios;
    }

    /**
     * Determinar el estado que corresponde, sin guardarlo.
     */
    public function calcular(Pasantia $pasantia, ?int $horasAprobadas = null, ?Carbon $hoy = null): string
    {
        $hoy = ($hoy ?? today())->copy()->startOfDay();
        $horasAprobadas ??= $pasantia->horasAprobadas;

        $horasCompletas = (int) $pasantia->horasRequeridas > 0
            && $horasAprobadas >= (int) $pasantia->horasRequeridas;

        $documentos = $this->estadoDocumental($pasantia);

        if ($horasCompletas && $documentos['todosAprobados']) {
            return self::CULMINADA;
        }

        if ($horasCompletas && $documentos['todosEntregados']) {
            return self::PENDIENTE_REVISION;
        }

        // La fecha final es el último día válido para registrar horas,
        // así que el incumplimiento se marca a partir del día siguiente.
        if ($pasantia->fechaFinEstimada !== null
            && $hoy->gt($pasantia->fechaFinEstimada->copy()->startOfDay())) {
            return self::CULMINADA_CON_INCUMPLIMIENTO;
        }

        return self::EN_PROGRESO;
    }

    /**
     * Revisa los formatos requeridos para la carrera del estudiante.
     *
     * Un documento cuenta como entregado si está Pendiente (en espera de
     * revisión) o Aprobado. Si está "A corregir" o "Rechazado", el
     * estudiante todavía debe entregarlo de nuevo.
     *
     * @return array{todosEntregados: bool, todosAprobados: bool}
     */
    public function estadoDocumental(Pasantia $pasantia): array
    {
        $idsFormatos = $this->gestionDocumental
            ->formatosRequeridos($pasantia)
            ->pluck('idFormato');

        $estados = Documento::where('idPasantia', $pasantia->idPasantia)
            ->whereIn('idFormato', $idsFormatos)
            ->pluck('estadoArchivo', 'idFormato');

        $todosEntregados = true;
        $todosAprobados = true;

        foreach ($idsFormatos as $idFormato) {
            $estado = $estados[$idFormato] ?? null;

            if ($estado !== 'Aprobado') {
                $todosAprobados = false;
            }

            if (! in_array($estado, ['Pendiente', 'Aprobado'], true)) {
                $todosEntregados = false;
            }
        }

        return [
            'todosEntregados' => $todosEntregados,
            'todosAprobados' => $todosAprobados,
        ];
    }
}
