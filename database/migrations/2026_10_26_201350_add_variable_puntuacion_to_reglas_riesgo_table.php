<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglas_riesgo', function (Blueprint $table) {
            $table->string('variablePuntuacion', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reglas_riesgo', function (Blueprint $table) {
            $table->dropColumn('variablePuntuacion');
        });
    }
};