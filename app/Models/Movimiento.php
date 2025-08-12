<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'numero_movimiento',
        'mobiliario_id',
        'area_actual_id',
        'area_anterior_id',
        'fecha_movimiento',
        'se_entrega_con',
        'se_retira_con',
        'observacion',
        'usuario_id',
        'version',
        'vale_generado',
        'vale_id',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'version' => 'integer',
        'vale_generado' => 'boolean',
    ];

    // Boot method para generar número de movimiento automáticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($movimiento) {
            if (!$movimiento->numero_movimiento) {
                $movimiento->numero_movimiento = static::generarNumeroMovimiento();
            }
        });
    }

    // Relaciones
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class);
    }

    public function mobiliarios(): BelongsToMany
    {
        return $this->belongsToMany(Mobiliario::class, 'movimiento_mobiliario')
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

    public function vales(): HasMany
    {
        return $this->hasMany(Vale::class);
    }

    public function vale(): HasOne
    {
        return $this->hasOne(Vale::class, 'movimiento_id');
    }

    // Métodos auxiliares
    public static function generarNumeroMovimiento(): string
    {
        $año = now()->year;
        
        // Obtener el último número del año actual
        $ultimoNumero = static::where('numero_movimiento', 'like', "MOV-{$año}-%")
                             ->orderBy('numero_movimiento', 'desc')
                             ->first();

        if ($ultimoNumero) {
            // Extraer el número y sumar 1
            preg_match('/MOV-\d{4}-(\d{4})/', $ultimoNumero->numero_movimiento, $matches);
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
        return $this->mobiliarios()->count() ?: ($this->mobiliario_id ? 1 : 0);
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

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_movimiento', [$fechaInicio, $fechaFin]);
    }

    public function scopePorArea($query, int $areaId)
    {
        return $query->where('area_actual_id', $areaId);
    }

    // Crear movimiento con control de concurrencia
    public static function crearMovimiento(array $datos): self
    {
        return DB::transaction(function () use ($datos) {
            // Actualizar la localización del mobiliario
            $mobiliario = Mobiliario::find($datos['mobiliario_id']);
            
            if (!$mobiliario) {
                throw new \Exception('Mobiliario no encontrado');
            }

            // Verificar que el mobiliario no esté bloqueado por otro usuario
            if ($mobiliario->estaBloquead()) {
                throw new \Exception('El mobiliario está siendo editado por otro usuario');
            }

            // Crear el movimiento
            $movimiento = static::create($datos);

            // Actualizar la localización del mobiliario
            $mobiliario->actualizarConVersion([
                'localizacion_id' => $datos['area_actual_id']
            ]);

            return $movimiento;
        });
    }
}
