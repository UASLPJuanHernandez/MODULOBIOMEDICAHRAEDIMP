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

// Ruta para generar vale PDF de mantenimiento
Route::get('/mantenimiento/{mantenimiento}/vale-pdf', [App\Http\Controllers\MantenimientoController::class, 'generarValePDF'])
    ->name('mantenimiento.vale.pdf')
    ->middleware('auth');

// Rutas para Auditorías
Route::get('/auditoria/{auditoria}/ejecutar', function ($auditoria) {
    return redirect()->route('filament.admin.resources.auditorias.ejecutar', ['record' => $auditoria]);
})->name('auditoria.ejecutar')->middleware('auth');

Route::get('/auditoria/{auditoria}/vale/{item}/pdf', [App\Http\Controllers\AuditoriaController::class, 'generarValePDF'])
    ->name('auditoria.vale.pdf')
    ->middleware('auth');

Route::get('/auditoria/{auditoria}/reporte-pdf', [App\Http\Controllers\AuditoriaController::class, 'generarReportePDF'])
    ->name('auditoria.reporte.pdf')
    ->middleware('auth');

// Pizarrón en pantalla completa (sin layout de Filament)
Route::get('/pizarron', function () {
    $reportes = \App\Models\ReportePizarron::activos()->orderBy('created_at', 'asc')->get();
    return view('pizarron-standalone', compact('reportes'));
})->name('pizarron.standalone')->middleware('auth');

// Rutas para Filament Admin
Route::get('/admin/vales/create-from-movimiento/{movimiento_id}', function ($movimiento_id) {
    return redirect()->route('filament.admin.resources.vales.create', [
        'movimiento_id' => $movimiento_id
    ]);
})->name('admin.vales.create-from-movimiento');

// Servir archivos de formatos (docx/pdf)
Route::get('/formato-archivo/{formato}', function (\App\Models\Formato $formato) {
    $disk = \Illuminate\Support\Facades\Storage::disk('local');
    abort_unless($disk->exists($formato->archivo_path), 404);

    $ext  = strtolower(pathinfo($formato->archivo_original, PATHINFO_EXTENSION));
    $mime = match($ext) {
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        default => 'application/octet-stream',
    };

    return response($disk->get($formato->archivo_path), 200, [
        'Content-Type'        => $mime,
        'Content-Disposition' => ($ext === 'pdf' ? 'inline' : 'attachment')
                                 . '; filename="' . rawurlencode($formato->archivo_original) . '"',
        'X-Frame-Options'     => 'SAMEORIGIN',
        'Cache-Control'       => 'private, no-cache',
    ]);
})->name('formato.archivo')->middleware('auth');
