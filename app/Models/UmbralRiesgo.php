<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmbralRiesgo extends Model
{
    protected $table = 'umbrales_riesgo';
    protected $primaryKey = 'idUmbralRiesgo';

    protected $fillable = [
        'nivel',
        'limiteInferior',
        'limiteSuperior',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'limiteInferior' => 'decimal:2',
            'limiteSuperior' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
