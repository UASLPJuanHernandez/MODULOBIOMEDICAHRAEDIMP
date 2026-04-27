<x-filament-panels::page>

    {{-- Pizarron: blanco con solo el margen --}}
    <div style="
        min-height: 78vh;
        background: white;
        border-radius: 16px;
        padding: 32px;
        border: 2px solid #e5e7eb;
    ">

        {{-- Titulo discreto --}}
        <div style="text-align: center; margin-bottom: 32px;">
            <p style="color: #9ca3af; font-size: 13px; font-family: sans-serif;">
                Reportes en tiempo real. Área de Ingeniería Biomédica del HRAE
            </p>
        </div>

        @php $reportes = $this->getReportes(); @endphp

        @if($reportes->isEmpty())
            {{-- Pantalla vacia --}}
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 50vh; color: rgba(255,255,255,0.2);">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:64px;height:64px;margin-bottom:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p style="font-size: 18px; font-family: sans-serif;">Sin reportes activos</p>
            </div>
        @else
            {{-- Grid de post-its --}}
            <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">

                @foreach($reportes as $reporte)

                    @php
                        // Color del post-it segun prioridad
                        $colores = [
                            'baja'     => ['fondo' => '#fefce8', 'borde' => '#eab308'],
                            'media'    => ['fondo' => '#fef9c3', 'borde' => '#f59e0b'],
                            'moderada' => ['fondo' => '#ffedd5', 'borde' => '#f97316'],
                            'urgencia' => ['fondo' => '#fee2e2', 'borde' => '#ef4444'],
                        ];
                        $colorFondo = $colores[$reporte->prioridad]['fondo'] ?? '#fefce8';
                        $colorBorde = $colores[$reporte->prioridad]['borde'] ?? '#eab308';

                        // Color de la etiqueta de estado
                        $estadoEstilo = match($reporte->estado) {
                            'en_curso'   => 'background:#16a34a;color:white;',
                            'completado' => 'background:#6b7280;color:white;',
                            default      => 'background:#111827;color:white;',
                        };
                        $etiquetaTexto = match($reporte->estado) {
                            'en_curso'   => 'En curso',
                            'completado' => 'Terminado',
                            default      => 'Pendiente',
                        };

                        // Etiqueta de prioridad
                        $prioridadEstilo = match($reporte->prioridad) {
                            'media'    => 'background:#f59e0b;color:white;',
                            'moderada' => 'background:#f97316;color:white;',
                            'urgencia' => 'background:#ef4444;color:white;',
                            default    => 'background:#eab308;color:#1a1a1a;',
                        };
                        $prioridadTexto = match($reporte->prioridad) {
                            'media'    => 'Media',
                            'moderada' => 'Moderada',
                            'urgencia' => 'Urgencia',
                            default    => 'Baja',
                        };
                    @endphp

                    @if($reporte->minimizado)

                        {{-- POST-IT MINIMIZADO --}}
                        <div
                            data-reporte="{{ $reporte->id }}"
                            wire:click="toggleMinimizado({{ $reporte->id }})"
                            title="Click para expandir"
                            style="
                                background: {{ $colorFondo }};
                                width: 130px;
                                min-height: 80px;
                                border-radius: 2px 8px 8px 2px;
                                padding: 10px 12px;
                                box-shadow: 3px 5px 10px rgba(0,0,0,0.4);
                                cursor: pointer;
                                border-left: 4px solid {{ $colorBorde }};
                                display: flex;
                                flex-direction: column;
                                justify-content: space-between;
                                transition: transform 0.1s;
                                position: relative;
                            "
                            onmouseover="this.style.transform='scale(1.05)'"
                            onmouseout="this.style.transform='scale(1)'"
                        >
                            <p style="font-size: 11px; font-weight: bold; color: #1a1a1a; margin: 0 0 8px 0; line-height: 1.3;">
                                {{ Str::limit($reporte->titulo, 40) }}
                            </p>
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 9px; padding: 2px 6px; border-radius: 999px; {{ $estadoEstilo }}">
                                    {{ $etiquetaTexto }}
                                </span>
                                <span style="font-size: 9px; padding: 2px 6px; border-radius: 999px; {{ $prioridadEstilo }}">
                                    {{ $prioridadTexto }}
                                </span>
                                <span style="font-size: 14px; color: #666;">⤢</span>
                            </div>
                        </div>

                    @else

                        {{-- POST-IT EXPANDIDO --}}
                        <div
                            data-reporte="{{ $reporte->id }}"
                            style="
                            background: {{ $colorFondo }};
                            width: 290px;
                            border-radius: 2px 8px 8px 2px;
                            padding: 16px;
                            box-shadow: 5px 8px 18px rgba(0,0,0,0.45);
                            border-left: 5px solid {{ $colorBorde }};
                            position: relative;
                        ">
                            {{-- Cabecera: titulo + boton minimizar --}}
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <h3 style="font-size: 13px; font-weight: bold; color: #111; margin: 0; flex: 1; line-height: 1.4;">
                                    {{ $reporte->titulo }}
                                </h3>
                                <button
                                    wire:click="toggleMinimizado({{ $reporte->id }})"
                                    title="Minimizar"
                                    style="background:none; border:none; cursor:pointer; font-size:18px; color:#777; margin-left:8px; line-height:1;"
                                >⤡</button>
                            </div>

                            {{-- Etiquetas de estado y prioridad --}}
                            <div style="display:flex; gap:6px; margin-bottom: 12px; flex-wrap:wrap;">
                                <span style="font-size: 10px; padding: 3px 8px; border-radius: 999px; font-weight: bold; {{ $estadoEstilo }}">
                                    {{ $etiquetaTexto }}
                                </span>
                                <span style="font-size: 10px; padding: 3px 8px; border-radius: 999px; font-weight: bold; {{ $prioridadEstilo }}">
                                    {{ $prioridadTexto }}
                                </span>
                            </div>

                            {{-- Datos del reporte --}}
                            @if($reporte->equipo)
                            <div style="margin-bottom: 7px;">
                                <span style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px;">Equipo</span>
                                <p style="font-size: 12px; color: #222; margin: 2px 0 0 0; font-weight: 600;">{{ $reporte->equipo }}</p>
                            </div>
                            @endif

                            @if($reporte->ubicacion)
                            <div style="margin-bottom: 7px;">
                                <span style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px;">Ubicación</span>
                                <p style="font-size: 12px; color: #222; margin: 2px 0 0 0;">{{ $reporte->ubicacion }}</p>
                            </div>
                            @endif

                            <div style="margin-bottom: 14px;">
                                <span style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px;">Descripción</span>
                                <p style="font-size: 11px; color: #333; margin: 4px 0 0 0; line-height: 1.5;">{{ $reporte->descripcion_original ?? $reporte->descripcion }}</p>
                            </div>

                            @if($reporte->reportante_nombre)
                            <div style="margin-bottom: 8px;">
                                <span style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px;">Reportado por</span>
                                <p style="font-size: 11px; color: #555; margin: 3px 0 0 0;">
                                    {{ $reporte->reportante_nombre }}
                                    @if($reporte->reportante_servicio)
                                        &middot; <em>{{ $reporte->reportante_servicio }}</em>
                                    @endif
                                </p>
                            </div>
                            @endif

                            {{-- Separador --}}
                            <hr style="border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 12px 0;">

                            {{-- Selector de estado --}}
                            <div style="margin-bottom: 8px;">
                                <label style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; display:block; margin-bottom:4px;">
                                    Estado
                                </label>
                                <select
                                    wire:change="cambiarEstado({{ $reporte->id }}, $event.target.value)"
                                    style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 12px; background: white; cursor: pointer;"
                                >
                                    <option value="pendiente"  {{ $reporte->estado === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_curso"   {{ $reporte->estado === 'en_curso'   ? 'selected' : '' }}>En curso</option>
                                    <option value="completado" {{ $reporte->estado === 'completado' ? 'selected' : '' }}>Completado (retirar)</option>
                                </select>
                            </div>

                            {{-- Selector de prioridad --}}
                            <div style="margin-bottom: 8px;">
                                <label style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; display:block; margin-bottom:4px;">
                                    Prioridad
                                </label>
                                <select
                                    wire:change="cambiarPrioridad({{ $reporte->id }}, $event.target.value)"
                                    style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 12px; background: white; cursor: pointer;"
                                >
                                    <option value="baja"     {{ $reporte->prioridad === 'baja'     ? 'selected' : '' }}>Baja</option>
                                    <option value="media"    {{ $reporte->prioridad === 'media'    ? 'selected' : '' }}>Media</option>
                                    <option value="moderada" {{ $reporte->prioridad === 'moderada' ? 'selected' : '' }}>Moderada</option>
                                    <option value="urgencia" {{ $reporte->prioridad === 'urgencia' ? 'selected' : '' }}>Urgencia</option>
                                </select>
                            </div>

                            {{-- Selector de responsable --}}
                            <div style="margin-bottom: 14px;">
                                <label style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; display:block; margin-bottom:4px;">
                                    Responsable
                                </label>
                                <select
                                    wire:change="asignarResponsable({{ $reporte->id }}, $event.target.value)"
                                    style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 12px; background: white; cursor: pointer;"
                                >
                                    <option value="">— Sin asignar —</option>
                                    @foreach(\App\Filament\Pages\Dashboard::getResponsables() as $ing)
                                        <option value="{{ $ing }}" {{ $reporte->responsable === $ing ? 'selected' : '' }}>
                                            {{ $ing }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($reporte->responsable)
                                    <p style="font-size: 11px; color: #555; margin: 4px 0 0 0;">
                                        Asignado a: <strong>{{ $reporte->responsable }}</strong>
                                    </p>
                                @endif
                            </div>

                            {{-- Boton eliminar --}}
                            <button
                                wire:click="eliminar({{ $reporte->id }})"
                                wire:confirm="¿Eliminar este reporte del pizarrón?"
                                style="
                                    width: 100%;
                                    padding: 6px;
                                    background: #dc2626;
                                    color: white;
                                    border: none;
                                    border-radius: 6px;
                                    font-size: 11px;
                                    font-weight: bold;
                                    cursor: pointer;
                                    letter-spacing: 0.5px;
                                "
                                onmouseover="this.style.background='#b91c1c'"
                                onmouseout="this.style.background='#dc2626'"
                            >
                                Eliminar del pizarrón
                            </button>

                        </div>

                    @endif

                @endforeach

            </div>
        @endif

    </div>


    @script
    <script>
        setInterval(() => $wire.$refresh(), 8000);
    </script>
    @endscript

</x-filament-panels::page>
