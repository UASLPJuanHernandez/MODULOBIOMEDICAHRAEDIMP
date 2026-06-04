<x-filament-panels::page>

{{-- ================================================================ --}}
{{-- LISTA                                                             --}}
{{-- ================================================================ --}}
@if($vista === 'lista')

<div class="flex items-center justify-end mb-5">
    <div class="flex items-center gap-2">
        @if(auth()->user()->esJefeServicio())
        <button wire:click="abrirModalMantenimientos"
                class="inline-flex items-center gap-2 border border-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30 text-teal-700 dark:text-teal-400 text-sm font-semibold px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Mantenimientos recibidos
            @php $cntPend = \App\Models\Registro::where('es_borrador',false)->where('estado','pendiente')->count(); @endphp
            @if($cntPend > 0)
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold bg-teal-500 text-white">
                {{ $cntPend }}
            </span>
            @endif
        </button>
        @endif
        <button wire:click="irCrear"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Subir formato
        </button>
    </div>
</div>

@php $formatos = $this->getFormatos(); @endphp

@if($formatos->isEmpty())
<div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 w-full">
    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">No hay formatos cargados.</p>
    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Sube un PDF para comenzar.</p>
</div>
@else
<div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="w-[35%] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-left:2rem;padding-right:1.25rem">Nombre</th>
                <th class="w-[30%] px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Archivo</th>
                <th class="w-[10%] px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Registros</th>
                <th class="w-[10%] px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                <th class="w-[15%] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-left:1.25rem;padding-right:2rem">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($formatos as $fmt)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <td class="py-3.5 font-medium text-gray-900 dark:text-gray-100" style="padding-left:2rem;padding-right:1.25rem"
                    x-data="{ editing: false, nombre: '{{ addslashes($fmt->nombre) }}' }">
                    <div x-show="!editing" class="flex items-center gap-1.5 group">
                        <span class="truncate">{{ $fmt->nombre }}</span>
                        <button @click.stop="editing = true"
                                class="opacity-40 hover:opacity-100 transition text-gray-400 hover:text-primary-600 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                            </svg>
                        </button>
                    </div>
                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                        <input x-model="nombre" type="text"
                               x-ref="inp"
                               x-init="$watch('editing', v => v && $nextTick(() => $refs.inp.select()))"
                               @keydown.enter.prevent="$wire.renombrarFormato({{ $fmt->id }}, nombre); editing = false"
                               @keydown.escape="editing = false; nombre = '{{ addslashes($fmt->nombre) }}'"
                               @blur="if(nombre.trim()) { $wire.renombrarFormato({{ $fmt->id }}, nombre); } editing = false"
                               class="w-full border border-primary-400 rounded px-2 py-0.5 text-sm text-gray-900 dark:text-gray-100 dark:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    </div>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $fmt->archivo_original }}</td>
                <td class="px-5 py-3.5 text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-bold">
                        {{ $fmt->registros_count }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ $fmt->created_at->format('d/m/Y') }}</td>
                <td class="py-3.5 text-right" style="padding-left:1.25rem;padding-right:2rem">
                    <div class="flex items-center justify-end gap-2">
                        <button wire:click="irEditar({{ $fmt->id }})"
                                class="text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-semibold px-3 py-1.5 rounded-lg transition">
                            Rellenar
                        </button>
                        <button wire:click="irBorradores({{ $fmt->id }})"
                                class="relative text-xs bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 font-semibold px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5">
                            Borradores
                            @if(($fmt->borradores_count ?? 0) > 0)
                            <span class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                                {{ $fmt->borradores_count }}
                            </span>
                            @endif
                        </button>
                        <button wire:click="eliminarFormato({{ $fmt->id }})"
                                wire:confirm="¿Eliminar el formato '{{ $fmt->nombre }}'? Se borrarán también todos sus registros."
                                class="text-xs bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-semibold px-3 py-1.5 rounded-lg transition">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif

{{-- ================================================================ --}}
{{-- CREAR                                                             --}}
{{-- ================================================================ --}}
@if($vista === 'crear')

