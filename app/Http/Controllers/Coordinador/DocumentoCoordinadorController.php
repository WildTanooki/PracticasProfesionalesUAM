<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CoordinacionAcademica;
use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Services\GestionDocumentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentoCoordinadorController extends Controller
{
    public function __construct(
        private GestionDocumentalService $gestionDocumental
    ) {
    }

    /**
     * Obtener la coordinación del usuario autenticado.
     */
    private function obtenerCoordinacion(): CoordinacionAcademica
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario && $usuario->rol === 'Coordinador',
            403,
            'No tienes permiso para acceder a esta sección.'
        );

        return CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();
    }

    /**
     * Restringir a documentos de estudiantes de la carrera del coordinador.
     */
    private function documentosDeCarrera(CoordinacionAcademica $coordinacion)
    {
        return Documento::whereHas(
            'pasantia.practicaProfesional.estudiante.planEstudios',
            function ($consulta) use ($coordinacion) {
                $consulta->where('idCarrera', $coordinacion->idCarrera);
            }
        );
    }

    /**
     * Listar documentos de la carrera, por defecto los pendientes.
     */
    public function index(Request $request)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $estados = ['Pendiente', 'A corregir', 'Rechazado', 'Aprobado'];

        $estado = in_array($request->query('estado'), $estados, true)
            ? $request->query('estado')
            : ($request->query('estado') === 'Todos' ? 'Todos' : 'Pendiente');

        $documentos = $this->documentosDeCarrera($coordinacion)
            ->with([
                'formato',
                'ultimaVersion',
                'pasantia.practicaProfesional.estudiante.usuario',
            ])
            ->when($estado !== 'Todos', function ($consulta) use ($estado) {
                $consulta->where('estadoArchivo', $estado);
            })
            ->orderByDesc('idDocumento')
            ->get();

        return view(
            'coordinador.documentos',
            compact('documentos', 'estados', 'estado')
        );
    }

    /**
     * Mostrar todos los documentos de una pasantía específica.
     */
    public function documentosDePasantia($idPasantia)
    {
        $coordinacion = $this->obtenerCoordinacion();
    
        // Verificar que la pasantía pertenezca a la carrera del coordinador.
        $pasantia = \App\Models\Pasantia::query()
            ->where('idPasantia', $idPasantia)
            ->whereHas(
                'practicaProfesional.estudiante.planEstudios',
                function ($consulta) use ($coordinacion) {
                    $consulta->where('idCarrera', $coordinacion->idCarrera);
                }
            )
            ->with([
                'practicaProfesional.estudiante.usuario',
                'practicaProfesional.estudiante.planEstudios.carrera',
                'documentos.formato',
                'documentos.ultimaVersion',
            ])
            ->firstOrFail();
    
        // Obtener todos los documentos registrados en la pasantía.
        $documentos = $pasantia->documentos
            ->sortBy('idDocumento')
            ->values();
    
        // Obtener los formatos activos requeridos para esta carrera.
        $formatos = $this->gestionDocumental
            ->formatosRequeridos($pasantia)
            ->get();
    
        $idsFormatosRequeridos = $formatos
            ->pluck('idFormato')
            ->unique()
            ->values();
    
        $formatosRequeridosTotal = $idsFormatosRequeridos->count();
    
        // Considerar únicamente los documentos correspondientes
        // a formatos requeridos para calcular el progreso.
        $documentosRequeridos = $documentos->filter(
            fn ($documento) => $idsFormatosRequeridos
                ->contains($documento->idFormato)
        );
    
        // Cantidad de formatos requeridos que ya fueron entregados.
        $formatosEntregados = $documentosRequeridos
            ->pluck('idFormato')
            ->unique()
            ->count();
    
        // Cantidad de formatos requeridos que tienen aprobación.
        $formatosAprobados = $documentosRequeridos
            ->where('estadoArchivo', 'Aprobado')
            ->pluck('idFormato')
            ->unique()
            ->count();
    
        // Cantidad de documentos por estado.
        $documentosPendientes = $documentosRequeridos
            ->where('estadoArchivo', 'Pendiente')
            ->count();
    
        $documentosACorregir = $documentosRequeridos
            ->where('estadoArchivo', 'A corregir')
            ->count();
    
        $documentosRechazados = $documentosRequeridos
            ->where('estadoArchivo', 'Rechazado')
            ->count();
    
        // La pasantía está aprobada si todos los formatos requeridos
        // fueron entregados y aprobados.
        $estadoGlobal = (
            $formatosRequeridosTotal > 0
            && $formatosEntregados === $formatosRequeridosTotal
            && $formatosAprobados === $formatosRequeridosTotal
        ) ? 'Aprobados' : 'Pendientes';
    
        return view('coordinador.documentos-pasantia', compact(
            'pasantia',
            'documentos',
            'formatosRequeridosTotal',
            'formatosEntregados',
            'formatosAprobados',
            'documentosPendientes',
            'documentosACorregir',
            'documentosRechazados',
            'estadoGlobal'
        ));
    }

    /**
     * Detalle de un documento con su historial de versiones.
     */
    public function show($idDocumento)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $documento = $this->documentosDeCarrera($coordinacion)
            ->with([
                'formato',
                'historial.coordinador.usuario',
                'pasantia.practicaProfesional.estudiante.usuario',
            ])
            ->where('idDocumento', $idDocumento)
            ->firstOrFail();

        return view(
            'coordinador.documentos-detalle',
            compact('documento')
        );
    }

    /**
     * Registrar la revisión de la versión vigente.
     */
    public function revisar(Request $request, $idDocumento)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $documento = $this->documentosDeCarrera($coordinacion)
            ->where('idDocumento', $idDocumento)
            ->firstOrFail();

        $datos = $request->validate([
            'version' => ['required', 'integer', 'min:1'],
            'estadoArchivo' => [
                'required',
                Rule::in(GestionDocumentalService::ESTADOS_REVISION),
            ],
            'observacion' => [
                Rule::requiredIf(fn () => $request->input('estadoArchivo') !== 'Aprobado'),
                'nullable',
                'string',
                'max:200',
            ],
        ], [
            'estadoArchivo.required' => 'Debes seleccionar una decisión.',
            'estadoArchivo.in' => 'La decisión seleccionada no es válida.',
            'observacion.required' => 'Debes indicar la observación para el estudiante.',
            'observacion.max' => 'La observación no puede superar los 200 caracteres.',
        ]);

        $this->gestionDocumental->revisar(
            $documento,
            $coordinacion,
            (int) $datos['version'],
            $datos['estadoArchivo'],
            $datos['observacion'] ?? null
        );

        return redirect()
            ->route('coordinador.documentos.show', $documento->idDocumento)
            ->with('success', 'La revisión se registró correctamente.');
    }

    /**
     * Descargar una versión de un documento de la carrera.
     */
    public function descargar($idHistorial)
    {
        $coordinacion = $this->obtenerCoordinacion();

        $version = DocumentoHistorial::where('idHistorial', $idHistorial)
            ->whereHas(
                'documento.pasantia.practicaProfesional.estudiante.planEstudios',
                function ($consulta) use ($coordinacion) {
                    $consulta->where('idCarrera', $coordinacion->idCarrera);
                }
            )
            ->firstOrFail();

        $disco = Storage::disk(GestionDocumentalService::DISCO);

        abort_unless($disco->exists($version->archivo), 404);

        return $disco->download(
            $version->archivo,
            $this->gestionDocumental->nombreDescarga($version)
        );
    }
}
