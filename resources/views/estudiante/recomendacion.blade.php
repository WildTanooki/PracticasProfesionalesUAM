<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cartas de Recomendación | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            {{-- Encabezado --}}
            <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-xl font-bold">
                        Cartas de Recomendación
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Sistema de Gestión de Prácticas Profesionales
                    </p>
                </div>
            </header>

            {{-- Contenido --}}
            <main class="mx-auto max-w-7xl px-6 py-8">

                {{-- Mensajes --}}
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Título y botón --}}
                <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">
                            Mis solicitudes
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consulta el estado de tus solicitudes de cartas de recomendación.
                        </p>
                    </div>

                    <a href="{{ route('estudiante.recomendacion.crear') }}"
                       class="inline-flex items-center justify-center rounded-full bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">
                        + Nueva solicitud
                    </a>
                </div>

                {{-- Tabla --}}
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[950px] text-left text-sm">

                            <thead class="bg-slate-200/80 text-slate-700">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">
                                        Solicitud
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Fecha
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Estado
                                    </th>

                                    <th class="px-5 py-4 font-semibold">
                                        Observaciones
                                    </th>

                                    <th class="px-5 py-4 text-center font-semibold">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">

                                @forelse ($cartas as $carta)

                                    <tr class="transition hover:bg-slate-50">

                                        {{-- Datos de la solicitud --}}
                                        <td class="px-5 py-4">
                                            <div class="flex items-start gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="mt-1 h-6 w-6 shrink-0 text-slate-400"
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
                                                        Carta de recomendación
                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-500">
                                                        Empresa:
                                                        {{ $carta->empresaDestino ?: 'No especificada' }}
                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-500">
                                                        Cargo:
                                                        {{ $carta->puestoCargo ?: 'No especificado' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Fecha de solicitud --}}
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                            {{ $carta->fechaSolicitud
                                                ? \Illuminate\Support\Carbon::parse($carta->fechaSolicitud)->format('d/m/Y')
                                                : '—' }}
                                        </td>

                                        {{-- Estado --}}
                                        <td class="px-5 py-4">
                                            @php
                                                $estado = $carta->estadoCarta ?? 'Pendiente';

                                                $estilosEstado = [
                                                    'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    'Generada' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                    'Rechazada' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                ];

                                                $estilo = $estilosEstado[$estado]
                                                    ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                            @endphp

                                            <span class="inline-flex whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $estilo }}">
                                                {{ $estado }}
                                            </span>
                                        </td>

                                        {{-- Observaciones --}}
                                        <td class="max-w-xs px-5 py-4">
                                            @if ($carta->observacionCoordinador)
                                                <p class="font-medium text-slate-700">
                                                    Coordinación:
                                                </p>

                                                <p class="mt-1 break-words text-slate-600">
                                                    {{ $carta->observacionCoordinador }}
                                                </p>
                                            @endif

                                            @if ($carta->observacionEstudiante)
                                                <p class="mt-2 font-medium text-slate-700">
                                                    Tu observación:
                                                </p>

                                                <p class="mt-1 break-words text-slate-600">
                                                    {{ $carta->observacionEstudiante }}
                                                </p>
                                            @endif

                                            @if (!$carta->observacionCoordinador && !$carta->observacionEstudiante)
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <div class="flex flex-col items-center gap-3">

                                                {{-- Ver carta generada --}}
                                                @if ($carta->estadoCarta === 'Generada' && $carta->rutaCartaEmitida)
                                                    <a
                                                        href="{{ route('estudiante.recomendacion.verCarta', [
                                                            'idCartaRecomendacion' => $carta->idCartaRecomendacion
                                                        ]) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex items-center gap-1 font-medium text-teal-700 hover:text-teal-900 hover:underline"
                                                    >
                                                        Ver carta
                                                    </a>
                                                @elseif ($carta->estadoCarta === 'Generada')
                                                    <span class="text-xs text-slate-500">
                                                        Archivo no disponible
                                                    </span>
                                                @endif

                                                {{-- Editar: únicamente Pendiente --}}
                                                @if ($carta->estadoCarta === 'Pendiente')
                                                    <a href="{{ route('estudiante.recomendacion.editar', $carta->idCartaRecomendacion) }}"
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

                                                    {{-- Eliminar: únicamente Pendiente --}}
                                                    <form action="{{ route('estudiante.recomendacion.eliminar', $carta->idCartaRecomendacion) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta solicitud? Esta acción no se puede deshacer.');">

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

                                            </div>
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
                                                    Selecciona «Nueva solicitud» para solicitar tu primera carta de recomendación.
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
