<aside class="sticky top-0 flex h-screen w-64 shrink-0 flex-col
    overflow-y-auto border-r border-white/10
    bg-[#5b9eaa] py-4 text-white">

    {{-- Encabezado --}}
    <div class="mb-5 border-b border-white/20 px-5 py-4 text-center">
    {{-- Logotipo institucional --}}
        <div class="flex justify-center">
            <img
                src="{{ asset('images/logotipoUAM.png') }}"
                alt="Logotipo de la Universidad Americana (UAM)"
                class="h-10 w-auto max-w-full object-contain"
            >
        </div>
        <p class="mt-2 text-xs leading-relaxed ">
            Sistema de prácticas profesionales
        </p>
    </div>

    {{-- Etiqueta de navegación --}}
    <p class="mb-3 px-5 text-xs font-semibold uppercase
        tracking-[0.15em] text-white/60">
        Menú principal
    </p>

    {{-- Opciones del menú --}}
    @php
        $menu = [
            [
                'ruta' => 'tutor.home',
                'texto' => 'Inicio',
                'icono' => 'home',
            ],
        ];
    @endphp

    <nav class="flex-1 space-y-1 px-3"
        aria-label="Navegación principal">

        @foreach ($menu as $item)
            @php
                $activo = request()->routeIs($item['ruta']);
                $rutaExiste = \Illuminate\Support\Facades\Route::has($item['ruta']);
            @endphp

            <a
                href="{{ $rutaExiste
                    ? route($item['ruta'])
                    : route('tutor.home') }}"
                @if ($activo) aria-current="page" @endif
                class="group relative flex items-center gap-3
                    rounded-xl px-3 py-3 text-sm font-medium
                    transition-all duration-200
                    {{ $activo
                        ? 'bg-white text-[#397783] shadow-md'
                        : 'text-white/90 hover:bg-white/15 hover:text-white' }}"
            >
                {{-- Indicador de opción activa --}}
                @if ($activo)
                    <span class="absolute bottom-2 left-0 top-2
                        w-1 rounded-r-full bg-[#397783]">
                    </span>
                @endif

                {{-- Iconos SVG --}}
                <span class="flex h-6 w-6 shrink-0 items-center
                    justify-center">

                    @switch($item['icono'])

                        @case('home')
                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="21" height="21" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="m3 10 9-7 9 7"/>
                                <path d="M5 9v12h14V9"/>
                                <path d="M9 21v-8h6v8"/>
                            </svg>
                            @break
                    @endswitch
                </span>

                {{-- Texto de la opción --}}
                <span class="min-w-0 flex-1 leading-5">
                    {{ $item['texto'] }}
                </span>

                {{-- Flecha de la opción activa --}}
                @if ($activo)
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Cerrar sesión --}}
    <div class="mx-3 mt-5 border-t border-white/20 pt-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button
                type="submit"
                class="group flex w-full items-center gap-3
                    rounded-xl px-3 py-3 text-left text-sm
                    font-medium text-white/90
                    transition-colors duration-200
                    hover:bg-red-500/20 hover:text-white"
            >
                <span class="flex h-6 w-6 shrink-0 items-center
                    justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="21" height="21" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M12 3h6a2 2 0 0 1 2 2v14
                            a2 2 0 0 1-2 2h-6"/>
                    </svg>
                </span>

                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>

</aside>