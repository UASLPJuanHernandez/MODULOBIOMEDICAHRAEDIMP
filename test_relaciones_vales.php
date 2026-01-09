<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mobiliario;
use App\Models\Vale;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "DIAGNÓSTICO: RELACIONES DE VALES\n";
echo "========================================\n\n";

// Verificar mobiliario ID 2
$mob = Mobiliario::find(2);

if ($mob) {
    echo "Mobiliario ID 2: {$mob->numero_control}\n\n";
    
    echo "1. Relación hasMany (vales):\n";
    $valesHasMany = $mob->vales;
    echo "   Total: {$valesHasMany->count()}\n";
    foreach ($valesHasMany as $vale) {
        echo "   - Vale #{$vale->id}: {$vale->numero_vale} ({$vale->tipo_vale})\n";
        echo "     Responsable: {$vale->responsable_recibe}\n";
    }
    
    echo "\n2. Relación belongsToMany (valesMultiples):\n";
    $valesMany = $mob->valesMultiples;
    echo "   Total: {$valesMany->count()}\n";
    foreach ($valesMany as $vale) {
        echo "   - Vale #{$vale->id}: {$vale->numero_vale} ({$vale->tipo_vale})\n";
        echo "     Responsable: {$vale->responsable_recibe}\n";
    }
}

echo "\n========================================\n";
echo "VERIFICAR TODOS LOS VALES\n";
echo "========================================\n\n";

$vales = Vale::all();
echo "Total de vales: {$vales->count()}\n\n";

foreach ($vales as $vale) {
    echo "Vale #{$vale->id}: {$vale->numero_vale}\n";
    echo "  mobiliario_id (hasMany): " . ($vale->mobiliario_id ?? 'NULL') . "\n";
    echo "  mobiliarios (belongsToMany): {$vale->mobiliarios()->count()}\n";
    echo "---\n";
}

echo "\n";
