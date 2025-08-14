<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA DE NOTIFICACIONES\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Verificar usuarios y roles
    echo "1️⃣ VERIFICANDO USUARIOS Y ROLES\n";
    $admin = User::role('Administrador')->first();
    $userApoyo = User::where('email', 'apoyo@inventario.hospital')->first();
    
    if (!$admin || !$userApoyo) {
        echo "❌ Error: Faltan usuarios necesarios\n";
        exit(1);
    }
    
    echo "   ✅ Admin: {$admin->name} (ID: {$admin->id})\n";
    echo "   ✅ User Apoyo: {$userApoyo->name} (ID: {$userApoyo->id})\n";
    echo "   🔑 Rol del admin: " . $admin->roles->first()->name . "\n";
    echo "   🔑 Rol del apoyo: " . $userApoyo->roles->first()->name . "\n\n";
    
    // 2. Verificar tabla de notificaciones
    echo "2️⃣ VERIFICANDO TABLA DE NOTIFICACIONES\n";
    try {
        $totalNotificaciones = \DB::table('notifications')->count();
        echo "   ✅ Tabla 'notifications' existe\n";
        echo "   📊 Total notificaciones en DB: {$totalNotificaciones}\n";
    } catch (Exception $e) {
        echo "   ❌ Error con tabla notifications: " . $e->getMessage() . "\n";
        exit(1);
    }
    echo "\n";
    
    // 3. Simular autenticación como usuario no admin
    echo "3️⃣ SIMULANDO AUTENTICACIÓN\n";
    Auth::login($userApoyo);
    echo "   ✅ Usuario autenticado: " . Auth::user()->name . "\n";
    echo "   🔍 ¿Es administrador? " . (Auth::user()->hasRole('Administrador') ? 'SÍ' : 'NO') . "\n\n";
    
    // 4. Contar notificaciones antes
    echo "4️⃣ CONTANDO NOTIFICACIONES ANTES\n";
    $notificacionesAntes = $admin->notifications()->count();
    echo "   📫 Notificaciones del admin antes: {$notificacionesAntes}\n\n";
    
    // 5. Probar directamente el servicio
    echo "5️⃣ PROBANDO ADMINNOTIFICATIONSERVICE DIRECTAMENTE\n";
    
    // Crear movimiento ficticio
    $movimientoFicticio = (object) [
        'id' => 999,
        'numero_movimiento' => 'TEST-' . time(),
        'mobiliarios' => collect([
            (object) ['numero_control' => 'TEST-001']
        ])
    ];
    
    echo "   🔔 Llamando AdminNotificationService::movimientoCreated...\n";
    AdminNotificationService::movimientoCreated(Auth::user(), $movimientoFicticio);
    echo "   ✅ Llamada completada\n\n";
    
    // 6. Verificar notificaciones después
    echo "6️⃣ VERIFICANDO RESULTADOS\n";
    $admin->refresh();
    $notificacionesDespues = $admin->notifications()->count();
    echo "   📫 Notificaciones del admin después: {$notificacionesDespues}\n";
    
    if ($notificacionesDespues > $notificacionesAntes) {
        echo "   🎉 ¡ÉXITO! Nueva notificación creada\n";
        $ultima = $admin->notifications()->latest()->first();
        if ($ultima) {
            echo "   📝 Título: " . $ultima->data['title'] . "\n";
            echo "   📝 Mensaje: " . $ultima->data['body'] . "\n";
            echo "   📅 Fecha: " . $ultima->created_at->format('d/m/Y H:i:s') . "\n";
        }
    } else {
        echo "   ❌ NO se creó ninguna notificación nueva\n";
    }
    echo "\n";
    
    // 7. Probar creación de movimiento real con Observer
    echo "7️⃣ PROBANDO CREACIÓN DE MOVIMIENTO REAL\n";
    $mobiliario = Mobiliario::first();
    $localizacion = Localizacion::where('id', '!=', $mobiliario->localizacion_id)->first();
    
    if ($mobiliario && $localizacion) {
        echo "   📦 Mobiliario: {$mobiliario->numero_control}\n";
        echo "   📍 Destino: {$localizacion->ubicacion_resumida}\n";
        
        $notificacionesAntesReal = $admin->notifications()->count();
        echo "   📫 Notificaciones antes del movimiento real: {$notificacionesAntesReal}\n";
        
        echo "   🔄 Creando movimiento real...\n";
        $movimientoReal = Movimiento::create([
            'mobiliario_id' => $mobiliario->id,
            'area_actual_id' => $localizacion->id,
            'area_anterior_id' => $mobiliario->localizacion_id,
            'fecha_movimiento' => now(),
            'se_entrega_con' => 'Juan Pérez',
            'se_retira_con' => 'María García',
            'observacion' => 'Prueba diagnóstico notificaciones',
            'usuario_id' => Auth::id(),
        ]);
        
        echo "   ✅ Movimiento creado: {$movimientoReal->numero_movimiento}\n";
        
        $admin->refresh();
        $notificacionesDespuesReal = $admin->notifications()->count();
        echo "   📫 Notificaciones después del movimiento real: {$notificacionesDespuesReal}\n";
        
        if ($notificacionesDespuesReal > $notificacionesAntesReal) {
            echo "   🎉 ¡Observer funcionó! Notificación creada por movimiento real\n";
        } else {
            echo "   ❌ Observer NO funcionó - ninguna notificación por movimiento real\n";
        }
    } else {
        echo "   ❌ No se pueden obtener datos para movimiento real\n";
    }
    echo "\n";
    
    // 8. Verificar logs del sistema
    echo "8️⃣ VERIFICANDO LOGS\n";
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $logLines = explode("\n", $logContent);
        $recentLogs = array_slice($logLines, -20); // Últimas 20 líneas
        
        echo "   📝 Últimas líneas del log:\n";
        foreach ($recentLogs as $line) {
            if (stripos($line, 'AdminNotificationService') !== false || 
                stripos($line, 'MovimientoObserver') !== false ||
                stripos($line, 'notificacion') !== false) {
                echo "   🔍 " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ❌ No se encontró archivo de log\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🏁 DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📚 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
