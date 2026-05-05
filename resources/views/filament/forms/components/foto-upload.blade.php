<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div wire:ignore>
        <div
            @php
        $fotoInit = $getState() ?? '';
        if ($fotoInit && !str_starts_with($fotoInit, 'data:')) {
            $fotoInit = \Illuminate\Support\Facades\Storage::url($fotoInit);
        }
    @endphp
    x-data="fotoUpload(@js($getStatePath()), @js($fotoInit))"
            x-init="init()"
        >

            {{-- Vista previa --}}
            <div class="flex items-start gap-5">

                {{-- Cuadro de foto --}}
                <div style="width:120px; height:120px; border-radius:10px; overflow:hidden; border:2px solid #e5e7eb; background:#f9fafb; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                    <template x-if="dataUrl">
                        <img :src="dataUrl" alt="Foto" style="width:100%; height:100%; object-fit:cover;" />
                    </template>
                    <template x-if="!dataUrl">
                        <svg style="width:36px; height:36px; color:#d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </template>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col gap-2 pt-1">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg bg-primary-600 hover:bg-primary-500 px-3 py-1.5 text-sm font-medium text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="dataUrl ? 'Cambiar foto' : 'Subir foto'"></span>
                        <input type="file" accept="image/png,image/jpeg,image/webp,image/gif"
                            class="hidden"
                            @change="handleFile($event)">
                    </label>

                    <template x-if="dataUrl">
                        <button type="button"
                            @click="remove()"
                            class="inline-flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Eliminar foto
                        </button>
                    </template>

                    <p class="text-xs text-gray-400">JPG, PNG, WebP · Máx. 5 MB</p>
                </div>
            </div>

        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script>
function fotoUpload(statePath, initialValue) {
    return {
        dataUrl: initialValue || '',

        init() {},

        handleFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                alert('La imagen no puede superar 5 MB.');
                e.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (ev) => {
                // Redimensiona a máx 600×600 antes de guardar
                const img = new Image();
                img.onload = () => {
                    const MAX = 600;
                    let w = img.width, h = img.height;
                    if (w > MAX || h > MAX) {
                        if (w > h) { h = Math.round(h * MAX / w); w = MAX; }
                        else       { w = Math.round(w * MAX / h); h = MAX; }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width  = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    this.dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    this._sync();
                };
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        },

        remove() {
            this.dataUrl = '';
            this._sync();
        },

        _sync() {
            @this.set(statePath, this.dataUrl);
        },
    };
}
</script>
@endpush
