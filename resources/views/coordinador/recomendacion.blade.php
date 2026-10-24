<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cartas de recomendación | UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

<div class="flex min-h-screen">

    {{-- Ajusta el componente si tu menú tiene otro nombre --}}
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

            {{-- Mensaje de resultado --}}
            @if (session('success'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800">
                    Solicitudes de carta de recomendación
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Consulta y gestiona las solicitudes enviadas por los estudiantes.
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

                        @forelse ($solicitudes as $solicitud)

                            @php
                                $usuario = $solicitud->estudiante?->usuario;
                                $carrera = $solicitud->estudiante?->planEstudios?->carrera;
                                $estado = $solicitud->estadoCarta ?? 'Pendiente';
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50">

                                {{-- Estudiante --}}
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-800">
                                        {{ $usuario?->nombre ?? 'Estudiante sin usuario asociado' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $solicitud->credencialEstudiante }}
                                    </div>
                                </td>

                                {{-- Carrera --}}
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $carrera?->nombre ?? 'No disponible' }}
                                </td>

                                {{-- Empresa --}}
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $solicitud->empresaDestino }}
                                </td>

                                {{-- Fecha --}}
                                <td class="whitespace-nowrap px-4 py-4 text-slate-600">
                                    {{ $solicitud->fechaSolicitud
                                        ? $solicitud->fechaSolicitud->format('d/m/Y')
                                        : 'N/D' }}
                                </td>

                                {{-- Observación del estudiante --}}
                                <td class="max-w-56 px-4 py-4 text-slate-600">
                                    <span title="{{ $solicitud->observacionEstudiante }}">
                                        {{ \Illuminate\Support\Str::limit(
                                            $solicitud->observacionEstudiante ?: 'Sin observaciones',
                                            65
                                        ) }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    @php
                                        $estiloEstado = match ($estado) {
                                            'Pendiente' => 'bg-amber-100 text-amber-800',
                                            'Generada' => 'bg-emerald-100 text-emerald-800',
                                            'Rechazada' => 'bg-red-100 text-red-800',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloEstado }}">
                                        {{ $estado }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    <a
                                        href="{{ route('coordinador.recomendacion.show', [
                                            'idCartaRecomendacion' => $solicitud->idCartaRecomendacion
                                        ]) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-[#5b9eaa] px-4 py-2 text-xs font-semibold text-[#397985] transition hover:bg-[#5b9eaa] hover:text-white"
                                    >
                                        {{ $estado === 'Pendiente' ? 'Gestionar' : 'Ver detalle' }}
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
                                        Cuando se reciban solicitudes de cartas de recomendación, aparecerán aquí.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana (UAM)
            </footer>

        </main>
    </div>
</div>

</body>
</html>