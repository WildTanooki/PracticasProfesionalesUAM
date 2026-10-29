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
    // frase: cómo se nombra la variable dentro de una oración.
    // plural: si la frase pide "son" en vez de "es".
    // porCada: texto cuando la puntuación se multiplica por la variable.
    public const VARIABLES = [
        'porcentaje_documental_completado' => [
            'etiqueta' => 'Porcentaje de documentos aprobados',
            'tipo' => 'decimal',
            'unidad' => '%',
            'frase' => 'el porcentaje de documentos aprobados',
            'plural' => false,
            'porCada' => 'por cada punto de porcentaje aprobado',
        ],
        'documentos_aprobados' => [
            'etiqueta' => 'Documentos aprobados',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos aprobados',
            'plural' => false,
            'porCada' => 'por cada documento aprobado',
        ],
        'documentos_no_entregados' => [
            'etiqueta' => 'Documentos sin entregar',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos sin entregar',
            'plural' => false,
            'porCada' => 'por cada documento sin entregar',
        ],
        'documentos_pendientes' => [
            'etiqueta' => 'Documentos pendientes de revisión',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos pendientes de revisión',
            'plural' => false,
            'porCada' => 'por cada documento pendiente de revisión',
        ],
        'documentos_a_corregir' => [
            'etiqueta' => 'Documentos a corregir',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos a corregir',
            'plural' => false,
            'porCada' => 'por cada documento a corregir',
        ],
        'documentos_rechazados' => [
            'etiqueta' => 'Documentos rechazados',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos rechazados',
            'plural' => false,
            'porCada' => 'por cada documento rechazado',
        ],
        'documentos_obligatorios' => [
            'etiqueta' => 'Documentos obligatorios',
            'tipo' => 'entero',
            'unidad' => 'documentos',
            'frase' => 'la cantidad de documentos obligatorios',
            'plural' => false,
            'porCada' => 'por cada documento obligatorio',
        ],
        'dias_restantes' => [
            'etiqueta' => 'Días que faltan para la fecha final de la pasantía',
            'tipo' => 'entero',
            'unidad' => 'días',
            'frase' => 'los días que faltan para la fecha final de la pasantía',
            'plural' => true,
            'porCada' => 'por cada día que falta para la fecha final',
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

    // Los mismos operadores concordando con una frase en plural.
    public const OPERADORES_PLURAL = [
        '<' => 'menores que',
        '<=' => 'menores o iguales que',
        '>' => 'mayores que',
        '>=' => 'mayores o iguales que',
        '=' => 'iguales a',
        '!=' => 'distintos de',
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

    /**
     * Condición en lenguaje natural, ej. "los días que faltan para la
     * fecha final de la pasantía son mayores o iguales que 2".
     */
    public static function frase(string $variable, string $operador, string $parametro): string
    {
        $datos = self::VARIABLES[$variable] ?? null;

        if ($datos === null) {
            return trim("{$variable} {$operador} {$parametro}");
        }

        $operadores = $datos['plural'] ? self::OPERADORES_PLURAL : self::OPERADORES;
        $valor = $datos['unidad'] === '%' ? $parametro . '%' : $parametro;

        return $datos['frase'] . ' ' . ($datos['plural'] ? 'son' : 'es') . ' '
            . ($operadores[$operador] ?? $operador) . ' ' . $valor;
    }
}
