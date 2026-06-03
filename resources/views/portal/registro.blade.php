<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — Portal de Reportes HRAE</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            margin: auto;
        }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo h1 { font-size: 20px; font-weight: 700; color: #111; }
        .logo p { font-size: 13px; color: #6b7280; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        input[type=text], input[type=password], input[type=email] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }
        input:focus { border-color: #2563eb; }
        .error { font-size: 12px; color: #dc2626; margin-top: 4px; }
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #b91c1c;
            margin-bottom: 16px;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #1d4ed8;
            margin-bottom: 20px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.15s;
        }
        .btn:hover { background: #1e40af; }
        .btn:disabled { background: #93c5fd; cursor: not-allowed; }
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #6b7280;
        }
        .footer-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .footer-link a:hover { text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 20px 0; }
        [x-cloak] { display: none !important; }
        .section-label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-label span.required { color: #dc2626; }

        /* ── Firma pad ── */
        .firma-tabs {
            display: flex;
            gap: 4px;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 4px;
            width: fit-content;
            margin-bottom: 10px;
        }
        .firma-tab {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            background: transparent;
            color: #6b7280;
            transition: all 0.15s;
        }
        .firma-tab.active {
            background: white;
            color: #111;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .firma-canvas-wrap {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            background: white;
            overflow: hidden;
        }
        .firma-canvas-wrap canvas {
            display: block;
            width: 100%;
            aspect-ratio: 3 / 1;
            touch-action: none;
            cursor: crosshair;
        }
        .firma-clear {
            font-size: 12px;
            color: #9ca3af;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 4px;
            padding: 0;
        }
        .firma-clear:hover { color: #ef4444; }
        .firma-upload-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 112px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            background: white;
            transition: background 0.15s;
        }
        .firma-upload-zone:hover { background: #f9fafb; }
        .firma-upload-text { font-size: 13px; color: #6b7280; margin-top: 6px; }
        .firma-upload-text strong { color: #2563eb; }
        .firma-preview-wrap {
            display: inline-block;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            padding: 8px;
            background-image: repeating-conic-gradient(#f3f4f6 0% 25%, transparent 0% 50%) 0 0 / 10px 10px;
        }
        .firma-preview-wrap img { display: block; height: 56px; width: auto; mix-blend-mode: multiply; }
        .firma-edit-btn {
            font-size: 12px;
            color: #2563eb;
            background: none;
            border: none;
            cursor: pointer;
            margin-top: 6px;
            padding: 0;
            font-weight: 600;
        }
        .firma-edit-btn:hover { text-decoration: underline; }
        .firma-done-btn {
            padding: 7px 16px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.15s;
        }
        .firma-done-btn:hover { background: #1e40af; }
        .firma-remove-btn {
            font-size: 12px;
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            margin-top: 8px;
            margin-left: 10px;
            text-decoration: underline;
        }
        .firma-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
            display: none;
        }
        .firma-error.visible { display: block; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>Solicitud de acceso</h1>
            <p>Portal de Reportes — HRAE</p>
        </div>


        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.registro.submit') }}"
              x-data="registroForm()"
              @submit.prevent="submitForm($el)">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" autofocus>
                @error('nombre')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="numero_empleado">Número de empleado</label>
                <input type="text" id="numero_empleado" name="numero_empleado" value="{{ old('numero_empleado') }}">
                @error('numero_empleado')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="servicio">Servicio / Área</label>
                <input type="text" id="servicio" name="servicio" value="{{ old('servicio') }}" placeholder="Ej. Urgencias, UCI, Quirófano...">
                @error('servicio')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>

            {{-- ── Jefe de Servicio ── --}}
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;font-size:13px;color:#374151;">
                    <input type="checkbox" name="es_jefe_servicio" value="1"
                           x-model="esJefe"
                           {{ old('es_jefe_servicio') ? 'checked' : '' }}
                           style="width:18px;height:18px;accent-color:#1d4ed8;cursor:pointer;flex-shrink:0;">
                    Soy Jefe de Servicio
                </label>
            </div>

            <div class="form-group" x-show="esJefe" x-cloak style="margin-top:-8px;">
                <label for="area_jefe_servicio">Área de la que es Jefe de Servicio</label>
                <select id="area_jefe_servicio" name="area_jefe_servicio"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;color:#111;outline:none;background:white;">
                    <option value="">Seleccione área</option>
                    <option {{ old('area_jefe_servicio') === 'Audiología' ? 'selected' : '' }}>Audiología</option>
                    <option {{ old('area_jefe_servicio') === 'Anestesiología' ? 'selected' : '' }}>Anestesiología</option>
                    <option {{ old('area_jefe_servicio') === 'Banco de Sangre' ? 'selected' : '' }}>Banco de Sangre</option>
                    <option {{ old('area_jefe_servicio') === 'Banco de Leches' ? 'selected' : '' }}>Banco de Leches</option>
                    <option {{ old('area_jefe_servicio') === 'Cardiología' ? 'selected' : '' }}>Cardiología</option>
                    <option {{ old('area_jefe_servicio') === 'CEYE' ? 'selected' : '' }}>CEYE</option>
                    <option {{ old('area_jefe_servicio') === 'Cirugía Ambulatoria' ? 'selected' : '' }}>Cirugía Ambulatoria</option>
                    <option {{ old('area_jefe_servicio') === 'Cirugías' ? 'selected' : '' }}>Cirugías</option>
                    <option {{ old('area_jefe_servicio') === 'Clínica de catéter' ? 'selected' : '' }}>Clínica de catéter</option>
                    <option {{ old('area_jefe_servicio') === 'Clínica displacías' ? 'selected' : '' }}>Clínica displacías</option>
                    <option {{ old('area_jefe_servicio') === 'Consultorio pediatría' ? 'selected' : '' }}>Consultorio pediatría</option>
                    <option {{ old('area_jefe_servicio') === 'Consultorio ginecología' ? 'selected' : '' }}>Consultorio ginecología</option>
                    <option {{ old('area_jefe_servicio') === 'Crecimiento y desarrollo' ? 'selected' : '' }}>Crecimiento y desarrollo</option>
                    <option {{ old('area_jefe_servicio') === 'Cuidados intermedios' ? 'selected' : '' }}>Cuidados intermedios</option>
                    <option {{ old('area_jefe_servicio') === 'Dermatología' ? 'selected' : '' }}>Dermatología</option>
                    <option {{ old('area_jefe_servicio') === 'Dietología' ? 'selected' : '' }}>Dietología</option>
                    <option {{ old('area_jefe_servicio') === 'Endoscopia' ? 'selected' : '' }}>Endoscopia</option>
                    <option {{ old('area_jefe_servicio') === 'Farmacia' ? 'selected' : '' }}>Farmacia</option>
                    <option {{ old('area_jefe_servicio') === 'Ginecología y obstetricia' ? 'selected' : '' }}>Ginecología y obstetricia</option>
                    <option {{ old('area_jefe_servicio') === 'Hemodiálisis' ? 'selected' : '' }}>Hemodiálisis</option>
                    <option {{ old('area_jefe_servicio') === 'Hemodinamia' ? 'selected' : '' }}>Hemodinamia</option>
                    <option {{ old('area_jefe_servicio') === 'Imagenología' ? 'selected' : '' }}>Imagenología</option>
                    <option {{ old('area_jefe_servicio') === 'Inhaloterapia' ? 'selected' : '' }}>Inhaloterapia</option>
                    <option {{ old('area_jefe_servicio') === 'Laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                    <option {{ old('area_jefe_servicio') === 'Lactantes' ? 'selected' : '' }}>Lactantes</option>
                    <option {{ old('area_jefe_servicio') === 'Maxilofacial' ? 'selected' : '' }}>Maxilofacial</option>
                    <option {{ old('area_jefe_servicio') === 'Neonatología' ? 'selected' : '' }}>Neonatología</option>
                    <option {{ old('area_jefe_servicio') === 'Medicina interna' ? 'selected' : '' }}>Medicina interna</option>
                    <option {{ old('area_jefe_servicio') === 'Neurología' ? 'selected' : '' }}>Neurología</option>
                    <option {{ old('area_jefe_servicio') === 'Oncología adultos' ? 'selected' : '' }}>Oncología adultos</option>
                    <option {{ old('area_jefe_servicio') === 'Oncología pediátrica' ? 'selected' : '' }}>Oncología pediátrica</option>
                    <option {{ old('area_jefe_servicio') === 'Oftalmología' ? 'selected' : '' }}>Oftalmología</option>
                    <option {{ old('area_jefe_servicio') === 'Ortopedia' ? 'selected' : '' }}>Ortopedia</option>
                    <option {{ old('area_jefe_servicio') === 'Otorrinolaringología' ? 'selected' : '' }}>Otorrinolaringología</option>
                    <option {{ old('area_jefe_servicio') === 'Patología' ? 'selected' : '' }}>Patología</option>
                    <option {{ old('area_jefe_servicio') === 'Pediatría' ? 'selected' : '' }}>Pediatría</option>
                    <option {{ old('area_jefe_servicio') === 'Quemados' ? 'selected' : '' }}>Quemados</option>
                    <option {{ old('area_jefe_servicio') === 'Quirófano' ? 'selected' : '' }}>Quirófano</option>
                    <option {{ old('area_jefe_servicio') === 'Radioterapia' ? 'selected' : '' }}>Radioterapia</option>
                    <option {{ old('area_jefe_servicio') === 'Rehabilitación' ? 'selected' : '' }}>Rehabilitación</option>
                    <option {{ old('area_jefe_servicio') === 'Reumatología' ? 'selected' : '' }}>Reumatología</option>
                    <option {{ old('area_jefe_servicio') === 'Somatometría' ? 'selected' : '' }}>Somatometría</option>
                    <option {{ old('area_jefe_servicio') === 'Tococirugía' ? 'selected' : '' }}>Tococirugía</option>
                    <option {{ old('area_jefe_servicio') === 'Trasplantes' ? 'selected' : '' }}>Trasplantes</option>
                    <option {{ old('area_jefe_servicio') === 'UCIA' ? 'selected' : '' }}>UCIA</option>
                    <option {{ old('area_jefe_servicio') === 'UCIN' ? 'selected' : '' }}>UCIN</option>
                    <option {{ old('area_jefe_servicio') === 'UCIN aislados' ? 'selected' : '' }}>UCIN aislados</option>
                    <option {{ old('area_jefe_servicio') === 'UCIP' ? 'selected' : '' }}>UCIP</option>
                    <option {{ old('area_jefe_servicio') === 'Urgencias' ? 'selected' : '' }}>Urgencias</option>
                </select>
                @error('area_jefe_servicio')<p class="error">{{ $message }}</p>@enderror
            </div>

            {{-- ── Horario ── --}}
            <div class="form-group">
                <label>Horario de turno <span style="color:#dc2626">*</span></label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="flex:1;">
                        <label for="horario_inicio" style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:3px;">Entrada</label>
                        <input type="time" id="horario_inicio" name="horario_inicio"
                               value="{{ old('horario_inicio') }}"
                               style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;color:#111;outline:none;">
                    </div>
                    <span style="color:#9ca3af;font-size:18px;padding-top:18px;">—</span>
                    <div style="flex:1;">
                        <label for="horario_fin" style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:3px;">Salida</label>
                        <input type="time" id="horario_fin" name="horario_fin"
                               value="{{ old('horario_fin') }}"
                               style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;color:#111;outline:none;">
                    </div>
                </div>
                @error('horario_inicio')<p class="error">{{ $message }}</p>@enderror
                @error('horario_fin')<p class="error">{{ $message }}</p>@enderror
            </div>

            {{-- ── Firma ── --}}
            <div class="form-group">
                <div class="section-label">
                    Firma <span class="required">*</span>
                </div>
                <p style="font-size:12px;color:#6b7280;margin-bottom:10px;">
                    Dibuja tu firma con el mouse/trackpad o sube una imagen PNG.
                </p>

                {{-- Modo: ver firma guardada --}}
                <div x-show="!editing">
                    <template x-if="dataUrl">
                        <div>
                            <div class="firma-preview-wrap">
                                <img :src="dataUrl" alt="Tu firma">
                            </div>
                            <br>
                            <button type="button" class="firma-edit-btn" @click="startEditing()">✏️ Editar firma</button>
                        </div>
                    </template>
                    <template x-if="!dataUrl">
                        <div>
                            <button type="button" class="firma-edit-btn" @click="startEditing()"
                                    style="font-size:13px; color:#374151; border:2px dashed #d1d5db; padding:10px 20px; border-radius:8px; display:inline-block;">
                                ✏️ Agregar firma
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Modo: editar firma --}}
                <div x-show="editing">
                    {{-- Panel dibujar --}}
                    <div>
                        <div class="firma-canvas-wrap">
                            <canvas x-ref="canvas" width="720" height="240"
                                @mousedown="startDraw($event)"
                                @mousemove="draw($event)"
                                @mouseup="endDraw()"
                                @mouseleave="endDraw()"
                                @touchstart.prevent="startDraw($event)"
                                @touchmove.prevent="draw($event)"
                                @touchend="endDraw()">
                            </canvas>
                        </div>
                        <button type="button" class="firma-clear" @click="clearCanvas()">Limpiar lienzo</button>
                    </div>

                    {{-- Botones listo / eliminar --}}
                    <div style="display:flex; align-items:center; margin-top:8px;">
                        <button type="button" class="firma-done-btn" @click="editing = false">Listo</button>
                        <template x-if="dataUrl">
                            <button type="button" class="firma-remove-btn" @click="removeSignature()">Eliminar firma</button>
                        </template>
                    </div>
                </div>

                {{-- Hidden input + error --}}
                <input type="hidden" name="firma" :value="dataUrl">
                <p class="firma-error" :class="{ visible: firmaError }" id="firma-error-msg">
                    Dibuja tu firma para continuar.
                </p>
                @error('firma')<p class="error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn">Registrar nuevo reportante</button>
        </form>
    </div>

    <script>
    function registroForm() {
        return {
            mode: 'draw',
            editing: false,
            dataUrl: '',
            drawing: false,
            firmaError: false,
            strokeColor: '#1e3a8a',
            esJefe: {{ old('es_jefe_servicio') ? 'true' : 'false' }},

            startEditing() {
                this.editing = true;
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

            _getPos(e) {
                const canvas = this.$refs.canvas;
                const rect   = canvas.getBoundingClientRect();
                const src    = e.touches ? e.touches[0] : e;
                return {
                    x: (src.clientX - rect.left) * (canvas.width  / rect.width),
                    y: (src.clientY - rect.top)  * (canvas.height / rect.height),
                };
            },

            _lw() {
                const canvas = this.$refs.canvas;
                return (canvas.width / canvas.getBoundingClientRect().width) * 1.5;
            },

            startDraw(e) {
                const canvas = this.$refs.canvas;
                const ctx    = canvas.getContext('2d');
                const pos    = this._getPos(e);
                this.drawing = true;
                ctx.beginPath();
                ctx.arc(pos.x, pos.y, this._lw() / 2, 0, Math.PI * 2);
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
            },

            clearCanvas() {
                const canvas = this.$refs.canvas;
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                this.dataUrl = '';
            },

            removeSignature() {
                this.dataUrl = '';
                const canvas = this.$refs.canvas;
                if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                this.editing = false;
            },

            submitForm(form) {
                if (!this.dataUrl) {
                    this.firmaError = true;
                    document.getElementById('firma-error-msg').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                this.firmaError = false;
                form.submit();
            },
        };
    }
    </script>
</body>
</html>
