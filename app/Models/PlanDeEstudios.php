<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanDeEstudios extends Model
{
    protected $table = 'Plan_De_Estudios';

    protected $primaryKey = 'idPlanEstudios';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'idPlanEstudios',
        'version',
        'cantidadPracticasRequeridas',
        'horasPorPractica',
        'idCarrera',
    ];

    protected $casts = [
        'cantidadPracticasRequeridas' => 'integer',
        'horasPorPractica' => 'integer',
        'idCarrera' => 'integer',
    ];

    // Un plan de estudio pertenece a una carrera.
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(
            Carrera::class,
            'idCarrera',
            'idCarrera'
        );
    }

    // Un plan de estudio puede corresponder a varios estudiantes.
    public function estudiantes(): HasMany
    {
        return $this->hasMany(
            Estudiante::class,
            'idPlanEstudios',
            'idPlanEstudios'
        );
    }
}
