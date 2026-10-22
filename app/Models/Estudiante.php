<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'Estudiante';

    protected $primaryKey = 'estudianteID';

    protected $fillable = [
        'credencial',
        'anioAcademico',
        'idPlanEstudios',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'credencial',
            'credencial'
        );
    }

    public function planEstudios(): BelongsTo
    {
        return $this->belongsTo(
            PlanDeEstudios::class,
            'idPlanEstudios',
            'idPlanEstudios'
        );
    }

    public function practicas(): HasMany
    {
        return $this->hasMany(
            PracticaProfesional::class,
            'cif',
            'credencial'
        );
    }
}
