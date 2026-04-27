<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Reporte</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            padding: 15px 18px 140px;
            color: #111;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header img { max-width: 100%; height: auto; display: block; margin: 0 auto 4px; }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section { margin-bottom: 10px; }
        .section-title {
            font-weight: bold;
            font-size: 10px;
            background: #1e3a5f;
            color: #fff;
            padding: 4px 7px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .two-col { display: table; width: 100%; }
        .col      { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .col:last-child { padding-right: 0; }
        .field { margin-bottom: 4px; }
        .label { font-weight: bold; }
        .narrativa {
            font-style: italic;
            font-size: 10px;
            line-height: 1.5;
            margin-bottom: 4px;
        }
        .texto-bloque {
            border: 1px solid #ccc;
            padding: 5px 7px;
            font-size: 10px;
            line-height: 1.5;
            min-height: 30px;
            background: #fafafa;
        }
        .accion-num {
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 2px;
        }
        .resultado-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .res-satisfactoria    { background: #d4edda; color: #155724; }
        .res-parcial          { background: #fff3cd; color: #856404; }
        .res-no_satisfactoria { background: #f8d7da; color: #721c24; }
        .signatures { display: table; width: 100%; margin-top: 30px; }
        .signature  { display: table-cell; text-align: center; width: 50%; padding: 0 12px; }
        .sig-line   { border-top: 1px solid #333; margin-top: 36px; margin-bottom: 4px; }
        .sig-name   { font-size: 9px; font-weight: bold; }
        .sig-label  { font-size: 8px; color: #555; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 18px;
            text-align: center;
            font-size: 8px;
            color: #555;
            border-top: 1px solid #ddd;
            background: #fff;
        }
        .footer img { max-width: 100%; height: auto; display: block; margin: 4px auto 0; }
    </style>
</head>
<body>

{{-- Encabezado --}}
<div class="header">
    @php
        $headerPath = public_path('images/vales/encabezado vale.jpg');
        $headerB64  = file_exists($headerPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerPath)) : null;
    @endphp
    @if($headerB64)
        <img src="{{ $headerB64 }}" alt="Encabezado">
    @endif
    <div class="doc-title">Bitácora de Reporte de Ingeniería Biomédica</div>
</div>

{{-- Datos del solicitante --}}
<div class="section">
    <div class="section-title">Datos del solicitante</div>
    <div class="two-col">
        <div class="col">
            <div class="field"><span class="label">Personal: </span>{{ $bitacora->nombre_personal }}</div>
            <div class="field"><span class="label">No. identificación: </span>{{ $bitacora->numero_identificacion ?: '—' }}</div>
        </div>
        <div class="col">
            <div class="field"><span class="label">Área / Departamento: </span>{{ $bitacora->area_departamento }}</div>
            <div class="field">
                <span class="label">Fecha: </span>{{ \Carbon\Carbon::parse($bitacora->fecha_reporte)->format('d/m/Y') }}
                &nbsp;&nbsp;<span class="label">Hora: </span>{{ substr($bitacora->hora_reporte ?? '', 0, 5) ?: '—' }}
            </div>
        </div>
    </div>
</div>

{{-- Narrativa --}}
<div class="section">
    <div class="section-title">Descripción</div>
    <p class="narrativa">
        El día {{ \Carbon\Carbon::parse($bitacora->fecha_reporte)->day }}
        de {{ $mesEspanol }}
        de {{ \Carbon\Carbon::parse($bitacora->fecha_reporte)->year }},
        a las {{ substr($bitacora->hora_reporte ?? '', 0, 5) ?: '——:——' }} horas,
        se recibió la siguiente solicitud:
    </p>
    <div class="texto-bloque">{{ $bitacora->mensaje_original }}</div>
</div>

{{-- Acciones --}}
@php $acciones = $bitacora->acciones ?? []; @endphp
@if(count($acciones))
<div class="section">
    <div class="section-title">Acciones realizadas</div>
    @foreach($acciones as $i => $accion)
        @if(!empty($accion['texto']))
        <div style="margin-bottom:6px">
            <div class="accion-num">{{ $i + 1 }}.</div>
            <div>{{ $accion['texto'] }}</div>
        </div>
        @endif
    @endforeach
</div>
@endif

{{-- Resultado --}}
<div class="section">
    <div class="section-title">Resultado</div>
    <div style="padding:4px 0">
        La solicitud fue resuelta de forma
        <strong>{{ $textoResultado }}</strong>.
        &nbsp;&nbsp;
        <span class="resultado-badge res-{{ $bitacora->resultado }}">{{ $labelResultado }}</span>
    </div>
</div>

{{-- Equipo --}}
@if($bitacora->nombre_dispositivo || $bitacora->numero_serie)
<div class="section">
    <div class="section-title">Datos del equipo</div>
    <div class="two-col">
        <div class="col">
            <div class="field"><span class="label">Dispositivo: </span>{{ $bitacora->nombre_dispositivo ?: '—' }}</div>
        </div>
        <div class="col">
            <div class="field"><span class="label">No. de serie: </span>{{ $bitacora->numero_serie ?: '—' }}</div>
        </div>
    </div>
</div>
@endif

{{-- Firmas --}}
@php
    $firmaSolicitud = isset($firmaSolicitud)
        ? $firmaSolicitud
        : \App\Models\FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('estado', 'firmado')->latest()->first();
@endphp
<div class="signatures">
    <div class="signature">
        @if($bitacora->firma_ingeniero)
        <img src="{{ $bitacora->firma_ingeniero }}" alt="Firma ingeniero"
             style="height:50px;width:auto;display:block;margin:0 auto 4px;mix-blend-mode:multiply;">
        @endif
        <div class="sig-line"></div>
        <div class="sig-name">{{ $bitacora->atiende_nombre ?: '____________________________' }}</div>
        <div class="sig-label">Atiende — Ingeniero Biomédico</div>
    </div>
    <div class="signature">
        @if($firmaSolicitud?->firma_data)
        <img src="{{ $firmaSolicitud->firma_data }}" alt="Firma jefe"
             style="height:50px;width:auto;display:block;margin:0 auto 4px;mix-blend-mode:multiply;">
        @endif
        <div class="sig-line"></div>
        <div class="sig-name">{{ $bitacora->recibe_nombre ?: '____________________________' }}</div>
        <div class="sig-label">Recibe — Jefe de Servicio</div>
    </div>
</div>

{{-- Footer fijo --}}
<div class="footer">
    <span>Generado el {{ now()->format('d/m/Y H:i') }} — Área de Ingeniería Biomédica, HRAEDIMP</span>
    @php
        $footerPath = public_path('images/vales/pie de pagina vale.jpg');
        $footerB64  = file_exists($footerPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($footerPath)) : null;
    @endphp
    @if($footerB64)
        <img src="{{ $footerB64 }}" alt="Pie de página">
    @endif
</div>

</body>
</html>
