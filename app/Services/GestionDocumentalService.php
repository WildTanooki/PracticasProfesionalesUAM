<?php

namespace App\Services;

use App\Models\CoordinacionAcademica;
use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Models\Formato;
use App\Models\Pasantia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Carga, reenvío y revisión de los documentos de una pasantía.
 *
 * Documentos guarda el estado vigente de cada formato de la pasantía y
 * documento_historial guarda cada versión enviada con el resultado de
 * su revisión. Ambas tablas se modifican siempre dentro de una misma
 * transacción.
 */
class GestionDocumentalService
{
    // Disco privado (storage/app/private).
    public const DISCO = 'local';

    public const ESTADOS_REVISION = ['Aprobado', 'A corregir', 'Rechazado'];

    /**
     * Formatos activos que corresponden a la carrera del estudiante
     * dueño de la pasantía.
     */
    public function formatosRequeridos(Pasantia $pasantia): Builder
    {
        $idCarrera = $pasantia->practicaProfesional
            ?->estudiante
            ?->planEstudios
            ?->idCarrera;

        return Formato::where('estado', 'Activo')
            ->whereHas('coordinador', function ($consulta) use ($idCarrera) {
                $consulta->where('idCarrera', $idCarrera);
            });
    }

    /**
     * Registrar la primera carga de un formato o el reenvío de una
     * versión corregida.
     */
    public function enviar(
        Pasantia $pasantia,
        Formato $formato,
        UploadedFile $archivo
    ): Documento {
        // Validación previa para no guardar archivos que serán rechazados.
        // Se vuelve a validar con el registro bloqueado dentro de la transacción.
        $existente = Documento::where('idPasantia', $pasantia->idPasantia)
            ->where('idFormato', $formato->idFormato)
            ->first();

        if ($existente) {
            $this->validarReenvio($existente);
        }

        $ruta = $this->guardarArchivo($pasantia, $formato, $archivo);

        try {
            return DB::transaction(function () use ($pasantia, $formato, $ruta) {
                $documento = Documento::where('idPasantia', $pasantia->idPasantia)
                    ->where('idFormato', $formato->idFormato)
                    ->lockForUpdate()
                    ->first();

                if ($documento === null) {
                    $documento = Documento::create([
                        'idPasantia' => $pasantia->idPasantia,
                        'idFormato' => $formato->idFormato,
                        'archivo' => $ruta,
                        'estadoArchivo' => 'Pendiente',
                        'observacion' => null,
                        'idCoordinador' => null,
                    ]);

                    $version = 1;
                } else {
                    $this->validarReenvio($documento);

                    $version = (int) DocumentoHistorial::where(
                        'idDocumento',
                        $documento->idDocumento
                    )->lockForUpdate()->max('version') + 1;

                    // La observación y el revisor anteriores quedan en el
                    // historial de la versión revisada.
                    $documento->update([
                        'archivo' => $ruta,
                        'estadoArchivo' => 'Pendiente',
                        'observacion' => null,
                        'idCoordinador' => null,
                    ]);
                }

                DocumentoHistorial::create([
                    'idDocumento' => $documento->idDocumento,
                    'archivo' => $ruta,
                    'version' => $version,
                    'estadoArchivo' => 'Pendiente',
                    'fechaEnvio' => now(),
                ]);

                return $documento;
            });
        } catch (Throwable $e) {
            // El archivo nuevo no quedó registrado: se elimina.
            // Los archivos de versiones anteriores no se tocan.
            Storage::disk(self::DISCO)->delete($ruta);

            if ($e instanceof UniqueConstraintViolationException) {
                // Otro envío simultáneo creó el mismo documento o la misma versión.
                throw ValidationException::withMessages([
                    'archivo' => 'Este formato acaba de recibir otro envío. Recarga la página e inténtalo de nuevo.',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Registrar la revisión de Coordinación Académica sobre la versión
     * indicada. No crea versiones nuevas.
     */
    public function revisar(
        Documento $documento,
        CoordinacionAcademica $coordinacion,
        int $versionRevisada,
        string $estado,
        ?string $observacion
    ): DocumentoHistorial {
        if (! in_array($estado, self::ESTADOS_REVISION, true)) {
            throw ValidationException::withMessages([
                'estadoArchivo' => 'La decisión seleccionada no es válida.',
            ]);
        }

        return DB::transaction(function () use (
            $documento,
            $coordinacion,
            $versionRevisada,
            $estado,
            $observacion
        ) {
            $documento = Documento::whereKey($documento->idDocumento)
                ->lockForUpdate()
                ->firstOrFail();

            $ultima = DocumentoHistorial::where('idDocumento', $documento->idDocumento)
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if ($ultima === null || $ultima->version !== $versionRevisada) {
                throw ValidationException::withMessages([
                    'version' => 'El estudiante envió una versión más reciente. Recarga la página antes de revisar.',
                ]);
            }

            if ($documento->estadoArchivo !== 'Pendiente' || $ultima->estadoArchivo !== 'Pendiente') {
                throw ValidationException::withMessages([
                    'version' => 'Esta versión ya fue revisada.',
                ]);
            }

            $documento->update([
                'estadoArchivo' => $estado,
                'observacion' => $observacion,
                'idCoordinador' => $coordinacion->idCoordinador,
            ]);

            $ultima->update([
                'estadoArchivo' => $estado,
                'observacion' => $observacion,
                'idCoordinador' => $coordinacion->idCoordinador,
                'fechaRevision' => now(),
            ]);

            return $ultima;
        });
    }

    /**
     * Indica si el estudiante puede enviar una nueva versión.
     */
    public function permiteReenvio(Documento $documento): bool
    {
        return match ($documento->estadoArchivo) {
            'A corregir' => true,
            'Rechazado' => (bool) config('documentos.permitir_reenvio_rechazado'),
            default => false,
        };
    }

    /**
     * Nombre legible para descargar una versión.
     */
    public function nombreDescarga(DocumentoHistorial $version): string
    {
        $nombre = Str::slug(
            $version->documento?->formato?->nombreFormato ?? 'documento',
            '_'
        );

        $extension = pathinfo($version->archivo, PATHINFO_EXTENSION);

        return "{$nombre}_v{$version->version}.{$extension}";
    }

    private function validarReenvio(Documento $documento): void
    {
        if ($this->permiteReenvio($documento)) {
            return;
        }

        $mensaje = match ($documento->estadoArchivo) {
            'Pendiente' => 'Este documento ya tiene una versión pendiente de revisión.',
            'Aprobado' => 'Este documento ya fue aprobado.',
            'Rechazado' => 'Este documento fue rechazado. Comunícate con Coordinación Académica.',
            default => 'Este documento no admite un nuevo envío.',
        };

        throw ValidationException::withMessages(['archivo' => $mensaje]);
    }

    /**
     * Guardar el archivo con un nombre único, sin sobrescribir versiones.
     */
    private function guardarArchivo(
        Pasantia $pasantia,
        Formato $formato,
        UploadedFile $archivo
    ): string {
        $extension = strtolower($archivo->getClientOriginalExtension());

        $ruta = $archivo->storeAs(
            "documentos/pasantia-{$pasantia->idPasantia}/formato-{$formato->idFormato}",
            Str::uuid() . '.' . $extension,
            self::DISCO
        );

        if (! $ruta) {
            throw ValidationException::withMessages([
                'archivo' => 'No se pudo guardar el archivo. Inténtalo de nuevo.',
            ]);
        }

        return $ruta;
    }
}
