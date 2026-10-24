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

    //editar
    
public function edit($idCartaAprobacion)
{
    $usuario = auth()->user();

    abort_unless($usuario->rol === 'Estudiante', 403);

    // Buscar únicamente una carta perteneciente al estudiante autenticado.
    $carta = CartaAprobacion::where(
        'credencial',
        $usuario->credencial
    )->findOrFail($idCartaAprobacion);

    // Solo se permite editar solicitudes pendientes.
    abort_unless( in_array($carta->estadoAprobacion, ['Pendiente', 'A corregir']), 403);

    return view('estudiante.aceptacion-editar', compact('carta'));
}

public function update(Request $request, $idCartaAprobacion)
{
    $usuario = auth()->user();

    abort_unless($usuario->rol === 'Estudiante', 403);

    // Verificar que la carta pertenezca al estudiante autenticado.
    $carta = CartaAprobacion::where(
        'credencial',
        $usuario->credencial
    )->findOrFail($idCartaAprobacion);

    // No permitir cambios si la solicitud ya fue revisada.
    abort_unless( in_array($carta->estadoAprobacion, ['Pendiente', 'A corregir']), 403);

    $datos = $request->validate([
        'empresaDestino' => ['required', 'string', 'max:50'],
        'puestoCargo' => ['required', 'string', 'max:50'],
        'fechaInicio' => ['required', 'date'],
        'fechaFin' => ['nullable', 'date', 'after_or_equal:fechaInicio'],
        'nombreTutor' => ['required', 'string', 'max:30'],
        'apellidoTutor' => ['required', 'string', 'max:30'],
        'telefonoTutor' => ['required', 'string', 'max:30'],
        'correoTutor' => ['required', 'email', 'max:100'],
        'horasPropuestas' => ['required', 'integer', 'min:1'],
        'documento' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],
    ]);

    // Actualizar el documento solamente si se subió uno nuevo.
    if ($request->hasFile('documento')) {
        $rutaAnterior = $carta->rutaArchivo;

        $datos['rutaArchivo'] = $request->file('documento')
            ->store('cartas-aceptacion', 'public');

        // Eliminar el archivo anterior después de guardar el nuevo.
        if ($rutaAnterior) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete($rutaAnterior);
        }
    }

    unset($datos['documento']);

    $carta->update($datos);

    return redirect()
        ->route('estudiante.aceptacion')
        ->with('success', 'La solicitud se actualizó correctamente.');
        }
}