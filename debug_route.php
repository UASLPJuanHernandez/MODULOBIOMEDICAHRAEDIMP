<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\TestWidget;
use App\Filament\Widgets\MovimientosPendientesWidget;
use App\Filament\Widgets\AdminNotificationsWidget;

Route::get('/dashboard-debug', function() {
    if (!Auth::check()) {
        return redirect('/simple-login');
    }
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>Dashboard Debug</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .widget { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
            .success { background-color: #d4edda; }
            .error { background-color: #f8d7da; }
            .info { background-color: #d1ecf1; }
        </style>
    </head>
    <body>
        <h1>🔍 Diagnóstico del Dashboard</h1>
        <p><strong>Usuario autenticado:</strong> ' . Auth::user()->name . ' (' . Auth::user()->email . ')</p>
        <hr>';
    
    // Test TestWidget
    try {
        $widget = new TestWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($widget);
        
        $html .= '<div class="widget success">
            <h3>✅ TestWidget - FUNCIONANDO</h3>
            <p>Este widget tiene ' . count($stats) . ' estadísticas:</p>
            <ul>';
        foreach ($stats as $stat) {
            $html .= '<li>' . $stat->getLabel() . ': ' . $stat->getValue() . '</li>';
        }
        $html .= '</ul></div>';
    } catch (Exception $e) {
        $html .= '<div class="widget error">
            <h3>❌ TestWidget - ERROR</h3>
            <p>Error: ' . $e->getMessage() . '</p>
        </div>';
    }
    
    // Test MovimientosPendientesWidget
    try {
        $widget = new MovimientosPendientesWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($widget);
        
        $html .= '<div class="widget success">
            <h3>✅ MovimientosPendientesWidget - FUNCIONANDO</h3>
            <p>Este widget tiene ' . count($stats) . ' estadísticas:</p>
            <ul>';
        foreach ($stats as $stat) {
            $html .= '<li>' . $stat->getLabel() . ': ' . $stat->getValue() . '</li>';
        }
        $html .= '</ul></div>';
    } catch (Exception $e) {
        $html .= '<div class="widget error">
            <h3>❌ MovimientosPendientesWidget - ERROR</h3>
            <p>Error: ' . $e->getMessage() . '</p>
        </div>';
    }
    
    // Test AdminNotificationsWidget
    try {
        $widget = new AdminNotificationsWidget();
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($widget);
        
        $html .= '<div class="widget success">
            <h3>✅ AdminNotificationsWidget - FUNCIONANDO</h3>
            <p>Este widget tiene ' . count($stats) . ' estadísticas:</p>
            <ul>';
        foreach ($stats as $stat) {
            $html .= '<li>' . $stat->getLabel() . ': ' . $stat->getValue() . '</li>';
        }
        $html .= '</ul></div>';
    } catch (Exception $e) {
        $html .= '<div class="widget error">
            <h3>❌ AdminNotificationsWidget - ERROR</h3>
            <p>Error: ' . $e->getMessage() . '</p>
        </div>';
    }
    
    $html .= '<div class="widget info">
        <h3>📊 Información del Sistema</h3>
        <ul>
            <li>Usuarios en sistema: ' . \App\Models\User::count() . '</li>
            <li>Movimientos totales: ' . \App\Models\Movimiento::count() . '</li>
            <li>Movimientos pendientes: ' . \App\Models\Movimiento::whereNull("vale_id")->count() . '</li>
            <li>Sesión ID: ' . session()->getId() . '</li>
            <li>Middleware de auth funciona: ✅</li>
        </ul>
    </div>';
    
    $html .= '<hr>
        <p><a href="/admin">🔗 Ir al Dashboard de Filament</a></p>
        <p><a href="/simple-logout">🚪 Cerrar Sesión</a></p>
    </body>
    </html>';
    
    return $html;
});
