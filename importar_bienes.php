<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$csvFile = $argv[1] ?? 'bienes_importar.csv';

if (!file_exists($csvFile)) {
    echo "❌ Archivo no encontrado: {$csvFile}\n";
    exit(1);
}

echo "📥 Importando desde: {$csvFile}\n\n";

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle);

// Detectar columnas
$columnMap = [
    'clave_bien' => null,
    'nombre_bien' => null,
    'grupo' => null,
    'subgrupo' => null,
    'clase' => null,
    'marca' => null,
    'modelo' => null,
    'color' => null,
    'numero_serie' => null,
    'no_factura' => null,
    'proveedor' => null,
    'metodo_adquisicion' => null,
    'fecha_factura' => null,
    'fecha_baja' => null,
    'valor' => null,
    'fecha_registro' => null,
    'ubicacion' => null,
    'caracteristicas' => null,
    'procedencia' => null,
    'direccion' => null,
    'division' => null,
    'departamento' => null,
    'responsable' => null,
    'clave_empleado' => null,
    'puesto' => null,
];

foreach ($headers as $index => $header) {
    $header = trim($header);
    if (stripos($header, 'Clave del Bien') !== false) $columnMap['clave_bien'] = $index;
    if (stripos($header, 'Nombre del Bien') !== false) $columnMap['nombre_bien'] = $index;
    if ($header === 'Grupo') $columnMap['grupo'] = $index;
    if ($header === 'Subgrupo') $columnMap['subgrupo'] = $index;
    if ($header === 'Clase') $columnMap['clase'] = $index;
    if ($header === 'Marca') $columnMap['marca'] = $index;
    if ($header === 'Modelo') $columnMap['modelo'] = $index;
    if ($header === 'Color') $columnMap['color'] = $index;
    if (stripos($header, 'N. de Serie') !== false) $columnMap['numero_serie'] = $index;
    if (stripos($header, 'No Factura') !== false) $columnMap['no_factura'] = $index;
    if ($header === 'Proveedor') $columnMap['proveedor'] = $index;
    if (stripos($header, 'F. Adquisici') !== false) $columnMap['metodo_adquisicion'] = $index;
    if (stripos($header, 'F. de Factura') !== false) $columnMap['fecha_factura'] = $index;
    if (stripos($header, 'F. de  Baja') !== false) $columnMap['fecha_baja'] = $index;
    if ($header === 'Valor') $columnMap['valor'] = $index;
    if (stripos($header, 'F. Registro') !== false) $columnMap['fecha_registro'] = $index;
    if ($header === 'Ubicacion') $columnMap['ubicacion'] = $index;
    if ($header === 'Caracteristicas') $columnMap['caracteristicas'] = $index;
    if ($header === 'Procedencia') $columnMap['procedencia'] = $index;
    if (stripos($header, 'Direcci') !== false) $columnMap['direccion'] = $index;
    if (stripos($header, 'Divisi') !== false) $columnMap['division'] = $index;
    if ($header === 'Departamento') $columnMap['departamento'] = $index;
    if ($header === 'Responsable') $columnMap['responsable'] = $index;
    if (stripos($header, 'Clave Emp') !== false) $columnMap['clave_empleado'] = $index;
    if ($header === 'Puesto') $columnMap['puesto'] = $index;
}

echo "✅ Columnas mapeadas correctamente\n\n";

// Saltar segunda línea de encabezados si existe
$segundaLinea = fgetcsv($handle);

$rowCount = 0;
$successCount = 0;
$errorCount = 0;

