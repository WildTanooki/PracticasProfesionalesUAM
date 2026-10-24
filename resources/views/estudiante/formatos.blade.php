<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formatos disponibles - UAM</title>

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
                        Formatos de prácticas profesionales
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Consulta y descarga los formatos disponibles para tu carrera.
                    </p>
                </div>

                {{-- Tabla --}}
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    {{-- Encabezado de la tabla --}}
                    <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Formatos disponibles
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">

                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">

                                    <th scope="col" class="px-6 py-4">
                                        Nombre del formato
                                    </th>

                                    <th scope="col" class="px-6 py-4">
                                        Descripción
                                    </th>

                                    <th scope="col" class="px-6 py-4 text-center">
                                        Acción
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 text-sm">

                                @forelse ($formatos as $formatoFila)

                                    <tr class="transition-colors hover:bg-slate-50/80">

                                        {{-- Nombre --}}
                                        <td class="px-6 py-4 font-medium text-slate-800">
                                            {{ $formatoFila->nombreFormato }}
                                        </td>

                                        {{-- Descripción --}}
                                        <td class="px-6 py-4 text-slate-600">
                                            {{ $formatoFila->descripcion ?: 'Sin descripción disponible.' }}
                                        </td>

                                        {{-- Acción: descargar --}}
                                        <td class="px-6 py-4 text-center">

                                            @if ($formatoFila->archivo)

                                                <a
                                                    href="{{ route('estudiante.formatos.descargar', ['idFormato' => $formatoFila->idFormato]) }}"
                                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#5b9eaa] px-4 py-2 font-medium text-white transition-colors hover:bg-[#4c848f] focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:ring-offset-2"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"
                                                        />
                                                    </svg>

                                                    Descargar
                                                </a>

                                            @else

                                                <span class="text-slate-400">
                                                    Archivo no disponible
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td
                                            colspan="3"
                                            class="px-6 py-12 text-center"
                                        >
                                            <p class="font-medium text-slate-700">
                                                No hay formatos disponibles.
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