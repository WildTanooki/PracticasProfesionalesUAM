
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle de Pasantía | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen bg-slate-100 text-slate-800"
    x-data="{
        openRegistrarHoras: @js(
            $errors->hasAny(['fechaRegistro', 'horasIngresadas', 'observaciones'])
            && (string) old('idPasantia') === (string) $pasantia->idPasantia
        ),
        openAprobarHoras: false,
        registrosSeleccionados: [],
        openEditarRegistro: false,
        editRegistro: {
            id: null,
            estado: 'Aprobada',
            observaciones: ''
        }
    }"
    @keydown.escape.window="
        openRegistrarHoras = false;
        openAprobarHoras = false;
        openEditarRegistro = false;
    "
>
    @php
        $estado = $pasantia->estado;

        $estilosEstado = match ($estado) {
            'En progreso' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'Culminada con requisitos', 'Completada' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Culminada sin requisitos' => 'bg-red-50 text-red-700 ring-red-200',
            'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };

        $requeridas = max(0, (int) $pasantia->horasRequeridas);

        // Solo se consideran horas aprobadas.
        $acumuladas = max(
            0,
            (int) $pasantia->registrosDeHoras
                ->where('estadoHoras', 'Aprobada')
                ->sum('horasIngresadas')
        );

        $porcentaje = $requeridas > 0
            ? min(100, (int) round(($acumuladas / $requeridas) * 100))
            : 0;

        $pendientes = max(0, $requeridas - $acumuladas);

        $registrosPendientes = $pasantia->registrosDeHoras
            ->where('estadoHoras', 'Pendiente')
            ->sortBy('fechaRegistro')
            ->values();

        $registrosOrdenados = $pasantia->registrosDeHoras
            ->sortByDesc('fechaRegistro')
            ->values();

        $idsPendientes = $registrosPendientes
            ->pluck('idRegistro')
            ->map(fn ($id) => (string) $id)
            ->values();

        $totalHorasPendientesRevision = (int) $registrosPendientes
            ->sum('horasIngresadas');

        $fechaMinima = $pasantia->fechaInicio?->format('Y-m-d');

        $fechaMaxima = min(
            now()->toDateString(),
            $pasantia->fechaFinEstimada?->format('Y-m-d')
                ?? now()->toDateString()
        );
    @endphp

    <div class="flex min-h-screen">

        {{-- Barra lateral del tutor --}}
        <x-sidebartutor />

        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 shadow-sm sm:px-8">
                <div>
                    <p class="text-sm font-semibold text-[#5b9eaa]">
                        Panel
                        <span class="font-bold text-slate-800">
                            de Tutor
                        </span>
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Gestión y revisión de horas de prácticas profesionales
                    </p>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                {{-- Regresar --}}
                <a
                    href="{{ route('tutor.home') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#5b9eaa]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a mis pasantías
                </a>

                {{-- Mensaje de éxito --}}
                @if (session('success'))
                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Mensajes generales de error --}}
                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">
                            Revisa la información enviada:
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Información principal --}}
                <section class="mt-5 rounded-2xl bg-[#5b9eaa] p-5 text-white shadow-sm sm:p-7">

                    <div class="flex flex-wrap items-start justify-between gap-4">

                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white/80">
                                Detalle de pasantía
                            </p>

                            <h1 class="mt-2 break-words text-2xl font-bold sm:text-3xl">
                                {{ $pasantia->empresa ?: 'Empresa no especificada' }}
                            </h1>

                            <p class="mt-2 text-sm text-white/90">
                                Cargo:
                                {{ $pasantia->cargo ?: 'No especificado' }}
                            </p>

                            @if ($pasantia->fechaInicio || $pasantia->fechaFinEstimada)
                                <p class="mt-2 text-xs text-white/80">
                                    {{ $pasantia->fechaInicio?->format('d/m/Y') ?? 'Sin fecha de inicio' }}
                                    –
                                    {{ $pasantia->fechaFinEstimada?->format('d/m/Y') ?? 'Sin fecha de finalización' }}
                                </p>
                            @endif
                        </div>

                        <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-white/50">
                            {{ $estado ?: 'Sin estado' }}
                        </span>

                    </div>

                    {{-- Indicadores --}}
                    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <div class="rounded-xl bg-white/15 p-4">
                            <p class="text-sm text-white/80">
                                Horas aprobadas
                            </p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ number_format($acumuladas) }} h
                            </p>
                        </div>

                        <div class="rounded-xl bg-white/15 p-4">
                            <p class="text-sm text-white/80">
                                Horas requeridas
                            </p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ number_format($requeridas) }} h
                            </p>
                        </div>

                        <div class="rounded-xl bg-white/15 p-4">
                            <p class="text-sm text-white/80">
                                Horas pendientes de completar
                            </p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ number_format($pendientes) }} h
                            </p>
                        </div>

                    </div>

                    {{-- Barra de progreso --}}
                    <div class="mt-6">

                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium">
                                Avance de horas aprobadas
                            </span>

                            <span class="font-bold">
                                {{ $porcentaje }}%
                            </span>
                        </div>

                        <div
                            class="h-3 w-full overflow-hidden rounded-full bg-black/15"
                            role="progressbar"
                            aria-label="Porcentaje de horas aprobadas"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $porcentaje }}"
                        >
                            <div
                                class="h-full rounded-full bg-white transition-all duration-500"
                                style="width: {{ $porcentaje }}%"
                            ></div>
                        </div>

                        <p class="mt-2 text-xs text-white/80">
                            @if ($requeridas === 0)
                                No se han definido las horas requeridas.
                            @elseif ($acumuladas >= $requeridas)
                                Se alcanzó el total de horas requeridas.
                            @else
                                Faltan {{ number_format($pendientes) }} horas aprobadas para completar el requisito.
                            @endif
                        </p>

                    </div>

                </section>

                {{-- Información adicional --}}
                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="text-lg font-bold text-slate-800">
                        Información de la pasantía
                    </h2>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <p class="text-sm text-slate-500">
                                Fecha de inicio
                            </p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $pasantia->fechaInicio?->format('d/m/Y') ?? 'No registrada' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Fecha estimada de finalización
                            </p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $pasantia->fechaFinEstimada?->format('d/m/Y') ?? 'No registrada' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Total de registros
                            </p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $pasantia->registrosDeHoras->count() }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Registros pendientes de revisión
                            </p>
                            <p class="mt-1 font-semibold text-amber-700">
                                {{ $registrosPendientes->count() }}
                            </p>
                        </div>

                    </div>

                </section>

                {{-- Registro de horas --}}
                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    {{-- Encabezado de la tabla --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">

                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                Registro de horas
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Administra los registros y revisa las horas reportadas por el estudiante.
                            </p>
                        </div>

                        @if ($pasantia->estado === 'En progreso')
                            <div class="flex flex-wrap gap-2">

                                <button
                                    type="button"
                                    @click="openRegistrarHoras = true"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[#5b9eaa] px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4c848f]"
                                >
                                    <span class="text-lg leading-none">+</span>
                                    Registrar horas
                                </button>

                                <button
                                    type="button"
                                    @click="openAprobarHoras = true"
                                    @disabled($registrosPendientes->isEmpty())
                                    class="inline-flex items-center gap-2 rounded-lg border border-[#5b9eaa] px-3 py-2 text-sm font-semibold text-[#438692] transition hover:bg-[#5b9eaa]/10 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Revisar pendientes
                                    <span class="rounded-full bg-[#5b9eaa]/10 px-2 py-0.5 text-xs">
                                        {{ $registrosPendientes->count() }}
                                    </span>
                                </button>

                            </div>
                        @endif

                    </div>

                    {{-- Resumen de pendientes --}}
                    @if ($registrosPendientes->isNotEmpty())
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">

                            <div>
                                <p class="text-sm font-semibold text-amber-900">
                                    Hay registros pendientes de revisión
                                </p>
                                <p class="mt-1 text-xs text-amber-800">
                                    {{ $registrosPendientes->count() }}
                                    {{ $registrosPendientes->count() === 1 ? 'registro' : 'registros' }}
                                    por revisar.
                                </p>
                            </div>

                            <p class="text-sm font-bold text-amber-900">
                                {{ number_format($totalHorasPendientesRevision) }} h reportadas
                            </p>

                        </div>
                    @endif

                    {{-- Tabla --}}
                    @if ($registrosOrdenados->isEmpty())

                        <div class="mt-5 rounded-xl border border-dashed border-slate-300 px-5 py-10 text-center">

                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-6 w-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M12 7v5l3 2"/>
                                </svg>
                            </div>

                            <p class="mt-3 font-semibold text-slate-700">
                                No hay registros de horas
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Los registros de esta pasantía aparecerán aquí.
                            </p>

                        </div>

                    @else

                        <div class="mt-5 overflow-x-auto">

                            <table class="w-full min-w-[650px] text-left text-sm">

                                <thead>
                                    <tr class="bg-slate-100 text-slate-600">
                                        <th class="rounded-l-lg px-4 py-3 font-semibold">
                                            Horas realizadas
                                        </th>
                                        <th class="px-4 py-3 font-semibold">
                                            Fecha
                                        </th>
                                        <th class="px-4 py-3 font-semibold">
                                            Observaciones
                                        </th>
                                        <th class="rounded-r-lg px-4 py-3 font-semibold">
                                            Estado
                                        </th>
                                        <th class="rounded-r-lg px-4 py-3 font-semibold">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">

                                    @foreach ($registrosOrdenados as $registro)

                                        @php
                                            $estiloHoras = match ($registro->estadoHoras) {
                                                'Aprobada' => 'bg-emerald-50 text-emerald-700',
                                                'Pendiente' => 'bg-amber-50 text-amber-700',
                                                'Rechazada' => 'bg-red-50 text-red-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp

                                        <tr class="align-top transition hover:bg-slate-50">

                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">
                                                {{ number_format((int) $registro->horasIngresadas) }} h
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                                {{ $registro->fechaRegistro?->format('d/m/Y') ?? 'No registrada' }}
                                            </td>

                                            <td class="max-w-[280px] px-4 py-3 text-slate-600">
                                                {{ $registro->observaciones ?: 'Sin observaciones' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloHoras }}">
                                                    {{ $registro->estadoHoras ?: 'Sin estado' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    @click="
                                                        editRegistro = {
                                                            id: {{ $registro->idRegistro }},
                                                            estado: @js($registro->estadoHoras),
                                                            observaciones: @js($registro->observaciones ?? '')
                                                        };
                                                        openEditarRegistro = true;
                                                    "
                                                    class="rounded-lg border border-slate-200 px-3 py-1.5
                                                           text-xs font-semibold text-slate-700
                                                           transition hover:border-[#5b9eaa]
                                                           hover:text-[#5b9eaa]"
                                                >
                                                    Editar
                                                </button>
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

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

    {{-- ====================================================== --}}
    {{-- MODAL: REGISTRAR HORAS --}}
    {{-- ====================================================== --}}
    <div
        x-cloak
        x-show="openRegistrarHoras"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="openRegistrarHoras = false"
    >
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">

                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        Registrar horas
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Las horas registradas por el tutor quedan aprobadas.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openRegistrarHoras = false"
                    class="text-2xl font-bold text-slate-400 hover:text-slate-700"
                    aria-label="Cerrar ventana"
                >&times;</button>

            </div>

            <form
                action="{{ route('tutor.registro-horas.store') }}"
                method="POST"
            >
                @csrf

                <input
                    type="hidden"
                    name="idPasantia"
                    value="{{ $pasantia->idPasantia }}"
                >

                <div class="space-y-4 p-6">

                    <div>
                        <label
                            for="fechaRegistroTutor"
                            class="mb-1 block text-sm font-semibold text-slate-700"
                        >
                            Fecha de las horas *
                        </label>

                        <input
                            type="date"
                            id="fechaRegistroTutor"
                            name="fechaRegistro"
                            value="{{ old('fechaRegistro') }}"
                            min="{{ $fechaMinima }}"
                            max="{{ $fechaMaxima }}"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >

                        @error('fechaRegistro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="horasIngresadasTutor"
                            class="mb-1 block text-sm font-semibold text-slate-700"
                        >
                            Horas realizadas *
                        </label>

                        <input
                            type="number"
                            id="horasIngresadasTutor"
                            name="horasIngresadas"
                            value="{{ old('horasIngresadas') }}"
                            min="1"
                            step="1"
                            required
                            placeholder="Ej. 8"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >

                        @error('horasIngresadas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="observacionesTutor"
                            class="mb-1 block text-sm font-semibold text-slate-700"
                        >
                            Observaciones (opcional)
                        </label>

                        <textarea
                            id="observacionesTutor"
                            name="observaciones"
                            rows="3"
                            maxlength="200"
                            placeholder="Describe brevemente las actividades realizadas."
                            class="w-full resize-y rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >{{ old('observaciones') }}</textarea>

                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <p class="mt-1 text-xs text-slate-400">
                            Máximo 200 caracteres.
                        </p>
                    </div>

                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                        <p class="text-sm font-semibold text-emerald-800">
                            Estado: Aprobada
                        </p>
                        <p class="mt-1 text-xs leading-5 text-emerald-700">
                            Las horas se sumarán al avance de la pasantía al guardar el registro.
                        </p>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">

                    <button
                        type="button"
                        @click="openRegistrarHoras = false"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                    >
                        Registrar y aprobar
                    </button>

                </div>
            </form>
        </div>
    </div>

    {{-- ====================================================== --}}
    {{-- MODAL: APROBAR VARIOS REGISTROS --}}
    {{-- ====================================================== --}}
    <div
        x-cloak
        x-show="openAprobarHoras"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="openAprobarHoras = false"
    >
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">

                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        Aprobar horas pendientes
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Selecciona uno o varios registros para aprobarlos.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openAprobarHoras = false"
                    class="text-2xl font-bold text-slate-400 hover:text-slate-700"
                    aria-label="Cerrar ventana"
                >&times;</button>

            </div>

            <form
                action="{{ route('tutor.registro-horas.aprobar-multiples', $pasantia->idPasantia) }}"
                method="POST"
            >
                @csrf

                <div class="p-5 sm:p-6">

                    {{-- Controles de selección --}}
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Registros pendientes
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Seleccionados:
                                <span
                                    class="font-bold text-[#438692]"
                                    x-text="registrosSeleccionados.length"
                                ></span>
                                de {{ $registrosPendientes->count() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">

                            <button
                                type="button"
                                @click="registrosSeleccionados = @js($idsPendientes)"
                                class="rounded-lg border border-[#5b9eaa] px-3 py-2 text-xs font-semibold text-[#438692] transition hover:bg-[#5b9eaa]/10"
                            >
                                Escoger todas
                            </button>

                            <button
                                type="button"
                                @click="registrosSeleccionados = []"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                            >
                                Desmarcar todas
                            </button>

                        </div>
                    </div>

                    {{-- Tabla de selección --}}
                    <div class="max-h-80 overflow-auto rounded-xl border border-slate-200">

                        <table class="w-full min-w-[650px] text-left text-sm">

                            <thead class="sticky top-0 z-10 bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">
                                        Seleccionar
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Horas
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Fecha
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Observaciones
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Estado
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">

                                @forelse ($registrosPendientes as $registro)

                                    <tr class="transition hover:bg-slate-50">

                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                name="registros[]"
                                                value="{{ $registro->idRegistro }}"
                                                x-model="registrosSeleccionados"
                                                class="h-4 w-4 rounded border-slate-300 text-[#5b9eaa] focus:ring-[#5b9eaa]"
                                                aria-label="Seleccionar registro {{ $registro->idRegistro }}"
                                            >
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">
                                            {{ number_format((int) $registro->horasIngresadas) }} h
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                            {{ $registro->fechaRegistro?->format('d/m/Y') ?? 'Sin fecha' }}
                                        </td>

                                        <td class="max-w-[240px] px-4 py-3 text-slate-600">
                                            {{ $registro->observaciones ?: 'Sin observaciones' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Pendiente
                                            </span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center">
                                            <p class="font-semibold text-slate-700">
                                                No hay registros pendientes
                                            </p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                Todos los registros ya fueron revisados.
                                            </p>
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- Aviso --}}
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <p class="text-sm font-semibold text-amber-900">
                            Confirma la selección antes de continuar.
                        </p>
                        <p class="mt-1 text-xs leading-5 text-amber-800">
                            Solo los registros que sigan en estado Pendiente y pertenezcan a esta pasantía serán aprobados. Los registros no seleccionados no cambiarán.
                        </p>
                    </div>

                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">

                    <button
                        type="button"
                        @click="openAprobarHoras = false"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        :disabled="registrosSeleccionados.length === 0"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Aprobar seleccionadas
                    </button>

                </div>
            </form>
        </div>
    </div>

        {{-- Modal para editar un registro de horas --}}
        <div
            x-show="openEditarRegistro"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center
                   overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
            @click.self="openEditarRegistro = false"
            @keydown.escape.window="openEditarRegistro = false"
        >
            <div class="w-full max-w-lg overflow-hidden rounded-2xl
                        border border-slate-200 bg-white shadow-xl">

                <div class="flex items-center justify-between
                            border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            Editar registro de horas
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Modifica el estado o las observaciones.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openEditarRegistro = false"
                        class="text-xl font-bold text-slate-400 hover:text-slate-600"
                        aria-label="Cerrar ventana"
                    >
                        &times;
                    </button>
                </div>

                <form
                    :action="'{{ url('/registros-horas') }}/' + editRegistro.id"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="space-y-4 p-6">

                        <div>
                            <label
                                for="estadoHorasEditar"
                                class="mb-1 block text-sm font-semibold text-slate-700"
                            >
                                Estado del registro *
                            </label>

                            <select
                                id="estadoHorasEditar"
                                name="estadoHoras"
                                x-model="editRegistro.estado"
                                required
                                class="w-full rounded-lg border border-slate-300
                                       bg-slate-50 px-3 py-2 text-sm
                                       focus:border-[#5b9eaa] focus:outline-none
                                       focus:ring-2 focus:ring-[#5b9eaa]"
                            >
                                <option value="Aprobada">Aprobada</option>
                                <option value="Rechazada">Rechazada</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="observacionesEditar"
                                class="mb-1 block text-sm font-semibold text-slate-700"
                            >
                                Observaciones
                            </label>

                            <textarea
                                id="observacionesEditar"
                                name="observaciones"
                                rows="4"
                                maxlength="200"
                                x-model="editRegistro.observaciones"
                                placeholder="Escribe una observación..."
                                class="w-full resize-y rounded-lg border border-slate-300
                                       bg-slate-50 px-3 py-2 text-sm
                                       focus:border-[#5b9eaa] focus:bg-white
                                       focus:outline-none focus:ring-2
                                       focus:ring-[#5b9eaa]"
                            ></textarea>

                            <p class="mt-1 text-xs text-slate-400">
                                Máximo 200 caracteres.
                            </p>
                        </div>

                        <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                            Si cambias el estado a Rechazada, las horas dejarán de
                            contabilizarse como aprobadas. Si lo cambias a Aprobada,
                            volverán a sumarse al avance de la pasantía.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100
                                bg-slate-50 px-6 py-4">
                        <button
                            type="button"
                            @click="openEditarRegistro = false"
                            class="rounded-lg bg-slate-200 px-4 py-2
                                   text-sm font-medium text-slate-700
                                   hover:bg-slate-300"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-[#5b9eaa] px-4 py-2
                                   text-sm font-semibold text-white
                                   shadow-sm hover:bg-[#4c848f]"
                        >
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
</body>
</html>
