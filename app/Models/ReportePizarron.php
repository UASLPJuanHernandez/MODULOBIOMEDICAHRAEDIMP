<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportePizarron extends Model
{
    protected $table = 'reportes_pizarron';

    protected $fillable = [
        'titulo',
        'equipo',
        'ubicacion',
        'descripcion',
        'prioridad',
        'estado',
        'responsable',
        'minimizado',
    ];

    protected $casts = [
        'minimizado' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_curso']);
    }
}
