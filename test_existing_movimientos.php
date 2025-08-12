<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;

echo "=== Verificando Movimientos Existentes ===\n";

try {
    // Obtener movimientos existentes
    $movimientos = Movimiento::with('mobiliarios')->get();
    echo "Total movimientos: " . $movimientos->count() . "\n\n";
    
    foreach ($movimientos as $mov) {
        $count = $mov->mobiliarios->count();
        echo "Movimiento: " . $mov->numero_movimiento . "\n";
        echo "Mobiliarios: " . $count . "\n";
        echo "Fecha: " . $mov->fecha_movimiento->format('d/m/Y H:i') . "\n";
        
        if ($count > 0) {
            echo "Detalle mobiliarios:\n";
            foreach ($mov->mobiliarios as $index => $mob) {
                echo "  " . ($index + 1) . ". " . $mob->numero_control . " - " . $mob->descripcion . "\n";
            }
        }
        
        echo "---\n";
        
        // Si encontramos uno con múltiples mobiliarios, lo usamos para probar
        if ($count > 1) {
            echo "\n=== ENCONTRADO MOVIMIENTO MÚLTIPLE ===\n";
            echo "Probando Vale con: " . $mov->numero_movimiento . "\n";
            break;
        }
    }
    
    // Si no encontramos movimientos múltiples, vamos a agregar mobiliarios a uno existente
    if ($movimientos->count() > 0) {
        $primerMovimiento = $movimientos->first();
        $mobiliarios = Mobiliario::limit(3)->get();
        
        echo "\n=== Agregando mobiliarios a movimiento existente ===\n";
        echo "Movimiento: " . $primerMovimiento->numero_movimiento . "\n";
        echo "Mobiliarios actuales: " . $primerMovimiento->mobiliarios->count() . "\n";
        
        if ($mobiliarios->count() >= 2) {
            // Agregar hasta 2 mobiliarios más
            $nuevosIds = $mobiliarios->take(2)->pluck('id')->toArray();
            $primerMovimiento->mobiliarios()->syncWithoutDetaching($nuevosIds);
            
            // Recargar
            $primerMovimiento->load('mobiliarios');
            echo "Mobiliarios después: " . $primerMovimiento->mobiliarios->count() . "\n";
            
            foreach ($primerMovimiento->mobiliarios as $index => $mob) {
                echo "  " . ($index + 1) . ". " . $mob->numero_control . " - " . $mob->descripcion . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
