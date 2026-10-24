
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de gestión de formatos - UAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-slate-100 text-slate-800 antialiased"
    x-data="{
        openModal: false,
        editModal: false,
        formato: {
            id: '',
            nombreFormato: '',
            descripcion: '',
            estado: 'Activo',
            archivo: ''
        },
        abrirEdicion(datos) {
            this.formato = { ...datos };
            this.editModal = true;
        }
    }"
    @keydown.escape.window="openModal = false; editModal = false"
>
    <div class="flex min-h-screen">

        <x-sidebarcoordinador />

        <div class="min-w-0 flex-1 flex flex-col">

            {{-- Navbar superior --}}
            <header class="border-b border-slate-200 bg-white px-8 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-sm font-semibold text-[#5b9eaa]">
                        Panel
                        <span class="text-slate-800 font-bold">
                            de Coordinación Académica
                        </span>
                    </span>
                    <p class="text-xs text-slate-500">
                        Panel de gestión de prácticas profesionales
                    </p>
                </div>
            </header>

            {{-- Contenido principal --}}
            <main class="flex-1 px-8 py-8 max-w-7xl w-full mx-auto">

                {{-- Mensaje de éxito --}}
                @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <p class="font-semibold mb-2">
                            No se pudieron guardar los cambios:
                        </p>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Título y botón de creación --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        Formatos de prácticas profesionales
                    </h1>

                    <button
                        @click="openModal = true"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 bg-[#5b9eaa] hover:bg-[#4c848f] text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition-colors text-sm"
                    >
                        <span class="text-lg font-bold">+</span>
                        Subir formato
                    </button>
                </div>

                {{-- Tabla --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                    {{-- Barra de herramientas --}}
                    <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between bg-slate-50/50">

                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                🔍
                            </span>
                            <input
                                type="text"
                                placeholder="Buscar formato..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white"
                            >
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 shadow-sm"
                            >
                                ⚙️ Filtros
                            </button>

                            <div class="relative">
                                <select
                                    class="appearance-none bg-white border border-slate-200 rounded-lg px-3 py-2 pr-8 text-sm text-slate-600 hover:bg-slate-50 shadow-sm focus:outline-none cursor-pointer"
                                >
                                    <option>Más recientes</option>
                                    <option>Más antiguos</option>
                                    <option>Alfabético (A-Z)</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-xs text-slate-400">
                                    ▼
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de formatos --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">

                            <thead>
                                <tr class="bg-slate-100/70 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    <th class="py-3.5 px-6">Nombre del formato</th>
                                    <th class="py-3.5 px-6">Versión</th>
                                    <th class="py-3.5 px-6">Fecha de actualización</th>
                                    <th class="py-3.5 px-6">Estado</th>
                                    <th class="py-3.5 px-6 text-right">Acción</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 text-sm">

                                @forelse ($formatos as $formatoFila)

                                    <tr class="hover:bg-slate-50/80 transition-colors">

                                        {{-- Nombre descargable --}}
                                        <td class="py-4 px-6 font-medium">
                                            @if ($formatoFila->archivo)
                                                <a
                                                    href="{{ route('coordinador.formatos.descargar', ['idFormato' => $formatoFila->idFormato]) }}"
                                                    class="text-[#4c848f] hover:text-[#356b75] hover:underline"
                                                    title="Descargar {{ $formatoFila->nombreFormato }}"
                                                >
                                                    {{ $formatoFila->nombreFormato }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">
                                                    {{ $formatoFila->nombreFormato }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-6 text-slate-600">
                                            1.0
                                        </td>

                                        <td class="py-4 px-6 text-slate-600">
                                            {{ $formatoFila->ultimaEdicion
                                                ? \Carbon\Carbon::parse($formatoFila->ultimaEdicion)->format('d/m/Y')
                                                : 'N/D' }}
                                        </td>

                                        <td class="py-4 px-6">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ ($formatoFila->estado ?? 'Activo') === 'Activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}"
                                            >
                                                {{ $formatoFila->estado ?? 'Activo' }}
                                            </span>
                                        </td>

                                        {{-- Botón de edición --}}
                                        <td class="py-4 px-6 text-right">
                                            <div class="inline-flex items-center justify-end gap-4">

                                                {{-- Ver documento: solo si existe un archivo --}}
                                                @if ($formatoFila->archivo)
                                                    <a href="{{ route('coordinador.formatos.descargar', ['idFormato' => $formatoFila->idFormato]) }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-1 font-medium text-teal-700 hover:text-teal-900 hover:underline">

                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="h-4 w-4"
                                                             fill="none"
                                                             viewBox="0 0 24 24"
                                                             stroke="currentColor"
                                                             stroke-width="1.8">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                                                            <circle cx="12" cy="12" r="2.5" />
                                                        </svg>

                                                        Ver documento
                                                    </a>
                                                @endif

                                                {{-- Editar formato --}}
                                                <button
                                                    type="button"
                                                    @click="abrirEdicion({
                                                        id: {{ Illuminate\Support\Js::from($formatoFila->idFormato) }},
                                                        nombreFormato: {{ Illuminate\Support\Js::from($formatoFila->nombreFormato) }},
                                                        descripcion: {{ Illuminate\Support\Js::from($formatoFila->descripcion ?? '') }},
                                                        estado: {{ Illuminate\Support\Js::from($formatoFila->estado ?? 'Activo') }},
                                                        archivo: {{ Illuminate\Support\Js::from($formatoFila->archivo ?? '') }}
                                                    })"
                                                    class="inline-flex items-center gap-1 font-medium text-amber-600 hover:text-amber-800 hover:underline">

                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-4 w-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor"
                                                         stroke-width="1.8">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="m16.862 4.487 2.651 2.651M8.5 15.5l8.362-8.362a1.875 1.875 0 0 1 2.652 2.652L11.15 18.154a4.5 4.5 0 0 1-1.897 1.13L5.5 20.5l1.216-3.753a4.5 4.5 0 0 1 1.784-1.247Z" />
                                                    </svg>

                                                    Editar
                                                </button>

                                            </div>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="py-8 px-6 text-center text-slate-500 text-sm">
                                            No hay formatos registrados actualmente para esta carrera.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer --}}
                <footer class="mt-12 border-t border-slate-200 pt-5 text-center text-xs text-slate-400">
                    Sistema de Gestión de Prácticas Profesionales · Universidad Americana (UAM)
                </footer>

            </main>
        </div>
    </div>


    {{-- ================================================= --}}
    {{-- MODAL PARA CREAR UN FORMATO --}}
    {{-- ================================================= --}}
    <div
        x-show="openModal"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.outside="openModal = false"
            class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200"
        >

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">
                    Subir nuevo formato
                </h3>

                <button
                    type="button"
                    @click="openModal = false"
                    class="text-slate-400 hover:text-slate-600 text-xl font-bold"
                    aria-label="Cerrar ventana"
                >
                    &times;
                </button>
            </div>

            <form
                action="{{ route('coordinador.formatos.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="p-6 space-y-4">

                    {{-- Nombre --}}
                    <div>
                        <label
                            for="nombreFormato"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Nombre del formato *
                        </label>

                        <input
                            type="text"
                            id="nombreFormato"
                            name="nombreFormato"
                            value="{{ old('nombreFormato') }}"
                            required
                            maxlength="50"
                            placeholder="Ej. Plan de prácticas"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white"
                        >
                    </div>

                    <div>
                        <label
                            for="descripcion"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Descripción (opcional)
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            maxlength="200"
                            rows="3"
                            placeholder="Ej. Formato para la planificación de las prácticas profesionales."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white resize-none"
                        >{{ old('descripcion') }}</textarea>

                        <p class="text-[11px] text-slate-400 mt-1">
                            Máximo 200 caracteres.
                        </p>
                    </div>

                    {{-- Archivo --}}
                    <div>
                        <label
                            for="archivo"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Archivo *
                        </label>

                        <input
                            type="file"
                            id="archivo"
                            name="archivo"
                            nullable
                            accept=".pdf,.doc,.docx"
                            class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                        >

                        <p class="text-[11px] text-slate-400 mt-1">
                            Formatos permitidos: PDF, DOC y DOCX. Tamaño máximo: 5 MB.
                        </p>
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label
                            for="estado"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Estado *
                        </label>

                        <select
                            id="estado"
                            name="estado"
                            required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white"
                        >
                            <option value="Activo" {{ old('estado', 'Activo') === 'Activo' ? 'selected' : '' }}>
                                Activo
                            </option>
                            <option value="Inactivo" {{ old('estado') === 'Inactivo' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button
                        type="button"
                        @click="openModal = false"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-medium"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-[#5b9eaa] hover:bg-[#4c848f] text-white rounded-lg text-sm font-medium shadow-sm"
                    >
                        Guardar formato
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ================================================= --}}
    {{-- MODAL PARA EDITAR UN FORMATO --}}
    {{-- ================================================= --}}
    <div
        x-show="editModal"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.outside="editModal = false"
            class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200"
        >

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">
                    Editar formato
                </h3>

                <button
                    type="button"
                    @click="editModal = false"
                    class="text-slate-400 hover:text-slate-600 text-xl font-bold"
                    aria-label="Cerrar ventana"
                >
                    &times;
                </button>
            </div>

            <form
                :action="'{{ route('coordinador.formatos.update', ['idFormato' => '__ID__']) }}'.replace('__ID__', formato.id)"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <div class="p-6 space-y-4">

                    {{-- Nombre --}}
                    <div>
                        <label
                            for="editarNombreFormato"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Nombre del formato *
                        </label>

                        <input
                            type="text"
                            id="editarNombreFormato"
                            name="nombreFormato"
                            x-model="formato.nombreFormato"
                            required
                            maxlength="50"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white"
                        >
                    </div>

                    <div>
                        <label
                            for="editarDescripcion"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Descripción (opcional)
                        </label>

                        <textarea
                            id="editarDescripcion"
                            name="descripcion"
                            x-model="formato.descripcion"
                            maxlength="200"
                            rows="3"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white resize-none"
                        ></textarea>

                        <p class="text-[11px] text-slate-400 mt-1">
                            Máximo 200 caracteres.
                        </p>
                    </div>

                    {{-- Archivo actual --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Archivo actual
                        </label>

                        <p
                            class="text-sm text-slate-600 break-all"
                            x-text="formato.archivo || 'Sin archivo registrado'"
                        ></p>

                        <label
                            for="editarArchivo"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mt-4 mb-1"
                        >
                            Reemplazar archivo (opcional)
                        </label>

                        <input
                            type="file"
                            id="editarArchivo"
                            name="archivo"
                            accept=".pdf,.doc,.docx"
                            class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                        >

                        <p class="text-[11px] text-slate-400 mt-1">
                            PDF, DOC o DOCX. Máximo 5 MB. Si no seleccionas otro archivo, se conservará el actual.
                        </p>
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label
                            for="editarEstado"
                            class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                        >
                            Estado *
                        </label>

                        <select
                            id="editarEstado"
                            name="estado"
                            x-model="formato.estado"
                            required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#5b9eaa] focus:bg-white"
                        >
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button
                        type="button"
                        @click="editModal = false"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-medium"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-[#5b9eaa] hover:bg-[#4c848f] text-white rounded-lg text-sm font-medium shadow-sm"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
