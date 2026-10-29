<?php

namespace Tests\Feature\Coordinador;

use App\Models\CondicionRegla;
use App\Models\ReglaRiesgo;
use App\Models\UmbralRiesgo;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pantalla de reglas parametrizables del coordinador.
 *
 * Las tablas de usuarios y coordinación vienen de una base de datos
 * externa, así que se crea aquí una copia mínima de su esquema.
 */
class ReglaRiesgoCoordinadorTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $coordinador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->crearEsquema();
        $this->crearDatos();

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

        Schema::create('coordinacion_academica', function (Blueprint $tabla) {
            $tabla->string('idCoordinador', 30)->primary();
            $tabla->string('credencial', 30)->unique();
            $tabla->unsignedInteger('idCarrera');
        });
    }

    private function crearDatos(): void
    {
        $usuarios = [
            ['COORD-1', 'Coordinador'],
            ['COORD-2', 'Coordinador'],
            ['EST-1', 'Estudiante'],
        ];

        foreach ($usuarios as [$credencial, $rol]) {
            DB::table('usuarios')->insert([
                'credencial' => $credencial,
                'nombre' => $credencial,
                'correo' => strtolower($credencial) . '@uam.test',
                'contrasena' => bcrypt('secreto'),
                'rol' => $rol,
            ]);
        }

        // COORD-1 es de la carrera 1 y COORD-2 de la carrera 2.
        DB::table('coordinacion_academica')->insert([
            ['idCoordinador' => 'C1', 'credencial' => 'COORD-1', 'idCarrera' => 1],
            ['idCoordinador' => 'C2', 'credencial' => 'COORD-2', 'idCarrera' => 2],
        ]);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_replace([
            'nombre' => 'Poca documentación aprobada',
            'descripcion' => 'Quedan pocos días y faltan documentos.',
            'puntuacion' => '30',
            'variablePuntuacion' => '',
            'prioridad' => '1',
            'activa' => '1',
            'conector' => 'AND',
            'condiciones' => [
                ['variable' => 'porcentaje_documental_completado', 'operador' => '<', 'parametro' => '10.5'],
                ['variable' => 'dias_restantes', 'operador' => '<=', 'parametro' => '15'],
            ],
            '_formulario' => 'crear',
        ], $cambios);
    }

    private function crearRegla(?string $idCoordinador, string $nombre): ReglaRiesgo
    {
        $regla = ReglaRiesgo::create([
            'nombre' => $nombre,
            'puntuacion' => 10,
            'activa' => true,
            'prioridad' => 1,
            'idCoordinador' => $idCoordinador,
        ]);

        $regla->condiciones()->create([
            'variable' => 'documentos_rechazados',
            'operador' => '>',
            'parametro' => '0',
            'tipoParametro' => 'entero',
            'conector' => 'AND',
            'orden' => 1,
        ]);

        return $regla;
    }

    public function test_un_estudiante_no_puede_entrar(): void
    {
        $this->actingAs(Usuario::find('EST-1'))
            ->get(route('coordinador.reglas'))
            ->assertForbidden();

        $this->actingAs(Usuario::find('EST-1'))
            ->post(route('coordinador.reglas.store'), $this->datosValidos())
            ->assertForbidden();

        $this->assertSame(0, ReglaRiesgo::count());
    }

    public function test_crea_una_regla_con_sus_condiciones(): void
    {
        $this->actingAs($this->coordinador)
            ->post(route('coordinador.reglas.store'), $this->datosValidos())
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasNoErrors();

        $regla = ReglaRiesgo::with('condiciones')->sole();

        $this->assertSame('C1', $regla->idCoordinador);
        $this->assertSame('Poca documentación aprobada', $regla->nombre);
        $this->assertNull($regla->variablePuntuacion);
        $this->assertTrue($regla->activa);

        $condiciones = $regla->condiciones;
        $this->assertCount(2, $condiciones);

        // El tipo sale del catálogo y el conector es el de la regla.
        $this->assertSame(
            [
                ['porcentaje_documental_completado', '<', '10.5', 'decimal', 'AND', 1],
                ['dias_restantes', '<=', '15', 'entero', 'AND', 2],
            ],
            $condiciones->map(fn ($c) => [
                $c->variable, $c->operador, $c->parametro, $c->tipoParametro, $c->conector, (int) $c->orden,
            ])->all()
        );
    }

    public static function datosInvalidos(): array
    {
        return [
            'sin condiciones' => [
                ['condiciones' => []],
                'condiciones',
            ],
            'variable desconocida' => [
                ['condiciones' => [['variable' => 'notas', 'operador' => '<', 'parametro' => '1']]],
                'condiciones.0.variable',
            ],
            'operador inválido' => [
                ['condiciones' => [['variable' => 'dias_restantes', 'operador' => 'LIKE', 'parametro' => '1']]],
                'condiciones.0.operador',
            ],
            'decimal en variable entera' => [
                ['condiciones' => [['variable' => 'dias_restantes', 'operador' => '<', 'parametro' => '2.5']]],
                'condiciones.0.parametro',
            ],
            'porcentaje mayor a 100' => [
                ['condiciones' => [['variable' => 'porcentaje_documental_completado', 'operador' => '<', 'parametro' => '150']]],
                'condiciones.0.parametro',
            ],
            'conector inválido' => [
                ['conector' => 'XOR'],
                'conector',
            ],
            'sin nombre' => [
                ['nombre' => ''],
                'nombre',
            ],
            'variable de puntuación desconocida' => [
                ['variablePuntuacion' => 'notas'],
                'variablePuntuacion',
            ],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_rechaza_reglas_invalidas(array $cambios, string $campo): void
    {
        $this->actingAs($this->coordinador)
            ->from(route('coordinador.reglas'))
            ->post(route('coordinador.reglas.store'), $this->datosValidos($cambios))
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasErrors($campo);

        $this->assertSame(0, ReglaRiesgo::count());
        $this->assertSame(0, CondicionRegla::count());
    }

    public function test_editar_reemplaza_las_condiciones(): void
    {
        $regla = $this->crearRegla('C1', 'Documentos rechazados');

        $this->actingAs($this->coordinador)
            ->put(route('coordinador.reglas.update', $regla->idReglaRiesgo), $this->datosValidos([
                'nombre' => 'Rechazos o pocos días',
                'conector' => 'OR',
                'activa' => '0',
                'variablePuntuacion' => 'documentos_rechazados',
                'condiciones' => [
                    ['variable' => 'documentos_rechazados', 'operador' => '>=', 'parametro' => '2'],
                ],
                '_formulario' => 'editar',
            ]))
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasNoErrors();

        $regla->refresh()->load('condiciones');

        $this->assertSame('Rechazos o pocos días', $regla->nombre);
        $this->assertFalse($regla->activa);
        $this->assertSame('documentos_rechazados', $regla->variablePuntuacion);
        $this->assertCount(1, $regla->condiciones);
        $this->assertSame('>=', $regla->condiciones[0]->operador);
        $this->assertSame('2', $regla->condiciones[0]->parametro);
        $this->assertSame(1, CondicionRegla::count());
    }

    public function test_no_edita_reglas_de_otra_carrera(): void
    {
        $ajena = $this->crearRegla('C2', 'De otra carrera');

        $this->actingAs($this->coordinador)
            ->put(route('coordinador.reglas.update', $ajena->idReglaRiesgo), $this->datosValidos([
                'nombre' => 'Cambiada',
            ]))
            ->assertNotFound();

        $this->assertSame('De otra carrera', $ajena->refresh()->nombre);
    }

    public function test_edita_reglas_generales_sin_asignarles_coordinador(): void
    {
        $general = $this->crearRegla(null, 'General');

        $this->actingAs($this->coordinador)
            ->put(route('coordinador.reglas.update', $general->idReglaRiesgo), $this->datosValidos([
                'nombre' => 'General cambiada',
                '_formulario' => 'editar',
            ]))
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasNoErrors();

        $general->refresh();

        $this->assertSame('General cambiada', $general->nombre);
        $this->assertNull($general->idCoordinador);
    }

    public function test_la_lista_muestra_reglas_de_la_carrera_y_generales(): void
    {
        $propia = $this->crearRegla('C1', 'Regla propia');
        $this->crearRegla(null, 'Regla general');
        $this->crearRegla('C2', 'Regla de otra carrera');

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.reglas'))
            ->assertOk()
            ->assertSee('Regla propia')
            ->assertSee('Regla general')
            ->assertDontSee('Regla de otra carrera')
            ->assertSee($propia->codigo)
            ->assertSee('La cantidad de documentos rechazados es mayor que 0');
    }

    public function test_la_condicion_se_lee_como_frase(): void
    {
        $this->assertSame(
            'los días que faltan para la fecha final de la pasantía son mayores o iguales que 2',
            \App\Services\RulesEngine\RiskVariables::frase('dias_restantes', '>=', '2')
        );
        $this->assertSame(
            'el porcentaje de documentos aprobados es menor que 10%',
            \App\Services\RulesEngine\RiskVariables::frase('porcentaje_documental_completado', '<', '10')
        );
    }

    public function test_filtra_por_busqueda_y_estado(): void
    {
        $this->crearRegla('C1', 'Regla activa');
        $this->crearRegla('C1', 'Regla apagada')->update(['activa' => false]);

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.reglas', ['estado' => 'Inactiva']))
            ->assertOk()
            ->assertSee('Regla apagada')
            ->assertDontSee('Regla activa');

        $this->actingAs($this->coordinador)
            ->get(route('coordinador.reglas', ['buscar' => 'activa']))
            ->assertOk()
            ->assertSee('Regla activa')
            ->assertDontSee('Regla apagada');
    }

    public function test_guarda_umbrales_validos(): void
    {
        UmbralRiesgo::create(['nivel' => 'Crítico', 'limiteInferior' => 80, 'limiteSuperior' => null, 'activo' => true]);

        $this->actingAs($this->coordinador)
            ->put(route('coordinador.reglas.umbrales'), ['umbrales' => [
                ['nivel' => 'Alto', 'limiteInferior' => '40', 'limiteSuperior' => ''],
                ['nivel' => 'Bajo', 'limiteInferior' => '0', 'limiteSuperior' => '19.99'],
                ['nivel' => 'Medio', 'limiteInferior' => '20', 'limiteSuperior' => '39.99'],
            ]])
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasNoErrors();

        $activos = UmbralRiesgo::where('activo', true)->orderBy('limiteInferior')->get();

        $this->assertSame(['Bajo', 'Medio', 'Alto'], $activos->pluck('nivel')->all());
        $this->assertSame('19.99', $activos[0]->limiteSuperior);
        $this->assertNull($activos[2]->limiteSuperior);

        // El nivel que ya no se usa queda inactivo, no se borra.
        $this->assertFalse(UmbralRiesgo::where('nivel', 'Crítico')->value('activo'));
    }

    public static function umbralesInvalidos(): array
    {
        return [
            'con hueco' => [[
                ['nivel' => 'Bajo', 'limiteInferior' => '0', 'limiteSuperior' => '24.99'],
                ['nivel' => 'Medio', 'limiteInferior' => '30', 'limiteSuperior' => '49.99'],
                ['nivel' => 'Alto', 'limiteInferior' => '50', 'limiteSuperior' => ''],
            ]],
            'no empieza en cero' => [[
                ['nivel' => 'Bajo', 'limiteInferior' => '5', 'limiteSuperior' => '24.99'],
                ['nivel' => 'Alto', 'limiteInferior' => '25', 'limiteSuperior' => ''],
            ]],
            'último con límite' => [[
                ['nivel' => 'Bajo', 'limiteInferior' => '0', 'limiteSuperior' => '24.99'],
                ['nivel' => 'Alto', 'limiteInferior' => '25', 'limiteSuperior' => '100'],
            ]],
            'niveles repetidos' => [[
                ['nivel' => 'Bajo', 'limiteInferior' => '0', 'limiteSuperior' => '24.99'],
                ['nivel' => 'Bajo', 'limiteInferior' => '25', 'limiteSuperior' => ''],
            ]],
        ];
    }

    #[DataProvider('umbralesInvalidos')]
    public function test_rechaza_umbrales_invalidos_sin_cambiar_nada(array $umbrales): void
    {
        UmbralRiesgo::create(['nivel' => 'Bajo', 'limiteInferior' => 0, 'limiteSuperior' => 49.99, 'activo' => true]);
        UmbralRiesgo::create(['nivel' => 'Alto', 'limiteInferior' => 50, 'limiteSuperior' => null, 'activo' => true]);

        $this->actingAs($this->coordinador)
            ->from(route('coordinador.reglas'))
            ->put(route('coordinador.reglas.umbrales'), ['umbrales' => $umbrales])
            ->assertRedirect(route('coordinador.reglas'))
            ->assertSessionHasErrors();

        $this->assertSame(
            [['Bajo', '0.00', '49.99', true], ['Alto', '50.00', null, true]],
            UmbralRiesgo::orderBy('limiteInferior')->get()
                ->map(fn ($u) => [$u->nivel, $u->limiteInferior, $u->limiteSuperior, $u->activo])
                ->all()
        );
    }

    public function test_muestra_niveles_iniciales_si_no_hay_umbrales(): void
    {
        $this->actingAs($this->coordinador)
            ->get(route('coordinador.reglas'))
            ->assertOk()
            ->assertSee('Niveles de riesgo')
            ->assertSee('Bajo')
            ->assertSee('Medio')
            ->assertSee('Alto');
    }
}
