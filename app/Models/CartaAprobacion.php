<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartaAprobacion extends Model
{
    protected $table = 'CartaAprobacion';

    protected $primaryKey = 'idCartaAprobacion';

    public $timestamps = false;

    protected $fillable = [
        'idPasantia',
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
    ];

    protected $casts = [
        'fechaEmision' => 'date',
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
    ];

    // Una carta de aprobación pertenece a una pasantía.
    public function pasantia(): BelongsTo
    {
        return $this->belongsTo(
            Pasantia::class,
            'idPasantia',
            'idPasantia'
        );
    }
}
