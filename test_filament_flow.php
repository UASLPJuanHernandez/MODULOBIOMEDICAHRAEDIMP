<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;

echo "=== Simulación del Flujo de Filament ===\n";

try {
    // Simular datos que vendrían del formulario de Filament
    $mobiliarios = Mobiliario::limit(4)->get();
    $localizacion = Localizacion::skip(1)->first(); // Una localización diferente
    $usuario = User::first();
    
    if ($mobiliarios->count() < 4 || !$localizacion || !$usuario) {
        echo "❌ Error: Necesitamos al menos 4 mobiliarios\n";
        exit(1);
    }
    
    // Simular los datos del formulario (como los manejaría MovimientoResource)
    $formData = [
        'mobiliarios_data' => $mobiliarios->pluck('id')->toArray(), // IDs de mobiliarios seleccionados
        'area_actual_id' => $localizacion->id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Encargado de Almacén',
        'se_retira_con' => 'Jefe de Servicio',
        'observacion' => 'Movimiento de lote de 4 mobiliarios para reorganización',
        'usuario_id' => $usuario->id,
    ];
    
    echo "1. Simulando selección de " . count($formData['mobiliarios_data']) . " mobiliarios\n";
    echo "2. Destino: {$localizacion->ubicacion_resumida}\n";
    
    // Simular el proceso handleRecordCreation
    $mobiliariosIds = $formData['mobiliarios_data'];
    unset($formData['mobiliarios_data']);
    
    // Crear el movimiento
    $movimiento = Movimiento::create($formData);
    echo "3. Movimiento base creado: {$movimiento->numero_movimiento}\n";
    
    // Asociar mobiliarios con áreas anteriores
    foreach ($mobiliariosIds as $mobiliarioId) {
        $mobiliario = Mobiliario::find($mobiliarioId);
        if ($mobiliario) {
            $ubicacionAnterior = $mobiliario->ubicacionReal();
            
            $movimiento->mobiliarios()->attach($mobiliarioId, [
                'area_anterior_id' => $ubicacionAnterior?->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Actualizar la ubicación del mobiliario
            $mobiliario->localizacion_id = $formData['area_actual_id'];
            $mobiliario->save();
            
            echo "   - {$mobiliario->numero_control}: {$ubicacionAnterior?->ubicacion_resumida} → {$localizacion->ubicacion_resumida}\n";
        }
    }
    
    // Refrescar y mostrar resultados
    $movimiento = $movimiento->fresh();
    
    echo "4. ✅ Movimiento completado:\n";
    echo "   - ID: {$movimiento->id}\n";
    echo "   - Número: {$movimiento->numero_movimiento}\n";
    echo "   - Cantidad de mobiliarios: {$movimiento->cantidad_mobiliarios}\n";
    echo "   - Vale generado: " . ($movimiento->vale_generado ? 'Sí' : 'No') . "\n";
    echo "   - Fecha: {$movimiento->fecha_movimiento->format('d/m/Y H:i')}\n";
    
    echo "\n🎯 Simulación del flujo de Filament completada exitosamente\n";
    echo "El sistema está listo para manejar hasta 4 mobiliarios por movimiento\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
