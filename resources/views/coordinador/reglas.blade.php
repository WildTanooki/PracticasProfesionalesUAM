<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reglas parametrizables | Coordinación</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    use App\Services\RulesEngine\RiskVariables;
    use Illuminate\Support\Js;

    $reglaVacia = [
        'id' => null,
        'nombre' => '',
        'descripcion' => '',
        'conector' => 'AND',
        'condiciones' => [
            ['variable' => 'porcentaje_documental_completado', 'operador' => '<', 'parametro' => ''],
        ],
        'puntuacion' => '',
        'variablePuntuacion' => '',
        'prioridad' => 1,
        'activa' => '1',
    ];

    // Si hubo errores al guardar una regla, se reabre su formulario
    // con lo que había escrito el coordinador.
    $formularioConError = $errors->any() ? old('_formulario') : null;

    $reglaConError = in_array($formularioConError, ['crear', 'editar'], true)
        ? [
            'id' => old('idReglaRiesgo'),
            'nombre' => old('nombre', ''),
            'descripcion' => old('descripcion', ''),
            'conector' => old('conector', 'AND'),
            'condiciones' => array_values(old('condiciones', $reglaVacia['condiciones'])),
            'puntuacion' => old('puntuacion', ''),
            'variablePuntuacion' => old('variablePuntuacion', ''),
            'prioridad' => old('prioridad', 1),
            'activa' => old('activa', '1'),
        ]
        : null;

    $umbralesFormulario = $errors->has('umbrales') || $errors->has('umbrales.*')
        ? array_values(old('umbrales', $umbrales))
        : $umbrales;

    $coloresNivel = ['bg-emerald-100 text-emerald-800', 'bg-amber-100 text-amber-800', 'bg-red-100 text-red-800', 'bg-rose-200 text-rose-900'];

    $resumenCondiciones = function ($regla) use ($operadores) {
        $conector = $regla->condiciones->first()?->conector === 'OR' ? ' o ' : ' y ';

        return $regla->condiciones
            ->map(function ($condicion) use ($operadores) {
                $unidad = RiskVariables::unidad($condicion->variable);
                $valor = $unidad === '%'
                    ? $condicion->parametro . '%'
                    : trim($condicion->parametro . ' ' . ($unidad === 'documentos' || $unidad === 'días' ? '' : $unidad));

                return RiskVariables::etiqueta($condicion->variable) . ' '
                    . ($operadores[$condicion->operador] ?? $condicion->operador) . ' ' . $valor;
            })
            ->implode($conector);
    };

    $hayFiltros = ($filtros['buscar'] ?? null) || ($filtros['estado'] ?? null);
@endphp

<body
    class="min-h-screen bg-slate-100 text-slate-800"
    x-data="{
        modal: {{ Js::from($reglaConError !== null) }},
        regla: {{ Js::from($reglaConError ?? $reglaVacia) }},
        vacia: {{ Js::from($reglaVacia) }},
        variables: {{ Js::from($variables) }},
        operadores: {{ Js::from($operadores) }},
        rutaCrear: {{ Js::from(route('coordinador.reglas.store')) }},
        rutaEditar: {{ Js::from(route('coordinador.reglas.update', ['idReglaRiesgo' => '__ID__'])) }},
        nueva() {
            this.regla = JSON.parse(JSON.stringify(this.vacia));
            this.modal = true;
        },
        editar(datos) {
            this.regla = JSON.parse(JSON.stringify(datos));
            this.modal = true;
        },
        agregarCondicion() {
            this.regla.condiciones.push({ variable: 'porcentaje_documental_completado', operador: '<', parametro: '' });
        },
        quitarCondicion(i) {
            if (this.regla.condiciones.length > 1) this.regla.condiciones.splice(i, 1);
        },
        unidad(variable) {
            return this.variables[variable]?.unidad ?? '';
        },
        resumen() {
            const union = this.regla.conector === 'OR' ? ' o ' : ' y ';
            const partes = this.regla.condiciones.map(c =>
                (this.variables[c.variable]?.etiqueta ?? '…').toLowerCase() + ' ' +
                (this.operadores[c.operador] ?? '…') + ' ' + (c.parametro === '' ? '…' : c.parametro) +
                (this.unidad(c.variable) === '%' ? '%' : ' ' + this.unidad(c.variable))
            );
            const puntos = this.regla.puntuacion === '' ? '…' : this.regla.puntuacion;
            const por = this.regla.variablePuntuacion
                ? ' por cada unidad de «' + (this.variables[this.regla.variablePuntuacion]?.etiqueta ?? '') + '»'
                : '';
            return 'Si ' + partes.join(union) + ', suma ' + puntos + ' puntos' + por + '.';
        }
    }"
    @keydown.escape.window="modal = false"
