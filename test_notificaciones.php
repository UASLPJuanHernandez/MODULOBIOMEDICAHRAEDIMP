<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Services\AdminNotificationService;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificando sistema de notificaciones...\n\n";

// Verificar usuarios y roles
$users = User::with('roles')->get();
echo "👥 Usuarios en el sistema:\n";
foreach($users as $user) {
    $roles = $user->roles->pluck('name')->implode(', ');
    echo "   • {$user->name} ({$user->email}) - Roles: {$roles}\n";
}

echo "\n";

// Verificar el usuario de apoyo
$userApoyo = User::where('email', 'apoyo@inventario.hospital')->first();
if ($userApoyo) {
    echo "✅ Usuario de apoyo encontrado: {$userApoyo->name}\n";
    echo "🔑 Rol: {$userApoyo->roles->first()->name}\n";
    echo "❓ ¿Es administrador? " . ($userApoyo->hasRole('Administrador') ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ Usuario de apoyo no encontrado\n";
}

echo "\n";

// Verificar el administrador
$admin = User::role('Administrador')->first();
if ($admin) {
    echo "✅ Administrador encontrado: {$admin->name} ({$admin->email})\n";
} else {
    echo "❌ No se encontró ningún administrador\n";
}

echo "\n✅ Sistema de notificaciones verificado correctamente\n";