while (($row = fgetcsv($handle)) !== false) {
    // Extraer datos
    $data = [];
    foreach ($columnMap as $key => $index) {
        $data[$key] = isset($row[$index]) ? trim($row[$index]) : null;
    }
    
    // Validar que tenga clave y nombre
    if (empty($data['clave_bien']) || empty($data['nombre_bien'])) {
        continue;
    }
    
    $rowCount++;
    echo "Procesando fila {$rowCount}: {$data['nombre_bien']} (#{$data['clave_bien']})...\n";
    
    try {
        DB::beginTransaction();
        
        // Generar número de control
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $numeroControl = "IMP-{$timestamp}-{$random}-{$rowCount}";
        
        // Resolver clasificación
        $clasificacionId = 1;
        if (!empty($data['grupo']) && !empty($data['subgrupo']) && !empty($data['clase'])) {
            $grupo = intval($data['grupo']);
            $subgrupo = intval($data['subgrupo']);
            $clase = intval($data['clase']);
            
            $clasificacion = ClasificacionBien::where('grupo', $grupo)
                ->where('subgrupo', $subgrupo)
                ->where('clase', $clase)
                ->first();
            
            if ($clasificacion) {
                $clasificacionId = $clasificacion->id;
            }
        }
        
        // Resolver localización (crear si no existe)
        $direccion = $data['direccion'] ?? 'Sin especificar';
        $division = $data['division'] ?? 'Sin especificar';
        $departamento = $data['departamento'] ?? 'Sin especificar';
        
        $localizacion = Localizacion::where('direccion', $direccion)
            ->where('division', $division)
            ->where('sub_area', $departamento)
            ->first();
        
        if (!$localizacion) {
            $localizacion = Localizacion::create([
                'direccion' => $direccion,
                'division' => $division,
                'sub_area' => $departamento,
                'ubicacion' => $data['ubicacion'] ?? 'Sin especificar',
                'created_by' => 1,
                'updated_by' => 1,
            ]);
            echo "  ➕ Creada localización: {$direccion} / {$division} / {$departamento}\n";
        }
        
        // Construir características
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
        
        // Parsear precio
        $precio = 0;
        if (!empty($data['valor'])) {
            $precio = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '', $data['valor'])));
        }
        
        // Estado basado en fecha de baja
        $estadoMobiliario = 'Usado';
        $dadoDeBaja = false;
        $fechaBaja = null;
        $motivoBaja = null;
        
        if (!empty($data['fecha_baja'])) {
            try {
                $fechaBajaParts = explode('/', $data['fecha_baja']);
                if (count($fechaBajaParts) === 3) {
                    $fechaBaja = \Carbon\Carbon::createFromDate(
                        $fechaBajaParts[2],
                        $fechaBajaParts[1],
                        $fechaBajaParts[0]
                    );
                    $estadoMobiliario = 'Baja';
                    $dadoDeBaja = true;
                    $motivoBaja = 'Importado del sistema anterior con fecha de baja';
                }
            } catch (\Exception $e) {
                // Ignorar error de fecha
            }
        }
        
        // Crear mobiliario
        $mobiliario = Mobiliario::create([
            'numero_control' => $numeroControl,
            'numero_inventario' => $data['clave_bien'],
            'clasificacion_bienes_id' => $clasificacionId,
            'caracteristicas' => $caracteristicasFinal,
            'descripcion' => substr($data['nombre_bien'], 0, 255),
            'marca' => $data['marca'] ?: 'Sin marca',
            'modelo' => $data['modelo'] ?: 'Sin modelo',
            'numero_serie' => $data['numero_serie'],
            'precio' => $precio,
            'tipo_mobiliario_id' => TipoMobiliario::first()?->id ?? 1,
            'localizacion_id' => $localizacion->id,
            'proveedor_id' => null,
            'metodo_adquisicion' => $data['metodo_adquisicion'] ?: 'Otros',
            'tiene_folio' => !empty($data['no_factura']),
            'numero_folio' => $data['no_factura'],
            'estado_mobiliario' => $estadoMobiliario,
            'dado_de_baja' => $dadoDeBaja,
            'fecha_baja' => $fechaBaja,
            'motivo_baja' => $motivoBaja,
            'tiene_accesorios' => false,
            'responsable_actual' => $data['responsable'],
            'matricula_responsable' => $data['clave_empleado'],
            'puesto_responsable' => $data['puesto'],
            'version' => 1,
            'depreciacion_registrada' => 0,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        
        DB::commit();
        
        echo "  ✅ Creado ID: {$mobiliario->id}\n";
        $successCount++;
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $errorCount++;
    }
    
    echo "\n";
}

fclose($handle);

echo "\n=== RESUMEN ===\n";
echo "Total procesadas: {$rowCount}\n";
echo "✅ Exitosas: {$successCount}\n";
echo "❌ Errores: {$errorCount}\n";
echo "\nTotal mobiliarios: " . Mobiliario::count() . "\n";
