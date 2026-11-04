<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Models\Notificacion;

/**
 * Notificaciones internas del sistema. Se guardan en la tabla
 * notificaciones y el usuario las consulta dentro de la aplicación;
 * no se envían correos ni se usan servicios externos.
 */
class NotificacionService
{
    /**
     * Avisar al estudiante dueño del documento que Coordinación
     * Académica registró la revisión de una versión.
     *
     * Devuelve null si el documento no tiene un estudiante asociado.
     */
    public function documentoRevisado(
        Documento $documento,
        DocumentoHistorial $version
    ): ?Notificacion {
        $documento->loadMissing(['formato', 'pasantia.practicaProfesional']);

        $credencial = $documento->pasantia?->practicaProfesional?->cif;

        if (! $credencial) {
            return null;
        }

        $nombre = $documento->formato?->nombreFormato ?? 'Documento';
        $estado = $version->estadoArchivo;

        [$titulo, $mensaje] = match ($estado) {
            'Aprobado' => [
                "Documento aprobado: {$nombre}",
                "Coordinación Académica aprobó tu documento «{$nombre}» (versión {$version->version}).",
            ],
            'A corregir' => [
                "Documento requiere corrección: {$nombre}",
                "Coordinación Académica revisó tu documento «{$nombre}» (versión {$version->version}) y requiere corrección. Envía una versión corregida.",
            ],
            'Rechazado' => [
                "Documento rechazado: {$nombre}",
                "Coordinación Académica rechazó tu documento «{$nombre}» (versión {$version->version}).",
            ],
            default => [
                "Documento actualizado: {$nombre}",
                "El estado de tu documento «{$nombre}» cambió a {$estado}.",
            ],
        };

        return Notificacion::create([
            'credencial' => $credencial,
            'idDocumento' => $documento->idDocumento,
            'idHistorial' => $version->idHistorial,
            'nombreDocumento' => mb_substr($nombre, 0, 100),
            'estadoDocumento' => $estado,
            'titulo' => mb_substr($titulo, 0, 150),
            'mensaje' => mb_substr($mensaje, 0, 255),
            'observacion' => $version->observacion ?: null,
            'fechaCreacion' => now(),
        ]);
    }

    public function noLeidas(string $credencial): int
    {
        return Notificacion::de($credencial)->noLeidas()->count();
    }

    /**
     * Marcar como leídas las notificaciones de un documento, por
     * ejemplo cuando el estudiante abre su detalle.
     */
    public function marcarLeidasDeDocumento(string $credencial, int $idDocumento): int
    {
        return Notificacion::de($credencial)
            ->noLeidas()
            ->where('idDocumento', $idDocumento)
            ->update(['fechaLectura' => now()]);
    }

    public function marcarTodasLeidas(string $credencial): int
    {
        return Notificacion::de($credencial)
            ->noLeidas()
            ->update(['fechaLectura' => now()]);
    }
}
