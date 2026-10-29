<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas y riesgos | Coordinación</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    use App\Http\Controllers\Coordinador\AlertaRiesgoCoordinadorController as Alertas;

    $estilos = [
        'verde' => ['tarjeta' => 'bg-emerald-50 text-emerald-600', 'etiqueta' => 'bg-emerald-100 text-emerald-800'],
        'ambar' => ['tarjeta' => 'bg-amber-50 text-amber-500', 'etiqueta' => 'bg-amber-100 text-amber-800'],
        'rojo' => ['tarjeta' => 'bg-red-50 text-red-500', 'etiqueta' => 'bg-red-100 text-red-800'],
    ];

    $colorNivel = $niveles->pluck('color', 'nivel');

    $etiquetaNivel = function (?string $nivel) use ($colorNivel, $estilos) {
        return match ($nivel) {
            Alertas::SIN_EVALUAR => 'bg-slate-100 text-slate-600',
            Alertas::CON_ERROR => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
            default => $estilos[$colorNivel[$nivel] ?? '']['etiqueta'] ?? 'bg-slate-100 text-slate-700',
        };
    };

    $sinEvaluar = $conteos[Alertas::SIN_EVALUAR] ?? 0;
    $conError = $conteos[Alertas::CON_ERROR] ?? 0;
    $hayFiltros = $filtros['buscar'] !== '' || $filtros['nivel'] !== '';
@endphp

