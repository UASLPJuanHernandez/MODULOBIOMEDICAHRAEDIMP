<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClasificacionBien extends Model
{
    use HasFactory;

    protected $table = 'clasificacion_bienes';

    protected $fillable = [
        'grupo',
        'subgrupo',
        'clase',
        'nombre_grupo',
        'descripcion_clase',
    ];

    protected $casts = [
        'grupo' => 'integer',
        'subgrupo' => 'integer',
        'clase' => 'integer',
    ];

    // Relaciones
    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    // Métodos auxiliares
    public function getCodigoCompletoAttribute(): string
    {
        return "{$this->grupo}.{$this->subgrupo}.{$this->clase}";
    }

    public function getDescripcionCompletaAttribute(): string
    {
        return "Grupo {$this->grupo} - {$this->nombre_grupo} | Clase {$this->clase}: {$this->descripcion_clase}";
    }

    // Scopes
    public function scopeByGrupo($query, int $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    public function scopeBySubgrupo($query, int $subgrupo)
    {
        return $query->where('subgrupo', $subgrupo);
    }
}
