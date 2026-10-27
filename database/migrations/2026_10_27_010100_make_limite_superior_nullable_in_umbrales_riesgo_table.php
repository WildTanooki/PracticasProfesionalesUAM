<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // El motor exige que el último umbral (el nivel más alto) no tenga
    // límite superior, así que la columna debe aceptar NULL.
    public function up(): void
    {
        Schema::table('umbrales_riesgo', function (Blueprint $table) {
            $table->decimal('limiteSuperior', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('umbrales_riesgo', function (Blueprint $table) {
            $table->decimal('limiteSuperior', 5, 2)->nullable(false)->change();
        });
    }
};
