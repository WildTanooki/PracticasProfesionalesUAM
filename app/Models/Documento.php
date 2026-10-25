<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Documento extends Model
{
    protected $table = 'Documentos';

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

    // Versiones enviadas del documento, de la más reciente a la más antigua.
    public function historial(): HasMany
    {
        return $this->hasMany(
            DocumentoHistorial::class,
            'idDocumento',
            'idDocumento'
        )->orderByDesc('version');
    }

    // Versión vigente: la última enviada por el estudiante.
    public function ultimaVersion(): HasOne
    {
        return $this->hasOne(
            DocumentoHistorial::class,
            'idDocumento',
            'idDocumento'
        )->ofMany('version', 'max');
    }
}
