<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticaProfesional extends Model
{
    protected $table = 'PracticaProfesional';

    protected $primaryKey = 'idPractica';

    public $timestamps = false;

    protected $fillable = [
        'nombrePractica',
        'horasRequeridas',
        'estadoPractica',
        'cif',
    ];

    protected $casts = [
        'horasRequeridas' => 'integer',
    ];

    // Una práctica profesional pertenece a un estudiante.
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(
            Estudiante::class,
            'cif',
            'credencial'
        );
    }

    // Una práctica profesional puede tener varias pasantías.
    public function pasantias(): HasMany
    {
        return $this->hasMany(
            Pasantia::class,
            'idPractica',
            'idPractica'
        );
    }
}
