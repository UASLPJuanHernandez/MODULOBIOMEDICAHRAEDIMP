<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vale;

echo "=== Simulando Comportamiento de Filament ===\n";

try {
    // Simular exactamente lo que hace el ValeResource
    $query = Vale::query()->with(['mobiliarios', 'movimiento', 'mobiliario']);
    $vales = $query->get();
    
    echo "Total vales obtenidos: " . $vales->count() . "\n\n";
    
    // Probar específicamente el vale 15
    $vale15 = $vales->find(15);
    if ($vale15) {
        echo "=== VALE 15 DETALLE ===\n";
        echo "ID: " . $vale15->id . "\n";
        echo "Número vale: " . $vale15->numero_vale_formateado . "\n";
        echo "mobiliario_id: " . ($vale15->mobiliario_id ?? 'NULL') . "\n";
        
        // Probar el accessor
        echo "cantidad_mobiliarios (accessor): " . $vale15->cantidad_mobiliarios . "\n";
        
        // Probar la relación
        echo "mobiliarios->count(): " . $vale15->mobiliarios->count() . "\n";
        echo "relationLoaded('mobiliarios'): " . ($vale15->relationLoaded('mobiliarios') ? 'SÍ' : 'NO') . "\n";
        
        // Probar el formateo de códigos como en ValeResource
        $codigos = collect();
        
        // Primero verificar mobiliarios múltiples
        if ($vale15->mobiliarios->count() > 0) {
            $codigos = $vale15->mobiliarios->pluck('numero_control');
            echo "Códigos de múltiples: " . $codigos->implode(', ') . "\n";
        } 
        // Si no hay múltiples, verificar mobiliario individual
        elseif ($vale15->mobiliario_id && $vale15->mobiliario) {
            $codigos = collect([$vale15->mobiliario->numero_control]);
            echo "Código individual: " . $codigos->implode(', ') . "\n";
        }
        
        if ($codigos->isEmpty()) {
            echo "RESULTADO: Sin mobiliarios\n";
        } else {
            $resultado = $codigos->take(3)->implode(', ');
            if ($codigos->count() > 3) {
                $resultado .= '...';
            }
            echo "RESULTADO FINAL: " . $resultado . "\n";
        }
        
        echo "\nDetalles de mobiliarios:\n";
        foreach ($vale15->mobiliarios as $mob) {
            echo "  - ID: {$mob->id}, Código: {$mob->numero_control}, Desc: {$mob->descripcion}\n";
        }
    }
    
    echo "\n=== RESUMEN DE TODOS LOS VALES ===\n";
    foreach ($vales as $vale) {
        $cantidad = $vale->mobiliarios->count();
        if ($cantidad == 0 && $vale->mobiliario_id) {
            $cantidad = 1; // Vale individual
        }
        
        $codigos = collect();
        if ($vale->mobiliarios->count() > 0) {
            $codigos = $vale->mobiliarios->pluck('numero_control');
        } elseif ($vale->mobiliario_id && $vale->mobiliario) {
            $codigos = collect([$vale->mobiliario->numero_control]);
        }
        
        $resultado_codigos = $codigos->isEmpty() ? 'Sin mobiliarios' : $codigos->take(3)->implode(', ');
        
        echo "Vale {$vale->id}: {$cantidad} items - {$resultado_codigos}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
