@props([
    'livewire' => null,
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
    @class([
        'fi min-h-screen',
        'dark' => filament()->hasDarkModeForced(),
    ])
>
    <head>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $livewire?->getRenderHookScopes()) }}

        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @if ($favicon = filament()->getFavicon())
            <link rel="icon" href="{{ $favicon }}" />
        @endif

        @php
            $title = trim(strip_tags(($livewire ?? null)?->getTitle() ?? ''));
            $brandName = trim(strip_tags(filament()->getBrandName()));
        @endphp

        <title>
            {{ filled($title) ? "{$title} - " : null }} {{ $brandName }}
        </title>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        @filamentStyles

    {{-- Fix temporal de widgets eliminado: estilos normales restaurados --}}

        {{ filament()->getTheme()->getHtml() }}
        {{ filament()->getFontHtml() }}

        <style>
            :root {
                --font-family: '{!! filament()->getFontFamily() !!}';
                --sidebar-width: {{ filament()->getSidebarWidth() }};
                --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
                --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
            }
        </style>

        @stack('styles')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_AFTER, scopes: $livewire?->getRenderHookScopes()) }}

        @if (! filament()->hasDarkMode())
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        @elseif (filament()->hasDarkModeForced())
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        @else
            <script>
                const loadDarkMode = () => {
                    window.theme = localStorage.getItem('theme') ?? '{{ filament()->getDefaultThemeMode()->value }}';

                    if (
                        window.theme === 'dark' ||
                        (window.theme === 'system' &&
                            window.matchMedia('(prefers-color-scheme: dark)')
                                .matches)
                    ) {
                        document.documentElement.classList.add('dark')
                    }
                }

                loadDarkMode()

                document.addEventListener('livewire:navigated', loadDarkMode)
            </script>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $livewire?->getRenderHookScopes()) }}
    </head>

    <body
        {{ $attributes
                ->merge(($livewire ?? null)?->getExtraBodyAttributes() ?? [], escape: false)
                ->class([
                    'fi-body',
                    'fi-panel-' . filament()->getId(),
                    'min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white',
                ]) }}
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $livewire?->getRenderHookScopes()) }}

        {{ $slot }}

    {{-- Eliminado componente de notificaciones para evitar dependencias de Echo mientras se corrige broadcasting --}}
    {{-- @livewire(Filament\Livewire\Notifications::class) --}}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

        {{-- Script para deshabilitar Echo completamente ANTES de que se cargue Livewire --}}
        <script>
            // Mock robusto de Echo para evitar cualquier fallo de Livewire / Filament
            (function(){
                const noopChain = function(){ return this }
                const channelObj = { notification: noopChain, listen: noopChain, here: noopChain, joining: noopChain, leaving: noopChain }
                window.Echo = {
                    private: function(){ return channelObj },
                    channel: function(){ return channelObj },
                    join: function(){ return channelObj },
                    socketId: function(){ return 'offline-mock' },
                    leave: function(){ return this },
                    disconnect: function(){ return this }
                }
                // Evitar que cualquier intento de inicializar Echo real sobre‑escriba el mock
                Object.freeze(window.Echo)
                // Disparar evento para código que espera EchoLoaded
                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            })();
        </script>

        @filamentScripts(withCore: true)

        {{-- Broadcasting completamente deshabilitado --}}
        {{-- @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
            <script data-navigate-once>
                // Broadcasting deshabilitado: línea original de inicialización de EchoFactory comentada
                // window.Echo = new window.EchoFactory(config('filament.broadcasting.echo'));
                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            </script>
        @endif --}}

        @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
            <script>
                loadDarkMode()
            </script>
        @endif

        @stack('scripts')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $livewire?->getRenderHookScopes()) }}

        {{-- Safeguard: asegurar que window.Echo exista y tenga API mínima que Livewire espera --}}
        <script>
            (function ensureEchoSafe(){
                const buildEchoMock = () => {
                    const chain = { notification(){return this}, listen(){return this}, here(){return this}, joining(){return this}, leaving(){return this} };
                    return {
                        private(){ return chain },
                        channel(){ return chain },
                        join(){ return chain },
                        socketId(){ return null },
                        leave(){ return this },
                        disconnect(){ return this },
                    };
                };
                if (!window.Echo) {
                    window.Echo = buildEchoMock();
                } else {
                    if (typeof window.Echo.socketId !== 'function') {
                        try { window.Echo.socketId = () => null } catch(e) {}
                    }
                    ['private','channel','join'].forEach(m=>{
                        if (typeof window.Echo[m] !== 'function') {
                            window.Echo[m] = () => ({ notification(){return this}, listen(){return this} });
                        }
                    });
                }
                // Reintentar una vez tras carga completa por si otros scripts sobrescriben
                window.addEventListener('load', () => setTimeout(ensureEchoSafe, 50), { once: true });
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $livewire?->getRenderHookScopes()) }}
    </body>
</html>
