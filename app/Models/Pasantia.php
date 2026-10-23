<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pasantia extends Model
{
    protected $table = 'Pasantia';

    protected $primaryKey = 'idPasantia';

    public $timestamps = false;

    protected $fillable = [
        'idPractica',
        'estado',
        'horasRequeridas',
        'horasAcumuladas',
        'fechaInicio',
        'fechaFinEstimada',
        'idTutor',
    ];

    protected $casts = [
        'horasRequeridas' => 'integer',
        'horasAcumuladas' => 'integer',
        'fechaInicio' => 'date',
        'fechaFinEstimada' => 'date',
    ];

    // Una pasantía pertenece a una práctica profesional.
    public function practicaProfesional(): BelongsTo
    {
        return $this->belongsTo(
            PracticaProfesional::class,
            'idPractica',
            'idPractica'
        );
    }

    // Una pasantía puede tener un tutor asignado.
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(
            Tutor::class,
            'idTutor',
            'idTutor'
        );
    }

    // Una pasantía puede tener varias cartas de aprobación.
    public function cartasAprobacion(): HasMany
    {
        return $this->hasMany(
            CartaAprobacion::class,
            'idPasantia',
            'idPasantia'
        );
    }
}
