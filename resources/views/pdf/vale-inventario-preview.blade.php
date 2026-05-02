<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $vale->tipo_label }} — {{ $vale->numero_inventario }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1a1a1a;
            padding: 28px 32px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header img { max-width: 100%; height: auto; display: block; margin: 0 auto 8px; }
        .title {
            font-size: 15px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }
        .badge-entrega { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-retiro  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #374151;
            background: #f3f4f6;
            border-left: 3px solid #1e40af;
            padding: 5px 8px;
            margin-bottom: 10px;
        }
        .row {
            display: flex;
            border-bottom: 1px dotted #d1d5db;
            padding: 5px 0;
        }
        .row:last-child { border-bottom: none; }
        .label {
            font-weight: bold;
            width: 180px;
            color: #374151;
            flex-shrink: 0;
        }
        .value { flex: 1; color: #111827; }

        .signatures {
            margin-top: 48px;
            display: flex;
            justify-content: space-around;
        }
        .sig-box {
            width: 42%;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #374151;
            padding-top: 6px;
            margin-top: 42px;
            font-size: 11px;
            color: #374151;
        }
        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .footer img { max-width: 100%; height: auto; margin-top: 8px; }
    </style>
</head>
<body>

<div class="header">
    @php
        $headerPath = public_path('images/vales/encabezado vale.jpg');
        $headerB64  = file_exists($headerPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerPath))
            : null;
    @endphp
    @if($headerB64)
        <img src="{{ $headerB64 }}" alt="Encabezado">
    @endif
    <div class="title">{{ $vale->tipo_label }}</div>
    <span class="badge badge-{{ $vale->tipo }}">
        {{ strtoupper($vale->tipo_label) }}
    </span>
</div>

<div class="section">
    <div class="section-title">Información general</div>
    <div class="row">
        <span class="label">Fecha:</span>
        <span class="value">{{ $vale->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="row">
        <span class="label">Generado por:</span>
        <span class="value">{{ $vale->usuario_nombre ?: '—' }}</span>
    </div>
    <div class="row">
        <span class="label">Área / Departamento:</span>
        <span class="value">{{ $vale->area ?: '—' }}</span>
    </div>
    @if($vale->unidad_medica)
    <div class="row">
        <span class="label">Unidad médica:</span>
        <span class="value">{{ $vale->unidad_medica }}</span>
    </div>
    @endif
</div>

<div class="section">
    <div class="section-title">Datos del equipo</div>
    <div class="row">
        <span class="label">Equipo / Descripción:</span>
        <span class="value">{{ $vale->equipo_nombre ?: '—' }}</span>
    </div>
    <div class="row">
        <span class="label">No. Inventario:</span>
        <span class="value">{{ $vale->numero_inventario ?: '—' }}</span>
    </div>
    @if($vale->marca)
    <div class="row">
        <span class="label">Marca:</span>
        <span class="value">{{ $vale->marca }}</span>
    </div>
    @endif
    @if($vale->modelo)
    <div class="row">
        <span class="label">Modelo:</span>
        <span class="value">{{ $vale->modelo }}</span>
    </div>
    @endif
    @if($vale->numero_serie)
    <div class="row">
        <span class="label">No. Serie:</span>
        <span class="value">{{ $vale->numero_serie }}</span>
    </div>
    @endif
</div>

<div class="signatures">
    <div class="sig-box">
        <div class="sig-line">
            <strong>ENTREGA</strong><br>
            Ingeniería Biomédica
        </div>
    </div>
    <div class="sig-box">
        <div class="sig-line">
            <strong>RECIBE</strong><br>
            {{ $vale->area ?: 'Área correspondiente' }}
        </div>
    </div>
</div>

<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }} —
    Área de Ingeniería Biomédica, HRAEIMP
    @php
        $footerPath = public_path('images/vales/pie de pagina vale.jpg');
        $footerB64  = file_exists($footerPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($footerPath))
            : null;
    @endphp
    @if($footerB64)
        <br><img src="{{ $footerB64 }}" alt="Pie de página">
    @endif
</div>

</body>
</html>
