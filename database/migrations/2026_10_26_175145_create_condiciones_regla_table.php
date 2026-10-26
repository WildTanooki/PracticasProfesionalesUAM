<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condiciones_regla', function (Blueprint $table) {
            $table->id('idCondicionRegla');

            $table->foreignId('idReglaRiesgo')
                ->constrained('reglas_riesgo', 'idReglaRiesgo')
                ->cascadeOnDelete();

            $table->string('variable', 80);
            $table->string('operador', 2);
            $table->string('parametro', 100);

            // entero, decimal, texto, booleano o fecha.
            $table->string('tipoParametro', 20);

            // Conector que une esta condición con la siguiente.
            $table->string('conector', 3)->default('AND');

            $table->unsignedSmallInteger('orden')->default(1);

            $table->timestamps();

            $table->unique(
                ['idReglaRiesgo', 'orden'],
                'uq_condicion_regla_orden'
            );

            $table->index(['variable', 'operador']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_regla');
    }
};
