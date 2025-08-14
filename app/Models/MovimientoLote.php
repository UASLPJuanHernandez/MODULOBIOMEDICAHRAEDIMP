<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MovimientoLote extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_lote',
        'area_actual_id',
        'area_anterior_id',
        'fecha_movimiento',
        'se_entrega_con',
        'se_retira_con',
        'observacion',
        'usuario_id',
        'vale_generado',
        'vale_id'
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'vale_generado' => 'boolean',
    ];

    // Boot method para generar número de lote automáticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($movimientoLote) {
            if (!$movimientoLote->numero_lote) {
                $movimientoLote->numero_lote = static::generarNumeroLote();
            }
        });
    }

    // Relaciones
    public function mobiliarios(): BelongsToMany
    {
        return $this->belongsToMany(Mobiliario::class, 'movimiento_lote_mobiliario')
                    ->withPivot('area_anterior_id')
                    ->withTimestamps();
    }

    public function areaActual(): BelongsTo
    {
        return $this->belongsTo(Localizacion::class, 'area_actual_id');
    }

    public function areaAnterior(): BelongsTo
    {
        return $this->belongsTo(Localizacion::class, 'area_anterior_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vale(): HasOne
    {
        return $this->hasOne(Vale::class, 'movimiento_lote_id');
    }

    // Métodos auxiliares
    public static function generarNumeroLote(): string
    {
        $año = now()->year;
        
        // Obtener el último número del año actual
        $ultimoNumero = static::where('numero_lote', 'like', "MOV-{$año}-%")
                             ->orderBy('numero_lote', 'desc')
                             ->first();

        if ($ultimoNumero) {
            // Extraer el número y sumar 1
            preg_match('/MOV-\d{4}-(\d{4})/', $ultimoNumero->numero_lote, $matches);
            $numero = (int)($matches[1] ?? 0) + 1;
        } else {
            $numero = 1;
        }

        return sprintf('MOV-%d-%04d', $año, $numero);
    }

    public function getDescripcionMovimientoAttribute(): string
    {
        $de = $this->areaAnterior ? $this->areaAnterior->ubicacion_resumida : 'N/A';
        $a = $this->areaActual->ubicacion_resumida;
        
        return "De: {$de} → A: {$a}";
    }

    public function getTipoMovimientoAttribute(): string
    {
        return $this->area_anterior_id ? 'Traslado' : 'Asignación inicial';
    }

    public function getCantidadMobiliariosAttribute(): int
    {
        return $this->mobiliarios()->count();
    }

    // Scopes
    public function scopeSinVale($query)
    {
        return $query->where('vale_generado', false);
    }

    public function scopeConVale($query)
    {
        return $query->where('vale_generado', true);
    }

    public function scopeRecientes($query, int $dias = 30)
    {
        return $query->where('fecha_movimiento', '>=', now()->subDays($dias));
    }
}