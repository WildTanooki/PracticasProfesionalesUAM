<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_evaluacion_regla', function (Blueprint $table) {
            $table->id('idDetalleEvaluacionRegla');

            $table->foreignId('idEvaluacionRiesgo')
                ->constrained('evaluaciones_riesgo', 'idEvaluacionRiesgo')
                ->cascadeOnDelete();

            $table->foreignId('idReglaRiesgo')
                ->constrained('reglas_riesgo', 'idReglaRiesgo')
                ->restrictOnDelete();

            $table->boolean('cumplida');

            $table->decimal('puntuacionAplicada', 5, 2)->default(0);

            $table->text('resultado')->nullable();

            // Datos de las condiciones y valores usados en la evaluación.
            $table->json('detalleCondiciones')->nullable();

            $table->timestamps();

            $table->unique(
                ['idEvaluacionRiesgo', 'idReglaRiesgo'],
                'uq_detalle_evaluacion_regla'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_evaluacion_regla');
    }
};
