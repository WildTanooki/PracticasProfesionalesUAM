<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle de solicitud | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Menú lateral del coordinador --}}
        <x-sidebarcoordinador />

        {{-- Contenido principal --}}
        <main class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="flex flex-col gap-3 border-b border-slate-200 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-10">
                <div>
                    <p class="text-sm text-slate-500">
                        Coordinación Académica / Cartas de recomendación
                    </p>
                    <h2 class="mt-1 text-2xl font-bold text-[#173b4b]">
                        Detalle de solicitud
                    </h2>
                </div>

                <a
                    href="{{ route('coordinador.recomendacion') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    ← Volver a solicitudes
                </a>
            </header>

            <div class="mx-auto max-w-5xl space-y-6 px-6 py-8 lg:px-10">

                {{-- Mensajes de validación --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">No se pudo procesar la solicitud.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Estado y datos generales --}}
                @php
                    $estado = $solicitud->estadoCarta ?? 'Pendiente';

                    $claseEstado = match ($estado) {
                        'Generada' => 'bg-emerald-100 text-emerald-700',
                        'Rechazada' => 'bg-red-100 text-red-700',
                        default => 'bg-amber-100 text-amber-700',
                    };

                    $nombreEstudiante = $solicitud->estudiante?->usuario?->nombre
                        ?? 'Nombre no disponible';

                    $credencial = $solicitud->credencialEstudiante
                        ?? $solicitud->estudiante?->credencial
                        ?? 'No disponible';

                    $carrera = $solicitud->estudiante?->planEstudios?->carrera?->nombre
                        ?? 'No disponible';
                @endphp

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Solicitud #{{ $solicitud->idCartaRecomendacion }}
                            </p>

                            <h3 class="mt-1 text-xl font-bold text-[#173b4b]">
                                Carta de recomendación
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Revisa la información antes de tomar una decisión.
                            </p>
                        </div>

                        <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-sm font-semibold {{ $claseEstado }}">
                            {{ $estado }}
                        </span>
                    </div>

                    <div class="mt-7 grid grid-cols-1 gap-5 border-t border-slate-100 pt-6 sm:grid-cols-2">

                        <div>
                            <p class="text-sm text-slate-500">Estudiante</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $nombreEstudiante }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Carnet</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $credencial }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Carrera</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $carrera }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Fecha de solicitud</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $solicitud->fechaSolicitud
                                    ? \Illuminate\Support\Carbon::parse($solicitud->fechaSolicitud)->format('d/m/Y')
                                    : 'No disponible' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Empresa de destino</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $solicitud->empresaDestino }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Puesto o cargo</p>
                            <p class="mt-1 font-semibold text-slate-800">
                                {{ $solicitud->puestoCargo }}
                            </p>
                        </div>

                    </div>
                </section>

                {{-- Observación del estudiante --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h3 class="text-lg font-bold text-[#173b4b]">
                        Observaciones de la solicitud
                    </h3>

                    <div class="mt-5">
                        <p class="text-sm font-medium text-slate-500">
                            Observación del estudiante
                        </p>

                        <div class="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                            {{ $solicitud->observacionEstudiante ?: 'El estudiante no agregó observaciones.' }}
                        </div>
                    </div>

                    @if ($solicitud->observacionCoordinador)
                        <div class="mt-5">
                            <p class="text-sm font-medium text-slate-500">
                                Observación de Coordinación Académica
                            </p>

                            <div class="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                                {{ $solicitud->observacionCoordinador }}
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Carta emitida, si existe --}}
                @if ($solicitud->rutaCartaEmitida)
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="text-lg font-bold text-[#173b4b]">
                            Carta emitida
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Archivo asociado a esta solicitud.
                        </p>

                        <p class="mt-3 break-all rounded-lg bg-slate-50 p-4 text-sm text-slate-700">
                            {{ basename($solicitud->rutaCartaEmitida) }}
                        </p>
                    </section>
                @endif

                {{-- Formulario para resolver --}}
                @if (!in_array($estado, ['Generada', 'Rechazada'], true))

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

                        <div>
                            <h3 class="text-lg font-bold text-[#173b4b]">
                                Gestionar solicitud
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Selecciona una decisión. Para aprobar, adjunta la carta firmada y sellada.
                            </p>
                        </div>

                        <form
                            action="{{ route('coordinador.recomendacion.resolver', [
                                'idCartaRecomendacion' => $solicitud->idCartaRecomendacion
                            ]) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="mt-6 space-y-6"
                        >
                            @csrf

                            {{-- Decisión --}}
                            <div>
                                <label for="estadoCarta" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Decisión <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="estadoCarta"
                                    name="estadoCarta"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#5b9eaa] focus:ring-2 focus:ring-[#5b9eaa]/20"
                                >
                                    <option value="">Selecciona una decisión</option>
                                    <option value="Generada" @selected(old('estadoCarta') === 'Generada')>
                                        Generar solicitud
                                    </option>
                                    <option value="Rechazada" @selected(old('estadoCarta') === 'Rechazada')>
                                        Rechazar solicitud
                                    </option>
                                </select>
                            </div>

                            {{-- Archivo de la carta --}}
                            <div id="contenedorArchivo" class="hidden">
                                <label for="archivoCarta" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Carta firmada y sellada
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    id="archivoCarta"
                                    name="archivoCarta"
                                    accept=".pdf,.doc,.docx"
                                    class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-3 file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                >

                                <p class="mt-2 text-xs text-slate-500">
                                    Formatos permitidos: PDF, DOC o DOCX. Tamaño máximo: 10 MB.
                                </p>
                            </div>

                            {{-- Observación --}}
                            <div>
                                <label for="observacionCoordinador" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Observación de Coordinación Académica
                                </label>

                                <textarea
                                    id="observacionCoordinador"
                                    name="observacionCoordinador"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Escribe una observación si corresponde..."
                                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#5b9eaa] focus:ring-2 focus:ring-[#5b9eaa]/20"
                                >{{ old('observacionCoordinador') }}</textarea>

                                <p class="mt-2 text-xs text-slate-500">
                                    Máximo 1000 caracteres.
                                </p>
                            </div>

                            {{-- Botones --}}
                            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">

                                <a
                                    href="{{ route('coordinador.recomendacion') }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Cancelar
                                </a>

                                <button
                                    type="submit"
                                    id="btnResolver"
                                    class="inline-flex items-center justify-center rounded-lg bg-[#397985] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2e6570]"
                                >
                                    Guardar decisión
                                </button>

                            </div>

                        </form>
                    </section>

                @else

                    {{-- Solicitud resuelta --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="text-lg font-bold text-[#173b4b]">
                            Solicitud resuelta
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Esta solicitud ya fue {{ strtolower($estado) }} y no admite nuevas decisiones desde esta pantalla.
                        </p>
                    </section>

                @endif

            </div>
        </main>
    </div>

    {{-- Mostrar el archivo únicamente cuando se selecciona aprobar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const estadoSelect = document.getElementById('estadoCarta');
            const contenedorArchivo = document.getElementById('contenedorArchivo');
            const archivoCarta = document.getElementById('archivoCarta');

            if (!estadoSelect || !contenedorArchivo || !archivoCarta) {
                return;
            }

            function actualizarFormulario() {
                const requiereArchivo = estadoSelect.value === 'Generada';

                contenedorArchivo.classList.toggle('hidden', !requiereArchivo);
                archivoCarta.required = requiereArchivo;
            }

            estadoSelect.addEventListener('change', actualizarFormulario);
            actualizarFormulario();
        });
    </script>

</body>
</html>