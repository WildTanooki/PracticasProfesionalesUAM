<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CoordinacionAcademica;
use App\Models\ReglaRiesgo;
use App\Models\UmbralRiesgo;
use App\Services\RulesEngine\RiskVariables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Pantalla de reglas parametrizables: el coordinador crea y edita las
 * reglas de riesgo con sus condiciones, y ajusta los umbrales que
 * convierten la puntuación en un nivel de riesgo.
 */
class ReglaRiesgoCoordinadorController extends Controller
{
    // Máximo que admiten las columnas decimal(5, 2).
    private const PUNTUACION_MAXIMA = 999.99;

    private const MAX_CONDICIONES = 10;

    // Niveles que se proponen cuando todavía no hay umbrales.
    private const UMBRALES_INICIALES = [
        ['nivel' => 'Bajo', 'limiteInferior' => '0.00', 'limiteSuperior' => '24.99'],
        ['nivel' => 'Medio', 'limiteInferior' => '25.00', 'limiteSuperior' => '49.99'],
        ['nivel' => 'Alto', 'limiteInferior' => '50.00', 'limiteSuperior' => null],
    ];

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

    /**
     * Listar las reglas de la carrera y las generales, con búsqueda,
     * filtro de estado y orden.
     */
    public function index(Request $request)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['Activa', 'Inactiva'])],
            'orden' => ['nullable', Rule::in(['recientes', 'prioridad', 'nombre'])],
        ]);

        $orden = $filtros['orden'] ?? 'recientes';

        $reglas = ReglaRiesgo::with(['condiciones', 'coordinador'])
            ->visiblesParaCarrera($coordinacion->idCarrera)
            ->when($filtros['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($consulta) use ($buscar) {
                    $consulta->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when(
                $filtros['estado'] ?? null,
                fn ($query, $estado) => $query->where('activa', $estado === 'Activa')
            )
            ->when($orden === 'recientes', fn ($query) => $query->orderByDesc('idReglaRiesgo'))
            ->when($orden === 'prioridad', fn ($query) => $query->orderBy('prioridad')->orderBy('idReglaRiesgo'))
            ->when($orden === 'nombre', fn ($query) => $query->orderBy('nombre'))
            ->get();

        $umbrales = UmbralRiesgo::where('activo', true)
            ->orderBy('limiteInferior')
            ->get()
            ->map(fn (UmbralRiesgo $umbral) => [
                'nivel' => $umbral->nivel,
                'limiteInferior' => $umbral->limiteInferior,
                'limiteSuperior' => $umbral->limiteSuperior,
            ])
            ->all();

        return view('coordinador.reglas', [
            'reglas' => $reglas,
            'filtros' => $filtros + ['orden' => $orden],
            'umbrales' => $umbrales ?: self::UMBRALES_INICIALES,
            'umbralesConfigurados' => $umbrales !== [],
            'idCarrera' => $coordinacion->idCarrera,
            'variables' => RiskVariables::VARIABLES,
            'operadores' => RiskVariables::OPERADORES,
            'conectores' => RiskVariables::CONECTORES,
        ]);
    }

    /**
     * Registrar una regla con sus condiciones.
     */
    public function store(Request $request)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $datos = $this->validarRegla($request);

        DB::transaction(function () use ($datos, $coordinacion) {
            $regla = ReglaRiesgo::create(
                $this->datosRegla($datos) + [
                    'idCoordinador' => $coordinacion->idCoordinador,
                ]
            );

            $this->guardarCondiciones($regla, $datos);
        });

        return redirect()
            ->route('coordinador.reglas')
            ->with('success', 'Regla registrada correctamente.');
    }

    /**
     * Actualizar una regla de la carrera o general y reemplazar sus
     * condiciones. Las reglas de otras carreras no se pueden editar.
     * Las evaluaciones anteriores no cambian: guardan su propia copia
     * de la configuración usada.
     */
    public function update(Request $request, $idReglaRiesgo)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $regla = ReglaRiesgo::where('idReglaRiesgo', $idReglaRiesgo)
            ->visiblesParaCarrera($coordinacion->idCarrera)
            ->firstOrFail();

        $datos = $this->validarRegla($request);

        DB::transaction(function () use ($regla, $datos) {
            $regla->update($this->datosRegla($datos));

            $regla->condiciones()->delete();
            $this->guardarCondiciones($regla, $datos);
        });

        return redirect()
            ->route('coordinador.reglas')
            ->with('success', 'Regla actualizada correctamente.');
    }

    /**
     * Guardar los umbrales de riesgo. Deben empezar en 0, ir seguidos
     * sin huecos ni superposiciones, y el último no tiene límite superior.
     */
    public function actualizarUmbrales(Request $request)
    {
        $this->obtenerCoordinacion();

        $datos = $request->validate([
            'umbrales' => ['required', 'array', 'min:2', 'max:6'],
            'umbrales.*.nivel' => ['required', 'string', 'max:20', 'distinct'],
            'umbrales.*.limiteInferior' => ['required', 'numeric', 'min:0', 'max:' . self::PUNTUACION_MAXIMA, 'decimal:0,2'],
            'umbrales.*.limiteSuperior' => ['nullable', 'numeric', 'min:0', 'max:' . self::PUNTUACION_MAXIMA, 'decimal:0,2'],
        ], [
            'umbrales.*.nivel.required' => 'Cada nivel debe tener un nombre.',
            'umbrales.*.nivel.distinct' => 'No puede haber dos niveles con el mismo nombre.',
            'umbrales.*.limiteInferior.required' => 'Cada nivel debe tener un límite inferior.',
            'umbrales.*.limiteInferior.numeric' => 'Los límites deben ser números.',
            'umbrales.*.limiteSuperior.numeric' => 'Los límites deben ser números.',
            'umbrales.*.limiteInferior.decimal' => 'Los límites admiten como máximo 2 decimales.',
            'umbrales.*.limiteSuperior.decimal' => 'Los límites admiten como máximo 2 decimales.',
        ]);

        $umbrales = collect($datos['umbrales'])
            ->map(fn ($umbral) => [
                'nivel' => trim($umbral['nivel']),
                'limiteInferior' => round((float) $umbral['limiteInferior'], 2),
                'limiteSuperior' => isset($umbral['limiteSuperior']) && $umbral['limiteSuperior'] !== ''
                    ? round((float) $umbral['limiteSuperior'], 2)
                    : null,
            ])
            ->sortBy('limiteInferior')
            ->values();

        $this->validarContinuidad($umbrales->all());

        DB::transaction(function () use ($umbrales) {
            // Los niveles que ya no están en la lista quedan inactivos
            // para no romper evaluaciones que los mencionan.
            UmbralRiesgo::whereNotIn('nivel', $umbrales->pluck('nivel'))
                ->update(['activo' => false]);

            foreach ($umbrales as $umbral) {
                UmbralRiesgo::updateOrCreate(
                    ['nivel' => $umbral['nivel']],
                    [
                        'limiteInferior' => $umbral['limiteInferior'],
                        'limiteSuperior' => $umbral['limiteSuperior'],
                        'activo' => true,
                    ]
                );
            }
        });

        return redirect()
            ->route('coordinador.reglas')
            ->with('success', 'Niveles de riesgo actualizados correctamente.');
    }

    /**
     * Mismas comprobaciones que hace RulesEngine al clasificar, para
     * no guardar umbrales con los que ninguna evaluación funcionaría.
     */
    private function validarContinuidad(array $umbrales): void
    {
        $error = fn (string $mensaje) => throw ValidationException::withMessages([
            'umbrales' => $mensaje,
        ]);

        if ($umbrales[0]['limiteInferior'] != 0) {
            $error('El primer nivel debe empezar en 0.');
        }

        $ultimo = count($umbrales) - 1;

        foreach ($umbrales as $i => $umbral) {
            $superior = $umbral['limiteSuperior'];

            if ($i < $ultimo && $superior === null) {
                $error("Solo el último nivel puede quedar sin límite superior. Falta el de '{$umbral['nivel']}'.");
            }

            if ($i === $ultimo && $superior !== null) {
                $error("El último nivel ('{$umbral['nivel']}') no debe tener límite superior.");
            }

            if ($superior !== null && $superior < $umbral['limiteInferior']) {
                $error("En '{$umbral['nivel']}' el límite superior es menor que el inferior.");
            }

            if ($i > 0) {
                $anterior = $umbrales[$i - 1];
                $esperado = round($anterior['limiteSuperior'] + 0.01, 2);

                if (abs($umbral['limiteInferior'] - $esperado) > 0.0001) {
                    $error(
                        "'{$umbral['nivel']}' debe empezar en "
                        . number_format($esperado, 2)
                        . ", justo después de '{$anterior['nivel']}', para que no queden huecos ni se superpongan."
                    );
                }
            }
        }
    }

    /**
     * Reglas de validación compartidas por crear y editar.
     */
    private function validarRegla(Request $request): array
    {
        $variables = array_keys(RiskVariables::VARIABLES);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'puntuacion' => ['required', 'numeric', 'min:0', 'max:' . self::PUNTUACION_MAXIMA, 'decimal:0,2'],
            'variablePuntuacion' => ['nullable', Rule::in($variables)],
            'prioridad' => ['required', 'integer', 'min:1', 'max:999'],
            'activa' => ['required', 'boolean'],
            'conector' => ['required', Rule::in(array_keys(RiskVariables::CONECTORES))],
            'condiciones' => ['required', 'array', 'min:1', 'max:' . self::MAX_CONDICIONES],
            'condiciones.*.variable' => ['required', Rule::in($variables)],
            'condiciones.*.operador' => ['required', Rule::in(array_keys(RiskVariables::OPERADORES))],
            'condiciones.*.parametro' => ['required', 'numeric', 'min:0', 'max:99999'],
            // Indica qué formulario reabrir si hay errores.
            '_formulario' => ['nullable', Rule::in(['crear', 'editar'])],
        ], [
            'nombre.required' => 'Debes indicar el nombre de la regla.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
            'puntuacion.required' => 'Debes indicar cuántos puntos suma la regla.',
            'puntuacion.numeric' => 'La puntuación debe ser un número.',
            'puntuacion.min' => 'La puntuación no puede ser negativa.',
            'puntuacion.max' => 'La puntuación no puede superar 999.99.',
            'puntuacion.decimal' => 'La puntuación admite como máximo 2 decimales.',
            'variablePuntuacion.in' => 'La variable para multiplicar la puntuación no es válida.',
            'prioridad.required' => 'Debes indicar la prioridad.',
            'prioridad.integer' => 'La prioridad debe ser un número entero.',
            'prioridad.min' => 'La prioridad debe ser 1 o más.',
            'conector.in' => 'Elige si se deben cumplir todas las condiciones o al menos una.',
            'condiciones.required' => 'La regla debe tener al menos una condición.',
            'condiciones.min' => 'La regla debe tener al menos una condición.',
            'condiciones.max' => 'Una regla puede tener como máximo ' . self::MAX_CONDICIONES . ' condiciones.',
            'condiciones.*.variable.required' => 'Elige la variable de cada condición.',
            'condiciones.*.variable.in' => 'Una de las variables elegidas no es válida.',
            'condiciones.*.operador.required' => 'Elige el operador de cada condición.',
            'condiciones.*.operador.in' => 'Uno de los operadores elegidos no es válido.',
            'condiciones.*.parametro.required' => 'Escribe el valor de cada condición.',
            'condiciones.*.parametro.numeric' => 'El valor de cada condición debe ser un número.',
            'condiciones.*.parametro.min' => 'El valor de una condición no puede ser negativo.',
            'condiciones.*.parametro.max' => 'El valor de una condición no puede superar 99999.',
        ]);

        // Las variables enteras (documentos, días) no admiten decimales.
        $errores = [];

        foreach (array_values($datos['condiciones']) as $i => $condicion) {
            $tipo = RiskVariables::tipo($condicion['variable']);

            if ($tipo === 'entero' && filter_var($condicion['parametro'], FILTER_VALIDATE_INT) === false) {
                $errores["condiciones.{$i}.parametro"] =
                    "'" . RiskVariables::etiqueta($condicion['variable']) . "' solo admite números enteros.";
            }

            if ($condicion['variable'] === 'porcentaje_documental_completado' && $condicion['parametro'] > 100) {
                $errores["condiciones.{$i}.parametro"] = 'Un porcentaje no puede ser mayor a 100.';
            }
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        return $datos;
    }

    private function datosRegla(array $datos): array
    {
        return [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'puntuacion' => $datos['puntuacion'],
            'variablePuntuacion' => $datos['variablePuntuacion'] ?? null,
            'prioridad' => $datos['prioridad'],
            'activa' => (bool) $datos['activa'],
        ];
    }

    /**
     * Crear las condiciones en orden. El tipo de parámetro sale del
     * catálogo de variables, no del formulario.
     */
    private function guardarCondiciones(ReglaRiesgo $regla, array $datos): void
    {
        foreach (array_values($datos['condiciones']) as $i => $condicion) {
            $tipo = RiskVariables::tipo($condicion['variable']);

            $parametro = $tipo === 'entero'
                ? (string) (int) $condicion['parametro']
                : (string) ($condicion['parametro'] + 0);

            $regla->condiciones()->create([
                'variable' => $condicion['variable'],
                'operador' => $condicion['operador'],
                'parametro' => $parametro,
                'tipoParametro' => $tipo,
                'conector' => $datos['conector'],
                'orden' => $i + 1,
            ]);
        }
    }
}
