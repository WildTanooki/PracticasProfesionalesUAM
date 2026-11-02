<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Dashboard del estudiante: prácticas, pasantía más reciente, alertas
 * y estado documental de esa pasantía.
 *
 * Las tablas del motor de riesgo vienen de las migraciones; las del
 * proyecto se crean aquí en SQLite.
 */
class DashboardEstudianteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $estudiante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Carbon::setTestNow('2026-11-02 10:00:00');

        $this->crearEsquema();
        $this->crearDatos();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Las migraciones del motor usan la intercalación de MySQL
     * utf8mb4_0900_ai_ci. Se registra en SQLite antes de migrar.
     */
    protected function beforeRefreshingDatabase(): void
    {
        $pdo = DB::connection()->getPdo();

        $crear = method_exists($pdo, 'createCollation')
            ? 'createCollation'
            : 'sqliteCreateCollation';

        $pdo->{$crear}('utf8mb4_0900_ai_ci', 'strcmp');
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

        Schema::create('plan_de_estudios', function (Blueprint $tabla) {
            $tabla->string('idPlanEstudios', 30)->primary();
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

        Schema::create('tutor', function (Blueprint $tabla) {
            $tabla->string('idTutor', 30)->primary();
            $tabla->string('credencial', 30);
            $tabla->string('estadoTutor', 20)->nullable();
        });

        Schema::create('formatos', function (Blueprint $tabla) {
            $tabla->increments('idFormato');
            $tabla->string('nombreFormato', 50);
            $tabla->string('idCoordinador', 30);
            $tabla->string('estado')->default('Activo');
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
            $tabla->unsignedInteger('idCartaAprobacion')->nullable();
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
        });

        Schema::create('documento_historial', function (Blueprint $tabla) {
            $tabla->increments('idHistorial');
            $tabla->unsignedInteger('idDocumento');
            $tabla->string('archivo');
            $tabla->unsignedInteger('version')->default(1);
            $tabla->string('estadoArchivo')->default('Pendiente');
            $tabla->dateTime('fechaEnvio')->nullable();
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

    /**
     * Un estudiante con dos prácticas: una pasantía antigua en Empresa
     * Antigua y la más reciente en CLN, con 3 formatos requeridos.
     * Otro estudiante con su propia pasantía que no debe aparecer.
     */
    private function crearDatos(): void
    {
        DB::table('plan_de_estudios')->insert([
            'idPlanEstudios' => 'Plan001',
            'cantidadPracticasRequeridas' => 2,
            'horasPorPractica' => 350,
            'idCarrera' => 1,
        ]);

        DB::table('coordinacion_academica')->insert([
            'idCoordinador' => 'COO0001', 'credencial' => 'COO0001', 'idCarrera' => 1,
        ]);

        DB::table('estudiante')->insert([
            ['credencial' => '21011890', 'anioAcademico' => 2026, 'idPlanEstudios' => 'Plan001'],
            ['credencial' => '21099999', 'anioAcademico' => 2026, 'idPlanEstudios' => 'Plan001'],
        ]);

        $this->estudiante = Usuario::create([
            'credencial' => '21011890',
            'nombre' => 'Celeste',
            'apellido' => 'Medina',
            'correo' => 'celeste@prueba.com',
            'contrasena' => 'secreto123',
            'rol' => 'Estudiante',
        ]);

        DB::table('formatos')->insert([
            ['idFormato' => 1, 'nombreFormato' => 'Matriz de actividades', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 2, 'nombreFormato' => 'Reporte de horas', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 3, 'nombreFormato' => 'Informe final', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
        ]);

        DB::table('practicaprofesional')->insert([
            ['idPractica' => 1, 'nombrePractica' => 'Práctica Profesional 1', 'horasRequeridas' => 350, 'estadoPractica' => 'Aprobada', 'cif' => '21011890'],
            ['idPractica' => 2, 'nombrePractica' => 'Práctica Profesional 2', 'horasRequeridas' => 350, 'estadoPractica' => 'Pendiente', 'cif' => '21011890'],
            ['idPractica' => 3, 'nombrePractica' => 'Práctica Profesional 1', 'horasRequeridas' => 350, 'estadoPractica' => 'Pendiente', 'cif' => '21099999'],
        ]);

        DB::table('pasantia')->insert([
            [
                'idPasantia' => 1, 'idPractica' => 1, 'estado' => 'Culminada',
                'horasRequeridas' => 350, 'fechaInicio' => '2026-01-10', 'fechaFinEstimada' => '2026-05-10',
                'empresa' => 'Empresa Antigua', 'cargo' => 'Soporte',
            ],
            [
                'idPasantia' => 2, 'idPractica' => 2, 'estado' => 'En progreso',
                'horasRequeridas' => 350, 'fechaInicio' => '2026-08-01', 'fechaFinEstimada' => '2026-12-15',
                'empresa' => 'CLN', 'cargo' => 'Desarrolladora',
            ],
            [
                'idPasantia' => 3, 'idPractica' => 3, 'estado' => 'En progreso',
                'horasRequeridas' => 350, 'fechaInicio' => '2026-08-01', 'fechaFinEstimada' => '2026-12-15',
                'empresa' => 'Empresa Ajena', 'cargo' => 'Analista',
            ],
        ]);

        DB::table('registro_de_horas')->insert([
            ['fechaRegistro' => '2026-09-01', 'horasIngresadas' => 200, 'estadoHoras' => 'Aprobada', 'idPasantia' => 2],
            ['fechaRegistro' => '2026-09-02', 'horasIngresadas' => 50, 'estadoHoras' => 'Aprobada', 'idPasantia' => 2],
            ['fechaRegistro' => '2026-09-03', 'horasIngresadas' => 8, 'estadoHoras' => 'Pendiente', 'idPasantia' => 2],
            ['fechaRegistro' => '2026-03-01', 'horasIngresadas' => 350, 'estadoHoras' => 'Aprobada', 'idPasantia' => 1],
        ]);

        DB::table('documentos')->insert([
            ['idDocumento' => 1, 'idFormato' => 1, 'estadoArchivo' => 'Aprobado', 'observacion' => null, 'idPasantia' => 2],
            ['idDocumento' => 2, 'idFormato' => 2, 'estadoArchivo' => 'A corregir', 'observacion' => 'Falta la firma del tutor.', 'idPasantia' => 2],
            ['idDocumento' => 3, 'idFormato' => 1, 'estadoArchivo' => 'Aprobado', 'observacion' => null, 'idPasantia' => 1],
            ['idDocumento' => 4, 'idFormato' => 3, 'estadoArchivo' => 'Rechazado', 'observacion' => null, 'idPasantia' => 3],
        ]);

        DB::table('documento_historial')->insert([
            ['idDocumento' => 1, 'archivo' => 'a.pdf', 'version' => 1, 'estadoArchivo' => 'Aprobado', 'fechaEnvio' => '2026-08-07 09:00:00'],
            ['idDocumento' => 2, 'archivo' => 'b.pdf', 'version' => 1, 'estadoArchivo' => 'A corregir', 'fechaEnvio' => '2026-08-20 09:00:00'],
            ['idDocumento' => 3, 'archivo' => 'c.pdf', 'version' => 1, 'estadoArchivo' => 'Aprobado', 'fechaEnvio' => '2026-02-01 09:00:00'],
            ['idDocumento' => 4, 'archivo' => 'd.pdf', 'version' => 1, 'estadoArchivo' => 'Rechazado', 'fechaEnvio' => '2026-08-20 09:00:00'],
        ]);
    }

    public function test_muestra_la_pasantia_mas_reciente_con_su_avance(): void
    {
        $respuesta = $this->actingAs($this->estudiante)->get(route('estudiante.home'));

        $respuesta->assertOk();
        $respuesta->assertSee('Hola, Celeste Medina');
        $respuesta->assertSee('Tu avance de prácticas en CLN');
        $respuesta->assertSee('250 de 350 horas completadas');
        $respuesta->assertSee('Estado: En progreso.');
        $respuesta->assertSee('71%');
        $respuesta->assertSee('100 h');
        $respuesta->assertDontSee('Empresa Antigua');

        $respuesta->assertViewHas('pasantia', fn ($pasantia) => $pasantia->idPasantia === 2);
        $respuesta->assertViewHas('avance', fn ($avance) => $avance['pendientes'] === 8);
    }

    public function test_muestra_las_practicas_del_estudiante(): void
    {
        $respuesta = $this->actingAs($this->estudiante)->get(route('estudiante.home'));

        $respuesta->assertViewHas('practicas', fn ($practicas) => $practicas->count() === 2);
        // El carrusel abre en la práctica de la pasantía más reciente.
        $respuesta->assertViewHas('indicePracticaActual', 1);
        $respuesta->assertSee('Práctica 2 de 2');
    }

    public function test_muestra_el_estado_documental_de_esa_pasantia(): void
    {
        $respuesta = $this->actingAs($this->estudiante)->get(route('estudiante.home'));

        $respuesta->assertSeeInOrder(['Reporte de horas', '20/08/2026', 'A corregir']);
        $respuesta->assertSeeInOrder(['Matriz de actividades', '07/08/2026', 'Aprobado']);
        $respuesta->assertSee('1 de 3 documentos aprobados');

        $respuesta->assertViewHas('documentos', fn ($documentos) => $documentos->pluck('idDocumento')->sort()->values()->all() === [1, 2]);
        $respuesta->assertViewHas('resumenDocumental', fn ($resumen) => $resumen['aprobados'] === 1
            && $resumen['aCorregir'] === 1
            && $resumen['faltantes']->all() === ['Informe final']);
    }

    public function test_el_resumen_coincide_con_la_tabla_aunque_un_formato_este_inactivo(): void
    {
        // Documento aprobado de un formato que luego se desactivó.
        DB::table('formatos')->insert([
            'idFormato' => 4, 'nombreFormato' => 'Carta de recursos humanos', 'idCoordinador' => 'COO0001', 'estado' => 'Inactivo',
        ]);
        DB::table('documentos')->insert([
            'idDocumento' => 5, 'idFormato' => 4, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 2,
        ]);
        DB::table('documento_historial')->insert([
            'idDocumento' => 5, 'archivo' => 'e.pdf', 'version' => 1, 'estadoArchivo' => 'Aprobado', 'fechaEnvio' => '2026-08-01 09:00:00',
        ]);

        $respuesta = $this->actingAs($this->estudiante)->get(route('estudiante.home'));

        // 3 enviados + 1 sin entregar; 2 aprobados.
        $respuesta->assertSee('2 de 4 documentos aprobados');
        $respuesta->assertSee('Aprobados: 2');
        $respuesta->assertSee('Sin entregar: 1');
        $respuesta->assertSeeInOrder(['Carta de recursos humanos', 'Informe final', 'Sin entregar', 'Subir']);
    }

    public function test_muestra_solo_las_alertas_del_estudiante(): void
    {
        $respuesta = $this->actingAs($this->estudiante)->get(route('estudiante.home'));

        $respuesta->assertSee('Subir documentación pendiente');
        $respuesta->assertSee('Falta: Informe final.');
        $respuesta->assertSee('Pendiente de atención');
        $respuesta->assertSee('Corregir Reporte de horas');
        $respuesta->assertSee('Falta la firma del tutor.');
        $respuesta->assertSee('8 h esperan aprobación');

        // El documento rechazado es de otro estudiante.
        $respuesta->assertDontSee('Documento rechazado');
    }

    public function test_avisa_cuando_se_acerca_la_fecha_final(): void
    {
        DB::table('pasantia')->where('idPasantia', 2)->update(['fechaFinEstimada' => '2026-11-12']);

        $this->actingAs($this->estudiante)
            ->get(route('estudiante.home'))
            ->assertSee('Quedan 10 días para la fecha final');
    }

    public function test_sin_pasantia_invita_a_iniciar_el_proceso(): void
    {
        DB::table('pasantia')->whereIn('idPasantia', [1, 2])->delete();

        $this->actingAs($this->estudiante)
            ->get(route('estudiante.home'))
            ->assertOk()
            ->assertSee('Aún no tienes una pasantía')
            ->assertSee('No tienes alertas')
            ->assertDontSee('Tu avance de prácticas');
    }
}
