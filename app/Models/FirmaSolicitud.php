<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaSolicitud extends Model
{
    protected $table = 'firma_solicitudes';

    protected $fillable = [
        'reporte_pizarron_id',
        'personal_reportante_id',
        'estado',
        'firmado_at',
        'firma_data',
    ];

    protected $casts = [
        'firmado_at' => 'datetime',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReportePizarron::class, 'reporte_pizarron_id');
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(PersonalReportante::class, 'personal_reportante_id');
    }
}
