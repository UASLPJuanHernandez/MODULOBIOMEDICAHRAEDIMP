<x-filament-panels::page>

<div class="max-w-xl mx-auto space-y-6">

    {{-- Vista previa actual --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Fondo actual de la pantalla de login</p>
        </div>
        <div class="relative h-48 bg-gray-100 dark:bg-gray-900 flex items-center justify-center"
             style="{{ $fondoActual ? 'background-image:url(\''.e($fondoActual).'\');background-size:cover;background-position:center;' : '' }}">
            @if(!$fondoActual)
            <div class="text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">Sin imagen — usando degradado por defecto</p>
            </div>
            @endif
        </div>
        @if($fondoActual)
        <div class="px-5 py-3 flex justify-end border-t border-gray-200 dark:border-gray-700">
            <button wire:click="eliminar"
                    wire:confirm="¿Eliminar el fondo actual y volver al degradado por defecto?"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg
                           bg-danger-100 hover:bg-danger-200 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar fondo
            </button>
        </div>
        @endif
    </div>

    {{-- Upload nueva imagen --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Subir nueva imagen de fondo</p>
            <p class="text-xs text-gray-400 mt-0.5">JPG, PNG o WEBP — máx. 5 MB. Se recomienda resolución 1920×1080 o superior.</p>
        </div>
        <div class="px-5 py-5 space-y-4">

            <div>
                <input wire:model="imagen" type="file" accept="image/*"
                       class="block w-full text-sm text-gray-600 dark:text-gray-300
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-primary-50 file:text-primary-700
                              hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300
                              cursor-pointer">
                @error('imagen')
                <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Vista previa de la imagen seleccionada --}}
            @if($imagen)
            <div class="rounded-lg overflow-hidden h-36 border border-gray-200 dark:border-gray-700">
                <img src="{{ $imagen->temporaryUrl() }}" class="w-full h-full object-cover">
            </div>
            @endif

            <button wire:click="guardar"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
                    style="background-color:#BC955C;">
                <svg wire:loading wire:target="guardar" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <svg wire:loading.remove wire:target="guardar" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Guardar como fondo
            </button>
        </div>
    </div>


    {{-- ── Salvapantallas del pizarrón ── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Salvapantallas del pizarrón (calendario)</p>
            <p class="text-xs text-gray-400 mt-0.5">Se muestra en pantalla completa tras 30 min sin nuevos reportes ni eventos. Se cancela automáticamente cuando llega actividad.</p>
        </div>

        {{-- Vista previa actual --}}
        <div class="relative h-48 bg-gray-100 dark:bg-gray-900 flex items-center justify-center"
             style="{{ $salvapantallasActual ? 'background-image:url(\''.e($salvapantallasActual).'\');background-size:cover;background-position:center;' : '' }}">
            @if(!$salvapantallasActual)
            <div class="text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">Sin salvapantallas — la pantalla seguirá el ciclo normal</p>
            </div>
            @endif
        </div>
        @if($salvapantallasActual)
        <div class="px-5 py-3 flex justify-end border-t border-gray-200 dark:border-gray-700">
            <button wire:click="eliminarSalvapantallas"
                    wire:confirm="¿Eliminar el salvapantallas? El pizarrón seguirá su ciclo normal sin pausa."
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg
                           bg-danger-100 hover:bg-danger-200 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar salvapantallas
            </button>
        </div>
        @endif

        {{-- Upload --}}
        <div class="px-5 py-5 space-y-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG o WEBP — máx. 10 MB. Se mostrará en pantalla completa (horizontal), resolución 1920×1080 o superior recomendada.</p>

            <div>
                <input wire:model="imagenSalvapantallas" type="file" accept="image/*"
                       class="block w-full text-sm text-gray-600 dark:text-gray-300
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-primary-50 file:text-primary-700
                              hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300
                              cursor-pointer">
                @error('imagenSalvapantallas')
                <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            @if($imagenSalvapantallas)
            <div class="rounded-lg overflow-hidden h-36 border border-gray-200 dark:border-gray-700">
                <img src="{{ $imagenSalvapantallas->temporaryUrl() }}" class="w-full h-full object-cover">
            </div>
            @endif

            <button wire:click="guardarSalvapantallas"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
                    style="background-color:#BC955C;">
                <svg wire:loading wire:target="guardarSalvapantallas" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <svg wire:loading.remove wire:target="guardarSalvapantallas" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Guardar salvapantallas
            </button>
        </div>
    </div>

</div>

</x-filament-panels::page>

