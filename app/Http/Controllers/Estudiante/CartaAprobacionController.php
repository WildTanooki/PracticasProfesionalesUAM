<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\CartaAprobacion;
use Illuminate\Http\Request;

class CartaAprobacionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $cartas = CartaAprobacion::where(
            'credencial',
            $usuario->credencial
        )
            ->orderByDesc('idCartaAprobacion')
            ->get();

        return view('estudiante.aceptacion', compact('cartas'));
    }

    public function create()
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        return view('estudiante.aceptacion-crear');
    }

    public function store(Request $request)
    {
        $usuario = auth()->user();

        abort_unless($usuario->rol === 'Estudiante', 403);

        $datos = $request->validate([
            'empresaDestino' => ['required', 'string', 'max:50'],
            'puestoCargo' => ['required', 'string', 'max:50'],
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['nullable', 'date', 'after_or_equal:fechaInicio'],
            'nombreTutor' => ['required', 'string', 'max:30'],
            'apellidoTutor' => ['required', 'string', 'max:30'],
            'telefonoTutor' => ['required', 'string', 'max:30'],
            'correoTutor' => ['required', 'email', 'max:100'],
            'documento' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'horasPropuestas' => ['required', 'integer', 'min:1'],
        ]);

        $rutaArchivo = $request->file('documento')
            ->store('cartas-aceptacion', 'public');

        CartaAprobacion::create([
            'credencial' => $usuario->credencial,
            'idPasantia' => null,
            'estadoAprobacion' => 'Pendiente',
            'rutaArchivo' => $rutaArchivo,
            'fechaEmision' => now()->toDateString(),
            'observacion' => null,
            'empresaDestino' => $datos['empresaDestino'],
            'puestoCargo' => $datos['puestoCargo'],
            'fechaInicio' => $datos['fechaInicio'],
            'fechaFin' => $datos['fechaFin'] ?? null,
            'nombreTutor' => $datos['nombreTutor'],
            'apellidoTutor' => $datos['apellidoTutor'],
            'telefonoTutor' => $datos['telefonoTutor'],
            'correoTutor' => $datos['correoTutor'],
            'horasPropuestas' => $datos['horasPropuestas'],
        ]);

        return redirect()
            ->route('estudiante.aceptacion')
            ->with('success', 'Tu solicitud se ha enviado correctamente.');
    }
}