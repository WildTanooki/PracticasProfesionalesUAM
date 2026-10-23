
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

        {{-- Navbar lateral reutilizable --}}
        <x-sidebarcoordinador />

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

                {{-- Coloca aquí tus seis tarjetas actuales, sin cambiar su contenido --}}
                <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

                    {{-- Tarjeta de Estudiantes --}}
                    {{-- Tarjeta de Revisión documental --}}
                    {{-- Tarjeta de Seguimiento de prácticas --}}
                    {{-- Tarjeta de Alertas de riesgo --}}
                    {{-- Tarjeta de Reglas de seguimiento --}}
                    {{-- Tarjeta de Reportes --}}

                </section>

                <footer class="mt-10 border-t border-slate-200 pt-5 text-center text-sm text-slate-500">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana
                </footer>

            </main>
        </div>

    </div>

</body>
</html>
