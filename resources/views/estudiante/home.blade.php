<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellido);

    // Colores de las etiquetas de alertas.
    $tonosAlerta = [
        'rojo' => [
            'icono' => 'bg-red-100 text-red-600',
            'etiqueta' => 'bg-red-100 text-red-700',
        ],
        'ambar' => [
            'icono' => 'bg-amber-100 text-amber-600',
            'etiqueta' => 'bg-amber-100 text-amber-800',
        ],
        'azul' => [
            'icono' => 'bg-sky-100 text-sky-600',
            'etiqueta' => 'bg-sky-100 text-sky-700',
        ],
    ];

    $coloresRiesgo = [
        'verde' => 'bg-emerald-100 text-emerald-700',
        'ambar' => 'bg-amber-100 text-amber-800',
        'rojo' => 'bg-red-100 text-red-700',
    ];

    // Estado de un documento: texto y colores de la etiqueta.
    $estadosDocumento = [
        'Aprobado' => ['texto' => 'Aprobado', 'clase' => 'bg-emerald-100 text-emerald-700'],
        'Pendiente' => ['texto' => 'En revisión', 'clase' => 'bg-amber-100 text-amber-800'],
        'A corregir' => ['texto' => 'A corregir', 'clase' => 'bg-orange-100 text-orange-700'],
        'Rechazado' => ['texto' => 'Rechazado', 'clase' => 'bg-red-100 text-red-700'],
    ];
@endphp

<body
    class="min-h-screen bg-slate-100 text-slate-800"
    x-data="{ openRegistroHoras: @js($pasantia !== null && $errors->hasAny(['fechaRegistro', 'horasIngresadas', 'observaciones'])) }"
    @keydown.escape.window="openRegistroHoras = false"
