<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('echo', 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js'),
            Js::make('pusher', 'https://js.pusher.com/8.2.0/pusher.min.js'),
        ]);
    }
}
