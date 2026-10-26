<?php

namespace App\Services\RulesEngine;

use App\Models\ReglaRiesgo;
use InvalidArgumentException;

class RulesEngine
{
    /**
     * Evalúa las reglas activas y devuelve la puntuación total.
     */
    public function matchRules(array $context): array
    {
        $rules = ReglaRiesgo::with('condiciones')
            ->where('activa', true)
            ->orderBy('prioridad')
            ->orderBy('idReglaRiesgo')
            ->get();

        $totalScore = 0.0;
        $evaluatedRules = [];

        foreach ($rules as $rule) {
            $conditions = $rule->condiciones;

            if ($conditions->isEmpty()) {
                throw new InvalidArgumentException(
                    "La regla '{$rule->nombre}' no tiene condiciones."
                );
            }

            $results = [];

            foreach ($conditions as $condition) {
                if (!array_key_exists($condition->variable, $context)) {
                    throw new InvalidArgumentException(
                        "La variable '{$condition->variable}' "
                        . "no existe en el contexto."
                    );
                }

                $actual = $context[$condition->variable];

                $expected = $this->parseParameter(
                    $condition->parametro,
                    $condition->tipoParametro
                );

                $results[] = [
                    'idCondicionRegla' => $condition->idCondicionRegla,
                    'variable' => $condition->variable,
                    'operador' => $condition->operador,
                    'valorReal' => $actual,
                    'parametro' => $expected,
                    'cumplida' => $this->evaluateCondition(
                        $actual,
                        $condition->operador,
                        $expected
                    ),
                ];
            }

            /*
             * En tu esquema, el conector de una condición
             * une esa condición con la siguiente.
             *
             * Por simplicidad, todas las uniones de una regla
             * deben ser AND o todas deben ser OR.
             */
            $connectors = $conditions
                ->take($conditions->count() - 1)
                ->pluck('conector')
                ->map(fn ($value) => strtoupper(trim($value)))
                ->unique()
                ->values();

            if ($connectors->count() > 1) {
                throw new InvalidArgumentException(
                    "La regla '{$rule->nombre}' mezcla AND y OR. "
                    . "Usa un solo conector por regla."
                );
            }

            $connector = $connectors->first() ?? 'AND';

            if (!in_array($connector, ['AND', 'OR'], true)) {
                throw new InvalidArgumentException(
                    "Conector inválido en la regla '{$rule->nombre}'."
                );
            }

            $conditionValues = array_column($results, 'cumplida');

            $matched = $connector === 'AND'
                ? !in_array(false, $conditionValues, true)
                : in_array(true, $conditionValues, true);

            $score = 0.0;

            if ($matched) {
                $weight = (float) $rule->puntuacion;

                if ($rule->variablePuntuacion) {
                    $scoreValue = $context[$rule->variablePuntuacion]
                        ?? null;

                    if (!is_numeric($scoreValue)) {
                        throw new InvalidArgumentException(
                            "La variable de puntuación "
                            . "'{$rule->variablePuntuacion}' "
                            . "debe ser numérica."
                        );
                    }

                    $score = (float) $scoreValue * $weight;
                } else {
                    // Sin variable de puntuación, la regla aporta
                    // su puntuación fija.
                    $score = $weight;
                }
            }

            $score = round($score, 2);
            $totalScore += $score;

            $evaluatedRules[] = [
                'idReglaRiesgo' => $rule->idReglaRiesgo,
                'nombre' => $rule->nombre,
                'cumplida' => $matched,
                'conector' => $connector,
                'puntuacionAplicada' => $score,
                'condiciones' => $results,
            ];
        }

        return [
            'puntuacionTotal' => round($totalScore, 2),
            'reglasEvaluadas' => $evaluatedRules,
        ];
    }

    /**
     * Convierte los parámetros guardados a tipos comparables.
     */
    private function parseParameter(
        string $parameter,
        string $type
    ): mixed {
        return match (strtolower(trim($type))) {
            'entero', 'integer', 'int' =>
                $this->parseInteger($parameter),

            'decimal', 'float', 'number' =>
                $this->parseNumber($parameter),

            'booleano', 'boolean', 'bool' =>
                $this->parseBoolean($parameter),

            'texto', 'string', 'fecha', 'date' => $parameter,

            default => throw new InvalidArgumentException(
                "Tipo de parámetro no permitido: {$type}"
            ),
        };
    }

    private function parseInteger(string $value): int
    {
        $parsed = filter_var($value, FILTER_VALIDATE_INT);

        if ($parsed === false) {
            throw new InvalidArgumentException(
                "El parámetro '{$value}' no es un entero válido."
            );
        }

        return $parsed;
    }

    private function parseNumber(string $value): float
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                "El parámetro '{$value}' no es numérico."
            );
        }

        return (float) $value;
    }

    private function parseBoolean(string $value): bool
    {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($parsed === null) {
            throw new InvalidArgumentException(
                "El parámetro '{$value}' no es booleano válido."
            );
        }

        return $parsed;
    }

    /**
     * Operadores admitidos por la primera versión del motor.
     */
    private function evaluateCondition(
        mixed $actual,
        string $operator,
        mixed $expected
    ): bool {
        $operator = trim($operator);

        return match ($operator) {
            '=', '==' => $actual == $expected,
            '!=' , '<>' => $actual != $expected,

            '>', '>=' , '<', '<=' => $this->compareValues(
                $actual,
                $expected,
                $operator
            ),

            default => throw new InvalidArgumentException(
                "Operador no permitido: {$operator}"
            ),
        };
    }

    private function compareValues(
        mixed $actual,
        mixed $expected,
        string $operator
    ): bool {
        if (!is_numeric($actual) || !is_numeric($expected)) {
            throw new InvalidArgumentException(
                'Los operadores de comparación numérica '
                . 'requieren valores numéricos.'
            );
        }

        return match ($operator) {
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
        };
    }
}
