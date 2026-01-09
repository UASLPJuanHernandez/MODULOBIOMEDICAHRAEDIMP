<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mobiliario;

echo "\n========================================\n";
echo "SIMULACIÓN DE CONSULTA FILAMENT\n";
echo "========================================\n\n";

// Simular la consulta exacta como en MobiliarioResource
$mobiliarios = Mobiliario::query()
    ->where('dado_de_baja', false)
    ->with([
        'vales' => function($query) {
            $query->latest()->limit(1);
        }
    ])
    ->limit(10)
    ->get();

echo "Total mobiliarios (activos): {$mobiliarios->count()}\n\n";

foreach ($mobiliarios as $mob) {
    echo "Mobiliario: {$mob->numero_control}\n";
    echo "  Descripción: {$mob->descripcion}\n";
    
    // Verificar si vales está cargado
    if ($mob->relationLoaded('vales')) {
        echo "  ✓ Relación 'vales' está cargada\n";
        echo "  Cantidad de vales: {$mob->vales->count()}\n";
        
        if ($mob->vales->count() > 0) {
            $ultimoVale = $mob->vales->first();
            echo "  📄 VALE ENCONTRADO:\n";
            echo "     Número: {$ultimoVale->numero_vale}\n";
            echo "     Tipo: {$ultimoVale->tipo_vale}\n";
            echo "     Responsable Entrega: {$ultimoVale->responsable_entrega}\n";
            echo "     Responsable Recibe: {$ultimoVale->responsable_recibe}\n";
            echo "     Fecha: {$ultimoVale->fecha_generacion}\n";
            
            // Simular lo que hacen las columnas
            $tipoVale = $ultimoVale->tipo_vale;
            $responsable = $ultimoVale->responsable_recibe ?: $ultimoVale->responsable_entrega;
            
            echo "  🎯 COLUMNAS MOSTRARÍAN:\n";
            echo "     Tipo Vale: {$tipoVale}\n";
            echo "     Responsable: {$responsable}\n";
        } else {
            echo "  ❌ Sin vales asociados\n";
        }
    } else {
        echo "  ❌ Relación 'vales' NO está cargada\n";
    }
    
    echo "  " . str_repeat("-", 50) . "\n\n";
}

echo "\n========================================\n";
echo "RESUMEN\n";
echo "========================================\n\n";

$conVales = $mobiliarios->filter(function($mob) {
    return $mob->vales->count() > 0;
})->count();

$sinVales = $mobiliarios->count() - $conVales;

echo "Mobiliarios CON vales: {$conVales}\n";
echo "Mobiliarios SIN vales: {$sinVales}\n\n";

if ($sinVales > 0) {
    echo "💡 PROBLEMA IDENTIFICADO:\n";
    echo "   Los mobiliarios sin vales mostrarán 'Sin vale' en las columnas.\n";
    echo "   Esto es el comportamiento esperado.\n\n";
}

if ($conVales > 0) {
    echo "✅ Los mobiliarios con vales DEBERÍAN mostrar la información correctamente.\n\n";
}

echo "\n";
