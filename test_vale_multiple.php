<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;

echo "=== Prueba de Vale con Movimiento Múltiple ===\n";

try {
    // Buscar el movimiento múltiple
    $movimiento = Movimiento::where('numero_movimiento', 'MOV-2025-0003')
                           ->with('mobiliarios')
                           ->first();
    
    if (!$movimiento) {
        echo "No se encontró el movimiento MOV-2025-0003\n";
        exit;
    }
    
    echo "Movimiento encontrado: " . $movimiento->numero_movimiento . "\n";
    echo "Fecha: " . $movimiento->fecha_movimiento->format('d/m/Y H:i') . "\n";
    echo "Cantidad de mobiliarios: " . $movimiento->mobiliarios->count() . "\n\n";
    
    echo "=== Datos para el Vale (Auto-población) ===\n";
    echo "Movimiento seleccionado: " . $movimiento->numero_movimiento . " (" . $movimiento->fecha_movimiento->format('d/m/Y H:i') . ", " . $movimiento->mobiliarios->count() . " items)\n\n";
    
    echo "Mobiliarios que se auto-poblarían:\n";
    foreach ($movimiento->mobiliarios as $index => $mobiliario) {
        echo "Mobiliario " . ($index + 1) . ":\n";
        echo "  - ID: " . $mobiliario->id . "\n";
        echo "  - Número Control: " . $mobiliario->numero_control . "\n";
        echo "  - Descripción: " . $mobiliario->descripcion . "\n";
        echo "  - Marca: " . ($mobiliario->marca ?? 'N/A') . "\n";
        echo "  - Modelo: " . ($mobiliario->modelo ?? 'N/A') . "\n";
        echo "  - Número Serie: " . ($mobiliario->numero_serie ?? 'N/A') . "\n";
        echo "  - Estado: " . ($mobiliario->estado ?? 'N/A') . "\n";
        echo "\n";
    }
    
    // Simular el array que se enviaría al formulario del Vale
    $mobiliarios_data = [];
    foreach ($movimiento->mobiliarios as $mobiliario) {
        $mobiliarios_data[] = [
            'mobiliario_id' => $mobiliario->id,
            'descripcion' => $mobiliario->descripcion,
            'marca' => $mobiliario->marca ?? '',
            'modelo' => $mobiliario->modelo ?? '',
            'numero_serie' => $mobiliario->numero_serie ?? '',
        ];
    }
    
    echo "=== Datos JSON para el formulario ===\n";
    echo json_encode($mobiliarios_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
