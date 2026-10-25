<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CartaAprobacion extends Model
{
    protected $table = 'CartaAprobacion';

    protected $primaryKey = 'idCartaAprobacion';

    public $timestamps = false;

    protected $fillable = [
        'credencial',
        'estadoAprobacion',
        'rutaArchivo',
        'fechaEmision',
        'observacion',
        'empresaDestino',
        'puestoCargo',
        'fechaInicio',
        'fechaFin',
        'telefonoTutor',
        'apellidoTutor',
        'nombreTutor',
        'correoTutor',
        'horasPropuestas',
    ];

    protected $casts = [
        'fechaEmision' => 'date',
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
    ];
    
    // Pasantía creada a partir de esta carta, si existe.
    public function pasantia(): HasOne
    {
        return $this->hasOne(
            Pasantia::class,
            'idCartaAprobacion',
            'idCartaAprobacion'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'credencial',
            'credencial'
        );
    }
}
