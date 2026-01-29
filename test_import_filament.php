#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use Illuminate\Support\Facades\Log;

// Leer el CSV
$csvFile = $argv[1] ?? 'bienes_importar.csv';
if (!file_exists($csvFile)) {
    die("❌ Archivo CSV no encontrado: $csvFile\n");
}

$file = fopen($csvFile, 'r');
$headers = fgetcsv($file);

echo "📋 Cabeceras encontradas:\n";
foreach ($headers as $index => $header) {
    echo "  [$index] " . trim($header) . "\n";
}
echo "\n";

// Procesar primera fila de datos
$rowData = fgetcsv($file);
fclose($file);

echo "📊 Primera fila de datos:\n";
foreach ($headers as $index => $header) {
    $value = $rowData[$index] ?? '';
    echo "  " . trim($header) . " => " . (strlen($value) > 50 ? substr($value, 0, 47) . '...' : $value) . "\n";
}
echo "\n";

// Mapear datos como lo haría el importer
$data = [];
foreach ($headers as $index => $header) {
    $data[trim($header)] = $rowData[$index] ?? '';
}

echo "🔧 Procesando datos...\n\n";

// Test 1: Clasificación
echo "1️⃣ Resolución de Clasificación:\n";
try {
    if (!empty($data['Grupo']) && !empty($data['Subgrupo']) && !empty($data['Clase'])) {
        $clasificacion = ClasificacionBien::where('grupo', intval($data['Grupo']))
            ->where('subgrupo', intval($data['Subgrupo']))
            ->where('clase', intval($data['Clase']))
            ->first();
        
        if ($clasificacion) {
            echo "  ✅ Clasificación encontrada: ID {$clasificacion->id}\n";
        } else {
            echo "  ⚠️  No se encontró clasificación, usando ID 1\n";
        }
    }
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Tipo Mobiliario
echo "\n2️⃣ Resolución de Tipo Mobiliario:\n";
try {
    $tipo = TipoMobiliario::first();
    if ($tipo) {
        echo "  ✅ Tipo encontrado: ID {$tipo->id} - {$tipo->tipo}\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Localización
echo "\n3️⃣ Resolución de Localización:\n";
try {
    $direccion = $data['Direccion'] ?? null;
    if (!empty($direccion)) {
        echo "  🔍 Buscando: '$direccion'\n";
        $localizacion = Localizacion::where('direccion', 'like', "%{$direccion}%")
            ->orWhere('division', 'like', "%{$direccion}%")
            ->first();
        
        if ($localizacion) {
            echo "  ✅ Localización encontrada: ID {$localizacion->id}\n";
        } else {
            echo "  ⚠️  No se encontró localización\n";
            $primera = Localizacion::first();
            if ($primera) {
                echo "  ℹ️  Primera localización disponible: ID {$primera->id}\n";
            }
        }
    } else {
        echo "  ⚠️  Sin dirección en datos\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Parseo de fechas
echo "\n4️⃣ Parseo de Fechas:\n";
$fechas = ['F. de Factura', 'F. de  Baja'];
foreach ($fechas as $campo) {
    if (isset($data[$campo]) && !empty($data[$campo])) {
        echo "  📅 $campo: {$data[$campo]}\n";
        
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($data[$campo]), $matches)) {
            $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $anio = $matches[3];
            
            if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                $fechaConvertida = "{$anio}-{$mes}-{$dia}";
                echo "    ✅ Convertido a: $fechaConvertida\n";
                
                // Intentar crear una instancia Carbon
                try {
                    $carbon = \Carbon\Carbon::parse($fechaConvertida);
                    echo "    ✅ Carbon válido: " . $carbon->toDateString() . "\n";
                } catch (\Exception $e) {
                    echo "    ❌ Error Carbon: " . $e->getMessage() . "\n";
                }
            } else {
                echo "    ❌ Fecha inválida\n";
            }
        } else {
            echo "    ⚠️  Formato no reconocido\n";
        }
    }
}

// Test 5: Parseo de valor
echo "\n5️⃣ Parseo de Valor:\n";
if (isset($data['Valor'])) {
    $valorOriginal = $data['Valor'];
    echo "  💰 Valor original: '$valorOriginal'\n";
    
    $valorLimpio = preg_replace('/[^0-9.]/', '', (string)$valorOriginal);
    $valorFinal = floatval($valorLimpio);
    
    echo "  ✅ Valor procesado: $valorFinal\n";
}

// Test 6: Intentar crear registro
echo "\n6️⃣ Intento de creación de registro:\n";
try {
    $mobiliario = new Mobiliario();
    $mobiliario->numero_control = 'TEST-' . time();
    $mobiliario->numero_inventario = $data['Clave del Bien'] ?? null;
    $mobiliario->descripcion = substr($data['Nombre del Bien'] ?? 'Test', 0, 255);
    $mobiliario->caracteristicas = 'Test import';
    $mobiliario->marca = $data['Marca'] ?? 'Sin marca';
    $mobiliario->modelo = $data['Modelo'] ?? 'Sin modelo';
    
    // Valor
    $precio = 0;
    if (isset($data['Valor'])) {
        $precio = floatval(preg_replace('/[^0-9.]/', '', (string)$data['Valor']));
    }
    $mobiliario->precio = max(0, $precio);
    
    $mobiliario->metodo_adquisicion = $data['F. Adquisicion'] ?? null;
    $mobiliario->estado_mobiliario = 'Usado';
    $mobiliario->dado_de_baja = false;
    $mobiliario->tiene_accesorios = false;
    $mobiliario->tiene_folio = false;
    
    // Relaciones
    $mobiliario->clasificacion_bienes_id = 1;
    $mobiliario->tipo_mobiliario_id = 1;
    $mobiliario->localizacion_id = 1;
    
    $mobiliario->version = 1;
    $mobiliario->depreciacion_registrada = 0;
    $mobiliario->created_by = 1;
    $mobiliario->updated_by = 1;
    
    // Fecha de baja si existe
    if (!empty($data['F. de  Baja']) && trim($data['F. de  Baja']) !== '') {
        echo "  🔍 Procesando fecha de baja: '{$data['F. de  Baja']}'\n";
        
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($data['F. de  Baja']), $matches)) {
            $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $anio = $matches[3];
            
            if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                $fechaBaja = "{$anio}-{$mes}-{$dia}";
                echo "  📅 Fecha de baja convertida: $fechaBaja\n";
                
                try {
                    $mobiliario->fecha_baja = $fechaBaja;
                    $mobiliario->estado_mobiliario = 'Baja';
                    $mobiliario->dado_de_baja = true;
                    $mobiliario->motivo_baja = 'Test import';
                    echo "  ✅ Fecha de baja asignada\n";
                } catch (\Exception $e) {
                    echo "  ❌ Error asignando fecha: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n  🔍 Validando modelo antes de guardar...\n";
    $mobiliario->save();
    
    echo "  ✅ Registro creado exitosamente: ID {$mobiliario->id}\n";
    echo "  🗑️  Eliminando registro de prueba...\n";
    $mobiliario->delete();
    echo "  ✅ Prueba completada con éxito\n";
    
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
    echo "  📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if ($e instanceof \Illuminate\Database\QueryException) {
        echo "  🔍 SQL: " . $e->getSql() . "\n";
    }
}

echo "\n✅ Diagnóstico completado\n";