>

    <div class="flex min-h-screen">

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            {{-- Saludo y menú del usuario --}}
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold text-slate-800">
                        Hola, {{ $nombreCompleto }}
                    </h1>
                    <p class="text-xs text-slate-500">
                        Consulta el avance de tus prácticas profesionales y gestiona tus reportes
                    </p>
                </div>

                <div class="relative" x-data="{ abierto: false }" @click.outside="abierto = false">
                    <button
                        type="button"
                        @click="abierto = !abierto"
                        :aria-expanded="abierto.toString()"
                        aria-haspopup="true"
                        class="flex items-center gap-3 rounded-full py-1 pl-1 pr-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-slate-600" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0021.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 003.065 7.097A9.716 9.716 0 0012 21.75a9.716 9.716 0 006.685-2.653zm-12.54-1.285A7.486 7.486 0 0112 15a7.486 7.486 0 015.855 2.812A8.224 8.224 0 0112 20.25a8.224 8.224 0 01-5.855-2.438zM15.75 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="hidden sm:inline">{{ $nombreCompleto }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="abierto"
                        x-cloak
                        style="display: none;"
                        class="absolute right-0 z-40 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-lg"
                    >
                        <p class="border-b border-slate-100 px-4 py-2 text-xs text-slate-500">
                            {{ $usuario->correo }}
                        </p>
                        <a href="{{ route('estudiante.pasantias') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50">
                            Mis pasantías
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl space-y-5 px-6 py-6">

                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Fila 1: prácticas profesionales y alertas --}}
                <div class="grid gap-5 lg:grid-cols-12">

                    {{-- Prácticas profesionales del estudiante --}}
                    <section class="lg:col-span-7" aria-labelledby="titulo-practicas">
                        @if ($practicas->isNotEmpty())
                            <div
                                class="relative flex h-full min-h-[11rem] flex-col rounded-2xl bg-[#5b9eaa] p-6 text-white shadow-sm"
                                data-practica-inicial="{{ $indicePracticaActual }}"
                            >
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h2 id="titulo-practicas" class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                        Mis prácticas profesionales
                                    </h2>

                                    <div class="flex items-center gap-2">
                                        <span id="numero-practica" class="rounded-full bg-white/20 px-3 py-1 text-xs">
                                            Práctica {{ $indicePracticaActual + 1 }} de {{ $practicas->count() }}
                                        </span>

                                        @if ($practicas->count() > 1)
                                            <button
                                                type="button"
                                                id="practica-anterior"
                                                aria-label="Ver práctica anterior"
                                                class="flex h-8 w-8 items-center justify-center rounded-full border border-white/40 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white"
                                            >
                                                <span aria-hidden="true" class="text-xl leading-none">‹</span>
                                            </button>
                                            <button
                                                type="button"
                                                id="practica-siguiente"
                                                aria-label="Ver práctica siguiente"
                                                class="flex h-8 w-8 items-center justify-center rounded-full border border-white/40 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white"
                                            >
                                                <span aria-hidden="true" class="text-xl leading-none">›</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @foreach ($practicas as $indice => $practica)
                                    <article
                                        data-practica-slide
                                        data-indice="{{ $indice }}"
                                        @if ($indice !== $indicePracticaActual) hidden @endif
                                        class="flex-1"
                                    >
                                        <h3 class="text-2xl font-bold sm:text-3xl">
                                            {{ $practica->nombrePractica }}
                                        </h3>
                                        <p class="mt-2 text-white/90">
                                            Horas requeridas: {{ number_format((int) $practica->horasRequeridas) }}
                                        </p>
                                        <p class="mt-1 text-white/90">
                                            Estado: {{ $practica->estadoPractica }}
                                        </p>
                                    </article>
                                @endforeach

                                @if ($practicas->count() > 1)
                                    <div class="mt-4 flex justify-center gap-2" id="indicadores-practicas">
                                        @foreach ($practicas as $indice => $practica)
                                            <button
                                                type="button"
                                                data-practica-indicador="{{ $indice }}"
                                                aria-label="Ver práctica {{ $indice + 1 }}"
                                                aria-current="{{ $indice === $indicePracticaActual ? 'true' : 'false' }}"
                                                class="h-2 rounded-full transition-all {{ $indice === $indicePracticaActual ? 'w-6 bg-white' : 'w-2 bg-white/50' }}"
                                            ></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex h-full flex-col justify-center rounded-2xl border border-slate-200 bg-white p-6 text-center">
                                <h2 id="titulo-practicas" class="font-semibold text-slate-800">
                                    No hay prácticas registradas
                                </h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Comprueba que tu plan de estudios esté asignado correctamente.
                                </p>
                            </div>
                        @endif
                    </section>

                    {{-- Alertas del estudiante --}}
                    <section
                        class="flex max-h-72 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-5"
                        aria-labelledby="titulo-alertas"
                    >
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-600" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </span>
                                <h2 id="titulo-alertas" class="text-lg font-bold text-slate-800">
                                    Alertas
                                    @if ($alertas->isNotEmpty())
                                        <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                            {{ $alertas->count() }}
                                        </span>
                                    @endif
                                </h2>
                            </div>

                            @if ($riesgo)
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $coloresRiesgo[$riesgo['color']] }}">
                                    Riesgo {{ mb_strtolower($riesgo['nivel']) }}
                                </span>
                            @endif
                        </div>

                        <div class="-mr-2 flex-1 overflow-y-auto pr-2">
                            @forelse ($alertas as $alerta)
                                @php $tono = $tonosAlerta[$alerta['tono']]; @endphp

                                <div class="flex gap-3 border-b border-slate-200 py-3 last:border-b-0">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $tono['icono'] }}" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V7.5m0 0l-3.75 3.75M12 7.5l3.75 3.75M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5"/>
                                        </svg>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        @if ($alerta['enlace'])
                                            <a href="{{ $alerta['enlace'] }}" class="font-semibold text-slate-800 hover:text-[#5b9eaa] hover:underline">
                                                {{ $alerta['titulo'] }}
                                            </a>
                                        @else
                                            <p class="font-semibold text-slate-800">{{ $alerta['titulo'] }}</p>
                                        @endif

                                        @if ($alerta['detalle'])
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $alerta['detalle'] }}</p>
                                        @endif

                                        <span class="mt-1 inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $tono['etiqueta'] }}">
                                            {{ $alerta['etiqueta'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="flex h-full flex-col items-center justify-center py-6 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                    </span>
                                    <p class="mt-2 text-sm font-semibold text-slate-700">No tienes alertas</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $pasantia ? 'Tu pasantía está al día.' : 'Aparecerán aquí cuando inicies una pasantía.' }}
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                @if ($pasantia)

                    {{-- Fila 2: avance de la pasantía más reciente y horas --}}
                    <div class="grid gap-5 lg:grid-cols-12">

                        <section
                            class="flex flex-col justify-between rounded-2xl bg-[#5b9eaa] p-6 text-white shadow-sm lg:col-span-7"
                            aria-labelledby="titulo-avance"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h2 id="titulo-avance" class="text-xl font-bold">
                                        Tu avance de prácticas en {{ $pasantia->empresa }}
                                    </h2>
                                    <p class="mt-1 text-sm text-white/90">
                                        {{ number_format($avance['aprobadas']) }} de {{ number_format($avance['requeridas']) }} horas completadas
                                    </p>
                                    <p class="text-sm text-white/90">
                                        Estado: {{ $pasantia->estado }}.
                                    </p>

                                    <dl class="mt-3 space-y-0.5 text-xs text-white/80">
                                        <div>
                                            <dt class="inline">Práctica:</dt>
                                            <dd class="inline">{{ $pasantia->practicaProfesional?->nombrePractica ?? 'Sin práctica' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="inline">Cargo:</dt>
                                            <dd class="inline">{{ $pasantia->cargo }}</dd>
                                        </div>
                                        <div>
                                            <dt class="inline">Período:</dt>
                                            <dd class="inline">
                                                {{ $pasantia->fechaInicio?->format('d/m/Y') ?? 'Sin fecha' }}
                                                al
                                                {{ $pasantia->fechaFinEstimada?->format('d/m/Y') ?? 'sin fecha' }}
                                            </dd>
                                        </div>
                                        @if ($pasantia->tutor?->usuario)
                                            <div>
                                                <dt class="inline">Tutor:</dt>
                                                <dd class="inline">{{ trim($pasantia->tutor->usuario->nombre . ' ' . $pasantia->tutor->usuario->apellido) }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>

                                <div class="flex shrink-0 flex-col gap-2">
                                    <button
                                        type="button"
                                        @click="openRegistroHoras = true"
                                        class="flex items-center gap-2 rounded-lg bg-[#3f6f78] px-4 py-2 text-sm font-medium hover:bg-[#36616a] focus:outline-none focus:ring-2 focus:ring-white"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Agregar horas
                                    </button>
                                    <a
                                        href="{{ route('estudiante.documentos', ['pasantia' => $pasantia->idPasantia]) }}"
                                        class="flex items-center gap-2 rounded-lg border border-white/60 px-4 py-2 text-sm font-medium hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                        </svg>
                                        Ver documentos
                                    </a>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="mb-1 flex justify-end text-xs font-semibold">
                                    {{ $avance['porcentaje'] }}%
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-white/30"
                                    role="progressbar"
                                    aria-label="Avance de horas de la pasantía"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-valuenow="{{ $avance['porcentaje'] }}"
                                >
                                    <div class="h-full rounded-full bg-white" style="width: {{ $avance['porcentaje'] }}%"></div>
                                </div>
                            </div>
                        </section>

                        {{-- Horas por realizar y horas realizadas --}}
                        <div class="grid gap-5 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-1">
                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-500" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12M6 21h12M7 3v3a5 5 0 005 5 5 5 0 005-5V3M7 21v-3a5 5 0 015-5 5 5 0 015 5v3"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-slate-500">Horas por realizar</p>
                                    <p class="text-2xl font-bold text-slate-800">{{ number_format($avance['restantes']) }} h</p>
                                    <p class="text-xs text-slate-500">
                                        para completar
                                        @if ($avance['pendientes'] > 0)
                                            · {{ number_format($avance['pendientes']) }} h en revisión
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-slate-500">Horas realizadas</p>
                                    <p class="text-2xl font-bold text-slate-800">{{ number_format($avance['aprobadas']) }} h</p>
                                    <p class="text-xs text-slate-500">de {{ number_format($avance['requeridas']) }} h requeridas</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: estado documental de la pasantía --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="titulo-documentos">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#5b9eaa]/15 text-[#4c848f]" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 id="titulo-documentos" class="text-lg font-bold text-slate-800">Documentos enviados</h2>
                                    @if ($resumenDocumental['total'] > 0)
                                        <p class="text-xs text-slate-500">
                                            {{ $resumenDocumental['aprobados'] }} de {{ $resumenDocumental['total'] }} documentos requeridos aprobados
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <a
                                href="{{ route('estudiante.documentos', ['pasantia' => $pasantia->idPasantia]) }}"
                                class="rounded-xl bg-[#5b9eaa] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:ring-offset-2"
                            >
                                + Nuevo documento
                            </a>
                        </div>

                        {{-- Resumen del estado documental --}}
                        @if ($resumenDocumental['total'] > 0)
                            <div class="mb-4">
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                                    role="progressbar"
                                    aria-label="Documentos aprobados"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-valuenow="{{ $resumenDocumental['porcentaje'] }}"
                                >
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $resumenDocumental['porcentaje'] }}%"></div>
                                </div>

                                <ul class="mt-3 flex flex-wrap gap-2 text-xs">
                                    <li class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                                        Aprobados: {{ $resumenDocumental['aprobados'] }}
                                    </li>
                                    <li class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800">
                                        En revisión: {{ $resumenDocumental['enRevision'] }}
                                    </li>
                                    <li class="rounded-full bg-orange-50 px-3 py-1 font-medium text-orange-700">
                                        A corregir: {{ $resumenDocumental['aCorregir'] }}
                                    </li>
                                    <li class="rounded-full bg-red-50 px-3 py-1 font-medium text-red-700">
                                        Rechazados: {{ $resumenDocumental['rechazados'] }}
                                    </li>
                                    <li class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">
                                        Sin entregar: {{ $resumenDocumental['faltantes']->count() }}
                                    </li>
                                </ul>
                            </div>
                        @endif

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full min-w-[36rem] text-left text-sm">
                                <thead class="bg-slate-100 text-slate-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 font-semibold">Documento</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Fecha</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Estado</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($documentos as $documento)
                                        @php
                                            $estado = $estadosDocumento[$documento->estadoArchivo]
                                                ?? ['texto' => $documento->estadoArchivo, 'clase' => 'bg-slate-100 text-slate-600'];
                                            $fechaEnvio = $documento->ultimaVersion?->fechaEnvio;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3 text-slate-800">
                                                {{ $documento->formato?->nombreFormato ?? 'Documento' }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $fechaEnvio ? \Illuminate\Support\Carbon::parse($fechaEnvio)->format('d/m/Y') : 'Sin fecha' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-block rounded-md px-2 py-0.5 text-xs font-semibold {{ $estado['clase'] }}">
                                                    {{ $estado['texto'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-4 text-sm">
                                                    <a href="{{ route('estudiante.documentos.show', $documento->idDocumento) }}"
                                                       class="inline-flex items-center gap-1 text-slate-700 hover:text-[#5b9eaa]">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        Ver
                                                    </a>

                                                    @if ($gestionDocumental->permiteReenvio($documento))
                                                        <a href="{{ route('estudiante.documentos.show', $documento->idDocumento) }}"
                                                           class="inline-flex items-center gap-1 text-slate-700 hover:text-[#5b9eaa]">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                            </svg>
                                                            Reenviar
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                                                Aún no has enviado documentos para esta pasantía.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                @else

                    {{-- Sin pasantías todavía --}}
                    <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                        <h2 class="text-lg font-bold text-slate-800">Aún no tienes una pasantía</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Cuando Coordinación Académica apruebe tu carta, aquí verás tu avance de horas y tus documentos.
                        </p>
                        <a
                            href="{{ route('estudiante.aceptacion') }}"
                            class="mt-4 inline-block rounded-xl bg-[#5b9eaa] px-5 py-2 text-sm font-semibold text-white hover:bg-[#4c848f]"
                        >
                            Iniciar proceso de pasantía
                        </a>
                    </section>

                @endif

                <footer class="border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>

    </div>

    @if ($pasantia)
        @include('partials.modal-registro-horas', ['pasantia' => $pasantia])
    @endif

</body>
</html>
