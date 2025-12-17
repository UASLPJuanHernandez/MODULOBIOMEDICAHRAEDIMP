<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoria extends Model
{
    protected $fillable = [
        'ubicacion_id',
        'usuario_id',
        'responsable_nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones_generales',
        'total_mobiliarios',
        'mobiliarios_presentes',
        'mobiliarios_ausentes',
        'vales_generados',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Localizacion::class, 'ubicacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AuditoriaItem::class);
    }

    public function itemsPresentes(): HasMany
    {
        return $this->hasMany(AuditoriaItem::class)->where('presente', true);
    }

    public function itemsAusentes(): HasMany
    {
        return $this->hasMany(AuditoriaItem::class)->where('presente', false);
    }

    public function itemsConVale(): HasMany
    {
        return $this->hasMany(AuditoriaItem::class)->where('requiere_vale', true);
    }

    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    public function estaEnProgreso(): bool
    {
        return $this->estado === 'en_progreso';
    }

    public function calcularEstadisticas(): void
    {
        $this->total_mobiliarios = $this->items()->count();
        $this->mobiliarios_presentes = $this->items()->where('presente', true)->count();
        $this->mobiliarios_ausentes = $this->items()->where('presente', false)->count();
        $this->vales_generados = $this->items()->where('requiere_vale', true)->count();
        $this->save();
    }
}
