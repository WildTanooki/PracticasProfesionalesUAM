<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'Usuarios';

    protected $primaryKey = 'credencial';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'credencial',
        'nombre',
        'apellido',
        'telefono',
        'correo',
        'contrasena',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function estudiante(): HasOne
    {
        return $this->hasOne(
            Estudiante::class,
            'credencial',
            'credencial'
        );
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(
            Tutor::class,
            'credencial',
            'credencial'
        );
    }

    public function coordinacion(): HasOne
    {
        return $this->hasOne(
            CoordinacionAcademica::class,
            'credencial',
            'credencial'
        );
    }
}
