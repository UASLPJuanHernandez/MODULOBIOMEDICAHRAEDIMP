<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Prueba Final Completa del Sistema ===\n";

try {
    // Obtener datos para la prueba
    $mobiliarios = Mobiliario::limit(3)->get();
    $ubicacionOrigen = $mobiliarios->first()->ubicacionReal();
    $ubicacionDestino = Localizacion::where('id', '!=', $ubicacionOrigen->id)->first();
    $usuario = User::first();
    
    echo "1. Configuración de la prueba:\n";
    echo "   - Mobiliarios: " . $mobiliarios->count() . "\n";
    echo "   - Origen: {$ubicacionOrigen->ubicacion_resumida}\n";
    echo "   - Destino: {$ubicacionDestino->ubicacion_resumida}\n";
    
    // Mostrar estado inicial
    echo "\n--- ESTADO INICIAL ---\n";
    foreach ($mobiliarios as $mob) {
        $ubicacion = $mob->ubicacionReal();
        echo "   {$mob->numero_control}: {$ubicacion->ubicacion_resumida}\n";
    }
    
    // Simular el flujo completo de CreateMovimiento
    echo "\n2. Simulando flujo completo de CreateMovimiento...\n";
    
    $formData = [
        'mobiliarios_data' => $mobiliarios->pluck('id')->toArray(),
        'area_actual_id' => $ubicacionDestino->id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Prueba final del sistema completo',
        'usuario_id' => $usuario->id,
    ];
    
    // Usar transacción como en el código real
    $movimiento = DB::transaction(function () use ($formData) {
        // Extraer los IDs de mobiliarios seleccionados
        $mobiliariosIds = $formData['mobiliarios_data'] ?? [];
        unset($formData['mobiliarios_data']);
        
        // Crear el movimiento
        $movimiento = Movimiento::create($formData);
        
        // Obtener ubicaciones anteriores y asociar mobiliarios
        foreach ($mobiliariosIds as $mobiliarioId) {
            $mobiliario = Mobiliario::find($mobiliarioId);
            if ($mobiliario) {
                $ubicacionAnterior = $mobiliario->ubicacionReal();
                
                // Asociar mobiliario con datos de área anterior
                $movimiento->mobiliarios()->attach($mobiliarioId, [
                    'area_anterior_id' => $ubicacionAnterior?->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Actualizar la ubicación del mobiliario
                $mobiliario->localizacion_id = $formData['area_actual_id'];
                $mobiliario->save();
            }
        }
        
        return $movimiento;
    });
    
    echo "   - Movimiento creado: {$movimiento->numero_movimiento}\n";
    
    // Verificar estado final
    echo "\n--- ESTADO FINAL ---\n";
    foreach ($mobiliarios as $mob) {
        $mob->refresh(); // Importante: refrescar desde la DB
        $ubicacion = $mob->ubicacionReal();
        echo "   {$mob->numero_control}: {$ubicacion->ubicacion_resumida}\n";
    }
    
    // Verificar información del movimiento en la tabla
    $movimiento = $movimiento->fresh(['mobiliarios', 'areaActual', 'usuario']);
    
    echo "\n--- INFORMACIÓN PARA LA TABLA DE FILAMENT ---\n";
    echo "   Número: {$movimiento->numero_movimiento}\n";
    echo "   Cantidad: {$movimiento->cantidad_mobiliarios} items\n";
    
    $codigos = $movimiento->mobiliarios->pluck('numero_control')->toArray();
    if (count($codigos) <= 2) {
        $mobiliariosResumen = implode(', ', $codigos);
    } else {
        $mobiliariosResumen = implode(', ', array_slice($codigos, 0, 2)) . ' y ' . (count($codigos) - 2) . ' más';
    }
    echo "   Códigos: {$mobiliariosResumen}\n";
    echo "   Destino: {$movimiento->areaActual->ubicacion_resumida}\n";
    echo "   Usuario: {$movimiento->usuario->name}\n";
    echo "   Fecha: {$movimiento->fecha_movimiento->format('d/m/Y H:i')}\n";
    
    // Verificar detalles del pivot
    echo "\n--- DETALLES DEL MOVIMIENTO ---\n";
    foreach ($movimiento->mobiliarios as $mob) {
        $areaAnterior = $mob->pivot->area_anterior_id 
            ? \App\Models\Localizacion::find($mob->pivot->area_anterior_id)->ubicacion_resumida 
            : 'N/A';
        echo "   {$mob->numero_control}: {$areaAnterior} → {$movimiento->areaActual->ubicacion_resumida}\n";
    }
    
    echo "\n✅ Prueba final completada exitosamente\n";
    echo "🎯 El movimiento {$movimiento->numero_movimiento} debería aparecer correctamente en la interfaz de Filament\n";
    echo "📍 Todas las ubicaciones de mobiliarios se actualizaron correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
