<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenServicio extends Model
{
    use HasFactory;

    protected $table = 'ordenes_servicio';

    protected $fillable = [
        'numero_orden',
        'fecha_orden',
        'mobiliario_id',
        'proveedor_servicio',
        'area_ubicacion',
        'nombre_equipo',
        'descripcion_falla',
        'trabajo_realizado',
        'componentes_cambiados',
        'componentes_agregados',
        'tipo_mantenimiento',
        'usuario_id',
        'estado',
        'archivo_pdf',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
    ];

    // Relaciones
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orden) {
            // Generar número de orden automáticamente
            if (empty($orden->numero_orden)) {
                $orden->numero_orden = static::generarNumeroOrden();
            }
        });
    }

    // Generación automática de número de orden
    public static function generarNumeroOrden(): string
    {
        $year = date('Y');
        $ultimaOrden = static::where('numero_orden', 'like', "OS-{$year}-%")
            ->orderBy('numero_orden', 'desc')
            ->first();

        $siguienteNumero = 1;
        if ($ultimaOrden) {
            $ultimoNumero = (int) substr($ultimaOrden->numero_orden, -6);
            $siguienteNumero = $ultimoNumero + 1;
        }

        return 'OS-' . $year . '-' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    // Métodos auxiliares
    public function getEstadoFormateadoAttribute(): string
    {
        $estados = [
            'Pendiente' => 'Pendiente',
            'En Proceso' => 'En Proceso',
            'Completada' => 'Completada',
            'Cancelada' => 'Cancelada'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    public function getColorEstadoAttribute(): string
    {
        $colores = [
            'Pendiente' => 'warning',
            'En Proceso' => 'info',
            'Completada' => 'success',
            'Cancelada' => 'danger'
        ];

        return $colores[$this->estado] ?? 'secondary';
    }

    public function getTipoMantenimientoFormateadoAttribute(): string
    {
        return ucfirst($this->tipo_mantenimiento);
    }

    // Scopes
    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorTipoMantenimiento($query, string $tipo)
    {
        return $query->where('tipo_mantenimiento', $tipo);
    }

    public function scopePorProveedor($query, string $proveedor)
    {
        return $query->where('proveedor_servicio', 'like', "%{$proveedor}%");
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_orden', [$fechaInicio, $fechaFin]);
    }

    public function scopeRecientes($query, int $dias = 30)
    {
        return $query->where('fecha_orden', '>=', now()->subDays($dias));
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'En Proceso');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completada');
    }
}
