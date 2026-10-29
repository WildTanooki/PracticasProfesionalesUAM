<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroDeHoras extends Model
{
    use HasFactory;

    protected $table = 'registro_de_horas';

    protected $primaryKey = 'idRegistro';

    public $timestamps = false;

    protected $fillable = [
        'fechaRegistro',
        'horasIngresadas',
        'observaciones',
        'estadoHoras',
        'idPasantia',
        'idTutor',
    ];

    protected function casts(): array
    {
        return [
            'fechaRegistro' => 'date',
            'horasIngresadas' => 'integer',
        ];
    }

    public function pasantia(): BelongsTo
    {
        return $this->belongsTo(
            Pasantia::class,
            'idPasantia',
            'idPasantia'
        );
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(
            Tutor::class,
            'idTutor',
            'idTutor'
        );
    }
}
