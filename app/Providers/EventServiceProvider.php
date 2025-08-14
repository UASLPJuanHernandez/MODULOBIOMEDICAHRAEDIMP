<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\AdminNotificationEvent::class => [
            \App\Listeners\DispatchAdminNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
