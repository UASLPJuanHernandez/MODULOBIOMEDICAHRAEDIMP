<?php

namespace App\Observers;

use App\Models\ClasificacionBien;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class ClasificacionBienObserver
{
    /**
     * Handle the ClasificacionBien "created" event.
     */
    public function created(ClasificacionBien $clasificacionBien): void
    {
        if (Auth::check()) {
            AdminNotificationService::clasificacionBienCreated(Auth::user(), $clasificacionBien);
        }
    }
}
