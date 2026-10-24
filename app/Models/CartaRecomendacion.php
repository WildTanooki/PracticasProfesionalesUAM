<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartaRecomendacion extends Model
{
    protected $table = 'CartaRecomendacion';

    protected $primaryKey = 'idCartaRecomendacion';

    public $timestamps = false;

    protected $fillable = [
        'empresaDestino',
        'puestoCargo',
        'fechaSolicitud',
        'rutaCartaEmitida',
        'estadoCarta',
        'idCoordinador',
        'credencialEstudiante',
        'observacionEstudiante',
        'observacionCoordinador',
    ];

    protected $attributes = [
        'estadoCarta' => 'Pendiente',
    ];

    protected $casts = [
        'fechaSolicitud' => 'date',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(
            Estudiante::class,
            'credencialEstudiante',
            'credencial'
        );
    }

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(
            CoordinacionAcademica::class,
            'idCoordinador',
            'idCoordinador'
        );
    }
}
