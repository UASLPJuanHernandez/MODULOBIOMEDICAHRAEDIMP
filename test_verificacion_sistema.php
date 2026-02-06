<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Vale;
use App\Models\Mobiliario;
use App\Models\Movimiento;

echo "=== VERIFICACIÓN DEL SISTEMA ===\n\n";

// 1. Verificar estructura de la tabla vales
echo "1. ESTRUCTURA DE LA TABLA VALES\n";
echo str_repeat("-", 50) . "\n";

$columns = DB::select("DESCRIBE vales");
foreach ($columns as $column) {
    $nullable = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
    echo "- {$column->Field}: {$column->Type} [{$nullable}]\n";
}

// Verificar si movimiento_id es nullable
$movimientoIdNullable = collect($columns)->first(fn($col) => $col->Field === 'movimiento_id')?->Null === 'YES';
echo "\n✓ movimiento_id es " . ($movimientoIdNullable ? "NULLABLE ✓" : "NOT NULL ⚠️") . "\n";

if (!$movimientoIdNullable) {
    echo "\n⚠️  PROBLEMA DETECTADO: movimiento_id debe ser nullable para vales de resguardo\n";
    echo "   Solución: Ejecutar migración para hacer movimiento_id nullable\n";
}

echo "\n\n";

// 2. Verificar migraciones ejecutadas
echo "2. MIGRACIONES EJECUTADAS\n";
echo str_repeat("-", 50) . "\n";

$migraciones = DB::table('migrations')->orderBy('id', 'desc')->limit(10)->get();
echo "Últimas 10 migraciones:\n";
foreach ($migraciones as $mig) {
    echo "- {$mig->migration} (batch: {$mig->batch})\n";
}

echo "\nTotal de migraciones: " . DB::table('migrations')->count() . "\n";

echo "\n\n";

// 3. Estadísticas de registros
echo "3. ESTADÍSTICAS DE REGISTROS\n";
echo str_repeat("-", 50) . "\n";

echo "Mobiliarios: " . Mobiliario::count() . "\n";
echo "  - Activos: " . Mobiliario::whereNull('dado_de_baja')->count() . "\n";
echo "  - Dados de baja: " . Mobiliario::whereNotNull('dado_de_baja')->count() . "\n";

echo "\nVales: " . Vale::count() . "\n";
if (Schema::hasColumn('vales', 'tipo_vale')) {
    $valesPorTipo = Vale::selectRaw('tipo_vale, COUNT(*) as total')
        ->groupBy('tipo_vale')
        ->get();
    foreach ($valesPorTipo as $tipo) {
        echo "  - {$tipo->tipo_vale}: {$tipo->total}\n";
    }
}

// Verificar vales sin movimiento_id
$valesSinMovimiento = Vale::whereNull('movimiento_id')->count();
echo "\nVales sin movimiento_id: {$valesSinMovimiento}\n";

if (Schema::hasTable('movimientos')) {
    echo "\nMovimientos: " . Movimiento::count() . "\n";
}

echo "\n\n";

// 4. Verificar tablas relacionadas
echo "4. TABLAS RELACIONADAS\n";
echo str_repeat("-", 50) . "\n";

$tablas = [
    'users' => 'Usuarios',
    'mobiliario' => 'Mobiliario',
    'vales' => 'Vales',
    'movimientos' => 'Movimientos',
    'vale_mobiliario' => 'Vale-Mobiliario (pivot)',
    'movimiento_mobiliario' => 'Movimiento-Mobiliario (pivot)',
    'localizacion' => 'Localizaciones',
    'clasificacion_bienes' => 'Clasificación de Bienes',
    'tipo_mobiliario' => 'Tipos de Mobiliario',
    'proveedores' => 'Proveedores',
];

foreach ($tablas as $tabla => $nombre) {
    $existe = Schema::hasTable($tabla);
    $count = $existe ? DB::table($tabla)->count() : 0;
    $status = $existe ? "✓" : "✗";
    echo "{$status} {$nombre}: " . ($existe ? "{$count} registros" : "NO EXISTE") . "\n";
}

echo "\n\n";

// 5. Verificar integridad de relaciones
echo "5. INTEGRIDAD DE RELACIONES\n";
echo str_repeat("-", 50) . "\n";

// Vales huérfanos (sin mobiliario en la relación pivot)
if (Schema::hasTable('vale_mobiliario')) {
    $valesConMobiliarios = DB::table('vale_mobiliario')
        ->select('vale_id')
        ->distinct()
        ->pluck('vale_id');
    
    $totalVales = Vale::count();
    $valesConRelacion = $valesConMobiliarios->count();
    $valesSinRelacion = $totalVales - $valesConRelacion;
    
    echo "Vales con mobiliarios asociados: {$valesConRelacion}/{$totalVales}\n";
    if ($valesSinRelacion > 0) {
        echo "⚠️  Vales sin mobiliarios en relación pivot: {$valesSinRelacion}\n";
    }
}

echo "\n\n";

// 6. Prueba de creación de vale sin movimiento_id
echo "6. PRUEBA DE CREACIÓN DE VALE DE RESGUARDO\n";
echo str_repeat("-", 50) . "\n";

try {
    // Obtener un mobiliario de prueba
    $mobiliario = Mobiliario::first();
    
    if (!$mobiliario) {
        echo "⚠️  No hay mobiliarios para hacer la prueba\n";
    } else {
        echo "Intentando crear vale de resguardo sin movimiento_id...\n";
        
        $valeData = [
            'numero_vale' => 'TEST-' . time(),
            'tipo_vale' => 'resguardo',
            'mobiliario_id' => $mobiliario->id,
            'responsable_entrega' => 'Test Usuario',
            'matricula_entrega' => '0001',
            'responsable_recibe' => 'Test Receptor',
            'matricula_recibe' => '0002',
            'fecha_generacion' => now(),
            'observaciones' => 'Vale de prueba',
            // Nota: NO incluimos movimiento_id
        ];
        
        DB::beginTransaction();
        
        $vale = Vale::create($valeData);
        
        echo "✓ Vale creado exitosamente (ID: {$vale->id})\n";
        echo "  Tipo: {$vale->tipo_vale}\n";
        echo "  Número: {$vale->numero_vale}\n";
        echo "  movimiento_id: " . ($vale->movimiento_id ?? 'NULL') . "\n";
        
        // Rollback para no afectar datos reales
        DB::rollBack();
        echo "\n✓ Transacción revertida (no se guardó en BD)\n";
        echo "✓ SISTEMA FUNCIONAL: Puede crear vales sin movimiento_id\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\n⚠️  PROBLEMA CONFIRMADO: No se pueden crear vales sin movimiento_id\n";
    echo "   El campo movimiento_id debe ser nullable en la base de datos\n";
}

echo "\n\n";

// 7. Recomendaciones
echo "7. RECOMENDACIONES\n";
echo str_repeat("-", 50) . "\n";

if (!$movimientoIdNullable) {
    echo "⚠️  ACCIÓN REQUERIDA:\n";
    echo "   1. Crear migración para hacer movimiento_id nullable:\n";
    echo "      php artisan make:migration make_movimiento_id_nullable_in_vales_table\n\n";
    echo "   2. Editar la migración con:\n";
    echo "      \$table->unsignedBigInteger('movimiento_id')->nullable()->change();\n\n";
    echo "   3. Ejecutar en producción:\n";
    echo "      php artisan migrate\n\n";
} else {
    echo "✓ Sistema configurado correctamente\n";
    echo "✓ No se detectaron problemas críticos\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
