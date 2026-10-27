<?php

namespace Tests\Feature;

use App\Models\CondicionRegla;
use App\Models\DetalleEvaluacionRegla;
use App\Models\EvaluacionRiesgo;
use App\Models\ReglaRiesgo;
use App\Models\UmbralRiesgo;
use App\Models\VersionConfiguracionRiesgo;
use App\Services\RulesEngine\RiskEvaluator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * Evaluación de riesgo de una pasantía real y su guardado.
 *
 * Las tablas del motor vienen de las migraciones. Las del proyecto
 * vienen de una base de datos externa, así que se crea aquí una copia
 * mínima de su esquema en SQLite.
 */
class EvaluacionRiesgoTest extends TestCase
{
    use RefreshDatabase;

    private RiskEvaluator $evaluador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearEsquema();
        $this->crearDatos();

        $this->evaluador = app(RiskEvaluator::class);
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
        Schema::create('coordinacion_academica', function (Blueprint $tabla) {
            $tabla->string('idCoordinador', 30)->primary();
            $tabla->string('credencial', 30)->unique();
            $tabla->unsignedInteger('idCarrera');
        });

        Schema::create('plan_de_estudios', function (Blueprint $tabla) {
            $tabla->string('idPlanEstudios', 30)->primary();
            $tabla->unsignedInteger('idCarrera');
        });

        Schema::create('estudiante', function (Blueprint $tabla) {
            $tabla->increments('estudianteID');
            $tabla->string('credencial', 30)->unique();
            $tabla->string('idPlanEstudios', 30);
        });

        Schema::create('formatos', function (Blueprint $tabla) {
            $tabla->increments('idFormato');
            $tabla->string('nombreFormato', 50);
            $tabla->string('idCoordinador', 30);
            $tabla->string('estado')->default('Activo');
        });

        Schema::create('practicaprofesional', function (Blueprint $tabla) {
            $tabla->increments('idPractica');
            $tabla->unsignedInteger('horasRequeridas');
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
            $tabla->string('empresa', 50);
            $tabla->string('cargo', 50);
        });

        Schema::create('documentos', function (Blueprint $tabla) {
            $tabla->increments('idDocumento');
            $tabla->unsignedInteger('idFormato')->nullable();
            $tabla->string('archivo')->nullable();
            $tabla->string('estadoArchivo', 30)->default('Pendiente');
            $tabla->unsignedInteger('idPasantia');
        });

