<?php

namespace App\Listeners;

use App\Events\AdminNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;

class DispatchAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AdminNotificationEvent $event): void
    {
        // Aquí podrías persistir en base de datos si quieres un historial
        // Por ahora solo broadcast (ya lo hace el evento) o log
        \Log::info('Admin notification broadcasted', [
            'title' => $event->title,
            'action' => $event->action,
            'user' => $event->user->id,
        ]);
    }
}
