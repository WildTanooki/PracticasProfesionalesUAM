<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formato extends Model
{
    use HasFactory;

    protected $table = 'Formatos';

    protected $primaryKey = 'idFormato';

    public $timestamps = false;

    protected $fillable = [
        'archivo',
        'nombreFormato',
        'estado',
        'descripcion',
        'ultimaEdicion',
        'idCoordinador',
    ];

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(
            CoordinacionAcademica::class,
            'idCoordinador',
            'idCoordinador'
        );
    }

    // Documentos entregados por los estudiantes para este formato.
    public function documentos(): HasMany
    {
        return $this->hasMany(
            Documento::class,
            'idFormato',
            'idFormato'
        );
    }
}
