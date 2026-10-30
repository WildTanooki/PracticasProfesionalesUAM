
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard del Tutor | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral del tutor --}}
        <x-sidebartutor />

        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="border-b border-slate-200 bg-white px-6 py-4 shadow-sm sm:px-8">
                <span class="text-sm font-semibold text-[#5b9eaa]">
                    Panel
                    <span class="font-bold text-slate-800">de Tutor</span>
                </span>
            </header>

            <main class="mx-auto max-w-7xl px-5 py-6 sm:px-7">

                {{-- Título --}}
                <section class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        Pasantías asignadas
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Consulta el estado y el avance de horas de los estudiantes que tienes asignados.
                    </p>
                </section>

                {{-- Indicadores --}}
                <section class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2">

                    {{-- Total de pasantías --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Pasantías totales
                                </p>

                                <p class="mt-1 text-3xl font-bold text-slate-800">
                                    {{ $pasantiasTotales }}
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-6 w-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Pasantías en progreso --}}
                    <div class="rounded-xl border border-blue-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Pasantías en progreso
                                </p>

                                <p class="mt-1 text-3xl font-bold text-blue-700">
                                    {{ $pasantiasEnProgreso }}
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-6 w-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                </section>

                {{-- Listado de pasantías --}}
                <section>

                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-slate-800">
                            Mis estudiantes
                        </h2>

                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            {{ $pasantias->count() }}
                            {{ $pasantias->count() === 1 ? 'pasantía' : 'pasantías' }}
                        </span>
                    </div>

                    @if ($pasantias->isEmpty())

                        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center shadow-sm">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#5b9eaa]/10 text-[#5b9eaa]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-6 w-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>

                            <h3 class="mt-3 text-base font-bold text-slate-800">
                                No tienes pasantías asignadas
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Las pasantías asignadas aparecerán en esta sección.
                            </p>
                        </div>

                    @else

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                            @foreach ($pasantias as $pasantia)

                                @php
                                    $requeridas = max(
                                        0,
                                        (int) $pasantia->horasRequeridas
                                    );

                                    $acumuladas = max(
                                        0,
                                        (int) ($pasantia->total_horas_aprobadas ?? 0)
                                    );

                                    $porcentaje = $requeridas > 0
                                        ? min(100, (int) round(
                                            ($acumuladas / $requeridas) * 100
                                        ))
                                        : 0;

                                    $estado = $pasantia->estado ?: 'Sin estado';

                                    $estilosEstado = match ($estado) {
                                        'En progreso' =>
                                            'bg-blue-50 text-blue-700 ring-blue-200',

                                        'Culminada con requisitos',
                                        'Completada' =>
                                            'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                        'Culminada sin cumplir requisitos' =>
                                            'bg-amber-50 text-amber-700 ring-amber-200',

                                        'Pendiente' =>
                                            'bg-amber-50 text-amber-700 ring-amber-200',

                                        default =>
                                            'bg-slate-100 text-slate-600 ring-slate-200',
                                    };

                                    $colorBarra = match ($estado) {
                                        'Culminada con requisitos',
                                        'Completada' => 'bg-emerald-500',

                                        'Culminada sin cumplir requisitos' =>
                                            'bg-amber-500',

                                        default => 'bg-[#5b9eaa]',
                                    };
                                @endphp

                                <a
                                    href="{{ route('tutor.pasantias.show', $pasantia->idPasantia) }}"
                                    class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#5b9eaa] hover:shadow-md"
                                >

                                    {{-- Nombre de empresa y estado --}}
                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                                Empresa
                                            </p>

                                            <h3 class="mt-1 truncate text-base font-bold text-slate-800"
                                                title="{{ $pasantia->empresa ?: 'Empresa no especificada' }}">
                                                {{ $pasantia->empresa ?: 'Empresa no especificada' }}
                                            </h3>

                                            <p class="mt-1 truncate text-sm text-slate-600"
                                               title="{{ $pasantia->cargo ?: 'Cargo no especificado' }}">
                                                {{ $pasantia->cargo ?: 'Cargo no especificado' }}
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $estilosEstado }}">
                                            {{ $estado }}
                                        </span>

                                    </div>

                                    {{-- Separador --}}
                                    <div class="my-3 border-t border-slate-100"></div>

                                    {{-- Avance de horas --}}
                                    <div>
                                        <div class="mb-2 flex items-end justify-between gap-3">
                                            <div>
                                                <p class="text-xs text-slate-500">
                                                    Horas completadas
                                                </p>

                                                <p class="mt-0.5 text-lg font-bold text-slate-800">
                                                    {{ number_format($acumuladas) }}
                                                    <span class="text-xs font-medium text-slate-400">
                                                        / {{ number_format($requeridas) }} h
                                                    </span>
                                                </p>
                                            </div>

                                            <span class="text-sm font-bold text-[#5b9eaa]">
                                                {{ $porcentaje }}%
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                                            role="progressbar"
                                            aria-label="Avance de horas de la pasantía"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            aria-valuenow="{{ $porcentaje }}"
                                        >
                                            <div
                                                class="h-full rounded-full {{ $colorBarra }} transition-all duration-300"
                                                style="width: {{ $porcentaje }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    {{-- Acceso al detalle --}}
                                    <div class="mt-3 flex items-center justify-end gap-1 text-xs font-semibold text-[#438692]">
                                        Ver detalle
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-4 w-4"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>

                                </a>

                            @endforeach

                        </div>

                    @endif

                </section>

                <footer class="mt-8 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>
    </div>

</body>
</html>