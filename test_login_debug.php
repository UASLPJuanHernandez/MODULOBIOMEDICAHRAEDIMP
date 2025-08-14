<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=== TEST LOGIN DEBUG ===\n";

// 1. Verificar usuario
$user = User::where('email', 'admin@inventario.hospital')->first();
if (!$user) {
    echo "❌ Usuario no encontrado\n";
    exit;
}
echo "✅ Usuario encontrado: {$user->email}\n";

// 2. Verificar password
if (!Hash::check('admin123', $user->password)) {
    echo "❌ Password incorrecto\n";
    exit;
}
echo "✅ Password correcto\n";

// 3. Intentar autenticación manual
Auth::login($user);
if (Auth::check()) {
    echo "✅ Autenticación manual exitosa\n";
    echo "Usuario autenticado: " . Auth::user()->email . "\n";
} else {
    echo "❌ Fallo en autenticación manual\n";
}

// 4. Verificar configuración de guards
$defaultGuard = config('auth.defaults.guard');
echo "Guard por defecto: {$defaultGuard}\n";

$guards = config('auth.guards');
echo "Guards disponibles: " . implode(', ', array_keys($guards)) . "\n";

// 5. Verificar sesión
echo "Driver de sesión: " . config('session.driver') . "\n";
echo "Tiempo de vida de sesión: " . config('session.lifetime') . " minutos\n";

echo "=== FIN TEST ===\n";
