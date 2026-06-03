<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Vale de Entrega</title>
<style>
body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; color: #000; margin: 30px 36px; }
</style>
</head>
<body>

{{-- Encabezado --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 2px solid #000; margin-bottom: 14px;">
    <tr>
        <td style="padding-bottom:8px;">
            <span style="font-size:11pt; font-weight:bold; text-transform:uppercase;">Hospital Regional de Alta Especialidad del Istmo</span><br>
            <span style="font-size:8pt; color:#444;">Departamento de Ingeniería Biomédica</span>
        </td>
        <td style="text-align:right; padding-bottom:8px; font-size:9pt; white-space:nowrap;">
            Fecha: <b>{{ $fecha }}</b>
        </td>
    </tr>
</table>

{{-- Título --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
    <tr>
        <td style="background:#1d4ed8; color:white; text-align:center; padding:8px 0; font-size:13pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.06em;">
            Vale de Entrega de Material / Consumible
        </td>
    </tr>
</table>

{{-- Sección título --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:7px;">
    <tr>
        <td style="border-left:3px solid #1d4ed8; padding-left:7px; font-size:8.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.05em; color:#374151;">
            Datos del material
        </td>
    </tr>
</table>

{{-- Tabla de datos --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-bottom:16px;">
    <tr>
        <td width="30%" style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Nombre</td>
        <td width="70%" style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt; font-weight:bold;">{{ $nombre }}</td>
    </tr>
    @if($descripcion)
    <tr>
        <td style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Descripción</td>
        <td style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt;">{{ $descripcion }}</td>
    </tr>
    @endif
    <tr>
        <td style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Marca</td>
        <td style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt;">{{ $marca ?: '—' }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Referencia</td>
        <td style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt;">{{ $referencia ?: '—' }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Cantidad entregada</td>
        <td style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt; font-weight:bold;">{{ $cantidad }}</td>
    </tr>
    @if($observaciones)
    <tr>
        <td style="border:1px solid #ccc; padding:6px 10px; background:#f3f4f6; font-size:8.5pt; font-weight:bold; color:#374151;">Observaciones</td>
        <td style="border:1px solid #ccc; padding:6px 10px; font-size:9.5pt;">{{ $observaciones }}</td>
    </tr>
    @endif
</table>

{{-- Sección firmas --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:7px;">
    <tr>
        <td style="border-left:3px solid #1d4ed8; padding-left:7px; font-size:8.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.05em; color:#374151;">
            Firmas de conformidad
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
    <tr>
        <td width="48%" style="vertical-align:top; padding-right:8px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #ccc;">
                <tr>
                    <td style="background:#f3f4f6; padding:6px 10px; font-size:8.5pt; font-weight:bold; text-transform:uppercase; border-bottom:1.5px solid #ccc;">
                        Entrega
                    </td>
                </tr>
                <tr>
                    <td style="height:80px; padding:8px 10px;"></td>
                </tr>
                <tr>
                    <td style="border-top:1.5px solid #000; padding:5px 10px 8px;">
                        <span style="font-size:8.5pt; font-weight:bold;">{{ $nombre_entrega ?: '_______________________________' }}</span><br>
                        <span style="font-size:8pt; color:#6b7280;">{{ $cargo_entrega ?: 'Nombre y cargo' }}</span>
                    </td>
                </tr>
            </table>
        </td>
        <td width="4%"></td>
        <td width="48%" style="vertical-align:top; padding-left:8px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #ccc;">
                <tr>
                    <td style="background:#f3f4f6; padding:6px 10px; font-size:8.5pt; font-weight:bold; text-transform:uppercase; border-bottom:1.5px solid #ccc;">
                        Recibe
                    </td>
                </tr>
                <tr>
                    <td style="height:80px; padding:8px 10px;"></td>
                </tr>
                <tr>
                    <td style="border-top:1.5px solid #000; padding:5px 10px 8px;">
                        <span style="font-size:8.5pt; font-weight:bold;">{{ $nombre_recibe ?: '_______________________________' }}</span><br>
                        <span style="font-size:8pt; color:#6b7280;">{{ $cargo_recibe ?: 'Nombre y cargo' }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br><br>
<table width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
    <tr>
        <td style="padding-top:8px; font-size:7.5pt; color:#9ca3af; text-align:center;">
            Documento generado el {{ $fecha }} &middot; Ingeniería Biomédica — HRAEIMP
        </td>
    </tr>
</table>

</body>
</html>
