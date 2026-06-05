{{--
    Plantilla oficial HRAEDIMP / IMSS Bienestar
    Uso:
        @include('pdf.partials.plantilla', [
            'titulo'       => 'TÍTULO DEL DOCUMENTO',
            'fecha'        => '14/04/2026',
            'subtitulo'    => 'Descripción opcional debajo del título',
        ])
    Luego cierra con:
        @include('pdf.partials.plantilla-footer')
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Documento HRAEDIMP' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.35;
            color: #1a1a1a;
            /* Márgenes del docx: top≈1.25cm right≈2.9cm bottom≈1.15cm left≈3cm */
            padding: 10px 30px 140px 30px;
        }

        /* ─── ENCABEZADO ─── */
        .plantilla-header {
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #7B3A2A;
        }
        .plantilla-header img {
            display: block;
            width: 100%;
            height: auto;
        }

        /* ─── BLOQUE TÍTULO / FECHA ─── */
        .plantilla-titulo-bloque {
            margin: 10px 0 14px 0;
        }
        .plantilla-titulo {
            font-family: Arial, sans-serif;
            font-size: 15px;
            font-weight: bold;
            font-style: italic;
            text-align: center;
            color: #1a1a1a;
            line-height: 1.3;
        }
        .plantilla-subtitulo {
            font-size: 9px;
            text-align: center;
            color: #555;
            margin-top: 3px;
            font-style: italic;
        }
        .plantilla-fecha {
            font-size: 11px;
            font-weight: bold;
            text-align: right;
            color: #1a1a1a;
            margin-top: 4px;
        }

        /* ─── PIE DE PÁGINA fijo ─── */
        .plantilla-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 4px 30px 6px 30px;
            background: #fff;
            border-top: 1px solid #CBD5E1;
        }
        .plantilla-footer-img {
            display: block;
            max-width: 220px;
            height: auto;
            margin: 0 auto 3px auto;
        }
        .plantilla-footer-texto {
            font-size: 7px;
            color: #6B7280;
            text-align: center;
        }

        /* ─── UTILIDADES de contenido ─── */
        .seccion-titulo {
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            background: #7B3A2A;
            padding: 4px 8px;
            margin: 12px 0 6px 0;
        }
        .seccion-titulo-azul {
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            background: #1E3A5F;
            padding: 4px 8px;
            margin: 12px 0 6px 0;
        }

        .ficha-card {
            background: #FAF7F5;
            border: 1px solid #D4B9A8;
            border-left: 4px solid #7B3A2A;
            border-radius: 3px;
            padding: 7px 10px;
            margin-bottom: 10px;
        }
        .ficha-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .ficha-card td {
            padding: 2px 6px 2px 0;
            font-size: 9px;
            vertical-align: top;
        }
        .ficha-card .lbl {
            font-weight: bold;
            color: #4B2E1E;
            white-space: nowrap;
            width: 140px;
        }

        .stats-bar {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #D4B9A8;
            border-radius: 3px;
            overflow: hidden;
        }
        .stat {
            display: table-cell;
            text-align: center;
            padding: 5px 4px;
            border-right: 1px solid #D4B9A8;
            background: #FAF7F5;
        }
        .stat:last-child { border-right: none; }
        .stat-num { font-size: 14px; font-weight: bold; color: #7B3A2A; display: block; }
        .stat-lbl { font-size: 7.5px; color: #6B7280; }

        /* Tabla de datos */
        .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 8px;
        }
        .tabla-datos thead th {
            background: #1E3A5F;
            color: #fff;
            font-weight: bold;
            padding: 4px 5px;
            border: 1px solid #1a2e4a;
            text-align: left;
        }
        .tabla-datos tbody td {
            padding: 3px 5px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            word-break: break-word;
        }
        .tabla-datos tbody tr:nth-child(even) td {
            background: #F0F4F8;
        }
        .tabla-datos tbody tr:hover td {
            background: #E8F0FE;
        }

        /* Badges de evento */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-creado      { background: #DCFCE7; color: #166534; }
        .badge-actualizado { background: #DBEAFE; color: #1D4ED8; }
        .badge-eliminado   { background: #FEE2E2; color: #991B1B; }
        .badge-gray        { background: #F3F4F6; color: #374151; }

        .td-anterior { color: #991B1B; background: #FEF2F2 !important; }
        .td-nuevo    { color: #166534; background: #F0FDF4 !important; }

        /* Timeline */
        .event {
            border-left: 3px solid #D4B9A8;
            padding-left: 10px;
            margin-bottom: 8px;
            position: relative;
        }
        .event-dot {
            position: absolute;
            left: -6px;
            top: 3px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: 1.5px solid #fff;
        }
        .dot-creado      { background: #16A34A; }
        .dot-actualizado { background: #2563EB; }
        .dot-eliminado   { background: #DC2626; }

        .event-header { display: table; width: 100%; }
        .event-meta   { display: table-cell; width: 60%; vertical-align: top; }
        .event-time   { display: table-cell; width: 40%; text-align: right; color: #6B7280; vertical-align: top; }
        .event-desc   { font-size: 9px; color: #374151; margin-top: 2px; }

        .cambios-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 8px;
        }
        .cambios-table th {
            background: #E8DEDD;
            color: #374151;
            font-weight: bold;
            padding: 3px 5px;
            border: 1px solid #D4B9A8;
            text-align: left;
        }
        .cambios-table td {
            padding: 3px 5px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            word-break: break-word;
        }
        .td-campo { font-weight: bold; color: #374151; width: 28%; }

        hr.sep { border: none; border-top: 1px dashed #D4B9A8; margin: 6px 0; }
        .vacio { text-align: center; color: #9CA3AF; font-style: italic; padding: 20px; }

        @page { margin: 0; }
    </style>
</head>
<body>

    {{-- ── ENCABEZADO ── --}}
    <div class="plantilla-header" style="border-bottom:2px solid #7B3A2A; padding-bottom:8px; margin-bottom:10px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="font-size:13px; font-weight:bold; font-style:italic;">
                    Hospital Regional de Alta Especialidad Dr. Ignacio Morones Prieto
                </td>
                <td style="text-align:right; font-size:10px; color:#555;">
                    Ingeniería Biomédica
                </td>
            </tr>
        </table>
    </div>

    {{-- ── TÍTULO Y FECHA ── --}}
    <div class="plantilla-titulo-bloque">
        <div class="plantilla-titulo">{{ $titulo ?? '' }}</div>
        @if(!empty($subtitulo))
            <div class="plantilla-subtitulo">{{ $subtitulo }}</div>
        @endif
        @if(!empty($fecha))
            <div class="plantilla-fecha">Fecha: {{ $fecha }}</div>
        @endif
    </div>

    {{-- ── PIE DE PÁGINA FIJO ── --}}
    <div class="plantilla-footer">
        <div class="plantilla-footer-texto">
            ☎ 2809-24556100 &nbsp;|&nbsp; Av. Venustiano Carranza N° 2395, Zona Universitaria, 78290 San Luis Potosí, S.L.P.
        </div>
    </div>

    {{-- ── CONTENIDO (se inserta después de este include) ── --}}
