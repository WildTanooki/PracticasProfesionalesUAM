<?php

namespace App\Http\Controllers;

use App\Models\RegistroDeHoras;
use App\Models\Pasantia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegistroDeHorasController extends Controller
{
    /**
     * El estudiante registra horas pendientes de revisión.
     */

    public function storeEstudiante(Request $request)
    {
        abort_unless(
            auth()->user()->rol === 'Estudiante',
            403
        );
    
        $datos = $request->validate([
            'idPasantia' => [
                'required',
                'integer',
                'exists:Pasantia,idPasantia',
            ],
            'fechaRegistro' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'horasIngresadas' => [
                'required',
                'integer',
                'min:1',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:200',
            ],
        ]);
    
        $pasantia = Pasantia::findOrFail(
            $datos['idPasantia']
        );
    
        // Verificar que la pasantía pertenece al estudiante.
        $esPropietario =
            (string) ($pasantia->practicaProfesional?->cif ?? '') ===
            (string) auth()->user()->credencial;
    
        abort_unless(
            $esPropietario,
            403,
            'Esta pasantía no te pertenece.'
        );
    
        // Verificar que la fecha esté dentro del período de la pasantía.
        $this->validarPeriodoPasantia(
            $pasantia,
            $datos['fechaRegistro']
        );
    
        // Solo se permiten registros en pasantías en progreso.
        abort_unless(
            $pasantia->estado === 'En progreso',
            422,
            'La pasantía no está en progreso.'
        );
    
        // Impedir más de un registro por fecha y pasantía.
        $existeRegistro = RegistroDeHoras::where(
            'idPasantia',
            $pasantia->idPasantia
        )
            ->whereDate(
                'fechaRegistro',
                $datos['fechaRegistro']
            )
            ->exists();
    
        if ($existeRegistro) {
            return back()
                ->withErrors([
                    'fechaRegistro' =>
                        'Ya existe un registro de horas para esta pasantía en la fecha seleccionada. Elige otra fecha.',
                ])
                ->withInput();
        }
    
        // Crear el registro pendiente de revisión por el tutor.
        RegistroDeHoras::create([
            'fechaRegistro' => $datos['fechaRegistro'],
            'horasIngresadas' => $datos['horasIngresadas'],
            'observaciones' => $datos['observaciones'] ?? null,
            'estadoHoras' => 'Pendiente',
            'idPasantia' => $pasantia->idPasantia,
            'idTutor' => null,
        ]);
    
        return back()->with(
            'success',
            'Las horas se registraron correctamente y están pendientes de revisión.'
        );
    }

    /**
     * El tutor registra horas directamente aprobadas.
     */
    public function storeTutor(Request $request)
    {
        abort_unless(
            $request->user()->rol === 'Tutor',
            403
        );

        $datos = $request->validate([
            'idPasantia' => [
                'required',
                'integer',
                'exists:Pasantia,idPasantia',
            ],
            'fechaRegistro' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'horasIngresadas' => [
                'required',
                'integer',
                'min:1',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:200',
            ],
        ]);

        $pasantia = Pasantia::findOrFail(
            $datos['idPasantia']
        );

        // Verificar que el tutor tenga asignada la pasantía.
        abort_unless(
            $pasantia->idTutor !== null &&
            (string) $pasantia->idTutor ===
                (string) $request->user()->credencial,
            403,
            'No tienes asignada esta pasantía.'
        );

        abort_unless(
            $pasantia->estado === 'En progreso',
            422,
            'La pasantía no está en progreso.'
        );

        $this->validarPeriodoPasantia(
            $pasantia,
            $datos['fechaRegistro']
        );

        // No permitir dos registros para la misma fecha.
        $existeRegistro = RegistroDeHoras::where(
            'idPasantia',
            $pasantia->idPasantia
        )
            ->whereDate(
                'fechaRegistro',
                $datos['fechaRegistro']
            )
            ->exists();

        if ($existeRegistro) {
            return back()
                ->withErrors([
                    'fechaRegistro' =>
                        'Ya existe un registro de horas para esta pasantía en esa fecha.',
                ])
                ->withInput();
        }

        RegistroDeHoras::create([
            'fechaRegistro' => $datos['fechaRegistro'],
            'horasIngresadas' => $datos['horasIngresadas'],
            'observaciones' => $datos['observaciones'] ?? null,
            'estadoHoras' => 'Aprobada',
            'idPasantia' => $pasantia->idPasantia,
            'idTutor' => $request->user()->credencial,
        ]);

        return back()->with(
            'success',
            'Las horas se registraron y aprobaron correctamente.'
        );
    }

    /**
     * Aprobar varios registros pendientes de una pasantía.
     */
    public function aprobarMultiples(
        Request $request,
        int $idPasantia
    ) {
        abort_unless(
            $request->user()->rol === 'Tutor',
            403
        );

        $datos = $request->validate([
            'registros' => [
                'required',
                'array',
                'min:1',
            ],
            'registros.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ]);

        $pasantia = Pasantia::query()
            ->where('idPasantia', $idPasantia)
            ->where('idTutor', $request->user()->credencial)
            ->firstOrFail();

        abort_unless(
            $pasantia->estado === 'En progreso',
            422,
            'La pasantía no está en progreso.'
        );

        DB::transaction(function () use (
            $datos,
            $pasantia,
            $request
        ) {
            $registros = RegistroDeHoras::query()
                ->where('idPasantia', $pasantia->idPasantia)
                ->whereIn('idRegistro', $datos['registros'])
                ->where('estadoHoras', 'Pendiente')
                ->lockForUpdate()
                ->get();

            // Todos los seleccionados deben ser pendientes
            // y pertenecer a esta pasantía.
            abort_if(
                $registros->count() !== count($datos['registros']),
                422,
                'Uno o más registros ya fueron revisados o no pertenecen a esta pasantía.'
            );

            RegistroDeHoras::query()
                ->where('idPasantia', $pasantia->idPasantia)
                ->whereIn('idRegistro', $registros->pluck('idRegistro'))
                ->where('estadoHoras', 'Pendiente')
                ->update([
                    'estadoHoras' => 'Aprobada',
                    'idTutor' => $request->user()->credencial,
                ]);
        });

        return back()->with(
            'success',
            'Los registros seleccionados fueron aprobados correctamente.'
        );
    }


        /**
         * El tutor aprueba o rechaza un registro pendiente.
         */
        public function update(Request $request, int $idRegistro)
    {
        abort_unless(
            auth()->user()->rol === 'Tutor',
            403
        );

        $registro = RegistroDeHoras::findOrFail($idRegistro);
        $pasantia = $registro->pasantia;

        // Verificar que la pasantía esté asignada al tutor autenticado.
        abort_unless(
            $pasantia &&
            $pasantia->idTutor !== null &&
            (string) $pasantia->idTutor ===
                (string) auth()->user()->credencial,
            403,
            'No tienes asignada esta pasantía.'
        );

        $datos = $request->validate([
            'observaciones' => [
                'nullable',
                'string',
                'max:200',
            ],
            'estadoHoras' => [
                'required',
                Rule::in(['Aprobada', 'Rechazada']),
            ],
        ]);

        DB::transaction(function () use ($registro, $datos) {
            $registro->update([
                'observaciones' => $datos['observaciones'] ?? null,
                'estadoHoras' => $datos['estadoHoras'],
                'idTutor' => auth()->user()->credencial,
            ]);
        });

        return back()->with(
            'success',
            'El registro de horas se actualizó correctamente.'
        );
    }
    
    /**
     * Comprueba que la fecha de registro esté dentro
     * del período de la pasantía, inclusive.
     */
    private function validarPeriodoPasantia(
        Pasantia $pasantia,
        string $fechaRegistro
    ): void {
        abort_unless(
            $pasantia->fechaInicio !== null &&
            $pasantia->fechaFinEstimada !== null,
            422,
            'La pasantía debe tener fechas de inicio y finalización definidas.'
        );

        $fechaInicio = $pasantia->fechaInicio->toDateString();
        $fechaFin = $pasantia->fechaFinEstimada->toDateString();

        abort_if(
            $fechaInicio > $fechaFin,
            422,
            'El período de la pasantía no es válido.'
        );

        abort_if(
            $fechaRegistro < $fechaInicio ||
            $fechaRegistro > $fechaFin,
            422,
            'La fecha del registro debe estar dentro del período de la pasantía.'
        );
    }
}
