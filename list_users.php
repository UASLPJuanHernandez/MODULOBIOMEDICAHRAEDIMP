<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n========================================\n";
echo "USUARIOS DE PRUEBA DISPONIBLES\n";
echo "========================================\n\n";

$users = App\Models\User::with('roles')->get();

foreach ($users as $user) {
    echo "📧 Email:    " . $user->email . "\n";
    echo "👤 Nombre:   " . $user->name . "\n";
    echo "🔐 Password: ";
    
    // Mostrar la contraseña según el email
    if ($user->email === 'admin@inventario.hospital') {
        echo "admin123\n";
    } elseif ($user->email === 'apoyo@inventario.hospital') {
        echo "apoyo123\n";
    } elseif ($user->email === 'jefe@inventario.hospital') {
        echo "jefe123\n";
    } else {
        echo "password\n";
    }
    
    echo "🎭 Roles:    " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "----------------------------------------\n\n";
}

echo "Total de usuarios: " . $users->count() . "\n\n";
