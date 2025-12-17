<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use App\Services\PDFService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MantenimientoController extends Controller
{
    public function generarValePDF(Mantenimiento $mantenimiento)
    {
        // Cargar relaciones necesarias
        $mantenimiento->load(['mobiliario', 'usuarioSolicitante', 'usuarioMantenimiento']);
        
        // Obtener información de ubicación actual del mobiliario
        $ubicacionActual = $mantenimiento->mobiliario->ubicacionReal();
        
        $data = [
            'mantenimiento' => $mantenimiento,
            'mobiliario' => $mantenimiento->mobiliario,
            'ubicacion' => $ubicacionActual,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ];
        
        $pdf = Pdf::loadView('pdf.vale-mantenimiento', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "vale-mantenimiento-{$mantenimiento->folio_vale}.pdf";
        
        return $pdf->download($filename);
    }
}
