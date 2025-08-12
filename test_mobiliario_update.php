<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mobiliario;
use App\Models\Localizacion;
use Illuminate\Support\Facades\DB;

echo "=== Debug: Actualización de Mobiliario ===\n";

try {
    // Tomar un mobiliario específico
    $mobiliario = Mobiliario::where('numero_control', 'F000001')->first();
    $localizacionOriginal = $mobiliario->localizacion;
    $nuevaLocalizacion = Localizacion::where('id', '!=', $localizacionOriginal->id)->first();
    
    echo "1. Mobiliario: {$mobiliario->numero_control}\n";
    echo "2. Ubicación original: {$localizacionOriginal->ubicacion_resumida} (ID: {$localizacionOriginal->id})\n";
    echo "3. Nueva ubicación: {$nuevaLocalizacion->ubicacion_resumida} (ID: {$nuevaLocalizacion->id})\n";
    
    echo "\n--- ANTES DE LA ACTUALIZACIÓN ---\n";
    echo "   localizacion_id en DB: {$mobiliario->localizacion_id}\n";
    echo "   ubicacionReal(): {$mobiliario->ubicacionReal()->ubicacion_resumida}\n";
    
    // Actualizar directamente
    echo "\n4. Actualizando localizacion_id a {$nuevaLocalizacion->id}...\n";
    $mobiliario->localizacion_id = $nuevaLocalizacion->id;
    $resultado = $mobiliario->save();
    
    echo "   save() resultado: " . ($resultado ? 'true' : 'false') . "\n";
    
    echo "\n--- DESPUÉS DE LA ACTUALIZACIÓN (sin refresh) ---\n";
    echo "   localizacion_id en modelo: {$mobiliario->localizacion_id}\n";
    echo "   ubicacionReal(): {$mobiliario->ubicacionReal()->ubicacion_resumida}\n";
    
    // Refresh el modelo
    $mobiliario->refresh();
    
    echo "\n--- DESPUÉS DE refresh() ---\n";
    echo "   localizacion_id en modelo: {$mobiliario->localizacion_id}\n";
    echo "   ubicacionReal(): {$mobiliario->ubicacionReal()->ubicacion_resumida}\n";
    
    // Verificar en la base de datos directamente
    echo "\n--- VERIFICACIÓN DIRECTA EN DB ---\n";
    $dbResult = DB::select("SELECT id, numero_control, localizacion_id FROM mobiliario WHERE numero_control = 'F000001'");
    echo "   DB directo - localizacion_id: {$dbResult[0]->localizacion_id}\n";
    
    // Crear un nuevo objeto desde la DB
    $mobiliarioFresco = Mobiliario::where('numero_control', 'F000001')->first();
    echo "   Objeto fresco - localizacion_id: {$mobiliarioFresco->localizacion_id}\n";
    echo "   Objeto fresco - ubicacionReal(): {$mobiliarioFresco->ubicacionReal()->ubicacion_resumida}\n";
    
    echo "\n✅ Debug completado\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
