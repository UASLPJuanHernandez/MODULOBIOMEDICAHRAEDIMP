<?php

namespace App\Services;

use App\Models\BitacoraReporte;
use App\Models\FirmaSolicitud;
use setasign\Fpdi\Fpdi;

class BitacoraOverlayService
{
    private string $template;

    public function __construct()
    {
        $this->template = storage_path('app/templates/solicitud_servicio.pdf');
    }

    /** Devuelve el PDF como string en memoria (para preview/stream). */
    public function stream(BitacoraReporte $bitacora): string
    {
        return $this->buildPdf($bitacora)->Output('S');
    }

    /** Guarda el PDF en disco y devuelve la ruta (para descarga). */
    public function generar(BitacoraReporte $bitacora): string
    {
        if (! is_dir(storage_path('app/bitacoras'))) {
            mkdir(storage_path('app/bitacoras'), 0755, true);
        }

        $ruta = storage_path('app/bitacoras/overlay_' . $bitacora->id . '_' . now()->format('Ymd_His') . '.pdf');
        $this->buildPdf($bitacora)->Output('F', $ruta);

        return $ruta;
    }

    private function buildPdf(BitacoraReporte $bitacora): Fpdi
    {
        $firmaSolicitud = FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('estado', 'firmado')
            ->latest()
            ->first();

        $pdf = new Fpdi();
        $pdf->AddPage('P', 'A4');
        $pdf->setSourceFile($this->template);
        $pdf->useTemplate($pdf->importPage(1));

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $fecha = \Carbon\Carbon::parse($bitacora->fecha_reporte)->format('d/m/Y');
        $hora  = substr($bitacora->hora_reporte ?? '', 0, 5) ?: '';

        // ── Fila 1: Unidad / Descripción / Fecha ──────────────────────────
        $this->escribir($pdf, 13,  47, 55,  $bitacora->area_departamento);
        $this->escribir($pdf, 70,  47, 82,  $bitacora->nombre_dispositivo ?: '');
        $this->escribir($pdf, 154, 47, 43,  $fecha . '  ' . $hora);

        // ── Fila 2: Marca / Modelo / N° Serie / N° Control ────────────────
        $this->escribir($pdf, 13,  73, 43,  $bitacora->marca ?: '');
        $this->escribir($pdf, 59,  73, 43,  $bitacora->modelo ?: '');
        $this->escribir($pdf, 107, 73, 43,  $bitacora->numero_serie ?: '');

        // ── Checkboxes SE SOLICITA ─────────────────────────────────────────
        // Correctivo marcado por defecto (col 2)
        $this->checkbox($pdf, 47,  99, true);   // CORRECTIVO ✓
        $this->checkbox($pdf, 24,  99, false);  // PREVENTIVO
        $this->checkbox($pdf, 70,  99, false);  // POR NO SER FUNCIONAL
        $this->checkbox($pdf, 93,  99, false);  // INSERVIBLE
        $this->checkbox($pdf, 116, 99, false);  // OBSOLETO
        $this->checkbox($pdf, 140, 99, false);  // A DISPOSICIÓN
        $this->checkbox($pdf, 163, 99, false);  // TRASPASO
        $this->checkbox($pdf, 186, 99, false);  // OTRO

        // ── Justificación / Observaciones ─────────────────────────────────
        $this->bloque($pdf, 13, 114, 184, $bitacora->mensaje_original ?? '', 40);

        // ── Finalizado ────────────────────────────────────────────────────
        $finalizado = in_array($bitacora->resultado, ['satisfactoria', 'parcial']);
        $this->checkbox($pdf, 166, 160, $finalizado);   // SI
        $this->checkbox($pdf, 179, 160, !$finalizado);  // NO

        if ($finalizado) {
            $this->escribir($pdf, 148, 167, 45, $fecha . '  ' . $hora);
        }

        // ── Firma: Solicita (izquierda) ───────────────────────────────────
        $this->escribir($pdf, 13, 258, 85, $bitacora->nombre_personal);

        if ($firmaSolicitud?->firma_data) {
            $raw = $firmaSolicitud->firma_data;

            if (str_starts_with($raw, '{')) {
                // Nuevo formato: {posicion: {page,x,y,w,h}, imagen: dataUrl}
                $decoded  = json_decode($raw, true);
                $imagen   = $decoded['imagen']   ?? '';
                $posicion = $decoded['posicion'] ?? null;

                if ($imagen) {
                    if ($posicion) {
                        // Convertir porcentajes del canvas a mm en A4 (210×297 mm)
                        $x = ($posicion['x'] / 100) * 210;
                        $y = ($posicion['y'] / 100) * 297;
                        $w = max(($posicion['w'] / 100) * 210, 10);
                        $h = max(($posicion['h'] / 100) * 297,  5);
                        $this->imagen($pdf, $imagen, $x, $y, $w, $h);
                    } else {
                        $this->imagen($pdf, $imagen, 20, 238, 60, 18);
                    }
                }
            } else {
                // Formato legado: data URL directo, posición fija
                $this->imagen($pdf, $raw, 20, 238, 60, 18);
            }
        }

        // ── Firma: Recibe Biomédica (derecha) ─────────────────────────────
        $this->escribir($pdf, 110, 258, 85, $bitacora->atiende_nombre ?: '');

        if ($bitacora->firma_ingeniero) {
            $raw = $bitacora->firma_ingeniero;
            $x = 120; $y = 238; $w = 60; $h = 18; // posición fija legado
            $imagenData = $raw;

            if (str_starts_with($raw, '{')) {
                $decoded    = json_decode($raw, true);
                $imagenData = $decoded['imagen'] ?? $raw;
                $pos        = $decoded['posicion'] ?? null;
                if ($pos) {
                    // Convertir porcentajes del canvas a mm en A4 (210×297 mm)
                    $x = ($pos['x'] / 100) * 210;
                    $y = ($pos['y'] / 100) * 297;
                    $w = max(($pos['w'] / 100) * 210, 10);
                    $h = max(($pos['h'] / 100) * 297,  5);
                }
            }

            $this->imagen($pdf, $imagenData, $x, $y, $w, $h);
        }

        return $pdf;
    }

