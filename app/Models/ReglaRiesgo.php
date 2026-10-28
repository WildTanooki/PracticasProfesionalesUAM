<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'variablePuntuacion',
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

    // Código visible en la pantalla, ej. R-001.
    protected function codigo(): Attribute
    {
        return Attribute::get(
            fn () => 'R-' . str_pad((string) $this->idReglaRiesgo, 3, '0', STR_PAD_LEFT)
        );
    }

    // Reglas generales (sin coordinador) y las de la carrera indicada.
    public function scopeVisiblesParaCarrera(Builder $query, int $idCarrera): Builder
    {
        return $query->where(function ($consulta) use ($idCarrera) {
            $consulta->whereNull('idCoordinador')
                ->orWhereHas('coordinador', function ($coordinador) use ($idCarrera) {
                    $coordinador->where('idCarrera', $idCarrera);
                });
        });
    }

    // Reglas creadas por coordinadores de la carrera indicada.
    public function scopeDeCarrera(Builder $query, int $idCarrera): Builder
    {
        return $query->whereHas('coordinador', function ($coordinador) use ($idCarrera) {
            $coordinador->where('idCarrera', $idCarrera);
        });
    }
}
