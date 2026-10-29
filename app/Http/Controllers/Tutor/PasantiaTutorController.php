<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Pasantia;
use Illuminate\Http\Request;

class PasantiaTutorController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            $request->user()->rol === 'Tutor',
            403
        );

        $credencialTutor = $request->user()->credencial;

        $consulta = Pasantia::query()
            ->where('idTutor', $credencialTutor)
            ->withSum([
                'registrosDeHoras as total_horas_aprobadas' => function ($query) {
                    $query->where('estadoHoras', 'Aprobada');
                },
            ], 'horasIngresadas');

        // Indicadores superiores.
        $pasantiasTotales = (clone $consulta)->count();

        $pasantiasEnProgreso = (clone $consulta)
            ->where('estado', 'En progreso')
            ->count();

        // Mostrar primero las pasantías en progreso.
        $pasantias = $consulta
            ->orderByRaw("
                CASE
                    WHEN estado = 'En progreso' THEN 0
                    ELSE 1
                END
            ")
            ->orderBy('estado')
            ->orderBy('empresa')
            ->get();

        return view('tutor.home', compact(
            'pasantias',
            'pasantiasTotales',
            'pasantiasEnProgreso'
        ));
    }

    public function show(Request $request, $idPasantia)
    {
        abort_unless(
            $request->user()->rol === 'Tutor',
            403
        );

        // El tutor solo puede consultar sus propias pasantías.
        $pasantia = Pasantia::query()
            ->where('idPasantia', $idPasantia)
            ->where('idTutor', $request->user()->credencial)
            ->withSum([
                'registrosDeHoras as total_horas_aprobadas' => function ($query) {
                    $query->where('estadoHoras', 'Aprobada');
                },
            ], 'horasIngresadas')
            ->firstOrFail();

        // Esta vista se construirá en el siguiente paso.
        return view('tutor.detalle-pasantia', compact('pasantia'));
    }
}
