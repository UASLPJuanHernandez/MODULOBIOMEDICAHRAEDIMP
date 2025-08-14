<?php

namespace App\Observers;

use App\Models\Movimiento;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovimientoObserver
{
    /**
     * Handle the Movimiento "created" event.
     */
    public function created(Movimiento $movimiento): void
    {
        Log::info('MovimientoObserver::created ejecutándose para movimiento: ' . $movimiento->id);
        
        if (Auth::check()) {
            Log::info('Usuario autenticado: ' . Auth::user()->name . ' (ID: ' . Auth::id() . ')');
            AdminNotificationService::movimientoCreated(Auth::user(), $movimiento);
            Log::info('AdminNotificationService::movimientoCreated ejecutado');
        } else {
            Log::warning('No hay usuario autenticado para enviar notificación');
        }
    }
}