<div class="max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <button wire:click="volverLista" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Subir nuevo formato</h2>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">

        @if($errorUpload)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3 text-sm">
            {{ $errorUpload }}
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del formato</label>
            <input type="text" wire:model="nombre" placeholder="Ej: Hoja de egreso"
                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Archivo PDF</label>
            <div x-data="{ label: '' }"
                 class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-primary-400 transition cursor-pointer"
                 onclick="document.getElementById('fmt-file').click()">
                <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400"
                   x-text="label || 'Haz clic para seleccionar un PDF'"></p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Solo .pdf — máx 20 MB</p>
                <input type="file" id="fmt-file" wire:model="archivo"
                       accept=".pdf" class="hidden"
                       x-on:change="label = $event.target.files[0]?.name || ''">
            </div>
            @error('archivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

            <div wire:loading wire:target="archivo" class="flex items-center gap-2 mt-2 text-sm text-gray-400 dark:text-gray-500">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Cargando...
            </div>
        </div>

        <div class="flex gap-3 pt-1">
            <button wire:click="subirArchivo" wire:loading.attr="disabled" wire:target="subirArchivo"
                    class="flex-1 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg transition text-sm flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="subirArchivo">Subir archivo</span>
                <span wire:loading wire:target="subirArchivo" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Subiendo...
                </span>
            </button>
            <button wire:click="volverLista"
                    class="flex-1 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium py-2.5 rounded-lg transition text-sm">
                Cancelar
            </button>
        </div>
    </div>
</div>

@endif

{{-- ================================================================ --}}
{{-- EDITAR / RELLENAR — PDF Overlay                                  --}}
{{-- ================================================================ --}}
@if($vista === 'editar')
@php $fmt = $this->getFormatoActual(); @endphp

<style>
#btn-pdf-add[data-active="true"] { background:#6366f1;color:#fff;border-color:#6366f1; }
@media print {
    .fmt-editor-header,.pdf-editor-tb { display:none!important; }
    #pdf-overlay-editor { background:white!important; }
    .pdf-pw { box-shadow:none!important;margin-bottom:0!important; }
    .pdf-campo-lbl { display:none!important; }
    .pdf-campo { border:none!important;background:transparent!important; }
    .pdf-campo-inp { color:#111!important; }
}
</style>

<div>
    {{-- Encabezado --}}
    <div class="fmt-editor-header flex items-center justify-between mb-3 gap-2 flex-wrap">
        <div class="flex items-center gap-3">
            <button wire:click="volverLista" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $fmt?->nombre }}</h2>
                    @if($borradorId)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                            </svg>
                            Borrador #{{ $borradorId }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $fmt?->archivo_original }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <input wire:model="identificador" type="text"
                   placeholder="Identificador (opcional)"
                   class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button onclick="pdfOverlay.guardarBorrador()"
                    class="border border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-semibold px-4 py-1.5 rounded-lg transition text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3v5H9V3"/>
                </svg>
                Guardar borrador
            </button>
            <button onclick="pdfOverlay.guardarRegistro()"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-1.5 rounded-lg transition text-sm">
                Guardar registro
            </button>
            @if($fmt?->archivo_path)
            <a href="{{ route('formato.archivo', $fmt) }}" target="_blank"
               class="border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium px-4 py-1.5 rounded-lg transition text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Original
            </a>
            @endif
        </div>
    </div>

    {{-- Editor PDF overlay --}}
    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-md"
         style="height: calc(100vh - 160px);" wire:ignore>

        {{-- Barra de herramientas --}}
        <div class="pdf-editor-tb flex items-center gap-2 flex-wrap px-3 py-2
                    bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <button type="button" id="btn-pdf-add" data-active="false"
                    onclick="pdfOverlay.toggleAddField()"
                    class="inline-flex items-center gap-1 text-sm font-semibold px-3 py-1.5 rounded-lg border
                           border-indigo-400 text-indigo-600 dark:text-indigo-400
                           hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar campo
            </button>
            <button type="button" id="btn-pdf-firma"
                    onclick="pdfOverlay.abrirPickerFirma(this)"
                    class="inline-flex items-center gap-1 text-sm font-semibold px-3 py-1.5 rounded-lg border
                           border-purple-400 text-purple-600 dark:text-purple-400
                           hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                </svg>
                Agregar firma
            </button>
            {{-- Firma quien recibe: solo disponible desde la plataforma de reportes --}}
            <button type="button" onclick="pdfOverlay.guardarPlantilla()"
                    class="inline-flex items-center gap-1 text-sm font-semibold px-3 py-1.5 rounded-lg border
                           border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Guardar plantilla
            </button>
            <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                Activa "Agregar campo" y haz clic sobre el PDF &nbsp;·&nbsp;
                Arrastra para mover &nbsp;·&nbsp; Esquina ↘ para redimensionar
            </span>
        </div>

        {{-- Área de scroll del PDF --}}
        <div class="overflow-y-auto bg-gray-400 dark:bg-gray-900" style="height: calc(100% - 46px);">
            <div id="pdf-overlay-editor" class="py-6 flex flex-col items-center min-h-full">
                <div class="pdf-pages-wrap flex flex-col items-center w-full">
                    <p class="text-white text-sm opacity-60">Cargando PDF…</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

