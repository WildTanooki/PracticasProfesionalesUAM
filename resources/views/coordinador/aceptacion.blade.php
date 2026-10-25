<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cartas de aprobación | UAM</title>

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

            {{-- Mensajes de resultado --}}
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

            {{-- Título --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800">
                    Solicitudes de cartas de aprobación
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Consulta y gestiona las solicitudes de cartas de aprobación
                    presentadas por los estudiantes de tu carrera.
                </p>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="overflow-x-auto">
                    <table class="w-full text-left">

                        <thead>
                            <tr class="bg-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                <th class="px-4 py-4">Estudiante</th>
                                <th class="px-4 py-4">Carrera</th>
                                <th class="px-4 py-4">Empresa</th>
                                <th class="px-4 py-4">Fecha de solicitud</th>
                                <th class="px-4 py-4">Observación</th>
                                <th class="px-4 py-4 text-center">Estado</th>
                                <th class="px-4 py-4 text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-sm">

                        @forelse ($cartas as $carta)

                            @php
                                $usuario = $carta->usuario;
                                $estudiante = $usuario?->estudiante;
                                $carrera = $estudiante?->planEstudios?->carrera;

                                $estado = $carta->estadoAprobacion ?? 'Pendiente';

                                $estiloEstado = match ($estado) {
                                    'Pendiente' => 'bg-amber-100 text-amber-800',
                                    'Aprobada' => 'bg-emerald-100 text-emerald-800',
                                    'Rechazada' => 'bg-red-100 text-red-800',
                                    'A corregir' => 'bg-orange-100 text-orange-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };

                                $nombreEstudiante = trim(
                                    ($usuario?->nombre ?? '') . ' ' .
                                    ($usuario?->apellido ?? '')
                                );
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50">

                                {{-- Estudiante --}}
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-800">
                                        {{ $nombreEstudiante !== ''
                                            ? $nombreEstudiante
                                            : 'Estudiante sin usuario asociado' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $carta->credencial ?? 'Sin credencial' }}
                                    </div>
                                </td>

                                {{-- Carrera --}}
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $carrera?->nombre ?? 'No disponible' }}
                                </td>

                                {{-- Empresa --}}
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $carta->empresaDestino ?? 'No especificada' }}
                                </td>

                                {{-- Fecha de solicitud --}}
                                <td class="whitespace-nowrap px-4 py-4 text-slate-600">
                                    {{ $carta->fechaEmision
                                        ? \Illuminate\Support\Carbon::parse($carta->fechaEmision)->format('d/m/Y')
                                        : 'N/D' }}
                                </td>

                                {{-- Observación --}}
                                <td class="max-w-56 px-4 py-4 text-slate-600">
                                    <span title="{{ $carta->observacion }}">
                                        {{ \Illuminate\Support\Str::limit(
                                            $carta->observacion ?: 'Sin observaciones',
                                            65
                                        ) }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloEstado }}">
                                        {{ $estado }}
                                    </span>
                                </td>

                                {{-- Acción --}}
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    <a
                                        href="{{ route('coordinador.aceptacion.gestionar', [
                                            'idCartaAprobacion' => $carta->idCartaAprobacion
                                        ]) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-[#5b9eaa] px-4 py-2 text-xs font-semibold text-[#397985] transition hover:bg-[#5b9eaa] hover:text-white"
                                    >
                                        {{ in_array($estado, ['Pendiente', 'A corregir'])
                                            ? 'Gestionar'
                                            : 'Ver detalle' }}
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="font-semibold text-slate-700">
                                        No hay solicitudes registradas.
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Cuando los estudiantes envíen solicitudes de
                                        cartas de aprobación, aparecerán aquí.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pie de página --}}
            <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana (UAM)
            </footer>

        </main>
    </div>
</div>

</body>
</html>