>
<div class="flex min-h-screen">
    <x-sidebarcoordinador />

    <div class="min-w-0 flex-1">
        {{-- Encabezado --}}
        <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
            <div class="text-sm font-semibold text-[#5b9eaa]">
                Bienvenido,
                <span class="font-bold text-slate-800">Coordinación Académica</span>
            </div>
            <p class="text-xs text-slate-500">Panel de gestión de prácticas profesionales</p>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6">

            <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Reglas parametrizables</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Cada regla que se cumple suma puntos. La suma de puntos define el nivel de riesgo de la pasantía.
                    </p>
                </div>

                <button
                    type="button"
                    @click="nueva()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#5b9eaa] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                >
                    <span class="text-lg leading-none">+</span>
                    Nueva regla
                </button>
            </section>

            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">No se pudieron guardar los cambios:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Niveles de riesgo (umbrales) --}}
            <section
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                x-data="{ umbrales: {{ Js::from($umbralesFormulario) }} }"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Niveles de riesgo</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Rango de puntos de cada nivel. Deben empezar en 0 e ir seguidos; el último no tiene límite.
                        </p>
                    </div>
                    @unless ($umbralesConfigurados)
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                            Sin guardar: las evaluaciones fallarán hasta que guardes los niveles
                        </span>
                    @endunless
                </div>

                <form method="POST" action="{{ route('coordinador.reglas.umbrales') }}" class="mt-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <template x-for="(umbral, i) in umbrales" :key="i">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <input
                                        type="text"
                                        :name="`umbrales[${i}][nivel]`"
                                        x-model="umbral.nivel"
                                        maxlength="20"
                                        required
                                        aria-label="Nombre del nivel"
                                        class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                                    >
                                    <button
                                        type="button"
                                        x-show="umbrales.length > 2"
                                        @click="umbrales.splice(i, 1)"
                                        class="text-lg font-bold leading-none text-slate-400 hover:text-red-600"
                                        aria-label="Quitar nivel"
                                    >&times;</button>
                                </div>

                                <div class="mt-3 flex items-center gap-2 text-sm">
                                    <label class="sr-only" :for="`umbral-desde-${i}`">Desde</label>
                                    <input
                                        type="number"
                                        :id="`umbral-desde-${i}`"
                                        :name="`umbrales[${i}][limiteInferior]`"
                                        x-model="umbral.limiteInferior"
                                        min="0" max="999.99" step="0.01" required
                                        class="w-20 rounded-md border border-slate-300 bg-white px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                                    >
                                    <span class="text-slate-500">a</span>
                                    <template x-if="i < umbrales.length - 1">
                                        <input
                                            type="number"
                                            :name="`umbrales[${i}][limiteSuperior]`"
                                            x-model="umbral.limiteSuperior"
                                            min="0" max="999.99" step="0.01" required
                                            aria-label="Hasta"
                                            class="w-20 rounded-md border border-slate-300 bg-white px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                                        >
                                    </template>
                                    <template x-if="i === umbrales.length - 1">
                                        <span class="whitespace-nowrap text-slate-600">en adelante</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <button
                            type="button"
                            x-show="umbrales.length < 6"
                            @click="umbrales.push({ nivel: '', limiteInferior: '', limiteSuperior: null })"
                            class="text-sm font-semibold text-[#397783] hover:underline"
                        >
                            + Agregar nivel
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                        >
                            Guardar niveles
                        </button>
                    </div>
                </form>
            </section>

            {{-- Reglas --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <form
                    method="GET"
                    action="{{ route('coordinador.reglas') }}"
                    class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/50 p-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <input
                        type="search"
                        name="buscar"
                        value="{{ $filtros['buscar'] ?? '' }}"
                        maxlength="100"
                        placeholder="Buscar regla..."
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] sm:w-80"
                    >

                    <div class="flex items-center gap-2">
                        <select
                            name="estado"
                            onchange="this.form.submit()"
                            aria-label="Filtrar por estado"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600"
                        >
                            <option value="">Todos los estados</option>
                            <option value="Activa" @selected(($filtros['estado'] ?? null) === 'Activa')>Activas</option>
                            <option value="Inactiva" @selected(($filtros['estado'] ?? null) === 'Inactiva')>Inactivas</option>
                        </select>

                        <select
                            name="orden"
                            onchange="this.form.submit()"
                            aria-label="Ordenar reglas"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600"
                        >
                            <option value="recientes" @selected($filtros['orden'] === 'recientes')>Más recientes</option>
                            <option value="prioridad" @selected($filtros['orden'] === 'prioridad')>Por prioridad</option>
                            <option value="nombre" @selected($filtros['orden'] === 'nombre')>Alfabético (A-Z)</option>
                        </select>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-3.5">ID</th>
                                <th class="px-6 py-3.5">Regla</th>
                                <th class="px-6 py-3.5">Se cumple cuando</th>
                                <th class="px-6 py-3.5">Puntos</th>
                                <th class="px-6 py-3.5">Prioridad</th>
                                <th class="px-6 py-3.5">Estado</th>
                                <th class="px-6 py-3.5 text-right">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse ($reglas as $regla)
                                @php
                                    $esGeneral = $regla->idCoordinador === null;
                                    $editable = ! $esGeneral && $regla->coordinador?->idCarrera === $idCarrera;
                                @endphp

                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-600">{{ $regla->codigo }}</td>

                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-800">
                                            {{ $regla->nombre }}
                                            @if ($esGeneral)
                                                <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-600">General</span>
                                            @endif
                                        </p>
                                        @if ($regla->descripcion)
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $regla->descripcion }}</p>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-slate-700">
                                        {{ $resumenCondiciones($regla) ?: 'Sin condiciones' }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-800">
                                        +{{ rtrim(rtrim((string) $regla->puntuacion, '0'), '.') }}
                                        @if ($regla->variablePuntuacion)
                                            <p class="text-xs font-normal text-slate-500">
                                                × {{ RiskVariables::etiqueta($regla->variablePuntuacion) }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">{{ $regla->prioridad }}</td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $regla->activa ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                            {{ $regla->activa ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        @if ($editable)
                                            <button
                                                type="button"
                                                @click="editar({{ Js::from([
                                                    'id' => $regla->idReglaRiesgo,
                                                    'nombre' => $regla->nombre,
                                                    'descripcion' => $regla->descripcion ?? '',
                                                    'conector' => $regla->condiciones->first()?->conector ?? 'AND',
                                                    'condiciones' => $regla->condiciones->map(fn ($c) => [
                                                        'variable' => $c->variable,
                                                        'operador' => $c->operador,
                                                        'parametro' => $c->parametro,
                                                    ])->values(),
                                                    'puntuacion' => (string) ($regla->puntuacion + 0),
                                                    'variablePuntuacion' => $regla->variablePuntuacion ?? '',
                                                    'prioridad' => $regla->prioridad,
                                                    'activa' => $regla->activa ? '1' : '0',
                                                ]) }})"
                                                class="font-medium text-amber-600 hover:text-amber-800 hover:underline"
                                            >
                                                Editar
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">
                                        @if ($hayFiltros)
                                            No se encontraron reglas con los criterios indicados.
                                        @else
                                            Todavía no hay reglas. Crea la primera con «Nueva regla».
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana (UAM)
            </footer>
        </main>
    </div>
