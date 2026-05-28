<?php

namespace App\Observers;

use App\Models\BitacoraReporte;
use App\Models\PersonalReportante;
use App\Models\ReportePizarron;
use App\Services\AuditService;

class ReportePizarronObserver
{
    public function created(ReportePizarron $reporte): void
    {
        AuditService::log('reporte', "Nuevo reporte: {$reporte->equipo} — {$reporte->ubicacion}", [
            'actor_tipo'     => 'personal',
            'actor_id'       => $reporte->personal_reportante_id ?? 0,
            'actor_nombre'   => $reporte->reportante_nombre ?? 'Sistema',
            'documento_tipo' => 'reporte',
            'documento_id'   => $reporte->id,
        ]);
    }

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

        $personal = $reporte->personal_reportante_id
            ? PersonalReportante::find($reporte->personal_reportante_id)
            : null;

        $numeroId = $personal?->numero_empleado;

        // Solo pre-seleccionar área si el reportante es Jefe de Servicio
        // (su area_jefe_servicio coincide con las opciones del Select).
        // Los demás usan campo libre que no coincide con el catálogo.
        $area = ($personal && $personal->es_jefe_servicio)
            ? $personal->area_jefe_servicio
            : null;

        BitacoraReporte::create([
            'reporte_pizarron_id'  => $reporte->id,
            'nombre_personal'      => $reporte->reportante_nombre ?? '—',
            'numero_identificacion'=> $numeroId,
            'area_departamento'    => $area,
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
