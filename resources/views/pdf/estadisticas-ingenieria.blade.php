<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas — Ingeniería Biomédica HRAEIMP</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: "Times New Roman", Times, serif; font-size: 10pt; color: #000; padding: 28px 32px; font-weight: bold; font-style: italic; }

.header { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px; }
.header-title { font-size: 16pt; font-weight: bold; font-style: italic; text-transform: uppercase; }
.header-sub  { font-size: 10pt; margin-top: 3px; }
.header-meta { text-align: right; font-size: 9pt; line-height: 1.6; }
.header-top  { display: flex; justify-content: space-between; align-items: flex-start; }

.section-title { font-size: 11pt; font-weight: bold; font-style: italic; text-transform: uppercase; border-left: 4px solid #000; padding: 5px 8px; margin: 20px 0 8px; page-break-after: avoid; }
.sub-title { font-size: 9.5pt; font-weight: bold; font-style: italic; text-transform: uppercase; border-left: 2px solid #555; padding: 3px 6px; margin: 12px 0 6px; page-break-after: avoid; color: #333; }

.stats-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.stats-table td { border: 1px solid #000; padding: 6px 5px; text-align: center; vertical-align: middle; }
.stat-num   { font-size: 15pt; font-weight: bold; font-style: italic; }
.stat-label { font-size: 7pt; text-transform: uppercase; margin-top: 2px; }
.stat-sub   { font-size: 6.5pt; color: #555; margin-top: 1px; }

table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #000; color: #fff; font-size: 7.5pt; font-weight: bold; font-style: italic; text-transform: uppercase; padding: 5px 5px; text-align: left; }
table.data td { font-size: 8pt; padding: 4px 5px; border-bottom: 1px solid #ccc; vertical-align: top; }
table.data tr:nth-child(even) td { background: #f0f0f0; }

.badge { display: inline-block; padding: 1px 5px; border: 1px solid #000; font-size: 7pt; font-weight: bold; font-style: italic; }
.footer { margin-top: 24px; border-top: 1px solid #000; padding-top: 8px; font-size: 8pt; text-align: center; }
.page-break { page-break-before: always; }
.note { font-size: 7.5pt; color: #555; font-style: italic; margin-bottom: 6px; }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-title">Estadísticas — Ingeniería Biomédica</div>
            <div class="header-sub">Departamento de Ingeniería Biomédica — HRAEIMP</div>
        </div>
        <div class="header-meta">
            Generado: {{ $generadoEn }}<br>
            Por: {{ $generadoPor }}
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════
     TAB 1: RESUMEN GENERAL
══════════════════════════════════════════════════════ --}}
<div class="section-title">1. Resumen general</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $d['totalEquipos'] }}</div><div class="stat-label">Total equipos</div></td>
        <td><div class="stat-num">{{ $d['estatusAgrupado']['Fuera de Servicio'] }}</div><div class="stat-label">Fuera de servicio</div></td>
        <td><div class="stat-num">{{ $d['reportesPendiente'] + $d['reportesEnCurso'] }}</div><div class="stat-label">Reportes activos</div></td>
        <td><div class="stat-num">{{ $d['tasaConcrecion'] }}%</div><div class="stat-label">% Concreción</div></td>
        <td><div class="stat-num">{{ $d['tasaSatisfaccion'] }}%</div><div class="stat-label">% Satisfacción</div></td>
    </tr>
</table>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $d['totalReportes'] }}</div><div class="stat-label">Total reportes</div></td>
        <td><div class="stat-num">{{ $d['reportesConcretados'] }}</div><div class="stat-label">Concretados</div></td>
        <td><div class="stat-num">{{ $d['totalBitacoras'] }}</div><div class="stat-label">Total bitácoras</div></td>
        <td><div class="stat-num">{{ $tiempoDias > 0 ? $tiempoDias.' d' : '—' }}</div><div class="stat-label">Tiempo prom. resolución</div></td>
        <td><div class="stat-num">{{ $d['ingenierosActivos'] }}/{{ $d['totalIngenieros'] }}</div><div class="stat-label">Ingenieros activos</div></td>
    </tr>
</table>


{{-- ══════════════════════════════════════════════════════
     TAB 2: PERSONAL
══════════════════════════════════════════════════════ --}}
<div class="section-title page-break">2. Personal</div>

<div class="sub-title">KPIs del equipo</div>
<table class="stats-table">
    <tr>
        <td>
            <div class="stat-num">{{ $d['ingenierosActivos'] }}</div>
            <div class="stat-label">Ingenieros activos</div>
            <div class="stat-sub">de {{ $d['totalIngenieros'] }} registrados</div>
        </td>
        <td>
            <div class="stat-num">{{ $totalAct }}</div>
            <div class="stat-label">Reportes activos ahora</div>
            <div class="stat-sub">pendientes + en curso</div>
        </td>
        <td>
            <div class="stat-num">{{ $totalTodo }}</div>
            <div class="stat-label">Total asignados</div>
            <div class="stat-sub">histórico completo</div>
        </td>
        <td>
            <div class="stat-num">{{ $promPorIng }}</div>
            <div class="stat-label">Promedio por ingeniero</div>
            <div class="stat-sub">reportes asignados c/u</div>
        </td>
        <td>
            <div class="stat-num">{{ $envioAreaLabel }}</div>
            <div class="stat-label">T. prom. p. firmar</div>
            <div class="stat-sub">desde que llega hasta firma</div>
        </td>
    </tr>
</table>

<div class="sub-title" style="margin-top:14px;">Perfil individual de ingenieros</div>
@if(!empty($d['ingenierosMetrics']))
@foreach($d['ingenierosMetrics'] as $ing)
@php
    $pct      = $ing['tasa_concrecion'];
    $pctColor = $pct >= 70 ? '#166534' : ($pct >= 40 ? '#92400e' : '#991b1b');
    $cargaLbl = $ing['activos'] === 0 ? 'Disponible' : ($ing['activos'] <= 2 ? 'Carga normal' : 'Alta carga');
@endphp
<table style="width:100%;border-collapse:collapse;border:1px solid #ccc;margin-bottom:8px;font-size:8pt;">
    <tr style="background:#eee;">
        <td colspan="9" style="padding:5px 8px;font-size:9pt;font-weight:bold;font-style:italic;">
            {{ $ing['nombre'] }}
            <span style="font-size:7.5pt;font-weight:normal;color:#444;margin-left:8px;">{{ $ing['cargo'] }}</span>
            <span style="float:right;font-size:7pt;border:1px solid #000;padding:1px 6px;">{{ $cargaLbl }}</span>
        </td>
    </tr>
    <tr style="text-align:center;background:#f9f9f9;">
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['total'] }}</strong><br><span style="font-size:6.5pt;">Total</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['activos'] }}</strong><br><span style="font-size:6.5pt;">Activos</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['este_mes'] }}</strong><br><span style="font-size:6.5pt;">Este mes</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['concretados'] }}</strong><br><span style="font-size:6.5pt;">Concretados</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['completados'] }}</strong><br><span style="font-size:6.5pt;">Completados</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['bitacoras'] }}</strong><br><span style="font-size:6.5pt;">Bitácoras</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $fmtHoras($ing['tiempo_envio_h']) }}</strong><br><span style="font-size:6.5pt;">T. p. firma</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong>{{ $ing['tiempo_prom_dias'] ? $ing['tiempo_prom_dias'].' d' : '—' }}</strong><br><span style="font-size:6.5pt;">Resolución</span></td>
        <td style="padding:5px;border:1px solid #ddd;"><strong style="color:{{ $pctColor }};">{{ $pct }}%</strong><br><span style="font-size:6.5pt;">% Concreción</span></td>
    </tr>
    <tr>
        <td colspan="9" style="padding:4px 8px;font-size:7pt;border-top:1px solid #ddd;">
            Último reporte: {{ $ing['ultimo_reporte'] ?? 'Sin asignaciones' }}
        </td>
    </tr>
