<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaItem extends Model
{
    protected $fillable = [
        'auditoria_id',
        'mobiliario_id',
        'presente',
        'comentarios',
        'requiere_vale',
        'folio_vale',
        'fecha_verificacion',
    ];

    protected $casts = [
        'presente' => 'boolean',
        'requiere_vale' => 'boolean',
        'fecha_verificacion' => 'datetime',
    ];

    public function auditoria(): BelongsTo
    {
        return $this->belongsTo(Auditoria::class);
    }

    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class);
    }
}
