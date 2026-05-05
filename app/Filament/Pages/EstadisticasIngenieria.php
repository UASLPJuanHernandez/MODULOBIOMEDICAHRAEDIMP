<?php

namespace App\Filament\Pages;

use App\Models\BitacoraReporte;
use App\Models\Ingeniero;
use App\Models\InventarioEquipo;
use App\Models\Mantenimiento;
use App\Models\ReportePizarron;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class EstadisticasIngenieria extends Page
{
    protected static string $view = 'filament.pages.estadisticas-ingenieria';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Estadísticas del área';
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getEstadisticasData(): array
    {
        // ── Equipos ──────────────────────────────────────────────────────────
        $totalEquipos = InventarioEquipo::count();

        $estatusRaw      = InventarioEquipo::selectRaw('estatus, count(*) as total')
            ->whereNotNull('estatus')->groupBy('estatus')->orderByDesc('total')
            ->pluck('total', 'estatus');

        $estatusAgrupado = ['Funcionamiento Completo' => 0, 'Funciona Parcialmente' => 0, 'Fuera de Servicio' => 0, 'Otro' => 0];
        foreach ($estatusRaw as $est => $cnt) {
            $norm = strtoupper(trim($est));
            if (str_contains($norm, 'COMPLETO') || str_contains($norm, 'FUNCIONANDO'))      $estatusAgrupado['Funcionamiento Completo'] += $cnt;
            elseif (str_contains($norm, 'PARCIAL'))                                          $estatusAgrupado['Funciona Parcialmente']   += $cnt;
            elseif (str_contains($norm, 'FUERA') || str_contains($norm, 'DISFUNCIONAL'))    $estatusAgrupado['Fuera de Servicio']       += $cnt;
            else                                                                              $estatusAgrupado['Otro']                    += $cnt;
        }

        $condicionesEquipos = InventarioEquipo::selectRaw('condiciones, count(*) as total')
            ->whereNotNull('condiciones')->groupBy('condiciones')->orderByDesc('total')
            ->pluck('total', 'condiciones')->toArray();

        $equiposPorArea = InventarioEquipo::selectRaw('area, count(*) as total')
            ->whereNotNull('area')->groupBy('area')->orderByDesc('total')->limit(10)
            ->pluck('total', 'area')->toArray();

        $conContrato = InventarioEquipo::where('tiene_contrato', true)->count();
        $sinContrato = $totalEquipos - $conContrato;
        $conGarantia = InventarioEquipo::where('garantia', true)->count();
        $finVidaUtil = InventarioEquipo::where('fin_vida_util', true)->count();
        $proximosMp  = InventarioEquipo::whereNotNull('siguiente_mp')
            ->where('siguiente_mp', '>=', now()->toDateString())
            ->where('siguiente_mp', '<=', now()->addDays(30)->toDateString())->count();
        $mpVencidos  = InventarioEquipo::whereNotNull('siguiente_mp')
            ->where('siguiente_mp', '<', now()->toDateString())->count();

        $tipoPropiedadEquipos = InventarioEquipo::selectRaw('propiedad, count(*) as total')
            ->whereNotNull('propiedad')->groupBy('propiedad')->orderByDesc('total')
            ->pluck('total', 'propiedad')->toArray();

        // ── Reportes ─────────────────────────────────────────────────────────
        $totalReportes       = ReportePizarron::count();
        $reportesPendiente   = ReportePizarron::where('estado', 'pendiente')->count();
        $reportesEnCurso     = ReportePizarron::where('estado', 'en_curso')->count();
        $reportesCompletados = ReportePizarron::where('estado', 'completado')->count();
        $reportesConcretados = ReportePizarron::where('concretado', true)->count();
        $tasaConcrecion      = $totalReportes > 0 ? round(($reportesConcretados / $totalReportes) * 100, 1) : 0;

        $reportesPorIngenieroActivos = ReportePizarron::selectRaw('responsable, count(*) as total')
            ->whereNotNull('responsable')->whereIn('estado', ['pendiente', 'en_curso'])
            ->groupBy('responsable')->orderByDesc('total')
            ->pluck('total', 'responsable')->toArray();

        $reportesPorIngenieroTotal = ReportePizarron::selectRaw('responsable, count(*) as total')
            ->whereNotNull('responsable')->groupBy('responsable')->orderByDesc('total')
            ->pluck('total', 'responsable')->toArray();

        $reportesPorMesRaw = ReportePizarron::where('created_at', '>=', now()->subMonths(6))
            ->get(['created_at'])
            ->groupBy(fn ($r) => Carbon::parse($r->created_at)->format('Y-m'))
            ->map->count()->toArray();

        $mesesLabels = [];
        $mesesData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha         = now()->subMonths($i);
            $mesesLabels[] = $fecha->locale('es')->isoFormat('MMM YYYY');
            $mesesData[]   = $reportesPorMesRaw[$fecha->format('Y-m')] ?? 0;
        }

        $tiempoPromedioHoras = ReportePizarron::whereNotNull('concretado_at')->get()
            ->map(fn ($r) => Carbon::parse($r->created_at)->diffInHours(Carbon::parse($r->concretado_at)))->avg();

        // ── Calidad (Bitácoras) ───────────────────────────────────────────────
        $totalBitacoras = BitacoraReporte::count();
        $calidadRaw     = BitacoraReporte::selectRaw('resultado, count(*) as total')
            ->whereNotNull('resultado')->groupBy('resultado')
            ->pluck('total', 'resultado')->toArray();

        $calidad = [
            'satisfactoria'    => $calidadRaw['satisfactoria']    ?? 0,
            'parcial'          => $calidadRaw['parcial']          ?? 0,
            'no_satisfactoria' => $calidadRaw['no_satisfactoria'] ?? 0,
        ];
        $tasaSatisfaccion = $totalBitacoras > 0
            ? round(($calidad['satisfactoria'] / $totalBitacoras) * 100, 1) : 0;

        $reportesPorAreaDep = BitacoraReporte::selectRaw('area_departamento, count(*) as total')
            ->whereNotNull('area_departamento')->groupBy('area_departamento')
            ->orderByDesc('total')->limit(10)
            ->pluck('total', 'area_departamento')->toArray();

        // ── Mantenimientos ────────────────────────────────────────────────────
        $totalMantenimientos = Mantenimiento::count();
        $mtosPendientes      = Mantenimiento::where('estado', 'pendiente')->count();
        $mtosAceptados       = Mantenimiento::where('estado', 'aceptado')->count();
        $mtosCompletados     = Mantenimiento::where('estado', 'completado')->count();
        $mtosRechazados      = Mantenimiento::where('estado', 'rechazado')->count();
        $tiempoMtoHoras      = Mantenimiento::whereNotNull('fecha_completado')->get()
            ->map(fn ($m) => Carbon::parse($m->created_at)->diffInHours(Carbon::parse($m->fecha_completado)))->avg();

        // ── Tiempo para enviar a firma (área general) ─────────────────────────
        $tiempoEnvioAreaHoras = BitacoraReporte::join('reportes_pizarron', 'bitacoras_reporte.reporte_pizarron_id', '=', 'reportes_pizarron.id')
            ->selectRaw('bitacoras_reporte.created_at as bit_at, reportes_pizarron.created_at as rep_at')
            ->get()
            ->map(fn ($r) => Carbon::parse($r->rep_at)->diffInHours(Carbon::parse($r->bit_at)))
            ->filter(fn ($h) => $h >= 0)
            ->avg();
        $tiempoEnvioAreaHoras = $tiempoEnvioAreaHoras !== null ? round($tiempoEnvioAreaHoras, 1) : null;

        // ── Ingenieros ────────────────────────────────────────────────────────
        $totalIngenieros   = Ingeniero::count();
        $ingenierosActivos = Ingeniero::where('activo', true)->count();

        $ingenierosMetrics = Ingeniero::where('activo', true)->orderBy('nombre')->get()
            ->map(function ($ing) {
                $total      = ReportePizarron::where('responsable', $ing->nombre)->count();
                $pendientes = ReportePizarron::where('responsable', $ing->nombre)->where('estado', 'pendiente')->count();
                $en_curso   = ReportePizarron::where('responsable', $ing->nombre)->where('estado', 'en_curso')->count();
                $concretados = ReportePizarron::where('responsable', $ing->nombre)->where('concretado', true)->count();
                $bitacoras  = BitacoraReporte::where('atiende_nombre', 'like', "%{$ing->nombre}%")->count();
                $este_mes   = ReportePizarron::where('responsable', $ing->nombre)
                    ->where('created_at', '>=', now()->startOfMonth())->count();
                $ultimo_at  = ReportePizarron::where('responsable', $ing->nombre)->max('created_at');

                $tiempos = ReportePizarron::where('responsable', $ing->nombre)
                    ->whereNotNull('concretado_at')->get()
                    ->map(fn ($r) => Carbon::parse($r->created_at)->diffInHours(Carbon::parse($r->concretado_at)));
                $tiempo_prom_h = $tiempos->avg() ?? 0;

                $tiemposEnvio = BitacoraReporte::join('reportes_pizarron', 'bitacoras_reporte.reporte_pizarron_id', '=', 'reportes_pizarron.id')
                    ->where('reportes_pizarron.responsable', $ing->nombre)
                    ->selectRaw('bitacoras_reporte.created_at as bit_at, reportes_pizarron.created_at as rep_at')
                    ->get()
                    ->map(fn ($r) => Carbon::parse($r->rep_at)->diffInHours(Carbon::parse($r->bit_at)))
                    ->filter(fn ($h) => $h >= 0);
                $tiempo_envio_h = $tiemposEnvio->count() > 0 ? round($tiemposEnvio->avg(), 1) : null;

                $fotoUrl = $ing->foto
                    ? (str_starts_with($ing->foto, 'data:') ? $ing->foto : Storage::url($ing->foto))
                    : null;

                return [
                    'nombre'        => $ing->nombre,
                    'cargo'         => $ing->cargo ?? '—',
                    'foto'          => $fotoUrl,
                    'total'         => $total,
                    'pendientes'    => $pendientes,
                    'en_curso'      => $en_curso,
                    'activos'       => $pendientes + $en_curso,
                    'completados'   => ReportePizarron::where('responsable', $ing->nombre)->where('estado', 'completado')->count(),
                    'concretados'   => $concretados,
                    'bitacoras'     => $bitacoras,
                    'este_mes'      => $este_mes,
                    'ultimo_reporte' => $ultimo_at ? Carbon::parse($ultimo_at)->format('d/m/Y') : null,
                    'tiempo_prom_dias'  => $tiempo_prom_h > 0 ? round($tiempo_prom_h / 24, 1) : null,
                    'tasa_concrecion'   => $total > 0 ? round(($concretados / $total) * 100) : 0,
                    'tiempo_envio_h'    => $tiempo_envio_h,
                ];
            })->toArray();

        return compact(
            'totalEquipos', 'estatusAgrupado', 'condicionesEquipos', 'equiposPorArea',
            'conContrato', 'sinContrato', 'conGarantia', 'finVidaUtil', 'proximosMp', 'mpVencidos',
            'tipoPropiedadEquipos',
            'totalReportes', 'reportesPendiente', 'reportesEnCurso', 'reportesCompletados',
            'reportesConcretados', 'tasaConcrecion',
            'reportesPorIngenieroActivos', 'reportesPorIngenieroTotal',
            'mesesLabels', 'mesesData', 'tiempoPromedioHoras',
            'totalBitacoras', 'calidad', 'tasaSatisfaccion', 'reportesPorAreaDep',
            'totalMantenimientos', 'mtosPendientes', 'mtosAceptados', 'mtosCompletados', 'mtosRechazados',
            'tiempoMtoHoras',
            'totalIngenieros', 'ingenierosActivos', 'ingenierosMetrics',
            'tiempoEnvioAreaHoras'
        );
    }
}