{{-- ================================================================ --}}
{{-- BORRADORES                                                        --}}
{{-- ================================================================ --}}
@if($vista === 'borradores')
@php $fmt = $this->getFormatoActual(); @endphp

<div>
    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <button wire:click="volverLista" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                Borradores: <span class="text-amber-600 dark:text-amber-400">{{ $fmt?->nombre }}</span>
            </h2>
        </div>
        <button wire:click="irEditar({{ $fmt?->id }})"
                class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
            + Nuevo registro
        </button>
    </div>

    {{-- Barra de búsqueda y filtros --}}
    @php $ingenieros = $this->getIngenieros(); @endphp
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="busquedaHistorial"
                   type="text" placeholder="Buscar por identificador…"
                   class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        @if($ingenieros->isNotEmpty())
        <select wire:model.live="filtroUsuario"
                class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">Ingeniero</option>
            @foreach($ingenieros as $ing)
            <option value="{{ $ing->id }}">{{ $ing->nombre }}</option>
            @endforeach
        </select>
        @endif
        <div class="flex items-center gap-1.5">
            <input wire:model.live="filtroFechaDesde" type="date"
                   class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <span class="text-xs text-gray-400">—</span>
            <input wire:model.live="filtroFechaHasta" type="date"
                   class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        @if($busquedaHistorial || $filtroUsuario || $filtroFechaDesde || $filtroFechaHasta)
        <button wire:click="$set('busquedaHistorial', ''); $set('filtroUsuario', ''); $set('filtroFechaDesde', ''); $set('filtroFechaHasta', '')"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Limpiar
        </button>
        @endif
    </div>

    @php $borradores = $this->getBorradores(); @endphp

    @if($borradores->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No hay borradores guardados.</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Rellena un formato y usa "Guardar borrador" para guardarlo aquí.</p>
    </div>
    @else
    <div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="w-[5%] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-left:2rem;padding-right:1.25rem">#</th>
                    <th class="w-[35%] px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Identificador</th>
                    <th class="w-[25%] px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                    <th class="w-[20%] px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                    <th class="w-[15%] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-left:1.25rem;padding-right:2rem">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($borradores as $reg)
                <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition bg-amber-50/20 dark:bg-amber-900/5">
                    <td class="py-3.5 text-sm text-gray-400 dark:text-gray-500" style="padding-left:2rem;padding-right:1.25rem">{{ $reg->id }}</td>
                    <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-gray-100 truncate">
                        {{ $reg->identificador ?: '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-400 truncate">{{ $reg->usuario?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3.5 text-right" style="padding-left:1.25rem;padding-right:2rem">
                        <button wire:click="continuarBorrador({{ $reg->id }})"
                                class="text-xs bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 font-semibold px-3 py-1.5 rounded-lg transition">
                            Continuar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endif

{{-- ================================================================ --}}
{{-- VER REGISTRO                                                      --}}
{{-- ================================================================ --}}
@if($vista === 'ver')
@php
    $reg = $this->getRegistroActual();
    $fmt = $this->getFormatoActual();
@endphp

<style>
@media print {
    .fmt-view-header { display:none!important; }
    #pdf-overlay-viewer { background:white!important; }
    .pdf-pw { box-shadow:none!important;margin-bottom:0!important; }
    .pdf-campo { border:none!important;background:transparent!important; }
}
</style>

<div class="flex flex-col" style="height: calc(100vh - 100px);">

    {{-- Header --}}
    <div class="fmt-view-header flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <button wire:click="volverLista"
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
                    {{ $fmt?->nombre }}
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
            <button wire:click="irEditar({{ $formatoId }})"
                    class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-4 py-1.5 rounded-lg transition text-sm">
                + Nuevo registro
            </button>
        </div>
    </div>

    {{-- Visor PDF con overlay --}}
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
{{-- MODAL: MANTENIMIENTOS RECIBIDOS (solo jefes de servicio)         --}}
{{-- ================================================================ --}}
@if($modalMantenimientosAbierto && auth()->user()->esJefeServicio())

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.6);">

    <div class="relative w-full bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col"
         style="max-width:960px; height:90vh;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Mantenimientos recibidos</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Formatos pendientes de firma — tu área de servicio</p>
                </div>
            </div>
            <button wire:click="cerrarModalMantenimientos"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @if($mantenimientoViendoId)
        {{-- Sub-vista: visor PDF del mantenimiento seleccionado --}}
        @php $mReg = \App\Models\Registro::with(['formato','usuario'])->find($mantenimientoViendoId); @endphp
        <div class="flex flex-col flex-1 overflow-hidden">
            {{-- Sub-header --}}
            <div class="flex items-center justify-between px-5 py-2.5 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <div class="flex items-center gap-2">
                    <button wire:click="$set('mantenimientoViendoId', null)"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $mReg?->identificador ?: 'Mantenimiento #' . $mReg?->id }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $mReg?->formato?->nombre }}
                            &mdash; {{ $mReg?->usuario?->name ?? '—' }}
                            &mdash; {{ $mReg?->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <button wire:click="firmarMantenimiento({{ $mantenimientoViendoId }})"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-1.5 rounded-lg text-white transition"
                        style="background-color:#0d9488;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                    </svg>
                    Firmar y enviar
                </button>
            </div>
            {{-- Visor PDF --}}
            <div class="flex-1 overflow-y-auto bg-gray-400 dark:bg-gray-900 rounded-b-2xl" wire:ignore>
                <div id="pdf-overlay-mantenimiento" class="py-6 flex flex-col items-center min-h-full">
                    <div class="pdf-pages-wrap flex flex-col items-center w-full">
                        <p class="text-white text-sm opacity-60">Cargando PDF…</p>
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- Lista de mantenimientos --}}
        <div class="flex flex-col flex-1 overflow-hidden">
            {{-- Filtros --}}
            <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="mBusqueda"
                           type="text" placeholder="Buscar por identificador…"
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                @php $fmtsLista = $this->getFormatos(); @endphp
                @if($fmtsLista->isNotEmpty())
                <select wire:model.live="mFiltroFormato"
                        class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Todos los formatos</option>
                    @foreach($fmtsLista as $fmtOpt)
                    <option value="{{ $fmtOpt->id }}">{{ $fmtOpt->nombre }}</option>
                    @endforeach
                </select>
                @endif
                <div class="flex items-center gap-1.5">
                    <input wire:model.live="mFechaDesde" type="date"
                           class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="text-xs text-gray-400">—</span>
                    <input wire:model.live="mFechaHasta" type="date"
                           class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                @if($mBusqueda || $mFiltroFormato || $mFechaDesde || $mFechaHasta)
                <button wire:click="$set('mBusqueda','');$set('mFiltroFormato','');$set('mFechaDesde','');$set('mFechaHasta','')"
                        class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpiar
                </button>
                @endif
            </div>

            {{-- Tabla --}}
            <div class="flex-1 overflow-y-auto p-5">
                @php $mantenimientos = $this->getMantenimientosPendientes(); @endphp
                @if($mantenimientos->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No hay mantenimientos pendientes de firma.</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Aparecen aquí cuando los ingenieros guardan registros.</p>
                </div>
                @else
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="w-[35%] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-left:1.5rem">Identificador</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Formato</th>
                                <th class="w-[140px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ingeniero</th>
                                <th class="w-[120px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                <th class="w-[120px] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="padding-right:1.5rem">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($mantenimientos as $mnt)
                            <tr class="hover:bg-teal-50/30 dark:hover:bg-teal-900/10 transition">
                                <td class="py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" style="padding-left:1.5rem">
                                    {{ $mnt->identificador ?: '# ' . $mnt->id }}
                                </td>
                                <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ $mnt->formato?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                                    {{ $mnt->usuario?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-sm text-gray-400 dark:text-gray-500">
                                    {{ $mnt->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 text-right" style="padding-right:1.5rem">
                                    <button wire:click="verMantenimiento({{ $mnt->id }})"
                                            class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg border border-teal-400 text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver y firmar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endif

</x-filament-panels::page>
