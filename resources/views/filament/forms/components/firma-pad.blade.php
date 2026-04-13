<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div wire:ignore>
        <div
            x-data="firmaPad(@js($getStatePath()), @js($getState() ?? ''))"
            x-init="init()"
        >

            {{-- ── MODO VER ── --}}
            <div x-show="!editing">

                {{-- Con firma --}}
                <div x-show="dataUrl"
                     class="inline-block border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 p-3"
                     style="background-image:repeating-conic-gradient(#e5e7eb 0% 25%,transparent 0% 50%) 0 0/12px 12px;">
                    <img :src="dataUrl" alt="Firma" class="block h-16 w-auto" style="mix-blend-mode:multiply;">
                </div>

                {{-- Sin firma --}}
                <div x-show="!dataUrl"
                     class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 0l.172.172a2 2 0 010 2.828L12 16H9v-3z"/>
                    </svg>
                    Sin firma registrada
                </div>

                <button type="button"
                    @click="startEditing()"
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 0l.172.172a2 2 0 010 2.828L12 16H9v-3z"/>
                    </svg>
                    <span x-text="dataUrl ? 'Editar firma' : 'Agregar firma'"></span>
                </button>
            </div>

            {{-- ── MODO EDITAR ── --}}
            <div x-show="editing" class="space-y-3">

                {{-- Tabs --}}
                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-700 rounded-lg w-fit">
                    <button type="button"
                        @click="mode = 'draw'"
                        :class="mode === 'draw'
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">
                        ✏️ Dibujar
                    </button>
                    <button type="button"
                        @click="mode = 'upload'"
                        :class="mode === 'upload'
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">
                        🖼️ Subir imagen
                    </button>
                </div>

                {{-- Panel: Dibujar --}}
                <div x-show="mode === 'draw'">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 overflow-hidden">
                        <canvas
                            x-ref="canvas"
                            width="720"
                            height="240"
                            style="display:block; width:100%; aspect-ratio:3/1; touch-action:none; cursor:crosshair;"
                            @mousedown="startDraw($event)"
                            @mousemove="draw($event)"
                            @mouseup="endDraw()"
                            @mouseleave="endDraw()"
                            @touchstart.prevent="startDraw($event)"
                            @touchmove.prevent="draw($event)"
                            @touchend="endDraw()"
                        ></canvas>
                    </div>
                    <button type="button"
                        @click="clearCanvas()"
                        class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 underline">
                        Limpiar lienzo
                    </button>
                </div>

                {{-- Panel: Subir imagen --}}
                <div x-show="mode === 'upload'">
                    <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex flex-col items-center justify-center gap-1">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-primary-600 dark:text-primary-400">Clic para subir</span>
                                o arrastra un PNG/JPG
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Fondo transparente recomendado</p>
                        </div>
                        <input type="file" accept="image/png,image/jpeg,image/gif,image/webp"
                            class="hidden"
                            @change="handleUpload($event)">
                    </label>
                </div>

                {{-- Botón Listo --}}
                <div class="flex items-center gap-3">
                    <button type="button"
                        @click="editing = false"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-500 transition-colors">
                        Listo
                    </button>
                    <template x-if="dataUrl">
                        <button type="button"
                            @click="removeSignature()"
                            class="text-xs text-red-500 hover:text-red-700 underline">
                            Eliminar firma
                        </button>
                    </template>
                </div>

            </div>

        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script>
    function firmaPad(statePath, initialValue) {
        return {
            mode:        'draw',
            editing:     false,
            dataUrl:     initialValue || '',
            drawing:     false,
            strokeColor: '#1e3a8a',

            init() {
                // Sin setup aquí; el canvas se inicializa cuando se entra al modo editar.
            },

            startEditing() {
                this.editing = true;
                // $nextTick: espera a que x-show muestre el canvas
                this.$nextTick(() => {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    if (this.dataUrl && this.dataUrl.startsWith('data:')) {
                        const img = new Image();
                        img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        img.src = this.dataUrl;
                    }
                });
            },

            // scaleX/scaleY mapea coordenadas CSS al buffer (720×240)
            _getPos(e) {
                const canvas = this.$refs.canvas;
                const rect   = canvas.getBoundingClientRect();
                const src    = e.touches ? e.touches[0] : e;
                return {
                    x: (src.clientX - rect.left) * (canvas.width  / rect.width),
                    y: (src.clientY - rect.top)  * (canvas.height / rect.height),
                };
            },

            // lineWidth en unidades de buffer para que visualmente sean ~1.5 px CSS
            _lw() {
                const canvas = this.$refs.canvas;
                return (canvas.width / canvas.getBoundingClientRect().width) * 1.5;
            },

            startDraw(e) {
                const canvas = this.$refs.canvas;
                const ctx    = canvas.getContext('2d');
                const lw     = this._lw();
                const pos    = this._getPos(e);
                this.drawing = true;
                ctx.beginPath();
                ctx.arc(pos.x, pos.y, lw / 2, 0, Math.PI * 2);
                ctx.fillStyle = this.strokeColor;
                ctx.fill();
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            },

            draw(e) {
                if (!this.drawing) return;
                const canvas = this.$refs.canvas;
                const ctx    = canvas.getContext('2d');
                const pos    = this._getPos(e);
                ctx.strokeStyle = this.strokeColor;
                ctx.lineWidth   = this._lw();
                ctx.lineCap     = 'round';
                ctx.lineJoin    = 'round';
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            },

            endDraw() {
                if (!this.drawing) return;
                this.drawing = false;
                this.dataUrl = this.$refs.canvas.toDataURL('image/png');
                this._sync();
            },

            clearCanvas() {
                const canvas = this.$refs.canvas;
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                this.dataUrl = '';
                this._sync();
            },

            handleUpload(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.dataUrl = ev.target.result;
                    this._sync();
                };
                reader.readAsDataURL(file);
            },

            removeSignature() {
                this.dataUrl = '';
                const canvas = this.$refs.canvas;
                if (canvas) {
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                }
                this.editing = false;
                this._sync();
            },

            _sync() {
                @this.set(statePath, this.dataUrl);
            },
        };
    }
</script>
@endpush
