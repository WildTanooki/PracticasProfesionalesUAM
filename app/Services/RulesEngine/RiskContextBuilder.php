<?php

namespace App\Services\RulesEngine;

use App\Models\Formato;
use App\Models\Pasantia;
use RuntimeException;

class RiskContextBuilder
{
    public function build(int $idPasantia): array
    {
        $pasantia = Pasantia::with([
            'practicaProfesional.estudiante.planEstudios',
        ])->findOrFail($idPasantia);

        // Validar que la pasantía esté en progreso.
        if ($pasantia->estado !== 'En progreso') {
            throw new \RuntimeException(
                'Solo se pueden evaluar pasantías en progreso.'
                );
        }

        // Obtener la carrera a la que pertenece el estudiante.
        $idCarrera = $pasantia
            ->practicaProfesional
            ?->estudiante
            ?->planEstudios
            ?->idCarrera;

        if ($idCarrera === null) {
            throw new RuntimeException(
                'No se pudo determinar la carrera de la pasantía.'
            );
        }

        // Obtener solo los formatos activos de esa carrera.
        $formatos = Formato::where('estado', 'Activo')
            ->whereHas('coordinador', function ($consulta) use ($idCarrera) {
                $consulta->where('idCarrera', $idCarrera);
            })
            ->get();

        // La fecha final es necesaria para calcular los días restantes.
        if ($pasantia->fechaFinEstimada === null) {
            throw new RuntimeException(
                'La pasantía no tiene fecha final estimada.'
            );
        }

        $diasRestantes = (int) today()->diffInDays(
            $pasantia->fechaFinEstimada,
            false
        );

        $idsFormatos = $formatos->pluck('idFormato');

        // Solo contar documentos de esta pasantía y de sus formatos.
        $documentos = $pasantia->documentos()
            ->whereIn('idFormato', $idsFormatos)
            ->with('ultimaVersion')
            ->get();

        // Formatos para los que todavía no existe un documento.
        $formatosEntregados = $documentos
            ->pluck('idFormato')
            ->unique()
            ->count();

        $documentosNoEntregados = $formatos->count()
            - $formatosEntregados;

        $conteoEstados = [
            'Pendiente' => 0,
            'Rechazado' => 0,
            'A corregir' => 0,
            'Aprobado' => 0,
        ];

        foreach ($documentos as $documento) {
            // Priorizar el estado de la última versión registrada.
            $estado = $documento->ultimaVersion?->estadoArchivo
                ?? $documento->estadoArchivo;

            if (array_key_exists($estado, $conteoEstados)) {
                $conteoEstados[$estado]++;
            }
        }

        $documentosObligatorios = $formatos->count();

        $porcentajeCompletado = $documentosObligatorios > 0
            ? round(
                ($conteoEstados['Aprobado'] / $documentosObligatorios)
                    * 100,
                2
            )
            : 0;

        return [
            'dias_restantes' => $diasRestantes,
            'documentos_obligatorios' => $documentosObligatorios,
            'documentos_no_entregados' => $documentosNoEntregados,
            'documentos_pendientes' => $conteoEstados['Pendiente'],
            'documentos_rechazados' => $conteoEstados['Rechazado'],
            'documentos_a_corregir' => $conteoEstados['A corregir'],
            'documentos_aprobados' => $conteoEstados['Aprobado'],
            'porcentaje_documental_completado' => $porcentajeCompletado,
        ];
    }
}
