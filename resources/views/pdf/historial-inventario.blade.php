@include('pdf.partials.plantilla', [
    'titulo'    => 'HISTORIAL DE CAMBIOS — INVENTARIO DE EQUIPOS BIOMÉDICOS',
    'subtitulo' => ($equipo->numero_inventario ? 'No. Inventario: ' . $equipo->numero_inventario . '  ·  ' : '') . ($equipo->equipo ?? ''),
    'fecha'     => $fechaGeneracion,
])

{{-- ── FICHA DEL EQUIPO ── --}}
<div class="ficha-card">
    <table>
        <tr>
            <td class="lbl">No. Inventario:</td>
            <td>{{ $equipo->numero_inventario ?? '—' }}</td>
            <td class="lbl">Equipo:</td>
            <td><strong>{{ $equipo->equipo ?? '—' }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Marca / Modelo:</td>
            <td>{{ $equipo->marca ?? '—' }} / {{ $equipo->modelo ?? '—' }}</td>
            <td class="lbl">No. de Serie:</td>
            <td>{{ $equipo->numero_serie ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Área:</td>
            <td>{{ $equipo->area ?? '—' }}</td>
            <td class="lbl">Estatus:</td>
            <td>{{ $equipo->estatus ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Unidad Médica:</td>
            <td colspan="3">{{ $equipo->unidad_medica ?? '—' }}</td>
        </tr>
    </table>
</div>

{{-- ── ESTADÍSTICAS ── --}}
@php
    $totalEventos    = $historiales->count();
    $totalCreados    = $historiales->where('tipo_evento', 'creado')->count();
    $totalActualiz   = $historiales->where('tipo_evento', 'actualizado')->count();
    $totalEliminados = $historiales->where('tipo_evento', 'eliminado')->count();
    $totalCambios    = $historiales->sum(fn($h) => count($h->cambios ?? []));
@endphp
<div class="stats-bar">
    <div class="stat">
        <span class="stat-num">{{ $totalEventos }}</span>
        <span class="stat-lbl">Total eventos</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#166534">{{ $totalCreados }}</span>
        <span class="stat-lbl">Creaciones</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#1D4ED8">{{ $totalActualiz }}</span>
        <span class="stat-lbl">Actualizaciones</span>
    </div>
    <div class="stat">
        <span class="stat-num" style="color:#991B1B">{{ $totalEliminados }}</span>
        <span class="stat-lbl">Eliminaciones</span>
    </div>
    <div class="stat">
        <span class="stat-num">{{ $totalCambios }}</span>
        <span class="stat-lbl">Campos modificados</span>
    </div>
</div>

{{-- ── LÍNEA DE TIEMPO ── --}}
<div class="seccion-titulo-azul">REGISTRO DE EVENTOS (del más reciente al más antiguo)</div>

@forelse($historiales as $historial)
    @php
        $dotClass  = 'dot-'   . ($historial->tipo_evento ?? 'gray');
        $badgeClass = 'badge-' . ($historial->tipo_evento ?? 'gray');
        $tipoLabel = match($historial->tipo_evento) {
            'creado'      => 'Creado',
            'actualizado' => 'Actualizado',
            'eliminado'   => 'Eliminado',
            default       => ucfirst($historial->tipo_evento),
        };
    @endphp

    <div class="event">
        <div class="event-dot {{ $dotClass }}"></div>

        <div class="event-header">
            <div class="event-meta">
                <span class="badge {{ $badgeClass }}">{{ $tipoLabel }}</span>
                <div class="event-desc">{{ $historial->descripcion }}</div>
            </div>
            <div class="event-time">
                {{ $historial->created_at->format('d/m/Y H:i:s') }}<br>
                <strong>{{ $historial->usuario_nombre ?? 'Desconocido' }}</strong>
                @if($historial->ip_address)
                    <br><span style="font-size:7.5px;color:#9CA3AF">{{ $historial->ip_address }}</span>
                @endif
            </div>
        </div>

        @if(!empty($historial->cambios))
            <table class="cambios-table">
                <thead>
                    <tr>
                        <th>Campo</th>
                        <th>Valor anterior</th>
                        <th>Valor nuevo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historial->cambios as $cambio)
                        <tr>
                            <td class="td-campo">{{ $cambio['etiqueta'] ?? $cambio['campo'] }}</td>
                            <td class="td-anterior">{{ $cambio['anterior'] ?? '(vacío)' }}</td>
                            <td class="td-nuevo">{{ $cambio['nuevo'] ?? '(vacío)' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if(!$loop->last)<hr class="sep">@endif
@empty
    <div class="vacio">No se encontraron eventos en el historial de este equipo.</div>
@endforelse

@include('pdf.partials.plantilla-close')
