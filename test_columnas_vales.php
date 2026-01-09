<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mobiliario;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "DIAGNÓSTICO COMPLETO: COLUMNAS DE VALES\n";
echo "========================================\n\n";

// Obtener exactamente la misma consulta que usa Filament
$mobiliarios = Mobiliario::query()
    ->where('dado_de_baja', false)
    ->with([
        'localizacion',
        'tipoMobiliario',
        'usuarioCreador',
        'usuarioEditor',
        'proveedor',
        'clasificacionBien',
        'ultimoMovimiento.areaActual',
        'movimientos' => function($query) {
            $query->orderBy('fecha_movimiento', 'desc');
        },
        'vales' => function($query) {
            $query->latest()->limit(1);
        }
    ])
    ->get();

echo "Total mobiliarios activos: {$mobiliarios->count()}\n\n";

foreach ($mobiliarios as $record) {
    echo "=" . str_repeat("=", 70) . "\n";
    echo "MOBILIARIO: {$record->numero_control}\n";
    echo "Descripción: {$record->descripcion}\n";
    echo str_repeat("-", 72) . "\n";
    
    // Verificar si la relación está cargada
    echo "¿Relación 'vales' cargada? " . ($record->relationLoaded('vales') ? '✓ SÍ' : '✗ NO') . "\n";
    
    if ($record->relationLoaded('vales')) {
        echo "Cantidad de vales en colección: {$record->vales->count()}\n\n";
        
        if ($record->vales->count() > 0) {
            $ultimoVale = $record->vales->first();
            
            echo "📄 VALE ENCONTRADO:\n";
            echo "   ID: {$ultimoVale->id}\n";
            echo "   Número: {$ultimoVale->numero_vale}\n";
            echo "   Tipo: {$ultimoVale->tipo_vale}\n";
            echo "   Responsable Entrega: {$ultimoVale->responsable_entrega}\n";
            echo "   Responsable Recibe: {$ultimoVale->responsable_recibe}\n";
            echo "   Fecha Generación: {$ultimoVale->fecha_generacion}\n\n";
            
            // Simular exactamente lo que hacen las columnas
            echo "🎯 SIMULACIÓN DE COLUMNAS:\n\n";
            
            // Columna 1: Tipo Vale
            if (!$record->relationLoaded('vales')) {
                $tipoVale = 'Sin vale';
            } else {
                $ultimoVale = $record->vales->first();
                if (!$ultimoVale) {
                    $tipoVale = 'Sin vale';
                } else {
                    $tipoVale = match($ultimoVale->tipo_vale) {
                        'resguardo' => 'Resguardo',
                        'entrega' => 'Entrega',
                        'retiro' => 'Retiro',
                        default => ucfirst($ultimoVale->tipo_vale)
                    };
                }
            }
            echo "   [Tipo Vale]: {$tipoVale}\n";
            
            // Columna 2: Responsable Vale
            if (!$record->relationLoaded('vales')) {
                $responsableVale = 'Sin responsable';
            } else {
                $ultimoVale = $record->vales->first();
                if (!$ultimoVale) {
                    $responsableVale = 'Sin responsable';
                } else {
                    $responsable = $ultimoVale->responsable_recibe ?: $ultimoVale->responsable_entrega;
                    $responsableVale = $responsable ?: 'Sin responsable';
                }
            }
            echo "   [Responsable Vale]: {$responsableVale}\n";
            
            // Columna 3: Fecha Vale
            if (!$record->relationLoaded('vales')) {
                $fechaVale = 'Sin vale';
            } else {
                $ultimoVale = $record->vales->first();
                $fechaVale = $ultimoVale ? $ultimoVale->fecha_generacion->format('Y-m-d') : 'Sin vale';
            }
            echo "   [Fecha Vale]: {$fechaVale}\n";
            
        } else {
            echo "❌ COLECCIÓN DE VALES VACÍA\n";
            echo "   Las columnas mostrarán: 'Sin vale' / 'Sin responsable'\n";
        }
    } else {
        echo "❌ RELACIÓN NO CARGADA\n";
        echo "   ESTO ES UN ERROR - La relación debería estar cargada\n";
    }
    
    echo "\n";
}

echo "\n========================================\n";
echo "VERIFICACIÓN ADICIONAL\n";
echo "========================================\n\n";

// Verificar directamente en la base de datos
echo "Consulta directa a tabla 'vales':\n";
$valesDB = DB::table('vales')->get();
echo "Total de registros en tabla vales: {$valesDB->count()}\n\n";

foreach ($valesDB as $vale) {
    echo "Vale #{$vale->id}: {$vale->numero_vale}\n";
    echo "  mobiliario_id: {$vale->mobiliario_id}\n";
    echo "  tipo_vale: {$vale->tipo_vale}\n";
    echo "  responsable_recibe: {$vale->responsable_recibe}\n";
    echo "---\n";
}

echo "\nConsulta directa a tabla 'vale_mobiliario':\n";
$valeMobiliarioDB = DB::table('vale_mobiliario')->get();
echo "Total de registros: {$valeMobiliarioDB->count()}\n\n";

foreach ($valeMobiliarioDB as $vm) {
    echo "Vale ID {$vm->vale_id} <-> Mobiliario ID {$vm->mobiliario_id}\n";
}

echo "\n";
