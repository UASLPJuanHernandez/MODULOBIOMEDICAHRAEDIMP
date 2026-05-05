@php
    $historial = $historial->sortByDesc('created_at');
@endphp

@if($historial->isEmpty())
    <div style="display:flex;align-items:center;justify-content:center;height:120px;color:#9ca3af;">
        <p style="font-size:14px;">Sin actividad registrada</p>
    </div>
@else
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Fecha</th>
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;">Ingeniero</th>
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;">Equipo / Descripción</th>
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;">Área</th>
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;">Estado</th>
                    <th style="padding:10px 14px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Concretado el</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historial as $i => $r)
                    @php
                        $estadoConfig = match($r->estado_actividad) {
                            'concretado'   => ['label' => 'Concretado',              'bg' => '#dcfce7', 'color' => '#15803d'],
                            'espera_firma' => ['label' => 'En espera de firma',       'bg' => '#fef9c3', 'color' => '#854d0e'],
                            default        => ['label' => 'En espera de ser enviado', 'bg' => '#f3f4f6', 'color' => '#374151'],
                        };
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;{{ $i % 2 === 1 ? 'background:#fafafa;' : '' }}">
                        <td style="padding:10px 14px;color:#6b7280;white-space:nowrap;">
                            {{ $r->created_at->format('d/m/Y') }}
                        </td>
                        <td style="padding:10px 14px;font-weight:600;color:#111827;white-space:nowrap;">
                            {{ $r->responsable ?? '—' }}
                        </td>
                        <td style="padding:10px 14px;color:#374151;max-width:240px;">
                            {{ Str::limit($r->titulo ?? $r->descripcion, 48) }}
                        </td>
                        <td style="padding:10px 14px;color:#6b7280;white-space:nowrap;">
                            {{ $r->ubicacion ?? '—' }}
                        </td>
                        <td style="padding:10px 14px;white-space:nowrap;">
                            <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;background:{{ $estadoConfig['bg'] }};color:{{ $estadoConfig['color'] }};">
                                {{ $estadoConfig['label'] }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;color:#6b7280;white-space:nowrap;">
                            {{ $r->concretado_at ? \Carbon\Carbon::parse($r->concretado_at)->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
