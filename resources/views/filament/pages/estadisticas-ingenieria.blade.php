<x-filament-panels::page>
@php
    $d = $this->getEstadisticasData();
    $tiempoDias    = ($d['tiempoPromedioHoras'] ?? 0) > 0 ? round($d['tiempoPromedioHoras'] / 24, 1) : 0;
    $tiempoMtoDias = ($d['tiempoMtoHoras'] ?? 0) > 0 ? round($d['tiempoMtoHoras'] / 24, 1) : 0;
    $pctContrato   = $d['totalEquipos'] > 0 ? round(($d['conContrato'] / $d['totalEquipos']) * 100) : 0;
    $totalCalidad  = array_sum(array_values($d['calidad']));
    $totalAct      = array_sum(array_values($d['reportesPorIngenieroActivos']));
    $totalTodo     = array_sum(array_values($d['reportesPorIngenieroTotal']));

    // Datos comparativos por ingeniero para las gráficas
    $ingNombres    = array_column($d['ingenierosMetrics'], 'nombre');
    $ingTotal      = array_column($d['ingenierosMetrics'], 'total');
    $ingConcretados = array_column($d['ingenierosMetrics'], 'concretados');
    $ingBitacoras  = array_column($d['ingenierosMetrics'], 'bitacoras');
    $ingActivos    = array_column($d['ingenierosMetrics'], 'activos');
    $ingEstesMes   = array_column($d['ingenierosMetrics'], 'este_mes');
    $ingEnvioH     = array_map(fn($v) => $v ?? 0, array_column($d['ingenierosMetrics'], 'tiempo_envio_h'));

    // Formato legible de horas
    $fmtHoras = function($h) {
        if ($h === null) return '—';
        if ($h < 1)   return round($h * 60) . ' min';
        if ($h < 24)  return round($h, 1) . ' h';
        return round($h / 24, 1) . ' días';
    };
    $envioAreaLabel = $fmtHoras($d['tiempoEnvioAreaHoras']);

    $colores = ['#3b82f6','#22c55e','#f59e0b','#8b5cf6','#ef4444','#ec4899','#14b8a6'];
@endphp

