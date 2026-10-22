<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100">

    <header class="bg-blue-900 px-6 py-4 text-white">
        <h1 class="text-xl font-bold">Prácticas Profesionales UAM</h1>
    </header>

    <main class="mx-auto max-w-5xl p-6">
        <section class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-2xl font-bold text-gray-800">
                Bienvenido, {{ auth()->user()->nombre }}
            </h2>

            <p class="mt-2 text-gray-600">
                Desde aquí podrás gestionar tus prácticas profesionales.
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

        <form action="{{ route('logout') }}" method="POST" class="mt-6">
            @csrf

            <button
                type="submit"
                class="rounded-lg bg-red-600 px-5 py-2 font-semibold text-white hover:bg-red-700"
            >
                Cerrar sesión
            </button>
        </form>
    </main>

</body>
</html>