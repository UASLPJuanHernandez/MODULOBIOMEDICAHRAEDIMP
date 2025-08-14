<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔔 Prueba del Sistema de Notificaciones\n\n";

try {
    // 1. Verificar usuarios y roles
    echo "1️⃣ Verificando usuarios...\n";
    $admin = User::role('Administrador')->first();
    $userApoyo = User::where('email', 'apoyo@inventario.hospital')->first();
    
    if (!$admin) {
        echo "❌ No se encontró usuario administrador\n";
        exit(1);
    }
    
    if (!$userApoyo) {
        echo "❌ No se encontró usuario de apoyo\n";
        exit(1);
    }
    
    echo "   ✅ Admin: {$admin->name} ({$admin->email})\n";
    echo "   ✅ User Apoyo: {$userApoyo->name} ({$userApoyo->email})\n\n";
    
    // 2. Simular login como usuario de apoyo (no admin)
    echo "2️⃣ Simulando login como usuario de apoyo...\n";
    Auth::login($userApoyo);
    echo "   ✅ Usuario logueado: " . Auth::user()->name . "\n\n";
    
    // 3. Obtener datos para crear movimiento
    echo "3️⃣ Preparando datos para movimiento...\n";
    $mobiliario = Mobiliario::first();
    $localizacion = Localizacion::where('id', '!=', $mobiliario->localizacion_id)->first();
    
    if (!$mobiliario || !$localizacion) {
        echo "❌ Faltan datos necesarios\n";
        exit(1);
    }
    
    echo "   ✅ Mobiliario: {$mobiliario->numero_control}\n";
    echo "   ✅ Destino: {$localizacion->ubicacion_resumida}\n\n";
    
    // 4. Verificar notificaciones antes
    echo "4️⃣ Verificando notificaciones del admin antes...\n";
    $notificacionesAntes = $admin->notifications()->count();
    echo "   📫 Notificaciones del admin antes: {$notificacionesAntes}\n\n";
    
    // 5. Crear movimiento (esto debería disparar el Observer)
    echo "5️⃣ Creando movimiento...\n";
    $movimiento = Movimiento::create([
        'mobiliario_id' => $mobiliario->id,
        'area_actual_id' => $localizacion->id,
        'area_anterior_id' => $mobiliario->localizacion_id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Prueba del sistema de notificaciones',
        'usuario_id' => Auth::id(),
    ]);
    
    echo "   ✅ Movimiento creado: {$movimiento->numero_movimiento}\n\n";
    
    // 6. Verificar notificaciones después
    echo "6️⃣ Verificando notificaciones del admin después...\n";
    $admin->refresh(); // Recargar desde DB
    $notificacionesDespues = $admin->notifications()->count();
    echo "   📫 Notificaciones del admin después: {$notificacionesDespues}\n";
    
    if ($notificacionesDespues > $notificacionesAntes) {
        echo "   🎉 ¡Nueva notificación enviada exitosamente!\n";
        
        // Mostrar la última notificación
        $ultimaNotificacion = $admin->notifications()->latest()->first();
        if ($ultimaNotificacion) {
            $data = $ultimaNotificacion->data;
            echo "   📝 Título: " . ($data['title'] ?? 'Sin título') . "\n";
            echo "   📝 Mensaje: " . ($data['body'] ?? 'Sin mensaje') . "\n";
        }
    } else {
        echo "   ❌ No se envió ninguna notificación nueva\n";
        
        // Verificar manualmente el servicio
        echo "\n7️⃣ Verificación manual del servicio...\n";
        \App\Services\AdminNotificationService::movimientoCreated(Auth::user(), $movimiento);
        
        $admin->refresh();
        $notificacionesManual = $admin->notifications()->count();
        echo "   📫 Notificaciones después de llamada manual: {$notificacionesManual}\n";
        
        if ($notificacionesManual > $notificacionesDespues) {
            echo "   ✅ El servicio funciona, pero el Observer no se ejecutó\n";
        } else {
            echo "   ❌ El servicio no está funcionando correctamente\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
}
