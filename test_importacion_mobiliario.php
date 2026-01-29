<?php

/**
 * Script para probar la importación de mobiliario desde CSV
 * Ejecutar con: ./vendor/bin/sail artisan tinker < test_importacion_mobiliario.php
 */

use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

echo "=== Prueba de Importación de Mobiliario ===" . PHP_EOL . PHP_EOL;

// Contar registros actuales
$countAntes = Mobiliario::count();
echo "Mobiliarios antes de la prueba: {$countAntes}" . PHP_EOL;

// Leer CSV de prueba
$csvFile = base_path('bienes_importar_limpio.csv');
if (!file_exists($csvFile)) {
    $csvFile = base_path('bienes_importar.csv');
}

echo "Archivo CSV: {$csvFile}" . PHP_EOL;

// Leer el CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("No se pudo abrir el archivo CSV" . PHP_EOL);
}

// Obtener encabezados
$headers = fgetcsv($handle);
echo "Columnas encontradas: " . count($headers) . PHP_EOL;

// Buscar índices de columnas importantes
$columnIndexes = [];
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

echo PHP_EOL . "Mapeo de columnas encontrado:" . PHP_EOL;
foreach ($columnIndexes as $key => $index) {
    echo "  {$key} => columna {$index} ({$headers[$index]})" . PHP_EOL;
}

// Procesar algunas filas de prueba
$rowCount = 0;
$successCount = 0;
$errorCount = 0;
$errors = [];

echo PHP_EOL . "=== Procesando filas de prueba ===" . PHP_EOL;

