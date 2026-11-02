<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\DocumentoHistorial;
use App\Models\Pasantia;
use App\Models\Usuario;
use App\Services\EstadoPasantiaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cambio automático del estado de la pasantía:
 * Culminada, Culminada con incumplimiento y Pendiente de revisión.
 */
class EstadoPasantiaTest extends TestCase
{
    private EstadoPasantiaService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
        Carbon::setTestNow('2026-11-02 10:00:00');

        $this->crearEsquema();
        $this->crearDatos();

        $this->servicio = app(EstadoPasantiaService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
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
    
        Schema::create('registro_de_horas', function (Blueprint $tabla) {
            $tabla->increments('idRegistro');
            $tabla->date('fechaRegistro');
            $tabla->unsignedInteger('horasIngresadas');
            $tabla->string('observaciones', 200)->nullable();
            $tabla->string('estadoHoras', 30)->default('Pendiente');
            $tabla->unsignedInteger('idPasantia');
            $tabla->string('idTutor', 30)->nullable();
        });
    }


    private function crearDatos(): void
    {
        DB::table('carrera')->insert(['idCarrera' => 1, 'nombre' => 'Ing. en Sistemas']);
        DB::table('plan_de_estudios')->insert(['idPlanEstudios' => 'Plan001', 'horasPorPractica' => 100, 'idCarrera' => 1]);
        DB::table('estudiante')->insert(['credencial' => '21011890', 'anioAcademico' => 2026, 'idPlanEstudios' => 'Plan001']);
        DB::table('coordinacion_academica')->insert(['idCoordinador' => 'COO0001', 'credencial' => 'COO0001', 'idCarrera' => 1]);

        Usuario::create([
            'credencial' => 'COO0001',
            'nombre' => 'Coordinador',
            'correo' => 'coo@prueba.com',
            'contrasena' => 'secreto123',
            'rol' => 'Coordinador',
        ]);

        DB::table('formatos')->insert([
            ['idFormato' => 1, 'nombreFormato' => 'Plan de trabajo', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 2, 'nombreFormato' => 'Informe final', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 3, 'nombreFormato' => 'Formato inactivo', 'idCoordinador' => 'COO0001', 'estado' => 'Inactivo'],
        ]);

        DB::table('practicaprofesional')->insert(['idPractica' => 1, 'horasRequeridas' => 100, 'cif' => '21011890']);

        DB::table('pasantia')->insert([
            'idPasantia' => 1,
            'idPractica' => 1,
            'estado' => 'En progreso',
            'horasRequeridas' => 100,
            'fechaInicio' => '2026-09-01',
            'fechaFinEstimada' => '2026-11-30',
            'empresa' => 'Empresa A',
            'cargo' => 'Desarrollador',
        ]);
    }

    private function horas(int $horas, string $estado = 'Aprobada'): void
    {
        DB::table('registro_de_horas')->insert([
            'fechaRegistro' => '2026-10-01',
            'horasIngresadas' => $horas,
            'estadoHoras' => $estado,
            'idPasantia' => 1,
        ]);
    }

    private function documento(int $idFormato, string $estado): Documento
    {
        $documento = Documento::create([
            'idPasantia' => 1,
            'idFormato' => $idFormato,
            'archivo' => "documentos/{$idFormato}.pdf",
            'estadoArchivo' => $estado,
        ]);

        DocumentoHistorial::create([
            'idDocumento' => $documento->idDocumento,
            'archivo' => "documentos/{$idFormato}.pdf",
            'version' => 1,
            'estadoArchivo' => $estado,
            'fechaEnvio' => now(),
        ]);

        return $documento;
    }

    private function estado(): string
    {
        return Pasantia::findOrFail(1)->estado;
    }

    public function test_sigue_en_progreso_si_falta_algo_antes_de_la_fecha_limite(): void
    {
        $this->horas(60);
        $this->documento(1, 'Aprobado');

        $this->assertSame('En progreso', $this->servicio->actualizar(1));
        $this->assertSame(60, Pasantia::findOrFail(1)->horasAcumuladas);
    }

