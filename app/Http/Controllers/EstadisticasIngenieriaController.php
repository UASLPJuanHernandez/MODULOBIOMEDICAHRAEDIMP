<?php

namespace App\Http\Controllers;

use App\Filament\Pages\EstadisticasIngenieria;
use Barryvdh\DomPDF\Facade\Pdf;

class EstadisticasIngenieriaController extends Controller
{
    public function exportarPdf()
    {
        abort_unless(auth()->check(), 403);

        $page = new EstadisticasIngenieria();
        $d    = $page->getEstadisticasData();

        $fmtHoras = function ($h) {
            if ($h === null) return '—';
            if ($h < 1)  return round($h * 60) . ' min';
            if ($h < 24) return round($h, 1) . ' h';
            return round($h / 24, 1) . ' días';
        };

        $tiempoDias     = ($d['tiempoPromedioHoras'] ?? 0) > 0 ? round($d['tiempoPromedioHoras'] / 24, 1) : 0;
        $tiempoMtoDias  = ($d['tiempoMtoHoras'] ?? 0) > 0 ? round($d['tiempoMtoHoras'] / 24, 1) : 0;
        $totalAct       = array_sum(array_values($d['reportesPorIngenieroActivos']));
        $totalTodo      = array_sum(array_values($d['reportesPorIngenieroTotal']));
        $envioAreaLabel = $fmtHoras($d['tiempoEnvioAreaHoras']);
        $pctContrato    = $d['totalEquipos'] > 0 ? round(($d['conContrato'] / $d['totalEquipos']) * 100) : 0;
        $totalCalidad   = array_sum(array_values($d['calidad']));
        $promPorIng     = $d['ingenierosActivos'] > 0 ? round($totalTodo / $d['ingenierosActivos'], 1) : 0;

        $generadoEn  = now()->format('d/m/Y H:i');
        $generadoPor = auth()->user()?->name ?? 'Administrador';

        $pdf = Pdf::loadView('pdf.estadisticas-ingenieria', compact(
            'd', 'fmtHoras',
            'tiempoDias', 'tiempoMtoDias',
            'totalAct', 'totalTodo', 'envioAreaLabel',
            'pctContrato', 'totalCalidad', 'promPorIng',
            'generadoEn', 'generadoPor'
        ))->setPaper('letter', 'portrait');

        return $pdf->download('estadisticas-ingenieria-' . now()->format('Y-m-d') . '.pdf');
    }
}
