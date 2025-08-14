<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 PRUEBA DE NOTIFICACIONES EN TIEMPO REAL CON LARAVEL REVERB\n";
echo str_repeat("=", 70) . "\n\n";

try {
    // 1. Verificar usuarios
    echo "1️⃣ VERIFICANDO USUARIOS\n";
    $admin = User::role('Administrador')->first();
    $userApoyo = User::where('email', 'apoyo@inventario.hospital')->first();
    
    if (!$admin || !$userApoyo) {
        echo "❌ Error: Faltan usuarios necesarios\n";
        exit(1);
    }
    
    echo "   ✅ Admin: {$admin->name}\n";
    echo "   ✅ User Apoyo: {$userApoyo->name}\n\n";
    
    // 2. Simular autenticación como usuario no admin
    echo "2️⃣ SIMULANDO AUTENTICACIÓN COMO USER APOYO\n";
    Auth::login($userApoyo);
    echo "   ✅ Usuario autenticado: " . Auth::user()->name . "\n";
    echo "   🔍 ¿Es administrador? " . (Auth::user()->hasRole('Administrador') ? 'SÍ' : 'NO') . "\n\n";
    
    // 3. Obtener datos para movimiento
    echo "3️⃣ PREPARANDO DATOS PARA MOVIMIENTO\n";
    $mobiliario = Mobiliario::first();
    $localizacion = Localizacion::where('id', '!=', $mobiliario->localizacion_id)->first();
    
    if (!$mobiliario || !$localizacion) {
        echo "❌ Error: Faltan datos para el movimiento\n";
        exit(1);
    }
    
    echo "   ✅ Mobiliario: {$mobiliario->numero_control}\n";
    echo "   ✅ Destino: {$localizacion->ubicacion_resumida}\n\n";
    
    // 4. Probar broadcast directo
    echo "4️⃣ PROBANDO BROADCAST DIRECTO\n";
    echo "   📡 Enviando notificación de prueba via Laravel Reverb...\n";
    
    AdminNotificationService::movimientoCreated(Auth::user(), (object)[
        'id' => 999,
        'numero_movimiento' => 'TEST-REVERB-' . time(),
        'mobiliarios' => collect([(object)['numero_control' => $mobiliario->numero_control]])
    ]);
    
    echo "   ✅ Notificación enviada\n\n";
    
    // 5. Crear movimiento real
    echo "5️⃣ CREANDO MOVIMIENTO REAL\n";
    echo "   🔄 Creando movimiento que activará el Observer...\n";
    
    $movimiento = Movimiento::create([
        'mobiliario_id' => $mobiliario->id,
        'area_actual_id' => $localizacion->id,
        'area_anterior_id' => $mobiliario->localizacion_id,
        'fecha_movimiento' => now(),
        'se_entrega_con' => 'Juan Pérez',
        'se_retira_con' => 'María García',
        'observacion' => 'Prueba notificaciones Reverb',
        'usuario_id' => Auth::id(),
    ]);
    
    echo "   ✅ Movimiento creado: {$movimiento->numero_movimiento}\n\n";
    
    // 6. Instrucciones para el usuario
    echo "6️⃣ INSTRUCCIONES PARA VERIFICAR\n";
    echo "   🌐 1. Abre el panel administrativo: http://localhost/admin\n";
    echo "   🔑 2. Inicia sesión como administrador:\n";
    echo "      📧 Email: admin@inventario.hospital\n";
    echo "      🔒 Password: admin123\n";
    echo "   📊 3. En el dashboard, busca el widget 'Notificaciones del Sistema'\n";
    echo "   🔔 4. Debería mostrar:\n";
    echo "      - Estado: 🟢 Conectado (si Reverb está funcionando)\n";
    echo "      - Las notificaciones que acabamos de enviar\n\n";
    
    // 7. Verificar estado de Reverb
    echo "7️⃣ VERIFICANDO ESTADO DE LARAVEL REVERB\n";
    $reverbPort = env('REVERB_PORT', 8080);
    $reverbHost = env('REVERB_HOST', 'localhost');
    
    echo "   🌐 Servidor Reverb: {$reverbHost}:{$reverbPort}\n";
    echo "   📡 Esquema: " . env('REVERB_SCHEME', 'http') . "\n";
    echo "   🔑 App Key: " . env('REVERB_APP_KEY') . "\n\n";
    
    // 8. Comandos útiles
    echo "8️⃣ COMANDOS ÚTILES PARA DEBUGGING\n";
    echo "   📋 Ver logs en tiempo real:\n";
    echo "      ./vendor/bin/sail logs laravel.test -f\n\n";
    echo "   🔄 Reiniciar Reverb:\n";
    echo "      ./vendor/bin/sail artisan reverb:restart\n\n";
    echo "   🐛 Iniciar Reverb en modo debug:\n";
    echo "      ./vendor/bin/sail artisan reverb:start --debug\n\n";
    
    echo "🎉 PRUEBA COMPLETADA\n";
    echo "Las notificaciones deberían aparecer en tiempo real en el dashboard del administrador.\n";
    echo "Si no aparecen, verifica que Laravel Reverb esté funcionando en el puerto 8080.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
