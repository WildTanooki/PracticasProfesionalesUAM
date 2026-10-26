<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondicionRegla extends Model
{
    protected $table = 'condiciones_regla';
    protected $primaryKey = 'idCondicionRegla';

    protected $fillable = [
        'idReglaRiesgo',
        'variable',
        'operador',
        'parametro',
        'tipoParametro',
        'conector',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'idReglaRiesgo' => 'integer',
            'orden' => 'integer',
        ];
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