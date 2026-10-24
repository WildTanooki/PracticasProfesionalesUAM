<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
