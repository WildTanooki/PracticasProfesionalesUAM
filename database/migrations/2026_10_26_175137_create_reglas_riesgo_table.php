<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglas_riesgo', function (Blueprint $table) {
            $table->id('idReglaRiesgo');

            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();

            // Contribución de la regla a la puntuación total.
            $table->decimal('puntuacion', 5, 2)->default(0);

            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('prioridad')->default(1);

            // Coordinador que creó o administra la regla.
            // NULL permite reglas generales del sistema.
            $table->string('idCoordinador', 30)
            ->nullable()
            ->charset('utf8mb4')
            ->collation('utf8mb4_0900_ai_ci');

            $table->timestamps();

            $table->index('activa');
            $table->index(['activa', 'prioridad']);

            $table->foreign('idCoordinador', 'fk_reglas_riesgo_coordinador')
                ->references('idCoordinador')
                ->on('coordinacion_academica')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglas_riesgo');
    }
};
