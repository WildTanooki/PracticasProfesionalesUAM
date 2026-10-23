<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    protected $table = 'Carrera';

    protected $primaryKey = 'idCarrera';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // Una carrera puede tener varios planes de estudio.
    public function planesDeEstudio(): HasMany
    {
        return $this->hasMany(
            PlanDeEstudios::class,
            'idCarrera',
            'idCarrera'
        );
    }
}
