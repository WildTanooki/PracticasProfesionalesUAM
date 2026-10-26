<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umbrales_riesgo', function (Blueprint $table) {
            $table->id('idUmbralRiesgo');

            $table->string('nivel', 20)->unique();

            $table->decimal('limiteInferior', 5, 2);
            $table->decimal('limiteSuperior', 5, 2);

            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umbrales_riesgo');
    }
};
