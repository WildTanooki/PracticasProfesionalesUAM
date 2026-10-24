
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nueva Carta de Recomendación | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral --}}
        <x-sidebarestudiante />

        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div class="mx-auto max-w-5xl">
                    <h1 class="text-xl font-bold">
                        Nueva Carta de Recomendación
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Sistema de Gestión de Prácticas Profesionales
                    </p>
                </div>
            </header>

            {{-- Contenido --}}
            <main class="mx-auto max-w-5xl px-6 py-8">

                {{-- Volver --}}
                <a href="{{ route('estudiante.recomendacion') }}"
                   class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-teal-700 hover:underline">
                    ← Volver a mis solicitudes
                </a>

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">
                            Revisa los siguientes campos:
                        </p>

                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario --}}
                <form action="{{ route('estudiante.recomendacion.store') }}"
                      method="POST"
                      class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                    @csrf

                    {{-- Información --}}
                    <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                        <h2 class="font-semibold text-teal-900">
                            Información de la solicitud
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-teal-800">
                            Completa los datos de la empresa y el puesto que ocuparás.
                            Coordinación Académica utilizará esta información para
                            preparar tu carta formal de recomendación.
                        </p>
                    </div>

                    {{-- Datos de la empresa --}}
                    <section>
                        <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                            Datos de la empresa
                        </h2>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Empresa --}}
                            <div>
                                <label for="empresaDestino"
                                       class="mb-1 block text-sm font-medium text-slate-700">
                                    Nombre de la empresa *
                                </label>

                                <input id="empresaDestino"
                                       name="empresaDestino"
                                       type="text"
                                       maxlength="50"
                                       value="{{ old('empresaDestino') }}"
                                       required
                                       autocomplete="organization"
                                       placeholder="Ej. Empresa ABC"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">

                                @error('empresaDestino')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Puesto --}}
                            <div>
                                <label for="puestoCargo"
                                       class="mb-1 block text-sm font-medium text-slate-700">
                                    Puesto o cargo *
                                </label>

                                <input id="puestoCargo"
                                       name="puestoCargo"
                                       type="text"
                                       maxlength="50"
                                       value="{{ old('puestoCargo') }}"
                                       required
                                       autocomplete="organization-title"
                                       placeholder="Ej. Asistente de sistemas"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">

                                @error('puestoCargo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </section>

                    {{-- Observaciones --}}
                    <section>
                        <h2 class="mb-4 border-b border-slate-200 pb-3 text-lg font-semibold">
                            Observaciones
                        </h2>

                        <div>
                            <label for="observacionEstudiante"
                                   class="mb-1 block text-sm font-medium text-slate-700">
                                Observaciones adicionales
                            </label>

                            <textarea id="observacionEstudiante"
                                      name="observacionEstudiante"
                                      rows="4"
                                      maxlength="100"
                                      placeholder="Escribe alguna indicación que deba conocer Coordinación Académica."
                                      class="w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('observacionEstudiante') }}</textarea>

                            <p class="mt-1 text-xs text-slate-500">
                                Campo opcional. Máximo 100 caracteres.
                            </p>

                            @error('observacionEstudiante')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- Aviso --}}
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm leading-6 text-amber-800">
                            <span class="font-semibold">Importante:</span>
                            al enviar la solicitud, su estado será
                            <span class="font-semibold">Pendiente</span>.
                            Podrás consultar el estado desde tus solicitudes.
                            No necesitas adjuntar ningún documento.
                        </p>
                    </div>

                    {{-- Botones --}}
                    <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-200 pt-5 sm:flex-row">

                        <a href="{{ route('estudiante.recomendacion') }}"
                           class="rounded-lg border border-slate-300 px-5 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            Enviar solicitud
                        </button>

                    </div>

                </form>

                {{-- Pie de página --}}
                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>
    </div>

</body>
</html>