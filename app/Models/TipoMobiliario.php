<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoMobiliario extends Model
{
    use HasFactory;

    protected $table = 'tipo_mobiliario';

    protected $fillable = [
        'tipo',
        'categoria',
        'prefijo',
    ];

    // Relaciones
    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    // Métodos auxiliares
    public function getDescripcionCompletaAttribute(): string
    {
        return "{$this->tipo} - {$this->categoria} ({$this->prefijo})";
    }

    // Scopes
    public function scopeByTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeByCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}