        Schema::create('documento_historial', function (Blueprint $tabla) {
            $tabla->increments('idHistorial');
            $tabla->unsignedInteger('idDocumento');
            $tabla->string('archivo');
            $tabla->unsignedInteger('version')->default(1);
            $tabla->string('estadoArchivo')->default('Pendiente');
        });
    }

    /**
     * Una pasantía en progreso con 4 formatos obligatorios:
     * 1 aprobado, 1 a corregir, 1 pendiente y 1 sin entregar (25 %).
     */
    private function crearDatos(): void
    {
        DB::table('coordinacion_academica')->insert([
            ['idCoordinador' => 'COO0001', 'credencial' => 'COO0001', 'idCarrera' => 1],
            ['idCoordinador' => 'COO0002', 'credencial' => 'COO0002', 'idCarrera' => 2],
        ]);

        DB::table('plan_de_estudios')->insert([
            ['idPlanEstudios' => 'Plan001', 'idCarrera' => 1],
        ]);

        DB::table('estudiante')->insert([
            ['credencial' => '21011890', 'idPlanEstudios' => 'Plan001'],
        ]);

        DB::table('formatos')->insert([
            ['idFormato' => 1, 'nombreFormato' => 'Plan de trabajo', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 2, 'nombreFormato' => 'Informe de avance', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 3, 'nombreFormato' => 'Evaluación del tutor', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            ['idFormato' => 4, 'nombreFormato' => 'Informe final', 'idCoordinador' => 'COO0001', 'estado' => 'Activo'],
            // No cuentan: inactivo o de otra carrera.
            ['idFormato' => 5, 'nombreFormato' => 'Formato inactivo', 'idCoordinador' => 'COO0001', 'estado' => 'Inactivo'],
            ['idFormato' => 6, 'nombreFormato' => 'Formato de Industrial', 'idCoordinador' => 'COO0002', 'estado' => 'Activo'],
        ]);

        DB::table('practicaprofesional')->insert([
            ['idPractica' => 1, 'horasRequeridas' => 400, 'cif' => '21011890'],
        ]);

        DB::table('pasantia')->insert([
            [
                'idPasantia' => 1,
                'idPractica' => 1,
                'estado' => 'En progreso',
                'horasRequeridas' => 400,
                'fechaInicio' => today()->subMonth()->toDateString(),
                'fechaFinEstimada' => today()->addDays(20)->toDateString(),
                'empresa' => 'Empresa A',
                'cargo' => 'Desarrollador',
            ],
            [
                'idPasantia' => 2,
                'idPractica' => 1,
                'estado' => 'Finalizada',
                'horasRequeridas' => 400,
                'fechaInicio' => null,
                'fechaFinEstimada' => today()->toDateString(),
                'empresa' => 'Empresa A',
                'cargo' => 'Desarrollador',
            ],
        ]);

        DB::table('documentos')->insert([
            ['idDocumento' => 1, 'idFormato' => 1, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 1],
            ['idDocumento' => 2, 'idFormato' => 2, 'estadoArchivo' => 'A corregir', 'idPasantia' => 1],
            ['idDocumento' => 3, 'idFormato' => 3, 'estadoArchivo' => 'Pendiente', 'idPasantia' => 1],
        ]);
    }

    /**
     * Reglas y umbrales válidos:
     *  - Documentación crítica: porcentaje < 50 y quedan <= 30 días → 40 puntos.
     *  - Documentos por corregir: a corregir > 0 → 10 puntos por documento.
     *  - Todo rechazado: rechazados > 0 → 30 puntos (no se cumple).
     *  - Umbrales: Bajo 0-19.99, Medio 20-49.99, Alto 50 en adelante.
     */
    private function crearConfiguracion(): void
    {
        $this->regla('Documentación crítica', 40, [
            ['porcentaje_documental_completado', '<', '50', 'decimal', 'AND'],
            ['dias_restantes', '<=', '30', 'entero', 'AND'],
        ]);

        $this->regla('Documentos por corregir', 10, [
            ['documentos_a_corregir', '>', '0', 'entero', 'AND'],
        ], variablePuntuacion: 'documentos_a_corregir');

        $this->regla('Documentos rechazados', 30, [
            ['documentos_rechazados', '>', '0', 'entero', 'AND'],
        ]);

        $this->umbral('Bajo', 0, 19.99);
        $this->umbral('Medio', 20, 49.99);
        $this->umbral('Alto', 50, null);
    }

    private function regla(
        string $nombre,
        float $puntuacion,
        array $condiciones,
        ?string $variablePuntuacion = null
    ): ReglaRiesgo {
        $regla = ReglaRiesgo::create([
            'nombre' => $nombre,
            'puntuacion' => $puntuacion,
            'activa' => true,
            'prioridad' => 1,
            'variablePuntuacion' => $variablePuntuacion,
        ]);

        foreach ($condiciones as $orden => [$variable, $operador, $parametro, $tipo, $conector]) {
            CondicionRegla::create([
                'idReglaRiesgo' => $regla->idReglaRiesgo,
                'variable' => $variable,
                'operador' => $operador,
                'parametro' => $parametro,
                'tipoParametro' => $tipo,
                'conector' => $conector,
                'orden' => $orden + 1,
            ]);
        }

        return $regla;
    }

    private function umbral(string $nivel, float $inferior, ?float $superior): UmbralRiesgo
    {
        return UmbralRiesgo::create([
            'nivel' => $nivel,
            'limiteInferior' => $inferior,
            'limiteSuperior' => $superior,
            'activo' => true,
        ]);
    }

    public function test_evalua_una_pasantia_real_y_guarda_la_evaluacion(): void
    {
        $this->crearConfiguracion();

        $evaluacion = $this->evaluador->evaluar(1);

        // 40 (documentación crítica) + 1 documento a corregir × 10.
        $this->assertSame(RiskEvaluator::ESTADO_CORRECTA, $evaluacion->estadoEvaluacion);
        $this->assertSame('50.00', $evaluacion->refresh()->puntuacionTotal);
        $this->assertSame('Alto', $evaluacion->nivelRiesgo);
        $this->assertNull($evaluacion->errorConfiguracion);

        // Contexto calculado con los datos reales de la pasantía.
        $this->assertSame(4, $evaluacion->contextoEvaluado['documentos_obligatorios']);
        $this->assertSame(1, $evaluacion->contextoEvaluado['documentos_no_entregados']);
        $this->assertSame(1, $evaluacion->contextoEvaluado['documentos_aprobados']);
        $this->assertSame(1, $evaluacion->contextoEvaluado['documentos_a_corregir']);
        $this->assertEquals(25, $evaluacion->contextoEvaluado['porcentaje_documental_completado']);
        $this->assertSame(20, $evaluacion->contextoEvaluado['dias_restantes']);
    }

    public function test_registra_la_fecha_y_la_version_de_configuracion(): void
    {
        $this->crearConfiguracion();
        $this->travelTo(now()->setTime(10, 30));

        $evaluacion = $this->evaluador->evaluar(1)->refresh();

        $this->assertTrue($evaluacion->fechaEvaluacion->equalTo(now()));

        $version = $evaluacion->version;
        $this->assertNotNull($version);
        $this->assertSame(['reglas', 'umbrales'], array_keys($version->configuracion));
        $this->assertCount(3, $version->configuracion['reglas']);
        $this->assertSame('Alto', $version->configuracion['umbrales'][2]['nivel']);
        $this->assertNull($version->configuracion['umbrales'][2]['limiteSuperior']);
    }

    public function test_guarda_el_detalle_de_cada_regla_y_sus_condiciones(): void
    {
        $this->crearConfiguracion();

        $evaluacion = $this->evaluador->evaluar(1);
        $detalles = $evaluacion->detalles()->with('regla')->orderBy('idReglaRiesgo')->get();

        $this->assertCount(3, $detalles);

        [$critica, $corregir, $rechazados] = $detalles->all();

        $this->assertTrue($critica->cumplida);
        $this->assertSame('40.00', $critica->puntuacionAplicada);
        $this->assertSame('Se cumplió la regla y aportó 40.00 puntos.', $critica->resultado);
        $this->assertSame('AND', $critica->detalleCondiciones['conector']);
        // JSON guarda 50.0 como 50, por eso se compara con assertEquals.
        $this->assertEquals(
            [
                ['porcentaje_documental_completado', '<', 50, true],
                ['dias_restantes', '<=', 30, true],
            ],
            array_map(
                fn ($c) => [$c['variable'], $c['operador'], $c['parametro'], $c['cumplida']],
                $critica->detalleCondiciones['condiciones']
            )
        );
        $this->assertEquals(25, $critica->detalleCondiciones['condiciones'][0]['valorReal']);

        $this->assertTrue($corregir->cumplida);
        $this->assertSame('10.00', $corregir->puntuacionAplicada);

        // Las reglas que no se cumplen también quedan registradas.
        $this->assertFalse($rechazados->cumplida);
        $this->assertSame('0.00', $rechazados->puntuacionAplicada);
        $this->assertSame('No se cumplió la regla.', $rechazados->resultado);
        $this->assertSame(0, $rechazados->detalleCondiciones['condiciones'][0]['valorReal']);
    }

    public function test_conserva_el_historial_de_evaluaciones(): void
    {
        $this->crearConfiguracion();

        $primera = $this->evaluador->evaluar(1);

        // El estudiante corrige y le aprueban todos los documentos.
        DB::table('documentos')->where('idPasantia', 1)->update(['estadoArchivo' => 'Aprobado']);
        DB::table('documentos')->insert(['idFormato' => 4, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 1]);

        $segunda = $this->evaluador->evaluar(1);

        $this->assertNotSame($primera->idEvaluacionRiesgo, $segunda->idEvaluacionRiesgo);
        $this->assertSame(2, EvaluacionRiesgo::where('idPasantia', 1)->count());
        $this->assertSame(6, DetalleEvaluacionRegla::count());

        // La primera evaluación no cambia.
        $primera->refresh();
        $this->assertSame('Alto', $primera->nivelRiesgo);
        $this->assertSame('50.00', $primera->puntuacionTotal);
        $this->assertEquals(25, $primera->contextoEvaluado['porcentaje_documental_completado']);

        $segunda->refresh();
        $this->assertSame('Bajo', $segunda->nivelRiesgo);
        $this->assertSame('0.00', $segunda->puntuacionTotal);
        $this->assertEquals(100, $segunda->contextoEvaluado['porcentaje_documental_completado']);
    }

    public function test_la_version_solo_cambia_cuando_cambia_la_configuracion(): void
    {
        $this->crearConfiguracion();

        $primera = $this->evaluador->evaluar(1);
        $mismaConfiguracion = $this->evaluador->evaluar(1);

        $this->assertSame($primera->versionConfiguracion, $mismaConfiguracion->versionConfiguracion);
        $this->assertSame(1, VersionConfiguracionRiesgo::count());

        // El coordinador cambia el parámetro de una condición.
        CondicionRegla::where('variable', 'porcentaje_documental_completado')
            ->update(['parametro' => '20']);

        $nueva = $this->evaluador->evaluar(1);

        $this->assertNotSame($primera->versionConfiguracion, $nueva->versionConfiguracion);
        $this->assertSame(2, VersionConfiguracionRiesgo::count());

        // La evaluación anterior conserva la versión con la que se calculó.
        $this->assertSame(
            '50',
            $primera->refresh()->version->configuracion['reglas'][0]['condiciones'][0]['parametro']
        );
        $this->assertSame(
            '20',
            $nueva->refresh()->version->configuracion['reglas'][0]['condiciones'][0]['parametro']
        );
    }

    /**
     * @return array<string, array{0: array<int, array{0: string, 1: float, 2: float|null}>, 1: string}>
     */
    public static function umbralesInvalidos(): array
    {
        return [
            'hueco entre niveles' => [
                [['Bajo', 0, 19.99], ['Medio', 30, 49.99], ['Alto', 50, null]],
                'Los umbrales tienen huecos o se superponen.',
            ],
            'niveles superpuestos' => [
                [['Bajo', 0, 29.99], ['Medio', 20, 49.99], ['Alto', 50, null]],
                'Los umbrales tienen huecos o se superponen.',
            ],
            'no empieza en cero' => [
                [['Bajo', 5, 19.99], ['Medio', 20, 49.99], ['Alto', 50, null]],
                'Los umbrales deben cubrir los puntajes desde cero.',
            ],
            'último nivel con límite superior' => [
                [['Bajo', 0, 19.99], ['Medio', 20, 49.99], ['Alto', 50, 100]],
                'El último umbral debe tener límite superior NULL.',
            ],
            'límites invertidos' => [
                [['Bajo', 0, 19.99], ['Medio', 20, 10], ['Alto', 50, null]],
                "El umbral 'Medio' tiene límites inválidos.",
            ],
            'sin umbrales activos' => [
                [],
                'No existen umbrales de riesgo activos.',
            ],
        ];
    }

    #[DataProvider('umbralesInvalidos')]
    public function test_umbrales_invalidos_impiden_guardar_una_evaluacion_incorrecta(
        array $umbrales,
        string $mensaje
    ): void {
        $this->regla('Documentación crítica', 40, [
            ['porcentaje_documental_completado', '<', '50', 'decimal', 'AND'],
        ]);

        foreach ($umbrales as [$nivel, $inferior, $superior]) {
            $this->umbral($nivel, $inferior, $superior);
        }

        $evaluacion = $this->evaluador->evaluar(1)->refresh();

        // Queda registro del intento, pero sin nivel ni puntuación.
        $this->assertSame(RiskEvaluator::ESTADO_ERROR_CONFIGURACION, $evaluacion->estadoEvaluacion);
        $this->assertSame($mensaje, $evaluacion->errorConfiguracion);
        $this->assertNull($evaluacion->nivelRiesgo);
        $this->assertNull($evaluacion->puntuacionTotal);
        $this->assertSame(0, DetalleEvaluacionRegla::count());
        $this->assertSame(
            0,
            EvaluacionRiesgo::where('estadoEvaluacion', RiskEvaluator::ESTADO_CORRECTA)->count()
        );
    }

    public function test_una_puntuacion_que_no_cabe_en_la_base_no_se_guarda(): void
    {
        $this->crearConfiguracion();
        // 100 puntos por cada día restante: 20 × 100 = 2000 puntos.
        $this->regla('Días restantes', 100, [
            ['dias_restantes', '>', '0', 'entero', 'AND'],
        ], variablePuntuacion: 'dias_restantes');

        $evaluacion = $this->evaluador->evaluar(1);

        $this->assertSame(RiskEvaluator::ESTADO_ERROR_CONFIGURACION, $evaluacion->estadoEvaluacion);
        $this->assertNull($evaluacion->nivelRiesgo);
        $this->assertSame(0, DetalleEvaluacionRegla::count());
    }

    public function test_la_evaluacion_y_sus_detalles_se_guardan_en_una_transaccion(): void
    {
        $this->crearConfiguracion();
        $this->evaluador->evaluar(1);

        $guardados = 0;

        // Simular que falla el guardado del segundo detalle.
        DetalleEvaluacionRegla::creating(function () use (&$guardados) {
            if (++$guardados === 2) {
                throw new RuntimeException('Fallo al guardar el detalle.');
            }
        });

        try {
            $this->evaluador->evaluar(1);
            $this->fail('La evaluación debía fallar.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo al guardar el detalle.', $e->getMessage());
        }

        // Solo queda la evaluación anterior con sus 3 detalles completos.
        $this->assertSame(1, EvaluacionRiesgo::count());
        $this->assertSame(3, DetalleEvaluacionRegla::count());
    }

    public function test_no_evalua_pasantias_que_no_estan_en_progreso(): void
    {
        $this->crearConfiguracion();

        try {
            $this->evaluador->evaluar(2);
            $this->fail('La evaluación debía fallar.');
        } catch (RuntimeException $e) {
            $this->assertSame('Solo se pueden evaluar pasantías en progreso.', $e->getMessage());
        }

        $this->assertSame(0, EvaluacionRiesgo::count());
        $this->assertSame(0, VersionConfiguracionRiesgo::count());
    }

    public function test_el_comando_evalua_las_pasantias_en_progreso(): void
    {
        $this->crearConfiguracion();

        $this->artisan('riesgo:evaluar')
            ->expectsOutputToContain('Pasantía 1: riesgo Alto (50.00 puntos, versión 1).')
            ->assertExitCode(0);

        $this->artisan('riesgo:evaluar', ['idPasantia' => 2])
            ->expectsOutputToContain('Pasantía 2: no se evaluó. Solo se pueden evaluar pasantías en progreso.')
            ->assertExitCode(1);

        $this->assertSame(1, EvaluacionRiesgo::count());
    }
}
