<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use App\Models\Proveedor;
use Illuminate\Support\Str;

echo "=== TEST DE IMPORTACIÓN LEGACY ===\n\n";

// Datos de prueba de la primera fila del CSV
$testData = [
    'clave_bien' => '42',
    'nombre_bien' => 'MESA PARA MAQUINA DE ESCRIBIR',
    'grupo' => '5',
    'subgrupo' => '1',
    'clase' => '1',
    'marca' => 'POLOMATIC',
    'modelo' => 'SIN MODELO',
    'numero_serie' => 'SIN SERIE',
    'no_factura' => null,
    'proveedor' => '0',
    'metodo_adquisicion' => 'COMPRA',
    'fecha_baja' => null,
    'valor' => '450',
    'direccion' => 'DIRECCION GENERAL',
];

try {
    echo "1. Verificando registros existentes...\n";
    echo "   - Clasificaciones: " . ClasificacionBien::count() . "\n";
    echo "   - Tipos: " . TipoMobiliario::count() . "\n";
    echo "   - Localizaciones: " . Localizacion::count() . "\n";
    echo "   - Proveedores: " . Proveedor::count() . "\n\n";
    
    echo "2. Creando registro de prueba...\n";
    
    $mobiliario = new Mobiliario();
    
    // Generar número de control
    $timestamp = now()->format('YmdHis');
    $random = strtoupper(Str::random(4));
    $mobiliario->numero_control = "TEST-{$timestamp}-{$random}";
    
    // Datos básicos
    $mobiliario->numero_inventario = $testData['clave_bien'];
    $mobiliario->descripcion = substr($testData['nombre_bien'], 0, 255);
    
    // Características
    $caracteristicas = [
        "Grupo: {$testData['grupo']}",
        "Subgrupo: {$testData['subgrupo']}",
        "Clase: {$testData['clase']}"
    ];
    $mobiliario->caracteristicas = implode(', ', $caracteristicas);
    
    $mobiliario->marca = $testData['marca'] ?: 'Sin marca';
    $mobiliario->modelo = $testData['modelo'] ?: 'Sin modelo';
    $mobiliario->numero_serie = $testData['numero_serie'];
    $mobiliario->precio = floatval($testData['valor']);
    $mobiliario->metodo_adquisicion = $testData['metodo_adquisicion'];
    $mobiliario->numero_folio = $testData['no_factura'];
    $mobiliario->tiene_folio = !empty($testData['no_factura']);
    $mobiliario->estado_mobiliario = 'Usado';
    $mobiliario->dado_de_baja = false;
    $mobiliario->tiene_accesorios = false;
    
    // Relaciones
    echo "3. Resolviendo relaciones...\n";
    
    $clasificacion = ClasificacionBien::first();
    if (!$clasificacion) {
        echo "   ⚠ No hay clasificaciones, creando una por defecto...\n";
        $clasificacion = ClasificacionBien::create([
            'grupo' => 0,
            'subgrupo' => 0,
            'clase' => 0,
            'nombre_grupo' => 'General',
            'descripcion_clase' => 'Sin clasificar',
        ]);
    }
    $mobiliario->clasificacion_bienes_id = $clasificacion->id;
    echo "   ✓ Clasificación: {$clasificacion->id}\n";
    
    $tipo = TipoMobiliario::first();
    if (!$tipo) {
        echo "   ⚠ No hay tipos, creando uno por defecto...\n";
        $tipo = TipoMobiliario::create([
            'tipo' => 'General',
            'numero_secuencial' => 1,
        ]);
    }
    $mobiliario->tipo_mobiliario_id = $tipo->id;
    echo "   ✓ Tipo: {$tipo->id}\n";
    
    $localizacion = Localizacion::first();
    if (!$localizacion) {
        echo "   ⚠ No hay localizaciones, creando una por defecto...\n";
        $localizacion = Localizacion::create([
            'nombre' => 'Sin especificar',
            'siglas' => 'SE',
            'descripcion' => 'Sin especificar',
        ]);
    }
    $mobiliario->localizacion_id = $localizacion->id;
    echo "   ✓ Localización: {$localizacion->id}\n";
    
    // Valores por defecto
    $mobiliario->version = 1;
    $mobiliario->depreciacion_registrada = 0;
    $mobiliario->created_by = 1;
    $mobiliario->updated_by = 1;
    
    echo "\n4. Guardando registro...\n";
    $mobiliario->save();
    
    echo "✅ ¡Registro creado exitosamente!\n";
    echo "   ID: {$mobiliario->id}\n";
    echo "   Número de control: {$mobiliario->numero_control}\n";
    echo "   Descripción: {$mobiliario->descripcion}\n\n";
    
    echo "5. Eliminando registro de prueba...\n";
    $mobiliario->delete();
    echo "✅ Registro eliminado\n\n";
    
    echo "=== TEST COMPLETADO EXITOSAMENTE ===\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
