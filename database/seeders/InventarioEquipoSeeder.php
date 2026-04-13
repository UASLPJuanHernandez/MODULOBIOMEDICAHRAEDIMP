<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventarioEquipo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventarioEquipoSeeder extends Seeder
{
    /**
     * CSV column indices (row 5 = headers, data starts at row 6):
     * 0  = index
     * 1  = No. DE INVENTARIO
     * 2  = NUEVA CLUE
     * 3  = CLUES
     * 4  = UNIDAD MÉDICA
     * 5  = ESPECIALIDAD/ÁREA DEL HOSPITAL
     * 6  = UBICACIÓN ESPECÍFICA
     * 9  = CLAVE DE CUADRO BÁSICO CSG
     * 10 = EQUIPO
     * 11 = EQUIPO ALTERNATIVO
     * 13 = MARCA
     * 14 = MODELO
     * 15 = NÚMERO DE SERIE
     * 16 = PROPIEDAD DEL EQUIPO
     * 18 = CONDICIONES DEL EQUIPO
     * 19 = ESTATUS DEL EQUIPO
     * 20 = CAUSA DE NO FUNCIONAMIENTO
     * 21 = FECHA DE ADQUISICIÓN (Excel serial)
     * 22 = FECHA DE FABRICACIÓN (AÑO)
     * 23 = REQUERIMIENTOS Y NECESIDADES
     * 40 = FRECUENCIA DE MANTENIMIENTO
     * 41 = MANTENIMIENTO PREVENTIVO (INTERNO/EXTERNO)
     * 42 = CONTRATO DE MANTENIMIENTO EXTERNO
     * 43 = FIN DE VIDA ÚTIL (EOL)
     * 44 = GARANTÍA (SI/NO)
     * 45 = FIN DE GARANTÍA (Excel serial)
     * 46 = CONTRATO (SI/NO)
     * 47 = No. DE CONTRATO
     * 48 = PROVEEDOR DEL SERVICIO DE MANTENIMIENTO EXTERNO
     * 49 = INICIO PÓLIZA (Excel serial)
     * 50 = FIN PÓLIZA (Excel serial)
     * 51 = COSTO DE CONTRATO
     * 52 = CANTIDAD DE MP AL AÑO
     * 55 = ÚLTIMO MP (Excel serial)
     * 56 = SIGUIENTE MP (Excel serial)
     * 69 = OBSERVACIONES
     */

    private function excelSerialToDate(?string $value): ?string
    {
        if (empty($value) || $value === 'N/A' || $value === 'NA' || !is_numeric($value)) {
            return null;
        }
        $serial = (int) $value;
        if ($serial < 1 || $serial > 200000) {
            return null;
        }
        try {
            // Excel serial date: days since 1900-01-00 (with Lotus 1-2-3 bug)
            $date = Carbon::create(1899, 12, 30)->addDays($serial);
            if ($date->year < 1950 || $date->year > 2100) {
                return null;
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseBool(?string $value): bool
    {
        if (empty($value)) return false;
        return strtoupper(trim($value)) === 'SI' || strtoupper(trim($value)) === 'SÍ';
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        if ($v === '' || strtoupper($v) === 'N/A' || strtoupper($v) === 'NA') return null;
        return $v;
    }

    public function run(): void
    {
        $csvPath = base_path('../ESTADO ACTUAL INVENTARIO EQUIPAMIENTO.- HRAEDIMP  (6) (1).xlsx - INVENTARIO.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV no encontrado en: {$csvPath}");
            $this->command->info('Intenta copiar el CSV a la raíz del proyecto y correr el seeder de nuevo.');
            return;
        }

        $this->command->info('Importando inventario desde CSV...');

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command->error('No se pudo abrir el archivo CSV.');
            return;
        }

        // Saltar las primeras 6 filas (encabezados del documento)
        for ($i = 0; $i < 6; $i++) {
            fgetcsv($handle);
        }

        DB::beginTransaction();
        try {
            InventarioEquipo::truncate();

            $count = 0;
            $batch = [];
            $batchSize = 100;
            $now = now()->toDateTimeString();

            while (($row = fgetcsv($handle)) !== false) {
                // Saltar filas vacías
                if (empty(array_filter(array_slice($row, 0, 5)))) {
                    continue;
                }

                $equipo = $this->clean($row[10] ?? null);
                if (empty($equipo)) continue;

                $batch[] = [
                    'numero_inventario'      => $this->clean($row[1] ?? null),
                    'clues'                  => $this->clean($row[3] ?? null),
                    'unidad_medica'          => $this->clean($row[4] ?? null),
                    'area'                   => $this->clean($row[5] ?? null),
                    'ubicacion_especifica'   => $this->clean($row[6] ?? null),
                    'clave_cbsg'             => $this->clean($row[9] ?? null),
                    'equipo'                 => $equipo,
                    'equipo_alternativo'     => $this->clean($row[11] ?? null),
                    'marca'                  => $this->clean($row[13] ?? null),
                    'modelo'                 => $this->clean($row[14] ?? null),
                    'numero_serie'           => $this->clean($row[15] ?? null),
                    'propiedad'              => $this->clean($row[16] ?? null),
                    'condiciones'            => $this->clean($row[18] ?? null),
                    'estatus'                => $this->clean($row[19] ?? null),
                    'causa_no_funcionamiento'=> $this->clean($row[20] ?? null),
                    'fecha_adquisicion'      => $this->excelSerialToDate($row[21] ?? null),
                    'anio_fabricacion'       => $this->clean($row[22] ?? null),
                    'requerimientos'         => $this->clean($row[23] ?? null),
                    'frecuencia_mantenimiento'=> $this->clean($row[40] ?? null),
                    'tipo_mantenimiento'     => $this->clean($row[41] ?? null),
                    'contrato_mantenimiento' => $this->clean($row[42] ?? null),
                    'fin_vida_util'          => $this->parseBool($row[43] ?? null),
                    'garantia'               => $this->parseBool($row[44] ?? null),
                    'fin_garantia'           => $this->excelSerialToDate($row[45] ?? null),
                    'tiene_contrato'         => $this->parseBool($row[46] ?? null),
                    'numero_contrato'        => $this->clean($row[47] ?? null),
                    'proveedor_mantenimiento'=> $this->clean($row[48] ?? null),
                    'inicio_poliza'          => $this->excelSerialToDate($row[49] ?? null),
                    'fin_poliza'             => $this->excelSerialToDate($row[50] ?? null),
                    'costo_contrato'         => $this->clean($row[51] ?? null),
                    'cantidad_mp_anio'       => $this->clean($row[52] ?? null),
                    'ultimo_mp'              => $this->excelSerialToDate($row[55] ?? null),
                    'siguiente_mp'           => $this->excelSerialToDate($row[56] ?? null),
                    'observaciones'          => $this->clean($row[69] ?? null),
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ];

                $count++;

                if (count($batch) >= $batchSize) {
                    InventarioEquipo::insert($batch);
                    $batch = [];
                    $this->command->info("  {$count} registros insertados...");
                }
            }

            if (!empty($batch)) {
                InventarioEquipo::insert($batch);
            }

            DB::commit();
            fclose($handle);
            $this->command->info("✓ Importación completada: {$count} equipos importados.");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $this->command->error('Error durante la importación: ' . $e->getMessage());
            throw $e;
        }
    }
}
