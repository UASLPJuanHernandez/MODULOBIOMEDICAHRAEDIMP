<?php

namespace App\Observers;

use App\Models\Mobiliario;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class MobiliarioObserver
{
    public function created(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'creado');
        }
    }

    public function updated(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            if ($mobiliario->wasChanged('dado_de_baja') && $mobiliario->dado_de_baja) {
                AdminNotificationService::equipoBaja(Auth::user(), $mobiliario);
            } else {
                AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'modificado');
            }
        }
    }

    public function deleted(Mobiliario $mobiliario): void
    {
        if (Auth::check()) {
            AdminNotificationService::mobiliarioModified(Auth::user(), $mobiliario, 'eliminado');
        }
    }
}
