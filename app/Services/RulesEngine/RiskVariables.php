<?php

namespace App\Services\RulesEngine;

/**
 * Variables y operadores que el coordinador puede usar al crear reglas.
 *
 * Las variables son las que calcula RiskContextBuilder: si se agrega
 * una variable allí, se debe registrar aquí para que aparezca en la
 * pantalla de reglas parametrizables.
 */
class RiskVariables
{
    public const VARIABLES = [
        'porcentaje_documental_completado' => [
            'etiqueta' => 'Porcentaje de documentos aprobados',
            'tipo' => 'decimal',
            'unidad' => '%',
        ],
        'documentos_aprobados' => [
            'etiqueta' => 'Documentos aprobados',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'documentos_no_entregados' => [
            'etiqueta' => 'Documentos sin entregar',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'documentos_pendientes' => [
            'etiqueta' => 'Documentos pendientes de revisión',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'documentos_a_corregir' => [
            'etiqueta' => 'Documentos a corregir',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'documentos_rechazados' => [
            'etiqueta' => 'Documentos rechazados',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'documentos_obligatorios' => [
            'etiqueta' => 'Documentos obligatorios',
            'tipo' => 'entero',
            'unidad' => 'documentos',
        ],
        'dias_restantes' => [
            'etiqueta' => 'Días para la fecha final',
            'tipo' => 'entero',
            'unidad' => 'días',
        ],
    ];

    // Operadores que acepta RulesEngine, con su texto para la pantalla.
    public const OPERADORES = [
        '<' => 'menor que',
        '<=' => 'menor o igual que',
        '>' => 'mayor que',
        '>=' => 'mayor o igual que',
        '=' => 'igual a',
        '!=' => 'distinto de',
    ];

    public const CONECTORES = [
        'AND' => 'Se cumplen todas',
        'OR' => 'Se cumple al menos una',
    ];

    public static function etiqueta(?string $variable): string
    {
        return self::VARIABLES[$variable]['etiqueta'] ?? (string) $variable;
    }

    public static function unidad(?string $variable): string
    {
        return self::VARIABLES[$variable]['unidad'] ?? '';
    }

    public static function tipo(string $variable): string
    {
        return self::VARIABLES[$variable]['tipo'] ?? 'decimal';
    }
}
