<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 Limpiando cola de trabajos...\n\n";

// Limpiar tabla de jobs
$deletedJobs = DB::table('jobs')->delete();
echo "✅ Eliminados {$deletedJobs} trabajos pendientes\n";

// Limpiar tabla de failed_jobs
$deletedFailedJobs = DB::table('failed_jobs')->delete();
echo "✅ Eliminados {$deletedFailedJobs} trabajos fallidos\n";

echo "\n📊 Estado actual:\n";
$pendingJobs = DB::table('jobs')->count();
$failedJobs = DB::table('failed_jobs')->count();

echo "   Trabajos pendientes: {$pendingJobs}\n";
echo "   Trabajos fallidos: {$failedJobs}\n";

echo "\n✨ Listo! Ahora puedes intentar importar de nuevo.\n";
