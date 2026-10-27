<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Coordinador | Prácticas UAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="flex min-h-screen">
    <x-sidebarcoordinador />

    <div class="min-w-0 flex-1">
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

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <section class="mb-7">
                <h2 class="text-2xl font-bold text-slate-900">
                    Bienvenido/a, {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Gestiona las prácticas profesionales y revisa los documentos enviados por los estudiantes.
                </p>
            </section>

            @if (session('success'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <section class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Por revisar</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $porRevisar }}</p>
                    <p class="mt-1 text-xs text-slate-500">Documentos pendientes</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Aprobados</p>
                    <p class="mt-2 text-3xl font-bold text-green-700">{{ $aprobados }}</p>
                    <p class="mt-1 text-xs text-slate-500">Documentos aprobados</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Rechazados</p>
                    <p class="mt-2 text-3xl font-bold text-red-700">{{ $desaprobados }}</p>
                    <p class="mt-1 text-xs text-slate-500">Documentos rechazados</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">A corregir</p>
                    <p class="mt-2 text-3xl font-bold text-orange-700">{{ $aCorregir }}</p>
                    <p class="mt-1 text-xs text-slate-500">Documentos que requieren cambios</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5">
                    <h2 class="text-lg font-bold text-slate-800">Pasantías profesionales</h2>
                    <p class="mt-1 text-sm text-slate-500">Busca una pasantía y consulta todos sus documentos.</p>
                </div>

                <div class="border-b border-slate-200 p-5">
                    <form action="{{ route('coordinador.home') }}" method="GET"
                          class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            type="search"
                            name="buscar"
                            value="{{ $buscar }}"
                            placeholder="Buscar estudiante, empresa o carrera..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                        >

                        <select
                            name="estado"
                            onchange="this.form.submit()"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        >
                            <option value="">Todos los estados</option>
                            <option value="Pendientes" @selected($estado === 'Pendientes')>Pendientes</option>
                            <option value="Aprobados" @selected($estado === 'Aprobados')>Aprobados</option>
                        </select>

                        <button type="submit"
                                class="rounded-lg bg-cyan-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-800">
                            Buscar
                        </button>

                        @if ($buscar !== '' || $estado !== '')
                            <a href="{{ route('coordinador.home') }}"
                               class="rounded-lg border border-slate-200 px-4 py-3 text-center text-sm text-slate-600 hover:bg-slate-50">
                                Limpiar
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Estudiante</th>
                            <th class="px-5 py-4 font-semibold">Carrera</th>
                            <th class="px-5 py-4 font-semibold">Empresa</th>
                            <th class="px-5 py-4 text-center font-semibold">Horas</th>
                            <th class="px-5 py-4 font-semibold">Documentos aprobados/entregados</th>
                            <th class="px-5 py-4 font-semibold">Estado documental</th>
                            <th class="px-5 py-4 text-center font-semibold">Acción</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @forelse ($pasantias as $pasantia)
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
                                $empresa = $pasantia->empresa ?: 'Sin empresa';
                                $horas = $pasantia->horasAcumuladas ?? 0;
                                $totalFormatos = (int) ($pasantia->totalFormatos ?? 0);
                                $entregados = (int) ($pasantia->documentosEntregados ?? 0);
                                $aprobadosPasantia = (int) ($pasantia->documentosAprobados ?? 0);
                                $porcentaje = $totalFormatos > 0
                                    ? min(100, (int) round(($aprobadosPasantia / $totalFormatos) * 100))
                                    : 0;
                                $estadoPasantia = $pasantia->estadoDocumental ?? 'Pendientes';
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-100 font-semibold text-violet-700">
                                            {{ mb_strtoupper(mb_substr($nombreEstudiante, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $nombreEstudiante }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $estudiante?->credencial ?? 'Sin credencial' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $carrera }}</td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $empresa }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="font-semibold">{{ $horas }}</span>
                                    <span class="text-xs text-slate-500">h</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="font-semibold text-slate-700">
                                            {{ $aprobadosPasantia }} / {{ $totalFormatos }} aprobados
                                        </span>
                                        <span class="text-xs text-slate-500">{{ $porcentaje }}%</span>
                                    </div>
                                    <div class="h-1.5 w-36 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-cyan-600 transition-all"
                                             style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $entregados }} formato(s) entregado(s)
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($estadoPasantia === 'Aprobados')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Aprobados
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                            Pendientes
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('coordinador.pasantias.documentos', ['idPasantia' => $pasantia->idPasantia]) }}"
                                       class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-cyan-700 px-3 py-2 text-xs font-semibold text-cyan-800 transition hover:bg-cyan-700 hover:text-white">
                                        Ver documentos
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <p class="font-semibold text-slate-700">No se encontraron pasantías</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Prueba con otro término de búsqueda o filtro.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-5 py-3">
                    <p class="text-xs text-slate-500">
                        Pasantías encontradas:
                        <span class="font-semibold text-slate-700">{{ $pasantias->count() }}</span>
                    </p>
                </div>
            </section>

            <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                Sistema de Gestión de Prácticas Profesionales · Universidad Americana
            </footer>
        </main>
    </div>
</div>
</body>
</html>
