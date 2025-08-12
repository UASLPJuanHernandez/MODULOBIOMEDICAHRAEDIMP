<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;

echo "=== Prueba de Creación de Movimiento ===\n";

try {
    // Obtener datos necesarios
    $mobiliario = Mobiliario::first();
    $localizacion = Localizacion::first();
    $usuario = User::first();
    
    if (!$mobiliario || !$localizacion || !$usuario) {
        echo "❌ Error: Faltan datos necesarios (mobiliario, localización o usuario)\n";
        exit(1);
    }
    
    echo "1. Mobiliario encontrado: {$mobiliario->numero_control}\n";
    echo "2. Localización encontrada: {$localizacion->ubicacion_resumida}\n";
    echo "3. Usuario encontrado: {$usuario->name}\n";
    
    // Crear un movimiento de prueba
    $movimiento = Movimiento::create([
        'mobiliario_id' => $mobiliario->id,
        'area_actual_id' => $localizacion->id,
        'area_anterior_id' => $mobiliario->localizacion_id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Prueba de movimiento con múltiples mobiliarios',
        'usuario_id' => $usuario->id,
    ]);
    
    echo "4. Movimiento creado con ID: {$movimiento->id}\n";
    echo "5. Número de movimiento generado: {$movimiento->numero_movimiento}\n";
    echo "6. Vale generado: " . ($movimiento->vale_generado ? 'Sí' : 'No') . "\n";
    echo "7. Cantidad de mobiliarios: {$movimiento->cantidad_mobiliarios}\n";
    
    // Agregar un mobiliario adicional al movimiento
    $segundoMobiliario = Mobiliario::where('id', '!=', $mobiliario->id)->first();
    if ($segundoMobiliario) {
        $movimiento->mobiliarios()->attach($segundoMobiliario->id, [
            'area_anterior_id' => $segundoMobiliario->localizacion_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "8. Segundo mobiliario agregado: {$segundoMobiliario->numero_control}\n";
        echo "9. Nueva cantidad de mobiliarios: {$movimiento->fresh()->cantidad_mobiliarios}\n";
    }
    
    echo "\n✅ Prueba completada exitosamente\n";
    echo "Movimiento ID: {$movimiento->id} - Número: {$movimiento->numero_movimiento}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
