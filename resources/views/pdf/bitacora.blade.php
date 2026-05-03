<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Servicio Ingeniería Biomédica</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5px;
            color: #000;
            padding: 10px 18px 55px;
        }

        /* Header */
        .hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .hdr-table td { border: none; padding: 0; vertical-align: middle; }

        /* Título */
        .doc-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }

        /* Tablas del formulario */
        .ft { border-collapse: collapse; width: 100%; margin-bottom: 4px; }
        .ft td, .ft th { border: 1px solid #000; padding: 2px 5px; vertical-align: top; }

        .lbl {
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            display: block;
            margin-bottom: 2px;
        }
        .val { font-size: 9px; min-height: 14px; }

        /* SE SOLICITA */
        .th-section {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .th-sub {
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            background: #f0f0f0;
        }
        .th-col {
            text-align: center;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            vertical-align: middle;
            line-height: 1.3;
        }
        .check-td {
            text-align: center;
            font-size: 12px;
            height: 18px;
            vertical-align: middle;
        }

        /* Observaciones */
        .obs-td {
            min-height: 65px;
            height: 65px;
            font-size: 9px;
            line-height: 1.6;
            vertical-align: top;
            padding: 4px 5px;
        }

        /* Finalizado */
        .fin-wrap { overflow: hidden; margin: 4px 0 8px; }
        .fin-table { border-collapse: collapse; float: right; width: 210px; }
        .fin-table td { border: 1px solid #000; padding: 4px 7px; }

        /* Firmas */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .sig-table td { width: 50%; text-align: center; padding: 0 20px; border: none; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #000; margin-top: 48px; padding-top: 3px; font-size: 8.5px; font-weight: bold; }
        .sig-label { font-size: 8px; color: #333; margin-top: 2px; }

        /* Footer fijo */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            font-size: 7px;
            text-align: center;
            padding: 3px 18px;
            color: #444;
            border-top: 1px solid #ccc;
            background: #fff;
        }
    </style>
</head>
<body>

@php
    $headerPath = public_path('images/vales/encabezado vale.jpg');
    $headerB64  = file_exists($headerPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerPath))
        : null;

    $footerPath = public_path('images/vales/pie de pagina vale.jpg');
    $footerB64  = file_exists($footerPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($footerPath))
        : null;

    $firmaSolicitud = isset($firmaSolicitud)
        ? $firmaSolicitud
        : \App\Models\FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('estado', 'firmado')->latest()->first();

    $fechaFmt = \Carbon\Carbon::parse($bitacora->fecha_reporte)->format('d/m/Y');
    $horaFmt  = substr($bitacora->hora_reporte ?? '', 0, 5) ?: '—';

    $finalizado = in_array($bitacora->resultado, ['satisfactoria', 'parcial']);
@endphp

{{-- Encabezado --}}
@if($headerB64)
<table class="hdr-table">
    <tr>
        <td style="width:155px;">
            <img src="{{ $headerB64 }}" alt="Encabezado" style="max-height:58px;max-width:150px;">
        </td>
        <td style="text-align:right;">
            <div style="font-weight:bold;font-size:10px;">HOSPITAL REGIONAL DE ALTA ESPECIALIDAD</div>
            <div style="font-weight:bold;font-size:10px;">DR. IGNACIO MORONES PRIETO</div>
            <div style="font-size:8px;color:#333;">HRAE-DG-DA-SG-IB-SSIB-144</div>
        </td>
    </tr>
</table>
@else
<table class="hdr-table">
    <tr>
        <td>
            <div style="font-weight:bold;font-size:12px;">IMSS BIENESTAR</div>
            <div style="font-size:8px;">Instituto Mexicano del Seguro Social</div>
        </td>
        <td style="text-align:right;">
            <div style="font-weight:bold;font-size:10px;">HOSPITAL REGIONAL DE ALTA ESPECIALIDAD</div>
            <div style="font-weight:bold;font-size:10px;">DR. IGNACIO MORONES PRIETO</div>
            <div style="font-size:8px;color:#333;">HRAE-DG-DA-SG-IB-SSIB-144</div>
        </td>
    </tr>
</table>
@endif

<div class="doc-title">Solicitud de Servicio Ingeniería Biomédica</div>

{{-- Unidad / Descripción / Fecha --}}
<table class="ft">
    <tr>
        <td style="width:28%;"><span class="lbl">Unidad / O Departamento</span></td>
        <td style="width:50%;"><span class="lbl">Descripción del Bien / Equipo</span></td>
        <td style="width:22%;"><span class="lbl">Fecha y Hora:</span></td>
    </tr>
    <tr>
        <td><div class="val">{{ $bitacora->area_departamento }}</div></td>
        <td><div class="val">{{ $bitacora->nombre_dispositivo ?: '—' }}</div></td>
        <td><div class="val">{{ $fechaFmt }} {{ $horaFmt }}</div></td>
    </tr>
</table>

{{-- Marca / Modelo / N° Serie / N° Control --}}
<table class="ft">
    <tr>
        <td style="width:25%;"><span class="lbl">Marca</span></td>
        <td style="width:25%;"><span class="lbl">Modelo</span></td>
        <td style="width:25%;"><span class="lbl">N° De Serie</span></td>
        <td style="width:25%;"><span class="lbl">N° De Control</span></td>
    </tr>
    <tr>
        <td><div class="val">{{ $bitacora->marca ?: '&nbsp;' }}</div></td>
        <td><div class="val">{{ $bitacora->modelo ?: '&nbsp;' }}</div></td>
        <td><div class="val">{{ $bitacora->numero_serie ?: '—' }}</div></td>
        <td><div class="val">&nbsp;</div></td>
    </tr>
</table>

{{-- SE SOLICITA --}}
<table class="ft">
    <tr>
        <td colspan="8" class="th-section">SE SOLICITA</td>
    </tr>
    <tr>
        <td colspan="3" class="th-sub">SERVICIO</td>
        <td colspan="5" class="th-sub">BAJA POR</td>
    </tr>
    <tr>
        <td class="th-col" style="width:12.5%;">PREVENTIVO</td>
        <td class="th-col" style="width:12.5%;">CORRECTIVO</td>
        <td class="th-col" style="width:12.5%;">POR NO SER FUNCIONAL PARA EL ÁREA</td>
        <td class="th-col" style="width:12.5%;">INSERVIBLE</td>
        <td class="th-col" style="width:12.5%;">OBSOLETO</td>
        <td class="th-col" style="width:12.5%;">A DISPOSICIÓN</td>
        <td class="th-col" style="width:12.5%;">TRASPASO</td>
        <td class="th-col" style="width:12.5%;">OTRO</td>
    </tr>
    <tr>
        <td class="check-td">☐</td>
        <td class="check-td">☑</td>
        <td class="check-td">☐</td>
        <td class="check-td">☐</td>
        <td class="check-td">☐</td>
        <td class="check-td">☐</td>
        <td class="check-td">☐</td>
        <td class="check-td">☐</td>
    </tr>
</table>

{{-- Justificación / Observaciones --}}
<table class="ft">
    <tr>
        <td>
            <span class="lbl">Justificación / Observaciones</span>
            <div class="obs-td">{{ $bitacora->mensaje_original }}</div>
        </td>
    </tr>
</table>

{{-- Finalizado --}}
<div class="fin-wrap">
    <table class="fin-table">
        <tr>
            <td>
                <span style="font-weight:bold;font-size:9px;">FINALIZADO</span>
                &nbsp;&nbsp;
                {{ $finalizado ? '☑' : '☐' }}&nbsp;SI
                &nbsp;&nbsp;
                {{ !$finalizado ? '☑' : '☐' }}&nbsp;NO
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Fecha y Hora:</span>
                <div class="val" style="border-top:1px solid #000;margin-top:4px;padding-top:2px;min-height:12px;">
                    @if($finalizado){{ $fechaFmt }} {{ $horaFmt }}@endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Firmas --}}
<table class="sig-table">
    <tr>
        <td>
            @if($firmaSolicitud?->firma_data)
            <img src="{{ $firmaSolicitud->firma_data }}" alt="Firma solicitante"
                 style="height:45px;width:auto;display:block;margin:0 auto 2px;mix-blend-mode:multiply;">
            @endif
            <div class="sig-line">{{ $bitacora->nombre_personal }}</div>
            <div class="sig-label">Solicita: Nombre y Firma</div>
        </td>
        <td>
            @if($bitacora->firma_ingeniero)
            <img src="{{ $bitacora->firma_ingeniero }}" alt="Firma Biomédica"
                 style="height:45px;width:auto;display:block;margin:0 auto 2px;mix-blend-mode:multiply;">
            @endif
            <div class="sig-line">{{ $bitacora->atiende_nombre ?: '' }}</div>
            <div class="sig-label">Recibe Biomédica:</div>
        </td>
    </tr>
</table>

{{-- Footer --}}
<div class="footer">
    @if($footerB64)
        <img src="{{ $footerB64 }}" alt="Pie" style="max-height:22px;max-width:100%;display:block;margin:0 auto;">
    @else
        Av. Venustiano Carranza #2395, Zona Universitaria &nbsp;C.P. 78290 &nbsp;|&nbsp; Tel (444)198-10-00 &nbsp;|&nbsp; San Luis Potosí, S.L.P.
    @endif
</div>

</body>
</html>
