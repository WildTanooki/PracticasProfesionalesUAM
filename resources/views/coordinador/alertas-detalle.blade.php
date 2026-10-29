<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del riesgo | Coordinación</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    use App\Services\RulesEngine\RiskEvaluator;
    use App\Services\RulesEngine\RiskVariables;

    $estudiante = $pasantia->practicaProfesional?->estudiante;
    $usuarioEstudiante = $estudiante?->usuario;
    $nombre = trim(($usuarioEstudiante?->nombre ?? '') . ' ' . ($usuarioEstudiante?->apellido ?? ''));
    $tutor = trim(($pasantia->tutor?->usuario?->nombre ?? '') . ' ' . ($pasantia->tutor?->usuario?->apellido ?? ''));

    $colorNivel = $niveles->pluck('color', 'nivel');

    $estilos = [
        'verde' => ['caja' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'icono' => 'bg-emerald-500', 'etiqueta' => 'bg-emerald-100 text-emerald-800'],
        'ambar' => ['caja' => 'border-amber-200 bg-amber-50 text-amber-800', 'icono' => 'bg-amber-500', 'etiqueta' => 'bg-amber-100 text-amber-800'],
        'rojo' => ['caja' => 'border-red-200 bg-red-50 text-red-800', 'icono' => 'bg-red-500', 'etiqueta' => 'bg-red-100 text-red-800'],
        'neutro' => ['caja' => 'border-slate-200 bg-white text-slate-700', 'icono' => 'bg-slate-400', 'etiqueta' => 'bg-slate-100 text-slate-700'],
    ];

    $estiloDe = fn (?string $nivel) => $estilos[$colorNivel[$nivel] ?? 'neutro'];

    $numero = fn ($valor) => rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');

    $correcta = $evaluacion?->estadoEvaluacion === RiskEvaluator::ESTADO_CORRECTA;
    $estiloActual = $correcta ? $estiloDe($evaluacion->nivelRiesgo) : $estilos['neutro'];
    $contexto = $evaluacion?->contextoEvaluado ?? [];

    $porcentaje = min(100, max(0, (float) ($contexto['porcentaje_documental_completado'] ?? 0)));
    $dias = $contexto['dias_restantes'] ?? null;
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
                    <a href="{{ route('coordinador.alertas') }}" class="text-sm font-semibold text-[#397783] hover:underline">
                        ← Volver a alertas
                    </a>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Detalle del riesgo</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Pasantía #{{ $pasantia->idPasantia }} ·
                        <a href="{{ route('coordinador.pasantias.documentos', $pasantia->idPasantia) }}" class="font-medium text-[#397783] hover:underline">
                            Ver documentos de la pasantía
                        </a>
                    </p>
                </div>

                @if ($pasantia->estado === 'En progreso')
                    <form method="POST" action="{{ route('coordinador.alertas.evaluar-pasantia', $pasantia->idPasantia) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-[#5b9eaa] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]">
                            Evaluar ahora
                        </button>
                    </form>
                @endif
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

            {{-- Estudiante, práctica y nivel --}}
            <section class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800">Datos del estudiante</h3>
                    <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 text-sm">
                        <dt class="text-slate-500">Nombre</dt>
                        <dd class="font-medium text-slate-800">{{ $nombre !== '' ? $nombre : 'Sin nombre' }}</dd>
                        <dt class="text-slate-500">Carnet</dt>
                        <dd class="text-slate-700">{{ $estudiante?->credencial ?? '—' }}</dd>
                        <dt class="text-slate-500">Carrera</dt>
                        <dd class="text-slate-700">{{ $estudiante?->planEstudios?->carrera?->nombre ?? '—' }}</dd>
                        <dt class="text-slate-500">Correo</dt>
                        <dd class="break-all text-slate-700">{{ $usuarioEstudiante?->correo ?? '—' }}</dd>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800">Datos de la práctica</h3>
                    <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 text-sm">
                        <dt class="text-slate-500">Empresa</dt>
                        <dd class="font-medium text-slate-800">{{ $pasantia->empresa ?: '—' }}</dd>
                        <dt class="text-slate-500">Cargo</dt>
                        <dd class="text-slate-700">{{ $pasantia->cargo ?: '—' }}</dd>
                        <dt class="text-slate-500">Tutor</dt>
                        <dd class="text-slate-700">{{ $tutor !== '' ? $tutor : 'Sin asignar' }}</dd>
                        <dt class="text-slate-500">Período</dt>
                        <dd class="text-slate-700">
                            {{ $pasantia->fechaInicio?->format('d/m/Y') ?? '—' }} – {{ $pasantia->fechaFinEstimada?->format('d/m/Y') ?? '—' }}
                        </dd>
                    </dl>
                </div>

                <div class="flex items-center gap-4 rounded-xl border p-5 shadow-sm {{ $estiloActual['caja'] }}">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-white {{ $estiloActual['icono'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z"/>
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                        </svg>
                    </span>
                    <div>
                        @if ($evaluacion === null)
                            <p class="text-lg font-bold">Sin evaluar</p>
                            <p class="text-sm">Esta pasantía todavía no tiene evaluación de riesgo.</p>
                        @elseif (! $correcta)
                            <p class="text-lg font-bold text-rose-700">Error de configuración</p>
                            <p class="text-sm">Evaluado el {{ $evaluacion->fechaEvaluacion?->format('d/m/Y H:i') }}</p>
                        @else
                            <p class="text-lg font-bold">Nivel de riesgo: {{ $evaluacion->nivelRiesgo }}</p>
                            <p class="text-sm">{{ $numero($evaluacion->puntuacionTotal) }} puntos</p>
                            <p class="text-sm">Evaluado el {{ $evaluacion->fechaEvaluacion?->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($evaluacion && ! $correcta)
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                    <p class="font-semibold">No se pudo calcular el riesgo con la configuración de ese momento:</p>
                    <p class="mt-1">{{ $evaluacion->errorConfiguracion }}</p>
                    <p class="mt-1">
                        Corrígelo en <a href="{{ route('coordinador.reglas') }}" class="font-semibold underline">Reglas parametrizables</a>
                        y vuelve a evaluar.
                    </p>
                </div>
            @endif

            @if ($correcta)
                <section class="grid gap-4 lg:grid-cols-5">
                    {{-- Reglas que se cumplieron --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-3">
                        <h3 class="font-bold text-slate-800">Reglas que se cumplieron</h3>

                        @forelse ($reglasCumplidas as $detalle)
                            @php
                                $regla = $detalle->regla;
                                $condiciones = $detalle->detalleCondiciones['condiciones'] ?? [];
                            @endphp
                            <article class="mt-4 rounded-lg border border-slate-200 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">{{ $regla?->codigo ?? 'Regla eliminada' }}</p>
                                        <p class="font-semibold text-slate-800">{{ $regla?->nombre }}</p>
                                        @if ($regla?->descripcion)
                                            <p class="mt-0.5 text-sm text-slate-500">{{ $regla->descripcion }}</p>
                                        @endif
                                    </div>
                                    <span class="whitespace-nowrap rounded-full bg-[#5b9eaa]/15 px-2.5 py-1 text-xs font-bold text-[#397783]">
                                        +{{ $numero($detalle->puntuacionAplicada) }} puntos
                                    </span>
                                </div>

                                <ul class="mt-3 space-y-1.5 text-sm">
                                    @foreach ($condiciones as $condicion)
                                        @php
                                            $unidad = RiskVariables::unidad($condicion['variable'] ?? null);
                                            $valorReal = $numero($condicion['valorReal'] ?? 0) . ($unidad === '%' ? '%' : '');
                                        @endphp
                                        <li class="flex items-start gap-2">
                                            @if ($condicion['cumplida'] ?? false)
                                                <span class="mt-0.5 font-bold text-emerald-600" aria-label="Se cumplió">✓</span>
                                            @else
                                                <span class="mt-0.5 font-bold text-slate-400" aria-label="No se cumplió">✗</span>
                                            @endif
                                            <span class="text-slate-700">
                                                {{ ucfirst(RiskVariables::frase($condicion['variable'] ?? '', $condicion['operador'] ?? '', $numero($condicion['parametro'] ?? 0))) }}
                                                <span class="text-slate-500">(valor: {{ $valorReal }})</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($regla && ! $regla->activa)
                                    <p class="mt-3 text-xs text-slate-500">Esta regla está desactivada desde entonces.</p>
                                @endif
                            </article>
                        @empty
                            <p class="mt-3 text-sm text-slate-500">
                                Ninguna regla se cumplió en esta evaluación.
                            </p>
                        @endforelse
                    </div>

                    <div class="space-y-4 lg:col-span-2">
                        {{-- Motivo --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-bold text-slate-800">Motivo de la alerta</h3>
                            <p class="mt-1 text-sm text-slate-500">Situación de la pasantía al momento de evaluarla.</p>

                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-slate-600">Documentos aprobados</span>
                                <span class="font-semibold text-slate-800">
                                    {{ $contexto['documentos_aprobados'] ?? 0 }} / {{ $contexto['documentos_obligatorios'] ?? 0 }}
                                    · {{ $numero($porcentaje) }}%
                                </span>
                            </div>
                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $estiloActual['icono'] }}" style="width: {{ $porcentaje }}%"></div>
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <dt class="text-slate-500">Sin entregar</dt>
                                    <dd class="text-lg font-bold text-slate-800">{{ $contexto['documentos_no_entregados'] ?? 0 }}</dd>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <dt class="text-slate-500">Pendientes de revisión</dt>
                                    <dd class="text-lg font-bold text-slate-800">{{ $contexto['documentos_pendientes'] ?? 0 }}</dd>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <dt class="text-slate-500">A corregir o rechazados</dt>
                                    <dd class="text-lg font-bold text-slate-800">
                                        {{ ($contexto['documentos_a_corregir'] ?? 0) + ($contexto['documentos_rechazados'] ?? 0) }}
                                    </dd>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <dt class="text-slate-500">Días para la fecha final</dt>
                                    <dd class="text-lg font-bold {{ $dias !== null && $dias < 0 ? 'text-red-700' : 'text-slate-800' }}">
                                        {{ $dias ?? '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Recomendación --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-bold text-slate-800">Recomendación</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                                @foreach ($recomendaciones as $recomendacion)
                                    <li>{{ $recomendacion }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Historial --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="font-bold text-slate-800">Historial de evaluaciones</h3>
                    <p class="mt-1 text-sm text-slate-500">Cada evaluación guarda las reglas y niveles que estaban vigentes en ese momento.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Nivel</th>
                                <th class="px-6 py-3">Puntos</th>
                                <th class="px-6 py-3">Reglas cumplidas</th>
                                <th class="px-6 py-3">Versión de configuración</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse ($evaluaciones as $fila)
                                @php $filaCorrecta = $fila->estadoEvaluacion === RiskEvaluator::ESTADO_CORRECTA; @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-3 text-slate-700">{{ $fila->fechaEvaluacion?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        @if ($filaCorrecta)
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $estiloDe($fila->nivelRiesgo)['etiqueta'] }}">
                                                {{ $fila->nivelRiesgo }}
                                            </span>
                                        @else
                                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">Error</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-slate-700">{{ $filaCorrecta ? $numero($fila->puntuacionTotal) : '—' }}</td>
                                    <td class="px-6 py-3 text-slate-700">
                                        {{ $filaCorrecta ? $fila->detalles->where('cumplida', true)->count() . ' de ' . $fila->detalles->count() : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-slate-500">{{ $fila->versionConfiguracion }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">
                                        Todavía no hay evaluaciones para esta pasantía.
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
