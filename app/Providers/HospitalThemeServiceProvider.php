<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;

class HospitalThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => Blade::render('
                <style>
                    /* Header personalizado - Color dorado (solo topbar, NO encabezado de página) */
                    .fi-topbar,
                    .fi-simple-header,
                    .fi-navbar,
                    .fi-sidebar-header {
                        background-color: #BC955C !important;
                        border-bottom-color: #BC955C !important;
                    }

                    /* En móvil el nav interno del topbar tiene bg-white — sobreescribir solo en pantalla chica */
                    @media (max-width: 1023px) {
                        .fi-topbar nav {
                            background-color: #BC955C !important;
                        }
                    }

                    .fi-topbar .fi-topbar-item,
                    .fi-navbar .fi-navbar-item,
                    .fi-sidebar-header .fi-sidebar-header-heading {
                        color: white !important;
                    }

                    /* Flechas de abrir/cerrar sidebar — blancas */
                    .fi-sidebar-header .fi-icon-btn,
                    .fi-sidebar-header .fi-icon-btn-icon,
                    .fi-topbar-open-sidebar-btn,
                    .fi-topbar-open-sidebar-btn .fi-icon-btn-icon,
                    .fi-topbar-close-sidebar-btn,
                    .fi-topbar-close-sidebar-btn .fi-icon-btn-icon {
                        color: white !important;
                    }

                    .fi-sidebar-header .fi-icon-btn svg,
                    .fi-topbar-open-sidebar-btn svg,
                    .fi-topbar-close-sidebar-btn svg {
                        stroke: white !important;
                        color: white !important;
                    }

                    .fi-sidebar-header .fi-icon-btn:hover {
                        background-color: rgba(255,255,255,0.15) !important;
                    }

                    /* Encabezado de página: sin fondo dorado, título grande */
                    .fi-header {
                        background: transparent !important;
                    }

                    .fi-header-heading {
                        font-size: 2.25rem !important;
                        font-weight: 800 !important;
                        color: #111827 !important;
                    }

                    /* Logo o título del sistema */
                    .fi-logo,
                    .fi-simple-header-heading,
                    .fi-sidebar-header-heading {
                        color: white !important;
                        font-weight: bold !important;
                    }

                    /* Navegación sidebar activa */
                    .fi-sidebar-nav-item.fi-active {
                        background-color: rgba(188, 149, 92, 0.1) !important;
                        border-left: 4px solid #BC955C !important;
                    }

                    .fi-sidebar-nav-item.fi-active .fi-sidebar-nav-item-label {
                        color: #BC955C !important;
                        font-weight: 600 !important;
                    }

                    .fi-sidebar-nav-item.fi-active .fi-sidebar-nav-item-icon {
                        color: #BC955C !important;
                    }

                    /* Botones primarios con color dorado */
                    .fi-btn.fi-btn-primary {
                        background-color: #BC955C !important;
                        border-color: #BC955C !important;
                        color: white !important;
                    }

                    .fi-btn.fi-btn-primary:hover {
                        background-color: #a67f4a !important;
                        border-color: #a67f4a !important;
                    }

                    /* Enlaces primarios */
                    .fi-link.fi-link-primary {
                        color: #BC955C !important;
                    }

                    .fi-link.fi-link-primary:hover {
                        color: #a67f4a !important;
                    }

                    /* Badges primarios */
                    .fi-badge.fi-color-primary {
                        background-color: #BC955C !important;
                        color: white !important;
                    }

                    /* Elementos de formulario focus */
                    .fi-input:focus,
                    .fi-select-input:focus,
                    .fi-textarea:focus {
                        border-color: #BC955C !important;
                        box-shadow: 0 0 0 1px #BC955C !important;
                    }

                    /* Tabs activos */
                    .fi-tabs-tab.fi-active {
                        border-bottom-color: #BC955C !important;
                        color: #BC955C !important;
                    }

                    /* Stats cards */
                    .fi-stats-overview-card {
                        border-left: 4px solid #BC955C !important;
                    }

                    /* Modal headers */
                    .fi-modal-header {
                        background-color: #BC955C !important;
                        color: white !important;
                    }

                    /* Footer personalizado - Color vino */
                    .fi-footer,
                    .fi-simple-footer,
                    footer[class*="fi-"],
                    body > div[class*="fi-"] > footer {
                        background-color: #691C32 !important;
                        color: white !important;
                        border-top: 2px solid #691C32 !important;
                    }

                    .fi-footer p,
                    .fi-footer span,
                    .fi-footer a,
                    .fi-simple-footer p,
                    .fi-simple-footer span,
                    .fi-simple-footer a {
                        color: white !important;
                    }

                    /* Estilos para el formulario de login */
                    .fi-simple-main {
                        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
                        min-height: 100vh !important;
                    }

                    .fi-simple-page {
                        background: white !important;
                        border-radius: 12px !important;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
                        overflow: hidden !important;
                    }

                    /* Logo en el login */
                    .hospital-logo-container {
                        text-align: center;
                        padding: 2rem 2rem 1rem 2rem;
                        background: linear-gradient(135deg, #BC955C 0%, #a67f4a 100%);
                        margin: -2rem -2rem 2rem -2rem;
                    }

                    .hospital-logo {
                        max-width: 100%;
                        height: auto;
                        max-height: 120px;
                        background: white;
                        padding: 10px;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    }

                    .login-title {
                        color: white;
                        font-size: 1.25rem;
                        font-weight: bold;
                        margin-top: 1rem;
                        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    }
                </style>
            ')
        );

        // Hook para agregar contenido al footer
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render('
                <div style="background-color: #691C32; color: white; text-align: center; padding: 1rem; margin-top: auto;">
                    <p style="margin: 0; color: white; font-weight: bold;">
                        Área de Ingeniería Biomédica
                    </p>
                    <p style="margin: 0; color: white; font-size: 0.75rem;">
                        Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"
                    </p>
                </div>
            ')
        );

        // Hook para personalizar la página de login
        FilamentView::registerRenderHook(
            'panels::auth.login.form.before',
            fn (): string => Blade::render('
                <div class="hospital-logo-container">
                    <div style="background: white; padding: 15px; border-radius: 10px; display: inline-block; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                            
                            <!-- Logo Hospital -->
                            <div style="text-align: center;">
                                <div style="border: 2px solid #2E8B57; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px;">
                                    <span style="font-size: 18px; font-weight: bold; color: #2E8B57;">HR</span>
                                </div>
                                <div style="font-size: 11px; color: #666; text-align: center; line-height: 1.1;">
                                    <div style="font-weight: bold;">HOSPITAL REGIONAL</div>
                                    <div>DE ALTA ESPECIALIDAD</div>
                                    <div style="font-weight: bold;">DR. IGNACIO MORONES PRIETO</div>
                                    <div style="letter-spacing: 2px; margin-top: 2px;">SAN LUIS POTOSÍ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="login-title">
                        Sistema de Activo Fijo.
                    </div>
                    <div style="color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-top: 0.5rem;">
                     
                    </div>
                </div>
            ')
        );
    }
}
