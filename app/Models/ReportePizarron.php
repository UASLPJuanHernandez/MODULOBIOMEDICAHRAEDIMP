<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReportePizarron extends Model
{
    protected $table = 'reportes_pizarron';

    protected $fillable = [
        'titulo',
        'equipo',
        'ubicacion',
        'descripcion',
        'descripcion_original',
        'prioridad',
        'estado',
        'responsable',
        'minimizado',
        'concretado',
        'concretado_at',
        'personal_reportante_id',
        'reportante_nombre',
        'reportante_servicio',
    ];

    protected $casts = [
        'minimizado'    => 'boolean',
        'concretado'    => 'boolean',
        'concretado_at' => 'datetime',
    ];

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_curso']);
    }

    public function bitacora(): HasOne
    {
        return $this->hasOne(\App\Models\BitacoraReporte::class, 'reporte_pizarron_id');
    }

    public function firmaSolicitud(): HasOne
    {
        return $this->hasOne(\App\Models\FirmaSolicitud::class, 'reporte_pizarron_id');
    }
}
