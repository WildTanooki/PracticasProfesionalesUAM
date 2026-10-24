<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-xl font-bold">
                        Panel de Estudiante
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Sistema de Gestión de Prácticas Profesionales
                    </p>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-8">

                <section class="mb-8">
                    <p class="mt-1 text-slate-600">
                        Bienvenido/a, {{ auth()->user()->nombre }}.
                        Desde aquí podrás gestionar las prácticas profesionales.
                    </p>
                </section>
                
{{-- Sección de prácticas profesionales --}}
<section class="mt-6">

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                Mis prácticas profesionales
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Plan de estudios: {{ $plan->idPlanEstudios }}
                · {{ $practicas->count() }} prácticas registradas
            </p>
        </div>
    </div>

    @if ($practicas->isNotEmpty())

        <div class="relative rounded-2xl bg-[#5b9eaa] p-6 text-white shadow-sm">

            {{-- Encabezado del carrusel --}}
            <div class="mb-5 flex items-center justify-between gap-3">

                <span id="numero-practica"
                      class="rounded-full bg-white/20 px-3 py-1 text-sm">
                    Práctica 1 de {{ $practicas->count() }}
                </span>

                <div class="flex gap-2">

                    <button
                        type="button"
                        id="practica-anterior"
                        aria-label="Ver práctica anterior"
                        class="flex h-10 w-10 items-center justify-center rounded-full
                               border border-white/40 hover:bg-white/20
                               focus:outline-none focus:ring-2 focus:ring-white">
                        <span aria-hidden="true" class="text-2xl">‹</span>
                    </button>

                    <button
                        type="button"
                        id="practica-siguiente"
                        aria-label="Ver práctica siguiente"
                        class="flex h-10 w-10 items-center justify-center rounded-full
                               border border-white/40 hover:bg-white/20
                               focus:outline-none focus:ring-2 focus:ring-white">
                        <span aria-hidden="true" class="text-2xl">›</span>
                    </button>

                </div>
            </div>

            {{-- Tarjetas de prácticas --}}
            @foreach ($practicas as $indice => $practica)

                <article
                    data-practica-slide
                    data-indice="{{ $indice }}"
                    @if ($indice !== 0) hidden @endif
                    class="rounded-xl bg-white p-5 text-slate-800 shadow-sm"
                >

                    <div class="flex flex-col gap-4 sm:flex-row
                                sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-medium text-[#5b9eaa]">
                                Práctica profesional {{ $indice + 1 }}
                            </p>

                            <h3 class="mt-1 text-2xl font-bold">
                                {{ $practica->nombrePractica }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Horas requeridas:
                                <span class="font-semibold text-slate-700">
                                    {{ $practica->horasRequeridas }} h
                                </span>
                            </p>
                        </div>

                        <div class="sm:text-right">
                            <p class="mb-2 text-xs text-slate-500">
                                Estado de la práctica
                            </p>

                            <span class="inline-flex rounded-full
                                bg-amber-100 px-3 py-1 text-sm font-medium
                                text-amber-800">
                                {{ $practica->estadoPractica }}
                            </span>
                        </div>

                    </div>

                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <span class="text-sm text-slate-500">
                            Detalles próximamente
                        </span>
                    </div>

                </article>

            @endforeach

            {{-- Indicadores de posición --}}
            <div class="mt-5 flex justify-center gap-2"
                 id="indicadores-practicas">

                @foreach ($practicas as $indice => $practica)
                    <button
                        type="button"
                        data-practica-indicador="{{ $indice }}"
                        aria-label="Ver práctica {{ $indice + 1 }}"
                        aria-current="{{ $indice === 0 ? 'true' : 'false' }}"
                        class="h-2 rounded-full transition-all
                               {{ $indice === 0 ? 'w-6 bg-white' : 'w-2 bg-white/50' }}">
                    </button>
                @endforeach

            </div>

        </div>

    @else

        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
            <h3 class="font-semibold text-slate-800">
                No hay prácticas registradas
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                Comprueba que tu plan de estudios esté asignado correctamente.
            </p>
        </div>

    @endif

</section>

                        <section class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Mis prácticas</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                Consulta el estado de tus prácticas profesionales.
                                </p>
                            </div>
                            
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Registro de horas</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Consulta el avance de las horas requeridas.
                                </p>
                            </div>
                            
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Documentación</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Revisa el estado de tus documentos.
                                </p>
                            </div>
        </section>

                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>

    </div>

</body>
</html>