<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarestudiante />

        {{-- Contenido principal --}}
        <div class="min-w-0 flex-1">

            <header class="border-b border-slate-200 bg-white px-6 py-5 shadow-sm">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-xl font-bold">
                        Panel de Coordinación Académica
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Sistema de Gestión de Prácticas Profesionales
                    </p>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-8">

                <section class="mb-8">
                    <p class="mt-1 text-slate-600">
                        Bienvenido/a, {{ auth()->user()->nombre }}.
                        Desde aquí podrás gestionar las prácticas profesionales.
                    </p>
                </section>
                        <section class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Mis prácticas</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                Consulta el estado de tus prácticas profesionales.
                                </p>
                            </div>
                            
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Registro de horas</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Consulta el avance de las horas requeridas.
                                </p>
                            </div>
                            
                            <div class="rounded-xl bg-white p-5 shadow">
                                <h3 class="font-semibold">Documentación</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Revisa el estado de tus documentos.
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