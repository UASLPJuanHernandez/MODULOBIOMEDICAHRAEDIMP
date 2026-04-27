<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraReporte extends Model
{
    protected $table = 'bitacoras_reporte';

    protected $fillable = [
        'reporte_pizarron_id',
        'nombre_personal',
        'numero_identificacion',
        'area_departamento',
        'fecha_reporte',
        'hora_reporte',
        'mensaje_original',
        'acciones',
        'resultado',
        'nombre_dispositivo',
        'numero_serie',
        'atiende_nombre',
        'recibe_nombre',
        'firma_ingeniero',
    ];

    protected $casts = [
        'acciones'      => 'array',
        'fecha_reporte' => 'date',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReportePizarron::class, 'reporte_pizarron_id');
    }
}
