<?php

namespace App\Observers;

use App\Models\Mobiliario;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class MobiliarioObserver
{
    /**
     * Handle the Mobiliario "created" event.
     */
    public function created(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'creado');
        }
    }

    /**
     * Handle the Mobiliario "updated" event.
     */
    public function updated(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            // Verificar si fue dado de baja
            if ($mobiliario->wasChanged('dado_de_baja') && $mobiliario->dado_de_baja) {
                AdminNotificationService::equipoBaja(Auth::user(), $mobiliario);
            } else {
                AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'modificado');
            }
        }
    }

    /**
     * Handle the Mobiliario "deleted" event.
     */
    public function deleted(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'eliminado');
        }
    }
}
