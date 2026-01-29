<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Modificando columna 'attempts' en tabla 'jobs'...\n";

try {
    DB::statement('ALTER TABLE jobs MODIFY attempts SMALLINT UNSIGNED NOT NULL');
    echo "✓ Columna modificada exitosamente\n";
    
    // Verificar el cambio
    $result = DB::select("SHOW COLUMNS FROM jobs WHERE Field = 'attempts'");
    if (!empty($result)) {
        echo "✓ Tipo actual: " . $result[0]->Type . "\n";
    }
    
    // Limpiar jobs con demasiados intentos
    echo "\nLimpiando jobs con intentos excesivos...\n";
    DB::table('jobs')->where('attempts', '>=', 3)->delete();
    echo "✓ Jobs limpiados\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Proceso completado\n";
