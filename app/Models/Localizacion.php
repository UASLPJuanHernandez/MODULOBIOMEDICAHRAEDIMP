<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Localizacion extends Model
{
    use HasFactory;

    protected $table = 'localizacion';

    protected $fillable = [
        'direccion',
        'division',
        'sub_area',
        'ubicacion',
    ];

    protected $appends = [
        'nombre_area',
        'ubicacion_completa',
        'ubicacion_resumida',
    ];

    // Relaciones
    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    public function movimientosActuales(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'area_actual_id');
    }

    public function movimientosAnteriores(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'area_anterior_id');
    }

    // Métodos auxiliares
    public function getUbicacionCompletaAttribute(): string
    {
        return "{$this->direccion} - {$this->division} / {$this->sub_area} / {$this->ubicacion}";
    }

    public function getUbicacionResumidaAttribute(): string
    {
        return "{$this->division} - {$this->sub_area}";
    }

    public function getNombreAreaAttribute(): string
    {
        return "{$this->division} - {$this->sub_area}";
    }

    // Scopes
    public function scopeByDivision($query, string $division)
    {
        return $query->where('division', $division);
    }

    public function scopeBySubArea($query, string $subArea)
    {
        return $query->where('sub_area', $subArea);
    }
}
