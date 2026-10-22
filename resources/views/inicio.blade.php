
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Prácticas Profesionales | UAM</title>

    @vite('resources/js/app.js')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">

    <header class="bg-blue-950 text-white shadow-lg">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-xl font-bold">UAM</h1>
                <p class="text-sm text-blue-200">
                    Gestión de prácticas profesionales
                </p>
            </div>

            <span class="rounded-lg bg-blue-900 px-4 py-2 text-sm">
                Portal académico
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm md:p-12">
            <p class="mb-3 font-semibold text-blue-700">
                Sistema de gestión académica
            </p>

            <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-5xl">
                Prácticas profesionales
            </h2>

            <p class="mb-8 max-w-2xl leading-7 text-slate-600">
                Consulta el avance de tus horas, gestiona tus documentos
                y revisa el estado de tus prácticas profesionales desde
                un solo lugar.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="#funciones"
                   class="rounded-lg bg-blue-700 px-6 py-3 font-semibold text-white transition hover:bg-blue-800">
                    Explorar funciones
                </a>

                <a href="#informacion"
                   class="rounded-lg border border-slate-300 px-6 py-3 font-semibold transition hover:bg-slate-50">
                    Más información
                </a>
            </div>
        </section>

        <section id="funciones"
                 class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-2 text-lg font-bold">Control de horas</h3>
                <p class="text-sm leading-6 text-slate-600">
                    Consulta las horas registradas y las pendientes
                    para completar los requisitos.
                </p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-2 text-lg font-bold">Gestión documental</h3>
                <p class="text-sm leading-6 text-slate-600">
                    Revisa los documentos requeridos y su estado
                    de validación.
                </p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-2 text-lg font-bold">Notificaciones</h3>
                <p class="text-sm leading-6 text-slate-600">
                    Identifica documentos pendientes y observaciones
                    que requieren atención.
                </p>
            </article>

        </section>

        <footer id="informacion" class="mt-12 text-center text-sm text-slate-500">
            Universidad Americana (UAM) · Managua, Nicaragua
        </footer>
    </main>

</body>
</html>
