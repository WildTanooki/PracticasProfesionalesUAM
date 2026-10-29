<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Revisar documento | UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

<div class="flex min-h-screen">

    <x-sidebarcoordinador />

    <div class="min-w-0 flex-1 flex flex-col">

        {{-- Encabezado --}}
        <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
            <div class="text-sm font-semibold text-[#5b9eaa]">
                Bienvenido,
                <span class="font-bold text-slate-800">
                    Coordinación Académica
                </span>
            </div>

            <p class="text-xs text-slate-500">
                Panel de gestión de prácticas profesionales
            </p>
        </header>

        <main class="mx-auto w-full max-w-[1600px] flex-1 px-6 py-8">

            @php
                $usuarioEstudiante = $documento->pasantia?->practicaProfesional?->estudiante?->usuario;
                $versionVigente = $documento->historial->first();
            @endphp
            {{-- Volver a los documentos de la pasantía del documento --}}
            <a href="{{ $documento->idPasantia
                    ? route('coordinador.pasantias.documentos', $documento->idPasantia)
                    : route('coordinador.documentos') }}"
               class="text-sm font-semibold text-slate-500 transition hover:text-[#5b9eaa]">
                ← Volver a documentos de la pasantía
            </a>

            @if (session('success'))
                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 mt-4">
                <h1 class="text-2xl font-bold text-slate-800">
                    {{ $documento->formato?->nombreFormato ?? 'Documento' }}
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    {{ trim(($usuarioEstudiante?->nombre ?? '') . ' ' . ($usuarioEstudiante?->apellido ?? '')) }}
                    ({{ $usuarioEstudiante?->credencial }})
                    · {{ $documento->pasantia?->empresa }}
                </p>
            </div>

            {{-- Revisión de la versión vigente --}}
            <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">Estado actual</p>
                        <p class="mt-1 text-lg font-semibold text-slate-800">
                            {{ $documento->estadoArchivo }}
                        </p>

                        @if ($documento->observacion)
                            <p class="mt-2 text-sm text-slate-600">
                                <span class="font-semibold">Observación:</span>
                                {{ $documento->observacion }}
                            </p>
                        @endif
                    </div>

                    @if ($versionVigente)
                        <a href="{{ route('coordinador.documentos.descargar', $versionVigente->idHistorial) }}"
                           class="inline-flex rounded-lg border border-[#5b9eaa] px-4 py-2 text-sm font-medium text-[#5b9eaa] transition-colors hover:bg-[#5b9eaa] hover:text-white">
                            Descargar versión {{ $versionVigente->version }}
                        </a>
                    @endif
                </div>

                @if ($versionVigente && $documento->estadoArchivo === 'Pendiente' && $versionVigente->estadoArchivo === 'Pendiente')

                    <form method="POST"
                          action="{{ route('coordinador.documentos.revisar', $documento->idDocumento) }}"
                          class="mt-6 grid gap-4 border-t border-slate-200 pt-6">
                        @csrf

                        {{-- Versión que se está revisando --}}
                        <input type="hidden" name="version" value="{{ $versionVigente->version }}">

                        <fieldset>
                            <legend class="mb-2 text-sm font-semibold text-slate-700">
                                Resultado de la revisión de la versión {{ $versionVigente->version }}
                            </legend>

                            <div class="flex flex-wrap gap-4 text-sm">
                                @foreach (\App\Services\GestionDocumentalService::ESTADOS_REVISION as $estadoOpcion)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio"
                                               name="estadoArchivo"
                                               value="{{ $estadoOpcion }}"
                                               required
                                               @checked(old('estadoArchivo') === $estadoOpcion)
                                               class="text-[#5b9eaa] focus:ring-[#5b9eaa]">
                                        {{ $estadoOpcion }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div>
                            <label for="observacion" class="mb-1 block text-sm font-semibold text-slate-700">
                                Observación
                            </label>

                            <textarea id="observacion"
                                      name="observacion"
                                      rows="3"
                                      maxlength="200"
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]/30">{{ old('observacion') }}</textarea>

                            <p class="mt-1 text-xs text-slate-400">
                                Obligatoria si el documento se marca como "A corregir" o "Rechazado". Máximo 200 caracteres.
                            </p>
                        </div>

                        <div>
                            <button type="submit"
                                    class="rounded-lg bg-[#5b9eaa] px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-[#4c848f] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:ring-offset-2">
                                Guardar revisión
                            </button>
                        </div>
                    </form>

                @endif
            </section>

            {{-- Historial --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Historial de versiones
                    </h2>
                </div>

                @include('partials.documento-historial', [
                    'documento' => $documento,
                    'rutaDescarga' => 'coordinador.documentos.descargar',
                ])
            </div>
        </main>
    </div>
</div>
</body>
</html>