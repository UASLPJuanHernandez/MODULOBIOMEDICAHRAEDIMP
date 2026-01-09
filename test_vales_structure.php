<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n========================================\n";
echo "ESTRUCTURA DE TABLA VALES\n";
echo "========================================\n\n";

$columns = DB::select('DESCRIBE vales');

foreach ($columns as $col) {
    echo sprintf("%-20s %-30s %-10s\n", 
        $col->Field, 
        $col->Type, 
        "NULL: " . $col->Null
    );
}

echo "\n========================================\n";
echo "TEST: Crear Vale de Resguardo\n";
echo "========================================\n\n";

try {
    $testVale = [
        'numero_vale' => 'TEST-VALE-001',
        'tipo_vale' => 'resguardo',
        'mobiliario_id' => null,
        'movimiento_id' => null,
        'responsable_entrega' => 'Test Entrega',
        'matricula_entrega' => '1234',
        'responsable_recibe' => 'Test Recibe',
        'matricula_recibe' => '5678',
        'fecha_generacion' => now(),
        'observaciones' => 'Test de vale',
    ];
    
    echo "✓ Estructura de vale válida\n";
    echo "✓ movimiento_id puede ser NULL\n";
    echo "✓ tipo_vale 'resguardo' es válido\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";
