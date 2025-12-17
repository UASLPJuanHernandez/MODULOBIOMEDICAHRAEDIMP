<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    protected $fillable = [
        'mobiliario_id',
        'fecha_programada',
        'motivo',
        'tipo_mantenimiento',
        'proveedor_nombre',
        'estado',
        'usuario_solicitante_id',
        'usuario_mantenimiento_id',
        'fecha_aceptacion',
        'fecha_completado',
        'observaciones',
        'folio_vale',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_aceptacion' => 'datetime',
        'fecha_completado' => 'datetime',
    ];

    // Relaciones
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class);
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_solicitante_id');
    }

    public function usuarioMantenimiento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_mantenimiento_id');
    }

    // Relaciones optimizadas
    public function mobiliarioConDetalles(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class, 'mobiliario_id')
            ->select(['id', 'numero_control', 'descripcion', 'marca', 'modelo']);
    }

    public function usuariosSistema()
    {
        return $this->belongsTo(User::class, 'usuario_solicitante_id')
            ->select(['id', 'name', 'email'])
            ->union(
                $this->belongsTo(User::class, 'usuario_mantenimiento_id')
                    ->select(['id', 'name', 'email'])
            );
    }

    // Métodos de estado
    public function aceptar($usuarioMantenimiento)
    {
        $this->update([
            'estado' => 'aceptado',
            'usuario_mantenimiento_id' => $usuarioMantenimiento,
            'fecha_aceptacion' => now(),
            'folio_vale' => $this->generarFolioVale(),
        ]);
    }

    public function completar($observaciones = null)
    {
        $this->update([
            'estado' => 'completado',
            'fecha_completado' => now(),
            'observaciones' => $observaciones,
        ]);
    }

    public function rechazar($observaciones = null)
    {
        $this->update([
            'estado' => 'rechazado',
            'observaciones' => $observaciones,
        ]);
    }

    private function generarFolioVale()
    {
        $fecha = now()->format('Ymd');
        $ultimo = static::where('folio_vale', 'LIKE', "MTO-{$fecha}-%")->count();
        $numero = str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
        
        return "MTO-{$fecha}-{$numero}";
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAceptados($query)
    {
        return $query->where('estado', 'aceptado');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }
}
