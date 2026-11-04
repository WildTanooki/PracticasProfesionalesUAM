<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notificaciones - UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    // Etiqueta del nuevo estado del documento.
    $estadosNotificacion = [
        'Aprobado' => ['texto' => 'Aprobado', 'clase' => 'bg-emerald-50 text-emerald-700'],
        'A corregir' => ['texto' => 'Requiere corrección', 'clase' => 'bg-orange-50 text-orange-700'],
        'Rechazado' => ['texto' => 'Rechazado', 'clase' => 'bg-red-50 text-red-700'],
    ];
@endphp

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
            <main class="mx-auto w-full max-w-4xl flex-1 px-8 py-8">

                {{-- Título --}}
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">
                            Notificaciones
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Avisos de Coordinación Académica sobre la revisión de tus documentos.
                        </p>
                    </div>

                    @if ($noLeidas > 0)
                        <form action="{{ route('estudiante.notificaciones.leer-todas') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Marcar todas como leídas ({{ $noLeidas }})
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Mensajes --}}
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($notificaciones->isEmpty())

                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center">
                        <p class="font-semibold text-slate-700">
                            No tienes notificaciones
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Aquí verás cuando Coordinación Académica apruebe, rechace o pida corregir uno de tus documentos.
                        </p>
                    </div>

                @else

                    <ul class="space-y-3">
                        @foreach ($notificaciones as $notificacion)
                            @php
                                $estado = $estadosNotificacion[$notificacion->estadoDocumento]
                                    ?? ['texto' => $notificacion->estadoDocumento, 'clase' => 'bg-slate-100 text-slate-600'];
                            @endphp

                            <li class="rounded-xl border bg-white p-5 shadow-sm
                                {{ $notificacion->leida ? 'border-slate-200' : 'border-[#5b9eaa] ring-1 ring-[#5b9eaa]/30' }}">

                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 font-semibold text-slate-800">
                                            @unless ($notificacion->leida)
                                                <span class="h-2 w-2 shrink-0 rounded-full bg-[#5b9eaa]" aria-hidden="true"></span>
                                                <span class="sr-only">Sin leer:</span>
                                            @endunless
                                            {{ $notificacion->titulo }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Documento: <span class="font-medium text-slate-700">{{ $notificacion->nombreDocumento }}</span>
                                            · {{ $notificacion->fechaCreacion?->format('d/m/Y H:i') }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $estado['clase'] }}">
                                        {{ $estado['texto'] }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm text-slate-600">
                                    {{ $notificacion->mensaje }}
                                </p>

                                @if ($notificacion->observacion)
                                    <p class="mt-3 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                        <span class="font-semibold">Observación de Coordinación Académica:</span>
                                        {{ $notificacion->observacion }}
                                    </p>
                                @endif

                                <form action="{{ route('estudiante.notificaciones.abrir', $notificacion->idNotificacion) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm font-semibold text-[#397783] transition hover:text-[#5b9eaa]">
                                        Ver documento →
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        {{ $notificaciones->links() }}
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
