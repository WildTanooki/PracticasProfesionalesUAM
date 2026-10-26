<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReglaRiesgo extends Model
{
    protected $table = 'reglas_riesgo';
    protected $primaryKey = 'idReglaRiesgo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'puntuacion',
        'activa',
        'prioridad',
        'idCoordinador',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'decimal:2',
            'activa' => 'boolean',
            'prioridad' => 'integer',
        ];
    }

    public function condiciones(): HasMany
    {
        return $this->hasMany(
            CondicionRegla::class,
            'idReglaRiesgo',
            'idReglaRiesgo'
        )->orderBy('orden');
    }

    public function detallesEvaluacion(): HasMany
    {
        return $this->hasMany(
            DetalleEvaluacionRegla::class,
            'idReglaRiesgo',
            'idReglaRiesgo'
        );
    }

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(
            CoordinacionAcademica::class,
            'idCoordinador',
            'idCoordinador'
        );
    }
}
