<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vale;

echo "=== Verificación de Vales con Mobiliarios ===\n";

try {
    // Obtener todos los vales con sus mobiliarios
    $vales = Vale::with(['mobiliarios', 'movimiento', 'mobiliario'])->get();
    
    echo "Total de vales: " . $vales->count() . "\n\n";
    
    foreach ($vales as $vale) {
        echo "Vale: " . $vale->numero_vale_formateado . "\n";
        echo "Tipo: " . $vale->tipo_vale . "\n";
        echo "Mobiliarios (relación múltiple): " . $vale->mobiliarios->count() . "\n";
        echo "Mobiliario individual ID: " . ($vale->mobiliario_id ?? 'NULL') . "\n";
        echo "Cantidad calculada: " . $vale->cantidad_mobiliarios . "\n";
        
        if ($vale->mobiliarios->count() > 0) {
            echo "Códigos de mobiliarios múltiples:\n";
            foreach ($vale->mobiliarios as $mob) {
                echo "  - " . $mob->numero_control . " - " . $mob->descripcion . "\n";
            }
        } elseif ($vale->mobiliario) {
            echo "Mobiliario individual: " . $vale->mobiliario->numero_control . " - " . $vale->mobiliario->descripcion . "\n";
        } else {
            echo "Sin mobiliarios asociados\n";
        }
        
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
