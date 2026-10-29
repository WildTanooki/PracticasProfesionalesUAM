<?php

namespace App\Services\RulesEngine;

/**
 * Recomendaciones para el coordinador a partir del contexto con el que
 * se evaluó una pasantía.
 */
class RiskRecommendations
{
    // Días antes de la fecha final en los que se pide seguimiento cercano.
    private const DIAS_SEGUIMIENTO = 15;

    /**
     * @return list<string>
     */
    public static function para(array $contexto): array
    {
        $noEntregados = (int) ($contexto['documentos_no_entregados'] ?? 0);
        $aCorregir = (int) ($contexto['documentos_a_corregir'] ?? 0);
        $rechazados = (int) ($contexto['documentos_rechazados'] ?? 0);
        $pendientes = (int) ($contexto['documentos_pendientes'] ?? 0);
        $dias = isset($contexto['dias_restantes']) ? (int) $contexto['dias_restantes'] : null;

        $recomendaciones = [];

        if ($noEntregados > 0) {
            $recomendaciones[] = 'Pedir al estudiante que entregue '
                . self::cantidad($noEntregados, 'el documento que le falta', 'los %d documentos que le faltan') . '.';
        }

        if ($aCorregir > 0) {
            $recomendaciones[] = 'Recordar al estudiante que corrija '
                . self::cantidad($aCorregir, 'el documento marcado como "A corregir"', 'los %d documentos marcados como "A corregir"') . '.';
        }

        if ($rechazados > 0) {
            $recomendaciones[] = 'Revisar con el estudiante '
                . self::cantidad($rechazados, 'el documento rechazado', 'los %d documentos rechazados')
                . ' y cómo volver a presentarlos.';
        }

        if ($pendientes > 0) {
            $recomendaciones[] = 'Revisar '
                . self::cantidad($pendientes, 'el documento que espera revisión', 'los %d documentos que esperan revisión') . '.';
        }

        $documentacionIncompleta = $noEntregados + $aCorregir + $rechazados + $pendientes > 0;

        if ($dias !== null && $dias < 0) {
            $recomendaciones[] = 'La fecha final estimada pasó hace '
                . self::cantidad(-$dias, '1 día', '%d días')
                . ': confirmar con la empresa si la pasantía se extendió.';
        } elseif ($dias !== null && $dias <= self::DIAS_SEGUIMIENTO && $documentacionIncompleta) {
            $recomendaciones[] = 'Quedan ' . self::cantidad($dias, '1 día', '%d días')
                . ' para la fecha final: dar seguimiento cercano al estudiante y a la empresa.';
        }

        if ($recomendaciones === []) {
            $recomendaciones[] = 'La documentación está al día. No se requiere ninguna acción por ahora.';
        }

        return $recomendaciones;
    }

    private static function cantidad(int $n, string $singular, string $plural): string
    {
        return $n === 1 ? $singular : sprintf($plural, $n);
    }
}
