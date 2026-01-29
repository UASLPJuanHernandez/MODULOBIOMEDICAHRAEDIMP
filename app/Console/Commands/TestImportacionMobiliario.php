<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestImportacionMobiliario extends Command
{
    protected $signature = 'test:importacion-mobiliario {--file=bienes_importar_limpio.csv}';
    protected $description = 'Prueba la importación de mobiliario desde CSV';

    public function handle()
    {
        $this->info('=== Prueba de Importación de Mobiliario ===');
        $this->newLine();
        
        $countAntes = Mobiliario::count();
        $this->info("Mobiliarios antes de la prueba: {$countAntes}");
        
        // Leer CSV
        $csvFile = base_path($this->option('file'));
        if (!file_exists($csvFile)) {
            $csvFile = base_path('bienes_importar.csv');
        }
        
        $this->info("Archivo CSV: {$csvFile}");
        
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->error("No se pudo abrir el archivo CSV");
            return 1;
        }
        
        // Obtener encabezados
        $headers = fgetcsv($handle);
        $this->info("Columnas encontradas: " . count($headers));
        
        // Mapear columnas
        $columnIndexes = $this->mapearColumnas($headers);
        
        $this->newLine();
        $this->info("Mapeo de columnas encontrado:");
        foreach ($columnIndexes as $key => $index) {
            $this->line("  {$key} => columna {$index} ({$headers[$index]})");
        }
        
        // Procesar filas
        $rowCount = 0;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        $this->newLine();
        $this->info("=== Procesando filas de prueba ===");
        
        while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
            if (empty($row[0]) || strpos($row[0] ?? '', 'F. Cal') !== false || strpos($row[0] ?? '', ',,,') !== false) {
                continue;
            }
            
            $rowCount++;
            $this->newLine();
            $this->info("--- Fila {$rowCount} ---");
            
            // Extraer datos
            $data = [];
            foreach ($columnIndexes as $key => $index) {
                $data[$key] = isset($row[$index]) ? trim($row[$index]) : null;
            }
            
            $this->line("  Clave: " . ($data['clave_bien'] ?? 'N/A'));
            $this->line("  Nombre: " . ($data['nombre_bien'] ?? 'N/A'));
            $this->line("  Valor: " . ($data['valor'] ?? 'N/A'));
            $this->line("  Dirección: " . ($data['direccion'] ?? 'N/A'));
            $this->line("  División: " . ($data['division'] ?? 'N/A'));
            $this->line("  Departamento: " . ($data['departamento'] ?? 'N/A'));
            $this->line("  Responsable: " . ($data['responsable'] ?? 'N/A'));
            
            try {
                DB::beginTransaction();
                
                $mobiliario = $this->crearMobiliario($data, $rowCount);
                
                DB::commit();
                
                $this->info("  ✅ Mobiliario creado: ID {$mobiliario->id}");
                $successCount++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("  ❌ Error: " . $e->getMessage());
                $errors[] = "Fila {$rowCount}: " . $e->getMessage();
                $errorCount++;
            }
        }
        
        fclose($handle);
        
        $this->newLine();
        $this->info("=== Resumen ===");
        $this->line("Filas procesadas: {$rowCount}");
        $this->line("Éxitos: {$successCount}");
        $this->line("Errores: {$errorCount}");
        
        $countDespues = Mobiliario::count();
        $this->line("Mobiliarios después de la prueba: {$countDespues}");
        $this->line("Nuevos registros: " . ($countDespues - $countAntes));
        
        if (!empty($errors)) {
            $this->newLine();
            $this->warn("=== Errores ===");
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }
        
        $this->newLine();
        $this->info("Prueba finalizada.");
        
        return 0;
    }
    
    protected function mapearColumnas(array $headers): array
    {
        $columnNames = [
            'clave_bien' => ['Clave del Bien'],
            'nombre_bien' => ['Nombre del Bien'],
            'grupo' => ['Grupo'],
            'subgrupo' => ['Subgrupo'],
            'clase' => ['Clase'],
            'marca' => ['Marca'],
            'modelo' => ['Modelo'],
            'color' => ['Color'],
            'numero_serie' => ['N. de Serie'],
            'no_factura' => ['No Factura'],
            'proveedor' => ['Proveedor'],
            'metodo_adquisicion' => ['F. Adquisici', 'F. Adquisicion'],
            'fecha_factura' => ['F. de Factura'],
            'fecha_baja' => ['F. de  Baja', 'F. de Baja'],
            'valor' => ['Valor'],
            'fecha_registro' => ['F. Registro'],
            'ubicacion' => ['Ubicacion'],
            'caracteristicas' => ['Caracteristicas'],
            'procedencia' => ['Procedencia'],
            'direccion' => ['Direcci', 'Direccion'],
            'division' => ['Divisi', 'Division'],
            'departamento' => ['Departamento'],
            'responsable' => ['Responsable'],
            'clave_empleado' => ['Clave Emp'],
            'puesto' => ['Puesto'],
        ];
        
        $columnIndexes = [];
        
        foreach ($columnNames as $key => $patterns) {
            foreach ($headers as $index => $header) {
                foreach ($patterns as $pattern) {
                    if (stripos($header, $pattern) !== false) {
                        $columnIndexes[$key] = $index;
                        break 2;
                    }
                }
            }
        }
        
        return $columnIndexes;
    }
    
    protected function crearMobiliario(array $data, int $rowCount): Mobiliario
    {
        // Generar número de control
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $numeroControl = "IMP-{$timestamp}-{$random}-{$rowCount}";
        
        // Resolver clasificación
        $clasificacionId = $this->resolverClasificacion($data);
        
        // Resolver tipo de mobiliario
        $tipoMobiliarioId = TipoMobiliario::first()?->id ?? 1;
        
        // Resolver localización
        $localizacionId = $this->resolverLocalizacion($data);
        
        // Parsear precio
        $precio = 0;
        if (!empty($data['valor'])) {
            $precio = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '', $data['valor'])));
        }
        
        // Estado basado en fecha de baja
        $estadoData = $this->procesarEstado($data);
        
        // Limpiar caracteristicas y construir el texto combinado
        $partesCaracteristicas = [];
        
        if (!empty($data['caracteristicas'])) {
            $caracteristicas = preg_replace('/^[\+\-\*\s]+/', '', $data['caracteristicas']);
            $partesCaracteristicas[] = trim($caracteristicas);
        }
        
        if (!empty($data['color'])) {
            $partesCaracteristicas[] = "Color: {$data['color']}";
        }
        
        if (!empty($data['procedencia'])) {
            $partesCaracteristicas[] = "Procedencia: {$data['procedencia']}";
        }
        
        if (!empty($data['grupo'])) {
            $partesCaracteristicas[] = "Grupo: {$data['grupo']}";
        }
        
        if (!empty($data['subgrupo'])) {
            $partesCaracteristicas[] = "Subgrupo: {$data['subgrupo']}";
        }
        
        if (!empty($data['clase'])) {
            $partesCaracteristicas[] = "Clase: {$data['clase']}";
        }
        
        $caracteristicasFinal = !empty($partesCaracteristicas) 
            ? implode(', ', $partesCaracteristicas)
            : 'Sin características especificadas';
        
        return Mobiliario::create([
            'numero_control' => $numeroControl,
            'numero_inventario' => $data['clave_bien'] ?? null,
            'clasificacion_bienes_id' => $clasificacionId,
            'caracteristicas' => $caracteristicasFinal,
            'descripcion' => substr($data['nombre_bien'] ?? 'Sin descripción', 0, 255),
            'marca' => $data['marca'] ?? 'Sin marca',
            'modelo' => $data['modelo'] ?? 'Sin modelo',
            'numero_serie' => $data['numero_serie'] ?? null,
            'precio' => $precio,
            'tipo_mobiliario_id' => $tipoMobiliarioId,
            'localizacion_id' => $localizacionId,
            'proveedor_id' => null,
            'metodo_adquisicion' => $data['metodo_adquisicion'] ?? null,
            'tiene_folio' => !empty($data['no_factura']),
            'numero_folio' => $data['no_factura'] ?? null,
            'estado_mobiliario' => $estadoData['estado'],
            'dado_de_baja' => $estadoData['dado_de_baja'],
            'fecha_baja' => $estadoData['fecha_baja'],
            'motivo_baja' => $estadoData['motivo_baja'],
            'tiene_accesorios' => false,
            'responsable_actual' => $data['responsable'] ?? null,
            'matricula_responsable' => $data['clave_empleado'] ?? null,
            'puesto_responsable' => $data['puesto'] ?? null,
            'version' => 1,
            'depreciacion_registrada' => 0,
            'created_by' => auth()->id() ?? 1,
            'updated_by' => auth()->id() ?? 1,
        ]);
    }
    
    protected function resolverClasificacion(array $data): int
    {
        $grupo = !empty($data['grupo']) ? intval($data['grupo']) : null;
        $subgrupo = !empty($data['subgrupo']) ? intval($data['subgrupo']) : null;
        $clase = !empty($data['clase']) ? intval($data['clase']) : null;
        
        if ($grupo !== null && $subgrupo !== null && $clase !== null) {
            $clasificacion = ClasificacionBien::where('grupo', $grupo)
                ->where('subgrupo', $subgrupo)
                ->where('clase', $clase)
                ->first();
            
            if ($clasificacion) {
                return $clasificacion->id;
            }
        }
        
        return ClasificacionBien::first()?->id ?? 1;
    }
    
    protected function resolverLocalizacion(array $data): int
    {
        $direccion = mb_strtoupper(trim($data['direccion'] ?? ''), 'UTF-8');
        $division = mb_strtoupper(trim($data['division'] ?? ''), 'UTF-8');
        $departamento = mb_strtoupper(trim($data['departamento'] ?? ''), 'UTF-8');
        $ubicacionStr = mb_strtoupper(trim($data['ubicacion'] ?? ''), 'UTF-8');
        
        // Buscar por división y departamento
        if (!empty($division) && !empty($departamento)) {
            $localizacion = Localizacion::where(function($q) use ($division, $departamento) {
                $q->whereRaw('UPPER(division) LIKE ?', ["%{$division}%"])
                  ->whereRaw('UPPER(sub_area) LIKE ?', ["%{$departamento}%"]);
            })->first();
            
            if ($localizacion) {
                return $localizacion->id;
            }
        }
        
        // Buscar por dirección
        if (!empty($direccion)) {
            $localizacion = Localizacion::whereRaw('UPPER(direccion) LIKE ?', ["%{$direccion}%"])->first();
            
            if ($localizacion) {
                return $localizacion->id;
            }
        }
        
        // Crear nueva localización
        $nuevaLocalizacion = Localizacion::create([
            'direccion' => !empty($direccion) ? $direccion : 'SIN ESPECIFICAR',
            'division' => !empty($division) ? $division : 'SIN ESPECIFICAR',
            'sub_area' => !empty($departamento) ? $departamento : 'SIN ESPECIFICAR',
            'ubicacion' => !empty($ubicacionStr) ? $ubicacionStr : 'IMPORTADO DEL SISTEMA ANTERIOR',
        ]);
        
        $this->line("  [Nueva localización creada: ID {$nuevaLocalizacion->id}]");
        
        return $nuevaLocalizacion->id;
    }
    
    protected function procesarEstado(array $data): array
    {
        $estado = 'Usado';
        $dadoDeBaja = false;
        $fechaBaja = null;
        $motivoBaja = null;
        
        if (!empty($data['fecha_baja'])) {
            $fechaStr = trim($data['fecha_baja']);
            
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fechaStr, $matches)) {
                $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $anio = $matches[3];
                
                if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                    $fechaBaja = "{$anio}-{$mes}-{$dia}";
                    $estado = 'Baja';
                    $dadoDeBaja = true;
                    $motivoBaja = 'Importado del sistema anterior con fecha de baja';
                }
            }
        }
        
        return [
            'estado' => $estado,
            'dado_de_baja' => $dadoDeBaja,
            'fecha_baja' => $fechaBaja,
            'motivo_baja' => $motivoBaja,
        ];
    }
}