while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
    // Saltar filas vacías o de encabezados secundarios
    if (empty($row[0]) || strpos($row[0], 'F. Cal') !== false) {
        continue;
    }
    
    $rowCount++;
    echo PHP_EOL . "--- Fila {$rowCount} ---" . PHP_EOL;
    
    // Extraer datos
    $data = [];
    foreach ($columnIndexes as $key => $index) {
        $data[$key] = isset($row[$index]) ? trim($row[$index]) : null;
    }
    
    echo "  Clave: " . ($data['clave_bien'] ?? 'N/A') . PHP_EOL;
    echo "  Nombre: " . ($data['nombre_bien'] ?? 'N/A') . PHP_EOL;
    echo "  Valor: " . ($data['valor'] ?? 'N/A') . PHP_EOL;
    echo "  Dirección: " . ($data['direccion'] ?? 'N/A') . PHP_EOL;
    echo "  División: " . ($data['division'] ?? 'N/A') . PHP_EOL;
    echo "  Departamento: " . ($data['departamento'] ?? 'N/A') . PHP_EOL;
    echo "  Responsable: " . ($data['responsable'] ?? 'N/A') . PHP_EOL;
    
    // Intentar crear el registro
    try {
        DB::beginTransaction();
        
        // Generar número de control
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $numeroControl = "IMP-{$timestamp}-{$random}-{$rowCount}";
        
        // Resolver clasificación
        $grupo = !empty($data['grupo']) ? intval($data['grupo']) : null;
        $subgrupo = !empty($data['subgrupo']) ? intval($data['subgrupo']) : null;
        $clase = !empty($data['clase']) ? intval($data['clase']) : null;
        
        $clasificacionId = null;
        if ($grupo && $subgrupo && $clase) {
            $clasificacion = ClasificacionBien::where('grupo', $grupo)
                ->where('subgrupo', $subgrupo)
                ->where('clase', $clase)
                ->first();
            $clasificacionId = $clasificacion?->id;
        }
        if (!$clasificacionId) {
            $clasificacionId = ClasificacionBien::first()?->id ?? 1;
        }
        
        // Resolver tipo de mobiliario
        $tipoMobiliarioId = TipoMobiliario::first()?->id ?? 1;
        
        // Resolver localización
        $direccion = $data['direccion'] ?? '';
        $division = $data['division'] ?? '';
        $departamento = $data['departamento'] ?? '';
        $ubicacionStr = $data['ubicacion'] ?? '';
        
        $localizacion = null;
        if (!empty($division) && !empty($departamento)) {
            $localizacion = Localizacion::where('division', 'like', "%{$division}%")
                ->where('sub_area', 'like', "%{$departamento}%")
                ->first();
        }
        
        if (!$localizacion && !empty($direccion)) {
            $localizacion = Localizacion::where('direccion', 'like', "%{$direccion}%")->first();
        }
        
        if (!$localizacion) {
            // Crear nueva localización
            $localizacion = Localizacion::create([
                'direccion' => !empty($direccion) ? $direccion : 'Sin especificar',
                'division' => !empty($division) ? $division : 'Sin especificar',
                'sub_area' => !empty($departamento) ? $departamento : 'Sin especificar',
                'ubicacion' => !empty($ubicacionStr) ? $ubicacionStr : 'Importado',
            ]);
            echo "  [Nueva localización creada: ID {$localizacion->id}]" . PHP_EOL;
        }
        
        // Parsear precio
        $precio = 0;
        if (!empty($data['valor'])) {
            $precio = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '', $data['valor'])));
        }
        
        // Parsear fecha de baja
        $estadoMobiliario = 'Usado';
        $dadoDeBaja = false;
        $fechaBaja = null;
        
        if (!empty($data['fecha_baja'])) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($data['fecha_baja']), $matches)) {
                $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $anio = $matches[3];
                
                if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                    $fechaBaja = "{$anio}-{$mes}-{$dia}";
                    $estadoMobiliario = 'Baja';
                    $dadoDeBaja = true;
                }
            }
        }
        
        // Crear mobiliario
        $mobiliario = Mobiliario::create([
            'numero_control' => $numeroControl,
            'numero_inventario' => $data['clave_bien'] ?? null,
            'clasificacion_bienes_id' => $clasificacionId,
            'caracteristicas' => $data['caracteristicas'] ?? 'Sin características',
            'descripcion' => substr($data['nombre_bien'] ?? 'Sin descripción', 0, 255),
            'marca' => $data['marca'] ?? 'Sin marca',
            'modelo' => $data['modelo'] ?? 'Sin modelo',
            'numero_serie' => $data['numero_serie'] ?? null,
            'precio' => $precio,
            'tipo_mobiliario_id' => $tipoMobiliarioId,
            'localizacion_id' => $localizacion->id,
            'proveedor_id' => null,
            'metodo_adquisicion' => $data['metodo_adquisicion'] ?? null,
            'tiene_folio' => !empty($data['no_factura']),
            'numero_folio' => $data['no_factura'] ?? null,
            'estado_mobiliario' => $estadoMobiliario,
            'dado_de_baja' => $dadoDeBaja,
            'fecha_baja' => $fechaBaja,
            'motivo_baja' => $dadoDeBaja ? 'Importado del sistema anterior' : null,
            'tiene_accesorios' => false,
            'responsable_actual' => $data['responsable'] ?? null,
            'matricula_responsable' => $data['clave_empleado'] ?? null,
            'puesto_responsable' => $data['puesto'] ?? null,
            'version' => 1,
            'depreciacion_registrada' => 0,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        
        DB::commit();
        
        echo "  ✅ Mobiliario creado: ID {$mobiliario->id}" . PHP_EOL;
        $successCount++;
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "  ❌ Error: " . $e->getMessage() . PHP_EOL;
        $errors[] = "Fila {$rowCount}: " . $e->getMessage();
        $errorCount++;
    }
}

fclose($handle);

echo PHP_EOL . "=== Resumen ===" . PHP_EOL;
echo "Filas procesadas: {$rowCount}" . PHP_EOL;
echo "Éxitos: {$successCount}" . PHP_EOL;
echo "Errores: {$errorCount}" . PHP_EOL;

$countDespues = Mobiliario::count();
echo "Mobiliarios después de la prueba: {$countDespues}" . PHP_EOL;
echo "Nuevos registros: " . ($countDespues - $countAntes) . PHP_EOL;

if (!empty($errors)) {
    echo PHP_EOL . "=== Errores ===" . PHP_EOL;
    foreach ($errors as $error) {
        echo "  - {$error}" . PHP_EOL;
    }
}

echo PHP_EOL . "Prueba finalizada." . PHP_EOL;
