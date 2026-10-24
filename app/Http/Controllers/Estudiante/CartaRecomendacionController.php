<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\CartaRecomendacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CartaRecomendacionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $cartas = CartaRecomendacion::where(
            'credencialEstudiante',
            $usuario->credencial
        )
            ->orderByDesc('idCartaRecomendacion')
            ->get();

        return view('estudiante.recomendacion', compact('cartas'));
    }

    public function create()
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        return view('estudiante.recomendacion-crear');
    }

    public function store(Request $request)
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $datos = $request->validate([
            'empresaDestino' => ['required', 'string', 'max:50'],
            'puestoCargo' => ['required', 'string', 'max:50'],
            'observacionEstudiante' => ['nullable', 'string', 'max:100'],
        ]);

        CartaRecomendacion::create([
            'empresaDestino' => $datos['empresaDestino'],
            'puestoCargo' => $datos['puestoCargo'],
            'fechaSolicitud' => now()->toDateString(),
            'rutaCartaEmitida' => null,
            'estadoCarta' => 'Pendiente',
            'idCoordinador' => null,
            'credencialEstudiante' => $usuario->credencial,
            'observacionEstudiante' => $datos['observacionEstudiante'] ?? null,
            'observacionCoordinador' => null,
        ]);

        return redirect()
            ->route('estudiante.recomendacion')
            ->with('success', 'Tu solicitud de carta de recomendación se envió correctamente.');
    }

    public function edit($idCartaRecomendacion)
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $carta = CartaRecomendacion::where(
            'credencialEstudiante',
            $usuario->credencial
        )->findOrFail($idCartaRecomendacion);

        abort_unless(
            $carta->estadoCarta === 'Pendiente',
            403
        );

        return view('estudiante.recomendacion-editar', compact('carta'));
    }

    public function update(Request $request, $idCartaRecomendacion)
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $carta = CartaRecomendacion::where(
            'credencialEstudiante',
            $usuario->credencial
        )->findOrFail($idCartaRecomendacion);

        abort_unless(
            $carta->estadoCarta === 'Pendiente',
            403
        );

        $datos = $request->validate([
            'empresaDestino' => ['required', 'string', 'max:50'],
            'puestoCargo' => ['required', 'string', 'max:50'],
            'observacionEstudiante' => ['nullable', 'string', 'max:100'],
        ]);

        $carta->update($datos);

        return redirect()
            ->route('estudiante.recomendacion')
            ->with('success', 'La solicitud se actualizó correctamente.');
    }

    public function destroy($idCartaRecomendacion)
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $carta = CartaRecomendacion::where(
            'credencialEstudiante',
            $usuario->credencial
        )->findOrFail($idCartaRecomendacion);

        abort_unless(
            $carta->estadoCarta === 'Pendiente',
            403
        );

        if ($carta->rutaCartaEmitida) {
            Storage::disk('public')->delete($carta->rutaCartaEmitida);
        }

        $carta->delete();

        return redirect()
            ->route('estudiante.recomendacion')
            ->with('success', 'La solicitud se eliminó correctamente.');
    }
}
