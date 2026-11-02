{{-- Modal para registrar horas de una pasantía. Requiere $pasantia y la variable de Alpine openRegistroHoras. --}}
<div
    x-show="openRegistroHoras"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
    @click.self="openRegistroHoras = false"
>
    <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Registrar horas</h3>
                <p class="mt-1 text-sm text-slate-500">Ingresa la información de las horas realizadas.</p>
            </div>
            <button
                type="button"
                @click="openRegistroHoras = false"
                class="text-xl font-bold text-slate-400 hover:text-slate-600"
                aria-label="Cerrar ventana"
            >&times;</button>
        </div>

        <form action="{{ route('estudiante.registro-horas.store') }}" method="POST">
            @csrf
            <input type="hidden" name="idPasantia" value="{{ $pasantia->idPasantia }}">

            <div class="space-y-4 p-6">
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <p class="mb-1 font-semibold">Revisa la información ingresada:</p>
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="fechaRegistro" class="mb-1 block text-sm font-semibold text-slate-700">
                        Fecha en que realizaste las horas *
                    </label>
                    <input
                        type="date"
                        id="fechaRegistro"
                        name="fechaRegistro"
                        value="{{ old('fechaRegistro') }}"
                        min="{{ $pasantia->fechaInicio?->format('Y-m-d') }}"
                        max="{{ min(now()->toDateString(), $pasantia->fechaFinEstimada?->format('Y-m-d') ?? now()->toDateString()) }}"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                    >
                    @error('fechaRegistro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="horasIngresadas" class="mb-1 block text-sm font-semibold text-slate-700">
                        Horas realizadas *
                    </label>
                    <input
                        type="number"
                        id="horasIngresadas"
                        name="horasIngresadas"
                        value="{{ old('horasIngresadas') }}"
                        min="1"
                        step="1"
                        required
                        placeholder="Ej. 4"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                    >
                    @error('horasIngresadas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="observaciones" class="mb-1 block text-sm font-semibold text-slate-700">
                        Observaciones (opcional)
                    </label>
                    <textarea
                        id="observaciones"
                        name="observaciones"
                        rows="3"
                        maxlength="200"
                        placeholder="Describe brevemente las actividades realizadas."
                        class="w-full resize-y rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-[#5b9eaa] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b9eaa]"
                    >{{ old('observaciones') }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">Máximo 200 caracteres.</p>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    El registro quedará pendiente hasta que tu tutor lo revise. Solo las horas aprobadas se sumarán al avance de la pasantía.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button
                    type="button"
                    @click="openRegistroHoras = false"
                    class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300"
                >Cancelar</button>
                <button
                    type="submit"
                    class="rounded-lg bg-[#5b9eaa] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4c848f]"
                >Enviar registro</button>
            </div>
        </form>
    </div>
</div>
