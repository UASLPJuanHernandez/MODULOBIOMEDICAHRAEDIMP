<?php

namespace App\Services;

use App\Models\OrdenServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PDFService
{
    /**
     * Generar PDF de la orden de servicio
     */
    public function generarOrdenServicio(OrdenServicio $orden): string
    {
        $data = [
            'orden' => $orden,
            'mobiliario' => $orden->mobiliario,
            'fecha' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('pdfs.orden-servicio', $data);
        
        $fileName = 'orden_servicio_' . $orden->numero_orden . '_' . now()->format('Y-m-d') . '.pdf';
        $filePath = 'pdfs/ordenes/' . $fileName;
        
        Storage::put($filePath, $pdf->output());
        
        return storage_path('app/' . $filePath);
    }

    /**
     * Generar etiqueta QR para mobiliario
     */
    public function generarEtiquetaQR($mobiliario): string
    {
        $data = [
            'mobiliario' => $mobiliario,
            'qr_code' => $mobiliario->qr_code,
            'fecha' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('pdfs.etiqueta-qr', $data);
        
        $fileName = 'etiqueta_qr_' . $mobiliario->numero_control . '_' . now()->format('Y-m-d') . '.pdf';
        $filePath = 'pdfs/etiquetas/' . $fileName;
        
        Storage::put($filePath, $pdf->output());
        
        return storage_path('app/' . $filePath);
    }
}
