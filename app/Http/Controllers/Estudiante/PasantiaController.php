<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Pasantia;
use Illuminate\Http\Request;

class PasantiaController extends Controller
{
    public function index(Request $request)
    {
        $credencial = $request->user()->credencial;

        $pasantias = Pasantia::with('practicaProfesional')
            ->whereHas('practicaProfesional', function ($query) use ($credencial) {
                $query->where('cif', $credencial);
            })
            ->withSum([
                'registrosDeHoras as total_horas_aprobadas' => function ($query) {
                    $query->where('estadoHoras', 'Aprobada');
                }
            ], 'horasIngresadas')
            ->orderByRaw("
                CASE
                    WHEN estado = 'En progreso' THEN 0
                    ELSE 1
                END
            ")
            ->orderBy('idPasantia', 'desc')
            ->get();

        return view('estudiante.pasantias', compact('pasantias'));
    }

    public function show(Request $request, $idPasantia)
    {
        $credencial = $request->user()->credencial;
    
        $pasantia = Pasantia::with([
                'documentos.formato',
                'registrosDeHoras',
            ])
            ->where('idPasantia', $idPasantia)
            ->whereHas('practicaProfesional', function ($query) use ($credencial) {
                $query->where('cif', $credencial);
            })
            ->firstOrFail();
    
        return view(
            'estudiante.pasantias-detalle',
            compact('pasantia')
        );
    }
}