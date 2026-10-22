<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoordinacionAcademica extends Model
{
    use HasFactory;

    protected $table = 'Coordinacion_Academica';

    protected $primaryKey = 'idCoordinador';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'idCoordinador',
        'credencial',
        'idCarrera',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'credencial',
            'credencial'
        );
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(
            Carrera::class,
            'idCarrera',
            'idCarrera'
        );
    }

    public function formatos(): HasMany
    {
        return $this->hasMany(
            Formato::class,
            'idCoordinador',
            'idCoordinador'
        );
    }

    public function reglas(): HasMany
    {
        return $this->hasMany(
            Regla::class,
            'idCoordinador',
            'idCoordinador'
        );
    }
}
