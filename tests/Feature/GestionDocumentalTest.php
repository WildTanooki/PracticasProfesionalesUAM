<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Models\Pasantia;
use App\Models\Usuario;
use App\Services\GestionDocumentalService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Flujo de carga, revisión y reenvío de documentos de pasantía.
 *
 * Las tablas del proyecto vienen de una base de datos externa, así que
 * se crea aquí una copia mínima del esquema en SQLite.
 */
class GestionDocumentalTest extends TestCase
{
    private Usuario $estudiante;

    private Usuario $otroEstudiante;

    private Usuario $coordinador;

    private Usuario $coordinadorOtraCarrera;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');

        $this->crearEsquema();
        $this->crearDatos();
    }

    private function crearEsquema(): void
    {
        Schema::create('usuarios', function (Blueprint $tabla) {
            $tabla->string('credencial', 30)->primary();
            $tabla->string('nombre', 30);
            $tabla->string('apellido', 30)->nullable();
            $tabla->string('telefono', 30)->nullable();
            $tabla->string('correo', 100)->unique();
            $tabla->string('contrasena');
            $tabla->string('rol');
            $tabla->string('estado', 20)->default('Activo');
        });

        Schema::create('carrera', function (Blueprint $tabla) {
            $tabla->increments('idCarrera');
            $tabla->string('nombre', 50);
        });

        Schema::create('plan_de_estudios', function (Blueprint $tabla) {
            $tabla->string('idPlanEstudios', 30)->primary();
            $tabla->string('version', 30)->nullable();
            $tabla->unsignedInteger('cantidadPracticasRequeridas')->default(1);
            $tabla->unsignedInteger('horasPorPractica');
            $tabla->unsignedInteger('idCarrera');
        });

        Schema::create('coordinacion_academica', function (Blueprint $tabla) {
            $tabla->string('idCoordinador', 30)->primary();
            $tabla->string('credencial', 30)->unique();
            $tabla->unsignedInteger('idCarrera');
        });

        Schema::create('estudiante', function (Blueprint $tabla) {
            $tabla->increments('estudianteID');
            $tabla->string('credencial', 30)->unique();
            $tabla->year('anioAcademico');
            $tabla->string('idPlanEstudios', 30);
        });

        Schema::create('formatos', function (Blueprint $tabla) {
            $tabla->increments('idFormato');
            $tabla->string('archivo')->nullable();
            $tabla->string('nombreFormato', 50);
            $tabla->string('idCoordinador', 30);
            $tabla->string('estado')->default('Activo');
            $tabla->dateTime('ultimaEdicion')->nullable();
        });

        Schema::create('practicaprofesional', function (Blueprint $tabla) {
            $tabla->increments('idPractica');
            $tabla->string('nombrePractica', 50)->nullable();
            $tabla->unsignedInteger('horasRequeridas');
            $tabla->string('estadoPractica', 30)->default('Pendiente');
            $tabla->string('cif', 30);
        });

        Schema::create('pasantia', function (Blueprint $tabla) {
            $tabla->increments('idPasantia');
            $tabla->unsignedInteger('idPractica');
            $tabla->string('estado', 30)->default('Pendiente');
            $tabla->unsignedInteger('horasRequeridas');
            $tabla->unsignedInteger('horasAcumuladas')->default(0);
            $tabla->date('fechaInicio')->nullable();
            $tabla->date('fechaFinEstimada')->nullable();
            $tabla->string('idTutor', 30)->nullable();
            $tabla->string('empresa', 50);
            $tabla->string('cargo', 50);
        });

        Schema::create('documentos', function (Blueprint $tabla) {
            $tabla->increments('idDocumento');
            $tabla->unsignedInteger('idFormato')->nullable();
            $tabla->string('archivo')->nullable();
            $tabla->string('estadoArchivo', 30)->default('Pendiente');
            $tabla->string('observacion', 200)->nullable();
            $tabla->string('idCoordinador', 30)->nullable();
            $tabla->unsignedInteger('idPasantia');
            $tabla->unique(['idPasantia', 'idFormato']);
        });

        Schema::create('documento_historial', function (Blueprint $tabla) {
            $tabla->increments('idHistorial');
            $tabla->unsignedInteger('idDocumento');
            $tabla->string('archivo');
            $tabla->unsignedInteger('version')->default(1);
            $tabla->string('estadoArchivo')->default('Pendiente');
            $tabla->string('observacion', 200)->nullable();
            $tabla->string('idCoordinador', 30)->nullable();
            $tabla->dateTime('fechaEnvio')->useCurrent();
            $tabla->dateTime('fechaRevision')->nullable();
            $tabla->unique(['idDocumento', 'version']);
            $tabla->foreign('idDocumento')->references('idDocumento')->on('documentos');
        });
    }

    private function crearDatos(): void
    {
        DB::table('carrera')->insert([
            ['idCarrera' => 1, 'nombre' => 'Ing. en Sistemas'],
            ['idCarrera' => 2, 'nombre' => 'Ing. Industrial'],
        ]);

        DB::table('plan_de_estudios')->insert([
            ['idPlanEstudios' => 'Plan001', 'horasPorPractica' => 350, 'idCarrera' => 1],
        ]);

        $this->estudiante = $this->usuario('21011890', 'Estudiante');
        $this->otroEstudiante = $this->usuario('22011890', 'Estudiante');
        $this->coordinador = $this->usuario('COO0001', 'Coordinador');
        $this->coordinadorOtraCarrera = $this->usuario('COO0002', 'Coordinador');

        DB::table('estudiante')->insert([
            ['credencial' => '21011890', 'anioAcademico' => 2026, 'idPlanEstudios' => 'Plan001'],
            ['credencial' => '22011890', 'anioAcademico' => 2026, 'idPlanEstudios' => 'Plan001'],
        ]);

        DB::table('coordinacion_academica')->insert([
            ['idCoordinador' => 'COO0001', 'credencial' => 'COO0001', 'idCarrera' => 1],
            ['idCoordinador' => 'COO0002', 'credencial' => 'COO0002', 'idCarrera' => 2],
        ]);

        DB::table('formatos')->insert([
            ['idFormato' => 1, 'nombreFormato' => 'Plan de trabajo', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 2, 'nombreFormato' => 'Informe final', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 3, 'nombreFormato' => 'Formato inactivo', 'idCoordinador' => 'COO0001', 'estado' => 'Inactivo'],
            ['idFormato' => 4, 'nombreFormato' => 'Formato de Industrial', 'idCoordinador' => 'COO0002', 'estado' => 'Activo'],
        ]);

        DB::table('practicaprofesional')->insert([
            ['idPractica' => 1, 'horasRequeridas' => 350, 'cif' => '21011890'],
            ['idPractica' => 2, 'horasRequeridas' => 350, 'cif' => '22011890'],
        ]);

        DB::table('pasantia')->insert([
            ['idPasantia' => 1, 'idPractica' => 1, 'estado' => 'En progreso', 'horasRequeridas' => 350, 'empresa' => 'Empresa A', 'cargo' => 'Desarrollador'],
            ['idPasantia' => 2, 'idPractica' => 2, 'estado' => 'En progreso', 'horasRequeridas' => 350, 'empresa' => 'Empresa B', 'cargo' => 'Analista'],
        ]);
    }

    private function usuario(string $credencial, string $rol): Usuario
    {
        return Usuario::create([
            'credencial' => $credencial,
            'nombre' => 'Usuario',
            'apellido' => $credencial,
            'correo' => "{$credencial}@prueba.com",
            'contrasena' => 'secreto123',
            'rol' => $rol,
            'estado' => 'Activo',
        ]);
    }

    private function enviar(int $idPasantia, int $idFormato, string $nombre = 'plan.pdf')
    {
        return $this->post(
            route('estudiante.documentos.store', $idPasantia),
            [
                'idFormato' => $idFormato,
                'archivo' => UploadedFile::fake()->create($nombre, 100, 'application/pdf'),
            ]
        );
    }

    private function revisar(int $idDocumento, int $version, string $estado, ?string $observacion = null)
    {
        return $this->post(
            route('coordinador.documentos.revisar', $idDocumento),
            [
                'version' => $version,
                'estadoArchivo' => $estado,
                'observacion' => $observacion,
            ]
        );
    }

    public function test_primera_carga_crea_documento_y_version_uno(): void
    {
        $this->actingAs($this->estudiante);

        $this->enviar(1, 1)
            ->assertRedirect(route('estudiante.documentos', ['pasantia' => 1]))
            ->assertSessionHasNoErrors();

        $documento = Documento::sole();
        $version = DocumentoHistorial::sole();

        $this->assertSame('Pendiente', $documento->estadoArchivo);
        $this->assertSame(1, $version->version);
        $this->assertSame('Pendiente', $version->estadoArchivo);
        $this->assertSame($documento->archivo, $version->archivo);
        Storage::disk('local')->assertExists($documento->archivo);
    }

    public function test_un_documento_por_cada_formato_requerido(): void
    {
        $this->actingAs($this->estudiante);

        $this->enviar(1, 1)->assertSessionHasNoErrors();
        $this->enviar(1, 2)->assertSessionHasNoErrors();

        $this->assertSame(2, Documento::where('idPasantia', 1)->count());
        $this->assertSame(2, DocumentoHistorial::count());
    }

    public function test_no_se_duplica_el_documento_mientras_esta_pendiente(): void
    {
        $this->actingAs($this->estudiante);

        $this->enviar(1, 1);
        $this->enviar(1, 1)->assertSessionHasErrors('archivo');

        $this->assertSame(1, Documento::count());
        $this->assertSame(1, DocumentoHistorial::count());
        // Solo queda el archivo de la primera carga.
        $this->assertCount(1, Storage::disk('local')->allFiles('documentos'));
    }

    public function test_rechaza_formatos_que_no_son_de_su_carrera_o_inactivos(): void
    {
        $this->actingAs($this->estudiante);

        $this->enviar(1, 3)->assertSessionHasErrors('idFormato');
        $this->enviar(1, 4)->assertSessionHasErrors('idFormato');

        $this->assertSame(0, Documento::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_valida_el_tipo_de_archivo(): void
    {
        $this->actingAs($this->estudiante);

        $this->post(route('estudiante.documentos.store', 1), [
            'idFormato' => 1,
            'archivo' => UploadedFile::fake()->create('virus.exe', 10),
        ])->assertSessionHasErrors('archivo');

        $this->assertSame(0, Documento::count());
    }

    public function test_estudiante_no_accede_a_pasantias_ajenas(): void
    {
        $this->actingAs($this->otroEstudiante);
        $this->enviar(2, 1)->assertSessionHasNoErrors();
        $documentoAjeno = Documento::sole();
        $versionAjena = DocumentoHistorial::sole();

        $this->actingAs($this->estudiante);

        $this->enviar(2, 2)->assertNotFound();
        $this->get(route('estudiante.documentos.show', $documentoAjeno->idDocumento))->assertNotFound();
        $this->get(route('estudiante.documentos.descargar', $versionAjena->idHistorial))->assertNotFound();

        // La pasantía ajena no se muestra aunque se pida por parámetro.
        $this->get(route('estudiante.documentos', ['pasantia' => 2]))
            ->assertOk()
            ->assertSee('Empresa A')
            ->assertDontSee('Empresa B');

        $this->assertSame(1, Documento::count());
    }

    public function test_revision_actualiza_documento_e_historial_sin_crear_version(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'A corregir', 'Falta la firma del tutor.')
            ->assertSessionHasNoErrors();

        $documento->refresh();
        $version = DocumentoHistorial::sole();

        $this->assertSame('A corregir', $documento->estadoArchivo);
        $this->assertSame('Falta la firma del tutor.', $documento->observacion);
        $this->assertSame('COO0001', $documento->idCoordinador);

        $this->assertSame('A corregir', $version->estadoArchivo);
        $this->assertSame('Falta la firma del tutor.', $version->observacion);
        $this->assertSame('COO0001', $version->idCoordinador);
        $this->assertNotNull($version->fechaRevision);
    }

    public function test_revision_exige_observacion_salvo_al_aprobar(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'Rechazado')->assertSessionHasErrors('observacion');
        $this->revisar($documento->idDocumento, 1, 'Aprobado')->assertSessionHasNoErrors();

        $this->assertSame('Aprobado', $documento->refresh()->estadoArchivo);
    }

    public function test_una_version_ya_revisada_no_se_vuelve_a_revisar(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'Aprobado');
        $this->revisar($documento->idDocumento, 1, 'Rechazado', 'No')->assertSessionHasErrors('version');

        $this->assertSame('Aprobado', $documento->refresh()->estadoArchivo);
    }

    public function test_coordinador_de_otra_carrera_no_puede_revisar(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();
        $version = DocumentoHistorial::sole();

        $this->actingAs($this->coordinadorOtraCarrera);
        $this->revisar($documento->idDocumento, 1, 'Aprobado')->assertNotFound();
        $this->get(route('coordinador.documentos.show', $documento->idDocumento))->assertNotFound();
        $this->get(route('coordinador.documentos.descargar', $version->idHistorial))->assertNotFound();

        $this->actingAs($this->estudiante);
        $this->revisar($documento->idDocumento, 1, 'Aprobado')->assertForbidden();

        $this->assertSame('Pendiente', $documento->refresh()->estadoArchivo);
    }

    public function test_reenvio_crea_nueva_version_y_conserva_la_anterior(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1, 'v1.pdf');
        $documento = Documento::sole();
        $archivoV1 = $documento->archivo;

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'A corregir', 'Falta la firma.');

        $this->actingAs($this->estudiante);
        $this->enviar(1, 1, 'v2.pdf')->assertSessionHasNoErrors();

        $documento->refresh();
        $v1 = DocumentoHistorial::where('version', 1)->sole();
        $v2 = DocumentoHistorial::where('version', 2)->sole();

        $this->assertSame(1, Documento::count());
        $this->assertSame('Pendiente', $documento->estadoArchivo);
        $this->assertNull($documento->observacion);
        $this->assertSame($v2->archivo, $documento->archivo);
        $this->assertNotSame($archivoV1, $v2->archivo);

        // La versión 1 conserva su archivo y el resultado de su revisión.
        $this->assertSame($archivoV1, $v1->archivo);
        $this->assertSame('A corregir', $v1->estadoArchivo);
        $this->assertSame('Falta la firma.', $v1->observacion);
        Storage::disk('local')->assertExists($archivoV1);
        Storage::disk('local')->assertExists($v2->archivo);

        $this->assertSame('Pendiente', $v2->estadoArchivo);
        $this->assertNull($v2->observacion);

        // La revisión de la versión 2 no altera la versión 1.
        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'Aprobado')->assertSessionHasErrors('version');
        $this->revisar($documento->idDocumento, 2, 'Aprobado')->assertSessionHasNoErrors();

        $this->assertSame('Aprobado', $v2->refresh()->estadoArchivo);
        $this->assertSame('A corregir', $v1->refresh()->estadoArchivo);
    }

    public function test_reenvio_tras_rechazo_sigue_la_configuracion(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'Rechazado', 'No corresponde.');

        $this->actingAs($this->estudiante);
        $this->enviar(1, 1)->assertSessionHasErrors('archivo');
        $this->assertSame(1, DocumentoHistorial::count());

        config(['documentos.permitir_reenvio_rechazado' => true]);
        $this->enviar(1, 1)->assertSessionHasNoErrors();
        $this->assertSame(2, DocumentoHistorial::count());
    }

    public function test_un_error_en_la_transaccion_no_deja_registros_ni_archivo_nuevo(): void
    {
        $this->actingAs($this->estudiante);
        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->actingAs($this->coordinador);
        $this->revisar($documento->idDocumento, 1, 'A corregir', 'Corregir.');

        // Simula un fallo al insertar la nueva versión en el historial.
        DocumentoHistorial::creating(function () {
            throw new \RuntimeException('Fallo simulado');
        });

        $this->actingAs($this->estudiante);
        $this->withoutExceptionHandling();

        try {
            $this->enviar(1, 1);
            $this->fail('Se esperaba una excepción.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo simulado', $e->getMessage());
        }

        $documento->refresh();

        // El documento vigente no cambió y solo existe el archivo anterior.
        $this->assertSame('A corregir', $documento->estadoArchivo);
        $this->assertSame('Corregir.', $documento->observacion);
        $this->assertSame(1, DocumentoHistorial::count());
        $this->assertSame([$documento->archivo], Storage::disk('local')->allFiles('documentos'));
    }

    public function test_pantallas_muestran_los_documentos(): void
    {
        $this->actingAs($this->estudiante);
        $this->get(route('estudiante.documentos'))
            ->assertOk()
            ->assertSee('Plan de trabajo')
            ->assertSee('Informe final')
            ->assertDontSee('Formato inactivo')
            ->assertDontSee('Formato de Industrial');

        $this->enviar(1, 1);
        $documento = Documento::sole();

        $this->get(route('estudiante.documentos.show', $documento->idDocumento))
            ->assertOk()
            ->assertSee('Historial de versiones');

        $this->get(route('estudiante.documentos.descargar', DocumentoHistorial::sole()->idHistorial))
            ->assertOk()
            ->assertDownload('plan_de_trabajo_v1.pdf');

        $this->actingAs($this->coordinador);
        $this->get(route('coordinador.documentos'))
            ->assertOk()
            ->assertSee('Plan de trabajo')
            ->assertSee('Revisar');

        $this->get(route('coordinador.documentos.show', $documento->idDocumento))
            ->assertOk()
            ->assertSee('Guardar revisión')
            ->assertSee(route('coordinador.pasantias.documentos', $documento->idPasantia))
            ->assertDontSee('url()->previous()');
    }
}
