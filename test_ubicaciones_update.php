<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;

echo "=== Prueba de Actualización de Ubicaciones ===\n";

try {
    // Obtener mobiliarios y localizaciones
    $mobiliarios = Mobiliario::limit(2)->get();
    $localizacionOrigen = $mobiliarios->first()->localizacion;
    $localizacionDestino = Localizacion::where('id', '!=', $localizacionOrigen->id)->first();
    $usuario = User::first();
    
    if ($mobiliarios->count() < 2 || !$localizacionDestino || !$usuario) {
        echo "❌ Error: Necesitamos al menos 2 mobiliarios, 2 localizaciones y 1 usuario\n";
        exit(1);
    }
    
    echo "1. Mobiliarios encontrados: " . $mobiliarios->count() . "\n";
    echo "2. Localización origen: {$localizacionOrigen->ubicacion_resumida}\n";
    echo "3. Localización destino: {$localizacionDestino->ubicacion_resumida}\n";
    
    // Mostrar ubicaciones antes del movimiento
    echo "\n--- ANTES DEL MOVIMIENTO ---\n";
    foreach ($mobiliarios as $mob) {
        $ubicacion = $mob->ubicacionReal();
        echo "   {$mob->numero_control}: {$ubicacion->ubicacion_resumida}\n";
    }
    
    // Crear movimiento con múltiples mobiliarios
    $movimiento = Movimiento::create([
        'area_actual_id' => $localizacionDestino->id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Prueba de actualización de ubicaciones',
        'usuario_id' => $usuario->id,
    ]);
    
    echo "\n4. Movimiento creado: {$movimiento->numero_movimiento}\n";
    
    // Agregar múltiples mobiliarios y actualizar sus ubicaciones
    foreach ($mobiliarios as $mobiliario) {
        $ubicacionAnterior = $mobiliario->ubicacionReal();
        
        // Asociar mobiliario con área anterior
        $movimiento->mobiliarios()->attach($mobiliario->id, [
            'area_anterior_id' => $ubicacionAnterior->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // IMPORTANTE: Actualizar la ubicación del mobiliario
        $mobiliario->localizacion_id = $localizacionDestino->id;
        $mobiliario->save();
        
        echo "   - Mobiliario {$mobiliario->numero_control} movido de {$ubicacionAnterior->ubicacion_resumida} a {$localizacionDestino->ubicacion_resumida}\n";
    }
    
    // Verificar ubicaciones después del movimiento
    echo "\n--- DESPUÉS DEL MOVIMIENTO ---\n";
    foreach ($mobiliarios as $mob) {
        $mob->refresh(); // Refrescar desde la base de datos
        $ubicacion = $mob->ubicacionReal();
        echo "   {$mob->numero_control}: {$ubicacion->ubicacion_resumida}\n";
    }
    
    // Verificar información del movimiento
    $movimiento = $movimiento->fresh();
    echo "\n--- INFORMACIÓN DEL MOVIMIENTO ---\n";
    echo "   Número: {$movimiento->numero_movimiento}\n";
    echo "   Cantidad de mobiliarios: {$movimiento->cantidad_mobiliarios}\n";
    echo "   Destino: {$movimiento->areaActual->ubicacion_resumida}\n";
    
    echo "\n--- DETALLES DE LA RELACIÓN PIVOT ---\n";
    foreach ($movimiento->mobiliarios as $mob) {
        $areaAnterior = \App\Models\Localizacion::find($mob->pivot->area_anterior_id);
        echo "   {$mob->numero_control}: de {$areaAnterior->ubicacion_resumida} → {$movimiento->areaActual->ubicacion_resumida}\n";
    }
    
    echo "\n✅ Prueba de actualización de ubicaciones completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
