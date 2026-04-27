<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Mobiliario extends Model
{
    use HasFactory;

    protected $table = 'mobiliario';

    // Bloquear SOLO las columnas del CSV que no existen en la tabla
    // Cuando usas $guarded, NO uses $fillable
    protected $guarded = [
        'clave_bien', 'nombre_bien', 'grupo', 'subgrupo', 'clase',
        'color', 'no_factura', 'proveedor', 
        'fecha_factura', 'fecha_registro', 'ubicacion',
        'procedencia', 'direccion', 'division', 'departamento',
        'responsable', 'clave_empleado', 'puesto', 'valor'
    ];

    // protected $fillable = [
    //     'numero_control',
    //     'numero_inventario',
    //     'clasificacion_bienes_id',
    //     'caracteristicas',
    //     'descripcion',
    //     'marca',
    //     'modelo',
    //     'numero_serie',
    //     'foto',
    //     'precio',
    //     'tipo_mobiliario_id',
    //     'localizacion_id',
    //     'proveedor_id',
    //     'metodo_adquisicion',
    //     'donante',
    //     'tiene_folio',
    //     'numero_folio',
    //     'estado_mobiliario',
    //     'tiene_accesorios',
    //     'descripcion_accesorios',
    //     'dado_de_baja',
    //     'fecha_baja',
    //     'motivo_baja',
    //     'depreciacion_registrada',
    //     'responsable_actual',
    //     'matricula_responsable',
    //     'puesto_responsable',
    //     'version',
    //     'locked_by',
    //     'locked_at',
    //     'created_by',
    //     'updated_by',
    // ];

    protected $casts = [
        'precio' => 'decimal:2',
        'depreciacion_registrada' => 'decimal:2',
        'tiene_accesorios' => 'boolean',
        'tiene_folio' => 'boolean',
        'dado_de_baja' => 'boolean',
        // 'fecha_baja' => 'datetime', // Comentado para permitir importación - se maneja en procesarEstadoMobiliario()
        'version' => 'integer',
        'locked_at' => 'datetime',
    ];

    // Relaciones
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
            $numero_serie = $this->numero_serie ?: 'S/N';
            $contenido = $this->numero_control . ' - ' . $this->marca . ' - ' . $numero_serie . ' - ' . $this->descripcion;
        }

        $qrCode = QrCode::create($contenido)
            ->setSize(300)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return $result->getString();
    }
    
    // Método para obtener el QR como Data URI (base64) para mostrar en HTML
    public function getQrDataUri(): string
    {
        $contenido = $this->numero_control . ' - ' . $this->descripcion;
        
        if ($this->tipoMobiliario->tipo === 'Equipo Médico') {
            $numero_serie = $this->numero_serie ?: 'S/N';
            $contenido = $this->numero_control . ' - ' . $this->marca . ' - ' . $numero_serie . ' - ' . $this->descripcion;
        }

        $qrCode = QrCode::create($contenido)
            ->setSize(200)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return $result->getDataUri();
    }

    // Scopes y métodos auxiliares
    public function getDescripcionCompletaAttribute(): string
    {
        return "{$this->numero_control} - {$this->marca} {$this->modelo} - {$this->descripcion}";
    }

    public function getValorActualAttribute(): float
    {
        return $this->precio - ($this->depreciacion_registrada ?? 0);
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
