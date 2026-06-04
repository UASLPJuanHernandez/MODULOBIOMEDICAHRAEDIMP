<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar documento — HRAE</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        /* ── Top bar ── */
        .topbar { display: flex; align-items: center; gap: 12px; padding: 10px 20px; background: white; border-bottom: 1.5px solid #e5e7eb; flex-shrink: 0; }
        .back-btn { background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .back-btn:hover { background: #f3f4f6; }
        .topbar-title { font-size: 15px; font-weight: 700; color: #111; }
        .topbar-sub { font-size: 12px; color: #9ca3af; }

        /* ── Instrucción flotante ── */
        #place-hint {
            display: none;
            position: fixed; top: 70px; left: 50%; transform: translateX(-50%);
            background: #1d4ed8; color: white;
            padding: 10px 20px; border-radius: 999px;
            font-size: 13px; font-weight: 600;
            box-shadow: 0 4px 20px rgba(29,78,216,.4);
            z-index: 200; pointer-events: none;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.7; } }

        /* ── Main layout ── */
        .main { display: flex; flex: 1; overflow: hidden; }

        /* ── PDF área ── */
        .pdf-area {
            flex: 1; overflow-y: auto; background: #4b5563;
            cursor: crosshair;
            position: relative;
        }
        .pdf-area.firma-colocada { cursor: default; }

        #pdf-pages {
            padding: 24px;
            display: flex; flex-direction: column; align-items: center; gap: 16px;
        }

        /* Cada página */
        .pdf-pg {
            position: relative;
            box-shadow: 0 4px 24px rgba(0,0,0,.4);
            background: white;
            flex-shrink: 0;
        }
        .pdf-pg canvas { display: block; }

        /* Overlay de campos (solo lectura) */
        .pdf-pg .campo-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
        }
        .pdf-campo-val {
            position: absolute;
            font-size: 11pt; color: #111;
            padding: 2px 4px;
            white-space: pre-wrap; word-break: break-word; line-height: 1.4;
            box-sizing: border-box;
        }
        .pdf-campo-firma {
            position: absolute;
            object-fit: contain;
            mix-blend-mode: multiply;
        }

        /* Firma arrastrable del jefe */
        #firma-drag {
            position: absolute;
            cursor: grab;
            z-index: 100;
            border: 2px dashed #1d4ed8;
            border-radius: 4px;
            background: rgba(255,255,255,.85);
            padding: 4px;
            display: none;
            user-select: none;
            width: 148px;
            height: 63px;
        }
        #firma-drag.dragging { cursor: grabbing; opacity: .85; }
        #firma-drag img { display: block; width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
        #firma-drag .drag-hint {
            position: absolute; top: -22px; left: 50%; transform: translateX(-50%);
            background: #1d4ed8; color: white; font-size: 10px; font-weight: 700;
            padding: 2px 8px; border-radius: 4px; white-space: nowrap;
        }
        #resize-handle {
            position: absolute; bottom: 0; right: 0;
            width: 14px; height: 14px;
            background: #1d4ed8; border-radius: 2px 0 4px 0;
            cursor: nwse-resize;
        }
        #resize-handle::after {
            content: ''; position: absolute; right: 2px; bottom: 2px;
            width: 6px; height: 6px;
            border-right: 2px solid white; border-bottom: 2px solid white;
        }

        /* ── Sidebar ── */
        .sidebar { width: 290px; flex-shrink: 0; background: white; border-left: 1.5px solid #e5e7eb; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-section { padding: 18px 20px; border-bottom: 1.5px solid #f3f4f6; }
        .sidebar-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 10px; }
        .info-row { display: flex; flex-direction: column; gap: 2px; margin-bottom: 8px; }
        .info-key { font-size: 11px; color: #9ca3af; }
        .info-val { font-size: 13px; font-weight: 600; color: #111; }
        .tipo-pill { display: inline-block; font-size: 11px; font-weight: 700; text-transform: capitalize; padding: 3px 10px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; }

        /* Firma preview */
        .firma-preview { background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px; text-align: center; }
        .firma-preview img { max-height: 80px; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; }
        .firma-preview p { font-size: 11px; color: #9ca3af; margin-top: 6px; }

        /* Instrucción en sidebar */
        .step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .step-num { flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; background: #1d4ed8; color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .step-txt { font-size: 12px; color: #4b5563; line-height: 1.5; }

        /* Botón confirmar */
        #btn-confirmar {
            display: block; width: 100%; padding: 12px;
            background: #1d4ed8; color: white;
            border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700;
            cursor: pointer; text-align: center; transition: background .15s;
        }
        #btn-confirmar:hover:not(:disabled) { background: #1e40af; }
        #btn-confirmar:disabled { background: #93c5fd; cursor: not-allowed; }
        #btn-confirmar.ready { background: #16a34a; }
        #btn-confirmar.ready:hover { background: #15803d; }

        .status-txt { font-size: 12px; color: #6b7280; text-align: center; margin-top: 8px; }
        .status-txt.ok { color: #16a34a; font-weight: 600; }

        .badge-firmado { display: flex; align-items: center; justify-content: center; gap: 8px; background: #d1fae5; color: #065f46; font-size: 13px; font-weight: 700; padding: 12px; border-radius: 10px; }

        /* Error message */
        .err-msg { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 12px; padding: 8px 12px; border-radius: 8px; margin-top: 8px; }
    </style>
</head>
<body>

@php
    $contenido = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
    $campos    = $contenido['campos']  ?? [];
    $valores   = $contenido['valores'] ?? [];

    // Convertir firma a data URL si es SVG crudo
    $firmaPersonal = $personal->firma ?? '';
    $firmaDataUrl  = '';
    if ($firmaPersonal) {
        if (str_starts_with($firmaPersonal, 'data:')) {
            $firmaDataUrl = $firmaPersonal;
        } else {
            $svgEnc = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 70" width="200" height="70">' . $firmaPersonal . '</svg>');
            $firmaDataUrl = 'data:image/svg+xml;charset=utf-8,' . $svgEnc;
        }
    }
@endphp

{{-- Instrucción flotante --}}
<div id="place-hint">Haz clic en el documento para colocar tu firma</div>

<div class="topbar">
    <a href="{{ route('portal.firmas') }}" class="back-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver
    </a>
    <div>
        <p class="topbar-title">Firmar documento</p>
        <p class="topbar-sub">{{ $registro->identificador ?: 'Registro #' . $registro->id }} — {{ $registro->formato?->nombre }}</p>
    </div>
</div>

<div class="main">

    {{-- ── PDF área ── --}}
    <div class="pdf-area" id="pdf-area">
        <div id="pdf-pages">
            <p style="color:rgba(255,255,255,.6);font-size:14px;">Cargando documento…</p>
        </div>
        {{-- Firma arrastrable (se mueve dentro de pdf-pages) --}}
        <div id="firma-drag">
            <div class="drag-hint">Arrastra · Esquina para redimensionar</div>
            <img src="{{ $firmaDataUrl }}" alt="Tu firma">
            <div id="resize-handle"></div>
        </div>
    </div>

    {{-- ── Sidebar ── --}}
    <div class="sidebar">

        {{-- Info del documento --}}
        <div class="sidebar-section">
            <p class="sidebar-label">Documento</p>
            <div class="info-row">
                <span class="info-key">Tipo</span>
                <span><span class="tipo-pill">{{ ucfirst($registro->tipo_documento ?? '—') }}</span></span>
            </div>
            <div class="info-row">
                <span class="info-key">Identificador</span>
                <span class="info-val">{{ $registro->identificador ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Formato</span>
                <span class="info-val">{{ $registro->formato?->nombre ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Generado por</span>
                <span class="info-val">{{ $registro->usuario?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Fecha</span>
                <span class="info-val">{{ $registro->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Tu firma --}}
        <div class="sidebar-section">
            <p class="sidebar-label">Tu firma</p>
            @if($firmaDataUrl)
            <div class="firma-preview">
                <img src="{{ $firmaDataUrl }}" alt="Firma">
                <p>{{ $personal->nombre }}</p>
            </div>
            @else
            <p style="font-size:13px;color:#9ca3af;">No tienes firma registrada.</p>
            @endif
        </div>

        {{-- Instrucciones / Acción --}}
        <div class="sidebar-section" style="flex:1;">
            @if($registro->estado === 'culminado')
            <div class="badge-firmado">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Documento firmado
            </div>
            <p style="font-size:12px;color:#9ca3af;margin-top:8px;text-align:center;">
                Firmado el {{ $registro->firmado_at?->format('d/m/Y H:i') }}
            </p>

            @elseif(!$firmaDataUrl)
            <p style="font-size:13px;color:#ef4444;font-weight:600;">
                No puedes firmar porque no tienes firma registrada. Actualiza tu perfil.
            </p>

            @else
            <p class="sidebar-label">Cómo firmar</p>
            <div class="step">
                <div class="step-num">1</div>
                <p class="step-txt">Haz clic en cualquier parte del documento donde quieras colocar tu firma.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <p class="step-txt">Arrastra la firma para ajustar su posición.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <p class="step-txt">Haz clic en "Confirmar firma".</p>
            </div>

            <div style="margin-top:16px;">
                <form id="firma-form" method="POST" action="{{ route('portal.documentos.firmar', $registro) }}">
                    @csrf
                    <input type="hidden" name="firma_posicion" id="firma-posicion-input" value="">
                    <button type="button" id="btn-confirmar" disabled>
                        Coloca la firma en el documento
                    </button>
                    <p class="status-txt" id="status-txt">Haz clic en el PDF para empezar.</p>

                    @if($errors->has('firma_posicion'))
                    <div class="err-msg">{{ $errors->first('firma_posicion') }}</div>
                    @endif
                </form>
            </div>
            @endif
        </div>

    </div>
</div>

<script>
(function () {
    var WORKER_SRC  = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var SCALE       = 1.5;
    var PDF_URL     = @json(route('portal.documentos.template', $registro));
    var CAMPOS      = @json($campos);
    var VALORES     = @json((object)$valores);
    var FIRMA_SRC   = @json($firmaDataUrl);
    var FIRMA_W_PCT = 18;   // % del ancho de página por defecto

    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_SRC;

    var pdfArea    = document.getElementById('pdf-area');
    var pdfPages   = document.getElementById('pdf-pages');
    var firmaDrag  = document.getElementById('firma-drag');
    var btnConf    = document.getElementById('btn-confirmar');
    var statusTxt  = document.getElementById('status-txt');
    var posInput   = document.getElementById('firma-posicion-input');
    var placeHint  = document.getElementById('place-hint');
    var firmaForm  = document.getElementById('firma-form');

    var firmaColocada  = false;
    var manualResized  = false;
    var isDragging     = false;
    var dragStartX, dragStartY, dragStartLeft, dragStartTop;

    /* ── Cargar y renderizar PDF (páginas secuenciales para garantizar orden correcto) ── */
    pdfjsLib.getDocument({ url: PDF_URL, withCredentials: true }).promise
        .then(function (doc) {
            pdfPages.innerHTML = '';
            var chain = Promise.resolve();
            for (var i = 1; i <= doc.numPages; i++) {
                (function (num) {
                    chain = chain.then(function () { return renderPage(doc, num); });
                })(i);
            }
            return chain;
        })
        .then(function () {
            /* Después de renderizar: mover firma-drag dentro de pdfPages para que
               haga scroll junto con el contenido y la posición sea coherente */
            if (FIRMA_SRC && firmaDrag) {
                pdfPages.appendChild(firmaDrag);
                placeHint.style.display = 'block';
                pdfArea.addEventListener('click', onPdfClick);
            }
        })
        .catch(function (err) {
            pdfPages.innerHTML = '<p style="color:#fca5a5;padding:24px;">Error al cargar el PDF: ' + err.message + '</p>';
        });

    function renderPage(doc, num) {
        return doc.getPage(num).then(function (page) {
            var vp     = page.getViewport({ scale: SCALE });
            var pg     = document.createElement('div');
            pg.className     = 'pdf-pg';
            pg.dataset.page  = num;
            pg.dataset.pw    = vp.width + ',' + vp.height;
            pg.style.width   = vp.width  + 'px';
            pg.style.height  = vp.height + 'px';

            var canvas    = document.createElement('canvas');
            canvas.width  = vp.width;
            canvas.height = vp.height;
            pg.appendChild(canvas);

            /* overlay de campos */
            var ov = document.createElement('div');
            ov.className = 'campo-overlay';
            pg.appendChild(ov);

            pdfPages.appendChild(pg);

            return page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise
                .then(function () { renderCamposPagina(pg, num, vp.width, vp.height); });
        });
    }

    function renderCamposPagina(pg, pageNum, pwW, pwH) {
        // PDF es el template crudo — todos los valores se renderizan aquí (igual que el visor admin).
        CAMPOS.forEach(function (c) {
            if (parseInt(c.page) !== pageNum) return;

            var xPx = c.x / 100 * pwW;
            var yPx = (c.y / 100 * pwH) + 4;
            var wPx = c.w / 100 * pwW;
            var hPx = Math.max(c.h / 100 * pwH, 24);

            var esFirma = c.tipo === 'firma' || c.tipo === 'firma_jefe';
            var valor   = (VALORES && VALORES[c.id]) ? VALORES[c.id] : '';

            if (esFirma) {
                if (!valor) return;
                var img = document.createElement('img');
                img.src = valor;
                img.style.cssText =
                    'position:absolute;left:' + xPx + 'px;top:' + yPx + 'px;' +
                    'width:' + wPx + 'px;height:' + hPx + 'px;' +
                    'object-fit:contain;mix-blend-mode:multiply;pointer-events:none;';
                pg.querySelector('.campo-overlay').appendChild(img);
            } else {
                var div = document.createElement('div');
                div.style.cssText =
                    'position:absolute;left:' + xPx + 'px;top:' + yPx + 'px;' +
                    'width:' + wPx + 'px;min-height:' + hPx + 'px;' +
                    'font-size:11pt;color:#111;padding:2px 4px;' +
                    'white-space:pre-wrap;word-break:break-word;line-height:1.4;' +
                    'pointer-events:none;box-sizing:border-box;';
                div.textContent = valor;
                pg.querySelector('.campo-overlay').appendChild(div);
            }
        });
    }

    /* ── Clic en PDF para colocar firma ── */
    function onPdfClick(e) {
        /* Ignorar si el clic fue sobre la firma ya colocada */
        if (firmaDrag.style.display !== 'none' && firmaDrag.contains(e.target)) return;

        /* Encontrar la página más cercana al clic */
        var pgs   = document.querySelectorAll('.pdf-pg');
        var clickX = e.clientX;
        var clickY = e.clientY;

        var bestPg = null;
        var bestDist = Infinity;
        pgs.forEach(function (pg) {
            var r = pg.getBoundingClientRect();
            var inX = clickX >= r.left && clickX <= r.right;
            var inY = clickY >= r.top  && clickY <= r.bottom;
            if (inX && inY) {
                bestPg   = pg;
                bestDist = 0;
            } else if (bestDist > 0) {
                var dx = Math.max(r.left - clickX, 0, clickX - r.right);
                var dy = Math.max(r.top  - clickY, 0, clickY - r.bottom);
                var d  = Math.sqrt(dx*dx + dy*dy);
                if (d < bestDist) { bestDist = d; bestPg = pg; }
            }
        });

        if (!bestPg) return;

        var pgRect = bestPg.getBoundingClientRect();
        var pagesRect = pdfPages.getBoundingClientRect();

        // Auto-tamaño proporcional a la imagen real (solo la primera vez, no si el usuario redimensionó)
        if (!manualResized) {
            var fImg = firmaDrag.querySelector('img');
            var natW = (fImg && fImg.naturalWidth)  || 200;
            var natH = (fImg && fImg.naturalHeight) || 70;
            var wPx  = Math.min(pgRect.width * 0.20, natW);
            var hPx  = wPx * (natH / natW);
            firmaDrag.style.width  = wPx + 'px';
            firmaDrag.style.height = hPx + 'px';
        }

        var leftInPages = (clickX - pagesRect.left) - (firmaDrag.offsetWidth  / 2);
        var topInPages  = (clickY - pagesRect.top)  - (firmaDrag.offsetHeight / 2);

        /* Mostrar la firma ahí */
        firmaDrag.style.display = 'block';
        firmaDrag.style.left    = leftInPages + 'px';
        firmaDrag.style.top     = topInPages  + 'px';

        /* Quitar cursor crosshair del área */
        pdfArea.classList.add('firma-colocada');
        placeHint.style.display = 'none';

        marcarFirmaColocada(bestPg);
    }

    function marcarFirmaColocada(pg) {
        firmaColocada = true;

        /* Calcular posición relativa a la página */
        var dims = (pg.dataset.pw || '0,0').split(',');
        var pwW  = parseFloat(dims[0]);
        var pwH  = parseFloat(dims[1]);

        var pgRect  = pg.getBoundingClientRect();
        var fdRect  = firmaDrag.getBoundingClientRect();

        /* Coordenadas relativas a la página en % (ambos rects son viewport coords
           que se desplazan igual al hacer scroll, la diferencia es invariante) */
        var xPct = ((fdRect.left - pgRect.left) / pgRect.width)  * 100;
        var yPct = ((fdRect.top  - pgRect.top)  / pgRect.height) * 100;
        var wPct = (firmaDrag.offsetWidth  / pgRect.width)  * 100;
        var hPct = (firmaDrag.offsetHeight / pgRect.height) * 100;

        var posData = JSON.stringify({
            page:     parseInt(pg.dataset.page),
            x:        xPct,
            y:        yPct,
            w:        wPct,
            h:        hPct,
            firma_svg: FIRMA_SRC,
        });
        posInput.value = posData;

        /* Habilitar botón */
        btnConf.disabled = false;
        btnConf.textContent = 'Confirmar firma';
        btnConf.classList.add('ready');
        if (statusTxt) {
            statusTxt.textContent = 'Firma colocada — puedes arrastrarla para ajustar.';
            statusTxt.className = 'status-txt ok';
        }
    }

    /* ── Drag + Resize de la firma arrastrable ── */
    var isResizing = false;
    var resStartX, resStartY, resStartW, resStartH;
    var resizeHandle = document.getElementById('resize-handle');

    if (firmaDrag) {
        firmaDrag.addEventListener('mousedown', function (e) {
            if (e.target === resizeHandle) return; /* el handle lo gestiona su propio listener */
            e.stopPropagation();
            isDragging    = true;
            dragStartX    = e.clientX;
            dragStartY    = e.clientY;
            dragStartLeft = parseFloat(firmaDrag.style.left) || 0;
            dragStartTop  = parseFloat(firmaDrag.style.top)  || 0;
            firmaDrag.classList.add('dragging');
            e.preventDefault();
        });

        firmaDrag.addEventListener('touchstart', function (e) {
            if (e.target === resizeHandle) return;
            e.stopPropagation();
            var t = e.touches[0];
            isDragging    = true;
            dragStartX    = t.clientX;
            dragStartY    = t.clientY;
            dragStartLeft = parseFloat(firmaDrag.style.left) || 0;
            dragStartTop  = parseFloat(firmaDrag.style.top)  || 0;
            firmaDrag.classList.add('dragging');
        }, { passive: true });
    }

    if (resizeHandle) {
        resizeHandle.addEventListener('mousedown', function (e) {
            e.stopPropagation();
            e.preventDefault();
            isResizing = true; manualResized = true;
            resStartX  = e.clientX;
            resStartY  = e.clientY;
            resStartW  = firmaDrag.offsetWidth;
            resStartH  = firmaDrag.offsetHeight;
        });

        resizeHandle.addEventListener('touchstart', function (e) {
            e.stopPropagation();
            var t = e.touches[0];
            isResizing = true; manualResized = true;
            resStartX  = t.clientX;
            resStartY  = t.clientY;
            resStartW  = firmaDrag.offsetWidth;
            resStartH  = firmaDrag.offsetHeight;
        }, { passive: false });
    }

    document.addEventListener('mousemove', function (e) {
        if (isResizing) {
            firmaDrag.style.width  = Math.max(60, resStartW + e.clientX - resStartX) + 'px';
            firmaDrag.style.height = Math.max(24, resStartH + e.clientY - resStartY) + 'px';
            if (firmaColocada) actualizarPosicion();
            return;
        }
        if (!isDragging) return;
        var dx = e.clientX - dragStartX;
        var dy = e.clientY - dragStartY;
        firmaDrag.style.left = (dragStartLeft + dx) + 'px';
        firmaDrag.style.top  = (dragStartTop  + dy) + 'px';
        actualizarPosicion();
    });

    document.addEventListener('touchmove', function (e) {
        var t = e.touches[0];
        if (isResizing) {
            firmaDrag.style.width  = Math.max(60, resStartW + t.clientX - resStartX) + 'px';
            firmaDrag.style.height = Math.max(24, resStartH + t.clientY - resStartY) + 'px';
            if (firmaColocada) actualizarPosicion();
            return;
        }
        if (!isDragging) return;
        var dx = t.clientX - dragStartX;
        var dy = t.clientY - dragStartY;
        firmaDrag.style.left = (dragStartLeft + dx) + 'px';
        firmaDrag.style.top  = (dragStartTop  + dy) + 'px';
        actualizarPosicion();
    }, { passive: true });

    document.addEventListener('mouseup',  function () { isDragging = false; isResizing = false; firmaDrag && firmaDrag.classList.remove('dragging'); });
    document.addEventListener('touchend', function () { isDragging = false; isResizing = false; firmaDrag && firmaDrag.classList.remove('dragging'); });

    function actualizarPosicion() {
        if (!firmaColocada) return;

        /* Determinar sobre qué página está la firma ahora */
        var fdRect = firmaDrag.getBoundingClientRect();
        var fdCX   = fdRect.left + fdRect.width  / 2;
        var fdCY   = fdRect.top  + fdRect.height / 2;

        var pgs = document.querySelectorAll('.pdf-pg');
        var targetPg = null;
        pgs.forEach(function (pg) {
            var r = pg.getBoundingClientRect();
            if (fdCX >= r.left && fdCX <= r.right && fdCY >= r.top && fdCY <= r.bottom) {
                targetPg = pg;
            }
        });
        if (!targetPg) {
            /* Usar la primera página si no está sobre ninguna */
            targetPg = pgs[0];
        }
        if (!targetPg) return;

        marcarFirmaColocada(targetPg);
    }

    /* ── Confirmar ── */
    if (btnConf) {
        btnConf.addEventListener('click', function () {
            if (!firmaColocada || !posInput.value) {
                alert('Coloca tu firma en el documento primero.');
                return;
            }
            /* Actualizar posición final antes de enviar */
            actualizarPosicion();

            if (firmaForm) firmaForm.submit();
        });
    }

    /* ── Posicionar firma relativa a #pdf-pages (necesario para absolute correcto) ── */
    pdfPages.style.position = 'relative';

})();
</script>

</body>
</html>
