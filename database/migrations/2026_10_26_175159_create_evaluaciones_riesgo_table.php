<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_riesgo', function (Blueprint $table) {
            $table->id('idEvaluacionRiesgo');

            // En tu esquema, pasantia.idPasantia es INT UNSIGNED.
            $table->unsignedInteger('idPasantia');

            $table->decimal('puntuacionTotal', 5, 2)->nullable();
            $table->string('nivelRiesgo', 20)->nullable();

            $table->dateTime('fechaEvaluacion')->useCurrent();

            // Versión de configuración aplicada en la evaluación.
            $table->unsignedInteger('versionConfiguracion')->default(1);

            // Una evaluación con error no debe aparentar riesgo bajo.
            $table->string('estadoEvaluacion', 30)
                ->default('Correcta');

            $table->text('errorConfiguracion')->nullable();

            // Contexto utilizado para poder explicar el resultado.
            $table->json('contextoEvaluado')->nullable();

            $table->timestamps();

            $table->index(
                ['idPasantia', 'fechaEvaluacion'],
                'idx_evaluacion_pasantia_fecha'
            );

            $table->foreign('idPasantia', 'fk_evaluacion_riesgo_pasantia')
                ->references('idPasantia')
                ->on('pasantia')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_riesgo');
    }
};