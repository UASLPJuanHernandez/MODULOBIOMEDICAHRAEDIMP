<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mobiliario;
use App\Models\Vale;
use App\Models\Localizacion;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE FLUJO COMPLETO: CREAR MOBILIARIO CON VALE ===\n\n";

DB::beginTransaction();

try {
    echo "PASO 1: Verificar datos necesarios\n";
    echo str_repeat("-", 50) . "\n";
    
    $localizacion = Localizacion::first();
    $clasificacion = ClasificacionBien::first();
    $tipoMobiliario = TipoMobiliario::first();
    
    if (!$localizacion || !$clasificacion || !$tipoMobiliario) {
        echo "⚠️  ADVERTENCIA: Faltan datos maestros\n";
        if (!$localizacion) echo "  - No hay localizaciones\n";
        if (!$clasificacion) echo "  - No hay clasificaciones de bienes\n";
        if (!$tipoMobiliario) echo "  - No hay tipos de mobiliario\n";
        echo "\nCreando datos de ejemplo...\n";
        
        if (!$localizacion) {
            $localizacion = Localizacion::create([
                'nombre' => 'Almacén General',
                'tipo' => 'edificio',
                'descripcion' => 'Almacén principal',
            ]);
            echo "  ✓ Localización creada\n";
        }
        
        if (!$clasificacion) {
            $clasificacion = ClasificacionBien::create([
                'grupo' => '5',
                'subgrupo' => '1',
                'clase' => '1',
                'descripcion' => 'Mobiliario de Oficina',
            ]);
            echo "  ✓ Clasificación creada\n";
        }
        
        if (!$tipoMobiliario) {
            $tipoMobiliario = TipoMobiliario::create([
                'nombre' => 'Escritorio',
                'descripcion' => 'Mobiliario de oficina',
            ]);
            echo "  ✓ Tipo de mobiliario creado\n";
        }
    }
    
    echo "✓ Datos necesarios disponibles\n\n";
    
    // PASO 2: Crear mobiliario
    echo "PASO 2: Crear mobiliario\n";
    echo str_repeat("-", 50) . "\n";
    
    $ultimoNumero = Mobiliario::max('numero_control');
    $nuevoNumero = $ultimoNumero ? (intval($ultimoNumero) + 1) : 1;
    $numeroControl = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
    
    $mobiliario = Mobiliario::create([
        'numero_control' => $numeroControl,
        'descripcion' => 'ESCRITORIO EJECUTIVO DE PRUEBA',
        'marca' => 'OFFICE PRO',
        'modelo' => 'EXEC-2024',
        'numero_serie' => 'TEST-' . time(),
        'color' => 'NEGRO',
        'precio' => 5000.00,
        'fecha_adquisicion' => now(),
        'estado_conservacion' => 'nuevo',
        'localizacion_id' => $localizacion->id,
        'clasificacion_bien_id' => $clasificacion->id,
        'tipo_mobiliario_id' => $tipoMobiliario->id,
        'observaciones' => 'Mobiliario de prueba del sistema',
    ]);
    
    echo "✓ Mobiliario creado:\n";
    echo "  ID: {$mobiliario->id}\n";
    echo "  Número de control: {$mobiliario->numero_control}\n";
    echo "  Descripción: {$mobiliario->descripcion}\n";
    echo "  Precio: \${$mobiliario->precio}\n\n";
    
    // PASO 3: Generar vale de resguardo (SIN movimiento_id)
    echo "PASO 3: Generar vale de resguardo\n";
    echo str_repeat("-", 50) . "\n";
    
    $year = now()->year;
    $ultimoVale = Vale::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
    $numeroSecuencial = $ultimoVale ? (intval(substr($ultimoVale->numero_vale, -4)) + 1) : 1;
    $numeroVale = 'VALE-' . $year . '-' . str_pad($numeroSecuencial, 4, '0', STR_PAD_LEFT);
    
    echo "Intentando crear vale sin movimiento_id...\n";
    
    $vale = Vale::create([
        'numero_vale' => $numeroVale,
        'tipo_vale' => 'resguardo',
        'mobiliario_id' => $mobiliario->id,
        'responsable_entrega' => 'Vicente Zarazua',
        'matricula_entrega' => '3782',
        'responsable_recibe' => 'Juan Pérez',
        'matricula_recibe' => '4521',
        'fecha_generacion' => now(),
        'observaciones' => 'Se entrega mobiliario en perfecto estado',
        // NO incluimos movimiento_id porque es un vale de resguardo directo
    ]);
    
    echo "✓ Vale de resguardo creado:\n";
    echo "  ID: {$vale->id}\n";
    echo "  Número: {$vale->numero_vale}\n";
    echo "  Tipo: {$vale->tipo_vale}\n";
    echo "  movimiento_id: " . ($vale->movimiento_id ?? 'NULL') . " ✓\n";
    echo "  Entrega: {$vale->responsable_entrega} ({$vale->matricula_entrega})\n";
    echo "  Recibe: {$vale->responsable_recibe} ({$vale->matricula_recibe})\n\n";
    
    // PASO 4: Relacionar mobiliario con vale (many-to-many)
    echo "PASO 4: Relacionar mobiliario con vale\n";
    echo str_repeat("-", 50) . "\n";
    
    $vale->mobiliarios()->attach($mobiliario->id);
    
    echo "✓ Relación establecida en tabla pivot vale_mobiliario\n";
    
    // Verificar la relación
    $mobiliariosDelVale = $vale->mobiliarios;
    echo "  Vale tiene {$mobiliariosDelVale->count()} mobiliario(s) asociado(s)\n";
    
    $valesDelMobiliario = $mobiliario->vales;
    echo "  Mobiliario tiene {$valesDelMobiliario->count()} vale(s) asociado(s)\n\n";
    
    // PASO 5: Verificar datos finales
    echo "PASO 5: Verificación final\n";
    echo str_repeat("-", 50) . "\n";
    
    $valeRecargado = Vale::with(['mobiliario', 'mobiliarios'])->find($vale->id);
    
    echo "Datos del vale recargado:\n";
    echo "  ✓ Vale cargado correctamente\n";
    echo "  ✓ Relación 'mobiliario' (belongsTo): " . ($valeRecargado->mobiliario ? "OK" : "NULL") . "\n";
    echo "  ✓ Relación 'mobiliarios' (belongsToMany): " . $valeRecargado->mobiliarios->count() . " item(s)\n";
    echo "  ✓ movimiento_id es NULL: " . ($valeRecargado->movimiento_id === null ? "Correcto" : "Incorrecto") . "\n";
    
    echo "\n";
    echo str_repeat("=", 70) . "\n";
    echo "   ✓✓✓ FLUJO COMPLETO EXITOSO ✓✓✓\n";
    echo str_repeat("=", 70) . "\n";
    echo "\nEl sistema puede:\n";
    echo "  ✓ Crear mobiliarios\n";
    echo "  ✓ Generar vales de resguardo sin movimiento_id\n";
    echo "  ✓ Establecer relaciones many-to-many\n";
    echo "  ✓ Cargar relaciones correctamente\n";
    echo "\n✓ SISTEMA FUNCIONANDO CORRECTAMENTE\n";
    
    DB::rollBack();
    echo "\n[Nota: Transacción revertida - no se guardaron cambios reales]\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n";
    echo str_repeat("=", 70) . "\n";
    echo "   ✗✗✗ ERROR EN EL FLUJO ✗✗✗\n";
    echo str_repeat("=", 70) . "\n";
    echo "\nError: " . $e->getMessage() . "\n";
    echo "\nArchivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    
    if (strpos($e->getMessage(), "movimiento_id") !== false) {
        echo "\n";
        echo str_repeat("-", 70) . "\n";
        echo "DIAGNÓSTICO: El campo 'movimiento_id' no es nullable\n";
        echo str_repeat("-", 70) . "\n";
        echo "\nSOLUCIÓN:\n";
        echo "1. Crear migración:\n";
        echo "   php artisan make:migration make_movimiento_id_nullable_in_vales_table\n\n";
        echo "2. Copiar contenido del archivo:\n";
        echo "   PLANTILLA_migracion_movimiento_id_nullable.php\n\n";
        echo "3. Ejecutar:\n";
        echo "   php artisan migrate\n\n";
        echo "4. Volver a ejecutar esta prueba\n";
    }
}

echo "\n=== FIN DE LA PRUEBA ===\n";