<style>[x-cloak]{display:none!important}</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div x-data="{ tab: 'personal' }" style="font-family:inherit;">

    {{-- HEADER --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;">
        <div>
            <h1 style="font-size:21px;font-weight:700;color:#111827;margin:0 0 4px;">Estadísticas — Ingeniería Biomédica</h1>
            <p style="font-size:12px;color:#9ca3af;margin:0;">Datos al {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ \App\Filament\Resources\IngenierResource::getUrl() }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:500;color:#374151;text-decoration:none;">
            ← Volver a Ingenieros
        </a>
    </div>

    {{-- TABS ─ segmented control con más separación --}}
    <div style="background:#f1f5f9;border-radius:16px;padding:6px;display:inline-flex;gap:4px;margin-bottom:32px;">
        @foreach([
            ['personal', 'Personal'],
            ['resumen',  'Resumen general'],
            ['equipos',  'Equipos'],
            ['reportes', 'Reportes y Calidad'],
        ] as [$key, $label])
        <button
            type="button"
            @click="tab = '{{ $key }}'"
            :style="tab === '{{ $key }}'
                ? 'background:#fff;color:#111827;box-shadow:0 1px 5px rgba(0,0,0,.13);font-weight:600;'
                : 'background:transparent;color:#64748b;'"
            style="padding:11px 36px;border-radius:11px;border:none;font-size:14px;cursor:pointer;transition:all .18s;white-space:nowrap;"
        >{{ $label }}</button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         TAB: PERSONAL
    ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'personal'" x-cloak>

        {{-- KPIs generales del equipo --}}
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:24px;">
            @foreach([
                ['Ingenieros activos',       $d['ingenierosActivos'],   '#3b82f6', 'de '.$d['totalIngenieros'].' registrados'],
                ['Reportes activos ahora',   $totalAct,                 '#f59e0b', 'pendientes + en curso'],
                ['Total asignados',          $totalTodo,                '#8b5cf6', 'histórico completo'],
                ['Promedio por ingeniero',   $d['ingenierosActivos'] > 0 ? round($totalTodo / $d['ingenierosActivos'], 1) : 0, '#22c55e', 'reportes asignados c/u'],
                ['Tiempo prom. p. firmar',   $envioAreaLabel,           '#f97316', 'desde que llega hasta enviar a firma'],
            ] as [$t, $v, $c, $sub])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 20px;">
                <div style="font-size:28px;font-weight:800;color:{{ $c }};line-height:1;margin-bottom:6px;">{{ $v }}</div>
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:3px;">{{ $t }}</div>
                <div style="font-size:11px;color:#94a3b8;">{{ $sub }}</div>
            </div>
            @endforeach
        </div>

        {{-- Tarjetas individuales por ingeniero --}}
        @if(!empty($d['ingenierosMetrics']))
        <div style="margin-bottom:10px;">
            <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 16px;">Perfil individual</h3>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:28px;">
            @foreach($d['ingenierosMetrics'] as $idx => $ing)
            @php
                $color     = $colores[$idx % count($colores)];
                $pct       = $ing['tasa_concrecion'];
                $barColor  = $pct >= 70 ? '#22c55e' : ($pct >= 40 ? '#f59e0b' : '#ef4444');
                $carga     = $ing['activos'] === 0 ? ['Disponible','#22c55e','#dcfce7'] : ($ing['activos'] <= 2 ? ['Carga normal','#f59e0b','#fef3c7'] : ['Alta carga','#ef4444','#fee2e2']);
                $iniciales = collect(explode(' ', $ing['nombre']))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
            @endphp
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                {{-- Cabecera con color --}}
                <div style="background:{{ $color }}15;padding:18px 20px 14px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:14px;">
                    @if(!empty($ing['foto']))
                        <img src="{{ $ing['foto'] }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid {{ $color }}40;flex-shrink:0;" />
                    @else
                        <div style="width:48px;height:48px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0;">{{ $iniciales }}</div>
                    @endif
                    <div style="min-width:0;">
                        <div style="font-size:15px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ing['nombre'] }}</div>
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;">{{ $ing['cargo'] }}</div>
                    </div>
                    <div style="margin-left:auto;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;background:{{ $carga[2] }};color:{{ $carga[0] === 'Disponible' ? '#166534' : ($carga[0] === 'Carga normal' ? '#92400e' : '#991b1b') }};">{{ $carga[0] }}</span>
                    </div>
                </div>
                {{-- Métricas en grid --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;padding:0;">
                    @foreach([
                        ['Total',       $ing['total'],       '#374151'],
                        ['Activos',     $ing['activos'],     '#f59e0b'],
                        ['Este mes',    $ing['este_mes'],    '#3b82f6'],
                        ['Concretados', $ing['concretados'], '#22c55e'],
                        ['Bitácoras',   $ing['bitacoras'],   '#8b5cf6'],
                        ['Completados', $ing['completados'], '#6b7280'],
                    ] as [$mt, $mv, $mc])
                    <div style="padding:13px 14px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;text-align:center;">
                        <div style="font-size:20px;font-weight:700;color:{{ $mc }};line-height:1;">{{ $mv }}</div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:3px;text-transform:uppercase;letter-spacing:.03em;">{{ $mt }}</div>
                    </div>
                    @endforeach
                </div>
                {{-- Barra concreción + info extra --}}
                <div style="padding:14px 20px;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:6px;">
                        <span style="font-weight:600;">Tasa de concreción</span>
                        <span style="font-weight:700;color:{{ $barColor }};">{{ $pct }}%</span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:999px;height:8px;overflow:hidden;">
                        <div style="background:{{ $barColor }};height:100%;width:{{ $pct }}%;border-radius:999px;transition:width .4s;"></div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;margin-top:10px;font-size:11px;color:#94a3b8;gap:4px;">
                        <span>Último: {{ $ing['ultimo_reporte'] ?? 'Sin asignaciones' }}</span>
                        <span style="display:flex;gap:10px;">
                            @if($ing['tiempo_envio_h'] !== null)
                            <span>P. firma: <strong style="color:#f97316;">{{ $fmtHoras($ing['tiempo_envio_h']) }}</strong></span>
                            @endif
                            @if($ing['tiempo_prom_dias'])
                            <span>Resolución: <strong style="color:#374151;">{{ $ing['tiempo_prom_dias'] }}d</strong></span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Gráficas comparativas --}}
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 16px;">Comparativas</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Total asignados vs Concretados</p>
                <div style="position:relative;height:{{ max(140, count($ingNombres) * 42) }}px;">
                    <canvas id="ch-per-comparativa"></canvas>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Reportes activos vs Bitácoras</p>
                <div style="position:relative;height:{{ max(140, count($ingNombres) * 42) }}px;">
                    <canvas id="ch-per-activos-bits"></canvas>
                </div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px;">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Actividad este mes</p>
                <div style="position:relative;height:{{ max(140, count($ingNombres) * 42) }}px;">
                    <canvas id="ch-per-mes"></canvas>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 4px;">Tasa de concreción (%)</p>
                <p style="font-size:11px;color:#94a3b8;margin:0 0 14px;">Verde ≥70% · Amarillo ≥40% · Rojo &lt;40%</p>
                <div style="position:relative;height:{{ max(140, count($ingNombres) * 42) }}px;">
                    <canvas id="ch-per-tasa"></canvas>
                </div>
            </div>
        </div>

        {{-- Gráfica tiempo para enviar a firma --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;margin-bottom:28px;">
            <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 4px;">Tiempo promedio para enviar a firma (horas)</p>
            <p style="font-size:11px;color:#94a3b8;margin:0 0 14px;">Desde que llega el reporte hasta que se genera la bitácora. Valores más bajos indican mayor agilidad en la atención.</p>
            <div style="position:relative;height:{{ max(80, count($ingNombres) * 42) }}px;">
                <canvas id="ch-per-envio"></canvas>
            </div>
        </div>

        {{-- Tabla resumen comparativo --}}
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 16px;">Resumen comparativo</h3>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:13px 18px;text-align:left;font-weight:600;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Ingeniero</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Total</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#f59e0b;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Activos</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#22c55e;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Concretados</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#3b82f6;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Este mes</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#8b5cf6;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Bitácoras</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#f97316;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">T. p. firma</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Prom. resolución</th>
                        <th style="padding:13px 18px;text-align:center;font-weight:600;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">% Concreción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(collect($d['ingenierosMetrics'])->sortBy('nombre')->values() as $ing)
                    @php
                        $pctIng   = $ing['tasa_concrecion'];
                        $barColor = $pctIng >= 70 ? '#22c55e' : ($pctIng >= 40 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="padding:14px 18px;">
                            <div style="font-weight:600;color:#111827;">{{ $ing['nombre'] }}</div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:1px;">{{ $ing['cargo'] }}</div>
                        </td>
                        <td style="padding:14px 18px;text-align:center;font-weight:600;color:#374151;">{{ $ing['total'] }}</td>
                        <td style="padding:14px 18px;text-align:center;">
                            @if($ing['activos'] > 0)<span style="background:#fef3c7;color:#92400e;font-size:12px;font-weight:600;padding:2px 10px;border-radius:999px;">{{ $ing['activos'] }}</span>
                            @else<span style="color:#94a3b8;">0</span>@endif
                        </td>
                        <td style="padding:14px 18px;text-align:center;">
                            <span style="background:#dcfce7;color:#166534;font-size:12px;font-weight:600;padding:2px 10px;border-radius:999px;">{{ $ing['concretados'] }}</span>
                        </td>
                        <td style="padding:14px 18px;text-align:center;font-weight:600;color:#3b82f6;">{{ $ing['este_mes'] }}</td>
                        <td style="padding:14px 18px;text-align:center;font-weight:600;color:#8b5cf6;">{{ $ing['bitacoras'] }}</td>
                        <td style="padding:14px 18px;text-align:center;color:#f97316;font-size:12px;font-weight:600;">
                            {{ $fmtHoras($ing['tiempo_envio_h']) }}
                        </td>
                        <td style="padding:14px 18px;text-align:center;color:#6b7280;font-size:12px;">
                            {{ $ing['tiempo_prom_dias'] ? $ing['tiempo_prom_dias'].' días' : '—' }}
                        </td>
                        <td style="padding:14px 18px;">
                            <div style="display:flex;align-items:center;gap:8px;justify-content:center;">
                                <div style="width:64px;background:#f1f5f9;border-radius:999px;height:7px;flex-shrink:0;">
                                    <div style="background:{{ $barColor }};height:100%;width:{{ $pctIng }}%;border-radius:999px;"></div>
                                </div>
                                <span style="font-size:12px;font-weight:700;color:{{ $barColor }};min-width:30px;">{{ $pctIng }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align:center;padding:70px;color:#94a3b8;">No hay ingenieros activos registrados.</div>
        @endif
    </div>{{-- /personal --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         TAB: RESUMEN
    ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'resumen'" x-cloak>
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:14px;">
            @foreach([
                ['Total equipos',     $d['totalEquipos'],                             '#3b82f6'],
                ['Fuera de servicio', $d['estatusAgrupado']['Fuera de Servicio'],     '#ef4444'],
                ['Reportes activos',  $d['reportesPendiente']+$d['reportesEnCurso'],  '#f59e0b'],
                ['% Concreción',      $d['tasaConcrecion'].'%',                       '#22c55e'],
                ['% Satisfacción',    $d['tasaSatisfaccion'].'%',                     '#8b5cf6'],
            ] as [$t,$v,$c])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;">
                <div style="font-size:28px;font-weight:800;color:{{ $c }};line-height:1;margin-bottom:6px;">{{ $v }}</div>
                <div style="font-size:12px;color:#6b7280;">{{ $t }}</div>
            </div>
            @endforeach
        </div>
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:26px;">
            @foreach([
                ['Total reportes',  $d['totalReportes'],         '#6b7280'],
                ['Concretados',     $d['reportesConcretados'],   '#22c55e'],
                ['Total bitácoras', $d['totalBitacoras'],        '#3b82f6'],
                ['Tiempo prom.',    $tiempoDias>0 ? $tiempoDias.' días' : '—', '#f59e0b'],
                ['Ingenieros',      $d['ingenierosActivos'].'/'.$d['totalIngenieros'], '#8b5cf6'],
            ] as [$t,$v,$c])
            <div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;padding:14px;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:{{ $c }};line-height:1;margin-bottom:5px;">{{ $v }}</div>
                <div style="font-size:11px;color:#94a3b8;">{{ $t }}</div>
            </div>
            @endforeach
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            @foreach([['ch-res-estatus','Estatus de equipos'],['ch-res-estado-rep','Reportes por estado'],['ch-res-calidad','Calidad de atenciones']] as [$id,$tit])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;text-align:center;">{{ $tit }}</p>
                <div style="position:relative;height:215px;"><canvas id="{{ $id }}"></canvas></div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         TAB: EQUIPOS
    ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'equipos'" x-cloak>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:16px;">
            @foreach([
                ['Con contrato',  $d['conContrato'], '#22c55e'],
                ['Sin contrato',  $d['sinContrato'], '#ef4444'],
                ['Con garantía',  $d['conGarantia'], '#3b82f6'],
                ['Fin vida útil', $d['finVidaUtil'], '#ef4444'],
                ['MP próx. 30 d', $d['proximosMp'],  '#f59e0b'],
                ['MP vencidos',   $d['mpVencidos'],  '#dc2626'],
            ] as [$t,$v,$c])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;text-align:center;">
                <div style="font-size:26px;font-weight:800;color:{{ $c }};line-height:1;margin-bottom:6px;">{{ $v }}</div>
                <div style="font-size:11px;color:#6b7280;line-height:1.4;">{{ $t }}</div>
            </div>
            @endforeach
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-size:13px;font-weight:600;color:#374151;">Cobertura de contratos de mantenimiento</span>
                <span style="font-size:15px;font-weight:700;color:#22c55e;">{{ $pctContrato }}%</span>
            </div>
            <div style="background:#f1f5f9;border-radius:999px;height:12px;overflow:hidden;">
                <div style="background:linear-gradient(90deg,#22c55e,#86efac);height:100%;width:{{ $pctContrato }}%;border-radius:999px;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:7px;font-size:12px;color:#94a3b8;">
                <span>{{ $d['conContrato'] }} con contrato</span>
                <span>{{ $d['totalEquipos'] }} total</span>
                <span>{{ $d['sinContrato'] }} sin contrato</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            @foreach([['ch-eq-cond','Condiciones de los equipos'],['ch-eq-prop','Tipo de propiedad']] as [$id,$tit])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;text-align:center;">{{ $tit }}</p>
                <div style="position:relative;height:225px;"><canvas id="{{ $id }}"></canvas></div>
            </div>
            @endforeach
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
            <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Top 10 áreas por cantidad de equipos</p>
            <div style="position:relative;height:{{ max(200, count($d['equiposPorArea']) * 32) }}px;">
                <canvas id="ch-eq-areas"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         TAB: REPORTES Y CALIDAD
    ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'reportes'" x-cloak>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:16px;">
            @foreach([
                ['Total',        $d['totalReportes'],      '#6b7280'],
                ['Pendientes',   $d['reportesPendiente'],  '#f59e0b'],
                ['En curso',     $d['reportesEnCurso'],    '#3b82f6'],
                ['Concretados',  $d['reportesConcretados'],'#22c55e'],
                ['% Concreción', $d['tasaConcrecion'].'%', '#8b5cf6'],
                ['Tiempo prom.', $tiempoDias>0 ? $tiempoDias.' d' : '—', '#f97316'],
            ] as [$t,$v,$c])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;text-align:center;">
                <div style="font-size:26px;font-weight:800;color:{{ $c }};line-height:1;margin-bottom:6px;">{{ $v }}</div>
                <div style="font-size:11px;color:#6b7280;">{{ $t }}</div>
            </div>
            @endforeach
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px;">
            <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Tendencia de reportes — últimos 6 meses</p>
            <div style="position:relative;height:175px;"><canvas id="ch-rep-tendencia"></canvas></div>
        </div>
        <div style="display:grid;grid-template-columns:250px 1fr;gap:16px;margin-bottom:16px;">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;text-align:center;">Calidad de atención</p>
                <div style="position:relative;height:180px;"><canvas id="ch-rep-calidad"></canvas></div>
                @if($totalCalidad > 0)
                <div style="margin-top:18px;display:flex;flex-direction:column;gap:9px;">
                    @foreach([['Satisfactoria','satisfactoria','#22c55e'],['Parcial','parcial','#f59e0b'],['No satisfactoria','no_satisfactoria','#ef4444']] as [$lbl,$key,$clr])
                    @php $pct = round(($d['calidad'][$key] / $totalCalidad) * 100); @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:4px;">
                            <span>{{ $lbl }}</span><span style="font-weight:700;">{{ $pct }}%</span>
                        </div>
                        <div style="background:#f1f5f9;border-radius:999px;height:7px;">
                            <div style="background:{{ $clr }};height:100%;width:{{ $pct }}%;border-radius:999px;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0 0 16px;">Top 10 áreas con más reportes</p>
                @if(!empty($d['reportesPorAreaDep']))
                <div style="position:relative;height:{{ max(200, count($d['reportesPorAreaDep']) * 30) }}px;">
                    <canvas id="ch-rep-areas"></canvas>
                </div>
                @else
                <p style="font-size:12px;color:#94a3b8;text-align:center;padding:50px 0;">Sin datos de área departamental</p>
                @endif
            </div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <p style="font-size:13px;font-weight:600;color:#374151;margin:0;">Órdenes de servicio / Mantenimientos</p>
                <span style="font-size:12px;color:#94a3b8;background:#f8fafc;padding:3px 12px;border-radius:999px;border:1px solid #f1f5f9;">Total: {{ $d['totalMantenimientos'] }}</span>
            </div>
            @if($d['totalMantenimientos'] > 0)
            <div style="display:grid;grid-template-columns:180px 1fr;gap:20px;align-items:center;">
                <div style="position:relative;height:170px;"><canvas id="ch-rep-mtos"></canvas></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    @foreach([['Pendientes',$d['mtosPendientes'],'#f59e0b'],['Aceptados',$d['mtosAceptados'],'#3b82f6'],['Completados',$d['mtosCompletados'],'#22c55e'],['Rechazados',$d['mtosRechazados'],'#ef4444']] as [$l,$v,$c])
                    <div style="background:#f8fafc;border-radius:10px;padding:16px;text-align:center;">
                        <div style="font-size:26px;font-weight:700;color:{{ $c }};">{{ $v }}</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">{{ $l }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @if($tiempoMtoDias > 0)
            <p style="margin:14px 0 0;padding-top:14px;border-top:1px solid #f1f5f9;font-size:12px;color:#94a3b8;text-align:center;">
                Tiempo promedio de resolución: <strong style="color:#374151;">{{ $tiempoMtoDias }} días</strong>
            </p>
            @endif
            @else
            <p style="font-size:12px;color:#94a3b8;text-align:center;padding:30px 0;">Sin órdenes de servicio registradas</p>
            @endif
        </div>
    </div>

</div>{{-- /x-data --}}

{{-- ═════════════════════ CHART INIT ══════════════════════════════════════ --}}
<script>
(function waitForChart() {
    if (typeof Chart === 'undefined') { setTimeout(waitForChart, 80); return; }

    function donut(id, labels, data, colors) {
        var el = document.getElementById(id); if (!el) return;
        return new Chart(el, { type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
        });
    }
    function hbar(id, labels, datasets) {
        var el = document.getElementById(id); if (!el) return;
        return new Chart(el, { type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: datasets.length > 1, labels: { boxWidth: 10, padding: 12, font: { size: 11 } } } },
                scales: { x: { grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });
    }
    function vbar(id, labels, data, color) {
        var el = document.getElementById(id); if (!el) return;
        return new Chart(el, { type: 'bar',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: color, borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
        });
    }
    function line(id, labels, data) {
        var el = document.getElementById(id); if (!el) return;
        return new Chart(el, { type: 'line',
            data: { labels: labels, datasets: [{ data: data, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', tension: 0.4, fill: true, pointBackgroundColor: '#3b82f6', pointRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, ticks: { precision: 0 } } } }
        });
    }

    var charts = {};

    // ── Personal ──────────────────────────────────────────────────────────
    var ingNombres    = {!! json_encode($ingNombres) !!};
    var ingTotal      = {!! json_encode($ingTotal) !!};
    var ingConcretados = {!! json_encode($ingConcretados) !!};
    var ingBitacoras  = {!! json_encode($ingBitacoras) !!};
    var ingActivos    = {!! json_encode($ingActivos) !!};
    var ingEstesMes   = {!! json_encode($ingEstesMes) !!};
    var ingTasas      = {!! json_encode(array_column($d['ingenierosMetrics'], 'tasa_concrecion')) !!};
    var ingEnvioH     = {!! json_encode($ingEnvioH) !!};

    charts['ch-per-comparativa'] = hbar('ch-per-comparativa', ingNombres, [
        { label: 'Total asignados', data: ingTotal,       backgroundColor: 'rgba(59,130,246,0.75)', borderRadius: 4, borderSkipped: false },
        { label: 'Concretados',     data: ingConcretados, backgroundColor: 'rgba(34,197,94,0.75)',  borderRadius: 4, borderSkipped: false }
    ]);
    charts['ch-per-activos-bits'] = hbar('ch-per-activos-bits', ingNombres, [
        { label: 'Activos',   data: ingActivos,   backgroundColor: 'rgba(239,68,68,0.75)',   borderRadius: 4, borderSkipped: false },
        { label: 'Bitácoras', data: ingBitacoras, backgroundColor: 'rgba(139,92,246,0.75)',  borderRadius: 4, borderSkipped: false }
    ]);
    charts['ch-per-mes'] = hbar('ch-per-mes', ingNombres, [
        { label: 'Este mes', data: ingEstesMes, backgroundColor: 'rgba(59,130,246,0.75)', borderRadius: 4, borderSkipped: false }
    ]);
    charts['ch-per-tasa'] = hbar('ch-per-tasa', ingNombres, [{
        data: ingTasas,
        backgroundColor: ingTasas.map(function(v) { return v >= 70 ? 'rgba(34,197,94,0.8)' : (v >= 40 ? 'rgba(245,158,11,0.8)' : 'rgba(239,68,68,0.8)'); }),
        borderRadius: 4, borderSkipped: false
    }]);
    charts['ch-per-envio'] = hbar('ch-per-envio', ingNombres, [{
        label: 'Horas hasta enviar a firma',
        data: ingEnvioH,
        backgroundColor: ingEnvioH.map(function(v) { return v === 0 ? 'rgba(203,213,225,0.6)' : 'rgba(249,115,22,0.75)'; }),
        borderRadius: 4, borderSkipped: false
    }]);

    // ── Resumen ────────────────────────────────────────────────────────────
    charts['ch-res-estatus']    = donut('ch-res-estatus',    {!! json_encode(array_keys($d['estatusAgrupado'])) !!},    {!! json_encode(array_values($d['estatusAgrupado'])) !!},    ['#22c55e','#f59e0b','#ef4444','#9ca3af']);
    charts['ch-res-estado-rep'] = donut('ch-res-estado-rep', ['Pendiente','En curso','Completado','Concretado'], [{!! $d['reportesPendiente'] !!},{!! $d['reportesEnCurso'] !!},{!! $d['reportesCompletados'] !!},{!! $d['reportesConcretados'] !!}], ['#f59e0b','#3b82f6','#8b5cf6','#22c55e']);
    charts['ch-res-calidad']    = donut('ch-res-calidad',    ['Satisfactoria','Parcial','No satisfactoria'], [{!! $d['calidad']['satisfactoria'] !!},{!! $d['calidad']['parcial'] !!},{!! $d['calidad']['no_satisfactoria'] !!}], ['#22c55e','#f59e0b','#ef4444']);

    // ── Equipos ────────────────────────────────────────────────────────────
    charts['ch-eq-cond']  = donut('ch-eq-cond',  {!! json_encode(array_keys($d['condicionesEquipos'])) !!},   {!! json_encode(array_values($d['condicionesEquipos'])) !!},   ['#22c55e','#f59e0b','#ef4444','#3b82f6','#9ca3af','#ec4899','#a78bfa']);
    charts['ch-eq-prop']  = donut('ch-eq-prop',  {!! json_encode(array_keys($d['tipoPropiedadEquipos'])) !!}, {!! json_encode(array_values($d['tipoPropiedadEquipos'])) !!}, ['#3b82f6','#f59e0b','#22c55e','#ef4444','#8b5cf6','#ec4899']);
    charts['ch-eq-areas'] = hbar('ch-eq-areas',  {!! json_encode(array_keys($d['equiposPorArea'])) !!}, [{ data: {!! json_encode(array_values($d['equiposPorArea'])) !!}, backgroundColor: 'rgba(59,130,246,0.72)', borderRadius: 4, borderSkipped: false }]);

    // ── Reportes ───────────────────────────────────────────────────────────
    charts['ch-rep-tendencia'] = line('ch-rep-tendencia', {!! json_encode($d['mesesLabels']) !!}, {!! json_encode($d['mesesData']) !!});
    charts['ch-rep-calidad']   = donut('ch-rep-calidad',  ['Satisfactoria','Parcial','No satisfactoria'], [{!! $d['calidad']['satisfactoria'] !!},{!! $d['calidad']['parcial'] !!},{!! $d['calidad']['no_satisfactoria'] !!}], ['#22c55e','#f59e0b','#ef4444']);
    charts['ch-rep-areas']     = hbar('ch-rep-areas',     {!! json_encode(array_keys($d['reportesPorAreaDep'])) !!}, [{ data: {!! json_encode(array_values($d['reportesPorAreaDep'])) !!}, backgroundColor: 'rgba(139,92,246,0.72)', borderRadius: 4, borderSkipped: false }]);
    charts['ch-rep-mtos']      = donut('ch-rep-mtos',     ['Pendiente','Aceptado','Completado','Rechazado'], [{!! $d['mtosPendientes'] !!},{!! $d['mtosAceptados'] !!},{!! $d['mtosCompletados'] !!},{!! $d['mtosRechazados'] !!}], ['#f59e0b','#3b82f6','#22c55e','#ef4444']);

    // Resize al cambiar pestaña
    document.querySelectorAll('button[\\@click^="tab"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setTimeout(function() {
                Object.values(charts).forEach(function(c) { if (c) try { c.resize(); } catch(e){} });
            }, 60);
        });
    });
})();
</script>

</x-filament-panels::page>