</table>
@endforeach

<div class="sub-title" style="margin-top:14px;">Tabla comparativa</div>
<table class="data">
    <thead><tr>
        <th>Ingeniero</th>
        <th style="width:35px;text-align:center;">Total</th>
        <th style="width:38px;text-align:center;">Activos</th>
        <th style="width:48px;text-align:center;">Concret.</th>
        <th style="width:40px;text-align:center;">Este mes</th>
        <th style="width:45px;text-align:center;">Bitácoras</th>
        <th style="width:52px;text-align:center;">T. p. firma</th>
        <th style="width:52px;text-align:center;">Resolución</th>
        <th style="width:60px;text-align:center;">% Concreción</th>
    </tr></thead>
    <tbody>
    @foreach(collect($d['ingenierosMetrics'])->sortBy('nombre')->values() as $ing)
    @php
        $pct = $ing['tasa_concrecion'];
        $pctColor = $pct >= 70 ? '#166534' : ($pct >= 40 ? '#92400e' : '#991b1b');
    @endphp
    <tr>
        <td>
            <strong>{{ $ing['nombre'] }}</strong><br>
            <span style="font-size:7pt;color:#555;">{{ $ing['cargo'] }}</span>
        </td>
        <td style="text-align:center;">{{ $ing['total'] }}</td>
        <td style="text-align:center;">{{ $ing['activos'] }}</td>
        <td style="text-align:center;">{{ $ing['concretados'] }}</td>
        <td style="text-align:center;">{{ $ing['este_mes'] }}</td>
        <td style="text-align:center;">{{ $ing['bitacoras'] }}</td>
        <td style="text-align:center;font-size:7.5pt;">{{ $fmtHoras($ing['tiempo_envio_h']) }}</td>
        <td style="text-align:center;font-size:7.5pt;">{{ $ing['tiempo_prom_dias'] ? $ing['tiempo_prom_dias'].' d' : '—' }}</td>
        <td style="text-align:center;"><strong style="color:{{ $pctColor }};">{{ $pct }}%</strong></td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="sub-title" style="margin-top:14px;">Reportes activos por ingeniero</div>
