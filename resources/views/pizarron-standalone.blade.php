<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Pizarrón — Depto. Ingeniería Biomédica</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 24px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            letter-spacing: 0.3px;
        }

        .header p {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .badge-count {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
        }

        .pizarron {
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            background: white;
            min-height: 80vh;
            padding: 28px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            align-items: flex-start;
        }

        .postit {
            border-radius: 3px 10px 10px 3px;
            padding: 16px;
            box-shadow: 2px 4px 12px rgba(0,0,0,0.1);
        }

        .postit-baja     { background: #fefce8; border-left: 5px solid #eab308; width: 270px; }
        .postit-media    { background: #fef9c3; border-left: 5px solid #f59e0b; width: 270px; }
        .postit-moderada { background: #ffedd5; border-left: 5px solid #f97316; width: 270px; }
        .postit-urgencia { background: #fee2e2; border-left: 5px solid #ef4444; width: 270px; }

        .postit-title {
            font-size: 13px;
            font-weight: 700;
            color: #111;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .postit-label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .postit-value {
            font-size: 12px;
            color: #222;
            margin: 2px 0 8px 0;
        }

        .postit-desc {
            font-size: 11px;
            color: #444;
            line-height: 1.5;
            margin: 2px 0 8px 0;
        }

        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 999px;
        }

        .badge-pendiente  { background: #111827; color: white; }
        .badge-en_curso   { background: #16a34a; color: white; }
        .badge-completado { background: #6b7280; color: white; }

        .badge-baja     { background: #eab308; color: #1a1a1a; }
        .badge-media    { background: #f59e0b; color: white; }
        .badge-moderada { background: #f97316; color: white; }
        .badge-urgencia { background: #ef4444; color: white; }

        .responsable {
            margin-top: 8px;
            font-size: 11px;
            color: #555;
            border-top: 1px solid rgba(0,0,0,0.08);
            padding-top: 8px;
        }

        .empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60vh;
            color: #d1d5db;
        }

        .empty svg { width: 56px; height: 56px; margin-bottom: 12px; }
        .empty p   { font-size: 16px; }

        .refresh-note {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>Pizarrón de Reportes</h1>
            <p>Departamento de Ingeniería Biomédica — HRAE</p>
        </div>
        <span class="badge-count">{{ $reportes->count() }} reporte(s) activo(s)</span>
    </div>

    <div class="pizarron">

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

                        <p class="postit-label">Descripción</p>
                        <p class="postit-desc">{{ $reporte->descripcion }}</p>

                        @if($reporte->responsable)
                            <div class="responsable">
                                Responsable: <strong>{{ $reporte->responsable }}</strong>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <p class="refresh-note">Esta vista se actualiza automaticamente cada 30 segundos</p>

</body>
</html>
