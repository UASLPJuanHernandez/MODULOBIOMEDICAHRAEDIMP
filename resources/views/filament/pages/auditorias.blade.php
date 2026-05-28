<x-filament-panels::page>
<div x-data="{ tab: 'actividad' }" class="space-y-5">

    {{-- ── Tarjetas de resumen ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $cards = [
                ['label'=>'Firmas hoy',       'value'=>$firmasHoy,       'color'=>'blue'],
                ['label'=>'Firmas esta semana','value'=>$firmasSem,       'color'=>'indigo'],
                ['label'=>'Vales este mes',    'value'=>$valesMes,        'color'=>'violet'],
                ['label'=>'Vales en proceso',  'value'=>$valesEnProceso,  'color'=>'amber'],
                ['label'=>'Usuarios activos',  'value'=>$usuariosActivos, 'color'=>'green'],
                ['label'=>'Pendientes aprob.', 'value'=>$pendientes,      'color'=>'red'],
            ];
            $cm = [
                'blue'   => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300',
                'indigo' => 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300',
                'violet' => 'bg-violet-50 dark:bg-violet-950 text-violet-700 dark:text-violet-300',
                'amber'  => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300',
                'green'  => 'bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-300',
                'red'    => 'bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300',
            ];
        @endphp
        @foreach($cards as $card)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 {{ $cm[$card['color']] }} p-4">
            <p class="text-xs font-semibold uppercase tracking-wide opacity-60 mb-1">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Barra de filtros ─────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
        <div class="flex flex-wrap gap-3 items-end">

            {{-- Búsqueda --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Buscar</label>
                <input wire:model.live.debounce.400ms="busqueda"
                       type="text" placeholder="Descripción, actor, IP…"
                       class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            {{-- Tipo --}}
            <div class="min-w-36">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tipo de evento</label>
                <select wire:model.live="filtroTipo"
                        class="w-full py-1.5 px-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <option value="acceso">Acceso</option>
                    <option value="firma">Firma</option>
                    <option value="equipo">Equipo</option>
                    <option value="reporte">Reporte</option>
                    <option value="usuario">Usuario</option>
                </select>
            </div>

            {{-- Fecha desde --}}
            <div class="min-w-40">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                <input wire:model.live="fechaDesde"
                       type="{{ $fechaDesde ? 'date' : 'text' }}"
                       placeholder="Escoger inicio"
                       onfocus="this.type='date'"
                       onblur="if(!this.value) this.type='text'"
                       class="w-full py-1.5 px-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Fecha hasta --}}
            <div class="min-w-40">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                <input wire:model.live="fechaHasta"
                       type="{{ $fechaHasta ? 'date' : 'text' }}"
                       placeholder="Escoger final"
                       onfocus="this.type='date'"
                       onblur="if(!this.value) this.type='text'"
                       class="w-full py-1.5 px-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Limpiar filtros --}}
            <div class="flex gap-2">
                <button wire:click="$set('busqueda',''); $set('filtroTipo',''); $set('fechaDesde',''); $set('fechaHasta','')"
                        class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Limpiar
                </button>
            </div>
        </div>

        @if($busqueda || $filtroTipo || $fechaDesde || $fechaHasta)
        <p class="mt-2 text-xs text-primary-600 dark:text-primary-400 font-medium">
            Filtros activos — mostrando resultados filtrados en las tabs Actividad e Historial Equipos.
        </p>
        @endif
    </div>

    {{-- ── Contenedor de tabs ───────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">

        {{-- Tab nav --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-x-auto">
            @foreach([
                ['key'=>'actividad', 'label'=>'Actividad', 'count'=>$auditLogs->count()],
                ['key'=>'firmas',    'label'=>'Firmas',    'count'=>$todasFirmas->count()],
                ['key'=>'equipos',   'label'=>'Historial equipos', 'count'=>$historialEquipos->count()],
                ['key'=>'vales',     'label'=>'Vales',     'count'=>$vales->count()],
                ['key'=>'accesos',   'label'=>'Accesos',   'count'=>$auditLogs->where('tipo','acceso')->count()],
                ['key'=>'usuarios',  'label'=>'Usuarios',  'count'=>$usuarios->count()],
            ] as $t)
            <button @click="tab = '{{ $t['key'] }}'"
                :class="tab === '{{ $t['key'] }}'
                    ? 'border-b-2 border-primary-600 text-primary-700 dark:text-primary-400 bg-white dark:bg-gray-900'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                style="padding: 14px 48px; gap: 4px;"
                class="flex items-center text-sm font-medium whitespace-nowrap transition-colors">
                {{ $t['label'] }}
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-full"
                      :class="tab === '{{ $t['key'] }}' ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                    {{ $t['count'] }}
                </span>
            </button>
            @endforeach
        </div>

        {{-- ── Actividad reciente ───────────────────────────────────────────── --}}
        <div x-show="tab === 'actividad'">
            @if($auditLogs->isEmpty())
            <p class="py-16 text-center text-sm text-gray-400">Sin actividad en el período seleccionado.</p>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Fecha/Hora</th>
                        <th class="px-4 py-3 text-left w-24">Tipo</th>
                        <th class="px-4 py-3 text-left">Descripción</th>
                        <th class="px-4 py-3 text-left w-44">Actor</th>
                        <th class="px-4 py-3 text-left w-28">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($auditLogs as $log)
                @php
                    $bc = match($log->tipo) {
                        'acceso'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                        'firma'   => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                        'equipo'  => 'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
                        'reporte' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                        'usuario' => 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300',
                        default   => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap font-mono">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $bc }}">{{ ucfirst($log->tipo) }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 max-w-sm">{{ $log->descripcion }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $log->actor_nombre }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-400 font-mono">{{ $log->ip ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- ── Firmas de documentos ─────────────────────────────────────────── --}}
        <div x-show="tab === 'firmas'">
            @if($todasFirmas->isEmpty())
            <p class="py-16 text-center text-sm text-gray-400">Sin documentos firmados aún.</p>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Fecha</th>
                        <th class="px-4 py-3 text-left w-28">Tipo</th>
                        <th class="px-4 py-3 text-left">Documento</th>
                        <th class="px-4 py-3 text-left w-44">Firmante (jefe)</th>
                        <th class="px-4 py-3 text-left w-36">Área</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($todasFirmas as $f)
                @php
                    $bf = match($f['badge']) {
                        'vale'     => 'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
                        'reporte'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                        'registro' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                        default    => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $f['fecha']?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-4 py-2.5"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $bf }}">{{ $f['tipo_doc'] }}</span></td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $f['documento'] }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $f['firmante'] }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500 truncate">{{ $f['area'] }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- ── Historial de equipos médicos ─────────────────────────────────── --}}
        <div x-show="tab === 'equipos'">
            @if($historialEquipos->isEmpty())
            <p class="py-16 text-center text-sm text-gray-400">Sin movimientos de equipos en el período seleccionado.</p>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Fecha/Hora</th>
                        <th class="px-4 py-3 text-left w-24">Evento</th>
                        <th class="px-4 py-3 text-left w-32">No. Inventario</th>
                        <th class="px-4 py-3 text-left">Descripción del cambio</th>
                        <th class="px-4 py-3 text-left w-32">Área</th>
                        <th class="px-4 py-3 text-left w-36">Usuario</th>
                        <th class="px-4 py-3 text-left w-24">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($historialEquipos as $h)
                @php
                    $bh = match($h->tipo_evento) {
                        'creado'      => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                        'actualizado' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                        'eliminado'   => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                        default       => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap font-mono">{{ $h->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-2.5"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $bh }}">{{ ucfirst($h->tipo_evento) }}</span></td>
                    <td class="px-4 py-2.5 text-xs font-mono text-gray-600 dark:text-gray-400">{{ $h->inventarioEquipo?->numero_inventario ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                        {{ $h->descripcion }}
                        @if(!empty($h->cambios))
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach(array_slice($h->cambios, 0, 4) as $c)
                            <span class="inline-block text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded">
                                {{ $c['etiqueta'] }}: {{ Str::limit($c['anterior'] ?? '—', 15) }} → {{ Str::limit($c['nuevo'] ?? '—', 15) }}
                            </span>
                            @endforeach
                            @if(count($h->cambios) > 4)
                            <span class="text-xs text-gray-400">+{{ count($h->cambios) - 4 }} más</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-500 truncate max-w-[128px]">{{ $h->inventarioEquipo?->area ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $h->usuario_nombre }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-400 font-mono">{{ $h->ip_address ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- ── Vales de inventario ──────────────────────────────────────────── --}}
        <div x-show="tab === 'vales'">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Fecha</th>
                        <th class="px-4 py-3 text-left w-24">Tipo</th>
                        <th class="px-4 py-3 text-left">Equipo</th>
                        <th class="px-4 py-3 text-left w-32">No. Inventario</th>
                        <th class="px-4 py-3 text-left w-32">Área</th>
                        <th class="px-4 py-3 text-left w-36">Técnico</th>
                        <th class="px-4 py-3 text-left w-36">Jefe firma</th>
                        <th class="px-4 py-3 text-left w-24">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($vales as $v)
                @php
                    $ev = match($v->estado) {
                        'culminado' => ['cls'=>'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300', 'lbl'=>'Firmado'],
                        'en_firma'  => ['cls'=>'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300', 'lbl'=>'En firma'],
                        default     => ['cls'=>'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',     'lbl'=>'Pendiente'],
                    };
                    $tv = $v->tipo === 'entrega'
                        ? ['cls'=>'bg-emerald-100 text-emerald-700', 'lbl'=>'Entrega']
                        : ['cls'=>'bg-red-100 text-red-700',         'lbl'=>'Retiro'];
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $v->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2.5"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $tv['cls'] }}">{{ $tv['lbl'] }}</span></td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 truncate max-w-[200px]">{{ $v->equipo_nombre ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-xs font-mono text-gray-600 dark:text-gray-400">{{ $v->numero_inventario ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500 truncate">{{ $v->area ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $v->usuario_nombre ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $v->jefe?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2.5"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $ev['cls'] }}">{{ $ev['lbl'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-12 text-center text-sm text-gray-400">Sin vales registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- ── Accesos al portal ────────────────────────────────────────────── --}}
        <div x-show="tab === 'accesos'">
            @php $accesos = $auditLogs->where('tipo', 'acceso')->values(); @endphp
            @if($accesos->isEmpty())
            <p class="py-16 text-center text-sm text-gray-400">Sin accesos registrados aún. Los nuevos inicios de sesión aparecerán aquí.</p>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left w-44">Fecha y hora</th>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-left w-36">Origen</th>
                        <th class="px-4 py-3 text-left w-36">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($accesos as $a)
                @php $origen = $a->metadata['origen'] ?? null; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap font-mono">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">{{ $a->actor_nombre }}</td>
                    <td class="px-4 py-2.5">
                        @if($origen === '/admin')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">
                                /admin
                            </span>
                        @elseif($origen === '/reportes')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                /reportes
                            </span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-xs font-mono text-gray-400">{{ $a->ip ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- ── Usuarios del portal ──────────────────────────────────────────── --}}
        <div x-show="tab === 'usuarios'">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left w-36">Registro</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left w-36">Servicio</th>
                        <th class="px-4 py-3 text-left w-28">Rol</th>
                        <th class="px-4 py-3 text-left w-44">Área (jefe)</th>
                        <th class="px-4 py-3 text-left w-24">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($usuarios as $u)
                @php
                    $eu = match($u->estado) {
                        'aprobado'  => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                        'rechazado' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                        default     => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $u->nombre }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">{{ $u->servicio ?: '—' }}</td>
                    <td class="px-4 py-2.5">
                        @if($u->es_jefe_servicio)
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">Jefe servicio</span>
                        @else
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Personal</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-500">{{ $u->area_jefe_servicio ?: '—' }}</td>
                    <td class="px-4 py-2.5"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $eu }}">{{ ucfirst($u->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">Sin usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

    </div>{{-- /card principal --}}

    {{-- ── Nota de protección ───────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
        </svg>
        Los registros de auditoría son inmutables — el sistema impide su edición o eliminación. Los filtros aplicados aquí también se trasladan al PDF descargado.
    </div>

</div>
</x-filament-panels::page>
