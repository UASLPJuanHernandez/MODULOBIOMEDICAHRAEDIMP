<x-filament-panels::page>

    <div x-data="{ modalReporte: false }"
         @reporte-creado.window="modalReporte = false">

    {{-- Pizarron: blanco con solo el margen --}}
    <div style="
        min-height: 78vh;
        background: white;
        border-radius: 16px;
        padding: 32px;
        border: 2px solid #e5e7eb;
    ">

        {{-- Cabecera: titulo + botón agregar --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 32px;">
            <p style="color: #9ca3af; font-size: 13px; font-family: sans-serif; margin:0;">
                Reportes en tiempo real. Área de Ingeniería Biomédica del HRAE
            </p>
            <button
                @click="modalReporte = true"
                style="
                    display:inline-flex; align-items:center; gap:6px;
                    background:#2563eb; color:white;
                    border:none; border-radius:8px;
                    padding:8px 16px; font-size:13px; font-weight:600;
                    cursor:pointer; transition:background 0.15s;
                "
                onmouseover="this.style.background='#1d4ed8'"
                onmouseout="this.style.background='#2563eb'"
            >
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar reporte
            </button>
        </div>

        <div wire:poll.3s>
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
                                <span style="font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px;">Mensaje recibido</span>
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
        </div>{{-- cierra wire:poll --}}

    </div>

    {{-- ── MODAL: Agregar reporte manual ── --}}
    <div
        x-show="modalReporte"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px;"
        @click.self="modalReporte = false"
    >
        <div
            x-show="modalReporte"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            style="background:white; border-radius:14px; padding:28px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);"
            @click.stop
        >
            {{-- Cabecera modal --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
                <h2 style="font-size:17px; font-weight:700; color:#111827; margin:0;">Nuevo reporte manual</h2>
                <button @click="modalReporte = false" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px; line-height:1;">&times;</button>
            </div>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:10px 14px; margin-bottom:16px;">
                    @foreach($errors->all() as $error)
                        <p style="font-size:12px; color:#dc2626; margin:2px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Formulario --}}
            <div style="display:flex; flex-direction:column; gap:14px;">

                {{-- Título --}}
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">
                        Título / descripción corta <span style="color:#ef4444;">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="nr_titulo"
                        placeholder="Ej: Falla en ventilador UCI"
                        style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                        onfocus="this.style.borderColor='#2563eb'"
                        onblur="this.style.borderColor='#d1d5db'"
                    >
                </div>

                {{-- Descripción --}}
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">
                        Mensaje / descripción completa <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea
                        wire:model="nr_descripcion"
                        placeholder="Describe el problema con detalle..."
                        rows="3"
                        style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; resize:vertical; box-sizing:border-box;"
                        onfocus="this.style.borderColor='#2563eb'"
                        onblur="this.style.borderColor='#d1d5db'"
                    ></textarea>
                </div>

                {{-- Equipo + Ubicación --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Equipo</label>
                        <input
                            type="text"
                            wire:model="nr_equipo"
                            placeholder="Ej: Ventilador Drager"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#2563eb'"
                            onblur="this.style.borderColor='#d1d5db'"
                        >
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Ubicación / Área</label>
                        <input
                            type="text"
                            wire:model="nr_ubicacion"
                            placeholder="Ej: UCI Piso 2"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#2563eb'"
                            onblur="this.style.borderColor='#d1d5db'"
                        >
                    </div>
                </div>

                {{-- Prioridad + Responsable --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">
                            Prioridad <span style="color:#ef4444;">*</span>
                        </label>
                        <select
                            wire:model="nr_prioridad"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; background:white; outline:none; box-sizing:border-box;"
                        >
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="moderada">Moderada</option>
                            <option value="urgencia">Urgencia</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Responsable</label>
                        <select
                            wire:model="nr_responsable"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; background:white; outline:none; box-sizing:border-box;"
                        >
                            <option value="">— Sin asignar —</option>
                            @foreach(\App\Filament\Pages\Dashboard::getResponsables() as $ing)
                                <option value="{{ $ing }}">{{ $ing }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Quién reporta --}}
                <div style="border-top:1px solid #f3f4f6; padding-top:14px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Nombre de quien reporta</label>
                        <input
                            type="text"
                            wire:model="nr_reportante_nombre"
                            placeholder="Nombre completo"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#2563eb'"
                            onblur="this.style.borderColor='#d1d5db'"
                        >
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Servicio / Área</label>
                        <input
                            type="text"
                            wire:model="nr_reportante_servicio"
                            placeholder="Ej: Urgencias"
                            style="width:100%; padding:8px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#2563eb'"
                            onblur="this.style.borderColor='#d1d5db'"
                        >
                    </div>
                </div>

                {{-- Botones --}}
                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:6px;">
                    <button
                        type="button"
                        @click="modalReporte = false"
                        style="padding:9px 20px; border:1.5px solid #d1d5db; border-radius:8px; background:white; font-size:13px; font-weight:600; color:#374151; cursor:pointer;"
                    >Cancelar</button>
                    <button
                        type="button"
                        wire:click="crearReporte"
                        style="padding:9px 20px; background:#2563eb; border:none; border-radius:8px; font-size:13px; font-weight:600; color:white; cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8'"
                        onmouseout="this.style.background='#2563eb'"
                    >
                        <span wire:loading.remove wire:target="crearReporte">Guardar reporte</span>
                        <span wire:loading wire:target="crearReporte">Guardando...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    </div>{{-- cierra x-data --}}

</x-filament-panels::page>
