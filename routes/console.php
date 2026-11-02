<?php

use App\Models\Pasantia;
use App\Services\EstadoPasantiaService;
use App\Services\RulesEngine\RiskEvaluator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Evaluar el riesgo de una pasantía, o de todas las que están en progreso.
Artisan::command('riesgo:evaluar {idPasantia? : Pasantía a evaluar}', function (RiskEvaluator $evaluador) {
    $ids = $this->argument('idPasantia')
        ? [(int) $this->argument('idPasantia')]
        : Pasantia::where('estado', 'En progreso')->orderBy('idPasantia')->pluck('idPasantia')->all();

    if ($ids === []) {
        $this->info('No hay pasantías en progreso para evaluar.');

        return 0;
    }

    $errores = 0;

    foreach ($ids as $idPasantia) {
        try {
            $evaluacion = $evaluador->evaluar($idPasantia);
        } catch (RuntimeException $e) {
            $errores++;
            $this->warn("Pasantía {$idPasantia}: no se evaluó. {$e->getMessage()}");

            continue;
        }

        if ($evaluacion->estadoEvaluacion === RiskEvaluator::ESTADO_CORRECTA) {
            $this->line(
                "Pasantía {$idPasantia}: riesgo {$evaluacion->nivelRiesgo} "
                . "({$evaluacion->puntuacionTotal} puntos, versión {$evaluacion->versionConfiguracion})."
            );
        } else {
            $errores++;
            $this->error("Pasantía {$idPasantia}: {$evaluacion->errorConfiguracion}");
        }
    }

    return $errores > 0 ? 1 : 0;
})->purpose('Evaluar el riesgo de las pasantías en progreso');

// Recalcular el estado de las pasantías: Culminada, Culminada con
// incumplimiento, Pendiente de revisión o En progreso.
Artisan::command('pasantias:actualizar-estados {idPasantia? : Pasantía a actualizar}', function (EstadoPasantiaService $servicio) {
    if ($this->argument('idPasantia')) {
        $estado = $servicio->actualizar((int) $this->argument('idPasantia'));
        $this->info("Pasantía {$this->argument('idPasantia')}: {$estado}.");

        return 0;
    }

    $cambios = $servicio->actualizarTodas();
    $this->info("Pasantías que cambiaron de estado: {$cambios}.");

    return 0;
})->purpose('Actualizar el estado de las pasantías según horas, documentos y fecha límite');

// Todos los días, para marcar las pasantías que llegaron a su fecha límite.
Schedule::command('pasantias:actualizar-estados')->dailyAt('00:10');
