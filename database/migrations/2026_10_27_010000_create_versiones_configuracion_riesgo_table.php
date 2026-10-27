<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada combinación distinta de reglas, condiciones y umbrales
        // activos recibe un número de versión. Las evaluaciones guardan
        // ese número para saber con qué configuración se calcularon.
        Schema::create('versiones_configuracion_riesgo', function (Blueprint $table) {
            // INT UNSIGNED, igual que evaluaciones_riesgo.versionConfiguracion.
            $table->increments('versionConfiguracion');

            // SHA-256 de la configuración, para no repetir versiones.
            $table->char('huella', 64)->unique();

            // Copia de la configuración usada.
            $table->json('configuracion');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versiones_configuracion_riesgo');
    }
};
