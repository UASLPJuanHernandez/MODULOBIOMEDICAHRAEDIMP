<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Verificar si el usuario ya existe
    $existingUser = App\Models\User::where('email', 'test@test.com')->first();
    
    if ($existingUser) {
        echo "Usuario ya existe: " . $existingUser->email . "\n";
        echo "Roles: " . $existingUser->getRoleNames()->implode(', ') . "\n";
    } else {
        // Crear nuevo usuario
        $user = new App\Models\User();
        $user->name = 'Usuario Test';
        $user->email = 'test@test.com';
        $user->password = Hash::make('password');
        $user->save();
        
        // Asignar rol de Usuario
        $user->assignRole('Usuario');
        
        echo "Usuario creado exitosamente: " . $user->email . "\n";
        echo "Rol asignado: Usuario\n";
    }
    
    // Mostrar usuario admin
    $admin = App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'Admin');
    })->first();
    
    if ($admin) {
        echo "Usuario administrador: " . $admin->email . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