<body class="min-h-screen bg-slate-100 text-slate-800">
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
                    <h2 class="text-2xl font-bold text-slate-900">Alertas y niveles de riesgo</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Último nivel de riesgo de cada pasantía en progreso de tu carrera, según las reglas parametrizables.
                    </p>
                </div>

                <form method="POST" action="{{ route('coordinador.alertas.evaluar') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#5b9eaa] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12a9 9 0 1 1-3-6.7L21 8"/>
                            <path d="M21 3v5h-5"/>
                        </svg>
                        Evaluar pasantías ahora
                    </button>
                </form>
            </section>

            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('avisos'))
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="font-semibold">Algunas pasantías no se pudieron evaluar:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        @foreach (session('avisos') as $aviso)
                            <li>{{ $aviso }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Conteo por nivel --}}
            @if ($niveles->isEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Todavía no hay niveles de riesgo configurados. Defínelos en
                    <a href="{{ route('coordinador.reglas') }}" class="font-semibold underline">Reglas parametrizables</a>.
                </div>
            @else
                <section class="grid gap-4 sm:grid-cols-2 {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][$niveles->count()] ?? 'lg:grid-cols-4' }}">
                    @foreach ($niveles as $nivel)
                        @php
                            $cantidad = $conteos[$nivel['nivel']] ?? 0;
                            $activo = $filtros['nivel'] === $nivel['nivel'];
                        @endphp
                        <a href="{{ route('coordinador.alertas', $activo ? [] : ['nivel' => $nivel['nivel']]) }}"
                           class="flex items-center gap-4 rounded-xl border bg-white p-5 shadow-sm transition hover:shadow-md
                               {{ $activo ? 'border-[#5b9eaa] ring-2 ring-[#5b9eaa]/30' : 'border-slate-200' }}">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $estilos[$nivel['color']]['tarjeta'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                                    <path d="M6 20v-4"/>
                                    <path d="M12 20v-9"/>
                                    <path d="M18 20V5"/>
                                </svg>
                            </span>
                            <span>
                                <span class="block text-lg font-bold text-slate-800">{{ $nivel['nivel'] }}</span>
                                <span class="block text-2xl font-bold leading-tight text-slate-900">{{ $cantidad }}</span>
                                <span class="block text-xs text-slate-500">{{ $cantidad === 1 ? 'estudiante' : 'estudiantes' }}</span>
                            </span>
                        </a>
                    @endforeach
                </section>
            @endif

            @if ($sinEvaluar > 0 || $conError > 0)
                <p class="text-sm text-slate-500">
                    @if ($sinEvaluar > 0)
                        {{ $sinEvaluar === 1 ? '1 pasantía todavía no tiene' : "{$sinEvaluar} pasantías todavía no tienen" }}
                        evaluación.
                    @endif
                    @if ($conError > 0)
                        {{ $conError === 1 ? '1 pasantía tiene' : "{$conError} pasantías tienen" }}
                        su última evaluación con error de configuración.
                    @endif
                    Usa "Evaluar pasantías ahora" para actualizarlas.
                </p>
            @endif

            {{-- Tabla de pasantías --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <form method="GET" action="{{ route('coordinador.alertas') }}"
                      class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex w-full max-w-md gap-2">
                        <input type="search" name="buscar" value="{{ $filtros['buscar'] }}"
                               placeholder="Buscar por estudiante, empresa o carrera…"
                               aria-label="Buscar"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]">
                        <button type="submit"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            Buscar
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($hayFiltros)
                            <a href="{{ route('coordinador.alertas') }}" class="text-sm font-medium text-[#397783] hover:underline">Quitar filtros</a>
                        @endif
                        <select name="nivel" onchange="this.form.submit()" aria-label="Nivel de riesgo"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                            <option value="">Todos los niveles</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel['nivel'] }}" @selected($filtros['nivel'] === $nivel['nivel'])>{{ $nivel['nivel'] }}</option>
                            @endforeach
                            <option value="{{ Alertas::SIN_EVALUAR }}" @selected($filtros['nivel'] === Alertas::SIN_EVALUAR)>{{ Alertas::SIN_EVALUAR }}</option>
                            <option value="{{ Alertas::CON_ERROR }}" @selected($filtros['nivel'] === Alertas::CON_ERROR)>Con error</option>
                        </select>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-3.5">Estudiante</th>
                                <th class="px-6 py-3.5">Empresa</th>
                                <th class="px-6 py-3.5">Regla</th>
                                <th class="px-6 py-3.5">Nivel de riesgo</th>
                                <th class="px-6 py-3.5">Motivo</th>
                                <th class="px-6 py-3.5">Evaluado</th>
                                <th class="px-6 py-3.5 text-right">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse ($pasantias as $pasantia)
                                @php
                                    $estudiante = $pasantia->practicaProfesional?->estudiante;
                                    $nombre = trim(($estudiante?->usuario?->nombre ?? '') . ' ' . ($estudiante?->usuario?->apellido ?? ''));
                                    $evaluacion = $pasantia->ultimaEvaluacionRiesgo;
                                    $principal = $pasantia->detallePrincipal;
                                @endphp

                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#5b9eaa]/15 text-xs font-bold text-[#397783]">
                                                {{ mb_strtoupper(mb_substr($estudiante?->usuario?->nombre ?? '?', 0, 1) . mb_substr($estudiante?->usuario?->apellido ?? '', 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="font-medium text-slate-800">{{ $nombre !== '' ? $nombre : 'Sin nombre' }}</p>
                                                <p class="text-xs text-slate-500">{{ $estudiante?->credencial }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">{{ $pasantia->empresa ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-600">
                                        {{ $principal?->regla?->codigo ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $etiquetaNivel($pasantia->nivelActual) }}">
                                            {{ $pasantia->nivelActual }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">
                                        @if ($pasantia->nivelActual === Alertas::CON_ERROR)
                                            <span class="text-rose-700">Revisar la configuración de reglas</span>
                                        @elseif ($principal)
                                            {{ $principal->regla?->nombre ?? 'Regla eliminada' }}
                                        @elseif ($evaluacion)
                                            <span class="text-slate-500">Sin incidencias</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">
                                        {{ $evaluacion?->fechaEvaluacion?->format('d/m/Y H:i') ?? 'Nunca' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('coordinador.alertas.show', $pasantia->idPasantia) }}"
                                           class="inline-flex whitespace-nowrap rounded-lg border border-[#5b9eaa] px-3 py-1.5 text-xs font-semibold text-[#397783] hover:bg-[#5b9eaa]/10">
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                        @if ($hayFiltros)
                                            No hay pasantías que coincidan con la búsqueda.
                                        @else
                                            No hay pasantías en progreso en tu carrera.
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
</body>
</html>