@if(!empty($d['reportesPorIngenieroActivos']))
<table class="data">
    <thead><tr><th>Ingeniero</th><th style="width:80px;text-align:center;">Activos</th></tr></thead>
    <tbody>
    @foreach($d['reportesPorIngenieroActivos'] as $nombre => $cnt)
    <tr><td>{{ $nombre }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

<div class="sub-title" style="margin-top:10px;">Total de reportes asignados por ingeniero (histórico)</div>
@if(!empty($d['reportesPorIngenieroTotal']))
<table class="data">
    <thead><tr><th>Ingeniero</th><th style="width:80px;text-align:center;">Total</th></tr></thead>
    <tbody>
    @foreach($d['reportesPorIngenieroTotal'] as $nombre => $cnt)
    <tr><td>{{ $nombre }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

@else
<p style="font-size:9pt;padding:8px 0;">Sin ingenieros activos registrados.</p>
@endif


{{-- ══════════════════════════════════════════════════════
     TAB 3: EQUIPOS
══════════════════════════════════════════════════════ --}}
<div class="section-title page-break">3. Equipos médicos</div>

<div class="sub-title">Indicadores generales</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $d['totalEquipos'] }}</div><div class="stat-label">Total equipos</div></td>
        <td><div class="stat-num">{{ $d['conContrato'] }}</div><div class="stat-label">Con contrato</div></td>
        <td><div class="stat-num">{{ $d['sinContrato'] }}</div><div class="stat-label">Sin contrato</div></td>
        <td><div class="stat-num">{{ $pctContrato }}%</div><div class="stat-label">Cobertura contratos</div></td>
        <td><div class="stat-num">{{ $d['conGarantia'] }}</div><div class="stat-label">Con garantía</div></td>
        <td><div class="stat-num">{{ $d['finVidaUtil'] }}</div><div class="stat-label">Fin vida útil</div></td>
        <td><div class="stat-num">{{ $d['proximosMp'] }}</div><div class="stat-label">MP próx. 30 d</div></td>
        <td><div class="stat-num">{{ $d['mpVencidos'] }}</div><div class="stat-label">MP vencidos</div></td>
    </tr>
</table>

<div class="sub-title" style="margin-top:12px;">Estatus de equipos</div>
<table class="stats-table">
    <tr>
        @foreach($d['estatusAgrupado'] as $lbl => $cnt)
        <td><div class="stat-num">{{ $cnt }}</div><div class="stat-label">{{ $lbl }}</div></td>
        @endforeach
    </tr>
</table>

@if(!empty($d['condicionesEquipos']))
<div class="sub-title" style="margin-top:12px;">Condiciones de los equipos</div>
<table class="data">
    <thead><tr><th>Condición</th><th style="width:80px;text-align:center;">Equipos</th></tr></thead>
    <tbody>
    @foreach($d['condicionesEquipos'] as $cond => $cnt)
    <tr><td>{{ $cond }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

@if(!empty($d['tipoPropiedadEquipos']))
<div class="sub-title" style="margin-top:12px;">Tipo de propiedad</div>
<table class="data">
    <thead><tr><th>Tipo</th><th style="width:80px;text-align:center;">Equipos</th></tr></thead>
    <tbody>
    @foreach($d['tipoPropiedadEquipos'] as $tipo => $cnt)
    <tr><td>{{ $tipo }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

@if(!empty($d['equiposPorArea']))
<div class="sub-title" style="margin-top:12px;">Top 10 áreas por cantidad de equipos</div>
<table class="data">
    <thead><tr><th>Área</th><th style="width:80px;text-align:center;">Equipos</th></tr></thead>
    <tbody>
    @foreach($d['equiposPorArea'] as $area => $cnt)
    <tr><td>{{ $area }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- ══════════════════════════════════════════════════════
     TAB 4: REPORTES Y CALIDAD
══════════════════════════════════════════════════════ --}}
<div class="section-title page-break">4. Reportes y calidad</div>

<div class="sub-title">Indicadores de reportes</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $d['totalReportes'] }}</div><div class="stat-label">Total</div></td>
        <td><div class="stat-num">{{ $d['reportesPendiente'] }}</div><div class="stat-label">Pendientes</div></td>
        <td><div class="stat-num">{{ $d['reportesEnCurso'] }}</div><div class="stat-label">En curso</div></td>
        <td><div class="stat-num">{{ $d['reportesCompletados'] }}</div><div class="stat-label">Completados</div></td>
        <td><div class="stat-num">{{ $d['reportesConcretados'] }}</div><div class="stat-label">Concretados</div></td>
        <td><div class="stat-num">{{ $d['tasaConcrecion'] }}%</div><div class="stat-label">% Concreción</div></td>
        <td><div class="stat-num">{{ $tiempoDias > 0 ? $tiempoDias.' d' : '—' }}</div><div class="stat-label">T. prom. resolución</div></td>
    </tr>
