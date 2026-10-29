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

    // Una pasantía puede tener muchos registros de horas asociados.
    public function registrosDeHoras(): HasMany
    {
        return $this->hasMany(
            RegistroDeHoras::class,
            'idPasantia',
            'idPasantia'
        );
    }
    
    //Calcula las horas correspondientes a registros aprobados.
    public function getHorasAprobadasAttribute(): int
    {
    return (int) $this->registrosDeHoras()
        ->where('estadoHoras', 'Aprobada')
        ->sum('horasIngresadas');
    }
}