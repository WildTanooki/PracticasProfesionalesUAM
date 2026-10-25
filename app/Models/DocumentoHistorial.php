<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoHistorial extends Model
{
    protected $table = 'documento_historial';

    protected $primaryKey = 'idHistorial';

    public $timestamps = false;

    protected $fillable = [
        'idDocumento',
        'archivo',
        'version',
        'estadoArchivo',
        'observacion',
        'idCoordinador',
        'fechaEnvio',
        'fechaRevision',
    ];

    protected $casts = [
        'fechaEnvio' => 'datetime',
        'fechaRevision' => 'datetime',
        'version' => 'integer',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(
            Documento::class,
            'idDocumento',
            'idDocumento'
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
