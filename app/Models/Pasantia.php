<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pasantia extends Model
{
    protected $table = 'Pasantia';

    protected $primaryKey = 'idPasantia';

    public $timestamps = false;

    protected $fillable = [
        'idPractica',
        'idCartaAprobacion',
        'estado',
        'horasRequeridas',
        'horasAcumuladas',
        'fechaInicio',
        'fechaFinEstimada',
        'idTutor',
        'empresa',
        'cargo',
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

    // Carta de aprobación que originó esta pasantía.
    public function cartaAprobacion(): BelongsTo
    {
        return $this->belongsTo(
            CartaAprobacion::class,
            'idCartaAprobacion',
            'idCartaAprobacion'
        );
    }

    // Una pasantía puede tener muchos documentos asociados.
    public function documentos(): HasMany
    {
        return $this->hasMany(
            Documento::class,
            'idPasantia',
            'idPasantia'
        );
    }

    // Evaluaciones de riesgo de la pasantía (historial).
    public function evaluacionesRiesgo(): HasMany
    {
        return $this->hasMany(
            EvaluacionRiesgo::class,
            'idPasantia',
            'idPasantia'
        );
    }

    // Evaluación de riesgo más reciente.
    public function ultimaEvaluacionRiesgo(): HasOne
    {
        return $this->hasOne(
            EvaluacionRiesgo::class,
            'idPasantia',
            'idPasantia'
        )->latestOfMany('idEvaluacionRiesgo');
    }
}
