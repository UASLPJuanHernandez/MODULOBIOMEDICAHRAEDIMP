<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;

echo "=== Prueba del Sistema de Movimientos ===\n";

try {
    // Verificar que la tabla existe y las relaciones funcionan
    echo "1. Verificando cantidad de movimientos: " . Movimiento::count() . "\n";
    echo "2. Verificando cantidad de mobiliarios: " . Mobiliario::count() . "\n";
    
    // Probar la relación
    if (Movimiento::count() > 0) {
        $movimiento = Movimiento::first();
        echo "3. Primer movimiento ID: " . $movimiento->id . "\n";
        echo "4. Número de movimiento: " . ($movimiento->numero_movimiento ?? 'No asignado') . "\n";
        echo "5. Cantidad de mobiliarios asociados: " . $movimiento->cantidad_mobiliarios . "\n";
        
        // Probar la relación many-to-many
        $mobiliarios = $movimiento->mobiliarios;
        echo "6. Mobiliarios asociados encontrados: " . $mobiliarios->count() . "\n";
    }
    
    echo "\n✅ Todas las pruebas pasaron correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
