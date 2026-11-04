<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificación interna dirigida a un usuario del sistema.
 */
class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $primaryKey = 'idNotificacion';

    public $timestamps = false;

    protected $fillable = [
        'credencial',
        'idDocumento',
        'idHistorial',
        'nombreDocumento',
        'estadoDocumento',
        'titulo',
        'mensaje',
        'observacion',
        'fechaCreacion',
        'fechaLectura',
    ];

    protected function casts(): array
    {
        return [
            'fechaCreacion' => 'datetime',
            'fechaLectura' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'credencial', 'credencial');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'idDocumento', 'idDocumento');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentoHistorial::class, 'idHistorial', 'idHistorial');
    }

    public function scopeDe(Builder $consulta, string $credencial): Builder
    {
        return $consulta->where('credencial', $credencial);
    }

    public function scopeNoLeidas(Builder $consulta): Builder
    {
        return $consulta->whereNull('fechaLectura');
    }

    public function getLeidaAttribute(): bool
    {
        return $this->fechaLectura !== null;
    }
}
