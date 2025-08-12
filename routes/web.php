<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vale/{vale}/imprimir', [ValeController::class, 'mostrarVista'])
    ->name('vale.imprimir');

// Rutas para Filament Admin
Route::get('/admin/vales/create-from-movimiento/{movimiento_id}', function ($movimiento_id) {
    return redirect()->route('filament.admin.resources.vales.create', [
        'movimiento_id' => $movimiento_id
    ]);
})->name('admin.vales.create-from-movimiento');
