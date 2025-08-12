<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vale;

echo "=== Prueba de URL PDF para Vale ===\n";

try {
    // Buscar un vale específico con múltiples mobiliarios
    $vale = Vale::where('id', 15)->with(['mobiliarios', 'movimiento', 'mobiliario'])->first();
    
    if ($vale) {
        echo "Vale encontrado: " . $vale->numero_vale_formateado . "\n";
        echo "Mobiliarios múltiples: " . $vale->mobiliarios->count() . "\n";
        echo "Mobiliario individual ID: " . ($vale->mobiliario_id ?? 'NULL') . "\n";
        
        echo "\nURL del PDF: http://localhost/vale/{$vale->id}/imprimir\n";
        
        if ($vale->mobiliarios->count() > 0) {
            echo "\nMobiliarios que deberían aparecer en el PDF:\n";
            foreach ($vale->mobiliarios as $mob) {
                echo "- " . $mob->numero_control . " - " . $mob->descripcion . "\n";
            }
        }
    } else {
        echo "Vale no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
