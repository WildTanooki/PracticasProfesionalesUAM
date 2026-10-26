<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Models\Pasantia;
use App\Services\GestionDocumentalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentoController extends Controller
{
    public function __construct(
        private GestionDocumentalService $gestionDocumental
    ) {
    }

    /**
     * Credencial del estudiante autenticado.
     */
    private function credencialEstudiante(): string
    {
        $usuario = auth()->user();

        abort_unless($usuario && $usuario->rol === 'Estudiante', 403);

        return $usuario->credencial;
    }

    /**
     * Pasantías que pertenecen al estudiante autenticado.
     */
    private function pasantiasPropias(string $credencial): Builder
    {
        return Pasantia::whereHas(
            'practicaProfesional',
            function ($consulta) use ($credencial) {
                $consulta->where('cif', $credencial);
            }
        );
    }

    /**
     * Formatos requeridos de una pasantía y el estado de cada documento.
     */
    public function index(Request $request)
    {
        $credencial = $this->credencialEstudiante();

        $pasantias = $this->pasantiasPropias($credencial)
            ->with('practicaProfesional.estudiante.planEstudios')
            ->orderByRaw("
                CASE
                    WHEN estado = 'En progreso' THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('idPasantia')
            ->get();

        $pasantia = $pasantias->firstWhere(
            'idPasantia',
            (int) $request->query('pasantia')
        ) ?? $pasantias->first();

        $formatos = collect();
        $documentos = collect();

        if ($pasantia) {
            $formatos = $this->gestionDocumental
                ->formatosRequeridos($pasantia)
                ->orderBy('nombreFormato')
                ->get();

            $documentos = Documento::with('ultimaVersion')
                ->where('idPasantia', $pasantia->idPasantia)
                ->get()
                ->keyBy('idFormato');
        }

        return view('estudiante.documentos', [
            'pasantias' => $pasantias,
            'pasantia' => $pasantia,
            'formatos' => $formatos,
            'documentos' => $documentos,
            'gestionDocumental' => $this->gestionDocumental,
        ]);
    }

    /**
     * Primera carga o reenvío de un documento.
     */
    public function store(Request $request, $idPasantia)
    {
        $credencial = $this->credencialEstudiante();

        $pasantia = $this->pasantiasPropias($credencial)
            ->with('practicaProfesional.estudiante.planEstudios')
            ->where('idPasantia', $idPasantia)
            ->firstOrFail();

        $datos = $request->validate([
            'idFormato' => ['required', 'integer'],
            'archivo' => [
                'required',
                'file',
                'mimes:' . implode(',', config('documentos.extensiones')),
                'max:' . config('documentos.tamano_maximo'),
            ],
        ], [
            'idFormato.required' => 'Debes seleccionar un formato.',
            'archivo.required' => 'Debes adjuntar un archivo.',
            'archivo.mimes' => 'El archivo debe ser PDF o Word.',
            'archivo.max' => 'El archivo no puede superar los 5 MB.',
        ]);

        // El formato debe ser uno de los requeridos para la carrera del estudiante.
        $formato = $this->gestionDocumental
            ->formatosRequeridos($pasantia)
            ->where('idFormato', $datos['idFormato'])
            ->first();

        if ($formato === null) {
            throw ValidationException::withMessages([
                'idFormato' => 'El formato seleccionado no corresponde a tu carrera.',
            ]);
        }

        $documento = $this->gestionDocumental->enviar(
            $pasantia,
            $formato,
            $request->file('archivo')
        );

        $mensaje = $documento->wasRecentlyCreated
            ? 'Documento enviado correctamente.'
            : 'Se envió una nueva versión del documento.';

        return redirect()
            ->route('estudiante.documentos', ['pasantia' => $pasantia->idPasantia])
            ->with('success', $mensaje);
    }

    /**
     * Historial de versiones de un documento propio.
     */
    public function show($idDocumento)
    {
        $credencial = $this->credencialEstudiante();

        $documento = Documento::with(['formato', 'pasantia', 'historial'])
            ->where('idDocumento', $idDocumento)
            ->whereHas('pasantia.practicaProfesional', function ($consulta) use ($credencial) {
                $consulta->where('cif', $credencial);
            })
            ->firstOrFail();

        return view('estudiante.documentos-detalle', [
            'documento' => $documento,
            'permiteReenvio' => $this->gestionDocumental->permiteReenvio($documento),
        ]);
    }

    /**
     * Descargar una versión de un documento propio.
     */
    public function descargar($idHistorial)
    {
        $credencial = $this->credencialEstudiante();

        $version = DocumentoHistorial::where('idHistorial', $idHistorial)
            ->whereHas('documento.pasantia.practicaProfesional', function ($consulta) use ($credencial) {
                $consulta->where('cif', $credencial);
            })
            ->firstOrFail();

        $disco = Storage::disk(GestionDocumentalService::DISCO);

        abort_unless($disco->exists($version->archivo), 404);

        return $disco->download(
            $version->archivo,
            $this->gestionDocumental->nombreDescarga($version)
        );
    }
}
