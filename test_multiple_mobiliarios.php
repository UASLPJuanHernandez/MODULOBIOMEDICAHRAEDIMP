<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;

echo "=== Prueba del Sistema de Múltiples Mobiliarios ===\n";

try {
    // Obtener múltiples mobiliarios
    $mobiliarios = Mobiliario::limit(3)->get();
    $localizacion = Localizacion::first();
    $usuario = User::first();
    
    if ($mobiliarios->count() < 2 || !$localizacion || !$usuario) {
        echo "❌ Error: Necesitamos al menos 2 mobiliarios, 1 localización y 1 usuario\n";
        exit(1);
    }
    
    echo "1. Mobiliarios encontrados: " . $mobiliarios->count() . "\n";
    echo "2. Localización: {$localizacion->ubicacion_resumida}\n";
    echo "3. Usuario: {$usuario->name}\n";
    
    // Crear movimiento SIN mobiliario_id (múltiples mobiliarios)
    $movimiento = Movimiento::create([
        // NO incluimos mobiliario_id porque manejamos múltiples mobiliarios
        'area_actual_id' => $localizacion->id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Movimiento de múltiples mobiliarios',
        'usuario_id' => $usuario->id,
    ]);
    
    echo "4. Movimiento creado (ID: {$movimiento->id}, Número: {$movimiento->numero_movimiento})\n";
    
    // Agregar múltiples mobiliarios
    foreach ($mobiliarios as $index => $mobiliario) {
        $movimiento->mobiliarios()->attach($mobiliario->id, [
            'area_anterior_id' => $mobiliario->localizacion_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "   - Mobiliario agregado: {$mobiliario->numero_control}\n";
    }
    
    // Refrescar el movimiento para obtener los datos actualizados
    $movimiento = $movimiento->fresh();
    
    echo "5. Cantidad final de mobiliarios: {$movimiento->cantidad_mobiliarios}\n";
    echo "6. Lista de mobiliarios asociados:\n";
    
    $mobiliariosAsociados = $movimiento->mobiliarios;
    foreach ($mobiliariosAsociados as $mob) {
        echo "   - {$mob->numero_control}: {$mob->descripcion}\n";
    }
    
    echo "\n✅ Prueba de múltiples mobiliarios completada exitosamente\n";
    echo "Movimiento: {$movimiento->numero_movimiento} con {$movimiento->cantidad_mobiliarios} mobiliarios\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
