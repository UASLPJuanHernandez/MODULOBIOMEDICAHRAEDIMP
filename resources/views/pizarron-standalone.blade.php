<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizarrón — Depto. Ingeniería Biomédica</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; min-height: 100vh; padding: 24px; }

        /* --- Pizarron --- */
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .header h1 { font-size: 18px; font-weight: 600; color: #374151; }
        .header p  { font-size: 12px; color: #9ca3af; margin-top: 2px; }
        .badge-count { background: #f3f4f6; color: #6b7280; font-size: 12px; padding: 4px 12px; border-radius: 999px; border: 1px solid #e5e7eb; }
        .pizarron { border: 2px solid #e5e7eb; border-radius: 16px; background: white; min-height: 80vh; padding: 28px; }
        .grid { display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-start; }
        .postit { border-radius: 3px 10px 10px 3px; padding: 16px; box-shadow: 2px 4px 12px rgba(0,0,0,0.1); }
        .postit-baja     { background: #fefce8; border-left: 5px solid #eab308; width: 270px; }
        .postit-media    { background: #fef9c3; border-left: 5px solid #f59e0b; width: 270px; }
        .postit-moderada { background: #ffedd5; border-left: 5px solid #f97316; width: 270px; }
        .postit-urgencia { background: #fee2e2; border-left: 5px solid #ef4444; width: 270px; }
        .postit-title { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 10px; line-height: 1.4; }
        .postit-label { font-size: 10px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }
        .postit-value { font-size: 12px; color: #222; margin: 2px 0 8px 0; }
        .postit-desc  { font-size: 11px; color: #444; line-height: 1.5; margin: 2px 0 8px 0; }
        .badge { display: inline-block; font-size: 10px; font-weight: bold; padding: 3px 8px; border-radius: 999px; }
        .badge-pendiente  { background: #111827; color: white; }
        .badge-en_curso   { background: #16a34a; color: white; }
        .badge-completado { background: #6b7280; color: white; }
        .badge-baja     { background: #eab308; color: #1a1a1a; }
        .badge-media    { background: #f59e0b; color: white; }
        .badge-moderada { background: #f97316; color: white; }
        .badge-urgencia { background: #ef4444; color: white; }
        .responsable { margin-top: 8px; font-size: 11px; color: #555; border-top: 1px solid rgba(0,0,0,0.08); padding-top: 8px; }
        .empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; color: #d1d5db; }
        .empty svg { width: 56px; height: 56px; margin-bottom: 12px; }
        .empty p { font-size: 16px; }

        /* --- Calendario --- */
        #seccion-calendario {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 100;
            background: #f8fafc;
            padding: 32px;
            overflow-y: auto;
        }
        .cal-header { font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 24px; }
        .cal-mes { margin-bottom: 28px; }
        .cal-mes-titulo { font-size: 14px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }
        .cal-evento { display: flex; align-items: flex-start; gap: 12px; padding: 10px 14px; background: white; border-radius: 8px; margin-bottom: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border-left: 4px solid #3b82f6; }
        .cal-evento-fecha { font-size: 11px; color: #9ca3af; white-space: nowrap; padding-top: 1px; min-width: 80px; }
        .cal-evento-titulo { font-size: 13px; font-weight: 600; color: #111827; }
        .cal-evento-sub { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .cal-vacio { color: #9ca3af; font-size: 13px; font-style: italic; }

        /* --- Salvapantallas --- */
        #salvapantallas {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: #000 center center / cover no-repeat;
        }

    </style>
</head>
<body>

    @php $salvapantallasUrl = \App\Filament\Pages\CambiarFondoLogin::getSalvapantallasUrl(); @endphp

    {{-- Overlay salvapantallas --}}
    @if($salvapantallasUrl)
    <div id="salvapantallas" style="background-image: url('{{ $salvapantallasUrl }}');"></div>
    @else
    <div id="salvapantallas" style="background: #111;"></div>
    @endif

    {{-- ======= PIZARRON ======= --}}
    <div id="seccion-pizarron">
        <div class="header">
            <div>
                <h1>Pizarrón de Reportes</h1>
                <p>Departamento de Ingeniería Biomédica — HRAE</p>
            </div>
            <span class="badge-count" id="badge-count">{{ $reportes->count() }} reporte(s) activo(s)</span>
        </div>

        <div class="pizarron" id="contenido-pizarron">
            @if($reportes->isEmpty())
                <div class="empty">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>Sin reportes activos</p>
                </div>
            @else
                <div class="grid">
                    @foreach($reportes as $reporte)
                        <div class="postit postit-{{ $reporte->prioridad }}">
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">
                                <span class="badge badge-{{ $reporte->estado }}">
                                    {{ match($reporte->estado) { 'en_curso' => 'En curso', 'completado' => 'Terminado', default => 'Pendiente' } }}
                                </span>
                                <span class="badge badge-{{ $reporte->prioridad }}">
                                    {{ match($reporte->prioridad) { 'media' => 'Media', 'moderada' => 'Moderada', 'urgencia' => 'Urgencia', default => 'Baja' } }}
                                </span>
                            </div>
                            <p class="postit-title">{{ $reporte->titulo }}</p>
                            @if($reporte->equipo)
                                <p class="postit-label">Equipo</p>
                                <p class="postit-value">{{ $reporte->equipo }}</p>
                            @endif
                            @if($reporte->ubicacion)
                                <p class="postit-label">Ubicación</p>
                                <p class="postit-value">{{ $reporte->ubicacion }}</p>
                            @endif
                            <p class="postit-label">Mensaje recibido</p>
                            <p class="postit-desc">{{ $reporte->descripcion_original ?? $reporte->descripcion }}</p>
                            @if($reporte->responsable)
                                <div class="responsable">Responsable: <strong>{{ $reporte->responsable }}</strong></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ======= CALENDARIO ======= --}}
    <div id="seccion-calendario">
        <p class="cal-header">Calendario — Ingeniería Biomédica</p>

        @php
            $porMes = $eventos->groupBy(fn($e) => \Carbon\Carbon::parse($e->fecha_inicio)->translatedFormat('F Y'));
        @endphp

        @if($eventos->isEmpty())
            <p class="cal-vacio">Sin eventos próximos.</p>
        @else
            @foreach($porMes as $mes => $evs)
                <div class="cal-mes">
                    <p class="cal-mes-titulo">{{ $mes }}</p>
                    @foreach($evs as $ev)
                        @php $fi = \Carbon\Carbon::parse($ev->fecha_inicio); @endphp
                        <div class="cal-evento" style="border-left-color: {{ $ev->color ?? '#3b82f6' }}">
                            <div class="cal-evento-fecha">
                                {{ $fi->translatedFormat('D d') }}
                                @if($ev->todo_el_dia)
                                    · <span style="font-size:9px;opacity:0.8;">Todo el día</span>
                                @elseif(!$fi->isStartOfDay())
                                    · {{ $fi->format('H:i') }}
                                @endif
                            </div>
                            <div>
                                <div class="cal-evento-titulo">{{ $ev->titulo }}</div>
                                @if($ev->ubicacion || $ev->responsable)
                                    <div class="cal-evento-sub">
                                        {{ $ev->ubicacion }}{{ ($ev->ubicacion && $ev->responsable) ? ' · ' : '' }}{{ $ev->responsable }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>


    <script>
        var lastCount    = {{ $reportes->count() }};
        var enCalendario = false;
        var tick         = 0;
        var calTick      = 0;

        // Salvapantallas: inactividad en segundos (30 min = 1800)
        var IDLE_LIMITE  = 1800;
        var idleSecs     = 0;
        var enSalvapantallas = false;

        function mostrarSalvapantallas() {
            enSalvapantallas = true;
            document.getElementById('salvapantallas').style.display = 'block';
        }

        function ocultarSalvapantallas() {
            if (!enSalvapantallas) return;
            enSalvapantallas = false;
            idleSecs = 0;
            document.getElementById('salvapantallas').style.display = 'none';
            mostrarPizarron();
        }

        function registrarActividad() {
            idleSecs = 0;
            if (enSalvapantallas) ocultarSalvapantallas();
        }

        function mostrarCalendario() {
            enCalendario = true;
            calTick = 0;
            document.getElementById('seccion-pizarron').style.display  = 'none';
            document.getElementById('seccion-calendario').style.display = 'block';
        }

        function mostrarPizarron() {
            enCalendario = false;
            tick = 0;
            document.getElementById('seccion-calendario').style.display = 'none';
            document.getElementById('seccion-pizarron').style.display   = 'block';
            refreshPizarron();
        }

        function refreshPizarron() {
            fetch('/pizarron')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var newGrid  = doc.getElementById('contenido-pizarron');
                    var curGrid  = document.getElementById('contenido-pizarron');
                    if (newGrid && curGrid) curGrid.innerHTML = newGrid.innerHTML;
                    var newBadge = doc.getElementById('badge-count');
                    var badge    = document.getElementById('badge-count');
                    if (newBadge && badge) badge.textContent = newBadge.textContent;
                })
                .catch(function() {});
        }

        function refreshCalendario() {
            fetch('/pizarron')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var doc    = new DOMParser().parseFromString(html, 'text/html');
                    var newCal = doc.getElementById('seccion-calendario');
                    var curCal = document.getElementById('seccion-calendario');
                    if (newCal && curCal) curCal.innerHTML = newCal.innerHTML;
                })
                .catch(function() {});
        }

        // Tick cada segundo — ciclo pizarrón/calendario + contador de inactividad
        setInterval(function() {
            // Contador de inactividad siempre avanza
            idleSecs++;
            if (!enSalvapantallas && idleSecs >= IDLE_LIMITE) {
                mostrarSalvapantallas();
                return;
            }

            // Ciclo normal solo si no está el salvapantallas
            if (enSalvapantallas) return;

            if (!enCalendario) {
                tick++;
                if (tick >= 60) mostrarCalendario();
            } else {
                calTick++;
                if (calTick >= 60) mostrarPizarron();
            }
        }, 1000);

        // Detectar reportes nuevos o modificados cada 5 segundos
        var lastUpdated        = null;
        var lastEventosUpdated = null;

        setInterval(function() {
            fetch('/pizarron/count')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.count !== lastCount) {
                        lastCount = data.count;
                        registrarActividad();
                        if (!enSalvapantallas) mostrarPizarron();
                    }
                })
                .catch(function() {});

            fetch('/pizarron/updated')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (lastUpdated === null) {
                        lastUpdated = data.updated_at;
                    } else if (data.updated_at !== lastUpdated) {
                        lastUpdated = data.updated_at;
                        registrarActividad();
                        if (!enSalvapantallas) refreshPizarron();
                    }
                })
                .catch(function() {});

            fetch('/pizarron/eventos-updated')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (lastEventosUpdated === null) {
                        lastEventosUpdated = data.updated_at;
                    } else if (data.updated_at !== lastEventosUpdated) {
                        lastEventosUpdated = data.updated_at;
                        registrarActividad();
                        if (!enSalvapantallas) refreshCalendario();
                    }
                })
                .catch(function() {});
        }, 5000);
    </script>

</body>
</html>
