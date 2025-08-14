<?php

namespace App\Observers;

use App\Models\Vale;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

class ValeObserver
{
    /**
     * Handle the Vale "created" event.
     */
    public function created(Vale $vale): void
    {
        if (Auth::check()) {
            AdminNotificationService::valeCreated(Auth::user(), $vale);
        }
    }
}
