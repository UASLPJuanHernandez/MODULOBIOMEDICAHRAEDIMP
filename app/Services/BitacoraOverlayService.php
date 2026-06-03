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
    private array  $pos;

    public function __construct()
    {
        $this->template = storage_path('app/templates/solicitud_servicio.pdf');

        $jsonPath = storage_path('app/overlay_positions.json');
        $this->pos = file_exists($jsonPath)
            ? json_decode(file_get_contents($jsonPath), true)
            : [];
    }

    private function p(string $section, string $key, string $field, mixed $default): mixed
    {
        if ($section === 'justificacion' || $section === 'justificacion2') {
            return $this->pos[$section][$field] ?? $default;
        }
        foreach ($this->pos[$section] ?? [] as $item) {
            if (($item['key'] ?? '') === $key) return $item[$field] ?? $default;
        }
        return $default;
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

        // ── Fila 1 ────────────────────────────────────────────────────
        $this->escribir($pdf, $this->p('textos','area_departamento','x',10),  $this->p('textos','area_departamento','y',37),  $this->p('textos','area_departamento','w',62),  $bitacora->area_departamento,        $this->p('textos','area_departamento','align','L'), $this->p('textos','area_departamento','h',5));
        $this->escribir($pdf, $this->p('textos','nombre_dispositivo','x',65), $this->p('textos','nombre_dispositivo','y',37), $this->p('textos','nombre_dispositivo','w',94), $bitacora->nombre_dispositivo ?: '', $this->p('textos','nombre_dispositivo','align','C'), $this->p('textos','nombre_dispositivo','h',5));
        $this->escribir($pdf, $this->p('textos','fecha_hora','x',159),        $this->p('textos','fecha_hora','y',37),        $this->p('textos','fecha_hora','w',56),        $fecha . '  ' . $hora,               $this->p('textos','fecha_hora','align','C'),        $this->p('textos','fecha_hora','h',5));

        // ── Fila 2 ────────────────────────────────────────────────────
        $this->escribir($pdf, $this->p('textos','marca','x',1),           $this->p('textos','marca','y',49),           $this->p('textos','marca','w',53),           $bitacora->marca ?: '',        $this->p('textos','marca','align','C'),           $this->p('textos','marca','h',5));
        $this->escribir($pdf, $this->p('textos','modelo','x',54),          $this->p('textos','modelo','y',49),          $this->p('textos','modelo','w',55),          $bitacora->modelo ?: '',       $this->p('textos','modelo','align','C'),          $this->p('textos','modelo','h',5));
        $this->escribir($pdf, $this->p('textos','numero_serie','x',109),   $this->p('textos','numero_serie','y',49),   $this->p('textos','numero_serie','w',55),   $bitacora->numero_serie ?: '', $this->p('textos','numero_serie','align','C'),    $this->p('textos','numero_serie','h',5));
        $this->escribir($pdf, $this->p('textos','numero_control','x',164), $this->p('textos','numero_control','y',49), $this->p('textos','numero_control','w',51), $bitacora->numero_control ?? '',$this->p('textos','numero_control','align','C'),  $this->p('textos','numero_control','h',5));

        // ── Óvalos SE SOLICITA ────────────────────────────────────────
        $esPreventivo = $bitacora->tipo_servicio === 'preventivo';
        $esCorrectivo = $bitacora->tipo_servicio === 'correctivo';
        $tipoBaja     = $bitacora->tipo_baja ?? '';

        $this->ovalo($pdf, $this->p('ovalos','preventivo','cx',22),   $this->p('ovalos','preventivo','cy',76),   $esPreventivo,               $this->p('ovalos','preventivo','rx',18));
        $this->ovalo($pdf, $this->p('ovalos','correctivo','cx',62),   $this->p('ovalos','correctivo','cy',76),   $esCorrectivo,               $this->p('ovalos','correctivo','rx',15));
        $this->ovalo($pdf, $this->p('ovalos','no_funcional','cx',96), $this->p('ovalos','no_funcional','cy',73), $tipoBaja==='no_funcional',   $this->p('ovalos','no_funcional','rx',9));
        $this->ovalo($pdf, $this->p('ovalos','inservible','cx',119),  $this->p('ovalos','inservible','cy',76),   $tipoBaja==='inservible',     $this->p('ovalos','inservible','rx',9));
        $this->ovalo($pdf, $this->p('ovalos','obsoleto','cx',141),    $this->p('ovalos','obsoleto','cy',76),     $tipoBaja==='obsoleto',       $this->p('ovalos','obsoleto','rx',7));
        $this->ovalo($pdf, $this->p('ovalos','disposicion','cx',160), $this->p('ovalos','disposicion','cy',76),  $tipoBaja==='disposicion',    $this->p('ovalos','disposicion','rx',6));
        $this->ovalo($pdf, $this->p('ovalos','traspaso','cx',176),    $this->p('ovalos','traspaso','cy',76),     $tipoBaja==='traspaso',       $this->p('ovalos','traspaso','rx',6));
        $this->ovalo($pdf, $this->p('ovalos','otro','cx',199),        $this->p('ovalos','otro','cy',76),         $tipoBaja==='otro',           $this->p('ovalos','otro','rx',9));

        // ── Justificación (auto font-size para que nunca desborde) ───
        $textoJustificacion = $this->limpiar($bitacora->justificacion ?? $bitacora->mensaje_original ?? '');
        $j1 = [
            'x' => $this->p('justificacion','','x',9),   'y' => $this->p('justificacion','','y',82),
            'w' => $this->p('justificacion','','w',197),  'h' => $this->p('justificacion','','h',25),
            'lh'=> $this->p('justificacion','','lineH',5),
        ];
        $j2 = [
            'x' => $this->p('justificacion2','','x',9),  'y' => $this->p('justificacion2','','y',99),
            'w' => $this->p('justificacion2','','w',137), 'h' => $this->p('justificacion2','','h',15),
            'lh'=> $this->p('justificacion2','','lineH',5),
        ];

        $this->renderJustificacion($pdf, $textoJustificacion, $j1, $j2);

        // ── Finalizado ────────────────────────────────────────────────
        $finalizado = in_array($bitacora->resultado, ['satisfactoria', 'parcial']);
        $this->checkbox($pdf, $this->p('checkboxes','si','x',171), $this->p('checkboxes','si','y',110), $finalizado,  $this->p('checkboxes','si','w',6),  $this->p('checkboxes','si','h',5));
        $this->checkbox($pdf, $this->p('checkboxes','no','x',184), $this->p('checkboxes','no','y',110), !$finalizado, $this->p('checkboxes','no','w',6), $this->p('checkboxes','no','h',5));

        if ($finalizado) {
            $this->escribir($pdf, $this->p('textos','fecha_hora_finalizado','x',163), $this->p('textos','fecha_hora_finalizado','y',113), $this->p('textos','fecha_hora_finalizado','w',50), $fecha . '  ' . $hora, $this->p('textos','fecha_hora_finalizado','align','C'), $this->p('textos','fecha_hora_finalizado','h',5));
        }

        // ── Firma: Solicita ───────────────────────────────────────────
        $this->escribir($pdf, $this->p('textos','nombre_personal','x',12), $this->p('textos','nombre_personal','y',122), $this->p('textos','nombre_personal','w',88), $bitacora->nombre_personal, $this->p('textos','nombre_personal','align','C'), $this->p('textos','nombre_personal','h',5));

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
                        $this->imagen($pdf, $imagen, $this->p('firmas','firma_solicita','x',12), $this->p('firmas','firma_solicita','y',106), $this->p('firmas','firma_solicita','w',55), $this->p('firmas','firma_solicita','h',14));
                    }
                }
            } else {
                $this->imagen($pdf, $raw, $this->p('firmas','firma_solicita','x',12), $this->p('firmas','firma_solicita','y',106), $this->p('firmas','firma_solicita','w',55), $this->p('firmas','firma_solicita','h',14));
            }
        }

        // ── Firma: Recibe Biomédica (derecha) ─────────────────────────────
        $this->escribir($pdf, $this->p('textos','atiende_nombre','x',118), $this->p('textos','atiende_nombre','y',122), $this->p('textos','atiende_nombre','w',88), $bitacora->atiende_nombre ?: '', $this->p('textos','atiende_nombre','align','C'), $this->p('textos','atiende_nombre','h',5));

        if ($bitacora->firma_ingeniero) {
            $raw = $bitacora->firma_ingeniero;
            $x = $this->p('firmas','firma_ingeniero','x',118);
            $y = $this->p('firmas','firma_ingeniero','y',106);
            $w = $this->p('firmas','firma_ingeniero','w',55);
            $h = $this->p('firmas','firma_ingeniero','h',14);
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

    private function escribir(Fpdi $pdf, float $x, float $y, float $w, string $texto, string $align = 'C', float $h = 5): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $this->limpiar($texto), 0, 0, $align);
    }

    private function bloque(Fpdi $pdf, float $x, float $y, float $w, string $texto, float $h, float $lineH = 5): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $lineH, $this->limpiar($texto), 0, 'L');
    }

    private function renderJustificacion(Fpdi $pdf, string $texto, array $j1, array $j2): void
    {
        $maxL1 = $j1['lh'] > 0 ? (int) floor($j1['h'] / $j1['lh']) : 100;
        $maxL2 = $j2['lh'] > 0 ? (int) floor($j2['h'] / $j2['lh']) : 100;

        // Buscar el tamaño de fuente más grande que quepa (9 → 5, paso 0.5)
        $fsFinal  = 5.0;
        $l1Final  = [];
        $l2Final  = [];

        for ($fs = 9.0; $fs >= 5.0; $fs -= 0.5) {
            $pdf->SetFont('Arial', '', $fs);

            $lineas  = $this->dividirLineas($pdf, $texto, $j1['w']);
            $parte1  = array_slice($lineas, 0, $maxL1);
            $resto   = array_slice($lineas, $maxL1);

            if (empty($resto)) {
                // Todo cabe en bloque 1
                $fsFinal = $fs; $l1Final = $parte1; $l2Final = []; break;
            }

            // Verificar si el resto cabe en bloque 2
            $parte2 = $this->dividirLineas($pdf, implode("\n", $resto), $j2['w']);
            if (count($parte2) <= $maxL2) {
                $fsFinal = $fs; $l1Final = $parte1; $l2Final = $parte2; break;
            }

            // No cabe: si llegamos a 5, truncar al máximo posible
            if ($fs <= 5.0) {
                $fsFinal = 5.0;
                $l1Final = $parte1;
                $l2Final = array_slice($parte2, 0, $maxL2);
            }
        }

        $pdf->SetFont('Arial', '', $fsFinal);

        $this->renderLineas($pdf, $l1Final, $j1['x'], $j1['y'], $j1['w'], $j1['lh']);

        if (!empty($l2Final)) {
            $this->renderLineas($pdf, $l2Final, $j2['x'], $j2['y'], $j2['w'], $j2['lh']);
        }

        $pdf->SetFont('Arial', '', 9);
    }

    private function renderLineas(Fpdi $pdf, array $lineas, float $x, float $y, float $w, float $lh, string $align = 'L'): void
    {
        foreach ($lineas as $linea) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($w, $lh, $linea, 0, 0, $align);
            $y += $lh;
        }
    }

    /** Divide texto en líneas que caben dentro del ancho dado. */
    private function dividirLineas(Fpdi $pdf, string $texto, float $w): array
    {
        $lineas = [];
        foreach (explode("\n", $texto) as $parrafo) {
            $parrafo = trim($parrafo);
            if ($parrafo === '') { $lineas[] = ''; continue; }
            $palabras = explode(' ', $parrafo);
            $linea = '';
            foreach ($palabras as $palabra) {
                $prueba = $linea !== '' ? $linea . ' ' . $palabra : $palabra;
                if ($pdf->GetStringWidth($prueba) <= $w) {
                    $linea = $prueba;
                } else {
                    if ($linea !== '') $lineas[] = $linea;
                    $linea = $palabra;
                }
            }
            if ($linea !== '') $lineas[] = $linea;
        }
        return $lineas;
    }

    /** Palomita pequeña — sólo para los cuadros SI / NO de Finalizado. */
    private function checkbox(Fpdi $pdf, float $x, float $y, bool $marcado, float $w = 6, float $h = 5): void
    {
        if (! $marcado) return;
        $pdf->SetFont('ZapfDingbats', '', 10);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, chr(52), 0, 0, 'C');
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
