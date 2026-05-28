<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Auditoría — Ingeniería Biomédica HRAE</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: "Times New Roman", Times, serif; font-size: 10pt; color: #000000; padding: 28px 32px; font-weight: bold; font-style: italic; }

.header { border-bottom: 2px solid #000000; padding-bottom: 12px; margin-bottom: 18px; }
.header-top { display: flex; justify-content: space-between; align-items: flex-start; }
.header-title { font-size: 16pt; font-weight: bold; font-style: italic; color: #000000; text-transform: uppercase; }
.header-sub { font-size: 10pt; color: #000000; margin-top: 3px; font-weight: bold; font-style: italic; }
.header-meta { text-align: right; font-size: 9pt; color: #000000; line-height: 1.6; font-weight: bold; font-style: italic; }

.periodo { border: 1px solid #000000; padding: 8px 12px; margin-bottom: 16px; font-size: 10pt; color: #000000; font-weight: bold; font-style: italic; }
.protected-notice { border: 1px solid #000000; padding: 5px 8px; font-size: 8pt; color: #000000; margin-bottom: 14px; font-weight: bold; font-style: italic; }

.section-title { font-size: 11pt; font-weight: bold; font-style: italic; text-transform: uppercase; color: #000000; border-left: 4px solid #000000; padding: 5px 8px; margin-bottom: 8px; margin-top: 22px; page-break-after: avoid; }

.stats-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.stats-table td { border: 1px solid #000000; padding: 7px 6px; text-align: center; }
.stat-num { font-size: 18pt; font-weight: bold; font-style: italic; color: #000000; }
.stat-label { font-size: 7.5pt; text-transform: uppercase; color: #000000; margin-top: 3px; font-weight: bold; font-style: italic; }

table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #000000; color: #ffffff; font-size: 8pt; font-weight: bold; font-style: italic; text-transform: uppercase; padding: 5px 6px; text-align: left; }
table.data td { font-size: 8.5pt; padding: 4px 6px; border-bottom: 1px solid #cccccc; color: #000000; vertical-align: top; font-weight: bold; font-style: italic; }
table.data tr:nth-child(even) td { background: #f0f0f0; }

.badge { display: inline-block; padding: 1px 5px; border: 1px solid #000000; font-size: 7.5pt; font-weight: bold; font-style: italic; color: #000000; }

.footer { margin-top: 24px; border-top: 1px solid #000000; padding-top: 8px; font-size: 8pt; color: #000000; text-align: center; font-weight: bold; font-style: italic; }
.empty { font-size: 9pt; color: #000000; padding: 8px 0; font-weight: bold; font-style: italic; }

.page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-title">Reporte de Auditoría</div>
            <div class="header-sub">Departamento de Ingeniería Biomédica — HRAEIMP</div>
        </div>
        <div class="header-meta">
            Generado: {{ $generadoEn }}<br>
            Por: {{ $generadoPor }}<br>
            Folio: AUD-{{ now()->format('YmdHis') }}
        </div>
    </div>
</div>

{{-- PERÍODO --}}
@if($desde || $hasta || $tipo)
<div class="periodo">
    <strong>Filtros aplicados:</strong>
    @if($desde) &nbsp;Desde: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} @endif
    @if($hasta) &nbsp;Hasta: {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }} @endif
    @if($tipo) &nbsp;Tipo de evento: {{ ucfirst($tipo) }} @endif
</div>
@else
<div class="periodo">
    <strong>Período:</strong> Historial completo hasta {{ now()->format('d/m/Y H:i') }}
</div>
@endif

<div class="protected-notice">
    &#9888; Los registros de auditoría son inmutables — el sistema impide su edición o eliminación.
</div>

{{-- RESUMEN --}}
<div class="section-title">Resumen del período</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $stats['total_firmas'] }}</div><div class="stat-label">Total firmas</div></td>
        <td><div class="stat-num">{{ $stats['total_vales'] }}</div><div class="stat-label">Total vales</div></td>
        <td><div class="stat-num">{{ $stats['vales_en_proceso'] }}</div><div class="stat-label">Vales en proceso</div></td>
        <td><div class="stat-num">{{ $stats['usuarios_activos'] }}</div><div class="stat-label">Usuarios activos</div></td>
        <td><div class="stat-num">{{ $stats['pendientes'] }}</div><div class="stat-label">Pendientes aprobación</div></td>
    </tr>
</table>

<div class="section-title" style="margin-top:12px;">Desglose de eventos registrados</div>
<table class="stats-table">
    <tr>
        <td><div class="stat-num">{{ $stats['total_logs'] }}</div><div class="stat-label">Total eventos</div></td>
        <td><div class="stat-num">{{ $stats['acceso'] }}</div><div class="stat-label">Accesos</div></td>
        <td><div class="stat-num">{{ $stats['firma_log'] }}</div><div class="stat-label">Firmas</div></td>
        <td><div class="stat-num">{{ $stats['equipo'] }}</div><div class="stat-label">Equipos</div></td>
        <td><div class="stat-num">{{ $stats['reporte'] }}</div><div class="stat-label">Reportes</div></td>
        <td><div class="stat-num">{{ $stats['usuario'] }}</div><div class="stat-label">Usuarios</div></td>
        <td><div class="stat-num">{{ $stats['historial_equipos'] }}</div><div class="stat-label">Historial inventario</div></td>
    </tr>
</table>


{{-- 1. ACTIVIDAD --}}
<div class="section-title page-break">
    1. Registro de actividad ({{ $logs->count() }} eventos{{ $tipo ? ' — tipo: ' . ucfirst($tipo) : '' }})
</div>
@if($logs->isEmpty())
<p class="empty">Sin eventos en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:80px;">Fecha/Hora</th>
        <th style="width:50px;">Tipo</th>
        <th>Descripción</th>
        <th style="width:95px;">Actor</th>
        <th style="width:65px;">IP</th>
    </tr></thead>
    <tbody>
    @foreach($logs as $log)
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
        <td><span class="badge">{{ ucfirst($log->tipo) }}</span></td>
        <td>{{ $log->descripcion }}</td>
        <td style="font-size:7.5pt;">{{ $log->actor_nombre }}</td>
        <td style="font-size:7.5pt;">{{ $log->ip ?? '—' }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- 2. FIRMAS DE DOCUMENTOS --}}
<div class="section-title page-break">
    2. Firmas de documentos ({{ $todasFirmas->count() }} registros)
</div>
@if($todasFirmas->isEmpty())
<p class="empty">Sin documentos firmados en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:80px;">Fecha</th>
        <th style="width:70px;">Tipo</th>
        <th>Documento</th>
        <th style="width:110px;">Firmante (jefe)</th>
        <th style="width:100px;">Área</th>
    </tr></thead>
    <tbody>
    @foreach($todasFirmas as $f)
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $f['fecha'] ? \Carbon\Carbon::parse($f['fecha'])->format('d/m/Y H:i') : '—' }}</td>
        <td><span class="badge">{{ $f['tipo_doc'] }}</span></td>
        <td>{{ $f['documento'] }}</td>
        <td style="font-size:7.5pt;">{{ $f['firmante'] }}</td>
        <td style="font-size:7.5pt;">{{ $f['area'] }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- 3. HISTORIAL DE EQUIPOS --}}
<div class="section-title page-break">
    3. Historial de equipos médicos ({{ $historial->count() }} registros)
</div>
@if($historial->isEmpty())
<p class="empty">Sin movimientos de equipos en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:80px;">Fecha/Hora</th>
        <th style="width:52px;">Evento</th>
        <th style="width:80px;">No. Inventario</th>
        <th>Descripción del cambio</th>
        <th style="width:75px;">Área</th>
        <th style="width:80px;">Usuario</th>
    </tr></thead>
    <tbody>
    @foreach($historial as $h)
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $h->created_at->format('d/m/Y H:i:s') }}</td>
        <td><span class="badge">{{ ucfirst($h->tipo_evento) }}</span></td>
        <td style="font-size:7.5pt;">{{ $h->inventarioEquipo?->numero_inventario ?? '—' }}</td>
        <td>
            {{ $h->descripcion }}
            @if(!empty($h->cambios))
            @foreach(array_slice($h->cambios, 0, 4) as $c)
            <br><span style="font-size:7pt;">{{ $c['etiqueta'] }}: {{ \Illuminate\Support\Str::limit($c['anterior'] ?? '—', 20) }} → {{ \Illuminate\Support\Str::limit($c['nuevo'] ?? '—', 20) }}</span>
            @endforeach
            @if(count($h->cambios) > 4)
            <br><span style="font-size:7pt;">+{{ count($h->cambios) - 4 }} cambios más</span>
            @endif
            @endif
        </td>
        <td style="font-size:7.5pt;">{{ $h->inventarioEquipo?->area ?? '—' }}</td>
        <td style="font-size:7.5pt;">{{ $h->usuario_nombre }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- 4. VALES DE INVENTARIO --}}
<div class="section-title page-break">
    4. Vales de inventario ({{ $vales->count() }} registros)
</div>
@if($vales->isEmpty())
<p class="empty">Sin vales en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:75px;">Fecha</th>
        <th style="width:48px;">Tipo</th>
        <th>Equipo</th>
        <th style="width:75px;">No. Inventario</th>
        <th style="width:75px;">Área</th>
        <th style="width:80px;">Técnico</th>
        <th style="width:80px;">Jefe firma</th>
        <th style="width:50px;">Estado</th>
    </tr></thead>
    <tbody>
    @foreach($vales as $v)
    @php
        $lbl_estado = match($v->estado) { 'culminado'=>'Firmado', 'en_firma'=>'En firma', default=>'Pendiente' };
        $lbl_tipo   = $v->tipo === 'entrega' ? 'Entrega' : 'Retiro';
    @endphp
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $v->created_at->format('d/m/Y H:i') }}</td>
        <td><span class="badge">{{ $lbl_tipo }}</span></td>
        <td>{{ $v->equipo_nombre ?: '—' }}</td>
        <td style="font-size:7.5pt;">{{ $v->numero_inventario ?: '—' }}</td>
        <td style="font-size:7.5pt;">{{ $v->area ?: '—' }}</td>
        <td style="font-size:7.5pt;">{{ $v->usuario_nombre ?: '—' }}</td>
        <td style="font-size:7.5pt;">{{ $v->jefe?->nombre ?? '—' }}</td>
        <td><span class="badge">{{ $lbl_estado }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- 5. ACCESOS AL PORTAL --}}
<div class="section-title page-break">
    5. Accesos al sistema ({{ $accesos->count() }} registros)
</div>
@if($accesos->isEmpty())
<p class="empty">Sin accesos registrados en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:110px;">Fecha y hora</th>
        <th>Usuario</th>
        <th style="width:100px;">IP</th>
    </tr></thead>
    <tbody>
    @foreach($accesos as $a)
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
        <td>{{ $a->actor_nombre }}</td>
        <td style="font-size:7.5pt;">{{ $a->ip ?? '—' }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


{{-- 6. USUARIOS DEL PORTAL --}}
<div class="section-title page-break">
    6. Usuarios del portal ({{ $usuarios->count() }} registros)
</div>
@if($usuarios->isEmpty())
<p class="empty">Sin usuarios en el período seleccionado.</p>
@else
<table class="data">
    <thead><tr>
        <th style="width:75px;">Registro</th>
        <th>Nombre</th>
        <th style="width:100px;">Servicio</th>
        <th style="width:70px;">Rol</th>
        <th style="width:110px;">Área (jefe)</th>
        <th style="width:60px;">Estado</th>
    </tr></thead>
    <tbody>
    @foreach($usuarios as $u)
    <tr>
        <td style="white-space:nowrap;font-size:7.5pt;">{{ $u->created_at->format('d/m/Y') }}</td>
        <td>{{ $u->nombre }}</td>
        <td style="font-size:7.5pt;">{{ $u->servicio ?: '—' }}</td>
        <td><span class="badge">{{ $u->es_jefe_servicio ? 'Jefe' : 'Personal' }}</span></td>
        <td style="font-size:7.5pt;">{{ $u->area_jefe_servicio ?: '—' }}</td>
        <td><span class="badge">{{ ucfirst($u->estado) }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif


<div class="footer">
    Reporte de Auditoría — Ingeniería Biomédica HRAEIMP — {{ $generadoEn }} — Documento confidencial. Solo para uso interno.
</div>

</body>
</html>
