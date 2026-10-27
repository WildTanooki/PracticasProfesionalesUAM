<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Prácticas UAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col justify-between bg-white text-gray-800 antialiased">

    <!-- Logo superior derecho -->
    <header class="w-full p-6 flex justify-end">
                        {{-- Logotipo institucional --}}
                        <div class="flex justify-center">
                            <img
                                src="{{ asset('images/UAMturquesa.png') }}"
                                alt="Logotipo de la Universidad Americana (UAM)"
                                class="h-10 w-auto max-w-full object-contain"
                            >
                        </div>
    </header>

    <main class="flex-grow flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <!-- Columna Izquierda: Ilustración -->
            <div class="flex justify-center">
                <img src="{{ asset('images/jobimage.jpg') }}" alt="Ilustración Prácticas UAM" class="w-full max-w-md h-auto">
            </div>

            <!-- Columna Derecha: Tarjeta de Acceso -->
            <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                
                <!-- Encabezado con Logo y Título -->
                <div class="flex items-center space-x-3 mb-6">
                        {{-- Logotipo institucional --}}
                        <div class="flex justify-center">
                            <img
                                src="{{ asset('images/UAMturquesa.png') }}"
                                alt="Logotipo de la Universidad Americana (UAM)"
                                class="h-10 w-auto max-w-full object-contain"
                            >
                        </div>
                    <div class="h-8 w-px bg-gray-300"></div>
                        <span class="text-xs font-bold tracking-wider text-[#4f959d] uppercase leading-none">
                        <span class="block text-[11px] font-bold tracking-normal">Acceso</span>
                        <span class="text-base font-extrabold tracking-tight">Usuarios</span>
                    </span>
                </div>

                <!-- Manejo de Errores -->
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 border border-red-200">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf

                    <!-- Campo Usuario / Credencial -->
                    <div class="mb-4">
                        <label for="credencial" class="mb-1 block text-sm font-medium text-gray-700">
                            Usuario:
                        </label>
                        <input
                            type="text"
                            id="credencial"
                            name="credencial"
                            value="{{ old('credencial') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 focus:border-[#4f959d] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#4f959d]"
                        >
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="mb-6">
                        <label for="contrasena" class="mb-1 block text-sm font-medium text-gray-700">
                            Contraseña:
                        </label>
                        <input
                            type="password"
                            id="contrasena"
                            name="contrasena"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 focus:border-[#4f959d] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#4f959d]"
                        >
                    </div>

                    <!-- Botones: Volver y Acceder -->
                    <div class="flex items-center space-x-4">
                        <a
                            href="{{ url('/') }}"
                            class="w-1/2 rounded-lg bg-[#4f959d]/80 px-4 py-2.5 text-center font-semibold text-white transition hover:bg-[#4f959d]"
                        >
                            Volver
                        </a>
                        <button
                            type="submit"
                            class="w-1/2 rounded-lg bg-[#4f959d] px-4 py-2.5 font-semibold text-white transition hover:bg-[#3b777e]"
                        >
                            Acceder
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <footer class="w-full p-4 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} Prácticas UAM. Todos los derechos reservados.
    </footer>

</body>
</html>