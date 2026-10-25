<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Revisión de documentos | UAM</title>

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

            @if (session('success'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Revisión de documentos
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Documentos enviados por los estudiantes de tu carrera.
                    </p>
                </div>

                {{-- Filtro por estado --}}
                <div class="flex flex-wrap gap-2">
                    @foreach (array_merge($estados, ['Todos']) as $estadoFiltro)
                        <a href="{{ route('coordinador.documentos', ['estado' => $estadoFiltro]) }}"
                           class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors
                               {{ $estado === $estadoFiltro
                                   ? 'bg-[#5b9eaa] text-white'
                                   : 'bg-white text-slate-600 hover:bg-slate-200' }}">
                            {{ $estadoFiltro }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] border-collapse text-left">

                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                <th scope="col" class="px-6 py-4">Estudiante</th>
                                <th scope="col" class="px-6 py-4">Pasantía</th>
                                <th scope="col" class="px-6 py-4">Formato</th>
                                <th scope="col" class="px-6 py-4 text-center">Versión</th>
                                <th scope="col" class="px-6 py-4">Enviado</th>
                                <th scope="col" class="px-6 py-4">Estado</th>
                                <th scope="col" class="px-6 py-4 text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-sm">

                            @forelse ($documentos as $documentoFila)

                                @php
                                    $usuarioFila = $documentoFila->pasantia?->practicaProfesional?->estudiante?->usuario;

                                    $estiloFila = match ($documentoFila->estadoArchivo) {
                                        'Aprobado' => 'bg-emerald-50 text-emerald-700',
                                        'Pendiente' => 'bg-amber-50 text-amber-700',
                                        'A corregir' => 'bg-orange-50 text-orange-700',
                                        'Rechazado' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp

                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-800">
                                            {{ trim(($usuarioFila?->nombre ?? '') . ' ' . ($usuarioFila?->apellido ?? '')) ?: '—' }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $usuarioFila?->credencial }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $documentoFila->pasantia?->empresa }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-800">
                                        {{ $documentoFila->formato?->nombreFormato ?? 'Documento' }}
                                    </td>

                                    <td class="px-6 py-4 text-center text-slate-600">
                                        {{ $documentoFila->ultimaVersion?->version ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $documentoFila->ultimaVersion?->fechaEnvio?->format('d/m/Y H:i') ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloFila }}">
                                            {{ $documentoFila->estadoArchivo }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('coordinador.documentos.show', $documentoFila->idDocumento) }}"
                                           class="inline-flex rounded-lg bg-[#5b9eaa] px-4 py-2 font-medium text-white transition-colors hover:bg-[#4c848f]">
                                            {{ $documentoFila->estadoArchivo === 'Pendiente' ? 'Revisar' : 'Ver' }}
                                        </a>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        No hay documentos con este estado.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