    private function escribir(Fpdi $pdf, float $x, float $y, float $w, string $texto): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 5, $this->limpiar($texto), 0, 0, 'L');
    }

    private function bloque(Fpdi $pdf, float $x, float $y, float $w, string $texto, float $h): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, 5, $this->limpiar($texto), 0, 'L');
    }

    private function checkbox(Fpdi $pdf, float $x, float $y, bool $marcado): void
    {
        $pdf->SetFont('ZapfDingbats', '', 10);
        $pdf->SetXY($x - 2, $y - 2);
        $pdf->Cell(6, 5, $marcado ? chr(52) : '', 0, 0, 'C');
        $pdf->SetFont('Arial', '', 9);
    }

    private function imagen(Fpdi $pdf, string $dataUrl, float $x, float $y, float $w, float $h): void
    {
        if (! str_starts_with($dataUrl, 'data:')) {
            return;
        }

        $partes = explode(',', $dataUrl, 2);
        if (count($partes) !== 2) {
            return;
        }

        $meta    = $partes[0];
        $payload = $partes[1];

        // SVG charset-encoded usa rawurlencode, no base64
        $binario = str_contains($meta, 'base64') ? base64_decode($payload) : rawurldecode($payload);

        $ext = 'jpg';
        if (str_contains($meta, 'png')) $ext = 'png';
        if (str_contains($meta, 'svg')) $ext = 'svg';

        $tmp = tempnam(sys_get_temp_dir(), 'firma_') . '.' . $ext;
        file_put_contents($tmp, $binario);

        try {
            if ($ext === 'svg' && extension_loaded('imagick')) {
                $im = new \Imagick();
                $im->setBackgroundColor(new \ImagickPixel('none'));
                $im->readImageBlob($binario);
                $im->setImageFormat('png32');
                $pngTmp = tempnam(sys_get_temp_dir(), 'firma_') . '.png';
                file_put_contents($pngTmp, $im->getImageBlob());
                $im->clear();
                $pdf->Image($pngTmp, $x, $y, $w, $h, 'PNG');
                @unlink($pngTmp);
            } elseif ($ext !== 'svg') {
                $pdf->Image($tmp, $x, $y, $w, $h, strtoupper($ext));
            }
        } catch (\Throwable) {
            // Si la imagen no se puede renderizar, se omite silenciosamente
        } finally {
            @unlink($tmp);
        }
    }

    private function limpiar(string $texto): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto) ?: $texto;
    }
}
