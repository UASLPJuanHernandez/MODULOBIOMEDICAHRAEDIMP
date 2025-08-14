<?php

Route::get('/test-login', function () {
    // Autenticar al usuario admin directamente
    $user = App\Models\User::where('email', 'admin@inventario.hospital')->first();
    
    if ($user) {
        Auth::login($user);
        return redirect('/admin');
    }
    
    return 'Usuario no encontrado';
});
