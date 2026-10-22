<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | Prácticas UAM</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <main class="w-full max-w-md rounded-xl bg-white p-8 shadow-md">
        <h1 class="mb-2 text-center text-2xl font-bold">
            Prácticas UAM
        </h1>

        <p class="mb-6 text-center text-gray-600">
            Inicia sesión para continuar
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="credencial" class="mb-1 block font-medium">
                    Credencial
                </label>

                <input
                    type="text"
                    id="credencial"
                    name="credencial"
                    value="{{ old('credencial') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                >
            </div>

            <div class="mb-6">
                <label for="contrasena" class="mb-1 block font-medium">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800"
            >
                Iniciar sesión
            </button>
        </form>
    </main>

</body>
</html>