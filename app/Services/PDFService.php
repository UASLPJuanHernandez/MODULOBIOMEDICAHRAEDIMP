<?php

namespace App\Services;

use App\Models\Vale;
use App\Models\OrdenServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PDFService
{
    /**
     * Generar PDF del vale de resguardo
     */
    public function generarValeResguardo(Vale $vale): string
    {
        try {
            // Cargar relaciones necesarias
            $vale->load(['mobiliario', 'mobiliarios', 'movimiento']);
            
            // Verificar que tenemos mobiliarios asociados
            $mobiliarios = $vale->mobiliarios;
            if ($mobiliarios->isEmpty() && $vale->mobiliario) {
                // Fallback para vales antiguos con un solo mobiliario
                $mobiliarios = collect([$vale->mobiliario]);
            }
            
            if ($mobiliarios->isEmpty()) {
                throw new \Exception("El vale no tiene mobiliarios asociados");
            }
            
            $data = [
                'vale' => $vale,
                'mobiliarios' => $mobiliarios,
                'mobiliario' => $mobiliarios->first(), // Para compatibilidad con template existente
                'fecha' => now()->format('d/m/Y'),
            ];

            Log::info('Generando PDF con datos:', [
                'vale_id' => $vale->id,
                'cantidad_mobiliarios' => $mobiliarios->count(),
                'numero_vale' => $vale->numero_vale,
            ]);

            $pdf = Pdf::loadView('pdfs.vale-resguardo-simple', $data);
            
            $fileName = 'vale_resguardo_' . str_replace(['-', ' ', '/', ''], '_', $vale->numero_vale) . '_' . now()->format('Y-m-d') . '.pdf';
            $filePath = 'pdfs/vales/' . $fileName;
            
            // Asegurar que el directorio existe
            $directory = storage_path('app/pdfs/vales');
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
                Log::info('Directorio creado: ' . $directory);
            }
            
            Log::info('Intentando guardar PDF en: ' . $filePath);
            
            // Generar el contenido del PDF
            $pdfContent = $pdf->output();
            
            // Verificar que el contenido se generó
            if (empty($pdfContent)) {
                throw new \Exception("El contenido del PDF está vacío");
            }
            
            Log::info('PDF generado, tamaño: ' . strlen($pdfContent) . ' bytes');
            
            // Guardar el archivo
            $saved = Storage::put($filePath, $pdfContent);
            
            if (!$saved) {
                throw new \Exception("Error al guardar el archivo con Storage::put()");
            }
            
            $fullPath = storage_path('app/' . $filePath);
            
            Log::info('Archivo guardado, verificando existencia en: ' . $fullPath);
            
            // Verificar que el archivo se creó correctamente
            if (!file_exists($fullPath)) {
                // Intentar método alternativo
                Log::info('Archivo no encontrado, intentando método alternativo');
                file_put_contents($fullPath, $pdfContent);
                
                if (!file_exists($fullPath)) {
                    throw new \Exception("No se pudo crear el archivo PDF en: {$fullPath}");
                }
            }
            
            Log::info('PDF generado exitosamente: ' . $fullPath);
            return $fullPath;
            
        } catch (\Exception $e) {
            Log::error('Error generando vale de resguardo: ' . $e->getMessage(), [
                'vale_id' => $vale->id ?? 'null',
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al generar el PDF del vale: ' . $e->getMessage());
        }
    }

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
