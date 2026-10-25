<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Historial del documento - UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        {{-- Menú lateral del estudiante --}}
        <x-sidebarestudiante />

        <div class="min-w-0 flex-1 flex flex-col">

            {{-- Navbar superior --}}
            <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm">
                <span class="text-sm font-semibold text-[#5b9eaa]">
                    Panel
                    <span class="font-bold text-slate-800">
                        del Estudiante
                    </span>
                </span>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-8 py-8">

                <a href="{{ route('estudiante.documentos', ['pasantia' => $documento->idPasantia]) }}"
                   class="text-sm font-semibold text-slate-500 transition hover:text-[#5b9eaa]">
                    ← Volver a mis documentos
                </a>

                <div class="mb-6 mt-4">
                    <h1 class="text-2xl font-bold text-slate-800">
                        {{ $documento->formato?->nombreFormato ?? 'Documento' }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $documento->pasantia?->empresa }} · {{ $documento->pasantia?->cargo }}
                    </p>
                </div>

                {{-- Estado vigente --}}
                <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Estado actual</p>

                    <p class="mt-1 text-lg font-semibold text-slate-800">
                        {{ $documento->estadoArchivo }}
                    </p>

                    @if ($documento->observacion)
                        <p class="mt-3 text-sm text-slate-600">
                            <span class="font-semibold">Observación:</span>
                            {{ $documento->observacion }}
                        </p>
                    @endif

                    @if ($permiteReenvio)
                        <a href="{{ route('estudiante.documentos', ['pasantia' => $documento->idPasantia]) }}"
                           class="mt-4 inline-flex rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#4c848f]">
                            Enviar nueva versión
                        </a>
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
                        'rutaDescarga' => 'estudiante.documentos.descargar',
                    ])
                </div>

                <footer class="mt-12 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                    Sistema de Gestión de Prácticas Profesionales
                    · Universidad Americana (UAM)
                </footer>

            </main>
        </div>
    </div>

</body>
</html>
