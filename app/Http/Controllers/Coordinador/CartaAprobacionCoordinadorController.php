<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CartaAprobacion;
use App\Models\CoordinacionAcademica;

class CartaAprobacionCoordinadorController extends Controller
{
    /**
     * Obtener el registro de coordinación del usuario autenticado.
     */
    private function obtenerCoordinacion(): CoordinacionAcademica
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario && $usuario->rol === 'Coordinador',
            403
        );

        $coordinacion = CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();

        return $coordinacion;
    }

    /**
     * Listar únicamente las cartas de estudiantes
     * pertenecientes a la carrera del coordinador.
     */
    public function index()
    {
        $coordinacion = $this->obtenerCoordinacion();

        $idCarrera = $coordinacion->idCarrera;

        $cartas = CartaAprobacion::with([
            'usuario.estudiante.planEstudios.carrera',
        ])
            ->whereHas(
                'usuario.estudiante.planEstudios',
                function ($query) use ($idCarrera) {
                    $query->where('idCarrera', $idCarrera);
                }
            )
            ->orderByDesc('fechaEmision')
            ->orderByDesc('idCartaAprobacion')
            ->get();

        return view(
            'coordinador.aceptacion',
            compact('cartas')
        );
    }

    /**
     * Mostrar una carta solo si pertenece a la carrera
     * del coordinador autenticado.
     */
    public function gestionar($idCartaAprobacion)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $idCarrera = $coordinacion->idCarrera;

        $carta = CartaAprobacion::with([
            'usuario.estudiante.planEstudios.carrera',
        ])
            ->where('idCartaAprobacion', $idCartaAprobacion)
            ->whereHas(
                'usuario.estudiante.planEstudios',
                function ($query) use ($idCarrera) {
                    $query->where('idCarrera', $idCarrera);
                }
            )
            ->firstOrFail();

        return view(
            'coordinador.aceptacion-gestionar',
            compact('carta')
        );
    }
}
