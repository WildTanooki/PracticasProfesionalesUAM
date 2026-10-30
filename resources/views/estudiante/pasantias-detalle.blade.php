
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
    x-data="{ openRegistroHoras: @js($errors->hasAny(['fechaRegistro', 'horasIngresadas', 'observaciones'])) }"
    @keydown.escape.window="openRegistroHoras = false"
>

    <div class="flex min-h-screen">

        {{-- Navbar lateral --}}
        <x-sidebarestudiante />

        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
                <span class="text-sm font-semibold text-[#5b9eaa]">
                    Panel
                    <span class="font-bold text-slate-800">
                        de Estudiante
                    </span>
                </span>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-6">

                {{-- Regresar --}}
                <a
                    href="{{ route('estudiante.pasantias') }}"
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

                {{-- Información principal --}}
                <section class="mt-5 rounded-2xl bg-[#5b9eaa] p-6 text-white shadow-sm sm:p-8">

                    <div class="flex flex-wrap items-start justify-between gap-4">

                        <div>
                            <p class="text-sm font-medium text-white/80">
                                Detalle de pasantía
                            </p>

                            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">
                                {{ $pasantia->empresa ?: 'Empresa no especificada' }}
                            </h1>

                            <p class="mt-2 text-sm text-white/90">
                                Cargo: {{ $pasantia->cargo ?: 'No especificado' }}
                            </p>
                        </div>

                        @php
                            $estado = $pasantia->estado;

                            $estilosEstado = match ($estado) {
                                'En progreso' => 'bg-blue-50 text-blue-700',
                                'Completada' => 'bg-emerald-50 text-emerald-700',
                                'Pendiente' => 'bg-amber-50 text-amber-700',
                                default => 'bg-white/90 text-slate-700',
                            };

                            $requeridas = max(0, (int) $pasantia->horasRequeridas);
                            $acumuladas = max(0, (int) $pasantia->registrosDeHoras->where('estadoHoras', 'Aprobada')->sum('horasIngresadas'));

                            $porcentaje = $requeridas > 0
                                ? min(100, (int) round(($acumuladas / $requeridas) * 100))
                                : 0;
                        @endphp

                        <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $estilosEstado }}">
                            {{ $estado ?: 'Sin estado' }}
                        </span>

                    </div>

                    {{-- Avance --}}
                    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3">

                        <div class="rounded-xl bg-white/15 p-4">
                            <p class="text-sm text-white/80">
                                Horas realizadas
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
                                Horas pendientes
                            </p>

                            <p class="mt-1 text-2xl font-bold">
                                {{ number_format(max(0, $requeridas - $acumuladas)) }} h
                            </p>
                        </div>

                    </div>

                    <div class="mt-6">

                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium">
                                Progreso de horas
                            </span>

                            <span class="font-bold">
                                {{ $porcentaje }}%
                            </span>
                        </div>

                        <div
                            class="h-3 overflow-hidden rounded-full bg-black/15"
                            role="progressbar"
                            aria-label="Avance de horas de la pasantía"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $porcentaje }}"
                        >
                            <div
                                class="h-full rounded-full bg-white transition-all duration-500"
                                style="width: {{ $porcentaje }}%"
                            ></div>
                        </div>

                    </div>

                </section>

                {{-- Información adicional --}}
                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <h2 class="text-lg font-bold text-slate-800">
                        Información de la pasantía
                    </h2>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">

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

                    </div>

                </section>

                {{-- Documentos y registros de horas --}}
                <div class="mt-6 grid grid-cols-1 items-start gap-6 xl:grid-cols-2">

                {{-- Documentos --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <div class="flex flex-wrap items-center justify-between gap-3">

                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                Documentos enviados
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Consulta el estado de los documentos asociados a esta pasantía.
                            </p>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">
                            {{ $pasantia->documentos->count() }}
                            {{ $pasantia->documentos->count() === 1 ? 'documento' : 'documentos' }}
                        </span>

                    </div>

                    @if ($pasantia->documentos->isEmpty())

                        <div class="mt-5 rounded-xl border border-dashed border-slate-300 px-5 py-10 text-center">

                            <p class="font-semibold text-slate-700">
                                No hay documentos registrados
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Los documentos asociados a esta pasantía aparecerán aquí.
                            </p>

                        </div>

                    @else

                        <div class="mt-5 overflow-x-auto">

                            <table class="w-full min-w-[600px] text-left text-sm">

                                <thead>
                                    <tr class="bg-slate-100 text-slate-600">
                                        <th class="rounded-l-lg px-4 py-3 font-semibold">
                                            Documento
                                        </th>

                                        <th class="px-4 py-3 font-semibold">
                                            Estado
                                        </th>

                                        <th class="rounded-r-lg px-4 py-3 font-semibold">
                                            Observación
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">

                                    @foreach ($pasantia->documentos as $documento)

                                        @php
                                            $estadoDocumento = $documento->estadoArchivo;

                                            $estiloDocumento = match ($estadoDocumento) {
                                                'Aprobado' => 'bg-emerald-50 text-emerald-700',
                                                'Pendiente' => 'bg-amber-50 text-amber-700',
                                                'En revisión' => 'bg-blue-50 text-blue-700',
                                                'A corregir' => 'bg-red-50 text-red-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp

                                        <tr class="align-top">

                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-800">
                                                    {{ $documento->formato?->nombreFormato ?? 'Documento' }}
                                                </p>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloDocumento }}">
                                                    {{ $estadoDocumento ?: 'Sin estado' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4 text-slate-600">
                                                {{ $documento->observacion ?: 'Sin observaciones' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @endif

                </section>


                    {{-- Registros de horas --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">
                                    Horas registradas
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Consulta las horas que has registrado y el estado de su revisión.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">
                                    {{ $pasantia->registrosDeHoras->count() }}
                                </span>

                                @if ($pasantia->estado === 'En progreso')
                                    <button
                                        type="button"
                                        @click="openRegistroHoras = true"
                                        class="inline-flex items-center gap-2 rounded-lg bg-[#5b9eaa] px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4c848f]"
                                    >
                                        <span class="text-lg leading-none">+</span>
                                        Registrar horas
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($pasantia->registrosDeHoras->isEmpty())
                            <div class="mt-5 rounded-xl border border-dashed border-slate-300 px-5 py-10 text-center">
                                <p class="font-semibold text-slate-700">
                                    No hay horas registradas
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Los registros de horas aparecerán aquí.
                                </p>
                            </div>
                        @else
                            <div class="mt-5 overflow-x-auto">
                                <table class="w-full min-w-[620px] text-left text-sm">
                                    <thead>
                                        <tr class="bg-slate-100 text-slate-600">
                                            <th class="rounded-l-lg px-4 py-3 font-semibold">Horas realizadas</th>
                                            <th class="px-4 py-3 font-semibold">Observaciones</th>
                                            <th class="px-4 py-3 font-semibold">Fecha registrada</th>
                                            <th class="rounded-r-lg px-4 py-3 font-semibold">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($pasantia->registrosDeHoras->sortByDesc('fechaRegistro') as $registro)
                                            @php
                                                $estadoHoras = $registro->estadoHoras;
                                                $estiloHoras = match ($estadoHoras) {
                                                    'Aprobada' => 'bg-emerald-50 text-emerald-700',
                                                    'Pendiente' => 'bg-amber-50 text-amber-700',
                                                    'Rechazada' => 'bg-red-50 text-red-700',
                                                    default => 'bg-slate-100 text-slate-700',
                                                };
                                            @endphp
                                            <tr class="align-top">
                                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-800">
                                                    {{ number_format((int) $registro->horasIngresadas) }} h
                                                </td>
                                                <td class="max-w-[220px] px-4 py-4 text-slate-600">
                                                    {{ $registro->observaciones ?: 'Sin observaciones' }}
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-4 text-slate-600">
                                                    {{ $registro->fechaRegistro?->format('d/m/Y') ?? 'No registrada' }}
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloHoras }}">
                                                        {{ $estadoHoras ?: 'Sin estado' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>

                </div>

                {{-- Pie de página --}}
                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>

        </div>

    </div>


    {{-- Modal para registrar horas --}}
    <div
        x-show="openRegistroHoras"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="openRegistroHoras = false"
    >
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Registrar horas</h3>
                    <p class="mt-1 text-sm text-slate-500">Ingresa la información de las horas realizadas.</p>
                </div>
                <button
                    type="button"
                    @click="openRegistroHoras = false"
                    class="text-xl font-bold text-slate-400 hover:text-slate-600"
                    aria-label="Cerrar ventana"
                >&times;</button>
            </div>

            <form action="{{ route('estudiante.registro-horas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="idPasantia" value="{{ $pasantia->idPasantia }}">

                <div class="space-y-4 p-6">
                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <p class="mb-1 font-semibold">Revisa la información ingresada:</p>
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="fechaRegistro" class="mb-1 block text-sm font-semibold text-slate-700">
                            Fecha en que realizaste las horas *
                        </label>
                        <input
                            type="date"
                            id="fechaRegistro"
                            name="fechaRegistro"
                            value="{{ old('fechaRegistro') }}"
                            min="{{ $pasantia->fechaInicio?->format('Y-m-d') }}"
                            max="{{ min(now()->toDateString(), $pasantia->fechaFinEstimada?->format('Y-m-d') ?? now()->toDateString()) }}"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >
                        @error('fechaRegistro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="horasIngresadas" class="mb-1 block text-sm font-semibold text-slate-700">
                            Horas realizadas *
                        </label>
                        <input
                            type="number"
                            id="horasIngresadas"
                            name="horasIngresadas"
                            value="{{ old('horasIngresadas') }}"
                            min="1"
                            step="1"
                            required
                            placeholder="Ej. 4"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >
                        @error('horasIngresadas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="observaciones" class="mb-1 block text-sm font-semibold text-slate-700">
                            Observaciones (opcional)
                        </label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="3"
                            maxlength="200"
                            placeholder="Describe brevemente las actividades realizadas."
                            class="w-full resize-y rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                        >{{ old('observaciones') }}</textarea>
                        <p class="mt-1 text-xs text-slate-400">Máximo 200 caracteres.</p>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                        El registro quedará pendiente hasta que tu tutor lo revise. Solo las horas aprobadas se sumarán al avance de la pasantía.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        @click="openRegistroHoras = false"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300"
                    >Cancelar</button>
                    <button
                        type="submit"
                        class="rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                    >Enviar registro</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
