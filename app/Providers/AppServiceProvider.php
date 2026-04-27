<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Mobiliario;
use App\Observers\MobiliarioObserver;
use App\Models\InventarioEquipo;
use App\Observers\InventarioEquipoObserver;
use App\Models\ReportePizarron;
use App\Observers\ReportePizarronObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mobiliario::observe(MobiliarioObserver::class);
        InventarioEquipo::observe(InventarioEquipoObserver::class);
        ReportePizarron::observe(ReportePizarronObserver::class);
    }
}
