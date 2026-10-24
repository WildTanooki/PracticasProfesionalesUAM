<?php

namespace App\Http\Controllers;

use App\Models\CoordinacionAcademica;
use App\Models\Estudiante;
use App\Models\Formato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormatoController extends Controller
{
    /**
     * Mostrar los formatos disponibles para la carrera
     * del coordinador autenticado.
     */
    public function index()
    {
        abort_unless(
            auth()->user()->rol === 'Coordinador',
            403
        );

        $coordinador = CoordinacionAcademica::where(
            'credencial',
            auth()->user()->credencial
        )->firstOrFail();

        $formatos = Formato::whereHas(
            'coordinador',
            function ($consulta) use ($coordinador) {
                $consulta->where(
                    'idCarrera',
                    $coordinador->idCarrera
                );
            }
        )->orderBy('nombreFormato')->get();

        return view(
            'coordinador.formatos',
            compact('formatos')
        );
    }

 
    /**
     * Registrar un nuevo formato.
     */
    public function store(Request $request)
    {
        abort_unless(
            auth()->user()->rol === 'Coordinador',
            403
        );

        $datos = $request->validate([
            'nombreFormato' => [
                'required',
                'string',
                'max:50'
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:200'
            ],
            'archivo' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120'
            ],
            'estado' => [
                'nullable',
                'string',
                'max:20'
            ]
        ]);

        $coordinador = CoordinacionAcademica::where(
            'credencial',
            auth()->user()->credencial
        )->firstOrFail();

        // Inicializar sin archivo.
        $rutaArchivo = null;

        // Guardar el archivo solo si se ha seleccionado uno.
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');

            $nombreOriginal = pathinfo(
                $archivo->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $extension = $archivo->getClientOriginalExtension();

            $nombreArchivo = $nombreOriginal . '_' . uniqid() . '.' . $extension;

            $rutaArchivo = $archivo->storeAs(
                'formatos',
                $nombreArchivo,
                'local'
            );
        }

        try {
            Formato::create([
                'nombreFormato' => $datos['nombreFormato'],
                'archivo' => $rutaArchivo,
                'estado' => $datos['estado'] ?? 'Activo',
                'ultimaEdicion' => now(),
                'idCoordinador' => $coordinador->idCoordinador,
            ]);
        } catch (\Throwable $e) {
            // Eliminar el archivo únicamente si se guardó uno.
            if ($rutaArchivo !== null) {
                Storage::disk('local')->delete($rutaArchivo);
            }

            throw $e;
        }

        return redirect()
            ->route('coordinador.formatos')
            ->with(
                'success',
                'Formato registrado correctamente.'
            );
    }


    /**
     * Descargar un formato autorizado para la carrera.
     */
    public function descargar($idFormato)
    {
        abort_unless(
            auth()->user()->rol === 'Coordinador',
            403
        );

        $coordinador = CoordinacionAcademica::where(
            'credencial',
            auth()->user()->credencial
        )->firstOrFail();

        $formato = Formato::where(
            'idFormato',
            $idFormato
        )->whereHas(
            'coordinador',
            function ($consulta) use ($coordinador) {
                $consulta->where(
                    'idCarrera',
                    $coordinador->idCarrera
                );
            }
        )->firstOrFail();

        abort_unless(
            $formato->archivo &&
            Storage::disk('local')->exists($formato->archivo),
            404
        );

        return Storage::disk('local')->download(
            $formato->archivo
        );
    }

    
public function update(Request $request, $idFormato)
{
    abort_unless(
        auth()->user()->rol === 'Coordinador',
        403
    );

    $datos = $request->validate([
        'nombreFormato' => ['required', 'string', 'max:50'],
        'descripcion' => ['nullable', 'string', 'max:200'],
        'estado' => ['required', 'in:Activo,Inactivo'],
        'archivo' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
    ]);

    $coordinador = CoordinacionAcademica::where(
        'credencial',
        auth()->user()->credencial
    )->firstOrFail();

    $formato = Formato::where('idFormato', $idFormato)
        ->whereHas('coordinador', function ($consulta) use ($coordinador) {
            $consulta->where('idCarrera', $coordinador->idCarrera);
        })
        ->firstOrFail();

    $formato->nombreFormato = $datos['nombreFormato'];
    $formato->descripcion = $datos['descripcion'];
    $formato->estado = $datos['estado'];
    $formato->ultimaEdicion = now();

    $rutaAnterior = null;
    $nuevaRuta = null;

    if ($request->hasFile('archivo')) {
        $archivo = $request->file('archivo');

        $nombreOriginal = pathinfo(
            $archivo->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = $archivo->getClientOriginalExtension();

        $nombreArchivo = $nombreOriginal . '_' . uniqid() . '.' . $extension;

        $nuevaRuta = $archivo->storeAs(
            'formatos',
            $nombreArchivo,
            'local'
        );

        if (!$nuevaRuta) {
            return back()->withErrors([
                'archivo' => 'No se pudo guardar el archivo nuevo.',
            ])->withInput();
        }

        $rutaAnterior = $formato->archivo;
        $formato->archivo = $nuevaRuta;
    }

    try {
        $formato->save();
    } catch (\Throwable $e) {
        if ($nuevaRuta) {
            Storage::disk('local')->delete($nuevaRuta);
        }

        throw $e;
    }

    if ($rutaAnterior && $nuevaRuta) {
        Storage::disk('local')->delete($rutaAnterior);
    }

    return redirect()
        ->route('coordinador.formatos')
        ->with('success', 'Formato actualizado correctamente.');
}

        /**
         * Mostrar únicamente los formatos activos de la carrera
         * del estudiante autenticado.
         */
        public function formatosEstudiante()
        {
            abort_unless(
                auth()->user()->rol === 'Estudiante',
                403
            );

            // Obtener el estudiante y su carrera mediante el plan de estudios.
            $estudiante = Estudiante::with('planEstudios')
                ->where('credencial', auth()->user()->credencial)
                ->firstOrFail();

            $idCarrera = $estudiante->planEstudios?->idCarrera;

            abort_if($idCarrera === null, 403, 'El estudiante no tiene una carrera asociada.');

            // Mostrar solo formatos activos cuyos coordinadores
            // pertenezcan a la carrera del estudiante.
            $formatos = Formato::where('estado', 'Activo')
                ->whereHas('coordinador', function ($consulta) use ($idCarrera) {
                    $consulta->where('idCarrera', $idCarrera);
                })
                ->orderBy('nombreFormato')
                ->get();

            return view(
                'estudiante.formatos',
                compact('formatos')
            );
        }

        /**
         * Descargar un formato activo de la carrera del estudiante.
         */
        public function descargarEstudiante($idFormato)
        {
            abort_unless(
                auth()->user()->rol === 'Estudiante',
                403
            );

            $estudiante = Estudiante::with('planEstudios')
                ->where('credencial', auth()->user()->credencial)
                ->firstOrFail();

            $idCarrera = $estudiante->planEstudios?->idCarrera;

            abort_if($idCarrera === null, 403);

            // Verificar nuevamente carrera y estado antes de descargar.
            $formato = Formato::where('idFormato', $idFormato)
                ->where('estado', 'Activo')
                ->whereHas('coordinador', function ($consulta) use ($idCarrera) {
                    $consulta->where('idCarrera', $idCarrera);
                })
                ->firstOrFail();

            abort_unless(
                $formato->archivo &&
                Storage::disk('local')->exists($formato->archivo),
                404
            );

            return Storage::disk('local')->download(
                $formato->archivo
            );
        }
}