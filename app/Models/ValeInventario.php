<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValeInventario extends Model
{
    protected $table = 'vale_inventarios';

    protected $fillable = [
        'tipo',
        'estado',
        'jefe_id',
        'inventario_equipo_id',
        'consumible_id',
        'cantidad_entregada',
        'numero_inventario',
        'equipo_nombre',
        'area',
        'unidad_medica',
        'marca',
        'modelo',
        'numero_serie',
        'usuario_id',
        'usuario_nombre',
        'nombre_entrega',
        'cargo_entrega',
        'quien_recibe',
        'cargo_recibe',
        'equipos',
        'observaciones',
        'firma_imagen',
        'firma_ingeniero',
        'firmado_at',
        'concretado_at',
    ];

    protected $casts = [
        'firmado_at'    => 'datetime',
        'concretado_at' => 'datetime',
        'equipos'       => 'array',
    ];

    public function inventarioEquipo(): BelongsTo
    {
        return $this->belongsTo(InventarioEquipo::class, 'inventario_equipo_id');
    }

    public function consumible(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Consumible::class, 'consumible_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PersonalReportante::class, 'jefe_id');
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