</div>

{{-- Formulario para crear o editar una regla --}}
<div
    x-show="modal"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center"
>
    <div @click.outside="modal = false" class="w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-bold text-slate-800" x-text="regla.id ? 'Editar regla' : 'Nueva regla'"></h3>
            <button type="button" @click="modal = false" class="text-xl font-bold text-slate-400 hover:text-slate-600" aria-label="Cerrar ventana">&times;</button>
        </div>

        <form method="POST" :action="regla.id ? rutaEditar.replace('__ID__', regla.id) : rutaCrear">
            @csrf
            <template x-if="regla.id">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="_formulario" :value="regla.id ? 'editar' : 'crear'">
            <input type="hidden" name="idReglaRiesgo" :value="regla.id">

            <div class="space-y-5 p-6">
                {{-- Errores del formulario que se reabrió --}}
                @if ($formularioConError)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="nombre" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Nombre de la regla *</label>
                        <input id="nombre" type="text" name="nombre" x-model="regla.nombre" required maxlength="100"
                               placeholder="Ej. Documentación atrasada"
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="descripcion" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Descripción</label>
                        <textarea id="descripcion" name="descripcion" x-model="regla.descripcion" maxlength="255" rows="2"
                                  placeholder="Para qué sirve la regla"
                                  class="w-full resize-none rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"></textarea>
                    </div>
                </div>

                {{-- Condiciones --}}
                <fieldset class="min-w-0 rounded-xl border border-slate-200 p-4">
                    <legend class="px-1 text-xs font-bold uppercase tracking-wider text-slate-700">Condiciones *</legend>

                    <div class="mb-3 flex flex-wrap items-center gap-2 text-sm">
                        <label for="conector" class="text-slate-600">La regla se cumple si</label>
                        <select id="conector" name="conector" x-model="regla.conector"
                                class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm">
                            @foreach ($conectores as $valor => $texto)
                                <option value="{{ $valor }}">{{ mb_strtolower($texto) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(condicion, i) in regla.condiciones" :key="i">
                            <div class="flex flex-wrap items-center gap-2 rounded-lg bg-slate-50 p-2 sm:flex-nowrap">
                                <select :name="`condiciones[${i}][variable]`" x-model="condicion.variable" required
                                        aria-label="Variable"
                                        class="min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm">
                                    @foreach ($variables as $clave => $variable)
                                        <option value="{{ $clave }}">{{ $variable['etiqueta'] }}</option>
                                    @endforeach
                                </select>

                                <select :name="`condiciones[${i}][operador]`" x-model="condicion.operador" required
                                        aria-label="Operador"
                                        class="shrink-0 rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm">
                                    @foreach ($operadores as $simbolo => $texto)
                                        <option value="{{ $simbolo }}">{{ $texto }}</option>
                                    @endforeach
                                </select>

                                <input type="number" :name="`condiciones[${i}][parametro]`" x-model="condicion.parametro" required
                                       min="0" max="99999" :step="variables[condicion.variable]?.tipo === 'entero' ? 1 : 0.01"
                                       aria-label="Valor"
                                       class="w-24 rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm">

                                <span class="w-16 shrink-0 text-xs text-slate-500" x-text="unidad(condicion.variable)"></span>

                                <button type="button" @click="quitarCondicion(i)" x-show="regla.condiciones.length > 1"
                                        class="px-1 text-lg font-bold leading-none text-slate-400 hover:text-red-600"
                                        aria-label="Quitar condición">&times;</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="agregarCondicion()" x-show="regla.condiciones.length < 10"
                            class="mt-3 text-sm font-semibold text-[#397783] hover:underline">
                        + Agregar condición
                    </button>
                </fieldset>

                {{-- Puntuación, prioridad y estado --}}
                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <label for="puntuacion" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Puntos *</label>
                        <input id="puntuacion" type="number" name="puntuacion" x-model="regla.puntuacion" required
                               min="0" max="999.99" step="0.01" placeholder="Ej. 40"
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="variablePuntuacion" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Multiplicar puntos por</label>
                        <select id="variablePuntuacion" name="variablePuntuacion" x-model="regla.variablePuntuacion"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                            <option value="">No multiplicar (puntos fijos)</option>
                            @foreach ($variables as $clave => $variable)
                                <option value="{{ $clave }}">{{ $variable['etiqueta'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="prioridad" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Prioridad *</label>
                        <input id="prioridad" type="number" name="prioridad" x-model="regla.prioridad" required min="1" max="999" step="1"
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]">
                    </div>

                    <div>
                        <label for="activa" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Estado *</label>
                        <select id="activa" name="activa" x-model="regla.activa"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>

                <p class="rounded-lg bg-cyan-50 px-4 py-3 text-sm text-cyan-900" x-text="resumen()"></p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button type="button" @click="modal = false"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">Cancelar</button>
                <button type="submit"
                        class="rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                        x-text="regla.id ? 'Guardar cambios' : 'Guardar regla'"></button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
