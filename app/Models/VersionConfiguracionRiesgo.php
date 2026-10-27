<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VersionConfiguracionRiesgo extends Model
{
    protected $table = 'versiones_configuracion_riesgo';
    protected $primaryKey = 'versionConfiguracion';

    protected $fillable = [
        'huella',
        'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'configuracion' => 'array',
        ];
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(
            EvaluacionRiesgo::class,
            'versionConfiguracion',
            'versionConfiguracion'
        );
    }
}
