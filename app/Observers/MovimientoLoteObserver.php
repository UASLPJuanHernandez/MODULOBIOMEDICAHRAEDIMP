<?php

namespace App\Observers;

use App\Models\MovimientoLote;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class MovimientoLoteObserver
{
    public function created(MovimientoLote $movimientoLote): void
    {
        if (Auth::check()) {
            // Reutilizamos la notificación de movimiento
            AdminNotificationService::movimientoCreated(Auth::user(), $movimientoLote);
        }
    }
}
