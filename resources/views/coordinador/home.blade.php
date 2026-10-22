
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Coordinador | Prácticas UAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <header class="bg-blue-950 text-white shadow-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-xl font-bold">
                    Prácticas Profesionales UAM
                </h1>
                <p class="mt-1 text-sm text-blue-200">
                    Panel de Coordinación Académica
                </p>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="rounded-lg bg-white/10 px-4 py-2 text-sm font-medium
                           transition hover:bg-white/20">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">

        <section class="mb-8">
            <p class="text-sm font-medium text-blue-700">
                Coordinación Académica
            </p>

            <h2 class="mt-2 text-3xl font-bold tracking-tight">
                Panel principal
            </h2>

            <p class="mt-2 text-slate-600">
                Bienvenido/a, {{ auth()->user()->nombre }}.
                Desde aquí podrás gestionar las prácticas profesionales.
            </p>
        </section>

        <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-blue-100 text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm10 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Estudiantes</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Consultar estudiantes y revisar su información académica
                    relacionada con las prácticas.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5-4v6a8 8 0 11-16 0V6l8-3 8 3z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Revisión documental</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Revisar documentos pendientes, incompletos o que requieren
                    correcciones.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-emerald-100 text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3v18h18M7 14l4-4 4 4 5-7"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Seguimiento de prácticas</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Dar seguimiento al avance de horas y detectar posibles
                    riesgos de incumplimiento.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v4m0 4h.01M10.3 3.86L1.82 18.5A2 2 0 003.55 21h16.9a2 2 0 001.73-2.5L13.7 3.86a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Alertas de riesgo</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Consultar alertas relacionadas con horas insuficientes
                    y documentación pendiente.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-violet-100 text-violet-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Reglas de seguimiento</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Administrar los criterios parametrizables para la detección
                    temprana de riesgos.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-lg bg-cyan-100 text-cyan-700">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h8m-8 4h8m-8 4h5M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold">Reportes</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Consultar información consolidada sobre el estado de las
                    prácticas profesionales.
                </p>

                <span class="mt-5 inline-block text-sm font-medium text-slate-400">
                    Próximamente
                </span>
            </article>

        </section>

        <footer class="mt-10 border-t border-slate-200 pt-5 text-center
                       text-sm text-slate-500">
            Sistema de Gestión de Prácticas Profesionales · Universidad Americana
        </footer>

    </main>

</body>
</html>
