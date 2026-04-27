<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes para firma — HRAE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; padding: 24px; }
        .container { max-width: 680px; margin: 0 auto; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .back-btn { background: white; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .back-btn:hover { background: #f9fafb; }
        h1 { font-size: 20px; font-weight: 700; color: #111; }
        .subtitle { font-size: 13px; color: #6b7280; margin-top: 2px; }
        /* Tabs */
        .tabs { display: flex; gap: 4px; margin-bottom: 20px; background: white; border: 1.5px solid #e5e7eb; border-radius: 12px; padding: 4px; }
        .tab-btn { flex: 1; padding: 9px 16px; border: none; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; background: transparent; color: #6b7280; display: flex; align-items: center; justify-content: center; gap: 7px; transition: background .15s, color .15s; }
        .tab-btn.active { background: #1d4ed8; color: white; }
        .tab-btn:not(.active):hover { background: #f3f4f6; color: #111; }
        .badge-count { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .tab-btn.active .badge-count { background: rgba(255,255,255,.25); color: white; }
        .tab-btn:not(.active) .badge-count { background: #e5e7eb; color: #374151; }
        /* Cards */
        .empty { text-align: center; padding: 60px 24px; background: white; border-radius: 16px; border: 2px dashed #e5e7eb; }
        .empty p { color: #9ca3af; font-size: 14px; margin-top: 12px; }
        .card { background: white; border-radius: 12px; border: 1.5px solid #e5e7eb; padding: 20px; margin-bottom: 12px; }
        .card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .equipo { font-size: 15px; font-weight: 700; color: #111; }
        .descripcion { font-size: 13px; color: #6b7280; margin-top: 4px; line-height: 1.5; }
        .meta { font-size: 12px; color: #9ca3af; margin-top: 6px; }
        .badge-pendiente { background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .badge-firmado { background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding-top: 12px; border-top: 1.5px solid #f3f4f6; }
        .btn-ver { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; font-size: 13px; font-weight: 700; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        .btn-ver:hover { background: #dbeafe; }
        .firmado-info { font-size: 12px; color: #059669; font-weight: 600; }
        .alert-ok { background: #d1fae5; color: #065f46; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ route('portal.reportes.form') }}" class="back-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver
            </a>
            <div>
                <h1>Reportes para firma</h1>
                <p class="subtitle">{{ $personal->nombre }} — Jefe/a de Servicio: {{ $personal->area_jefe_servicio }}</p>
            </div>
        </div>

        @if(session('firmado_id'))
        <div class="alert-ok">✓ Reporte firmado correctamente.</div>
        @endif

        {{-- Tabs --}}
        <div class="tabs">
            <button class="tab-btn active" onclick="cambiarTab('pendientes', this)">
                Por firmar
                <span class="badge-count">{{ $pendientes->count() }}</span>
            </button>
            <button class="tab-btn" onclick="cambiarTab('historial', this)">
                Historial
                <span class="badge-count">{{ $firmadas->count() }}</span>
            </button>
        </div>

        {{-- Panel: Por firmar --}}
        <div id="tab-pendientes" class="tab-panel active">
            @if($pendientes->isEmpty())
            <div class="empty">
                <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 0l.172.172a2 2 0 010 2.828L12 16H9v-3z"/>
                </svg>
                <p>No tienes reportes pendientes de firma.</p>
            </div>
            @else
            @foreach($pendientes as $sol)
            <div class="card">
                <div class="card-header">
                    <div style="min-width:0;flex:1;">
                        <div class="equipo">{{ $sol->reporte?->equipo ?: 'Equipo sin especificar' }}</div>
                        <div class="descripcion">{{ Str::limit($sol->reporte?->descripcion, 140) }}</div>
                        <div class="meta">
                            Área: {{ $sol->reporte?->ubicacion ?: '—' }}
                            &nbsp;·&nbsp; Recibido: {{ $sol->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <span class="badge-pendiente">Pendiente</span>
                </div>
                <div class="card-footer">
                    <a href="{{ route('portal.firmas.ver', $sol) }}" class="btn-ver">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                        </svg>
                        Ver PDF y firmar
                    </a>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Panel: Historial --}}
        <div id="tab-historial" class="tab-panel">
            @if($firmadas->isEmpty())
            <div class="empty">
                <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <p>Aún no has firmado ningún reporte.</p>
            </div>
            @else
            @foreach($firmadas as $sol)
            <div class="card">
                <div class="card-header">
                    <div style="min-width:0;flex:1;">
                        <div class="equipo">{{ $sol->reporte?->equipo ?: 'Equipo sin especificar' }}</div>
                        <div class="descripcion">{{ Str::limit($sol->reporte?->descripcion, 140) }}</div>
                        <div class="meta">
                            Área: {{ $sol->reporte?->ubicacion ?: '—' }}
                            &nbsp;·&nbsp; Firmado: {{ $sol->firmado_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <span class="badge-firmado">Firmado</span>
                </div>
                <div class="card-footer">
                    <span class="firmado-info">✓ Firmado</span>
                    @if($sol->reporte?->bitacora)
                    <a href="{{ route('portal.bitacora.pdf', $sol->reporte->bitacora) }}" target="_blank"
                       style="font-size:12px;color:#6b7280;text-decoration:underline;">Ver PDF</a>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    <script>
    function cambiarTab(tab, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        btn.classList.add('active');
    }

    // Si acaba de firmar, abrir automáticamente el historial
    @if(session('firmado_id'))
    document.addEventListener('DOMContentLoaded', function () {
        cambiarTab('historial', document.querySelectorAll('.tab-btn')[1]);
    });
    @endif
    </script>
</body>
</html>
