<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar reporte — HRAE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; }
        .top-bar { background: white; border-bottom: 1.5px solid #e5e7eb; padding: 12px 20px; display: flex; align-items: center; gap: 12px; }
        .back-btn { background: white; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .back-btn:hover { background: #f9fafb; }
        .top-title { font-size: 16px; font-weight: 700; color: #111; }
        .top-sub { font-size: 12px; color: #6b7280; margin-top: 1px; }
        .layout { display: flex; gap: 0; height: calc(100vh - 57px); }
        .pdf-col { flex: 1; overflow-y: auto; background: #374151; }
        .pdf-col iframe { width: 100%; height: 100%; border: none; display: block; }
        .sidebar { width: 320px; flex-shrink: 0; background: white; border-left: 1.5px solid #e5e7eb; display: flex; flex-direction: column; padding: 24px 20px; gap: 16px; overflow-y: auto; }
        .report-info { background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 14px; }
        .info-equipo { font-size: 14px; font-weight: 700; color: #111; }
        .info-desc { font-size: 12px; color: #6b7280; margin-top: 3px; line-height: 1.5; }
        .info-meta { font-size: 11px; color: #9ca3af; margin-top: 6px; }
        .firma-section label { font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 8px; }
        .firma-preview { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 10px; background: white; text-align: center; min-height: 70px; display: flex; align-items: center; justify-content: center; }
        .firma-preview img { max-height: 60px; width: auto; mix-blend-mode: multiply; }
        .no-firma { font-size: 12px; color: #9ca3af; }
        .btn-firmar { width: 100%; padding: 12px; background: #059669; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-firmar:hover { background: #047857; }
        .btn-firmar:disabled { background: #6ee7b7; cursor: not-allowed; }
        .badge-firmado { background: #d1fae5; color: #065f46; font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 8px; text-align: center; }
        .divider { border: none; border-top: 1.5px solid #f3f4f6; }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="top-bar">
    <a href="{{ route('portal.firmas') }}" class="back-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver
    </a>
    <div>
        <div class="top-title">Firmar reporte</div>
        <div class="top-sub">{{ $personal->nombre }} — {{ $personal->area_jefe_servicio }}</div>
    </div>
</div>

<div class="layout">
    {{-- PDF --}}
    <div class="pdf-col">
        @if($solicitud->reporte?->bitacora)
        <iframe src="{{ route('portal.bitacora.pdf', $solicitud->reporte->bitacora) }}"
                title="Bitácora de reporte"></iframe>
        @else
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#d1d5db;font-size:14px;">
            El PDF estará disponible en breve.
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="report-info">
            <div class="info-equipo">{{ $solicitud->reporte?->equipo ?: 'Equipo sin especificar' }}</div>
            <div class="info-desc">{{ Str::limit($solicitud->reporte?->descripcion, 120) }}</div>
            <div class="info-meta">
                Área: {{ $solicitud->reporte?->ubicacion ?: '—' }} &nbsp;·&nbsp;
                Recibido: {{ $solicitud->created_at->format('d/m/Y') }}
            </div>
        </div>

        <hr class="divider">

        @if($solicitud->estado === 'firmado')
        <div class="badge-firmado">
            ✓ Firmado el {{ $solicitud->firmado_at->format('d/m/Y H:i') }}
        </div>
        @if($solicitud->firma_data)
        <div class="firma-preview">
            <img src="{{ $solicitud->firma_data }}" alt="Tu firma">
        </div>
        @endif
        @else
        <div class="firma-section">
            <label>Tu firma registrada</label>
            <div class="firma-preview">
                @if($personal->firma)
                <img src="{{ $personal->firma }}" alt="Firma de {{ $personal->nombre }}" id="firma-img">
                @else
                <span class="no-firma">Sin firma registrada</span>
                @endif
            </div>
        </div>

        @if($personal->firma)
        <form action="{{ route('portal.firmar', $solicitud) }}" method="POST" id="form-firma">
            @csrf
            <input type="hidden" name="firma_data" value="{{ $personal->firma }}">
            <button type="submit" class="btn-firmar" id="btn-firmar">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                </svg>
                Confirmar y firmar
            </button>
        </form>
        <p style="font-size:11px;color:#9ca3af;margin-top:8px;text-align:center;">
            Se usará tu firma registrada para firmar este reporte.
        </p>
        @else
        <p style="font-size:13px;color:#ef4444;font-weight:600;">
            Necesitas registrar tu firma para poder firmar reportes. Edita tu perfil en el portal.
        </p>
        @endif
        @endif
    </div>
</div>

<script>
    var form = document.getElementById('form-firma');
    var btn  = document.getElementById('btn-firmar');
    if (form) {
        form.addEventListener('submit', function() {
            if (btn) { btn.disabled = true; btn.textContent = 'Firmando…'; }
        });
    }
</script>
</body>
</html>
