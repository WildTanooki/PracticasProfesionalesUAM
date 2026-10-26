<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleEvaluacionRegla extends Model
{
    protected $table = 'detalle_evaluacion_regla';
    protected $primaryKey = 'idDetalleEvaluacionRegla';

    protected $fillable = [
        'idEvaluacionRiesgo',
        'idReglaRiesgo',
        'cumplida',
        'puntuacionAplicada',
        'resultado',
        'detalleCondiciones',
    ];

    protected function casts(): array
    {
        return [
            'cumplida' => 'boolean',
            'puntuacionAplicada' => 'decimal:2',
            'detalleCondiciones' => 'array',
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(
            EvaluacionRiesgo::class,
            'idEvaluacionRiesgo',
            'idEvaluacionRiesgo'
        );
    }

    public function regla(): BelongsTo
    {
        return $this->belongsTo(
            ReglaRiesgo::class,
            'idReglaRiesgo',
            'idReglaRiesgo'
        );
    }
}
