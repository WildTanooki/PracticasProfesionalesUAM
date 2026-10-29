<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\CoordinacionAcademica;
use App\Models\Documento;
use App\Models\Pasantia;
use App\Models\RegistroDeHoras;
use App\Services\GestionDocumentalService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(
        Request $request,
        GestionDocumentalService $gestionDocumental
    ) {
        $usuario = auth()->user();

        abort_unless(
            $usuario && $usuario->rol === 'Coordinador',
            403,
            'No tienes permiso para acceder a este panel.'
        );

        $coordinacion = CoordinacionAcademica::where(
            'credencial',
            $usuario->credencial
        )->firstOrFail();

        // Documentos correspondientes únicamente a la carrera
        // del coordinador autenticado.
        $documentosCarrera = Documento::whereHas(
            'pasantia.practicaProfesional.estudiante.planEstudios',
            function ($query) use ($coordinacion) {
                $query->where(
                    'idCarrera',
                    $coordinacion->idCarrera
                );
            }
        );

        // Indicadores de documentos.
        $porRevisar = (clone $documentosCarrera)
            ->where('estadoArchivo', 'Pendiente')
            ->count();

        $aprobados = (clone $documentosCarrera)
            ->where('estadoArchivo', 'Aprobado')
            ->count();

        $desaprobados = (clone $documentosCarrera)
            ->where('estadoArchivo', 'Rechazado')
            ->count();

        $aCorregir = (clone $documentosCarrera)
            ->where('estadoArchivo', 'A corregir')
            ->count();

        // Consultar pasantías de la carrera.
        $pasantias = Pasantia::whereHas(
            'practicaProfesional.estudiante.planEstudios',
            function ($query) use ($coordinacion) {
                $query->where(
                    'idCarrera',
                    $coordinacion->idCarrera
                );
            }
        )
            ->with([
                'documentos.formato',
                'registrosDeHoras',
                'practicaProfesional.estudiante.usuario',
                'practicaProfesional.estudiante.planEstudios.carrera',
            ])
            ->orderByDesc('idPasantia')
            ->get();

        // Calcular progreso documental por pasantía.
        $pasantias->each(function ($pasantia) use (
            $gestionDocumental
        ) {
            $formatos = $gestionDocumental
                ->formatosRequeridos($pasantia)
                ->get();

            $idsFormatosRequeridos = $formatos
                ->pluck('idFormato')
                ->unique()
                ->values();

            $documentos = $pasantia->documentos;

            // Considerar solo documentos de formatos requeridos.
            $documentosRequeridos = $documentos->filter(
                function ($documento) use ($idsFormatosRequeridos) {
                    return $idsFormatosRequeridos->contains(
                        $documento->idFormato
                    );
                }
            );

            // Un formato cuenta como entregado si tiene un documento.
            // Se cuentan formatos distintos, no filas de documentos.
            $formatosEntregados = $documentosRequeridos
                ->pluck('idFormato')
                ->unique()
                ->count();

            // Un formato se considera aprobado si tiene un documento
            // con estado Aprobado.
            $formatosAprobados = $documentosRequeridos
                ->where('estadoArchivo', 'Aprobado')
                ->pluck('idFormato')
                ->unique()
                ->count();

            $totalFormatos = $idsFormatosRequeridos->count();

            $todosAprobados = $totalFormatos > 0
                && $formatosEntregados === $totalFormatos
                && $formatosAprobados === $totalFormatos;

            $pasantia->totalFormatos = $totalFormatos;
            $pasantia->documentosEntregados = $formatosEntregados;
            $pasantia->documentosAprobados = $formatosAprobados;

            $pasantia->estadoDocumental = $todosAprobados
                ? 'Aprobados'
                : 'Pendientes';
        });

        // Filtro por estado global.
        $estado = $request->query('estado', '');

        if (in_array($estado, ['Aprobados', 'Pendientes'], true)) {
            $pasantias = $pasantias
                ->filter(
                    fn ($pasantia) =>
                        $pasantia->estadoDocumental === $estado
                )
                ->values();
        } else {
            $estado = '';
        }

        // Búsqueda por estudiante, carrera o empresa.
        $buscar = trim($request->query('buscar', ''));

        if ($buscar !== '') {
            $termino = mb_strtolower($buscar);

            $pasantias = $pasantias
                ->filter(function ($pasantia) use ($termino) {
                    $estudiante = $pasantia
                        ->practicaProfesional?->estudiante;

                    $nombre = $estudiante?->usuario?->nombre ?? '';
                    $apellido = $estudiante?->usuario?->apellido ?? '';

                    $carrera = $estudiante
                        ?->planEstudios?->carrera?->nombre ?? '';

                    $empresa = $pasantia->empresa ?? '';

                    $texto = mb_strtolower(
                        "{$nombre} {$apellido} {$carrera} {$empresa}"
                    );

                    return str_contains($texto, $termino);
                })
                ->values();
        }

        return view('coordinador.home', compact(
            'coordinacion',
            'pasantias',
            'porRevisar',
            'aprobados',
            'desaprobados',
            'aCorregir',
            'buscar',
            'estado'
        ));
    }
}
