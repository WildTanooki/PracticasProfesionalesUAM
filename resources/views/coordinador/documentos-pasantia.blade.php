<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de la pasantía | Coordinación</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="flex min-h-screen">
    <x-sidebarcoordinador />

    <div class="min-w-0 flex-1">
        <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="mx-auto max-w-7xl">
                <h1 class="text-xl font-bold">Panel de Coordinación Académica</h1>
                <p class="mt-1 text-sm text-slate-500">Sistema de Gestión de Prácticas Profesionales</p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6">
            @php
                $estudiante = $pasantia->practicaProfesional?->estudiante;
                $usuarioEstudiante = $estudiante?->usuario;
                $nombreEstudiante = trim(
                    ($usuarioEstudiante?->nombre ?? '') . ' ' .
                    ($usuarioEstudiante?->apellido ?? '')
                );

                if ($nombreEstudiante === '') {
                    $nombreEstudiante = $estudiante?->credencial ?? 'Sin estudiante';
                }

                $carrera = $estudiante?->planEstudios?->carrera?->nombre ?? 'Sin carrera';
                $totalFormatos = (int) ($formatosRequeridosTotal ?? 0);
                $totalAprobados = (int) ($formatosAprobados ?? 0);
                $porcentaje = $totalFormatos > 0
                    ? min(100, (int) round(($totalAprobados / $totalFormatos) * 100))
                    : 0;
            @endphp

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('coordinador.home') }}"
                       class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">
                        ← Volver al panel
                    </a>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Documentos de la pasantía</h2>
                    <p class="mt-1 text-sm text-slate-500">Pasantía #{{ $pasantia->idPasantia }}</p>
                </div>

                <span class="rounded-full px-4 py-2 text-sm font-semibold
                    {{ $estadoGlobal === 'Aprobados'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-amber-100 text-amber-800' }}">
                    {{ $estadoGlobal }}
                </span>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Estudiante</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $nombreEstudiante }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $estudiante?->credencial ?? 'Sin credencial' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Carrera</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $carrera }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Empresa</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $pasantia->empresa ?: 'Sin empresa' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Horas acumuladas</p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $pasantia->horasAcumuladas ?? 0 }} / {{ $pasantia->horasRequeridas ?? 0 }}
                    </p>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Progreso documental</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Formatos aprobados respecto al total de formatos requeridos.
                        </p>
                    </div>
                    <p class="text-lg font-bold text-slate-800">
                        {{ $totalAprobados }} / {{ $totalFormatos }}
                    </p>
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-cyan-600 transition-all"
                         style="width: {{ $porcentaje }}%"></div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Formatos entregados</p>
                        <p class="mt-1 text-xl font-bold text-slate-800">{{ $formatosEntregados  }}</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4">
                        <p class="text-sm text-green-700">Formatos aprobados</p>
                        <p class="mt-1 text-xl font-bold text-green-800">{{ $formatosAprobados }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-4">
                        <p class="text-sm text-amber-700">Pendientes de revisión</p>
                        <p class="mt-1 text-xl font-bold text-amber-800">{{ $documentosPendientes }}</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-4">
                        <p class="text-sm text-red-700">A corregir o rechazados</p>
                        <p class="mt-1 text-xl font-bold text-red-800">
                            {{ $documentosACorregir + $documentosRechazados }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Documentos asociados</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Abre un documento para consultar su historial y realizar la revisión.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Documento</th>
                            <th class="px-5 py-4 font-semibold">Estado</th>
                            <th class="px-5 py-4 font-semibold">Observación</th>
                            <th class="px-5 py-4 text-center font-semibold">Acción</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse ($documentos as $documento)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        {{ $documento->formato?->nombre ?? 'Documento #' . $documento->idDocumento }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">ID: {{ $documento->idDocumento }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $claseEstado = match ($documento->estadoArchivo) {
                                            'Aprobado' => 'bg-green-100 text-green-800',
                                            'Rechazado' => 'bg-red-100 text-red-800',
                                            'A corregir' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $claseEstado }}">
                                        {{ $documento->estadoArchivo ?? 'Sin estado' }}
                                    </span>
                                </td>
                                <td class="max-w-sm px-5 py-4 text-slate-600">
                                    {{ $documento->observacion ?: 'Sin observaciones' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('coordinador.documentos.show', $documento->idDocumento) }}"
                                       class="inline-flex items-center justify-center rounded-lg bg-cyan-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-cyan-800">
                                        Revisar documento
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <p class="font-semibold text-slate-700">No hay documentos asociados</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Todavía no se han registrado documentos para esta pasantía.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana
            </footer>
        </main>
    </div>
</div>
</body>
</html>
