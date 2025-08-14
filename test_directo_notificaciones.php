<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test Directo del AdminNotificationService\n\n";

try {
    // 1. Obtener usuarios
    $admin = User::role('Administrador')->first();
    $userApoyo = User::where('email', 'apoyo@inventario.hospital')->first();
    
    echo "👤 Admin: {$admin->name}\n";
    echo "👤 User Apoyo: {$userApoyo->name}\n\n";
    
    // 2. Simular login como usuario de apoyo
    Auth::login($userApoyo);
    echo "🔑 Logueado como: " . Auth::user()->name . "\n";
    echo "❓ ¿Es administrador? " . (Auth::user()->hasRole('Administrador') ? 'Sí' : 'No') . "\n\n";
    
    // 3. Verificar notificaciones del admin antes
    $notificacionesAntes = $admin->notifications()->count();
    echo "📫 Notificaciones del admin antes: {$notificacionesAntes}\n";
    
    // 4. Llamar directamente al servicio (simulando un movimiento ficticio)
    echo "\n🔔 Enviando notificación de prueba...\n";
    
    // Crear un objeto ficticio para simular un movimiento
    $movimientoFicticio = (object) [
        'id' => 999,
        'numero_movimiento' => 'MOV-TEST-' . time(),
        'mobiliarios' => collect([
            (object) ['numero_control' => 'TEST-001']
        ])
    ];
    
    AdminNotificationService::movimientoCreated(Auth::user(), $movimientoFicticio);
    
    // 5. Verificar notificaciones después
    $admin->refresh();
    $notificacionesDespues = $admin->notifications()->count();
    echo "📫 Notificaciones del admin después: {$notificacionesDespues}\n";
    
    if ($notificacionesDespues > $notificacionesAntes) {
        echo "✅ ¡Notificación enviada exitosamente!\n";
        
        $ultimaNotificacion = $admin->notifications()->latest()->first();
        if ($ultimaNotificacion) {
            $data = $ultimaNotificacion->data;
            echo "📝 Título: " . ($data['title'] ?? 'Sin título') . "\n";
            echo "📝 Mensaje: " . ($data['body'] ?? 'Sin mensaje') . "\n";
            echo "📅 Fecha: " . $ultimaNotificacion->created_at->format('d/m/Y H:i:s') . "\n";
        }
    } else {
        echo "❌ No se envió notificación\n";
    }
    
    // 6. Verificar también con un admin logueado
    echo "\n🔄 Probando con admin logueado...\n";
    Auth::login($admin);
    echo "🔑 Logueado como: " . Auth::user()->name . "\n";
    echo "❓ ¿Es administrador? " . (Auth::user()->hasRole('Administrador') ? 'Sí' : 'No') . "\n";
    
    $notificacionesAntesAdmin = $admin->notifications()->count();
    echo "📫 Notificaciones antes (admin logueado): {$notificacionesAntesAdmin}\n";
    
    AdminNotificationService::movimientoCreated(Auth::user(), $movimientoFicticio);
    
    $admin->refresh();
    $notificacionesDespuesAdmin = $admin->notifications()->count();
    echo "📫 Notificaciones después (admin logueado): {$notificacionesDespuesAdmin}\n";
    
    if ($notificacionesDespuesAdmin > $notificacionesAntesAdmin) {
        echo "⚠️  Se envió notificación incluso siendo admin (esto NO debería pasar)\n";
    } else {
        echo "✅ Correctamente NO se envió notificación al admin logueado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
