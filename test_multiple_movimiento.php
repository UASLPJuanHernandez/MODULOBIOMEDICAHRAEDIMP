<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;

echo "=== Creando Movimiento con Múltiples Mobiliarios ===\n";

try {
    // Obtener mobiliarios disponibles
    $mobiliarios = Mobiliario::limit(3)->get();
    echo "Mobiliarios disponibles: " . $mobiliarios->count() . "\n";
    
    if ($mobiliarios->count() >= 2) {
        // Crear movimiento
        $movimiento = Movimiento::create([
            'area_actual_id' => 1,
            'area_anterior_id' => 2,
            'fecha_movimiento' => now(),
            'observacion' => 'Prueba múltiples mobiliarios para Vale - AUTO',
            'usuario_id' => 1
        ]);
        
        echo "Movimiento creado: " . $movimiento->numero_movimiento . "\n";
        
        // Asociar múltiples mobiliarios
        $mobiliarioIds = $mobiliarios->take(2)->pluck('id')->toArray();
        $movimiento->mobiliarios()->attach($mobiliarioIds);
        
        // Recargar para verificar
        $movimiento->load('mobiliarios');
        
        echo "Mobiliarios asociados: " . $movimiento->mobiliarios->count() . "\n";
        foreach ($movimiento->mobiliarios as $index => $mob) {
            echo "  " . ($index + 1) . ". " . $mob->numero_control . " - " . $mob->descripcion . "\n";
        }
        
        echo "\n=== Probando Vale con este Movimiento ===\n";
        echo "Número de movimiento: " . $movimiento->numero_movimiento . "\n";
        echo "Fecha: " . $movimiento->fecha_movimiento->format('d/m/Y H:i') . "\n";
        echo "Cantidad de items: " . $movimiento->cantidad_mobiliarios . "\n";
        
        echo "\nDatos que se auto-poblarían en el Vale:\n";
        foreach ($movimiento->mobiliarios as $index => $mob) {
            echo "Mobiliario " . ($index + 1) . ":\n";
            echo "  - ID: " . $mob->id . "\n";
            echo "  - Número Control: " . $mob->numero_control . "\n";
            echo "  - Descripción: " . $mob->descripcion . "\n";
            echo "  - Marca: " . ($mob->marca ?? 'N/A') . "\n";
            echo "  - Modelo: " . ($mob->modelo ?? 'N/A') . "\n";
            echo "  - Número Serie: " . ($mob->numero_serie ?? 'N/A') . "\n\n";
        }
        
    } else {
        echo "No hay suficientes mobiliarios para crear el movimiento múltiple\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
