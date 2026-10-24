<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Carta | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

<div class="flex min-h-screen">

    <x-sidebarestudiante />

    <div class="min-w-0 flex-1">

        <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="mx-auto max-w-5xl">
                <h1 class="text-xl font-bold">Editar Carta de Aceptación</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Registra los datos de tu empresa y tutor.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-8">

            <a href="{{ route('estudiante.aceptacion') }}"
               class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-teal-700 hover:underline">
                ← Volver a mis solicitudes
            </a>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Revisa los siguientes campos:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('estudiante.aceptacion.update', $carta->idCartaAprobacion) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                @csrf
                @method('PUT')

                {{-- Datos de la empresa --}}
                <section>
                    <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                        Datos de la empresa
                    </h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="empresaDestino"
                                   class="mb-1 block text-sm font-medium">
                                Nombre de la empresa *
                            </label>

                            <input id="empresaDestino"
                                   name="empresaDestino"
                                   type="text"
                                   maxlength="50"
                                   value="{{ old('empresaDestino', $carta->empresaDestino) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="horasPropuestas"
                                   class="mb-1 block text-sm font-medium">
                                Horas propuestas a completar*
                            </label>

                            <input id="horasPropuestas"
                                   name="horasPropuestas"
                                   type="number"
                                   min="1"
                                   value="{{ old('horasPropuestas', $carta->horasPropuestas) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="puestoCargo"
                                   class="mb-1 block text-sm font-medium">
                                Puesto o cargo *
                            </label>

                            <input id="puestoCargo"
                                   name="puestoCargo"
                                   type="text"
                                   maxlength="50"
                                   value="{{ old('puestoCargo', $carta->puestoCargo) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                    </div>
                </section>

                {{-- Datos del tutor --}}
                <section>
                    <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                        Datos del tutor empresarial
                    </h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="nombreTutor"
                                   class="mb-1 block text-sm font-medium">
                                Nombre del tutor *
                            </label>

                            <input id="nombreTutor"
                                   name="nombreTutor"
                                   type="text"
                                   maxlength="30"
                                   value="{{ old('nombreTutor', $carta->nombreTutor) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="apellidoTutor"
                                   class="mb-1 block text-sm font-medium">
                                Apellido del tutor *
                            </label>

                            <input id="apellidoTutor"
                                   name="apellidoTutor"
                                   type="text"
                                   maxlength="30"
                                   value="{{ old('apellidoTutor', $carta->apellidoTutor) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="telefonoTutor"
                                   class="mb-1 block text-sm font-medium">
                                Teléfono del tutor *
                            </label>

                            <input id="telefonoTutor"
                                   name="telefonoTutor"
                                   type="tel"
                                   maxlength="30"
                                   value="{{ old('telefonoTutor', $carta->telefonoTutor) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="correoTutor"
                                   class="mb-1 block text-sm font-medium">
                                Correo del tutor *
                            </label>

                            <input id="correoTutor"
                                   name="correoTutor"
                                   type="email"
                                   maxlength="100"
                                   value="{{ old('correoTutor', $carta->correoTutor) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                    </div>
                </section>

                {{-- Fechas --}}
                <section>
                    <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                        Periodo de práctica
                    </h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="fechaInicio"
                                   class="mb-1 block text-sm font-medium">
                                Fecha prevista de inicio *
                            </label>

                            <input id="fechaInicio"
                                   name="fechaInicio"
                                   type="date"
                                   value="{{ old('fechaInicio', $carta->fechaInicio ? \Carbon\Carbon::parse($carta->fechaInicio)->format('Y-m-d') : '') }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label for="fechaFin"
                                   class="mb-1 block text-sm font-medium">
                                Fecha prevista de finalización
                            </label>

                            <input id="fechaFin"
                                   name="fechaFin"
                                   type="date"
                                   value="{{ old('fechaFin', $carta->fechaFin ? \Carbon\Carbon::parse($carta->fechaFin)->format('Y-m-d') : '') }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                    </div>
                </section>

                {{-- Archivo --}}
                <section>
                    <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                        Documento de respaldo
                    </h2>

                    <label for="documento"
                           class="mb-2 block text-sm font-medium">
                        Adjunta la carta o contrato que confirma tu aceptación *
                    </label>

                    {{-- Mostrar el documento actualmente guardado --}}
                    @if ($carta->rutaArchivo)
                    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="mb-2 text-sm font-medium text-slate-700">
                            Documento actual
                        </p>
                        
                        <a href="{{ asset('storage/' . $carta->rutaArchivo) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-sm font-medium text-teal-700 hover:text-teal-900 hover:underline">
                            Ver documento adjunto
                        </a>
                    </div>
                    @else
                        <p class="mb-3 text-sm text-slate-500">
                                Esta solicitud no tiene un documento adjunto.
                            </p>
                    @endif
                    
                    {{-- Permitir adjuntar un documento nuevo --}}
                    <input id="documento"
                        name="documento"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full rounded-lg border border-slate-300 p-3 text-sm file:mr-4 
                        file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-medium file:text-teal-700
                         hover:file:bg-teal-100">
                        <p class="mt-2 text-xs text-slate-500">
                            Formatos permitidos: PDF, JPG o PNG. Tamaño máximo: 5 MB.
                            Si no seleccionas otro archivo, se conservará el documento actual.
                        </p>
                </section>

                {{-- Acciones --}}
                <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-200 pt-5 sm:flex-row">

                    <a href="{{ route('estudiante.aceptacion') }}"
                       class="rounded-lg border border-slate-300 px-5 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                        Guardar Cambios
                    </button>

                </div>

            </form>

            <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana
            </footer>

        </main>
    </div>
</div>

</body>
</html>