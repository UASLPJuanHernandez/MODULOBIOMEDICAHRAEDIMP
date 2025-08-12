<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vale extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_vale',
        'mobiliario_id',
        'movimiento_id',
        'tipo_vale',
        'fecha_generacion',
        'responsable_entrega',
        'firma_entrega',
        'matricula_entrega',
        'responsable_recibe',
        'firma_recibe',
        'matricula_recibe',
        'observaciones',
        'archivo_pdf',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
    ];

    protected $appends = [
        'numero_vale_formateado',
        'cantidad_mobiliarios',
    ];

    // Relaciones
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class);
    }

    public function mobiliarios(): BelongsToMany
    {
        return $this->belongsToMany(Mobiliario::class, 'vale_mobiliario');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class);
    }

    // Métodos auxiliares
    public function getTipoValeFormateadoAttribute(): string
    {
        return match($this->tipo_vale) {
            'entrega' => 'Vale de Entrega',
            'retiro' => 'Vale de Retiro',
            'resguardo' => 'Vale de Resguardo',
            default => 'Vale de ' . ucfirst($this->tipo_vale)
        };
    }

    public function getNumeroValeAttribute(): string
    {
        // Si el campo numero_vale existe y tiene valor, usarlo
        if (!empty($this->attributes['numero_vale'])) {
            return $this->attributes['numero_vale'];
        }
        
        // Si no existe, generar uno basado en ID y tipo
        if ($this->id) {
            return 'V-' . str_pad($this->id, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(substr($this->tipo_vale ?? 'G', 0, 1));
        }
        
        return 'Pendiente';
    }

    public function getNumeroValeFormateadoAttribute(): string
    {
        return $this->getNumeroValeAttribute();
    }

    public function getCantidadMobiliariosAttribute(): int
    {
        // Si la relación ya está cargada, usar el conteo de la colección
        if ($this->relationLoaded('mobiliarios')) {
            return $this->mobiliarios->count();
        }
        
        // Si no está cargada, hacer la consulta
        return $this->mobiliarios()->count();
    }

    // Scopes
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_vale', $tipo);
    }

    public function scopeRecientes($query, int $dias = 30)
    {
        return $query->where('fecha_generacion', '>=', now()->subDays($dias));
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_generacion', [$fechaInicio, $fechaFin]);
    }

    // Métodos para generar número de vale automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vale) {
            if (empty($vale->numero_vale)) {
                $vale->numero_vale = static::generarNumeroVale($vale->tipo_vale);
            }
        });
    }

    public static function generarNumeroVale(string $tipo): string
    {
        $prefijo = match($tipo) {
            'entrega' => 'VE',
            'retiro' => 'VR',
            'resguardo' => 'VG',
            default => 'V'
        };
        
        $year = date('Y');
        $ultimoVale = static::where('numero_vale', 'like', "{$prefijo}-{$year}-%")
            ->orderBy('numero_vale', 'desc')
            ->first();

        $siguienteNumero = 1;
        if ($ultimoVale) {
            $partes = explode('-', $ultimoVale->numero_vale);
            if (count($partes) >= 3) {
                $siguienteNumero = (int) $partes[2] + 1;
            }
        }

        return "{$prefijo}-{$year}-" . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);
    }
}
