<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValeController;
use App\Http\Controllers\SimpleLoginController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de debugging eliminadas (dashboard-debug, test-dashboard, test-admin-access, test-login) - limpieza post migración a widgets definitivos

// Login simplificado
Route::get('/simple-login', [SimpleLoginController::class, 'showLoginForm'])->name('simple.login');
Route::post('/simple-login', [SimpleLoginController::class, 'login'])->name('simple.login.submit');
Route::post('/simple-logout', [SimpleLoginController::class, 'logout'])->name('simple.logout');

// Redirigir login principal al simplificado
Route::get('/admin/login', function() {
    return redirect('/simple-login');
});

Route::get('/vale/{vale}/imprimir', [ValeController::class, 'mostrarVista'])
    ->name('vale.imprimir');

// Rutas para Filament Admin
Route::get('/admin/vales/create-from-movimiento/{movimiento_id}', function ($movimiento_id) {
    return redirect()->route('filament.admin.resources.vales.create', [
        'movimiento_id' => $movimiento_id
    ]);
})->name('admin.vales.create-from-movimiento');
