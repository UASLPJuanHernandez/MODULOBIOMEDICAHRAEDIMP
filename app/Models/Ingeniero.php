<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingeniero extends Model
{
    protected $fillable = [
        'nombre',
        'cargo',
        'cedula_profesional',
        'email',
        'firma_svg',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
