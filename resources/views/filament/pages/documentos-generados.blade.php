<x-filament-panels::page>

@if($vistaDoc === 'lista')

{{-- ================================================================ --}}
{{-- PESTAÑAS                                                          --}}
{{-- ================================================================ --}}
<div class="mb-6">
    <div class="flex items-center gap-1 border-b border-gray-200 dark:border-gray-700">

        {{-- Pestaña: Vales de Inventario --}}
        <button wire:click="cambiarTab('vales')"
                class="relative flex items-center gap-2 px-5 py-3 text-sm font-semibold transition
                       {{ $tabActiva === 'vales'
                            ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400 -mb-px'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            Vales de Inventario
            @php $bVales = $this->getBadgeVales(); @endphp
            @if($bVales > 0)
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold
                         bg-warning-100 dark:bg-warning-900/40 text-warning-700 dark:text-warning-300">
                {{ $bVales }}
            </span>
            @endif
        </button>

        {{-- Pestaña: Registros de Formatos --}}
        <button wire:click="cambiarTab('registros')"
                class="relative flex items-center gap-2 px-5 py-3 text-sm font-semibold transition
                       {{ $tabActiva === 'registros'
                            ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400 -mb-px'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Registros de Formatos
            @php $bReg = $this->getBadgeRegistros(); @endphp
            @if($bReg > 0)
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold
                         bg-success-100 dark:bg-success-900/40 text-success-700 dark:text-success-300">
                {{ $bReg }}
            </span>
            @endif
        </button>

        {{-- Pestaña: Reportes --}}
        <button wire:click="cambiarTab('reportes')"
                class="relative flex items-center gap-2 px-5 py-3 text-sm font-semibold transition
                       {{ $tabActiva === 'reportes'
                            ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400 -mb-px'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Reportes
            @php $bRpPend = $this->getBadgeReportesPendientes(); @endphp
            @if($bRpPend > 0)
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold
                         bg-warning-100 dark:bg-warning-900/40 text-warning-700 dark:text-warning-300">
                {{ $bRpPend }}
            </span>
            @endif
        </button>

    </div>
</div>

{{-- ================================================================ --}}
{{-- PESTAÑA: VALES DE INVENTARIO                                     --}}
{{-- ================================================================ --}}
@if($tabActiva === 'vales' && $vistaDoc === 'lista')

{{-- Sub-pestañas por estado --}}
<div class="flex items-center gap-1 mb-5">
    @foreach(['pendientes' => ['Pendientes','warning'], 'culminados' => ['Culminados','success']] as $sub => [$lbl, $col])
    @php
        $cntSub = match($sub) {
            'pendientes' => \App\Models\ValeInventario::whereIn('estado',['pendiente','en_firma'])->count(),
            'culminados' => \App\Models\ValeInventario::where('estado','culminado')->count(),
        };
        $active = $vSubTab === $sub;
        $cls = $active
            ? "bg-{$col}-100 dark:bg-{$col}-900/30 text-{$col}-700 dark:text-{$col}-300"
            : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700';
    @endphp
    <button wire:click="cambiarSubTabVales('{{ $sub }}')"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition {{ $cls }}">
        @if($sub === 'pendientes')
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @else
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @endif
        {{ $lbl }}
        @if($cntSub > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold
                     {{ $active ? "bg-{$col}-200 dark:bg-{$col}-800 text-{$col}-800 dark:text-{$col}-200" : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
            {{ $cntSub }}
        </span>
        @endif
    </button>
    @endforeach
</div>

{{-- Barra de filtros --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input wire:model.live.debounce.300ms="vBusqueda"
               type="text" placeholder="Buscar por equipo, área, no. inventario…"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>
    <select wire:model.live="vFiltroTipo"
            class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option value="">Todos los tipos</option>
        <option value="entrega">Vale de Entrega</option>
        <option value="retiro">Vale de Retiro</option>
    </select>
    <div class="flex items-center gap-1.5">
        <input wire:model.live="vFechaDesde" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span class="text-xs text-gray-400">—</span>
        <input wire:model.live="vFechaHasta" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>
    @if($vFiltroTipo || $vBusqueda || $vFechaDesde || $vFechaHasta)
    <button wire:click="limpiarFiltrosVales"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar
    </button>
    @endif
</div>

@php $vales = $this->getVales(); @endphp

@if($vales->isEmpty())
<div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 w-full">
    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">No hay vales en este estado.</p>
</div>
@else
<div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto mb-4">
    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="w-[120px] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-left:1.5rem">Fecha</th>
                <th class="w-[110px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Tipo</th>
                <th class="w-[120px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">No. Inventario</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Equipo</th>
                <th class="w-[190px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                    {{ $vSubTab === 'culminados' ? 'Firma' : 'Estado' }}
                </th>
                <th class="w-[180px] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-right:1.5rem">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($vales as $vale)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <td class="py-3.5 text-sm text-gray-500 dark:text-gray-400" style="padding-left:1.5rem">
                    {{ $vale->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3.5">
                    @if($vale->tipo === 'entrega')
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Entrega
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-7 7-7-7"/></svg>
                            Retiro
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ $vale->numero_inventario ?: '—' }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300 truncate">
                    {{ $vale->equipo_nombre ?: '—' }}
                </td>
                <td class="px-4 py-3.5">
                    @if($vSubTab === 'culminados')
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            Firmado · {{ $vale->firmado_at?->format('d/m/Y') ?? '—' }}
                        </span>
                    @elseif($vale->estado === 'en_firma')
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                En espera de firma
                            </span>
                            @if($vale->jefe)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">→ {{ $vale->jefe->nombre }}</span>
                            @endif
                        </div>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pendiente
                        </span>
                    @endif
                </td>
                <td class="py-3.5 text-right" style="padding-right:1.5rem">
                    <div class="flex items-center justify-end gap-1.5">
                        {{-- Ver (PDF preview) --}}
                        <button wire:click="verVale({{ $vale->id }})"
                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition text-white"
                                style="background-color:#16a34a;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver
                        </button>
                        {{-- Descargar PDF --}}
                        <a href="{{ route('inventario.vale.descargar-pdf', $vale) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-semibold px-2.5 py-1.5 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            PDF
                        </a>
                        {{-- Concretar (solo si aún no fue enviado a firma) --}}
                        @if($vSubTab === 'pendientes' && $vale->estado === 'pendiente')
                        <button wire:click="abrirConcretarVale({{ $vale->id }})"
                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition text-white"
                                style="background-color:#4f46e5;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Concretar
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $vales->links() }}</div>
@endif

@endif {{-- fin tab vales lista --}}


{{-- ================================================================ --}}
{{-- PESTAÑA: REGISTROS DE FORMATOS                                   --}}
{{-- ================================================================ --}}
@if($tabActiva === 'registros')

{{-- Sub-pestañas Pendientes / En Curso --}}
<div class="flex items-center gap-1 mb-5">
    <button wire:click="cambiarSubTabRegistros('pendientes')"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $rSubTab === 'pendientes'
                        ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Pendientes
        @php $cntRegPend = \App\Models\Registro::where('es_borrador',false)->whereIn('estado',['pendiente','en_firma'])->count(); @endphp
        @if($cntRegPend > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold bg-warning-200 dark:bg-warning-800 text-warning-800 dark:text-warning-200">
            {{ $cntRegPend }}
        </span>
        @endif
    </button>

    <button wire:click="cambiarSubTabRegistros('culminado')"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $rSubTab === 'culminado'
                        ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Culminados
        @php $cntRegCulm = \App\Models\Registro::where('es_borrador',false)->where('estado','culminado')->count(); @endphp
        @if($cntRegCulm > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold
                     {{ $rSubTab === 'culminado' ? 'bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
            {{ $cntRegCulm }}
        </span>
        @endif
    </button>
</div>

{{-- Barra de filtros --}}
@php
    $formatos   = $this->getFormatos();
    $ingenieros = $this->getIngenieros();
@endphp

<div class="flex flex-wrap items-center gap-3 mb-4">

    {{-- Buscador --}}
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input wire:model.live.debounce.300ms="rBusqueda"
               type="text" placeholder="Buscar por identificador…"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    {{-- Filtro por formato --}}
    @if($formatos->isNotEmpty())
    <select wire:model.live="rFiltroFormato"
            class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option value="">Todos los formatos</option>
        @foreach($formatos as $fmt)
        <option value="{{ $fmt->id }}">{{ $fmt->nombre }}</option>
        @endforeach
    </select>
    @endif

    {{-- Filtro por ingeniero --}}
    @if($ingenieros->isNotEmpty())
    <select wire:model.live="rFiltroIngeniero"
            class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option value="">Todos los ingenieros</option>
        @foreach($ingenieros as $ing)
        <option value="{{ $ing->id }}">{{ $ing->nombre }}</option>
        @endforeach
    </select>
    @endif

    {{-- Rango de fechas --}}
    <div class="flex items-center gap-1.5">
        <input wire:model.live="rFechaDesde" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span class="text-xs text-gray-400">—</span>
        <input wire:model.live="rFechaHasta" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    {{-- Limpiar --}}
    @if($rFiltroFormato || $rFiltroIngeniero || $rBusqueda || $rFechaDesde || $rFechaHasta)
    <button wire:click="limpiarFiltrosRegistros"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar
    </button>
    @endif
</div>

@php $registros = $this->getRegistros(); @endphp

@if($registros->isEmpty())
<div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 w-full">
    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">
        {{ $rSubTab === 'culminado' ? 'No hay registros culminados aún.' : 'No hay registros pendientes.' }}
    </p>
    @if($rSubTab === 'pendientes')
    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Los registros aparecen aquí cuando los ingenieros guardan formatos.</p>
    @endif
</div>
@else

{{-- Agrupado por formato si no hay filtro de formato específico --}}
@if(!$rFiltroFormato)
    {{-- Mostrar subgrupos por formato --}}
    @php
        $porFormato = $registros->getCollection()->groupBy('formato_id');
    @endphp
    @foreach($porFormato as $fmtId => $regs)
    @php $fmtNombre = $regs->first()->formato?->nombre ?? 'Sin formato'; @endphp
    <div class="mb-6">
        {{-- Encabezado de subgrupo --}}
        <div class="flex items-center gap-2 mb-2">
            <div class="flex items-center gap-1.5 text-sm font-semibold text-gray-700 dark:text-gray-200">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                {{ $fmtNombre }}
            </div>
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ $regs->count() }}
            </span>
        </div>

        @include('filament.pages.partials.documentos-registros-tabla', ['registros' => $regs])
    </div>
    @endforeach

    {{-- Paginación (aplica al total paginado) --}}
    <div class="mt-2">{{ $registros->links() }}</div>

@else
    {{-- Sin agrupación cuando hay filtro de formato específico --}}
    @include('filament.pages.partials.documentos-registros-tabla', ['registros' => $registros->getCollection()])
    <div class="mt-2">{{ $registros->links() }}</div>
@endif

@endif

@endif {{-- fin tab registros --}}


{{-- ================================================================ --}}
{{-- PESTAÑA: BITÁCORAS DE REPORTE                                    --}}
{{-- ================================================================ --}}
@if($tabActiva === 'bitacoras')

<div class="flex flex-wrap items-center gap-3 mb-4">

    {{-- Buscador --}}
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input wire:model.live.debounce.300ms="bBusqueda"
               type="text" placeholder="Buscar por personal, área, equipo…"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    {{-- Rango de fechas --}}
    <div class="flex items-center gap-1.5">
        <input wire:model.live="bFechaDesde" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span class="text-xs text-gray-400">—</span>
        <input wire:model.live="bFechaHasta" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    @if($bBusqueda || $bFechaDesde || $bFechaHasta)
    <button wire:click="limpiarFiltrosBitacoras"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar
    </button>
    @endif
</div>

@php $bitacoras = $this->getBitacoras(); @endphp

@if($bitacoras->isEmpty())
<div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 w-full">
    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">No hay bitácoras registradas.</p>
</div>
@else
<div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto mb-4">
    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="w-[110px] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-left:1.5rem">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Personal</th>
                <th class="w-[160px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Área</th>
                <th class="w-[160px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Equipo</th>
                <th class="w-[120px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Resultado</th>
                <th class="w-[160px] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-right:1.5rem">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($bitacoras as $bit)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <td class="py-3.5 text-sm text-gray-500 dark:text-gray-400" style="padding-left:1.5rem">
                    {{ \Carbon\Carbon::parse($bit->fecha_reporte)->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300 truncate">
                    {{ $bit->nombre_personal }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ $bit->area_departamento }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ $bit->nombre_dispositivo ?: '—' }}
                </td>
                <td class="px-4 py-3.5">
                    @php
                        $color = match($bit->resultado) {
                            'satisfactoria'    => 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300',
                            'parcial'          => 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300',
                            'no_satisfactoria' => 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300',
                            default            => 'bg-gray-100 text-gray-600',
                        };
                        $label = match($bit->resultado) {
                            'satisfactoria'    => 'Satisfactoria',
                            'parcial'          => 'Parcial',
                            'no_satisfactoria' => 'No satisfactoria',
                            default            => $bit->resultado,
                        };
                    @endphp
                    <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full {{ $color }}">
                        {{ $label }}
                    </span>
                </td>
                <td class="py-3.5 text-right flex items-center justify-end gap-2" style="padding-right:1.5rem">
                    <button wire:click="verBitacora({{ $bit->id }})"
                            class="inline-flex items-center gap-1.5 text-xs bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver
                    </button>
                    <a href="{{ \App\Filament\Resources\BitacoraReporteResource::getUrl('edit', ['record' => $bit]) }}"
                       class="inline-flex items-center gap-1.5 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                    <a href="{{ route('bitacora.descargar', $bit) }}"
                       class="inline-flex items-center gap-1.5 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-semibold px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        DOCX
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $bitacoras->links() }}</div>
@endif

@endif {{-- fin tab bitacoras --}}


{{-- ================================================================ --}}
{{-- PESTAÑA: REPORTES                                                 --}}
{{-- ================================================================ --}}
@if($tabActiva === 'reportes')


{{-- Sub-pestañas Pendientes / Completados --}}
<div class="flex items-center gap-1 mb-5">
    <button wire:click="cambiarSubTabReportes('pendientes')"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $rpSubTab === 'pendientes'
                        ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Pendientes
        @php $cntPend = \App\Models\ReportePizarron::where('estado','completado')->where('concretado',false)->count(); @endphp
        @if($cntPend > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold bg-warning-200 dark:bg-warning-800 text-warning-800 dark:text-warning-200">
            {{ $cntPend }}
        </span>
        @endif
    </button>

    <button wire:click="cambiarSubTabReportes('completados')"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $rpSubTab === 'completados'
                        ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Completados
    </button>
</div>

{{-- Barra de filtros --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input wire:model.live.debounce.300ms="rpBusqueda"
               type="text" placeholder="Buscar por equipo, área, responsable…"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>
    <div class="flex items-center gap-1.5">
        <input wire:model.live="rpFechaDesde" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span class="text-xs text-gray-400">—</span>
        <input wire:model.live="rpFechaHasta" type="date"
               class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>
    @if($rpBusqueda || $rpFechaDesde || $rpFechaHasta)
    <button wire:click="limpiarFiltrosReportes"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar
    </button>
    @endif
</div>

@php $reportes = $this->getReportes(); @endphp

@if($reportes->isEmpty())
<div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 w-full">
    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">
        {{ $rpSubTab === 'pendientes' ? 'No hay reportes pendientes de concretar.' : 'No hay reportes concretados aún.' }}
    </p>
    @if($rpSubTab === 'pendientes')
    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Los reportes aparecen aquí cuando se marcan como completados en el pizarrón.</p>
    @endif
</div>
@else
<div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto mb-4">
    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="w-[110px] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-left:1.5rem">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Equipo / Descripción</th>
                <th class="w-[150px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Área</th>
                <th class="w-[130px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Responsable</th>
                <th class="w-[150px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Estado</th>
                <th class="w-[180px] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-right:1.5rem">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($reportes as $rp)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <td class="py-3.5 text-sm text-gray-500 dark:text-gray-400" style="padding-left:1.5rem">
                    {{ $rp->updated_at->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3.5">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $rp->equipo ?: '—' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ Str::limit($rp->descripcion, 80) }}</p>
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ $rp->ubicacion ?: '—' }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ $rp->responsable ?: '—' }}
                </td>
                <td class="px-4 py-3.5">
                    @if($rp->concretado)
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                Concretado
                            </span>
                            @if($rp->concretado_at)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $rp->concretado_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    @elseif($rp->firmaSolicitud && !$rp->firmaSolicitud->firmado_at)
                        @php $jefeFirma = \App\Models\PersonalReportante::find($rp->firmaSolicitud->personal_reportante_id); @endphp
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                En espera de firma
                            </span>
                            @if($jefeFirma)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">→ {{ $jefeFirma->nombre }}</span>
                            @endif
                        </div>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pendiente
                        </span>
                    @endif
                </td>
                <td class="py-3.5 text-right" style="padding-right:1.5rem">
                    @if($rpSubTab === 'pendientes')
                    <button type="button"
                            wire:click="abrirBitacoraReporte({{ $rp->id }})"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                            style="background-color:#16a34a;color:#ffffff;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Concretar reporte
                    </button>
                    @else
                    @if($rp->bitacora)
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('bitacora.preview', $rp->bitacora) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-indigo-300 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver PDF
                        </a>
                        <a href="{{ route('bitacora.descargar', $rp->bitacora) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descargar
                        </a>
                    </div>
                    @else
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Concretado
                    </span>
                    @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $reportes->links() }}</div>
@endif

@endif {{-- fin tab reportes --}}


@endif {{-- fin vistaDoc lista --}}

{{-- ================================================================ --}}
{{-- VISOR DE REGISTRO                                                 --}}
{{-- ================================================================ --}}
@if($vistaDoc === 'ver')
@php $reg = $this->getRegistroViendo(); @endphp

<style>
@media print {
    .doc-view-header { display:none!important; }
    #pdf-overlay-viewer { background:white!important; }
    .pdf-pw { box-shadow:none!important;margin-bottom:0!important; }
    .pdf-campo { border:none!important;background:transparent!important; }
}
</style>

<div class="flex flex-col" style="height: calc(100vh - 100px);">

    {{-- Header --}}
    <div class="doc-view-header flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <button wire:click="volverARegistros"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $reg?->identificador ?: 'Registro #' . $reg?->id }}
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $reg?->formato?->nombre }}
                    &mdash; {{ $reg?->usuario?->name ?? '—' }}
                    &mdash; {{ $reg?->created_at?->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                    class="border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium px-4 py-1.5 rounded-lg transition text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
        </div>
    </div>

    {{-- Visor PDF --}}
    <div class="flex-1 overflow-y-auto bg-gray-400 dark:bg-gray-900 rounded-xl" wire:ignore>
        <div id="pdf-overlay-viewer" class="py-6 flex flex-col items-center min-h-full">
            <div class="pdf-pages-wrap flex flex-col items-center w-full">
                <p class="text-white text-sm opacity-60">Cargando PDF…</p>
            </div>
        </div>
    </div>

</div>

@endif

{{-- ================================================================ --}}
{{-- MODAL: VISTA PREVIA DE BITÁCORA (PDF inline)                     --}}
{{-- ================================================================ --}}
@if($bitacoraViendoId)
@php $bv = $this->getBitacoraViendo(); @endphp
@if($bv)

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.6);">

    <div class="relative w-full bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col"
         style="max-width:860px; height:90vh;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Bitácora — {{ $bv->nombre_personal }}
                    &mdash; {{ \Carbon\Carbon::parse($bv->fecha_reporte)->format('d/m/Y') }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bitacora.descargar', $bv) }}"
                   class="inline-flex items-center gap-1.5 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-semibold px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar DOCX
                </a>
                <button wire:click="cerrarBitacora"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- iframe PDF --}}
        <iframe src="{{ route('bitacora.preview', $bv) }}"
                class="flex-1 w-full rounded-b-2xl"
                style="border:none;"
                wire:ignore>
        </iframe>

    </div>
</div>

@endif
@endif

{{-- ================================================================ --}}
{{-- MODAL: VISTA PREVIA DE VALE DE INVENTARIO (PDF inline)           --}}
{{-- ================================================================ --}}
@if($valeViendoId)
@php $vv = $this->getValeViendo(); @endphp
@if($vv)

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.6);">

    <div class="relative w-full bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col"
         style="max-width:860px; height:90vh;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-2">
                @if($vv->tipo === 'entrega')
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Entrega
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-7 7-7-7"/>
                    </svg>
                    Retiro
                </span>
                @endif
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ $vv->equipo_nombre ?: 'Vale #'.$vv->id }}
                    @if($vv->numero_inventario)
                        &mdash; {{ $vv->numero_inventario }}
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventario.vale.descargar-pdf', $vv) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-semibold px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar PDF
                </a>
                <button wire:click="cerrarVale"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- iframe PDF --}}
        <iframe src="{{ route('inventario.vale.preview', $vv) }}"
                class="flex-1 w-full rounded-b-2xl"
                style="border:none;"
                wire:ignore>
        </iframe>

    </div>
</div>

@endif
@endif

{{-- ================================================================ --}}
{{-- MODAL: SOLICITAR FIRMA A JEFE DE SERVICIO                        --}}
{{-- ================================================================ --}}
@if($firmaModalReporteId)
@php $jefes = $this->getJefesServicio(); @endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.55);">

    <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 0l.172.172a2 2 0 010 2.828L12 16H9v-3z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Solicitar firma</h3>
            </div>
            <button wire:click="cerrarModalFirma"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-5 py-4 space-y-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Selecciona el Jefe de Servicio al que le enviarás este reporte para firma.
            </p>

            @if($jefes->isEmpty())
                <div class="text-sm text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-lg px-4 py-3">
                    No hay Jefes de Servicio registrados y aprobados en el sistema.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($jefes as $jefe)
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition
                                  {{ $firmaJefeId == $jefe->id
                                       ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-500'
                                       : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40' }}">
                        <input type="radio" wire:model="firmaJefeId" value="{{ $jefe->id }}" class="text-indigo-600">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $jefe->nombre }}</p>
                            @if($jefe->area_jefe_servicio)
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $jefe->area_jefe_servicio }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            @endif

            @if($firmaModalError)
            <p class="text-xs text-danger-600 dark:text-danger-400">{{ $firmaModalError }}</p>
            @endif
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="cerrarModalFirma"
                    class="text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium px-4 py-2 rounded-lg transition">
                Cancelar
            </button>
            @if($jefes->isNotEmpty())
            <button wire:click="enviarSolicitudFirma"
                    class="text-sm font-semibold px-4 py-2 rounded-lg transition text-white"
                    style="background-color:#4f46e5;">
                Enviar solicitud
            </button>
            @endif
        </div>

    </div>
</div>
@endif

{{-- ================================================================ --}}
{{-- MODAL: ENVIAR REGISTRO A JEFE DE SERVICIO                        --}}
{{-- ================================================================ --}}
@if($regEnviarId)
@php $jefesEnviar = $this->getJefesServicio(); @endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.55);">

    <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-success-100 dark:bg-success-900/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-success-600 dark:text-success-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Enviar a Jefe de Servicio</h3>
            </div>
            <button wire:click="cerrarModalEnviar"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto">

            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tipo de documento</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['mantenimiento' => 'Mantenimiento', 'reporte' => 'Reporte', 'vale' => 'Vale', 'documento' => 'Documento'] as $val => $lbl)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-sm font-medium
                                  {{ $regEnviarTipo === $val
                                       ? 'border-success-400 bg-success-50 dark:bg-success-900/20 dark:border-success-500 text-success-700 dark:text-success-300'
                                       : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/40' }}">
                        <input type="radio" wire:model="regEnviarTipo" value="{{ $val }}" class="text-success-600">
                        {{ $lbl }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Jefe de Servicio</p>
                @if($jefesEnviar->isEmpty())
                    <div class="text-sm text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-lg px-4 py-3">
                        No hay Jefes de Servicio registrados y aprobados.
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach($jefesEnviar as $jefe)
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition
                                      {{ $regEnviarJefeId == $jefe->id
                                           ? 'border-success-400 bg-success-50 dark:bg-success-900/20 dark:border-success-500'
                                           : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40' }}">
                            <input type="radio" wire:model="regEnviarJefeId" value="{{ $jefe->id }}" class="text-success-600">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $jefe->nombre }}</p>
                                @if($jefe->area_jefe_servicio)
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $jefe->area_jefe_servicio }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($regEnviarError)
            <p class="text-xs text-danger-600 dark:text-danger-400">{{ $regEnviarError }}</p>
            @endif
        </div>

        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="cerrarModalEnviar"
                    class="text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium px-4 py-2 rounded-lg transition">
                Cancelar
            </button>
            @if($jefesEnviar->isNotEmpty())
            <button wire:click="enviarRegistroAJefe"
                    class="text-sm font-semibold px-4 py-2 rounded-lg transition text-white"
                    style="background-color:#16a34a;">
                Enviar
            </button>
            @endif
        </div>

    </div>
</div>
@endif


</x-filament-panels::page>
