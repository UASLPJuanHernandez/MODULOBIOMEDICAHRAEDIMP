<?php

namespace App\Observers;

use App\Models\Localizacion;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class LocalizacionObserver
{
    /**
     * Handle the Localizacion "created" event.
     */
    public function created(Localizacion $localizacion): void
    {
        if (Auth::check()) {
            AdminNotificationService::localizacionCreated(Auth::user(), $localizacion);
        }
    }
}
