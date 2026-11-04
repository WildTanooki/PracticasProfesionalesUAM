<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Services\NotificacionService;

/**
 * Notificaciones internas del estudiante: cambios de estado de sus
 * documentos registrados por Coordinación Académica.
 */
class NotificacionController extends Controller
{
    public function __construct(
        private NotificacionService $notificaciones
    ) {
    }

    private function credencialEstudiante(): string
    {
        $usuario = auth()->user();

        abort_unless($usuario && $usuario->rol === 'Estudiante', 403);

        return $usuario->credencial;
    }

    public function index()
    {
        $credencial = $this->credencialEstudiante();

        $notificaciones = Notificacion::de($credencial)
            ->orderByDesc('fechaCreacion')
            ->orderByDesc('idNotificacion')
            ->paginate(15);

        return view('estudiante.notificaciones', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $this->notificaciones->noLeidas($credencial),
        ]);
    }

    /**
     * Abrir una notificación: se marca como leída y lleva al documento.
     */
    public function abrir($idNotificacion)
    {
        $credencial = $this->credencialEstudiante();

        $notificacion = Notificacion::de($credencial)
            ->where('idNotificacion', $idNotificacion)
            ->firstOrFail();

        if (! $notificacion->leida) {
            $notificacion->update(['fechaLectura' => now()]);
        }

        return redirect()->route('estudiante.documentos.show', $notificacion->idDocumento);
    }

    public function marcarTodasLeidas()
    {
        $credencial = $this->credencialEstudiante();

        $this->notificaciones->marcarTodasLeidas($credencial);

        return redirect()
            ->route('estudiante.notificaciones')
            ->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }
}
