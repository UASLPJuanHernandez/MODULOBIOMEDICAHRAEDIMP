@php
    $plugin = \Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::get();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>

        {{-- Barra de busqueda y filtros --}}
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; align-items:flex-end;">

            {{-- Busqueda libre --}}
            <div style="flex:1; min-width:180px;">
                <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
                    Buscar
                </label>
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Título, descripción, ubicación…"
                        style="width:100%; padding:6px 10px 6px 30px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none;"
                    >
                </div>
            </div>

            {{-- Tipo --}}
            <div style="min-width:140px;">
                <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
                    Tipo
                </label>
                <select wire:model.live="filtroTipo"
                    style="width:100%; padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:white;">
                    <option value="">Todos</option>
                    <option value="reunion">Reunión</option>
                    <option value="mantenimiento">Mantenimiento</option>
                    <option value="inspeccion">Inspección</option>
                    <option value="capacitacion">Capacitación</option>
                    <option value="entrega">Entrega de equipo</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            {{-- Estado --}}
            <div style="min-width:130px;">
                <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
                    Estado
                </label>
                <select wire:model.live="filtroEstado"
                    style="width:100%; padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:white;">
                    <option value="">Todos</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="tentativo">Tentativo</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            {{-- Prioridad --}}
            <div style="min-width:130px;">
                <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
                    Prioridad
                </label>
                <select wire:model.live="filtroPrioridad"
                    style="width:100%; padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:white;">
                    <option value="">Todas</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            {{-- Responsable --}}
            <div style="min-width:160px;">
                <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
                    Responsable
                </label>
                <select wire:model.live="filtroResponsable"
                    style="width:100%; padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:white;">
                    <option value="">Todos</option>
                    @foreach(['Ing. María','Ing. Renata','Ing. María José','Ing. Ana Julia','Ing. Daniela','Ing. Flor','Ing. Pedro','Ing. Sergio','Ing. José','Ing. Juan Pablo'] as $ing)
                        <option value="{{ $ing }}">{{ $ing }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Boton limpiar --}}
            <div>
                <label style="font-size:11px; color:transparent; display:block; margin-bottom:4px;">_</label>
                <button
                    wire:click="$set('search',''); $set('filtroTipo',''); $set('filtroEstado',''); $set('filtroPrioridad',''); $set('filtroResponsable','');"
                    style="padding:6px 14px; border:1px solid #e5e7eb; border-radius:8px; font-size:12px; color:#6b7280; background:white; cursor:pointer;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='white'"
                >
                    Limpiar
                </button>
            </div>

        </div>

        {{-- Boton nuevo evento --}}
        <div class="flex justify-end flex-1 mb-4">
            <x-filament-actions::actions :actions="$this->getCachedHeaderActions()" class="shrink-0" />
        </div>

        {{-- Calendario --}}
        <div
            class="filament-fullcalendar"
            wire:ignore
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-fullcalendar-alpine', 'saade/filament-fullcalendar') }}"
            ax-load-css="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('filament-fullcalendar-styles', 'saade/filament-fullcalendar') }}"
            x-ignore
            x-data="fullcalendar({
                locale: @js($plugin->getLocale()),
                plugins: @js($plugin->getPlugins()),
                schedulerLicenseKey: @js($plugin->getSchedulerLicenseKey()),
                timeZone: @js($plugin->getTimezone()),
                config: @js($this->getConfig()),
                editable: @json($plugin->isEditable()),
                selectable: @json($plugin->isSelectable()),
                eventClassNames: {!! htmlspecialchars($this->eventClassNames(), ENT_COMPAT) !!},
                eventContent: {!! htmlspecialchars($this->eventContent(), ENT_COMPAT) !!},
                eventDidMount: {!! htmlspecialchars($this->eventDidMount(), ENT_COMPAT) !!},
                eventWillUnmount: {!! htmlspecialchars($this->eventWillUnmount(), ENT_COMPAT) !!},
            })"
        >
        </div>

    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
