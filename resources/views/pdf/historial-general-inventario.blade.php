@include('pdf.partials.plantilla', [
    'titulo'    => 'HISTORIAL GENERAL DE MOVIMIENTOS — INVENTARIO DE EQUIPOS BIOMÉDICOS',
    'subtitulo' => isset($filtros['desde']) || isset($filtros['hasta'])
        ? 'Período: ' . ($filtros['desde'] ?? '—') . ' al ' . ($filtros['hasta'] ?? 'hoy')
        : 'Todos los registros',
    'fecha'     => $fechaGeneracion,
])

{{-- ── ESTADÍSTICAS GLOBALES ── --}}
@php
    $totalEventos  = $historiales->count();
    $creados       = $historiales->where('tipo_evento', 'creado')->count();
    $actualizados  = $historiales->where('tipo_evento', 'actualizado')->count();
    $eliminados    = $historiales->where('tipo_evento', 'eliminado')->count();
    $equiposUnicos = $historiales->pluck('inventario_equipo_id')->unique()->count();
    $totalCampos   = $historiales->sum(fn($h) => count($h->cambios ?? []));
@endphp

<div class="stats-bar">
    <div class="stat">
        <span class="stat-num">{{ $totalEventos }}</span>
        <span class="stat-lbl">Total eventos</span>
    </div>
    <div class="stat">
        <span class="stat-num">{{ $equiposUnicos }}</span>
        <span class="stat-lbl">Equipos distintos</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#166534">{{ $creados }}</span>
        <span class="stat-lbl">Registros creados</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#1D4ED8">{{ $actualizados }}</span>
        <span class="stat-lbl">Actualizaciones</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#991B1B">{{ $eliminados }}</span>
        <span class="stat-lbl">Eliminaciones</span>
    </div>
    <div class="stat">
        <span class="stat-num">{{ $totalCampos }}</span>
        <span class="stat-lbl">Campos modificados</span>
    </div>
</div>

{{-- ── TABLA GENERAL ── --}}
<div class="seccion-titulo-azul">REGISTRO DE EVENTOS (del más reciente al más antiguo)</div>

@forelse($historiales as $historial)
    @php
        $equipo = $historial->inventarioEquipo;
        $badgeClass = 'badge-' . ($historial->tipo_evento ?? 'gray');
        $tipoLabel = match($historial->tipo_evento) {
            'creado'      => 'Creado',
            'actualizado' => 'Actualizado',
            'eliminado'   => 'Eliminado',
            default       => ucfirst($historial->tipo_evento),
        };
        $cambios = $historial->cambios ?? [];
    @endphp

    {{-- Fila del equipo --}}
    <table class="tabla-datos" style="margin-bottom:0">
        <thead>
            <tr>
                <th style="width:14%">Fecha y Hora</th>
                <th style="width:9%">Evento</th>
                <th style="width:14%">No. Inventario</th>
                <th style="width:28%">Equipo</th>
                <th style="width:20%">Área</th>
                <th style="width:15%">Usuario</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $historial->created_at->format('d/m/Y H:i:s') }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $tipoLabel }}</span></td>
                <td>{{ $equipo?->numero_inventario ?? '—' }}</td>
                <td>{{ $equipo?->equipo ?? '(eliminado)' }}</td>
                <td>{{ $equipo?->area ?? '—' }}</td>
                <td>{{ $historial->usuario_nombre ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Detalle de cambios (si hay) --}}
    @if(!empty($cambios))
        <table class="cambios-table" style="margin-left:12px; width:calc(100% - 12px); margin-bottom:2px;">
            <thead>
                <tr>
                    <th style="width:28%">Campo modificado</th>
                    <th style="width:36%">Valor anterior</th>
                    <th style="width:36%">Valor nuevo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cambios as $cambio)
                    <tr>
                        <td class="td-campo">{{ $cambio['etiqueta'] ?? $cambio['campo'] }}</td>
                        <td class="td-anterior">{{ $cambio['anterior'] ?? '(vacío)' }}</td>
                        <td class="td-nuevo">{{ $cambio['nuevo'] ?? '(vacío)' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!$loop->last)<hr class="sep">@endif
@empty
    <div class="vacio">No se encontraron eventos en el período seleccionado.</div>
@endforelse

@include('pdf.partials.plantilla-close')
