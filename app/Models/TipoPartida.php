<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPartida extends Model
{
    use HasFactory;

    protected $table = 'tipo_partida';

    protected $fillable = [
        'tipo_partida',
    ];

    // Relaciones
    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'partida_id');
    }
}
