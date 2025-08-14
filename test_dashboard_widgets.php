<?php

require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test widgets
echo "=== PROBANDO WIDGETS DEL DASHBOARD ===\n\n";

try {
    // Test MovimientosPendientesWidget
    echo "1. Probando MovimientosPendientesWidget:\n";
    $widget = new App\Filament\Widgets\MovimientosPendientesWidget();
    $reflection = new ReflectionClass($widget);
    $method = $reflection->getMethod('getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);
    echo "   ✅ Widget funciona, stats count: " . count($stats) . "\n";
    foreach ($stats as $index => $stat) {
        echo "   - Stat $index: " . $stat->getLabel() . " = " . $stat->getValue() . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Error en MovimientosPendientesWidget: " . $e->getMessage() . "\n\n";
}

try {
    // Test AdminNotificationsWidget
    echo "2. Probando AdminNotificationsWidget:\n";
    // Simular autenticación
    Auth::loginUsingId(1);
    $widget = new App\Filament\Widgets\AdminNotificationsWidget();
    $reflection = new ReflectionClass($widget);
    $method = $reflection->getMethod('getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);
    echo "   ✅ Widget funciona, stats count: " . count($stats) . "\n";
    foreach ($stats as $index => $stat) {
        echo "   - Stat $index: " . $stat->getLabel() . " = " . $stat->getValue() . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Error en AdminNotificationsWidget: " . $e->getMessage() . "\n\n";
}

try {
    // Test TestWidget
    echo "3. Probando TestWidget:\n";
    $widget = new App\Filament\Widgets\TestWidget();
    $reflection = new ReflectionClass($widget);
    $method = $reflection->getMethod('getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);
    echo "   ✅ Widget funciona, stats count: " . count($stats) . "\n";
    foreach ($stats as $index => $stat) {
        echo "   - Stat $index: " . $stat->getLabel() . " = " . $stat->getValue() . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Error en TestWidget: " . $e->getMessage() . "\n\n";
}

echo "=== VERIFICACIÓN DE BASE DE DATOS ===\n";
echo "Usuarios: " . App\Models\User::count() . "\n";
echo "Movimientos: " . App\Models\Movimiento::count() . "\n";
echo "Movimientos sin vale: " . App\Models\Movimiento::whereNull('vale_id')->count() . "\n";

echo "\n=== PRUEBA COMPLETADA ===\n";
