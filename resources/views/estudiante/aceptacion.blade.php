<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cartas de Aprobación | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-xl font-bold">
                        Cartas de Aprobación
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Sistema de Gestión de Prácticas Profesionales
                    </p>
                </div>
            </header>

            {{-- Contenido --}}
            <main class="mx-auto max-w-7xl px-6 py-8">

                {{-- Título y botón --}}
                <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">
                            Mis solicitudes
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consulta el estado de las cartas de aceptación que has enviado.
                        </p>
                    </div>

                    <a href="{{ route('estudiante.aceptacion.crear') }}"
                       class="inline-flex items-center justify-center rounded-full bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">
                        + Nueva Carta
                    </a>
                </div>

                {{-- Tabla --}}
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[750px] text-left text-sm">

                            <thead class="bg-slate-200/80 text-slate-700">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">
                                        Documento
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Fecha
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Estado
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Observación
                                    </th>

                                    <th class="px-5 py-4 text-center font-semibold">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">

                                @forelse ($cartas as $carta)

                                    <tr class="transition hover:bg-slate-50">

                                        {{-- Documento --}}
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-6 w-6 shrink-0 text-slate-400"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.5">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M7 3.75h7l5 5v11.5A1.75 1.75 0 0 1 17.25 22h-10.5A1.75 1.75 0 0 1 5 20.25V5.5A1.75 1.75 0 0 1 6.75 3.75Z" />
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M14 4v5h5M8 14h8M8 17h8" />
                                                </svg>

                                                <div>
                                                    <p class="font-medium text-slate-700">
                                                        Carta de aceptación
                                                    </p>

                                                    @if ($carta->empresaDestino)
                                                        <p class="mt-1 text-xs text-slate-500">
                                                            {{ $carta->empresaDestino }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Fecha --}}
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                            {{ $carta->fechaEmision
                                                ? \Illuminate\Support\Carbon::parse($carta->fechaEmision)->format('d/m/Y')
                                                : '—' }}
                                        </td>

                                        {{-- Estado --}}
                                        <td class="px-5 py-4">
                                            @php
                                                $estado = $carta->estadoAprobacion ?? 'Pendiente';

                                                $estilosEstado = [
                                                    'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    'Generada' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                    'Rechazado' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    'A corregir' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                                ];

                                                $estilo = $estilosEstado[$estado]
                                                    ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                            @endphp

                                            <span class="inline-flex whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $estilo }}">
                                                {{ $estado }}
                                            </span>
                                        </td>

                                        {{-- Observación --}}
                                        <td class="max-w-xs px-5 py-4 text-slate-600">
                                            {{ $carta->observacion ?: '—' }}
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="whitespace-nowrap px-5 py-4 text-center">
                                            @if ($carta->rutaArchivo)
                                                <a href="{{ asset('storage/' . $carta->rutaArchivo) }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 font-medium text-teal-700 hover:text-teal-900 hover:underline">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-4 w-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor"
                                                         stroke-width="1.8">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                                                        <circle cx="12" cy="12" r="2.5" />
                                                    </svg>

                                                    Ver documento
                                                </a>
                                            @else
                                                <span class="text-slate-400">
                                                    Sin archivo
                                                </span>
                                            @endif

                                            {{-- Editar solicitud --}}
                                            @if (in_array($carta->estadoAprobacion, ['Pendiente', 'A corregir']))
                                                <a href="{{ route('estudiante.aceptacion.editar', $carta->idCartaAprobacion) }}"
                                                class="inline-flex items-center gap-1 font-medium text-amber-600 hover:text-amber-800 hover:underline">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8">
                                                        <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m16.862 4.487 2.651 2.651M8.5 15.5l8.362-8.362a1.875 1.875 0 0 1 2.652 2.652L11.15 18.154a4.5 4.5 0 0 1-1.897 1.13L5.5 20.5l1.216-3.753a4.5 4.5 0 0 1 1.784-1.247Z" />
                                                    </svg>
                                                    Editar
                                                </a>
                                            @endif

                                           
                                        {{-- Eliminar solicitud --}}
                                        @if ($carta->estadoAprobacion === 'Pendiente')
                                            <form action="{{ route('estudiante.aceptacion.eliminar', $carta->idCartaAprobacion) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta solicitud? Esta acción no se puede deshacer.');"
                                                  class="inline">
                                        
                                                @csrf
                                                @method('DELETE')
                                        
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 font-medium text-red-600 hover:text-red-800 hover:underline">
                                        
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-4 w-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor"
                                                         stroke-width="1.8">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M6 7h12m-10 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13H9L8 7m3 4v5m2-5v5" />
                                                    </svg>
                                        
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif


                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="mb-3 h-10 w-10 text-slate-300"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.5">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M7 3.75h7l5 5v11.5A1.75 1.75 0 0 1 17.25 22h-10.5A1.75 1.75 0 0 1 5 20.25V5.5A1.75 1.75 0 0 1 6.75 3.75Z" />
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M14 4v5h5" />
                                                </svg>

                                                <p class="font-medium text-slate-700">
                                                    No tienes solicitudes registradas
                                                </p>

                                                <p class="mt-1 text-sm text-slate-500">
                                                    Selecciona «Nueva Carta» para enviar tu primera solicitud.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    {{-- Total de solicitudes --}}
                    @if ($cartas->isNotEmpty())
                        <div class="border-t border-slate-200 px-5 py-3 text-xs text-slate-500">
                            Total de solicitudes: {{ $cartas->count() }}
                        </div>
                    @endif

                </div>

                {{-- Pie de página --}}
                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>

    </div>

</body>
</html>