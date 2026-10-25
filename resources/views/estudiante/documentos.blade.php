<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subir documentos - UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        {{-- Menú lateral del estudiante --}}
        <x-sidebarestudiante />

        <div class="min-w-0 flex-1 flex flex-col">

            {{-- Navbar superior --}}
            <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
                <div>
                    <span class="text-sm font-semibold text-[#5b9eaa]">
                        Panel
                        <span class="font-bold text-slate-800">
                            del Estudiante
                        </span>
                    </span>

                    <p class="text-xs text-slate-500">
                        Gestión de prácticas profesionales
                    </p>
                </div>
            </header>

            {{-- Contenido principal --}}
            <main class="mx-auto w-full max-w-7xl flex-1 px-8 py-8">

                {{-- Título --}}
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        Documentos de pasantía
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Sube el documento correspondiente a cada formato requerido y consulta su revisión.
                    </p>
                </div>

                {{-- Mensajes --}}
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($pasantias->isEmpty())

                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center">
                        <p class="font-semibold text-slate-700">
                            No tienes pasantías registradas
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Podrás subir documentos cuando Coordinación Académica registre tu pasantía.
                        </p>
                    </div>

                @else

                    @php
                        // Formatos sin documento o con reenvío permitido.
                        $formatosDisponibles = $formatos->filter(function ($formatoFila) use ($documentos, $gestionDocumental) {
                            $documentoFila = $documentos->get($formatoFila->idFormato);

                            return $documentoFila === null
                                || $gestionDocumental->permiteReenvio($documentoFila);
                        });
                    @endphp

                    {{-- Selección de pasantía --}}
                    @if ($pasantias->count() > 1)
                        <form method="GET"
                              action="{{ route('estudiante.documentos') }}"
                              class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                            <div class="min-w-64 flex-1">
                                <label for="pasantia" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Pasantía
                                </label>

                                <select id="pasantia"
                                        name="pasantia"
                                        onchange="this.form.submit()"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]/30">
                                    @foreach ($pasantias as $pasantiaOpcion)
                                        <option value="{{ $pasantiaOpcion->idPasantia }}"
                                            @selected($pasantiaOpcion->idPasantia === $pasantia->idPasantia)>
                                            {{ $pasantiaOpcion->empresa }} · {{ $pasantiaOpcion->cargo }} ({{ $pasantiaOpcion->estado }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <noscript>
                                <button type="submit"
                                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                                    Ver
                                </button>
                            </noscript>
                        </form>
                    @endif

                    {{-- Formulario de carga --}}
                    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="font-semibold text-slate-800">
                            Subir documento
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $pasantia->empresa }} · {{ $pasantia->cargo }}
                        </p>

                        @if ($formatosDisponibles->isEmpty())

                            <p class="mt-4 text-sm text-slate-500">
                                No hay formatos pendientes de envío para esta pasantía.
                            </p>

                        @else

                            <form method="POST"
                                  action="{{ route('estudiante.documentos.store', $pasantia->idPasantia) }}"
                                  enctype="multipart/form-data"
                                  class="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                                @csrf

                                <div>
                                    <label for="idFormato" class="mb-1 block text-sm font-semibold text-slate-700">
                                        Formato
                                    </label>

                                    <select id="idFormato"
                                            name="idFormato"
                                            required
                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]/30">
                                        <option value="">Selecciona un formato</option>

                                        @foreach ($formatosDisponibles as $formatoOpcion)
                                            @php
                                                $documentoOpcion = $documentos->get($formatoOpcion->idFormato);
                                            @endphp

                                            <option value="{{ $formatoOpcion->idFormato }}"
                                                @selected((int) old('idFormato') === $formatoOpcion->idFormato)>
                                                {{ $formatoOpcion->nombreFormato }}{{ $documentoOpcion ? ' (nueva versión)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="archivo" class="mb-1 block text-sm font-semibold text-slate-700">
                                        Archivo
                                    </label>

                                    <input id="archivo"
                                           name="archivo"
                                           type="file"
                                           required
                                           accept=".pdf,.doc,.docx"
                                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">

                                    <p class="mt-1 text-xs text-slate-400">
                                        PDF o Word, máximo 5 MB.
                                    </p>
                                </div>

                                <button type="submit"
                                        class="rounded-lg bg-[#5b9eaa] px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-[#4c848f] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:ring-offset-2">
                                    Enviar
                                </button>
                            </form>

                        @endif
                    </section>

                    {{-- Estado por formato --}}
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                            <h2 class="font-semibold text-slate-800">
                                Formatos requeridos
                            </h2>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">

                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                        <th scope="col" class="px-6 py-4">Formato</th>
                                        <th scope="col" class="px-6 py-4">Estado</th>
                                        <th scope="col" class="px-6 py-4 text-center">Versión</th>
                                        <th scope="col" class="px-6 py-4">Observación</th>
                                        <th scope="col" class="px-6 py-4 text-center">Acción</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-200 text-sm">

                                    @forelse ($formatos as $formatoFila)

                                        @php
                                            $documentoFila = $documentos->get($formatoFila->idFormato);
                                            $estadoFila = $documentoFila?->estadoArchivo ?? 'Sin enviar';

                                            $estiloFila = match ($estadoFila) {
                                                'Aprobado' => 'bg-emerald-50 text-emerald-700',
                                                'Pendiente' => 'bg-amber-50 text-amber-700',
                                                'A corregir' => 'bg-orange-50 text-orange-700',
                                                'Rechazado' => 'bg-red-50 text-red-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp

                                        <tr class="transition-colors hover:bg-slate-50/80">
                                            <td class="px-6 py-4 font-medium text-slate-800">
                                                {{ $formatoFila->nombreFormato }}
                                            </td>

                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloFila }}">
                                                    {{ $estadoFila }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-center text-slate-600">
                                                {{ $documentoFila?->ultimaVersion?->version ?? '—' }}
                                            </td>

                                            <td class="px-6 py-4 text-slate-600">
                                                {{ $documentoFila?->observacion ?: '—' }}
                                            </td>

                                            <td class="px-6 py-4 text-center">
                                                @if ($documentoFila)
                                                    <a href="{{ route('estudiante.documentos.show', $documentoFila->idDocumento) }}"
                                                       class="font-medium text-[#5b9eaa] hover:underline">
                                                        Ver historial
                                                    </a>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center">
                                                <p class="font-medium text-slate-700">
                                                    No hay formatos requeridos.
                                                </p>

                                                <p class="mt-2 text-sm text-slate-500">
                                                    Actualmente no existen formatos activos para tu carrera.
                                                </p>
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>

                @endif

                {{-- Footer --}}
                <footer class="mt-12 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                    Sistema de Gestión de Prácticas Profesionales
                    · Universidad Americana (UAM)
                </footer>

            </main>
        </div>
    </div>

</body>
</html>
