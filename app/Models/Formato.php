<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $primaryKey = 'idDocumento';

    public $timestamps = false;

    protected $fillable = [
        'idFormato',
        'archivo',
        'estadoArchivo',
        'observacion',
        'idCoordinador',
        'idPasantia',
    ];

    public function formato(): BelongsTo
    {
        return $this->belongsTo(
            Formato::class,
            'idFormato',
            'idFormato'
        );
    }

    public function pasantia(): BelongsTo
    {
        return $this->belongsTo(
            Pasantia::class,
            'idPasantia',
            'idPasantia'
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

    public function historial(): HasMany
    {
        return $this->hasMany(
            DocumentoHistorial::class,
            'idDocumento',
            'idDocumento'
        )->orderByDesc('version');
    }
}
