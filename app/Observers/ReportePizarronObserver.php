<?php

namespace App\Observers;

use App\Models\BitacoraReporte;
use App\Models\PersonalReportante;
use App\Models\ReportePizarron;

class ReportePizarronObserver
{
    public function updated(ReportePizarron $reporte): void
    {
        if (! $reporte->wasChanged('estado')) {
            return;
        }

        if ($reporte->estado !== 'completado') {
            return;
        }

        // Evitar duplicados
        if (BitacoraReporte::where('reporte_pizarron_id', $reporte->id)->exists()) {
            return;
        }

        // Numero de identificación del personal si existe
        $numeroId = null;
        if ($reporte->personal_reportante_id) {
            $personal = PersonalReportante::find($reporte->personal_reportante_id);
            $numeroId = $personal?->numero_empleado;
        }

        BitacoraReporte::create([
            'reporte_pizarron_id'  => $reporte->id,
            'nombre_personal'      => $reporte->reportante_nombre ?? '—',
            'numero_identificacion'=> $numeroId,
            'area_departamento'    => $reporte->reportante_servicio ?? '—',
            'fecha_reporte'        => $reporte->created_at->toDateString(),
            'hora_reporte'         => $reporte->created_at->format('H:i:s'),
            'mensaje_original'     => $reporte->descripcion_original ?? $reporte->descripcion,
            'acciones'             => [],
            'resultado'            => 'satisfactoria',
            'nombre_dispositivo'   => $reporte->equipo,
            'numero_serie'         => null,
            'atiende_nombre'       => null,
            'recibe_nombre'        => null,
        ]);
    }
}
