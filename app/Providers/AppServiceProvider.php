<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Mobiliario;
use App\Models\Movimiento;
use App\Models\Vale;
use App\Models\Localizacion;
use App\Models\ClasificacionBien;
use App\Observers\MobiliarioObserver;
use App\Observers\MovimientoObserver;
use App\Observers\ValeObserver;
use App\Observers\LocalizacionObserver;
use App\Observers\ClasificacionBienObserver;

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
        // Registrar observers para notificaciones
        Mobiliario::observe(MobiliarioObserver::class);
        Movimiento::observe(MovimientoObserver::class);
        Vale::observe(ValeObserver::class);
        Localizacion::observe(LocalizacionObserver::class);
        ClasificacionBien::observe(ClasificacionBienObserver::class);
    }
}
