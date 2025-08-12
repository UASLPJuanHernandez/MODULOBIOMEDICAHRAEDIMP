<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vale;
use Illuminate\Support\Facades\DB;

echo "=== Diagnóstico Detallado de Vales ===\n";

try {
    // 1. Verificar tabla vale_mobiliario directamente
    echo "1. Contenido de tabla vale_mobiliario:\n";
    $vale_mobiliario = DB::table('vale_mobiliario')->get();
    
    if ($vale_mobiliario->count() > 0) {
        foreach ($vale_mobiliario as $vm) {
            echo "   Vale ID: {$vm->vale_id}, Mobiliario ID: {$vm->mobiliario_id}\n";
        }
    } else {
        echo "   *** NO HAY REGISTROS EN vale_mobiliario ***\n";
    }
    
    echo "\n2. Verificando un vale específico (ID 15):\n";
    $vale = Vale::find(15);
    if ($vale) {
        echo "   Vale 15 encontrado\n";
        echo "   mobiliario_id: " . ($vale->mobiliario_id ?? 'NULL') . "\n";
        
        // Verificar relación mobiliarios sin eager loading
        $mobiliarios_count = $vale->mobiliarios()->count();
        echo "   Mobiliarios count (query): {$mobiliarios_count}\n";
        
        // Verificar con eager loading
        $vale_with_mobiliarios = Vale::with('mobiliarios')->find(15);
        echo "   Mobiliarios count (eager): " . $vale_with_mobiliarios->mobiliarios->count() . "\n";
        
        // Query directo
        $mobiliarios_direct = DB::table('vale_mobiliario')
            ->where('vale_id', 15)
            ->join('mobiliario', 'vale_mobiliario.mobiliario_id', '=', 'mobiliario.id')
            ->select('mobiliario.numero_control', 'mobiliario.descripcion')
            ->get();
            
        echo "   Query directo encuentra: " . $mobiliarios_direct->count() . " mobiliarios\n";
        foreach ($mobiliarios_direct as $mob) {
            echo "     - {$mob->numero_control}: {$mob->descripcion}\n";
        }
    }
    
    echo "\n3. Verificando relación en modelo Vale:\n";
    $vale_test = new Vale();
    $relation = $vale_test->mobiliarios();
    echo "   Relación: " . get_class($relation) . "\n";
    echo "   Tabla pivot: " . $relation->getTable() . "\n";
    echo "   Foreign key: " . $relation->getForeignPivotKeyName() . "\n";
    echo "   Related key: " . $relation->getRelatedPivotKeyName() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
