<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CartaAprobacion;
use App\Models\CoordinacionAcademica;
use App\Models\PracticaProfesional;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartaAprobacionCoordinadorController extends Controller
{
    /**
     * Obtener la coordinación del usuario autenticado.
     */
    private function obtenerCoordinacion(): CoordinacionAcademica
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario && $usuario->rol === 'Coordinador',
            403,
            'No tienes permiso para acceder a esta sección.'
        );

        return CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();
    }

    /**
     * Listar las cartas correspondientes a la carrera del coordinador.
     */
    public function index()
    {
        $coordinacion = $this->obtenerCoordinacion();

        $cartas = CartaAprobacion::with([
            'usuario.estudiante.planEstudios.carrera',
        ])
            ->whereHas(
                'usuario.estudiante.planEstudios',
                function ($query) use ($coordinacion) {
                    $query->where(
                        'idCarrera',
                        $coordinacion->idCarrera
                    );
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
     * Mostrar el detalle de una carta y los datos para crear la pasantía.
     */
    public function gestionar($idCartaAprobacion)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $carta = CartaAprobacion::with([
            'usuario.estudiante.planEstudios.carrera',
        ])
            ->where('idCartaAprobacion', $idCartaAprobacion)
            ->whereHas(
                'usuario.estudiante.planEstudios',
                function ($query) use ($coordinacion) {
                    $query->where(
                        'idCarrera',
                        $coordinacion->idCarrera
                    );
                }
            )
            ->firstOrFail();

        // Solo las prácticas pendientes del estudiante de esta carta.
        $practicasPendientes = PracticaProfesional::where(
            'cif',
            $carta->credencial
        )
            ->where('estadoPractica', 'Pendiente')
            ->orderBy('idPractica')
            ->get();

        // Tutores activos con sus datos de usuario.
        $tutores = Tutor::with('usuario')
            ->where('estadoTutor', 'Activo')
            ->orderBy('idTutor')
            ->get();

        return view(
            'coordinador.aceptacion-gestionar',
            compact('carta', 'practicasPendientes', 'tutores')
        );
    }

    /**
     * Solicitar correcciones o rechazar una carta.
     * La aprobación se gestiona desde PasantiaCoordinadorController.
     */
    public function resolver(Request $request, $idCartaAprobacion)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $carta = CartaAprobacion::where(
            'idCartaAprobacion',
            $idCartaAprobacion
        )
            ->whereHas(
                'usuario.estudiante.planEstudios',
                function ($query) use ($coordinacion) {
                    $query->where(
                        'idCarrera',
                        $coordinacion->idCarrera
                    );
                }
            )
            ->firstOrFail();

        abort_if(
            in_array(
                $carta->estadoAprobacion,
                ['Aprobada', 'Rechazada'],
                true
            ),
            403,
            'Esta carta ya fue resuelta y no puede modificarse.'
        );

        $datos = $request->validate([
            'estadoAprobacion' => [
                'required',
                Rule::in(['Rechazada', 'A corregir']),
            ],
            'observacion' => [
                'required',
                'string',
                'max:1000',
            ],
            '_formulario' => [
                'nullable',
                Rule::in(['corregir', 'rechazar']),
            ],
        ], [
            'estadoAprobacion.required' =>
                'Debes seleccionar una decisión.',

            'estadoAprobacion.in' =>
                'La decisión seleccionada no es válida.',

            'observacion.required' =>
                'Debes indicar las observaciones correspondientes.',

            'observacion.max' =>
                'Las observaciones no pueden superar los 1000 caracteres.',
        ]);

        $carta->estadoAprobacion = $datos['estadoAprobacion'];
        $carta->observacion = $datos['observacion'];
        $carta->save();

        $mensaje = $datos['estadoAprobacion'] === 'Rechazada'
            ? 'La carta fue rechazada correctamente.'
            : 'Se registraron las observaciones para corregir la carta.';

        return redirect()
            ->route('coordinador.aceptacion')
            ->with('success', $mensaje);
    }
}
