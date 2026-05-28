<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'partida_id',
        'nombre_proveedor',
        'monto_unitario',
        'monto_total',
        'cantidad_mobiliario',
    ];

    protected $casts = [
        'monto_unitario' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'cantidad_mobiliario' => 'integer',
    ];

    // Relaciones
    public function tipoPartida(): BelongsTo
    {
        return $this->belongsTo(TipoPartida::class, 'partida_id');
    }

    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    // Métodos auxiliares
    public function getDescripcionCompletaAttribute(): string
    {
        return "{$this->nombre_proveedor} - Total: $" . number_format($this->monto_total, 2);
    }

    // Scopes
    public function scopeByNombre($query, string $nombre)
    {
        return $query->where('nombre_proveedor', 'like', "%{$nombre}%");
    }

    public function scopeByMontoTotal($query, float $montoMin, ?float $montoMax = null)
    {
        $query->where('monto_total', '>=', $montoMin);
        if ($montoMax) {
            $query->where('monto_total', '<=', $montoMax);
        }
        return $query;
    }
}
