<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auditoria;
use App\Models\AuditoriaItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AuditoriaController extends Controller
{
    public function generarValePDF(Auditoria $auditoria, AuditoriaItem $item)
    {
        // Cargar relaciones necesarias
        $item->load(['mobiliario', 'auditoria.ubicacion', 'auditoria.usuario']);
        
        $data = [
            'auditoria' => $auditoria,
            'item' => $item,
            'mobiliario' => $item->mobiliario,
        ];
        
        $pdf = Pdf::loadView('pdf.vale-auditoria', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "vale-auditoria-{$item->folio_vale}.pdf";
        
        return $pdf->download($filename);
    }
    
    public function generarReportePDF(Auditoria $auditoria)
    {
        // Cargar todas las relaciones necesarias
        $auditoria->load([
            'ubicacion',
            'usuario',
            'items.mobiliario',
            'itemsPresentes.mobiliario',
            'itemsAusentes.mobiliario',
            'itemsConVale.mobiliario',
        ]);
        
        $data = [
            'auditoria' => $auditoria,
        ];
        
        $pdf = Pdf::loadView('pdf.reporte-auditoria', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "reporte-auditoria-{$auditoria->id}-{$auditoria->fecha_inicio->format('Ymd')}.pdf";
        
        return $pdf->download($filename);
    }
}
