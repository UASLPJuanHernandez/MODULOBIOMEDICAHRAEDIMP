<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registro extends Model
{
    protected $fillable = [
        'formato_id',
        'usuario_id',
        'identificador',
        'contenido_editado',
        'es_borrador',
        'estado',
        'firmado_por_id',
        'firmado_at',
    ];

    protected $casts = [
        'es_borrador' => 'boolean',
        'firmado_at'  => 'datetime',
    ];

    public function scopeBorradores($query)
    {
        return $query->where('es_borrador', true);
    }

    public function scopeFinales($query)
    {
        return $query->where('es_borrador', false);
    }

    public function formato(): BelongsTo
    {
        return $this->belongsTo(Formato::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function firmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'firmado_por_id');
    }
}
