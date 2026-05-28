<x-filament-panels::page>

<style>
@media print {
    .firmar-header { display:none!important; }
    #pdf-overlay-editor { background:white!important; }
    .pdf-pw { box-shadow:none!important; }
    .pdf-campo { border:none!important;background:transparent!important; }
}
</style>

<div class="flex flex-col" style="height: calc(100vh - 130px);">

    {{-- Cabecera --}}
    <div class="firmar-header flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ \App\Filament\Pages\DocumentosGenerados::getUrl() }}"
               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Firmar vale</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $this->record->tipo_label }} — {{ $this->record->equipo_nombre ?: 'Vale #'.$this->record->id }}
                    @if($this->record->area) &mdash; {{ $this->record->area }} @endif
                </p>
            </div>
        </div>

        <button type="button"
                onclick="pdfOverlay.enviarFirmaIngeniero()"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg font-semibold text-sm
                       bg-primary-600 hover:bg-primary-700 text-white transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Enviar vale
        </button>
    </div>

    {{-- Área del editor --}}
    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-md flex-1 flex flex-col"
         wire:ignore>

        {{-- Barra de herramientas --}}
        <div class="flex items-center gap-2 flex-wrap px-3 py-2
                    bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

            <button type="button"
                    onclick="pdfOverlay.abrirPickerFirma(this)"
                    class="inline-flex items-center gap-1 text-sm font-semibold px-3 py-1.5 rounded-lg border
                           border-purple-400 text-purple-600 dark:text-purple-400
                           hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                </svg>
                Agregar mi firma
            </button>

            <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                Selecciona tu firma y arrástrala a la posición correcta en el PDF
            </span>
        </div>

        {{-- PDF con overlay --}}
        <div class="overflow-y-auto bg-gray-400 dark:bg-gray-900 flex-1">
            <div id="pdf-overlay-editor" class="py-6 flex flex-col items-center min-h-full">
                <div class="pdf-pages-wrap flex flex-col items-center w-full">
                    <p class="text-white text-sm opacity-60">Cargando PDF…</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', function () {
    window._fmtIngenieros  = @json($ingenieros);
    window._fmtJefas       = [];
    window._firmaExistente = @js($record->firma_ingeniero);

    setTimeout(function () {
        document.dispatchEvent(new CustomEvent('fmt:init-direct', {
            detail: {
                url:    @js(route('inventario.vale.preview', $record)),
                campos: [],
                valores: {}
            }
        }));
    }, 250);
});
</script>

</x-filament-panels::page>
