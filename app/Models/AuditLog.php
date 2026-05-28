<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'tipo',
        'descripcion',
        'actor_tipo',
        'actor_id',
        'actor_nombre',
        'documento_tipo',
        'documento_id',
        'ip',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Los audit logs son inmutables: no se pueden editar ni eliminar.
    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }
}
