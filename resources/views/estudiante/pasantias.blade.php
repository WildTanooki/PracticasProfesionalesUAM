
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis Pasantías | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
                <div>
                    <span class="text-sm font-semibold text-[#5b9eaa]">
                        Panel
                        <span class="font-bold text-slate-800">
                            de Estudiante
                        </span>
                    </span>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-6">

                {{-- Sección de pasantías --}}
                <section class="mt-3">

                    {{-- Encabezado de la sección --}}
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">
                                Mis Pasantías
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Consulta el estado y el avance de las horas de tus pasantías.
                            </p>
                        </div>

                        <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200">
                            {{ $pasantias->count() }}
                            {{ $pasantias->count() === 1 ? 'pasantía' : 'pasantías' }}
                        </span>
                    </div>

                    {{-- Si no hay pasantías --}}
                    @if ($pasantias->isEmpty())

                        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">

                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#5b9eaa]/10 text-[#5b9eaa]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-7 w-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>

                            <h2 class="mt-4 text-lg font-bold text-slate-800">
                                No tienes pasantías registradas
                            </h2>

                            <p class="mt-2 text-sm text-slate-500">
                                Cuando tengas una pasantía registrada, aparecerá en esta sección.
                            </p>

                        </div>

                    @else

                        {{-- Tarjetas de pasantías --}}
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                            @foreach ($pasantias as $pasantia)

                                @php
                                    $requeridas = max(0, (int) $pasantia->horasRequeridas);
                                    $acumuladas = max(0, (int) ($pasantia->total_horas_aprobadas ?? 0));

                                    $porcentaje = $requeridas > 0
                                        ? min(100, (int) round(($acumuladas / $requeridas) * 100))
                                        : 0;

                                    $estado = $pasantia->estado;

                                    $estilosEstado = match ($estado) {
                                        'En progreso' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                        'Completada' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                    };

                                    $colorBarra = match ($estado) {
                                        'Completada' => 'bg-emerald-500',
                                        default => 'bg-[#5b9eaa]',
                                    };
                                @endphp

                                <a
                                href="{{ route('estudiante.pasantias.show', $pasantia->idPasantia) }}"
                                class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm 
                                transition duration-200 hover:-translate-y-0.5 hover:border-[#5b9eaa] 
                                hover:shadow-md"
                                >
                                    {{-- Empresa, cargo y estado --}}
                                    <div class="flex items-start justify-between gap-4">

                                        <div class="min-w-0">

                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                                Empresa
                                            </p>

                                            <h2 class="mt-1 break-words text-xl font-bold text-slate-800">
                                                {{ $pasantia->empresa ?: 'Empresa no especificada' }}
                                            </h2>

                                            <div class="mt-3 flex items-start gap-2 text-sm text-slate-600">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="mt-0.5 h-4 w-4 shrink-0 text-[#5b9eaa]"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>

                                                <span>
                                                    <span class="font-semibold text-slate-700">
                                                        Cargo:
                                                    </span>

                                                    {{ $pasantia->cargo ?: 'No especificado' }}
                                                </span>

                                            </div>

                                        </div>

                                        <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $estilosEstado }}">
                                            {{ $estado ?: 'Sin estado' }}
                                        </span>

                                    </div>

                                    {{-- Separador --}}
                                    <div class="my-5 border-t border-slate-100"></div>

                                    {{-- Avance de horas --}}
                                    <div>

                                        <div class="flex items-end justify-between gap-3">

                                            <div>
                                                <p class="text-sm font-medium text-slate-500">
                                                    Horas acumuladas
                                                </p>

                                                <p class="mt-1 text-2xl font-bold text-slate-800">
                                                    {{ number_format($acumuladas) }}
                                                    <span class="text-sm font-medium text-slate-400">
                                                        / {{ number_format($requeridas) }} h
                                                    </span>
                                                </p>
                                            </div>

                                            <span class="text-lg font-bold text-[#5b9eaa]">
                                                {{ $porcentaje }}%
                                            </span>

                                        </div>

                                        {{-- Barra de progreso --}}
                                        <div
                                            class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100"
                                            role="progressbar"
                                            aria-label="Porcentaje de avance de horas"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            aria-valuenow="{{ $porcentaje }}"
                                        >
                                            <div
                                                class="h-full rounded-full transition-all duration-500 {{ $colorBarra }}"
                                                style="width: {{ $porcentaje }}%"
                                            ></div>
                                        </div>

                                        {{-- Mensaje de avance --}}
                                        <p class="mt-3 text-xs leading-5 text-slate-500">

                                            @if ($requeridas === 0)

                                                No se han definido las horas requeridas.

                                            @elseif ($acumuladas >= $requeridas)

                                                Has alcanzado las horas requeridas.

                                            @else

                                                Te faltan
                                                <span class="font-semibold text-slate-700">
                                                    {{ number_format($requeridas - $acumuladas) }} horas
                                                </span>
                                                para completar el total requerido.

                                            @endif

                                        </p>

                                    </div>
                                </a>

                            @endforeach

                        </div>

                    @endif

                </section>

                {{-- Pie de página --}}
                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>

        </div>

    </div>

</body>
</html>