</table>

<div class="sub-title" style="margin-top:12px;">Tendencia de reportes — últimos 6 meses</div>
<table class="data">
    <thead><tr>
        @foreach($d['mesesLabels'] as $mes)
        <th style="text-align:center;">{{ $mes }}</th>
        @endforeach
    </tr></thead>
    <tbody>
    <tr>
        @foreach($d['mesesData'] as $val)
        <td style="text-align:center;">{{ $val }}</td>
        @endforeach
    </tr>
    </tbody>
</table>

<div class="sub-title" style="margin-top:12px;">Calidad de atención (bitácoras)</div>
<table class="stats-table">
    <tr>
        <td>
            <div class="stat-num">{{ $d['calidad']['satisfactoria'] }}</div>
            <div class="stat-label">Satisfactoria{{ $totalCalidad > 0 ? ' — '.round($d['calidad']['satisfactoria']/$totalCalidad*100).'%' : '' }}</div>
        </td>
        <td>
            <div class="stat-num">{{ $d['calidad']['parcial'] }}</div>
            <div class="stat-label">Parcial{{ $totalCalidad > 0 ? ' — '.round($d['calidad']['parcial']/$totalCalidad*100).'%' : '' }}</div>
        </td>
        <td>
            <div class="stat-num">{{ $d['calidad']['no_satisfactoria'] }}</div>
            <div class="stat-label">No satisfactoria{{ $totalCalidad > 0 ? ' — '.round($d['calidad']['no_satisfactoria']/$totalCalidad*100).'%' : '' }}</div>
        </td>
        <td><div class="stat-num">{{ $d['tasaSatisfaccion'] }}%</div><div class="stat-label">Tasa de satisfacción</div></td>
        <td><div class="stat-num">{{ $d['totalBitacoras'] }}</div><div class="stat-label">Total bitácoras</div></td>
    </tr>
</table>

@if(!empty($d['reportesPorAreaDep']))
<div class="sub-title" style="margin-top:12px;">Top áreas con más reportes</div>
<table class="data">
    <thead><tr><th>Área / Departamento</th><th style="width:80px;text-align:center;">Reportes</th></tr></thead>
    <tbody>
    @foreach($d['reportesPorAreaDep'] as $area => $cnt)
    <tr><td>{{ $area }}</td><td style="text-align:center;">{{ $cnt }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

<div class="sub-title" style="margin-top:12px;">Órdenes de servicio / Mantenimientos</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $d['totalMantenimientos'] }}</div><div class="stat-label">Total</div></td>
        <td><div class="stat-num">{{ $d['mtosPendientes'] }}</div><div class="stat-label">Pendientes</div></td>
        <td><div class="stat-num">{{ $d['mtosAceptados'] }}</div><div class="stat-label">Aceptados</div></td>
        <td><div class="stat-num">{{ $d['mtosCompletados'] }}</div><div class="stat-label">Completados</div></td>
        <td><div class="stat-num">{{ $d['mtosRechazados'] }}</div><div class="stat-label">Rechazados</div></td>
        <td><div class="stat-num">{{ $tiempoMtoDias > 0 ? $tiempoMtoDias.' d' : '—' }}</div><div class="stat-label">T. prom. resolución</div></td>
    </tr>
</table>


<div class="footer">
    Estadísticas Ingeniería Biomédica — HRAEIMP — {{ $generadoEn }} — Documento confidencial. Solo para uso interno.
</div>

</body>
</html>
