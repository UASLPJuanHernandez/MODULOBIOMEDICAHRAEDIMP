<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vale;
use App\Models\Movimiento;

echo "=== Debug Creación de Vale desde Movimiento ===\n";

try {
    // Buscar un movimiento con múltiples mobiliarios
    $movimiento = Movimiento::where('numero_movimiento', 'MOV-2025-0003')
                           ->with('mobiliarios')
                           ->first();
    
    if (!$movimiento) {
        echo "No se encontró el movimiento MOV-2025-0003\n";
        exit;
    }
    
    echo "Movimiento encontrado: " . $movimiento->numero_movimiento . "\n";
    echo "Mobiliarios en movimiento: " . $movimiento->mobiliarios->count() . "\n";
    
    // Simular los datos que llegarían del formulario
    $mobiliariosData = $movimiento->mobiliarios->map(function ($mobiliario) {
        return [
            'mobiliario_id' => $mobiliario->id,
            'descripcion' => $mobiliario->descripcion,
            'marca' => $mobiliario->marca,
            'modelo' => $mobiliario->modelo,
            'numero_serie' => $mobiliario->numero_serie,
        ];
    })->toArray();
    
    echo "\nDatos de mobiliarios preparados:\n";
    foreach ($mobiliariosData as $index => $mob) {
        echo "  " . ($index + 1) . ". ID: {$mob['mobiliario_id']}, Desc: {$mob['descripcion']}\n";
    }
    
    // Simular mutateFormDataBeforeCreate
    $formData = [
        'tipo_vale' => 'entrega',
        'fecha_generacion' => now(),
        'movimiento_id' => $movimiento->id,
        'responsable_entrega' => 'Test Entrega',
        'matricula_entrega' => '1234',
        'responsable_recibe' => 'Test Recibe',
        'matricula_recibe' => '5678',
        'observaciones' => 'Vale de prueba desde movimiento',
        'mobiliarios_data' => $mobiliariosData
    ];
    
    echo "\nSimulando mutateFormDataBeforeCreate...\n";
    
    if (isset($formData['mobiliarios_data']) && is_array($formData['mobiliarios_data'])) {
        $mobiliarios_ids = collect($formData['mobiliarios_data'])
            ->pluck('mobiliario_id')
            ->filter()
            ->toArray();
        
        echo "IDs extraídos: " . implode(', ', $mobiliarios_ids) . "\n";
        
        if (count($mobiliarios_ids) > 1) {
            $formData['mobiliario_id'] = null;
            echo "Vale múltiple - mobiliario_id = NULL\n";
        } elseif (count($mobiliarios_ids) == 1) {
            $formData['mobiliario_id'] = $mobiliarios_ids[0];
            echo "Vale individual - mobiliario_id = {$mobiliarios_ids[0]}\n";
        } else {
            $formData['mobiliario_id'] = null;
            echo "Sin mobiliarios - mobiliario_id = NULL\n";
        }
        
        $mobiliarios_para_asociar = $mobiliarios_ids;
    }
    
    unset($formData['mobiliarios_data']);
    $formData['fecha_generacion'] = now();
    
    echo "\nCreando vale...\n";
    $vale = Vale::create($formData);
    echo "Vale creado con ID: " . $vale->id . "\n";
    
    echo "\nAsociando mobiliarios...\n";
    if (isset($mobiliarios_para_asociar) && !empty($mobiliarios_para_asociar)) {
        $vale->mobiliarios()->sync($mobiliarios_para_asociar);
        echo "Mobiliarios asociados: " . implode(', ', $mobiliarios_para_asociar) . "\n";
    } else {
        echo "❌ No hay mobiliarios para asociar\n";
    }
    
    // Verificar el resultado
    $vale = $vale->fresh(['mobiliarios']);
    echo "\n=== RESULTADO ===\n";
    echo "Vale ID: " . $vale->id . "\n";
    echo "Número: " . $vale->numero_vale_formateado . "\n";
    echo "mobiliario_id: " . ($vale->mobiliario_id ?? 'NULL') . "\n";
    echo "Mobiliarios asociados: " . $vale->mobiliarios->count() . "\n";
    
    foreach ($vale->mobiliarios as $mob) {
        echo "  - " . $mob->numero_control . " - " . $mob->descripcion . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
