<?php

namespace App\Traits;

use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

trait TriggersAdminNotification
{
    protected function notifyAdmin(string $title, string $message, string $action, array $data = []): void
    {
        $user = Auth::user();
        if (!$user) { return; }
        AdminNotificationService::notify($title, $message, $action, $user, $data);
    }
}
