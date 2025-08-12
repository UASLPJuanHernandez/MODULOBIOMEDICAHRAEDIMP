<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Vale;

echo "=== Prueba de Creación de Vale con Múltiples Mobiliarios ===\n";

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
    echo "Mobiliarios: " . $movimiento->mobiliarios->count() . "\n\n";
    
    // Crear el Vale (simulando los datos del formulario)
    $valeData = [
        'tipo_vale' => 'entrega',
        'fecha_generacion' => now(),
        'movimiento_id' => $movimiento->id,
        'responsable_entrega' => 'Vicente Zarazua',
        'matricula_entrega' => '3782',
        'responsable_recibe' => 'Vicente zarazua',
        'matricula_recibe' => '3782',
        'observaciones' => 'Prueba de generación de vales con múltiples mobiliarios',
        'mobiliario_id' => null // Importante: NULL para vales múltiples
    ];
    
    echo "Creando Vale...\n";
    $vale = Vale::create($valeData);
    echo "Vale creado con ID: " . $vale->id . "\n";
    
    // Crear los registros en vale_mobiliarios (sin campos adicionales en la tabla pivot)
    echo "Agregando mobiliarios al vale...\n";
    foreach ($movimiento->mobiliarios as $index => $mobiliario) {
        $vale->mobiliarios()->attach($mobiliario->id);
        
        echo "  " . ($index + 1) . ". " . $mobiliario->numero_control . " - " . $mobiliario->descripcion . "\n";
    }
    
    echo "\n=== Vale Creado Exitosamente ===\n";
    echo "ID del Vale: " . $vale->id . "\n";
    echo "Tipo: " . $vale->tipo_vale . "\n";
    echo "Movimiento: " . $vale->movimiento->numero_movimiento . "\n";
    echo "Mobiliarios asociados: " . $vale->mobiliarios->count() . "\n";
    
    // Verificar la creación
    $vale->load('mobiliarios');
    foreach ($vale->mobiliarios as $mob) {
        echo "- " . $mob->numero_control . " - " . $mob->descripcion . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
