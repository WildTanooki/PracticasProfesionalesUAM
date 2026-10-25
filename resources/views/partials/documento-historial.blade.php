{{--
    Historial de versiones de un documento.
    Parámetros: $documento (con historial cargado) y $rutaDescarga.
--}}
<div class="overflow-x-auto">
    <table class="w-full min-w-[700px] border-collapse text-left">

        <thead>
            <tr class="border-b border-slate-200 bg-slate-100/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <th scope="col" class="px-6 py-4 text-center">Versión</th>
                <th scope="col" class="px-6 py-4">Enviado</th>
                <th scope="col" class="px-6 py-4">Estado</th>
                <th scope="col" class="px-6 py-4">Observación</th>
                <th scope="col" class="px-6 py-4">Revisado</th>
                <th scope="col" class="px-6 py-4 text-center">Archivo</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-200 text-sm">

            @forelse ($documento->historial as $versionFila)

                @php
                    $estiloVersion = match ($versionFila->estadoArchivo) {
                        'Aprobado' => 'bg-emerald-50 text-emerald-700',
                        'Pendiente' => 'bg-amber-50 text-amber-700',
                        'A corregir' => 'bg-orange-50 text-orange-700',
                        'Rechazado' => 'bg-red-50 text-red-700',
                        default => 'bg-slate-100 text-slate-600',
                    };
                @endphp

                <tr class="align-top transition-colors hover:bg-slate-50/80">
                    <td class="px-6 py-4 text-center font-semibold text-slate-800">
                        {{ $versionFila->version }}
                    </td>

                    <td class="px-6 py-4 text-slate-600">
                        {{ $versionFila->fechaEnvio?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estiloVersion }}">
                            {{ $versionFila->estadoArchivo }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-slate-600">
                        {{ $versionFila->observacion ?: '—' }}
                    </td>

                    <td class="px-6 py-4 text-slate-600">
                        {{ $versionFila->fechaRevision?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="px-6 py-4 text-center">
                        <a href="{{ route($rutaDescarga, $versionFila->idHistorial) }}"
                           class="font-medium text-[#5b9eaa] hover:underline">
                            Descargar
                        </a>
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                        Este documento no tiene versiones registradas.
                    </td>
                </tr>

            @endforelse

        </tbody>
    </table>
</div>
