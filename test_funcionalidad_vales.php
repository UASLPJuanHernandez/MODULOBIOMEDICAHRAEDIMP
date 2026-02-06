<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vale;
use App\Models\Mobiliario;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE FUNCIONALIDAD DE VALES ===\n\n";

// Test 1: Crear mobiliario con vale de resguardo
echo "TEST 1: Crear Vale de Resguardo\n";
echo str_repeat("-", 50) . "\n";

DB::beginTransaction();

try {
    $mobiliario = Mobiliario::first();
    
    if (!$mobiliario) {
        echo "⚠️  No hay mobiliarios disponibles\n";
    } else {
        $year = now()->year;
        $ultimoVale = Vale::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $numeroSecuencial = $ultimoVale ? (intval(substr($ultimoVale->numero_vale, -4)) + 1) : 1;
        $numeroVale = 'VALE-' . $year . '-' . str_pad($numeroSecuencial, 4, '0', STR_PAD_LEFT);
        
        $vale = Vale::create([
            'numero_vale' => $numeroVale,
            'tipo_vale' => 'resguardo',
            'mobiliario_id' => $mobiliario->id,
            'responsable_entrega' => 'Vicente Zarazua',
            'matricula_entrega' => '3782',
            'responsable_recibe' => 'Usuario Final',
            'matricula_recibe' => '0000',
            'fecha_generacion' => now(),
            'observaciones' => 'Vale de prueba - resguardo',
            // Sin movimiento_id
        ]);
        
        echo "✓ Vale de resguardo creado: {$vale->numero_vale}\n";
        echo "  ID: {$vale->id}\n";
        echo "  Tipo: {$vale->tipo_vale}\n";
        echo "  Mobiliario: {$mobiliario->descripcion}\n";
        echo "  movimiento_id: " . ($vale->movimiento_id ?? 'NULL') . " ✓\n";
    }
    
    DB::rollBack();
    echo "\n✓ TEST 1 EXITOSO\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ TEST 1 FALLIDO: " . $e->getMessage() . "\n\n";
}

// Test 2: Crear vale con movimiento
echo "TEST 2: Crear Vale con Movimiento\n";
echo str_repeat("-", 50) . "\n";

DB::beginTransaction();

try {
    $mobiliario = Mobiliario::first();
    $movimiento = Movimiento::first();
    
    if (!$mobiliario || !$movimiento) {
        echo "⚠️  Faltan datos (mobiliario o movimiento)\n";
    } else {
        $vale = Vale::create([
            'numero_vale' => 'VALE-TEST-' . time(),
            'tipo_vale' => 'entrega',
            'mobiliario_id' => $mobiliario->id,
            'movimiento_id' => $movimiento->id,
            'responsable_entrega' => 'Almacén',
            'matricula_entrega' => '1111',
            'responsable_recibe' => 'Departamento',
            'matricula_recibe' => '2222',
            'fecha_generacion' => now(),
            'observaciones' => 'Vale con movimiento',
        ]);
        
        echo "✓ Vale con movimiento creado: {$vale->numero_vale}\n";
        echo "  Movimiento ID: {$vale->movimiento_id}\n";
    }
    
    DB::rollBack();
    echo "\n✓ TEST 2 EXITOSO\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ TEST 2 FALLIDO: " . $e->getMessage() . "\n\n";
}

// Test 3: Relación many-to-many vale-mobiliario
echo "TEST 3: Relación Vale-Mobiliario (Many-to-Many)\n";
echo str_repeat("-", 50) . "\n";

DB::beginTransaction();

try {
    $vale = Vale::create([
        'numero_vale' => 'VALE-MULTI-' . time(),
        'tipo_vale' => 'entrega',
        'responsable_entrega' => 'Test',
        'matricula_entrega' => '0001',
        'responsable_recibe' => 'Test',
        'matricula_recibe' => '0002',
        'fecha_generacion' => now(),
    ]);
    
    $mobiliarios = Mobiliario::take(3)->pluck('id')->toArray();
    
    if (count($mobiliarios) > 0) {
        $vale->mobiliarios()->attach($mobiliarios);
        
        echo "✓ Vale creado con múltiples mobiliarios\n";
        echo "  Vale ID: {$vale->id}\n";
        echo "  Mobiliarios asociados: " . count($mobiliarios) . "\n";
        
        // Verificar la relación inversa
        foreach ($mobiliarios as $mobId) {
            $mob = Mobiliario::find($mobId);
            $valesDelMobiliario = $mob->vales()->count();
            echo "  - Mobiliario {$mobId}: {$valesDelMobiliario} vale(s)\n";
        }
    }
    
    DB::rollBack();
    echo "\n✓ TEST 3 EXITOSO\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ TEST 3 FALLIDO: " . $e->getMessage() . "\n\n";
}

// Test 4: Consultas y filtros
echo "TEST 4: Consultas y Filtros\n";
echo str_repeat("-", 50) . "\n";

try {
    // Vales por tipo
    $valesPorTipo = Vale::selectRaw('tipo_vale, COUNT(*) as total')
        ->groupBy('tipo_vale')
        ->get();
    
    echo "Vales por tipo:\n";
    foreach ($valesPorTipo as $tipo) {
        echo "  - {$tipo->tipo_vale}: {$tipo->total}\n";
    }
    
    // Vales recientes
    $valesRecientes = Vale::orderBy('created_at', 'desc')->take(5)->get();
    echo "\nÚltimos 5 vales:\n";
    foreach ($valesRecientes as $vale) {
        echo "  - {$vale->numero_vale} ({$vale->tipo_vale}) - {$vale->created_at->format('Y-m-d')}\n";
    }
    
    echo "\n✓ TEST 4 EXITOSO\n\n";
    
} catch (\Exception $e) {
    echo "✗ TEST 4 FALLIDO: " . $e->getMessage() . "\n\n";
}

echo "=== FIN DE PRUEBAS DE FUNCIONALIDAD ===\n";
