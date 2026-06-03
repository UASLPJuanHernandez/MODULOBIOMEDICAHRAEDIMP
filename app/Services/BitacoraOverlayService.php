<?php

namespace App\Services;

use App\Models\BitacoraReporte;
use App\Models\FirmaSolicitud;
use setasign\Fpdi\Fpdi;

// Subclase que agrega Ellipse usando curvas Bézier (FPDF no lo incluye de base)
class FpdiEx extends Fpdi
{
    public function Ellipse(float $cx, float $cy, float $rx, float $ry): void
    {
        $k     = $this->k;
        $h     = $this->h;
        $kappa = 0.5523;

        $this->_out(sprintf(
            '%.2F %.2F m '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c S',
            // punto de inicio (derecha)
            ($cx + $rx) * $k,                    ($h - $cy) * $k,
            // arco cuadrante superior-derecho
            ($cx + $rx) * $k,                    ($h - ($cy - $kappa * $ry)) * $k,
            ($cx + $kappa * $rx) * $k,           ($h - ($cy - $ry)) * $k,
            $cx * $k,                            ($h - ($cy - $ry)) * $k,
            // arco cuadrante superior-izquierdo
            ($cx - $kappa * $rx) * $k,           ($h - ($cy - $ry)) * $k,
            ($cx - $rx) * $k,                    ($h - ($cy - $kappa * $ry)) * $k,
            ($cx - $rx) * $k,                    ($h - $cy) * $k,
            // arco cuadrante inferior-izquierdo
            ($cx - $rx) * $k,                    ($h - ($cy + $kappa * $ry)) * $k,
            ($cx - $kappa * $rx) * $k,           ($h - ($cy + $ry)) * $k,
            $cx * $k,                            ($h - ($cy + $ry)) * $k,
            // arco cuadrante inferior-derecho
            ($cx + $kappa * $rx) * $k,           ($h - ($cy + $ry)) * $k,
            ($cx + $rx) * $k,                    ($h - ($cy + $kappa * $ry)) * $k,
            ($cx + $rx) * $k,                    ($h - $cy) * $k
        ));
    }
}

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

    private function buildPdf(BitacoraReporte $bitacora): FpdiEx
    {
        $firmaSolicitud = FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('estado', 'firmado')
            ->latest()
            ->first();

        // Dimensiones del formulario 144: 216.49 mm × 137.87 mm
        $pdf = new FpdiEx();
        $pdf->SetMargins(0, 0, 0);
        $pdf->AddPage('L', [137.87, 216.49]);
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($this->template);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, 0, 0, 216.49, 137.87);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $fecha = \Carbon\Carbon::parse($bitacora->fecha_reporte)->format('d/m/Y');
        $hora  = substr($bitacora->hora_reporte ?? '', 0, 5) ?: '';

        // ── Fila 1: Unidad / Descripción / Fecha y Hora  (y_top≈28, h≈22 → y=37) ─
        $this->escribir($pdf,   1, 37,  64, $bitacora->area_departamento);
        $this->escribir($pdf,  65, 37,  94, $bitacora->nombre_dispositivo ?: '');
        $this->escribir($pdf, 159, 37,  56, $fecha . '  ' . $hora);

        // ── Fila 2: Marca / Modelo / N° Serie / N° Control ───────────────────
        $this->escribir($pdf,   1, 49,  53, $bitacora->marca ?: '');
        $this->escribir($pdf,  54, 49,  55, $bitacora->modelo ?: '');
        $this->escribir($pdf, 109, 49,  55, $bitacora->numero_serie ?: '');
        $this->escribir($pdf, 164, 49,  51, $bitacora->numero_control ?? '');

        // ── Checkboxes SE SOLICITA — óvalo por columna ────────────────────
        $esPreventivo   = $bitacora->tipo_servicio === 'preventivo';
        $esCorrectivo   = $bitacora->tipo_servicio === 'correctivo';
        $tipoBaja       = $bitacora->tipo_baja ?? '';

        $this->ovalo($pdf,  22, 76, $esPreventivo,              18);  // PREVENTIVO
        $this->ovalo($pdf,  62, 76, $esCorrectivo,              15);  // CORRECTIVO
        $this->ovalo($pdf,  96, 73, $tipoBaja === 'no_funcional', 9); // POR NO SER FUNCIONAL
        $this->ovalo($pdf, 119, 76, $tipoBaja === 'inservible',   9); // INSERVIBLE
        $this->ovalo($pdf, 141, 76, $tipoBaja === 'obsoleto',     7); // OBSOLETO
        $this->ovalo($pdf, 160, 76, $tipoBaja === 'disposicion',  6); // A DISPOSICIÓN
        $this->ovalo($pdf, 176, 76, $tipoBaja === 'traspaso',     6); // TRASPASO
        $this->ovalo($pdf, 199, 76, $tipoBaja === 'otro',         9); // OTRO

        // ── Justificación / Observaciones ────────────────────────────────
        $textoJustificacion = $bitacora->justificacion ?? $bitacora->mensaje_original ?? '';
        $this->bloque($pdf, 9, 82, 197, $textoJustificacion, 25);

        // ── Finalizado ────────────────────────────────────────────────────
        $finalizado = in_array($bitacora->resultado, ['satisfactoria', 'parcial']);
        $this->checkbox($pdf, 171, 110, $finalizado);
        $this->checkbox($pdf, 184, 110, !$finalizado);

        if ($finalizado) {
            $this->escribir($pdf, 163, 113, 50, $fecha . '  ' . $hora);
        }

        // ── Firma: Solicita (izquierda) ───────────────────────────────────
        $this->escribir($pdf, 12, 122, 88, $bitacora->nombre_personal);

        if ($firmaSolicitud?->firma_data) {
            $raw = $firmaSolicitud->firma_data;

            if (str_starts_with($raw, '{')) {
                $decoded  = json_decode($raw, true);
                $imagen   = $decoded['imagen']   ?? '';
                $posicion = $decoded['posicion'] ?? null;

                if ($imagen) {
                    if ($posicion) {
                        // Convertir porcentajes del canvas a mm (216.49×137.87 mm)
                        $x = ($posicion['x'] / 100) * 216.49;
                        $y = ($posicion['y'] / 100) * 137.87;
                        $w = max(($posicion['w'] / 100) * 216.49, 10);
                        $h = max(($posicion['h'] / 100) * 137.87,  5);
                        \Log::info('[BitacoraOverlay] firma solicita posicion', [
                            'pct' => ['x' => $posicion['x'], 'y' => $posicion['y'], 'w' => $posicion['w'], 'h' => $posicion['h']],
                            'mm'  => compact('x', 'y', 'w', 'h'),
                        ]);
                        $this->imagen($pdf, $imagen, $x, $y, $w, $h);
                    } else {
                        $this->imagen($pdf, $imagen, 12, 106, 55, 14);
                    }
                }
            } else {
                $this->imagen($pdf, $raw, 12, 106, 55, 14);
            }
        }

        // ── Firma: Recibe Biomédica (derecha) ─────────────────────────────
        $this->escribir($pdf, 118, 122, 88, $bitacora->atiende_nombre ?: '');

        if ($bitacora->firma_ingeniero) {
            $raw = $bitacora->firma_ingeniero;
            $x = 118; $y = 106; $w = 55; $h = 14;
            $imagenData = $raw;

            if (str_starts_with($raw, '{')) {
                $decoded    = json_decode($raw, true);
                $imagenData = $decoded['imagen'] ?? $raw;
                $pos        = $decoded['posicion'] ?? null;
                if ($pos) {
                    // Convertir porcentajes del canvas a mm (216.49×137.87 mm)
                    $x = ($pos['x'] / 100) * 216.49;
                    $y = ($pos['y'] / 100) * 137.87;
                    $w = max(($pos['w'] / 100) * 216.49, 10);
                    $h = max(($pos['h'] / 100) * 137.87,  5);
                }
            }

            $this->imagen($pdf, $imagenData, $x, $y, $w, $h);
        }

        return $pdf;
    }

    private function escribir(Fpdi $pdf, float $x, float $y, float $w, string $texto, string $align = 'C'): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 5, $this->limpiar($texto), 0, 0, $align);
    }

    private function bloque(Fpdi $pdf, float $x, float $y, float $w, string $texto, float $h): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, 5, $this->limpiar($texto), 0, 'L');
    }

    /** Palomita pequeña — sólo para los cuadros SI / NO de Finalizado. */
    private function checkbox(Fpdi $pdf, float $x, float $y, bool $marcado): void
    {
        if (! $marcado) return;
        $pdf->SetFont('ZapfDingbats', '', 10);
        $pdf->SetXY($x - 2, $y - 2);
        $pdf->Cell(6, 5, chr(52), 0, 0, 'C');
        $pdf->SetFont('Arial', '', 9);
    }

    /** Óvalo encerrador — para opciones de Servicio y Baja Por. */
    private function ovalo(FpdiEx $pdf, float $cx, float $cy, bool $marcado, float $rx): void
    {
        if (! $marcado) return;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.7);
        // cy + 2.5 = centro vertical de la celda de 5 mm; ry cubre la fila de ≈12 mm
        $pdf->Ellipse($cx, $cy + 2.5, $rx, 4.5);
        $pdf->SetLineWidth(0.2);
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
                // h=0 → FPDF calcula la altura proporcional automáticamente
                $pdf->Image($pngTmp, $x, $y, $w, 0, 'PNG');
                @unlink($pngTmp);
            } elseif ($ext !== 'svg') {
                // h=0 → FPDF calcula la altura proporcional automáticamente
                $info = @getimagesize($tmp);
                \Log::info('[BitacoraOverlay] imagen', [
                    'ext'   => $ext,
                    'w_mm'  => $w,
                    'h_mm'  => $h,
                    'img_w' => $info[0] ?? 'N/A',
                    'img_h' => $info[1] ?? 'N/A',
                ]);
                $pdf->Image($tmp, $x, $y, $w, 0, strtoupper($ext));
            }
        } catch (\Throwable $e) {
            \Log::error('[BitacoraOverlay] imagen error: ' . $e->getMessage());
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Calcula la altura en mm que preserva el aspect ratio natural de la imagen.
     * Si no se puede leer, devuelve $hFallback (el valor original).
     */
    private function alturaProporcionada(string $path, float $w, float $hFallback): float
    {
        $info = @getimagesize($path);
        if ($info && $info[0] > 0 && $info[1] > 0) {
            return $w * ($info[1] / $info[0]);
        }
        return $hFallback;
    }

    private function limpiar(string $texto): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto) ?: $texto;
    }
}
