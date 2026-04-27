<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\Authenticate;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Área de Ingeniería Biomédica')
            ->colors([
                'primary' => Color::hex('#BC955C'),
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([])
            ->widgets([])
            ->plugins([
                FilamentFullCalendarPlugin::make()
                    ->schedulerLicenseKey('GPL-My-Project-Is-Open-Source'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications(false)
            ->databaseNotificationsPolling(null)
            ->assets([
                Js::make('pdfjs', 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'),
            ])
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn () => new HtmlString('<script src="/js/formatos-pdf-overlay.js"></script>'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => new HtmlString('
                    <div style="display: flex; justify-content: flex-end; padding: 8px 12px 12px 12px;">
                        <a
                            href="' . route('pizarron.standalone') . '"
                            target="_blank"
                            title="Abrir pizarrón en nueva pestaña"
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 6px;
                                padding: 6px 10px;
                                border-radius: 7px;
                                font-size: 11px;
                                color: #9ca3af;
                                text-decoration: none;
                                font-family: sans-serif;
                                border: 1px solid transparent;
                            "
                            onmouseover="this.style.background=\'rgba(0,0,0,0.05)\';this.style.color=\'#374151\';this.style.borderColor=\'#e5e7eb\'"
                            onmouseout="this.style.background=\'transparent\';this.style.color=\'#9ca3af\';this.style.borderColor=\'transparent\'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                '),
            );
    }
}
