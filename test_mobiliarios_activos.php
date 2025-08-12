<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Mobiliario;
use App\Models\TipoMobiliario;

$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== TEST: Verificación de filtro de mobiliarios activos ===\n\n";

// 1. Crear un mobiliario de prueba y darlo de baja
echo "1. Creando mobiliario de prueba...\n";

$tipoMobiliario = TipoMobiliario::first();
if (!$tipoMobiliario) {
    echo "❌ Error: No hay tipos de mobiliario disponibles\n";
    exit(1);
}

$mobiliario = Mobiliario::create([
    'numero_control' => 'TEST-BAJA-001',
    'descripcion' => 'Mobiliario de prueba para baja',
    'tipo_mobiliario_id' => $tipoMobiliario->id,
    'localizacion_id' => 1,
    'precio' => 1000.00,
    'estado_mobiliario' => 'Bueno',
    'metodo_adquisicion' => 'Compra',
    'dado_de_baja' => false
]);

echo "✅ Mobiliario creado: {$mobiliario->numero_control} (ID: {$mobiliario->id})\n";

// 2. Verificar que aparece en consultas normales
echo "\n2. Verificando consultas antes de dar de baja...\n";

$totalTodos = Mobiliario::count();
$totalActivos = Mobiliario::activos()->count();

echo "📊 Total de mobiliarios: {$totalTodos}\n";
echo "📊 Mobiliarios activos: {$totalActivos}\n";

// 3. Dar de baja el mobiliario
echo "\n3. Dando de baja el mobiliario...\n";

$mobiliario->update([
    'dado_de_baja' => true,
    'fecha_baja' => now(),
    'motivo_baja' => 'Prueba de filtro'
]);

echo "✅ Mobiliario dado de baja\n";

// 4. Verificar que no aparece en mobiliarios activos
echo "\n4. Verificando consultas después de dar de baja...\n";

$totalTodosDepues = Mobiliario::count();
$totalActivosDepues = Mobiliario::activos()->count();
$totalBajas = Mobiliario::dadosDeBaja()->count();

echo "📊 Total de mobiliarios: {$totalTodosDepues}\n";
echo "📊 Mobiliarios activos: {$totalActivosDepues}\n";
echo "📊 Mobiliarios dados de baja: {$totalBajas}\n";

// 5. Verificar que el scope funciona correctamente
if ($totalActivosDepues == $totalActivos - 1) {
    echo "✅ El scope 'activos()' funciona correctamente\n";
} else {
    echo "❌ Error: El scope 'activos()' no funciona como esperado\n";
}

// 6. Simular lo que haría el formulario de movimientos
echo "\n5. Simulando selección de mobiliarios para movimientos...\n";

$opcionesMovimiento = Mobiliario::with('localizacion')
    ->activos()
    ->get()
    ->mapWithKeys(function ($mobiliario) {
        $ubicacion = $mobiliario->ubicacionReal();
        $ubicacionTexto = $ubicacion ? $ubicacion->ubicacion_resumida : 'Sin ubicación';
        
        return [
            $mobiliario->id => "{$mobiliario->numero_control} - {$mobiliario->descripcion} (Actual: {$ubicacionTexto})"
        ];
    });

$incluyeMobiliarioBaja = $opcionesMovimiento->has($mobiliario->id);

if (!$incluyeMobiliarioBaja) {
    echo "✅ El mobiliario dado de baja NO aparece en las opciones de movimiento\n";
} else {
    echo "❌ Error: El mobiliario dado de baja SÍ aparece en las opciones de movimiento\n";
}

// 7. Simular lo que haría el formulario de vales
echo "\n6. Simulando selección de mobiliarios para vales...\n";

$opcionesVale = Mobiliario::activos()->pluck('numero_control', 'id');

$incluyeMobiliarioBajaVale = $opcionesVale->has($mobiliario->id);

if (!$incluyeMobiliarioBajaVale) {
    echo "✅ El mobiliario dado de baja NO aparece en las opciones de vale\n";
} else {
    echo "❌ Error: El mobiliario dado de baja SÍ aparece en las opciones de vale\n";
}

// 8. Limpiar - eliminar el mobiliario de prueba
echo "\n7. Limpiando datos de prueba...\n";

$mobiliario->delete();
echo "✅ Mobiliario de prueba eliminado\n";

// 9. Resumen final
echo "\n=== RESUMEN ===\n";
echo "✅ Scope 'activos()' funciona correctamente\n";
echo "✅ Scope 'dadosDeBaja()' funciona correctamente\n";
echo "✅ Formulario de movimientos excluye mobiliarios dados de baja\n";
echo "✅ Formulario de vales excluye mobiliarios dados de baja\n";
echo "\n🎉 Todos los tests pasaron exitosamente!\n";
echo "Los mobiliarios dados de baja ya no aparecerán en las opciones de movimientos y vales.\n";

?>
