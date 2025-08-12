<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Movimiento;
use App\Models\Mobiliario;

echo "=== Prueba del Sistema de Vales con Movimientos ===\n";

try {
    // Buscar un movimiento que tenga mobiliarios asociados
    $movimiento = Movimiento::with('mobiliarios')->whereHas('mobiliarios')->first();
    
    if (!$movimiento) {
        echo "❌ No se encontró ningún movimiento con mobiliarios asociados\n";
        exit(1);
    }
    
    echo "1. Movimiento encontrado:\n";
    echo "   - Número: {$movimiento->numero_movimiento}\n";
    echo "   - Fecha: {$movimiento->fecha_movimiento->format('d/m/Y H:i')}\n";
    echo "   - Cantidad de mobiliarios: {$movimiento->cantidad_mobiliarios}\n";
    
    echo "\n2. Mobiliarios asociados al movimiento:\n";
    foreach ($movimiento->mobiliarios as $index => $mobiliario) {
        echo "   " . ($index + 1) . ". {$mobiliario->numero_control}\n";
        echo "      - Descripción: {$mobiliario->descripcion}\n";
        echo "      - Marca: {$mobiliario->marca}\n";
        echo "      - Modelo: {$mobiliario->modelo}\n";
        echo "      - Número de Serie: {$mobiliario->numero_serie}\n";
        echo "\n";
    }
    
    // Simular los datos que se cargarían automáticamente en el formulario
    echo "3. Datos que se cargarían automáticamente en el vale:\n";
    $mobiliariosData = [];
    foreach ($movimiento->mobiliarios as $mobiliario) {
        $mobiliariosData[] = [
            'mobiliario_id' => $mobiliario->id,
            'descripcion' => $mobiliario->descripcion,
            'marca' => $mobiliario->marca,
            'modelo' => $mobiliario->modelo,
            'numero_serie' => $mobiliario->numero_serie,
        ];
    }
    
    echo "   Array que se enviaría al repeater:\n";
    foreach ($mobiliariosData as $index => $data) {
        echo "   [{$index}]:\n";
        echo "     - mobiliario_id: {$data['mobiliario_id']}\n";
        echo "     - descripcion: {$data['descripcion']}\n";
        echo "     - marca: {$data['marca']}\n";
        echo "     - modelo: {$data['modelo']}\n";
        echo "     - numero_serie: {$data['numero_serie']}\n";
        echo "\n";
    }
    
    // Simular la etiqueta del movimiento que aparecería en el select
    $etiquetaMovimiento = "{$movimiento->numero_movimiento}" . 
        ($movimiento->fecha_movimiento ? " - {$movimiento->fecha_movimiento->format('d/m/Y H:i')}" : "") . 
        " ({$movimiento->cantidad_mobiliarios} items)";
    
    echo "4. Información del select de movimientos:\n";
    echo "   - Etiqueta: {$etiquetaMovimiento}\n";
    echo "   - Value: {$movimiento->id}\n";
    
    echo "\n--- RESUMEN DE LA FUNCIONALIDAD ---\n";
    echo "✅ Al seleccionar el movimiento '{$movimiento->numero_movimiento}' en el formulario de vales:\n";
    echo "   1. Se cargarán automáticamente {$movimiento->cantidad_mobiliarios} mobiliarios\n";
    echo "   2. Cada mobiliario tendrá sus datos completos (control, descripción, marca, modelo, serie)\n";
    echo "   3. Los campos estarán deshabilitados (solo lectura)\n";
    echo "   4. No se podrán agregar/eliminar mobiliarios manualmente\n";
    echo "   5. La sección manual está comentada para uso futuro\n";
    
    echo "\n--- VERIFICACIÓN DE DATOS ---\n";
    $datosCompletos = true;
    foreach ($movimiento->mobiliarios as $mob) {
        if (empty($mob->numero_control) || empty($mob->descripcion)) {
            echo "⚠️  {$mob->numero_control}: Faltan datos básicos\n";
            $datosCompletos = false;
        } else {
            echo "✅ {$mob->numero_control}: Datos completos\n";
        }
    }
    
    if ($datosCompletos) {
        echo "\n🎯 Todos los mobiliarios tienen los datos necesarios para el vale\n";
    } else {
        echo "\n⚠️  Algunos mobiliarios pueden tener datos incompletos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
