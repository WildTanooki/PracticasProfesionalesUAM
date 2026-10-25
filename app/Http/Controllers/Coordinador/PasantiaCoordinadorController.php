<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CartaAprobacion;
use App\Models\CoordinacionAcademica;
use App\Models\Pasantia;
use App\Models\PracticaProfesional;
use App\Models\Tutor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PasantiaCoordinadorController extends Controller
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
            'No tienes permiso para realizar esta operación.'
        );

        return CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();
    }

    /**
     * Aprobar una carta y crear su pasantía.
     */
    public function store(Request $request, $idCartaAprobacion)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $tablaUsuarios = (new Usuario())->getTable();

        $datos = $request->validate([
            'idPractica' => [
                'required',
                'integer',
                'min:1',
            ],

            'horasRequeridas' => [
                'required',
                'integer',
                'min:1',
            ],

            'fechaInicio' => [
                'required',
                'date',
            ],

            'fechaFinEstimada' => [
                'required',
                'date',
                'after_or_equal:fechaInicio',
            ],

            'empresa' => [
                'required',
                'string',
                'max:50',
            ],

            'cargo' => [
                'required',
                'string',
                'max:50',
            ],

            'tipoTutor' => [
                'required',
                Rule::in(['existente', 'nuevo']),
            ],

            'idTutor' => [
                'required_if:tipoTutor,existente',
                'nullable',
                'string',
                'max:30',
            ],

            'credencialTutor' => [
                'required_if:tipoTutor,nuevo',
                'nullable',
                'string',
                'max:30',
                Rule::unique($tablaUsuarios, 'credencial'),
            ],

            'nombreTutor' => [
                'required_if:tipoTutor,nuevo',
                'nullable',
                'string',
                'max:30',
            ],

            'apellidoTutor' => [
                'required_if:tipoTutor,nuevo',
                'nullable',
                'string',
                'max:30',
            ],

            'telefonoTutor' => [
                'nullable',
                'string',
                'max:30',
            ],

            'correoTutor' => [
                'required_if:tipoTutor,nuevo',
                'nullable',
                'email',
                'max:100',
                Rule::unique($tablaUsuarios, 'correo'),
            ],

            'contrasenaTutor' => [
                'required_if:tipoTutor,nuevo',
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],

            '_formulario' => [
                'required',
                Rule::in(['crear_pasantia']),
            ],
        ], [
            'idPractica.required' =>
                'Debes seleccionar una práctica profesional pendiente.',

            'horasRequeridas.required' =>
                'Debes indicar las horas requeridas de la pasantía.',

            'fechaInicio.required' =>
                'Debes indicar la fecha de inicio.',

            'fechaFinEstimada.required' =>
                'Debes indicar la fecha de finalización estimada.',

            'fechaFinEstimada.after_or_equal' =>
                'La fecha de finalización no puede ser anterior a la fecha de inicio.',

            'empresa.required' =>
                'Debes indicar la empresa.',

            'cargo.required' =>
                'Debes indicar el puesto o cargo.',

            'tipoTutor.required' =>
                'Debes seleccionar cómo se asignará el tutor.',

            'credencialTutor.unique' =>
                'La credencial indicada ya está registrada.',

            'correoTutor.unique' =>
                'El correo indicado ya está registrado.',

            'contrasenaTutor.min' =>
                'La contraseña del tutor debe tener al menos 8 caracteres.',
        ]);

        DB::transaction(function () use (
            $datos,
            $idCartaAprobacion,
            $coordinacion
        ) {
            /*
             * Bloquear la carta para reducir el riesgo de que dos
             * solicitudes concurrentes la aprueben simultáneamente.
             */
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
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array(
                $carta->estadoAprobacion,
                ['Pendiente', 'A corregir'],
                true
            )) {
                throw ValidationException::withMessages([
                    'carta' =>
                        'Esta carta ya fue aprobada, rechazada o no está disponible para gestión.',
                ]);
            }

            if ($carta->horasPropuestas === null) {
                throw ValidationException::withMessages([
                    'horasRequeridas' =>
                        'La carta no tiene horas propuestas registradas.',
                ]);
            }

            // Evitar crear más de una pasantía asociada a la misma carta.
            if (Pasantia::where(
                'idCartaAprobacion',
                $carta->idCartaAprobacion
            )->exists()) {
                throw ValidationException::withMessages([
                    'carta' =>
                        'Esta carta ya tiene una pasantía asociada.',
                ]);
            }

            /*
             * La práctica debe pertenecer al estudiante de la carta
             * y continuar en estado Pendiente.
             */
            $practica = PracticaProfesional::where(
                'idPractica',
                $datos['idPractica']
            )
                ->where('cif', $carta->credencial)
                ->where('estadoPractica', 'Pendiente')
                ->lockForUpdate()
                ->first();

            if (!$practica) {
                throw ValidationException::withMessages([
                    'idPractica' =>
                        'La práctica seleccionada no pertenece a este estudiante o ya no está pendiente.',
                ]);
            }

            /*
             * Seleccionar un tutor existente o registrar uno nuevo.
             */
            if ($datos['tipoTutor'] === 'existente') {
                $tutor = Tutor::where(
                    'idTutor',
                    $datos['idTutor']
                )
                    ->where('estadoTutor', 'Activo')
                    ->first();

                if (!$tutor) {
                    throw ValidationException::withMessages([
                        'idTutor' =>
                            'El tutor seleccionado no existe o no está activo.',
                    ]);
                }
            } else {
                $usuarioTutor = Usuario::create([
                    'credencial' => $datos['credencialTutor'],
                    'nombre' => $datos['nombreTutor'],
                    'apellido' => $datos['apellidoTutor'],
                    'telefono' => $datos['telefonoTutor'] ?? null,
                    'correo' => $datos['correoTutor'],
                    'contrasena' => $datos['contrasenaTutor'],
                    'rol' => 'Tutor',
                    'estado' => 'Activo',
                ]);

                $tutor = Tutor::create([
                    'idTutor' => $usuarioTutor->credencial,
                    'credencial' => $usuarioTutor->credencial,
                    'estadoTutor' => 'Activo',
                ]);
            }

            /*
             * Crear la pasantía con los datos finales del formulario.
             * El estado inicial siempre se asigna en el servidor.
             */
            Pasantia::create([
                'idPractica' => $practica->idPractica,
                'idCartaAprobacion' => $carta->idCartaAprobacion,
                'idTutor' => $tutor->idTutor,

                'estado' => 'En progreso',
                'horasRequeridas' => $datos['horasRequeridas'],
                'horasAcumuladas' => 0,

                'fechaInicio' => $datos['fechaInicio'],
                'fechaFinEstimada' => $datos['fechaFinEstimada'],

                'empresa' => $datos['empresa'],
                'cargo' => $datos['cargo'],
            ]);

            /*
             * Aprobar la carta solo después de crear correctamente
             * la pasantía. Si algo falla, la transacción se revierte.
             */
            $carta->estadoAprobacion = 'Aprobada';
            $carta->save();
        });

        return redirect()
            ->route('coordinador.aceptacion')
            ->with(
                'success',
                'La carta fue aprobada y la pasantía se creó correctamente.'
            );
    }
}
