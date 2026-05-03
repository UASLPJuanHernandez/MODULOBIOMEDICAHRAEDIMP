<?php

namespace App\Services;

use App\Models\Registro;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class RegistroOverlayService
{
    public function stream(Registro $registro): string
    {
        return $this->buildPdf($registro)->Output('S');
    }

    private function buildPdf(Registro $registro): Fpdi
    {
        $data    = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
        $campos  = $data['campos']  ?? [];
        $valores = $data['valores'] ?? [];

        // firma_jefe_data: JSON {"page","x","y","w","h","firma_svg"}
        $firmaJefe = $registro->firma_jefe_data
            ? json_decode($registro->firma_jefe_data, true)
            : null;

        $formato = $registro->formato;
        $pdfPath = Storage::disk('local')->path($formato->archivo_path);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
            $tplId = $pdf->importPage($pageNum);
            $size  = $pdf->getTemplateSize($tplId);

            $pw = (float) $size['width'];
            $ph = (float) $size['height'];
            $orientation = $pw > $ph ? 'L' : 'P';

            $pdf->AddPage($orientation, [$pw, $ph]);
            $pdf->useTemplate($tplId);

            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0, 0, 0);

            // Campos de texto y firmas de formato
            foreach ($campos as $campo) {
                if ((int) ($campo['page'] ?? 1) !== $pageNum) continue;

                $tipo  = $campo['tipo'] ?? 'texto';
                $valor = $valores[$campo['id'] ?? ''] ?? '';

                if ((string) $valor === '') continue;

                $x = ($campo['x'] / 100) * $pw;
                $y = ($campo['y'] / 100) * $ph;
                $w = max(($campo['w'] / 100) * $pw, 5.0);
                $h = max(($campo['h'] / 100) * $ph, 4.0);

                if ($tipo === 'firma' || $tipo === 'firma_jefe') {
                    $this->imagen($pdf, $valor, $x, $y, $w, $h);
                } else {
                    $pdf->SetXY($x, $y);
                    $pdf->MultiCell($w, 4, $this->limpiar($valor), 0, 'L');
                }
            }

            // Firma del jefe colocada libremente
            if ($firmaJefe && (int) ($firmaJefe['page'] ?? 1) === $pageNum) {
                $x = ($firmaJefe['x'] / 100) * $pw;
                $y = ($firmaJefe['y'] / 100) * $ph;
                $w = max(($firmaJefe['w'] / 100) * $pw, 10.0);
                $h = max(($firmaJefe['h'] / 100) * $ph,  5.0);

                $firmaImg = $firmaJefe['firma_svg'] ?? '';
                if ($firmaImg) {
                    $this->imagen($pdf, $firmaImg, $x, $y, $w, $h);
                }
            }
        }

        return $pdf;
    }

    private function imagen(Fpdi $pdf, string $dataUrl, float $x, float $y, float $w, float $h): void
    {
        if (! str_starts_with($dataUrl, 'data:')) return;

        $partes = explode(',', $dataUrl, 2);
        if (count($partes) !== 2) return;

        $meta    = $partes[0];
        $payload = $partes[1];

        // SVG charset-encoded: data:image/svg+xml;charset=utf-8,...
        // PNG/JPG:             data:image/png;base64,...
        if (str_contains($meta, 'base64')) {
            $binario = base64_decode($payload);
        } else {
            $binario = rawurldecode($payload);
        }

        $ext = 'jpg';
        if (str_contains($meta, 'png')) $ext = 'png';
        if (str_contains($meta, 'svg')) $ext = 'svg';

        $tmp = tempnam(sys_get_temp_dir(), 'regov_') . '.' . $ext;
        file_put_contents($tmp, $binario);

        try {
            if ($ext === 'svg') {
                // Convertir SVG a PNG via Imagick si está disponible
                if (extension_loaded('imagick')) {
                    $im = new \Imagick();
                    $im->setBackgroundColor(new \ImagickPixel('none'));
                    $im->readImageBlob($binario);
                    $im->setImageFormat('png32');
                    $pngTmp = tempnam(sys_get_temp_dir(), 'regov_') . '.png';
                    file_put_contents($pngTmp, $im->getImageBlob());
                    $im->clear();
                    $pdf->Image($pngTmp, $x, $y, $w, $h, 'PNG');
                    @unlink($pngTmp);
                }
                // Si Imagick no está disponible, la firma SVG se omite silenciosamente
            } else {
                $pdf->Image($tmp, $x, $y, $w, $h, strtoupper($ext));
            }
        } catch (\Throwable) {
            // Si la imagen no se puede renderizar, se omite
        } finally {
            @unlink($tmp);
        }
    }

    private function limpiar(string $texto): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto) ?: $texto;
    }
}
