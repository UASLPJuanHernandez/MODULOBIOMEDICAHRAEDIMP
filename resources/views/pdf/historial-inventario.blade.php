<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial — {{ $equipo->numero_inventario }}</title>
<style>
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #111; margin: 28px 32px; }
    h1 { font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0 0 2px; }
    .sub { font-size: 8.5pt; color: #555; margin-bottom: 14px; }
    .header-bar { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
    .badge { display: inline-block; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: 3px; }
    .badge-ok  { background: #d1fae5; color: #065f46; }
    .badge-mal { background: #fee2e2; color: #991b1b; }
    .badge-par { background: #fef3c7; color: #92400e; }

    table.info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.info td { padding: 4px 8px; font-size: 8.5pt; border: 1px solid #ddd; }
    table.info td.lbl { background: #f3f4f6; font-weight: bold; width: 32%; color: #374151; }

    .section-title { font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: #374151; border-left: 3px solid #1d4ed8; padding-left: 7px; margin: 16px 0 8px; }

    table.hist { width: 100%; border-collapse: collapse; }
    table.hist th { background: #1d4ed8; color: white; font-size: 7.5pt; font-weight: bold; padding: 5px 7px; text-align: left; }
    table.hist td { font-size: 8pt; padding: 5px 7px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    table.hist tr:nth-child(even) td { background: #f9fafb; }

    .cambio-item { margin-bottom: 3px; }
    .cambio-campo { font-weight: bold; color: #374151; }
    .cambio-antes { color: #6b7280; text-decoration: line-through; }
    .cambio-despues { color: #059669; }

    .footer { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 7pt; color: #9ca3af; text-align: center; }
    .no-hist { text-align: center; color: #9ca3af; font-size: 8.5pt; padding: 20px; }
</style>
</head>
<body>

<div class="header-bar">
    <div>
        <h1>Historial de Cambios</h1>
        <div class="sub">
            {{ $equipo->equipo }} &nbsp;·&nbsp; No. Inv: <strong>{{ $equipo->numero_inventario }}</strong>
            &nbsp;·&nbsp; {{ $equipo->area }}
        </div>
    </div>
    <div style="text-align:right; font-size:8pt; color:#555;">
        Generado: {{ $fechaGeneracion }}<br>
        Ingeniería Biomédica — HRAEIMP
    </div>
</div>

{{-- Datos del equipo --}}
<div class="section-title">Datos del equipo</div>
<table class="info">
    <tr>
        <td class="lbl">No. Inventario</td>
        <td>{{ $equipo->numero_inventario ?: '—' }}</td>
        <td class="lbl">Marca / Modelo</td>
        <td>{{ $equipo->marca ?: '—' }} / {{ $equipo->modelo ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Área</td>
        <td>{{ $equipo->area ?: '—' }}</td>
        <td class="lbl">Ubicación específica</td>
        <td>{{ $equipo->ubicacion_especifica ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">No. Serie</td>
        <td>{{ $equipo->numero_serie ?: '—' }}</td>
        <td class="lbl">Estatus</td>
        <td>
            @php
                $est = strtolower($equipo->estatus ?? '');
                $cls = str_contains($est,'completo') ? 'badge-ok' : (str_contains($est,'parcial') ? 'badge-par' : 'badge-mal');
            @endphp
            <span class="badge {{ $cls }}">{{ $equipo->estatus ?: '—' }}</span>
        </td>
    </tr>
    <tr>
        <td class="lbl">Condiciones</td>
        <td>{{ $equipo->condiciones ?: '—' }}</td>
        <td class="lbl">Propiedad</td>
        <td>{{ $equipo->propiedad ?: '—' }}</td>
    </tr>
</table>

{{-- Historial --}}
<div class="section-title">Historial de eventos ({{ $historiales->count() }})</div>

@if($historiales->isEmpty())
    <div class="no-hist">Este equipo no tiene eventos registrados aún.</div>
@else
<table class="hist">
    <thead>
        <tr>
            <th style="width:13%">Fecha</th>
            <th style="width:14%">Evento</th>
            <th style="width:16%">Usuario</th>
            <th>Detalle / Cambios</th>
        </tr>
    </thead>
    <tbody>
    @foreach($historiales as $h)
        <tr>
            <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$h->tipo_evento ?? '—')) }}</td>
            <td>{{ $h->usuario_nombre ?: '—' }}</td>
            <td>
                @if($h->descripcion)
                    <div style="margin-bottom:4px;">{{ $h->descripcion }}</div>
                @endif
                @if(!empty($h->cambios))
                    @foreach($h->cambios as $campo => $vals)
                        <div class="cambio-item">
                            <span class="cambio-campo">{{ $campo }}:</span>
                            @if(is_array($vals))
                                <span class="cambio-antes">{{ $vals['antes'] ?? '' }}</span>
                                → <span class="cambio-despues">{{ $vals['despues'] ?? '' }}</span>
                            @else
                                {{ $vals }}
                            @endif
                        </div>
                    @endforeach
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    {{ $equipo->equipo }} · {{ $equipo->numero_inventario }} · Historial generado el {{ $fechaGeneracion }}
</div>

</body>
</html>
