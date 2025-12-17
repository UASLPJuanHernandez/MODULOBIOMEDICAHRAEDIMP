<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Mobiliario extends Model
{
    use HasFactory;

    protected $table = 'mobiliario';

    protected $fillable = [
        'numero_control',
        'clasificacion_bienes_id',
        'caracteristicas',
        'descripcion',
        'marca',
        'modelo',
        'numero_serie',
        'foto',
        'precio',
        'tipo_mobiliario_id',
        'localizacion_id',
        'proveedor_id',
        'metodo_adquisicion',
        'tiene_folio',
        'numero_folio',
        'estado_mobiliario',
        'tiene_accesorios',
        'descripcion_accesorios',
        'dado_de_baja',
        'fecha_baja',
        'motivo_baja',
        'depreciacion_registrada',
        'version',
        'locked_by',
        'locked_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'depreciacion_registrada' => 'decimal:2',
        'tiene_accesorios' => 'boolean',
        'tiene_folio' => 'boolean',
        'dado_de_baja' => 'boolean',
        'fecha_baja' => 'datetime',
        'version' => 'integer',
        'locked_at' => 'datetime',
    ];

    // Relaciones
    public function clasificacionBien(): BelongsTo
    {
        return $this->belongsTo(ClasificacionBien::class, 'clasificacion_bienes_id');
    }

    public function tipoMobiliario(): BelongsTo
    {
        return $this->belongsTo(TipoMobiliario::class);
    }

    public function localizacion(): BelongsTo
    {
        return $this->belongsTo(Localizacion::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function movimientosMultiples(): BelongsToMany
    {
        return $this->belongsToMany(Movimiento::class, 'movimiento_mobiliario')
                    ->withPivot('area_anterior_id')
                    ->withTimestamps();
    }

    public function ultimoMovimiento(): HasOne
    {
        return $this->hasOne(Movimiento::class)->latestOfMany();
    }

    public function ubicacionActual(): BelongsTo
    {
        return $this->belongsTo(Localizacion::class, 'localizacion_id');
    }

    public function ubicacionReal()
    {
        // Buscar el último movimiento (tanto individual como múltiple)
        $ultimoMovimientoIndividual = $this->movimientos()
            ->with('areaActual')
            ->orderBy('fecha_movimiento', 'desc')
            ->first();
            
        $ultimoMovimientoMultiple = $this->movimientosMultiples()
            ->with('areaActual')
            ->orderBy('fecha_movimiento', 'desc')
            ->first();
        
        // Determinar cuál es más reciente
        $ultimoMovimiento = null;
        
        if ($ultimoMovimientoIndividual && $ultimoMovimientoMultiple) {
            $ultimoMovimiento = $ultimoMovimientoIndividual->fecha_movimiento->greaterThan($ultimoMovimientoMultiple->fecha_movimiento)
                ? $ultimoMovimientoIndividual
                : $ultimoMovimientoMultiple;
        } elseif ($ultimoMovimientoIndividual) {
            $ultimoMovimiento = $ultimoMovimientoIndividual;
        } elseif ($ultimoMovimientoMultiple) {
            $ultimoMovimiento = $ultimoMovimientoMultiple;
        }
        
        if ($ultimoMovimiento && $ultimoMovimiento->areaActual) {
            return $ultimoMovimiento->areaActual;
        }
        
        // Si no tiene movimientos, usar la ubicación original
        return $this->localizacion;
    }

    public function tieneMovimientos(): bool
    {
        return $this->movimientos()->count() > 0;
    }

    public function ultimaFechaMovimiento(): ?Carbon
    {
        return $this->ultimoMovimiento?->fecha_movimiento;
    }

    public function resumenUbicacion(): string
    {
        $ubicacion = $this->ubicacionReal();
        if (!$ubicacion) {
            return 'Sin ubicación';
        }
        
        $movimientos = $this->movimientos()->count();
        $suffix = $movimientos > 0 ? " ({$movimientos} mov.)" : " (original)";
        
        return $ubicacion->ubicacion_resumida . $suffix;
    }

    public function vales(): HasMany
    {
        return $this->hasMany(Vale::class);
    }

    public function valesMultiples(): BelongsToMany
    {
        return $this->belongsToMany(Vale::class, 'vale_mobiliario');
    }

    public function ordenesServicio(): HasMany
    {
        return $this->hasMany(OrdenServicio::class);
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function mantenimientosOrdenados(): HasMany
    {
        return $this->hasMany(Mantenimiento::class)->orderBy('created_at', 'desc');
    }

    public function mantenimientosCompletados(): HasMany
    {
        return $this->hasMany(Mantenimiento::class)
            ->where('estado', 'completado')
            ->orderBy('fecha_completado', 'desc');
    }

    public function ultimoMantenimiento()
    {
        return $this->hasOne(Mantenimiento::class)
            ->where('estado', 'completado')
            ->latest('fecha_completado');
    }

    // Métodos de estadísticas
    public function totalMantenimientos(): int
    {
        return $this->mantenimientos()->count();
    }

    public function mantenimientosPendientes(): int
    {
        return $this->mantenimientos()->where('estado', 'pendiente')->count();
    }

    public function mantenimientosAceptados(): int
    {
        return $this->mantenimientos()->where('estado', 'aceptado')->count();
    }

    public function tieneMantenimientosActivos(): bool
    {
        return $this->mantenimientos()
            ->whereIn('estado', ['pendiente', 'aceptado'])
            ->exists();
    }

    public function diasSinMantenimiento(): ?int
    {
        $ultimoMantenimiento = $this->ultimoMantenimiento;
        
        if (!$ultimoMantenimiento) {
            return null;
        }
        
        return $ultimoMantenimiento->fecha_completado->diffInDays(now());
    }

    public function resumenMantenimientos(): array
    {
        $mantenimientos = $this->mantenimientos()->get();
        
        return [
            'total' => $mantenimientos->count(),
            'por_estado' => [
                'pendiente' => $mantenimientos->where('estado', 'pendiente')->count(),
                'aceptado' => $mantenimientos->where('estado', 'aceptado')->count(),
                'completado' => $mantenimientos->where('estado', 'completado')->count(),
                'rechazado' => $mantenimientos->where('estado', 'rechazado')->count(),
            ],
            'por_tipo' => [
                'interno' => $mantenimientos->where('tipo_mantenimiento', 'mantenimiento')->count(),
                'proveedor' => $mantenimientos->where('tipo_mantenimiento', 'proveedor')->count(),
            ],
            'ultimo_mantenimiento' => $this->ultimoMantenimiento?->fecha_completado,
            'dias_sin_mantenimiento' => $this->diasSinMantenimiento(),
            'tiene_activos' => $this->tieneMantenimientosActivos(),
            'con_vales' => $mantenimientos->whereNotNull('folio_vale')->count(),
        ];
    }

    public function usuarioBloqueo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usuarioEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mobiliario) {
            // Generar número de control automáticamente
            if (empty($mobiliario->numero_control)) {
                $mobiliario->numero_control = static::generarNumeroControl($mobiliario->tipo_mobiliario_id);
            }
            
            // Asignar usuario que crea el registro
            if (Auth::check()) {
                $mobiliario->created_by = Auth::id();
            }
        });

        static::updating(function ($mobiliario) {
            // Asignar usuario que actualiza el registro
            if (Auth::check()) {
                $mobiliario->updated_by = Auth::id();
            }
        });
    }

    // Métodos para control de concurrencia
    public function bloquear(): bool
    {
        $userId = Auth::id();
        $now = Carbon::now();

        // Verificar si ya está bloqueado por otro usuario
        if ($this->locked_by && $this->locked_by !== $userId) {
            // Verificar si el bloqueo ha expirado (30 minutos)
            if ($this->locked_at && $this->locked_at->diffInMinutes($now) < 30) {
                return false;
            }
        }

        // Bloquear el registro
        return $this->update([
            'locked_by' => $userId,
            'locked_at' => $now,
        ]);
    }

    public function desbloquear(): bool
    {
        return $this->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    public function estaBloquead(): bool
    {
        if (!$this->locked_by || !$this->locked_at) {
            return false;
        }

        // Verificar si el bloqueo ha expirado
        if ($this->locked_at->diffInMinutes(Carbon::now()) >= 30) {
            $this->desbloquear();
            return false;
        }

        return $this->locked_by !== Auth::id();
    }

    // Generación automática de número de control
    public static function generarNumeroControl(int $tipoMobiliarioId): string
    {
        $tipoMobiliario = TipoMobiliario::find($tipoMobiliarioId);
        if (!$tipoMobiliario) {
            throw new \Exception('Tipo de mobiliario no encontrado');
        }

        $prefijo = $tipoMobiliario->prefijo;
        
        // Obtener el último número consecutivo para este prefijo
        $ultimoRegistro = static::where('numero_control', 'like', $prefijo . '%')
            ->orderBy('numero_control', 'desc')
            ->first();

        $siguienteNumero = 1;
        if ($ultimoRegistro) {
            $ultimoNumero = (int) substr($ultimoRegistro->numero_control, strlen($prefijo));
            $siguienteNumero = $ultimoNumero + 1;
        }

        return $prefijo . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    // Generación de código QR
    public function generarQR(): string
    {
        $contenido = $this->numero_control . ' - ' . $this->descripcion;
        
        if ($this->tipoMobiliario->tipo === 'Equipo Médico') {
            $contenido = $this->numero_control . ' - ' . $this->marca . ' - ' . $this->numero_serie . ' - ' . $this->descripcion;
        }

        return $contenido;
    }

    // Métodos auxiliares
    public function getDescripcionCompletaAttribute(): string
    {
        return "{$this->numero_control} - {$this->marca} {$this->modelo} - {$this->descripcion}";
    }

    public function getValorActualAttribute(): float
    {
        return $this->precio - ($this->depreciacion_registrada ?? 0);
    }

    public function getUltimoMovimientoAttribute()
    {
        return $this->movimientos()->latest('fecha_movimiento')->first();
    }

    // Scopes
    public function scopeByTipo($query, string $tipo)
    {
        return $query->whereHas('tipoMobiliario', function (Builder $q) use ($tipo) {
            $q->where('tipo', $tipo);
        });
    }

    public function scopeByCategoria($query, string $categoria)
    {
        return $query->whereHas('tipoMobiliario', function (Builder $q) use ($categoria) {
            $q->where('categoria', $categoria);
        });
    }

    public function scopeByLocalizacion($query, int $localizacionId)
    {
        return $query->where('localizacion_id', $localizacionId);
    }

    public function scopeByMetodoAdquisicion($query, string $metodo)
    {
        return $query->where('metodo_adquisicion', $metodo);
    }

    public function scopeByGrupoClasificacion($query, int $grupo)
    {
        return $query->whereHas('clasificacionBien', function (Builder $q) use ($grupo) {
            $q->where('grupo', $grupo);
        });
    }

    public function scopeDisponibles($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('locked_by')
              ->orWhere('locked_at', '<', Carbon::now()->subMinutes(30));
        });
    }

    public function scopeActivos($query)
    {
        return $query->where('dado_de_baja', false);
    }

    public function scopeDadosDeBaja($query)
    {
        return $query->where('dado_de_baja', true);
    }

    // Versioning para control de concurrencia optimista
    public function actualizarConVersion(array $datos): bool
    {
        $versionActual = $this->version;
        
        return DB::transaction(function () use ($datos, $versionActual) {
            // Verificar que la versión no haya cambiado
            $registroActual = static::where('id', $this->id)
                ->where('version', $versionActual)
                ->first();

            if (!$registroActual) {
                throw new \Exception('El registro ha sido modificado por otro usuario. Por favor, recarga y vuelve a intentar.');
            }

            // Incrementar la versión
            $datos['version'] = $versionActual + 1;

            return $this->update($datos);
        });
    }
}
