<?php

namespace App\Services;

use App\Models\BitacoraReporte;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class BitacoraDocxService
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = storage_path('app/templates/bitacora_template.docx');
    }

    public function generar(BitacoraReporte $bitacora): string
    {
        $tp = new TemplateProcessor($this->templatePath);

        $fecha  = Carbon::parse($bitacora->fecha_reporte);
        $hora   = $bitacora->hora_reporte;

        // Datos del solicitante
        $tp->setValue('nombre_personal',      $bitacora->nombre_personal);
        $tp->setValue('numero_identificacion', $bitacora->numero_identificacion ?? '—');
        $tp->setValue('area_departamento',    $bitacora->area_departamento);

        // Fechas
        $tp->setValue('fecha_reporte',   $fecha->format('d/m/Y'));
        $tp->setValue('hora_reporte',    substr($hora, 0, 5));

        // Narrativa
        $tp->setValue('dia',            $fecha->day);
        $tp->setValue('mes',            $this->mesEnEspanol($fecha->month));
        $tp->setValue('anio',           $fecha->year);
        $tp->setValue('hora_narrativa', substr($hora, 0, 5));

        // Mensaje original
        $tp->setValue('mensaje_original', $bitacora->mensaje_original);

        // Acciones (hasta 3)
        $acciones = $bitacora->acciones ?? [];
        for ($i = 1; $i <= 3; $i++) {
            $texto = $acciones[$i - 1]['texto'] ?? '';
            $tp->setValue("accion_{$i}", $texto);
        }

        // Resultado
        $tp->setValue('resultado', $this->textoResultado($bitacora->resultado));

        // Equipo
        $tp->setValue('nombre_dispositivo', $bitacora->nombre_dispositivo ?? '—');
        $tp->setValue('numero_serie',       $bitacora->numero_serie ?? '—');

        // Guardar en storage
        $filename = 'bitacora_' . $bitacora->id . '_' . now()->format('Ymd_His') . '.docx';
        $outputPath = storage_path('app/bitacoras/' . $filename);

        if (! is_dir(storage_path('app/bitacoras'))) {
            mkdir(storage_path('app/bitacoras'), 0755, true);
        }

        $tp->saveAs($outputPath);

        return $outputPath;
    }

    private function mesEnEspanol(int $mes): string
    {
        return [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ][$mes];
    }

    private function textoResultado(string $resultado): string
    {
        return match($resultado) {
            'parcial'          => 'parcial',
            'no_satisfactoria' => 'no satisfactoria',
            default            => 'satisfactoria',
        };
    }
}
