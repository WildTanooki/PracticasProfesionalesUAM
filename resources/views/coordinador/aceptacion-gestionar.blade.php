
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar carta de aprobación | UAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-screen">
    <x-sidebarcoordinador />

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
            <div class="text-sm font-semibold text-[#5b9eaa]">
                Bienvenido,
                <span class="font-bold text-slate-800">Coordinación Académica</span>
            </div>
            <p class="text-xs text-slate-500">Panel de gestión de prácticas profesionales</p>
        </header>

        <main class="mx-auto w-full max-w-[1200px] flex-1 px-6 py-8">

            @if (session('success'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="mb-2 font-semibold">No fue posible procesar la solicitud:</p>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6">
                <a href="{{ route('coordinador.aceptacion') }}"
                   class="text-sm font-semibold text-[#397985] hover:underline">
                    &larr; Volver a las solicitudes
                </a>
            </div>

            @php
                $estado = $carta->estadoAprobacion ?? 'Pendiente';

                $estiloEstado = match ($estado) {
                    'Pendiente' => 'bg-amber-100 text-amber-800',
                    'Aprobada' => 'bg-emerald-100 text-emerald-800',
                    'Rechazada' => 'bg-red-100 text-red-800',
                    'A corregir' => 'bg-orange-100 text-orange-800',
                    default => 'bg-slate-100 text-slate-700',
                };

                $usuario = $carta->usuario;
                $estudiante = $usuario?->estudiante;
                $carrera = $estudiante?->planEstudios?->carrera;

                $nombreEstudiante = trim(
                    ($usuario?->nombre ?? '') . ' ' .
                    ($usuario?->apellido ?? '')
                );

                $puedeGestionar = in_array($estado, ['Pendiente', 'A corregir'], true);
            @endphp

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Detalle de carta de aprobación
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Revisa la información y selecciona la acción correspondiente.
                    </p>
                </div>

                <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold {{ $estiloEstado }}">
                    {{ $estado }}
                </span>
            </div>

            {{-- Información del estudiante --}}
            <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Información del estudiante</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nombre completo</p>
                        <p class="mt-1 font-semibold">{{ $nombreEstudiante ?: 'No disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Carnet / credencial</p>
                        <p class="mt-1 font-semibold">{{ $carta->credencial ?? 'No disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Carrera</p>
                        <p class="mt-1 font-semibold">{{ $carrera?->nombre ?? 'No disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Plan de estudios</p>
                        <p class="mt-1 font-semibold">{{ $estudiante?->idPlanEstudios ?? 'No disponible' }}</p>
                    </div>
                </div>
            </section>

            {{-- Información de la carta --}}
            <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Información de la práctica indicada en la carta</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Empresa de destino</p>
                        <p class="mt-1 font-semibold">{{ $carta->empresaDestino ?? 'No especificada' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Puesto o cargo</p>
                        <p class="mt-1 font-semibold">{{ $carta->puestoCargo ?? 'No especificado' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Fecha de inicio</p>
                        <p class="mt-1 font-semibold">
                            {{ $carta->fechaInicio ? \Illuminate\Support\Carbon::parse($carta->fechaInicio)->format('d/m/Y') : 'No especificada' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Fecha de finalización estimada</p>
                        <p class="mt-1 font-semibold">
                            {{ $carta->fechaFin ? \Illuminate\Support\Carbon::parse($carta->fechaFin)->format('d/m/Y') : 'No especificada' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Horas propuestas</p>
                        <p class="mt-1 font-semibold">
                            {{ $carta->horasPropuestas !== null ? number_format($carta->horasPropuestas) . ' horas' : 'No especificadas' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Fecha de solicitud</p>
                        <p class="mt-1 font-semibold">
                            {{ $carta->fechaEmision ? \Illuminate\Support\Carbon::parse($carta->fechaEmision)->format('d/m/Y') : 'No disponible' }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Tutor indicado en la carta --}}
            <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Información del tutor empresarial indicado en la carta</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nombre completo</p>
                        <p class="mt-1 font-semibold">
                            {{ trim(($carta->nombreTutor ?? '') . ' ' . ($carta->apellidoTutor ?? '')) ?: 'No especificado' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Teléfono</p>
                        <p class="mt-1 font-semibold">{{ $carta->telefonoTutor ?? 'No especificado' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Correo electrónico</p>
                        <p class="mt-1 break-words font-semibold">{{ $carta->correoTutor ?? 'No especificado' }}</p>
                    </div>
                </div>
            </section>

            {{-- Documento adjunto --}}
            <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Documento adjunto</h2>
                </div>

                <div class="p-6">
                    @if ($carta->rutaArchivo)
                        <p class="mb-3 text-sm text-slate-600">
                            El estudiante adjuntó un archivo a su solicitud.
                        </p>
                        <a
                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($carta->rutaArchivo) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex rounded-lg border border-[#5b9eaa] px-4 py-2 text-sm font-semibold text-[#397985] hover:bg-[#5b9eaa] hover:text-white"
                        >
                            Ver documento adjunto
                        </a>
                    @else
                        <p class="text-sm text-slate-500">No hay ningún documento adjunto.</p>
                    @endif
                </div>
            </section>

            {{-- Observaciones --}}
            <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Observaciones registradas</h2>
                </div>
                <div class="p-6">
                    <p class="whitespace-pre-line text-sm leading-6 text-slate-600">
                        {{ $carta->observacion ?: 'No se han registrado observaciones.' }}
                    </p>
                </div>
            </section>

            {{-- Gestión --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="font-semibold">Gestión de la solicitud</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Selecciona una acción para mostrar el formulario correspondiente.
                    </p>
                </div>

                <div class="space-y-6 p-6">
                    @if ($puedeGestionar)
                        <div>
                            <label for="accionGestion" class="mb-2 block text-sm font-semibold">
                                Acción que deseas realizar
                            </label>
                            <select
                                id="accionGestion"
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm sm:max-w-xl"
                            >
                                <option value="">Selecciona una opción</option>
                                <option value="corregir">Solicitar correcciones</option>
                                <option value="rechazar">Rechazar solicitud</option>
                                <option value="aceptar">Aceptar y crear pasantía</option>
                            </select>
                        </div>

                        {{-- Solicitar correcciones --}}
                        <div id="panelCorregir" class="hidden rounded-lg border border-orange-200 bg-orange-50/50 p-5">
                            <h3 class="font-semibold text-orange-800">Solicitar correcciones</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Describe los errores, documentos faltantes o modificaciones que debe realizar el estudiante.
                            </p>

                            <form
                                action="{{ route('coordinador.aceptacion.resolver', ['idCartaAprobacion' => $carta->idCartaAprobacion]) }}"
                                method="POST"
                                class="mt-4 space-y-4"
                            >
                                @csrf
                                <input type="hidden" name="estadoAprobacion" value="A corregir">
                                <input type="hidden" name="_formulario" value="corregir">

                                <div>
                                    <label for="observacion_corregir" class="mb-2 block text-sm font-semibold">
                                        Observaciones para el estudiante
                                    </label>
                                    <textarea
                                        id="observacion_corregir"
                                        name="observacion"
                                        rows="4"
                                        maxlength="1000"
                                        required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        placeholder="Describe las correcciones que debe realizar..."
                                    >{{ old('_formulario') === 'corregir' ? old('observacion') : $carta->observacion }}</textarea>
                                </div>

                                <button type="submit" class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">
                                    Enviar solicitud de corrección
                                </button>
                            </form>
                        </div>

                        {{-- Rechazar --}}
                        <div id="panelRechazar" class="hidden rounded-lg border border-red-200 bg-red-50/50 p-5">
                            <h3 class="font-semibold text-red-800">Rechazar solicitud</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Indica el motivo por el cual la solicitud no puede ser aprobada.
                            </p>

                            <form
                                action="{{ route('coordinador.aceptacion.resolver', ['idCartaAprobacion' => $carta->idCartaAprobacion]) }}"
                                method="POST"
                                class="mt-4 space-y-4"
                                onsubmit="return confirm('¿Confirmas que deseas rechazar esta solicitud?');"
                            >
                                @csrf
                                <input type="hidden" name="estadoAprobacion" value="Rechazada">
                                <input type="hidden" name="_formulario" value="rechazar">

                                <div>
                                    <label for="observacion_rechazo" class="mb-2 block text-sm font-semibold">
                                        Motivo del rechazo
                                    </label>
                                    <textarea
                                        id="observacion_rechazo"
                                        name="observacion"
                                        rows="4"
                                        maxlength="1000"
                                        required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        placeholder="Explica el motivo del rechazo..."
                                    >{{ old('_formulario') === 'rechazar' ? old('observacion') : '' }}</textarea>
                                </div>

                                <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                                    Confirmar rechazo
                                </button>
                            </form>
                        </div>

                        {{-- Aprobar y crear pasantía --}}
                        <div id="panelAceptar" class="hidden rounded-lg border border-emerald-200 bg-emerald-50/50 p-5">
                            <h3 class="font-semibold text-emerald-800">Aceptar carta y crear pasantía</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Los campos se autocompletan con la carta. Puedes corregir sus valores antes de confirmar.
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-4 rounded-lg border border-emerald-200 bg-white p-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Estudiante</p>
                                    <p class="mt-1 font-semibold">{{ $nombreEstudiante ?: 'No disponible' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Tutor indicado en la carta</p>
                                    <p class="mt-1 font-semibold">
                                        {{ trim(($carta->nombreTutor ?? '') . ' ' . ($carta->apellidoTutor ?? '')) ?: 'No especificado' }}
                                    </p>
                                </div>
                            </div>

                            <form
                                action="{{ route('coordinador.aceptacion.pasantia.store', ['idCartaAprobacion' => $carta->idCartaAprobacion]) }}"
                                method="POST"
                                id="formCrearPasantia"
                                class="mt-6 space-y-5"
                            >
                                @csrf
                                <input type="hidden" name="_formulario" value="crear_pasantia">

                                {{-- Práctica pendiente --}}
                                <div>
                                    <label for="idPractica" class="mb-2 block text-sm font-semibold">
                                        Práctica profesional del estudiante
                                    </label>
                                    <select
                                        id="idPractica"
                                        name="idPractica"
                                        required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >
                                        <option value="">Selecciona una práctica pendiente</option>
                                        @foreach ($practicasPendientes as $practica)
                                            <option
                                                value="{{ $practica->idPractica }}"
                                                @selected((string) old('idPractica') === (string) $practica->idPractica)
                                            >
                                                {{ $practica->nombrePractica ?? 'Práctica profesional' }}
                                                — {{ $practica->horasRequeridas }} horas registradas en la práctica
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($practicasPendientes->isEmpty())
                                        <p class="mt-2 text-sm text-red-600">
                                            El estudiante no tiene prácticas pendientes disponibles.
                                        </p>
                                    @endif
                                </div>

                                {{-- Horas requeridas --}}
                                <div>
                                    <label for="horasRequeridas" class="mb-2 block text-sm font-semibold">
                                        Horas requeridas para esta pasantía
                                    </label>
                                    <input
                                        type="number"
                                        id="horasRequeridas"
                                        name="horasRequeridas"
                                        min="1"
                                        required
                                        value="{{ old('horasRequeridas', $carta->horasPropuestas) }}"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >
                                    <p class="mt-1 text-xs text-slate-500">
                                        Valor inicial tomado de las horas propuestas en la carta.
                                    </p>
                                </div>

                                {{-- Fechas --}}
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="fechaInicio" class="mb-2 block text-sm font-semibold">
                                            Fecha de inicio
                                        </label>
                                        <input
                                            type="date"
                                            id="fechaInicio"
                                            name="fechaInicio"
                                            required
                                            value="{{ old('fechaInicio', $carta->fechaInicio ? \Illuminate\Support\Carbon::parse($carta->fechaInicio)->format('Y-m-d') : '') }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        >
                                    </div>

                                    <div>
                                        <label for="fechaFinEstimada" class="mb-2 block text-sm font-semibold">
                                            Fecha de finalización estimada
                                        </label>
                                        <input
                                            type="date"
                                            id="fechaFinEstimada"
                                            name="fechaFinEstimada"
                                            required
                                            value="{{ old('fechaFinEstimada', $carta->fechaFin ? \Illuminate\Support\Carbon::parse($carta->fechaFin)->format('Y-m-d') : '') }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        >
                                    </div>
                                </div>

                                {{-- Empresa y cargo --}}
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="empresa" class="mb-2 block text-sm font-semibold">Empresa</label>
                                        <input
                                            type="text"
                                            id="empresa"
                                            name="empresa"
                                            maxlength="50"
                                            required
                                            value="{{ old('empresa', $carta->empresaDestino) }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        >
                                    </div>

                                    <div>
                                        <label for="cargo" class="mb-2 block text-sm font-semibold">Puesto o cargo</label>
                                        <input
                                            type="text"
                                            id="cargo"
                                            name="cargo"
                                            maxlength="50"
                                            required
                                            value="{{ old('cargo', $carta->puestoCargo) }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                        >
                                    </div>
                                </div>

                                {{-- Tipo de tutor --}}
                                <div>
                                    <label for="tipoTutor" class="mb-2 block text-sm font-semibold">
                                        Tutor empresarial
                                    </label>
                                    <select
                                        id="tipoTutor"
                                        name="tipoTutor"
                                        required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >
                                        <option value="">Selecciona una opción</option>
                                        <option value="existente" @selected(old('tipoTutor') === 'existente')>
                                            Seleccionar tutor existente
                                        </option>
                                        <option value="nuevo" @selected(old('tipoTutor') === 'nuevo')>
                                            Registrar un tutor nuevo
                                        </option>
                                    </select>
                                </div>

                                {{-- Tutor existente --}}
                                <div id="panelTutorExistente" class="hidden">
                                    <label for="idTutor" class="mb-2 block text-sm font-semibold">
                                        Selecciona el tutor
                                    </label>
                                    <select
                                        id="idTutor"
                                        name="idTutor"
                                        disabled
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >
                                        <option value="">Selecciona un tutor</option>
                                        @foreach ($tutores as $tutor)
                                            <option
                                                value="{{ $tutor->idTutor }}"
                                                @selected((string) old('idTutor') === (string) $tutor->idTutor)
                                            >
                                                {{ $tutor->usuario?->nombre ?? 'Sin nombre' }}
                                                {{ $tutor->usuario?->apellido ?? '' }}
                                                — {{ $tutor->usuario?->correo ?? $tutor->idTutor }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($tutores->isEmpty())
                                        <p class="mt-2 text-sm text-slate-500">
                                            No hay tutores activos registrados. Puedes registrar uno nuevo.
                                        </p>
                                    @endif
                                </div>

                                {{-- Nuevo tutor --}}
                                <div id="panelTutorNuevo" class="hidden space-y-4 rounded-lg border border-slate-200 bg-white p-5">
                                    <h4 class="font-semibold">Datos del nuevo tutor</h4>
                                    <p class="text-sm text-slate-500">
                                        La credencial se utilizará como identificador del usuario y del tutor.
                                    </p>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="credencialTutor" class="mb-2 block text-sm font-medium">Credencial</label>
                                            <input type="text" id="credencialTutor" name="credencialTutor"
                                                   maxlength="30" value="{{ old('credencialTutor') }}" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                        </div>

                                        <div>
                                            <label for="nombreTutorNuevo" class="mb-2 block text-sm font-medium">Nombre</label>
                                            <input type="text" id="nombreTutorNuevo" name="nombreTutor"
                                                   maxlength="30" value="{{ old('nombreTutor', $carta->nombreTutor) }}" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                        </div>

                                        <div>
                                            <label for="apellidoTutorNuevo" class="mb-2 block text-sm font-medium">Apellido</label>
                                            <input type="text" id="apellidoTutorNuevo" name="apellidoTutor"
                                                   maxlength="30" value="{{ old('apellidoTutor', $carta->apellidoTutor) }}" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                        </div>

                                        <div>
                                            <label for="telefonoTutorNuevo" class="mb-2 block text-sm font-medium">Teléfono</label>
                                            <input type="text" id="telefonoTutorNuevo" name="telefonoTutor"
                                                   maxlength="30" value="{{ old('telefonoTutor', $carta->telefonoTutor) }}" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label for="correoTutorNuevo" class="mb-2 block text-sm font-medium">Correo electrónico</label>
                                            <input type="email" id="correoTutorNuevo" name="correoTutor"
                                                   maxlength="100" value="{{ old('correoTutor', $carta->correoTutor) }}" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label for="contrasenaTutor" class="mb-2 block text-sm font-medium">Contraseña inicial</label>
                                            <input type="password" id="contrasenaTutor" name="contrasenaTutor"
                                                   minlength="8" autocomplete="new-password" disabled
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm">
                                            <p class="mt-1 text-xs text-slate-500">Debe tener al menos 8 caracteres.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                                    <strong>Estado inicial:</strong> la pasantía se registrará automáticamente como
                                    <strong>En progreso</strong>.
                                </div>

                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    <strong>Importante:</strong> al confirmar, se aprobará la carta y se creará la pasantía
                                    con los valores finales del formulario.
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="submit"
                                        id="btnCrearPasantia"
                                        @disabled($practicasPendientes->isEmpty())
                                        onclick="return confirm('¿Confirmas la aprobación de la carta y la creación de la pasantía?')"
                                        class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Aprobar carta y crear pasantía
                                    </button>

                                    <button
                                        type="button"
                                        id="btnCancelarAceptar"
                                        class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-600">
                                La solicitud está en estado <strong>{{ $estado }}</strong>.
                                No hay acciones disponibles para este estado.
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            <div class="mt-6">
                <a href="{{ route('coordinador.aceptacion') }}"
                   class="inline-flex rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-slate-50">
                    Volver al listado
                </a>
            </div>

            <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana (UAM)
            </footer>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('accionGestion');

    if (!selector) return;

    const paneles = {
        corregir: document.getElementById('panelCorregir'),
        rechazar: document.getElementById('panelRechazar'),
        aceptar: document.getElementById('panelAceptar'),
    };

    function actualizarPaneles() {
        Object.entries(paneles).forEach(([accion, panel]) => {
            panel?.classList.toggle('hidden', selector.value !== accion);
        });
    }

    selector.addEventListener('change', actualizarPaneles);

    const tipoTutor = document.getElementById('tipoTutor');
    const panelTutorExistente = document.getElementById('panelTutorExistente');
    const panelTutorNuevo = document.getElementById('panelTutorNuevo');
    const selectTutor = document.getElementById('idTutor');

    const camposTutorNuevo = [
        document.getElementById('credencialTutor'),
        document.getElementById('nombreTutorNuevo'),
        document.getElementById('apellidoTutorNuevo'),
        document.getElementById('telefonoTutorNuevo'),
        document.getElementById('correoTutorNuevo'),
        document.getElementById('contrasenaTutor'),
    ];

    function actualizarTutor() {
        if (!tipoTutor) return;

        const existente = tipoTutor.value === 'existente';
        const nuevo = tipoTutor.value === 'nuevo';

        panelTutorExistente?.classList.toggle('hidden', !existente);
        panelTutorNuevo?.classList.toggle('hidden', !nuevo);

        if (selectTutor) {
            selectTutor.disabled = !existente;
            selectTutor.required = existente;
        }

        camposTutorNuevo.forEach((campo) => {
            if (!campo) return;
            campo.disabled = !nuevo;
            campo.required = nuevo && campo.id !== 'telefonoTutorNuevo';
        });
    }

    tipoTutor?.addEventListener('change', actualizarTutor);

    document.getElementById('btnCancelarAceptar')?.addEventListener('click', function () {
        selector.value = '';
        actualizarPaneles();
        if (tipoTutor) {
            tipoTutor.value = '';
            actualizarTutor();
        }
    });

    const hayErrores = @json($errors->any());
    const formularioAnterior = @json(old('_formulario'));
    const estadoAnterior = @json(old('estadoAprobacion'));

    if (hayErrores) {
        if (formularioAnterior === 'crear_pasantia') {
            selector.value = 'aceptar';
        } else if (formularioAnterior === 'corregir' || estadoAnterior === 'A corregir') {
            selector.value = 'corregir';
        } else if (formularioAnterior === 'rechazar' || estadoAnterior === 'Rechazada') {
            selector.value = 'rechazar';
        }
    }

    actualizarPaneles();
    actualizarTutor();
});
</script>
</body>
</html>
