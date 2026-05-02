<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos — HRAE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; padding: 24px; }
        .container { max-width: 720px; margin: 0 auto; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .back-btn { background: white; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .back-btn:hover { background: #f9fafb; }
        h1 { font-size: 20px; font-weight: 700; color: #111; }
        .subtitle { font-size: 13px; color: #6b7280; margin-top: 2px; }
        /* Tabs principales */
        .tabs { display: flex; gap: 4px; margin-bottom: 20px; background: white; border: 1.5px solid #e5e7eb; border-radius: 12px; padding: 4px; }
        .tab-btn { flex: 1; padding: 9px 10px; border: none; border-radius: 9px; font-size: 12px; font-weight: 600; cursor: pointer; background: transparent; color: #6b7280; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background .15s, color .15s; }
        .tab-btn.active { background: #1d4ed8; color: white; }
        .tab-btn:not(.active):hover { background: #f3f4f6; color: #111; }
        .badge-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .tab-btn.active .badge-count { background: rgba(255,255,255,.25); color: white; }
        .tab-btn:not(.active) .badge-count { background: #e5e7eb; color: #374151; }
        /* Paneles */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        /* Cards */
        .empty { text-align: center; padding: 48px 24px; background: white; border-radius: 16px; border: 2px dashed #e5e7eb; }
        .empty p { color: #9ca3af; font-size: 14px; margin-top: 12px; }
        .card { background: white; border-radius: 12px; border: 1.5px solid #e5e7eb; padding: 18px; margin-bottom: 10px; }
        .card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .reg-id { font-size: 15px; font-weight: 700; color: #111; }
        .reg-sub { font-size: 13px; color: #6b7280; margin-top: 3px; }
        .meta { font-size: 12px; color: #9ca3af; margin-top: 5px; }
        .badge-pendiente { background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .badge-firmado { background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1.5px solid #f3f4f6; }
        .btn-firmar { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; font-size: 13px; font-weight: 700; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        .btn-firmar:hover { background: #dbeafe; }
        .firmado-info { font-size: 12px; color: #059669; font-weight: 600; }
        .alert-ok { background: #d1fae5; color: #065f46; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
        .tipo-tag { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 2px 8px; border-radius: 999px; background: #f3f4f6; color: #6b7280; margin-right: 6px; }
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
                <h1>Documentos</h1>
                <p class="subtitle">{{ $personal->nombre }} — Jefe/a de Servicio: {{ $personal->area_jefe_servicio }}</p>
            </div>
        </div>

        @if(session('firmado_id'))
        <div class="alert-ok">✓ Documento firmado correctamente.</div>
        @endif

        @php
            $tipos = [
                'mantenimiento' => 'Mantenimientos',
                'reporte'       => 'Reportes',
                'vale'          => 'Vales',
                'documento'     => 'Varios',
            ];
        @endphp

        {{-- Tabs --}}
        <div class="tabs">
            @foreach($tipos as $key => $label)
            @php
                $cnt = $porTipo[$key]->where('estado','en_firma')->count();
                if ($key === 'reporte') {
                    $cnt += $solicitudesFirma->where('estado','pendiente')->count();
                }
            @endphp
            <button class="tab-btn {{ $loop->first ? 'active' : '' }}" onclick="cambiarTab('{{ $key }}', this)">
                {{ $label }}
                @if($cnt > 0)
                <span class="badge-count">{{ $cnt }}</span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- Paneles por tipo --}}
        @foreach($tipos as $key => $label)
        <div id="tab-{{ $key }}" class="tab-panel {{ $loop->first ? 'active' : '' }}">
            @php
                $items = $porTipo[$key]->sortByDesc('created_at');
                $pendientesTab = $items->where('estado', 'en_firma');
                $firmadosTab   = $items->where('estado', 'culminado');
                $solPendientes = $key === 'reporte' ? $solicitudesFirma->where('estado', 'pendiente') : collect();
                $solFirmadas   = $key === 'reporte' ? $solicitudesFirma->where('estado', 'firmado')   : collect();
                $hayContenido  = $items->isNotEmpty() || ($key === 'reporte' && $solicitudesFirma->isNotEmpty());
            @endphp

            @if(! $hayContenido)
            <div class="empty">
                <svg width="44" height="44" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <p>No tienes {{ strtolower($label) }} asignados.</p>
            </div>
            @else

                {{-- Por firmar: registros de formato --}}
                @if($pendientesTab->isNotEmpty() || $solPendientes->isNotEmpty())
                @php $totalPendientes = $pendientesTab->count() + $solPendientes->count(); @endphp
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;margin-bottom:8px;">
                    Por firmar ({{ $totalPendientes }})
                </p>

                @foreach($pendientesTab as $reg)
                <div class="card">
                    <div class="card-header">
                        <div style="min-width:0;flex:1;">
                            <div class="reg-id">{{ $reg->identificador ?: 'Registro #' . $reg->id }}</div>
                            <div class="reg-sub">{{ $reg->formato?->nombre ?? '—' }}</div>
                            <div class="meta">
                                Generado por: {{ $reg->usuario?->name ?? '—' }}
                                &nbsp;·&nbsp; {{ $reg->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <span class="badge-pendiente">Por firmar</span>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('portal.documentos.ver', $reg) }}" class="btn-firmar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                            </svg>
                            Ver y firmar
                        </a>
                    </div>
                </div>
                @endforeach

                @foreach($solPendientes as $sol)
                <div class="card">
                    <div class="card-header">
                        <div style="min-width:0;flex:1;">
                            <div class="reg-id">{{ $sol->reporte?->equipo ?: 'Reporte #' . $sol->reporte_pizarron_id }}</div>
                            <div class="reg-sub">{{ $sol->reporte?->ubicacion ?? '—' }}</div>
                            <div class="meta">{{ Str::limit($sol->reporte?->descripcion, 100) }}</div>
                            <div class="meta">{{ $sol->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="badge-pendiente">Por firmar</span>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('portal.firmas.ver', $sol) }}" class="btn-firmar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                            </svg>
                            Ver y firmar
                        </a>
                    </div>
                </div>
                @endforeach
                @endif

                {{-- Firmados: registros de formato --}}
                @if($firmadosTab->isNotEmpty() || $solFirmadas->isNotEmpty())
                @php $totalFirmados = $firmadosTab->count() + $solFirmadas->count(); @endphp
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;margin-top:18px;margin-bottom:8px;">
                    Firmados ({{ $totalFirmados }})
                </p>

                @foreach($firmadosTab as $reg)
                <div class="card">
                    <div class="card-header">
                        <div style="min-width:0;flex:1;">
                            <div class="reg-id">{{ $reg->identificador ?: 'Registro #' . $reg->id }}</div>
                            <div class="reg-sub">{{ $reg->formato?->nombre ?? '—' }}</div>
                            <div class="meta">
                                Generado por: {{ $reg->usuario?->name ?? '—' }}
                                &nbsp;·&nbsp; Firmado: {{ $reg->firmado_at?->format('d/m/Y H:i') ?? '—' }}
                            </div>
                        </div>
                        <span class="badge-firmado">Firmado ✓</span>
                    </div>
                    <div class="card-footer">
                        <span class="firmado-info">✓ Firmado</span>
                        <a href="{{ route('portal.documentos.pdf', $reg) }}" target="_blank"
                           style="font-size:12px;color:#6b7280;text-decoration:underline;">Ver PDF</a>
                    </div>
                </div>
                @endforeach

                @foreach($solFirmadas as $sol)
                <div class="card">
                    <div class="card-header">
                        <div style="min-width:0;flex:1;">
                            <div class="reg-id">{{ $sol->reporte?->equipo ?: 'Reporte #' . $sol->reporte_pizarron_id }}</div>
                            <div class="reg-sub">{{ $sol->reporte?->ubicacion ?? '—' }}</div>
                            <div class="meta">Firmado: {{ $sol->firmado_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="badge-firmado">Firmado ✓</span>
                    </div>
                    <div class="card-footer">
                        <span class="firmado-info">✓ Firmado</span>
                    </div>
                </div>
                @endforeach
                @endif

            @endif
        </div>
        @endforeach

    </div>

    <script>
    function cambiarTab(key, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + key).classList.add('active');
        btn.classList.add('active');
    }
    @if(session('firmado_id'))
    document.addEventListener('DOMContentLoaded', function () {
        // No auto-switch needed; user sees the success banner
    });
    @endif
    </script>
</body>
</html>
