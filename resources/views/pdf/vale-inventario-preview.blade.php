<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $vale->tipo_label }} — {{ $vale->numero_inventario }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000000;
            font-weight: bold;
            font-style: italic;
        }

        /* ── Imagen de encabezado fija en la parte superior de cada página ── */
        .page-header {
            position: fixed;
            top: 10px;
            left: 24px;
            right: 24px;
            width: auto;
        }
        .page-header img { width: 100%; display: block; }

        /* ── Imagen de pie fija en la parte inferior de cada página ── */
        .page-footer {
            position: fixed;
            bottom: 10px;
            left: 24px;
            right: 24px;
            width: auto;
            text-align: center;
            font-size: 8pt;
            color: #000000;
        }
        .page-footer img { width: 100%; display: block; }
        .page-footer-text { padding: 4px 32px 0; border-top: 1px solid #000000; }

        /* ── Contenido — margen suficiente para no quedar bajo header/footer ── */
        .content {
            margin-top: 160px;
            margin-bottom: 180px;
            padding: 0 32px;
        }

        .doc-header { border-bottom: 2px solid #000000; padding-bottom: 12px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: flex-start; }
        .header-title { font-size: 16pt; font-weight: bold; font-style: italic; color: #000000; text-transform: uppercase; }
        .header-sub { font-size: 10pt; color: #000000; margin-top: 3px; font-weight: bold; font-style: italic; }
        .header-meta { text-align: right; font-size: 9pt; color: #000000; line-height: 1.6; font-weight: bold; font-style: italic; }

        .tipo-box { border: 1px solid #000000; padding: 5px 8px; font-size: 8pt; margin-bottom: 14px; font-weight: bold; font-style: italic; }

        .section-title { font-size: 11pt; font-weight: bold; font-style: italic; text-transform: uppercase; color: #000000; border-left: 4px solid #000000; padding: 5px 8px; margin-bottom: 8px; margin-top: 18px; page-break-after: avoid; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.data th { background: #000000; color: #ffffff; font-size: 8pt; font-weight: bold; font-style: italic; text-transform: uppercase; padding: 5px 6px; text-align: left; }
        table.data td { font-size: 9pt; padding: 5px 6px; border-bottom: 1px solid #cccccc; color: #000000; vertical-align: top; font-weight: bold; font-style: italic; }
        table.data tr:nth-child(even) td { background: #f0f0f0; }
        .label-col { width: 180px; }

        .signatures { margin-top: 52px; width: 100%; page-break-inside: avoid; }
        .signatures table { width: 100%; border-collapse: collapse; }
        .sig-box { width: 45%; text-align: center; vertical-align: top; padding: 0 10px; }
        .sig-area { height: 70px; min-height: 70px; border-bottom: none; }
        .sig-spacer { display: block; height: 70px; width: 100%; }
        .sig-line { border-top: 2px solid #000000; padding-top: 6px; margin-top: 4px; font-size: 9pt; font-weight: bold; font-style: italic; }
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
@endphp

{{-- IMAGEN FIJA DE ENCABEZADO --}}
@if($headerB64)
<div class="page-header">
    <img src="{{ $headerB64 }}" alt="Encabezado">
</div>
@endif

{{-- IMAGEN FIJA DE PIE --}}
<div class="page-footer">
    @if($footerB64)
        <img src="{{ $footerB64 }}" alt="Pie de página">
    @else
        <div class="page-footer-text">
            Documento generado el {{ now()->format('d/m/Y H:i') }} — Área de Ingeniería Biomédica, HRAEIMP
        </div>
    @endif
</div>

{{-- CONTENIDO --}}
<div class="content">

    {{-- Título del documento --}}
    <div class="doc-header">
        <div>
            <div class="header-title">{{ $vale->tipo_label }}</div>
            <div class="header-sub">Departamento de Ingeniería Biomédica — HRAEIMP</div>
        </div>
        <div class="header-meta">
            Folio: VAL-{{ str_pad($vale->id, 5, '0', STR_PAD_LEFT) }}<br>
            Fecha: {{ $vale->created_at->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- TIPO --}}
    <div class="tipo-box">
        <strong>Tipo de movimiento:</strong> &nbsp;
        {{ strtoupper($vale->tipo === 'entrega' ? 'Entrega (alta / préstamo)' : 'Retiro (baja / devolución)') }}
    </div>

    {{-- DATOS DEL EQUIPO --}}
    @php $equipos = $vale->equipos ?: [['equipo_nombre' => $vale->equipo_nombre, 'numero_inventario' => $vale->numero_inventario, 'marca' => $vale->marca, 'modelo' => $vale->modelo, 'numero_serie' => $vale->numero_serie, 'unidad_medica' => $vale->unidad_medica]]; @endphp

    @foreach($equipos as $i => $eq)
    <div style="page-break-inside: avoid;">
    <div class="section-title">{{ count($equipos) > 1 ? 'Equipo ' . ($i + 1) : 'Datos del equipo' }}</div>
    <table class="data">
        <tbody>
            <tr><td class="label-col">Equipo / Descripción</td><td>{{ $eq['equipo_nombre'] ?: '—' }}</td></tr>
            @if(!empty($eq['numero_inventario']))
            <tr><td class="label-col">No. Inventario</td><td>{{ $eq['numero_inventario'] }}</td></tr>
            @endif
            @if(!empty($eq['marca']))
            <tr><td class="label-col">Marca</td><td>{{ $eq['marca'] }}</td></tr>
            @endif
            @if(!empty($eq['modelo']))
            <tr><td class="label-col">Modelo</td><td>{{ $eq['modelo'] }}</td></tr>
            @endif
            @if(!empty($eq['numero_serie']))
            <tr><td class="label-col">No. Serie</td><td>{{ $eq['numero_serie'] }}</td></tr>
            @endif
            @if(!empty($eq['unidad_medica']))
            <tr><td class="label-col">Unidad médica</td><td>{{ $eq['unidad_medica'] }}</td></tr>
            @endif
            @if($vale->cantidad_entregada)
            <tr><td class="label-col">Cantidad entregada</td><td>{{ $vale->cantidad_entregada }}</td></tr>
            @endif
        </tbody>
    </table>
    </div>
    @endforeach

    {{-- INFORMACIÓN GENERAL --}}
    <div class="section-title">Información general</div>
    <table class="data">
        <tbody>
            <tr><td class="label-col">Área / Departamento</td><td>{{ $vale->area ?: '—' }}</td></tr>
            @if($vale->quien_recibe)
            <tr><td class="label-col">Recibe</td><td>{{ $vale->quien_recibe }}</td></tr>
            @endif
            @if($vale->cargo_recibe)
            <tr><td class="label-col">Cargo / Servicio</td><td>{{ $vale->cargo_recibe }}</td></tr>
            @endif
            @if($vale->observaciones)
            <tr><td class="label-col">Observaciones</td><td>{{ $vale->observaciones }}</td></tr>
            @endif
        </tbody>
    </table>

    {{-- FIRMAS --}}
    @php
        $parseFirma = function(?string $raw): array {
            if (!$raw) return ['type' => 'none', 'content' => null];
            $imagen = $raw;
            if (str_starts_with(ltrim($raw), '{')) {
                $decoded = json_decode($raw, true);
                $imagen  = $decoded['imagen'] ?? $raw;
            }
            if (!$imagen) return ['type' => 'none', 'content' => null];
            if (str_starts_with($imagen, 'data:image/svg+xml;charset=utf-8,')) {
                $svg = rawurldecode(substr($imagen, strlen('data:image/svg+xml;charset=utf-8,')));
                return ['type' => 'svg', 'content' => $svg];
            }
            if (str_starts_with($imagen, 'data:image/svg+xml;base64,')) {
                $svg = base64_decode(substr($imagen, strlen('data:image/svg+xml;base64,')));
                return ['type' => 'svg', 'content' => $svg];
            }
            if (str_starts_with($imagen, 'data:image/')) {
                return ['type' => 'img', 'content' => $imagen];
            }
            if (str_contains($imagen, '<path') || str_contains($imagen, '<polyline') || str_contains($imagen, '<line')) {
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 140">' . $imagen . '</svg>';
                return ['type' => 'svg', 'content' => $svg];
            }
            return ['type' => 'none', 'content' => null];
        };
        $firmaEnt = $parseFirma($vale->firma_ingeniero);
        $firmaRec = $parseFirma($vale->firma_imagen);
    @endphp

    {{-- FIRMAS --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px; page-break-inside:avoid;">
        <tr>
            <td width="45%" style="text-align:center; padding:0 10px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr><td style="height:70px; text-align:center; vertical-align:middle;">
                        @if($firmaEnt['type'] === 'img')
                            <img src="{{ $firmaEnt['content'] }}" style="max-height:60px;max-width:100%;" alt="Firma">
                        @elseif($firmaEnt['type'] === 'svg')
                            {!! $firmaEnt['content'] !!}
                        @endif
                    </td></tr>
                    <tr><td style="border-top:2px solid #000000; padding-top:6px; font-size:9pt; font-weight:bold; font-style:italic; text-align:center;">
                        ENTREGA<br>
                        <span style="font-weight:normal;">
                            @if($vale->nombre_entrega){{ $vale->nombre_entrega }}@if($vale->cargo_entrega)<br>{{ $vale->cargo_entrega }}@endif
                            @else Ingeniería Biomédica @endif
                        </span>
                    </td></tr>
                </table>
            </td>
            <td width="10%"></td>
            <td width="45%" style="text-align:center; padding:0 10px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr><td style="height:70px; text-align:center; vertical-align:middle;">
                        @if($firmaRec['type'] === 'img')
                            <img src="{{ $firmaRec['content'] }}" style="max-height:60px;max-width:100%;" alt="Firma">
                        @elseif($firmaRec['type'] === 'svg')
                            {!! $firmaRec['content'] !!}
                        @endif
                    </td></tr>
                    <tr><td style="border-top:2px solid #000000; padding-top:6px; font-size:9pt; font-weight:bold; font-style:italic; text-align:center;">
                        RECIBE<br>
                        <span style="font-weight:normal;">{{ $vale->quien_recibe ?: ($vale->area ?: 'Área correspondiente') }}</span>
                        @if($vale->cargo_recibe)<br><span style="font-weight:normal;">{{ $vale->cargo_recibe }}</span>@endif
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>

</div>{{-- /content --}}

</body>
</html>
