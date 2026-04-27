<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValeInventario extends Model
{
    protected $table = 'vale_inventarios';

    protected $fillable = [
        'tipo',
        'inventario_equipo_id',
        'numero_inventario',
        'equipo_nombre',
        'area',
        'unidad_medica',
        'marca',
        'modelo',
        'numero_serie',
        'usuario_id',
        'usuario_nombre',
    ];

    public function inventarioEquipo(): BelongsTo
    {
        return $this->belongsTo(InventarioEquipo::class, 'inventario_equipo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'entrega' => 'Vale de Entrega',
            'retiro'  => 'Vale de Retiro',
            default   => ucfirst($this->tipo),
        };
    }

    public function getTipoBadgeColorAttribute(): string
    {
        return match ($this->tipo) {
            'entrega' => 'success',
            'retiro'  => 'danger',
            default   => 'gray',
        };
    }
}
