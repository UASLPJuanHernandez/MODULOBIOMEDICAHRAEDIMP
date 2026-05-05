<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingeniero extends Model
{
    protected $fillable = [
        'nombre',
        'cargo',
        'cedula_profesional',
        'email',
        'firma_svg',
        'foto',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Reportes donde este ingeniero es responsable (match por nombre)
    public function reportes(): HasMany
    {
        return $this->hasMany(ReportePizarron::class, 'responsable', 'nombre');
    }
}