    public function test_culminada_con_horas_y_todos_los_documentos_aprobados(): void
    {
        $this->horas(80);
        $this->horas(20);
        $this->horas(50, 'Pendiente');
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'Aprobado');

        $this->assertSame('Culminada', $this->servicio->actualizar(1));
        $this->assertSame('Culminada', $this->estado());
    }

    public function test_pendiente_de_revision_si_entrego_todo_pero_falta_revisar(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'Pendiente');

        $this->assertSame('Pendiente de revisión', $this->servicio->actualizar(1));
    }

    public function test_no_es_pendiente_de_revision_si_falta_entregar_un_formato(): void
    {
        $this->horas(100);
        $this->documento(1, 'Pendiente');

        $this->assertSame('En progreso', $this->servicio->actualizar(1));
    }

    public function test_documento_a_corregir_no_cuenta_como_entregado(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'A corregir');

        $this->assertSame('En progreso', $this->servicio->actualizar(1));
    }

    public function test_incumplimiento_al_pasar_la_fecha_limite_sin_horas(): void
    {
        $this->horas(90);
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'Aprobado');

        // El último día todavía está dentro del período.
        $this->assertSame('En progreso', $this->servicio->actualizar(1, Carbon::parse('2026-11-30')));

        $this->assertSame(
            'Culminada con incumplimiento',
            $this->servicio->actualizar(1, Carbon::parse('2026-12-01'))
        );
    }

    public function test_incumplimiento_al_pasar_la_fecha_limite_sin_documentos(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');

        $this->assertSame(
            'Culminada con incumplimiento',
            $this->servicio->actualizar(1, Carbon::parse('2026-12-01'))
        );
    }

    public function test_entregas_completas_despues_de_la_fecha_quedan_pendientes_de_revision(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'Pendiente');

        $this->assertSame(
            'Pendiente de revisión',
            $this->servicio->actualizar(1, Carbon::parse('2026-12-05'))
        );
    }

    public function test_no_cambia_pasantias_con_otros_estados(): void
    {
        DB::table('pasantia')->where('idPasantia', 1)->update(['estado' => 'Cancelada']);
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $this->documento(2, 'Aprobado');

        $this->assertSame('Cancelada', $this->servicio->actualizar(1));
        $this->assertSame('Cancelada', $this->estado());
    }

    public function test_la_revision_del_coordinador_culmina_la_pasantia(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $documento = $this->documento(2, 'Pendiente');

        $this->servicio->actualizar(1);
        $this->assertSame('Pendiente de revisión', $this->estado());

        $this->actingAs(Usuario::findOrFail('COO0001'))
            ->post(route('coordinador.documentos.revisar', $documento->idDocumento), [
                'version' => 1,
                'estadoArchivo' => 'Aprobado',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Culminada', $this->estado());
    }

    public function test_si_el_coordinador_pide_correccion_regresa_a_en_progreso(): void
    {
        $this->horas(100);
        $this->documento(1, 'Aprobado');
        $documento = $this->documento(2, 'Pendiente');

        $this->servicio->actualizar(1);

        $this->actingAs(Usuario::findOrFail('COO0001'))
            ->post(route('coordinador.documentos.revisar', $documento->idDocumento), [
                'version' => 1,
                'estadoArchivo' => 'A corregir',
                'observacion' => 'Falta la firma del tutor.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('En progreso', $this->estado());
    }

    public function test_comando_marca_incumplimiento_por_fecha(): void
    {
        DB::table('pasantia')->where('idPasantia', 1)->update(['fechaFinEstimada' => '2026-10-31']);
        $this->horas(40);

        $this->artisan('pasantias:actualizar-estados')
            ->expectsOutput('Pasantías que cambiaron de estado: 1.')
            ->assertExitCode(0);

        $this->assertSame('Culminada con incumplimiento', $this->estado());
    }
}
