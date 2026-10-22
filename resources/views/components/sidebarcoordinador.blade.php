<aside class="flex min-h-screen w-56 shrink-0 flex-col bg-[#5b9eaa] py-3 text-white">

    {{-- Encabezado --}}
    <div class="mb-5 border-b border-white/20 px-5 py-4 text-center">
        <h1 class="text-4xl font-bold tracking-wider">UAM</h1>

        <p class="mt-2 text-xs leading-relaxed">
            Sistema de prácticas profesionales
        </p>
    </div>

    {{-- Navegación --}}
    @php
        $menu = [
            [
                'ruta' => 'coordinador.home',
                'texto' => 'Inicio',
                'icono' => '⌂',
            ],
            [
                'ruta' => 'coordinador.aceptacion',
                'texto' => 'Cartas de aceptación',
                'icono' => '✉',
            ],
            [
                'ruta' => 'coordinador.recomendacion',
                'texto' => 'Cartas de recomendación',
                'icono' => '▱',
            ],
            [
                'ruta' => 'coordinador.formatos',
                'texto' => 'Subir formatos de prácticas',
                'icono' => '▤',
            ],
            [
                'ruta' => 'coordinador.alertas',
                'texto' => 'Alertas y riesgos',
                'icono' => '⚠',
            ],
        ];
    @endphp

    <nav class="flex-1 space-y-1" aria-label="Navegación principal">
        @foreach ($menu as $item)
            @php
                $activo = request()->routeIs($item['ruta']);
            @endphp

            <a
                href="{{ \Illuminate\Support\Facades\Route::has($item['ruta'])
                    ? route($item['ruta'])
                    : route('coordinador.home') }}"
                @if ($activo)
                    aria-current="page"
                @endif
                class="flex items-center gap-3 px-5 py-3 text-sm font-medium transition-colors duration-200
                    {{ $activo
                        ? 'bg-[#397783] shadow'
                        : 'hover:bg-white/15' }}"
            >
                <span class="w-5 shrink-0 text-center text-lg" aria-hidden="true">
                    {{ $item['icono'] }}
                </span>

                <span>{{ $item['texto'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Cerrar sesión --}}
    <div class="mt-5 border-t border-white/20 pt-3">
        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button
                type="submit"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-sm font-medium transition-colors duration-200 hover:bg-white/15"
            >
                <span class="w-5 text-center text-xl" aria-hidden="true">
                    ⇥
                </span>

                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>

</aside>