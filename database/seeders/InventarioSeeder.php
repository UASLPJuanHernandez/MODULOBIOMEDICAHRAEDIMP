<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventarioEquipo;
use Illuminate\Support\Facades\DB;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/inventario.xlsx');

        if (! file_exists($path)) {
            $this->command->error("No se encontró el archivo: $path");
            return;
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['INVENTARIO']);
        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $datos = array_slice($rows, 2);

        $limpiarStr = fn($v) => ($v !== null && $v !== '' &&
            strtolower(trim((string)$v)) !== 'n/a' &&
            strtolower(trim((string)$v)) !== 'n/d')
            ? trim((string)$v) : null;

        $limpiarFecha = function ($v) {
            if (! $v) return null;
            if ($v instanceof \DateTime) return $v->format('Y-m-d');
            if (is_numeric($v)) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v)->format('Y-m-d');
                } catch (\Throwable) { return null; }
            }
            try { return (new \DateTime($v))->format('Y-m-d'); }
            catch (\Throwable) { return null; }
        };

        $limpiarBool = fn($v) => in_array(strtoupper(trim((string)($v ?? ''))), ['SI', 'SÍ', 'YES', '1', 'TRUE']);

        $this->command->info('Limpiando inventario anterior...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        InventarioEquipo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Importando equipos...');
        $insertados = 0;

        foreach ($datos as $r) {
            if (! isset($r[0]) || $r[0] === null || trim((string)$r[0]) === '') continue;
            $numInv = $limpiarStr($r[1]);
            if (! $numInv) continue;

            InventarioEquipo::create([
                'numero_inventario'       => $numInv,
                'clues'                   => $limpiarStr($r[3]),
                'unidad_medica'           => $limpiarStr($r[4]),
                'area'                    => $limpiarStr($r[5]),
                'ubicacion_especifica'    => $limpiarStr($r[6]),
                'clave_cbsg'              => $limpiarStr($r[9]),
                'equipo'                  => $limpiarStr($r[10]),
                'equipo_alternativo'      => $limpiarStr($r[11]),
                'marca'                   => $limpiarStr($r[13]),
                'modelo'                  => $limpiarStr($r[14]),
                'numero_serie'            => $limpiarStr($r[15]),
                'propiedad'               => $limpiarStr($r[16]),
                'condiciones'             => $limpiarStr($r[18]),
                'estatus'                 => $limpiarStr($r[19]),
                'causa_no_funcionamiento' => $limpiarStr($r[20]),
                'fecha_adquisicion'       => $limpiarFecha($r[21]),
                'anio_fabricacion'        => $limpiarStr($r[22]),
                'requerimientos'          => $limpiarStr($r[23]),
                'frecuencia_mantenimiento'=> $limpiarStr($r[40]),
                'tipo_mantenimiento'      => $limpiarStr($r[41]),
                'contrato_mantenimiento'  => $limpiarStr($r[42]),
                'fin_vida_util'           => $limpiarBool($r[43]),
                'garantia'                => $limpiarBool($r[44]),
                'fin_garantia'            => $limpiarFecha($r[45]),
                'tiene_contrato'          => $limpiarBool($r[46]),
                'numero_contrato'         => $limpiarStr($r[47]),
                'proveedor_mantenimiento' => $limpiarStr($r[48]),
                'inicio_poliza'           => $limpiarFecha($r[49]),
                'fin_poliza'              => $limpiarFecha($r[50]),
                'costo_contrato'          => $limpiarStr($r[51]),
                'cantidad_mp_anio'        => $limpiarStr($r[52]),
                'ultimo_mp'               => $limpiarFecha($r[55]),
                'siguiente_mp'            => $limpiarFecha($r[56]),
                'observaciones'           => $limpiarStr($r[69]),
            ]);
            $insertados++;
        }

        $this->command->info("✓ $insertados equipos importados correctamente.");
    }
}
