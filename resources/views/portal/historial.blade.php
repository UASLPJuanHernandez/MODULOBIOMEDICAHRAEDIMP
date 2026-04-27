<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi historial — HRAE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; padding: 24px; }
        .container { max-width: 720px; margin: 0 auto; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .back-btn { background: white; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .back-btn:hover { background: #f9fafb; }
        h1 { font-size: 20px; font-weight: 700; color: #111; }
        .subtitle { font-size: 13px; color: #6b7280; margin-top: 2px; }
        .empty { text-align: center; padding: 60px 24px; background: white; border-radius: 16px; border: 2px dashed #e5e7eb; }
        .empty p { color: #9ca3af; font-size: 14px; margin-top: 12px; }
        .card { background: white; border-radius: 12px; border: 1.5px solid #e5e7eb; padding: 18px 20px; margin-bottom: 10px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .card-body { flex: 1; min-width: 0; }
        .equipo { font-size: 14px; font-weight: 700; color: #111; }
        .descripcion { font-size: 12px; color: #6b7280; margin-top: 3px; line-height: 1.5; }
        .meta { font-size: 11px; color: #9ca3af; margin-top: 5px; }
        .card-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
        .badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .badge-pendiente  { background: #fef3c7; color: #92400e; }
        .badge-en_curso   { background: #dbeafe; color: #1d4ed8; }
        .badge-completado { background: #ede9fe; color: #6d28d9; }
        .badge-concretado { background: #d1fae5; color: #065f46; }
        .btn-pdf { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 7px; text-decoration: none; border: 1.5px solid; }
        .btn-ver { color: #4f46e5; border-color: #c7d2fe; background: #eef2ff; }
        .btn-ver:hover { background: #e0e7ff; }
        .btn-dl { color: #374151; border-color: #d1d5db; background: white; }
        .btn-dl:hover { background: #f9fafb; }
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
                <h1>Mi historial</h1>
                <p class="subtitle">{{ $personal->nombre }} — {{ $personal->servicio }}</p>
            </div>
        </div>

        @if($reportes->isEmpty())
        <div class="empty">
            <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto;">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p>Aún no has enviado ningún reporte.</p>
        </div>
        @else
        @foreach($reportes as $rp)
        @php
            $estado = $rp->concretado ? 'concretado' : $rp->estado;
            $labels = ['pendiente'=>'Pendiente','en_curso'=>'En curso','completado'=>'En proceso','concretado'=>'Concluido'];
            $label  = $labels[$estado] ?? ucfirst($estado);
        @endphp
        <div class="card">
            <div class="card-body">
                <div class="equipo">{{ $rp->equipo ?: 'Equipo no especificado' }}</div>
                <div class="descripcion">{{ Str::limit($rp->descripcion, 120) }}</div>
                <div class="meta">
                    {{ $rp->created_at->format('d/m/Y H:i') }}
                    @if($rp->ubicacion) &nbsp;·&nbsp; {{ $rp->ubicacion }} @endif
                </div>
            </div>
            <div class="card-actions">
                <span class="badge badge-{{ $estado }}">{{ $label }}</span>
                @if($rp->concretado && $rp->bitacora)
                <a href="{{ route('portal.bitacora.pdf', $rp->bitacora) }}" target="_blank" class="btn-pdf btn-ver">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Ver PDF
                </a>
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>
</body>
</html>
