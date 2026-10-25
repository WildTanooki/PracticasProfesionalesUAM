<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tutor extends Model
{
    use HasFactory;

    protected $table = 'Tutor';

    protected $primaryKey = 'idTutor';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'idTutor',
        'credencial',
        'estadoTutor',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'credencial',
            'credencial'
        );
    }

    public function pasantias(): HasMany
    {
        return $this->hasMany(
            Pasantia::class,
            'idTutor',
            'idTutor'
        );
    }

    public function registrosDeHoras(): HasMany
    {
        return $this->hasMany(
            RegistroDeHoras::class,
            'idTutor',
            'idTutor'
        );
    }
}
