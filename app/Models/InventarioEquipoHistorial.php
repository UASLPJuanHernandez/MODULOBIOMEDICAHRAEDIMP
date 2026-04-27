<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioEquipoHistorial extends Model
{
    protected $table = 'inventario_equipo_historiales';

    protected $fillable = [
        'inventario_equipo_id',
        'tipo_evento',
        'cambios',
        'descripcion',
        'usuario_id',
        'usuario_nombre',
        'ip_address',
    ];

    protected $casts = [
        'cambios' => 'array',
    ];

    public function inventarioEquipo(): BelongsTo
    {
        return $this->belongsTo(InventarioEquipo::class, 'inventario_equipo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function getTipoEventoBadgeColor(): string
    {
        return match ($this->tipo_evento) {
            'creado'     => 'success',
            'actualizado' => 'info',
            'eliminado'  => 'danger',
            default      => 'gray',
        };
    }
}
