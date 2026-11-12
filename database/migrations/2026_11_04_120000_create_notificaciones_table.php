<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notificaciones internas del sistema (sin correo ni servicios externos).
 *
 * Por ahora se usan para avisar al estudiante cuando Coordinación
 * Académica revisa uno de sus documentos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('idNotificacion');

            // Usuario que recibe la notificación. Misma intercalación que
            // usuarios.credencial para poder crear la clave foránea.
            // En SQLite (pruebas) la intercalación de MySQL no existe.
            $credencial = $table->string('credencial', 30);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $credencial->charset('utf8mb4')->collation('utf8mb4_0900_as_cs');
            }

            // Documento y versión revisados.
            $table->unsignedInteger('idDocumento');
            $table->unsignedInteger('idHistorial')->nullable();

            // Nombre del formato al momento de la revisión.
            $table->string('nombreDocumento', 100);

            // Aprobado, A corregir o Rechazado.
            $table->string('estadoDocumento', 30);

            $table->string('titulo', 150);
            $table->string('mensaje', 255);

            // Observación registrada por Coordinación Académica.
            $table->string('observacion', 200)->nullable();

            $table->dateTime('fechaCreacion')->useCurrent();

            // Nula mientras el estudiante no la haya leído.
            $table->dateTime('fechaLectura')->nullable();

            $table->index(['credencial', 'fechaLectura'], 'idx_notificaciones_usuario_lectura');

            $table->foreign('credencial', 'fk_notificaciones_usuario')
                ->references('credencial')
                ->on('usuarios')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('idDocumento', 'fk_notificaciones_documento')
                ->references('idDocumento')
                ->on('documentos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('idHistorial', 'fk_notificaciones_historial')
                ->references('idHistorial')
                ->on('documento_historial')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
