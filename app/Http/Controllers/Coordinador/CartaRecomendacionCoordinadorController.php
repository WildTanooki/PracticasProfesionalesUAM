<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CartaRecomendacion;
use App\Models\CoordinacionAcademica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CartaRecomendacionCoordinadorController extends Controller
{
    /**
     * Obtener el coordinador autenticado.
     */
    private function coordinadorAutenticado(): CoordinacionAcademica
    {
        abort_unless(
            auth()->check() &&
            auth()->user()->rol === 'Coordinador',
            403
        );

        return CoordinacionAcademica::where(
            'credencial',
            auth()->user()->credencial
        )->firstOrFail();
    }

    /**
     * Listar las solicitudes de los estudiantes de la carrera
     * del coordinador autenticado.
     */
    public function index()
    {
        $coordinador = $this->coordinadorAutenticado();

        $solicitudes = CartaRecomendacion::with([
            'estudiante.usuario',
            'estudiante.planEstudios.carrera',
        ])
            ->whereHas(
                'estudiante.planEstudios',
                function ($consulta) use ($coordinador) {
                    $consulta->where(
                        'idCarrera',
                        $coordinador->idCarrera
                    );
                }
            )
            ->orderByDesc('fechaSolicitud')
            ->get();

        return view(
            'coordinador.recomendacion',
            compact('solicitudes')
        );
    }

    /**
     * Mostrar el detalle de una solicitud perteneciente
     * a la carrera del coordinador.
     */
    public function show($idCartaRecomendacion)
    {
        $coordinador = $this->coordinadorAutenticado();

        $solicitud = CartaRecomendacion::with([
            'estudiante.usuario',
            'estudiante.planEstudios.carrera',
        ])
            ->where(
                'idCartaRecomendacion',
                $idCartaRecomendacion
            )
            ->whereHas(
                'estudiante.planEstudios',
                function ($consulta) use ($coordinador) {
                    $consulta->where(
                        'idCarrera',
                        $coordinador->idCarrera
                    );
                }
            )
            ->firstOrFail();

        return view(
            'coordinador.recomendacion-detalle',
            compact('solicitud')
        );
    }

    /**
     * Aprobar o rechazar una solicitud.
     */
    public function resolver(
        Request $request,
        $idCartaRecomendacion
    ) {
        $coordinador = $this->coordinadorAutenticado();

        // La solicitud debe pertenecer a la carrera del coordinador.
        $solicitud = CartaRecomendacion::where(
            'idCartaRecomendacion',
            $idCartaRecomendacion
        )
            ->whereHas(
                'estudiante.planEstudios',
                function ($consulta) use ($coordinador) {
                    $consulta->where(
                        'idCarrera',
                        $coordinador->idCarrera
                    );
                }
            )
            ->firstOrFail();

        // Impedir que una solicitud finalizada se resuelva otra vez.
        abort_if(
            in_array(
                $solicitud->estadoCarta,
                ['Generada', 'Rechazada'],
                true
            ),
            403,
            'Esta solicitud ya fue resuelta.'
        );

        $datos = $request->validate([
            'estadoCarta' => [
                'required',
                Rule::in(['Generada', 'Rechazada']),
            ],
            'observacionCoordinador' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'archivoCarta' => [
                Rule::requiredIf(
                    $request->input('estadoCarta') === 'Generada'
                ),
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
        ], [
            'archivoCarta.required' =>
                'Debes adjuntar la carta firmada y sellada para generar la solicitud.',
            'archivoCarta.mimes' =>
                'La carta debe estar en formato PDF, DOC o DOCX.',
            'archivoCarta.max' =>
                'El archivo no puede superar los 10 MB.',
        ]);

        $rutaNueva = null;
        $rutaAnterior = $solicitud->rutaCartaEmitida;

        // Guardar la carta únicamente cuando la solicitud se aprueba.
        if ($datos['estadoCarta'] === 'Generada') {
            $archivo = $request->file('archivoCarta');

            $nombreOriginal = pathinfo(
                $archivo->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $extension = $archivo->getClientOriginalExtension();

            $nombreArchivo = $nombreOriginal
                . '_'
                . uniqid()
                . '.'
                . $extension;

            $rutaNueva = $archivo->storeAs(
                'cartas-recomendacion',
                $nombreArchivo,
                'local'
            );

            if (!$rutaNueva) {
                return back()
                    ->withErrors([
                        'archivoCarta' =>
                            'No se pudo guardar el archivo de la carta.',
                    ])
                    ->withInput();
            }
        }

        try {
            DB::transaction(function () use (
                $solicitud,
                $datos,
                $rutaNueva,
                $coordinador
            ) {
                $solicitud->estadoCarta = $datos['estadoCarta'];

                $solicitud->observacionCoordinador =
                    $datos['observacionCoordinador'] ?? null;

                // Registrar quién tomó la decisión.
                $solicitud->idCoordinador =
                    $coordinador->idCoordinador;

                // La carta emitida solo se actualiza al aprobar.
                if ($datos['estadoCarta'] === 'Generada') {
                    $solicitud->rutaCartaEmitida = $rutaNueva;
                }

                $solicitud->save();
            });
        } catch (\Throwable $e) {
            // Si falla la transacción, eliminar el archivo nuevo.
            if ($rutaNueva) {
                Storage::disk('local')->delete($rutaNueva);
            }

            throw $e;
        }

        // Eliminar el archivo anterior después de guardar correctamente.
        if (
            $rutaAnterior &&
            $rutaNueva &&
            $rutaAnterior !== $rutaNueva
        ) {
            Storage::disk('local')->delete($rutaAnterior);
        }

        return redirect()
            ->route('coordinador.recomendacion')
            ->with(
                'success',
                $datos['estadoCarta'] === 'Generada'
                    ? 'Solicitud generada y carta guardada correctamente.'
                    : 'Solicitud rechazada correctamente.'
            );
    }
}
