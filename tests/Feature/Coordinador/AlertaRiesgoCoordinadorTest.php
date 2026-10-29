<?php

namespace Tests\Feature\Coordinador;

use App\Models\CondicionRegla;
use App\Models\EvaluacionRiesgo;
use App\Models\ReglaRiesgo;
use App\Models\UmbralRiesgo;
use App\Models\Usuario;
use App\Services\RulesEngine\RiskEvaluator;
use App\Services\RulesEngine\RiskRecommendations;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pantalla de alertas y riesgos del coordinador.
 *
 * Las tablas del proyecto vienen de una base de datos externa, así que
 * se crea aquí una copia mínima de su esquema en SQLite.
 */
class AlertaRiesgoCoordinadorTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $coordinador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->crearEsquema();
        $this->crearDatos();
        $this->crearConfiguracion();

        $this->coordinador = Usuario::find('COORD-1');
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

        Schema::create('carrera', function (Blueprint $tabla) {
            $tabla->increments('idCarrera');
            $tabla->string('nombre', 50);
        });

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

        Schema::create('tutor', function (Blueprint $tabla) {
            $tabla->increments('idTutor');
            $tabla->string('credencial', 30);
            $tabla->string('estadoTutor', 20)->default('Activo');
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
            $tabla->unsignedInteger('idTutor')->nullable();
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
     * Carrera 1 con 4 formatos obligatorios:
     *  - Pasantía 1 (Ana): 1 aprobado, 1 a corregir, 1 pendiente, 1 sin entregar.
     *  - Pasantía 2 (Luis): los 4 aprobados.
     *  - Pasantía 3 (Ana): finalizada, no aparece en la lista.
     *  - Pasantía 4 (Marta): de la carrera 2.
     */
    private function crearDatos(): void
    {
        $usuarios = [
            ['COORD-1', 'Coordinador', 'Coordinación', null],
            ['COORD-2', 'Coordinador', 'Coordinación', 'Industrial'],
            ['EST-1', 'Estudiante', 'Ana', 'López'],
            ['EST-2', 'Estudiante', 'Luis', 'Pérez'],
            ['EST-3', 'Estudiante', 'Marta', 'Ruiz'],
            ['TUT-1', 'Tutor', 'Roberto', 'Morales'],
        ];

        foreach ($usuarios as [$credencial, $rol, $nombre, $apellido]) {
            DB::table('usuarios')->insert([
                'credencial' => $credencial,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'correo' => strtolower($credencial) . '@uam.test',
                'contrasena' => bcrypt('secreto'),
                'rol' => $rol,
            ]);
        }

        DB::table('carrera')->insert([
            ['idCarrera' => 1, 'nombre' => 'Ing. en Sistemas'],
            ['idCarrera' => 2, 'nombre' => 'Ing. Industrial'],
        ]);

        DB::table('coordinacion_academica')->insert([
            ['idCoordinador' => 'C1', 'credencial' => 'COORD-1', 'idCarrera' => 1],
            ['idCoordinador' => 'C2', 'credencial' => 'COORD-2', 'idCarrera' => 2],
        ]);

        DB::table('plan_de_estudios')->insert([
            ['idPlanEstudios' => 'P1', 'idCarrera' => 1],
            ['idPlanEstudios' => 'P2', 'idCarrera' => 2],
        ]);

        DB::table('estudiante')->insert([
            ['credencial' => 'EST-1', 'idPlanEstudios' => 'P1'],
            ['credencial' => 'EST-2', 'idPlanEstudios' => 'P1'],
            ['credencial' => 'EST-3', 'idPlanEstudios' => 'P2'],
        ]);

        DB::table('tutor')->insert(['idTutor' => 1, 'credencial' => 'TUT-1']);

        foreach (range(1, 4) as $id) {
            DB::table('formatos')->insert([
                'idFormato' => $id,
                'nombreFormato' => "Formato {$id}",
                'idCoordinador' => 'C1',
            ]);
        }

        DB::table('practicaprofesional')->insert([
            ['idPractica' => 1, 'horasRequeridas' => 400, 'cif' => 'EST-1'],
            ['idPractica' => 2, 'horasRequeridas' => 400, 'cif' => 'EST-2'],
            ['idPractica' => 3, 'horasRequeridas' => 400, 'cif' => 'EST-3'],
        ]);

        $pasantia = fn (int $id, int $practica, string $estado, string $empresa) => [
            'idPasantia' => $id,
            'idPractica' => $practica,
            'estado' => $estado,
            'horasRequeridas' => 400,
            'fechaInicio' => today()->subMonth()->toDateString(),
            'fechaFinEstimada' => today()->addDays(20)->toDateString(),
            'idTutor' => 1,
            'empresa' => $empresa,
            'cargo' => 'Desarrollador web',
        ];

        DB::table('pasantia')->insert([
            $pasantia(1, 1, 'En progreso', 'Banco Atlántida'),
            $pasantia(2, 2, 'En progreso', 'Softech'),
            $pasantia(3, 1, 'Finalizada', 'Empresa antigua'),
            $pasantia(4, 3, 'En progreso', 'Fábrica Industrial'),
        ]);

        DB::table('documentos')->insert([
            ['idFormato' => 1, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 1],
            ['idFormato' => 2, 'estadoArchivo' => 'A corregir', 'idPasantia' => 1],
            ['idFormato' => 3, 'estadoArchivo' => 'Pendiente', 'idPasantia' => 1],
            ['idFormato' => 1, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 2],
            ['idFormato' => 2, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 2],
            ['idFormato' => 3, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 2],
            ['idFormato' => 4, 'estadoArchivo' => 'Aprobado', 'idPasantia' => 2],
        ]);
    }

    /**
     * R-001 Documentación crítica: porcentaje < 50 y quedan <= 30 días → 40.
     * R-002 Documentos por corregir: a corregir > 0 → 10 por documento.
     * Niveles: Bajo 0-19.99, Medio 20-49.99, Alto 50 en adelante.
     */
    private function crearConfiguracion(): void
    {
        $this->regla('Documentación crítica', 40, [
            ['porcentaje_documental_completado', '<', '50', 'decimal'],
            ['dias_restantes', '<=', '30', 'entero'],
        ]);

        $this->regla('Documentos por corregir', 10, [
            ['documentos_a_corregir', '>', '0', 'entero'],
        ], 'documentos_a_corregir');

        foreach ([['Bajo', 0, 19.99], ['Medio', 20, 49.99], ['Alto', 50, null]] as [$nivel, $inferior, $superior]) {
            UmbralRiesgo::create([
                'nivel' => $nivel,
                'limiteInferior' => $inferior,
                'limiteSuperior' => $superior,
                'activo' => true,
            ]);
        }
    }

    private function regla(string $nombre, float $puntuacion, array $condiciones, ?string $variablePuntuacion = null): void
    {
        $regla = ReglaRiesgo::create([
            'nombre' => $nombre,
            'puntuacion' => $puntuacion,
            'activa' => true,
            'prioridad' => 1,
            'variablePuntuacion' => $variablePuntuacion,
            'idCoordinador' => 'C1',
        ]);

        foreach ($condiciones as $orden => [$variable, $operador, $parametro, $tipo]) {
            CondicionRegla::create([
                'idReglaRiesgo' => $regla->idReglaRiesgo,
                'variable' => $variable,
                'operador' => $operador,
                'parametro' => $parametro,
                'tipoParametro' => $tipo,
                'conector' => 'AND',
                'orden' => $orden + 1,
            ]);
        }
    }

    public function test_un_estudiante_no_puede_entrar(): void
    {
        $estudiante = Usuario::find('EST-1');

        $this->actingAs($estudiante)->get(route('coordinador.alertas'))->assertForbidden();
        $this->actingAs($estudiante)->post(route('coordinador.alertas.evaluar'))->assertForbidden();
        $this->actingAs($estudiante)->get(route('coordinador.alertas.show', 1))->assertForbidden();

        $this->assertSame(0, EvaluacionRiesgo::count());
    }

    public function test_lista_solo_pasantias_en_progreso_de_la_carrera(): void
    {
        $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas'))
            ->assertOk()
            ->assertSee('Ana López')
            ->assertSee('Luis Pérez')
            ->assertSee('Sin evaluar')
            ->assertDontSee('Marta Ruiz')
            ->assertDontSee('Empresa antigua');
    }

    public function test_evaluar_todas_guarda_una_evaluacion_por_pasantia_de_la_carrera(): void
    {
        $this->actingAs($this->coordinador)
            ->post(route('coordinador.alertas.evaluar'))
            ->assertRedirect(route('coordinador.alertas'))
            ->assertSessionHas('avisos', [])
            ->assertSessionHas('success', 'Se evaluaron 2 pasantías.');

        $this->assertEqualsCanonicalizing([1, 2], EvaluacionRiesgo::pluck('idPasantia')->all());
        $this->assertSame('Alto', EvaluacionRiesgo::where('idPasantia', 1)->value('nivelRiesgo'));
        $this->assertSame('Bajo', EvaluacionRiesgo::where('idPasantia', 2)->value('nivelRiesgo'));

        $respuesta = $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas'))
            ->assertOk()
            ->assertSee('R-001')
            ->assertSee('Documentación crítica')
            ->assertSee('Sin incidencias');

        // Una pasantía en Alto y otra en Bajo.
        $this->assertSame(['Alto' => 1, 'Bajo' => 1], $respuesta->viewData('conteos')->sortKeys()->all());
    }

    public function test_filtra_por_nivel_y_busqueda(): void
    {
        $this->actingAs($this->coordinador)->post(route('coordinador.alertas.evaluar'));

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas', ['nivel' => 'Alto']))
            ->assertOk()
            ->assertSee('Ana López')
            ->assertDontSee('Luis Pérez');

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas', ['buscar' => 'softech']))
            ->assertOk()
            ->assertSee('Luis Pérez')
            ->assertDontSee('Ana López');
    }

    public function test_detalle_muestra_reglas_motivo_recomendacion_e_historial(): void
    {
        $this->actingAs($this->coordinador)->post(route('coordinador.alertas.evaluar-pasantia', 1))
            ->assertRedirect(route('coordinador.alertas.show', 1))
            ->assertSessionHas('success');
        $this->actingAs($this->coordinador)->post(route('coordinador.alertas.evaluar-pasantia', 1));

        $this->assertSame(2, EvaluacionRiesgo::where('idPasantia', 1)->count());

        $respuesta = $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas.show', 1))
            ->assertOk()
            ->assertSee('Nivel de riesgo: Alto')
            ->assertSee('50 puntos')
            ->assertSee('Banco Atlántida')
            ->assertSee('Roberto Morales')
            ->assertSee('Documentación crítica')
            ->assertSee('El porcentaje de documentos aprobados es menor que 50%')
            ->assertSee('(valor: 25%)', false)
            ->assertSee('Pedir al estudiante que entregue el documento que le falta.', false)
            ->assertSee('Historial de evaluaciones');

        $this->assertCount(2, $respuesta->viewData('evaluaciones'));
        $this->assertCount(2, $respuesta->viewData('reglasCumplidas'));
    }

    public function test_no_muestra_ni_evalua_pasantias_de_otra_carrera(): void
    {
        $this->actingAs($this->coordinador)->get(route('coordinador.alertas.show', 4))->assertNotFound();
        $this->actingAs($this->coordinador)->post(route('coordinador.alertas.evaluar-pasantia', 4))->assertNotFound();

        $this->assertSame(0, EvaluacionRiesgo::count());
    }

    public function test_umbrales_invalidos_muestran_error_sin_nivel(): void
    {
        UmbralRiesgo::where('nivel', 'Medio')->update(['limiteInferior' => 30]);

        $this->actingAs($this->coordinador)
            ->post(route('coordinador.alertas.evaluar'))
            ->assertRedirect(route('coordinador.alertas'))
            ->assertSessionHas('error');

        $evaluacion = EvaluacionRiesgo::sole();
        $this->assertSame(RiskEvaluator::ESTADO_ERROR_CONFIGURACION, $evaluacion->estadoEvaluacion);
        $this->assertNull($evaluacion->nivelRiesgo);

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.alertas.show', $evaluacion->idPasantia))
            ->assertOk()
            ->assertSee('Error de configuración')
            ->assertDontSee('Nivel de riesgo:');
    }

    public function test_avisa_las_pasantias_que_no_se_pueden_evaluar(): void
    {
        DB::table('pasantia')->where('idPasantia', 2)->update(['fechaFinEstimada' => null]);

        $this->actingAs($this->coordinador)
            ->post(route('coordinador.alertas.evaluar'))
            ->assertSessionHas('success', 'Se evaluó 1 pasantía.')
            ->assertSessionHas('avisos', ['Pasantía #2: La pasantía no tiene fecha final estimada.']);
    }

    public function test_recomendaciones_segun_el_contexto(): void
    {
        $this->assertSame(
            [
                'Pedir al estudiante que entregue los 2 documentos que le faltan.',
                'Revisar el documento que espera revisión.',
                'La fecha final estimada pasó hace 3 días: confirmar con la empresa si la pasantía se extendió.',
            ],
            RiskRecommendations::para([
                'documentos_no_entregados' => 2,
                'documentos_pendientes' => 1,
                'dias_restantes' => -3,
            ])
        );

        $this->assertSame(
            ['La documentación está al día. No se requiere ninguna acción por ahora.'],
            RiskRecommendations::para(['dias_restantes' => 40])
        );
    }
}
