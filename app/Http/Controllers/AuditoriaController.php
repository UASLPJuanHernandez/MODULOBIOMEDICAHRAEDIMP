<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FirmaSolicitud;
use App\Models\InventarioEquipoHistorial;
use App\Models\PersonalReportante;
use App\Models\Registro;
use App\Models\ValeInventario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function exportarPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);
        set_time_limit(0);

        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $tipo  = $request->query('tipo');

        // ── Actividad (con todos los filtros) ────────────────────────────────
        $logQuery = AuditLog::query();
        if ($desde) $logQuery->whereDate('created_at', '>=', $desde);
        if ($hasta) $logQuery->whereDate('created_at', '<=', $hasta);
        if ($tipo)  $logQuery->where('tipo', $tipo);
        $logs = $logQuery->latest()->get();

        // Logs solo con filtro de fecha (para desglose por tipo y accesos)
        $logsConFecha = AuditLog::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest()->get();

        $accesos = $logsConFecha->where('tipo', 'acceso')->values();

        // ── Historial de equipos ──────────────────────────────────────────────
        $historial = InventarioEquipoHistorial::with('inventarioEquipo:id,numero_inventario,equipo,area')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest()->get();

        // ── Firmas (vales + solicitudes + registros) ──────────────────────────
        $fVale = ValeInventario::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->with('jefe:id,nombre,area_jefe_servicio')
            ->latest('firmado_at')
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','jefe_id','firmado_at','estado'])
            ->map(fn ($v) => [
                'tipo_doc'  => $v->tipo === 'entrega' ? 'Vale entrega' : 'Vale retiro',
                'documento' => ($v->equipo_nombre ?: '—') . ($v->numero_inventario ? " ({$v->numero_inventario})" : ''),
                'firmante'  => $v->jefe?->nombre ?? '—',
                'area'      => $v->area ?? '—',
                'fecha'     => $v->firmado_at,
            ]);

        $fSol = FirmaSolicitud::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->latest('firmado_at')->get()
            ->map(function ($s) {
                $jefe = PersonalReportante::find($s->personal_reportante_id);
                return [
                    'tipo_doc'  => 'Reporte',
                    'documento' => 'Reporte #' . $s->reporte_pizarron_id,
                    'firmante'  => $jefe?->nombre ?? '—',
                    'area'      => $jefe?->area_jefe_servicio ?? '—',
                    'fecha'     => $s->firmado_at,
                ];
            });

        $fReg = Registro::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->with(['jefe:id,nombre,area_jefe_servicio', 'formato:id,nombre'])
            ->latest('firmado_at')->get()
            ->map(fn ($r) => [
                'tipo_doc'  => 'Registro',
                'documento' => $r->formato?->nombre ?? 'Registro #' . $r->id,
                'firmante'  => $r->jefe?->nombre ?? '—',
                'area'      => $r->jefe?->area_jefe_servicio ?? '—',
                'fecha'     => $r->firmado_at,
            ]);

        $todasFirmas = $fVale->concat($fSol)->concat($fReg)
            ->sortByDesc('fecha')->values();

        // ── Vales ─────────────────────────────────────────────────────────────
        $vales = ValeInventario::with('jefe:id,nombre')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest()
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','usuario_nombre','jefe_id','estado','created_at','firmado_at']);

        // ── Usuarios ──────────────────────────────────────────────────────────
        $usuarios = PersonalReportante::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest()
            ->get(['id','nombre','servicio','es_jefe_servicio','area_jefe_servicio','estado','created_at']);

        // ── Totales de registros y reportes (para stats) ───────────────────────
        $totalRegistrosPeriodo = Registro::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        $totalReportesPeriodo = FirmaSolicitud::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        // ── Stats ─────────────────────────────────────────────────────────────
        $stats = [
            'total_firmas'       => $todasFirmas->count(),
            'usuarios_activos'   => PersonalReportante::where('estado', 'aprobado')
                ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
                ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
                ->count(),
            'pendientes'         => PersonalReportante::where('estado', 'pendiente')
                ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
                ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
                ->count(),

            'vales_firmados'     => $fVale->count(),
            'total_vales'        => $vales->count(),

            'registros_firmados' => $fReg->count(),
            'total_registros'    => $totalRegistrosPeriodo,

            'reportes_firmados'  => $fSol->count(),
            'total_reportes'     => $totalReportesPeriodo,

            'total_logs'         => $logsConFecha->count(),
            'acceso'             => $accesos->count(),
            'firma_log'          => $logsConFecha->where('tipo', 'firma')->count(),
            'equipo'             => $logsConFecha->where('tipo', 'equipo')->count(),
            'reporte'            => $logsConFecha->where('tipo', 'reporte')->count(),
            'usuario'            => $logsConFecha->where('tipo', 'usuario')->count(),
            'historial_equipos'  => $historial->count(),
        ];

        $generadoEn  = now()->format('d/m/Y H:i');
        $generadoPor = auth()->user()?->name ?? 'Administrador';

        $pdf = Pdf::loadView('pdf.auditoria-export', compact(
            'logs', 'historial', 'todasFirmas', 'vales', 'accesos', 'usuarios',
            'stats', 'desde', 'hasta', 'tipo',
            'generadoEn', 'generadoPor'
        ))->setPaper('letter', 'portrait');

        return $pdf->download('auditoria-' . now()->format('Y-m-d') . '.pdf');
    }
}
