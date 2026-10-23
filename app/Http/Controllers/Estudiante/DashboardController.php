<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\PracticaProfesional;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // Obtener el estudiante y su plan de estudios.
        $estudiante = $usuario->estudiante()
            ->with('planEstudios')
            ->firstOrFail();

        $plan = $estudiante->planEstudios;

        abort_if(!$plan, 403, 'El estudiante no tiene un plan asignado.');

        // Crear únicamente las prácticas que todavía falten.
        DB::transaction(function () use ($estudiante, $plan) {
            $practicas = PracticaProfesional::where(
                'cif',
                $estudiante->credencial
            )->get();

            $cantidadExistente = $practicas->count();
            $cantidadRequerida = (int) $plan->cantidadPracticasRequeridas;

            for (
                $numero = $cantidadExistente + 1;
                $numero <= $cantidadRequerida;
                $numero++
            ) {
                PracticaProfesional::create([
                    'nombrePractica' => 'Práctica Profesional ' . $numero,
                    'horasRequeridas' => $plan->horasPorPractica,
                    'estadoPractica' => 'Pendiente',
                    'cif' => $estudiante->credencial,
                ]);
            }
        });

        // Obtener todas las prácticas del estudiante.
        $practicas = PracticaProfesional::where(
            'cif',
            $estudiante->credencial
        )
            ->orderBy('idPractica')
            ->get();

        return view('estudiante.home', compact(
            'usuario',
            'estudiante',
            'plan',
            'practicas'
        ));
    }
}
