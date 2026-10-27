<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluacionRiesgo extends Model
{
    protected $table = 'evaluaciones_riesgo';
    protected $primaryKey = 'idEvaluacionRiesgo';

    protected $fillable = [
        'idPasantia',
        'puntuacionTotal',
        'nivelRiesgo',
        'fechaEvaluacion',
        'versionConfiguracion',
        'estadoEvaluacion',
        'errorConfiguracion',
        'contextoEvaluado',
    ];

    protected function casts(): array
    {
        return [
            'idPasantia' => 'integer',
            'puntuacionTotal' => 'decimal:2',
            'fechaEvaluacion' => 'datetime',
            'versionConfiguracion' => 'integer',
            'contextoEvaluado' => 'array',
        ];
    }

    public function pasantia(): BelongsTo
    {
        return $this->belongsTo(
            Pasantia::class,
            'idPasantia',
            'idPasantia'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(
            DetalleEvaluacionRegla::class,
            'idEvaluacionRiesgo',
            'idEvaluacionRiesgo'
        );
    }

    // Configuración de reglas y umbrales usada en esta evaluación.
    public function version(): BelongsTo
    {
        return $this->belongsTo(
            VersionConfiguracionRiesgo::class,
            'versionConfiguracion',
            'versionConfiguracion'
        );
    }
}
