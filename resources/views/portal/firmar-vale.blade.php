<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar vale — HRAE</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .topbar { display: flex; align-items: center; gap: 12px; padding: 10px 20px; background: white; border-bottom: 1.5px solid #e5e7eb; flex-shrink: 0; }
        .back-btn { background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .back-btn:hover { background: #f3f4f6; }
        .topbar-title { font-size: 15px; font-weight: 700; color: #111; }
        .topbar-sub { font-size: 12px; color: #9ca3af; }

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

        .main { display: flex; flex: 1; overflow: hidden; }

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

        .pdf-pg {
            position: relative;
            box-shadow: 0 4px 24px rgba(0,0,0,.4);
            background: white;
            flex-shrink: 0;
        }
        .pdf-pg canvas { display: block; }

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

        .sidebar { width: 290px; flex-shrink: 0; background: white; border-left: 1.5px solid #e5e7eb; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-section { padding: 18px 20px; border-bottom: 1.5px solid #f3f4f6; }
        .sidebar-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 10px; }
        .info-row { display: flex; flex-direction: column; gap: 2px; margin-bottom: 8px; }
        .info-key { font-size: 11px; color: #9ca3af; }
        .info-val { font-size: 13px; font-weight: 600; color: #111; }

        .firma-preview { background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px; text-align: center; }
        .firma-preview img { max-height: 80px; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; }
        .firma-preview p { font-size: 11px; color: #9ca3af; margin-top: 6px; }

        .step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .step-num { flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; background: #1d4ed8; color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .step-txt { font-size: 12px; color: #4b5563; line-height: 1.5; }

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
        .err-msg { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 12px; padding: 8px 12px; border-radius: 8px; margin-top: 8px; }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

@php
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
    $pdfUrl   = route('portal.vales.pdf', $vale);
    $yaFirmado = $vale->estado === 'culminado';
@endphp

<div id="place-hint">Haz clic en el documento para colocar tu firma</div>

<div class="topbar">
    <a href="{{ route('portal.firmas') }}" class="back-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver
    </a>
    <div>
        <p class="topbar-title">Firmar vale</p>
        <p class="topbar-sub">{{ $personal->nombre }} — {{ $personal->area_jefe_servicio }}</p>
    </div>
</div>

<div class="main">

    {{-- ── PDF área ── --}}
    <div class="pdf-area" id="pdf-area">
        @if($yaFirmado)
        <iframe src="{{ $pdfUrl }}" title="Documento firmado" style="width:100%;height:100%;border:none;display:block;"></iframe>
        @else
        <div id="pdf-pages">
            <p style="color:rgba(255,255,255,.6);font-size:14px;">Cargando documento…</p>
        </div>
        <div id="firma-drag">
            <div class="drag-hint">Arrastra · Esquina para redimensionar</div>
            <img src="{{ $firmaDataUrl }}" alt="Tu firma">
            <div id="resize-handle"></div>
        </div>
        @endif
    </div>

    {{-- ── Sidebar ── --}}
    <div class="sidebar">

        <div class="sidebar-section">
            <p class="sidebar-label">Vale</p>
            <div class="info-row">
                <span class="info-key">Tipo</span>
                <span class="info-val">{{ $vale->tipo === 'entrega' ? 'Vale de Entrega' : 'Vale de Retiro' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Equipo</span>
                <span class="info-val">{{ $vale->equipo_nombre ?: '—' }}</span>
            </div>
            @if($vale->numero_inventario)
            <div class="info-row">
                <span class="info-key">No. Inventario</span>
                <span class="info-val">{{ $vale->numero_inventario }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-key">Área</span>
                <span class="info-val">{{ $vale->area ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Recibido</span>
                <span class="info-val">{{ $vale->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

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

        <div class="sidebar-section" style="flex:1;">
            @if($yaFirmado)
            <div class="badge-firmado">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Vale firmado
            </div>
            <p style="font-size:12px;color:#9ca3af;margin-top:8px;text-align:center;">
                Firmado el {{ $vale->firmado_at?->format('d/m/Y H:i') }}
            </p>
            <a href="{{ $pdfUrl }}" target="_blank" download
               style="display:block;margin-top:12px;padding:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;text-align:center;font-size:13px;font-weight:600;color:#16a34a;text-decoration:none;">
                Descargar PDF
            </a>

            @elseif(!$firmaDataUrl)
            <p style="font-size:13px;color:#ef4444;font-weight:600;">
                No puedes firmar porque no tienes firma registrada. Actualiza tu perfil.
            </p>

            @else
            <p class="sidebar-label">Cómo firmar</p>
            <div class="step">
                <div class="step-num">1</div>
                <p class="step-txt">Haz clic en cualquier parte del documento para colocar tu firma.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <p class="step-txt">Arrastra para mover o arrastra la esquina azul para redimensionar.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <p class="step-txt">Haz clic en "Confirmar firma".</p>
            </div>

            <div style="margin-top:16px;">
                <form id="firma-form" method="POST" action="{{ route('portal.vales.firmar', $vale) }}">
                    @csrf
                    <input type="hidden" name="firma_data" value="{{ $firmaDataUrl }}">
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

@if(!$yaFirmado && $firmaDataUrl)
<script>
(function () {
    var WORKER_SRC = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var SCALE      = 1.5;
    var PDF_URL    = @json($pdfUrl);
    var FIRMA_SRC  = @json($firmaDataUrl);

    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_SRC;

    var pdfArea      = document.getElementById('pdf-area');
    var pdfPages     = document.getElementById('pdf-pages');
    var firmaDrag    = document.getElementById('firma-drag');
    var btnConf      = document.getElementById('btn-confirmar');
    var statusTxt    = document.getElementById('status-txt');
    var posInput     = document.getElementById('firma-posicion-input');
    var placeHint    = document.getElementById('place-hint');
    var firmaForm    = document.getElementById('firma-form');
    var resizeHandle = document.getElementById('resize-handle');

    var firmaColocada = false;
    var isDragging = false, isResizing = false;
    var dragStartX, dragStartY, dragStartLeft, dragStartTop;
    var resStartX, resStartY, resStartW, resStartH;

    /* ── Cargar PDF ── */
    pdfjsLib.getDocument({ url: PDF_URL, withCredentials: true }).promise
        .then(function (doc) {
            pdfPages.innerHTML = '';
            var renders = [];
            for (var i = 1; i <= doc.numPages; i++) renders.push(renderPage(doc, i));
            return Promise.all(renders);
        })
        .then(function () {
            pdfPages.appendChild(firmaDrag);
            placeHint.style.display = 'block';
            pdfArea.addEventListener('click', onPdfClick);
        })
        .catch(function (err) {
            pdfPages.innerHTML = '<p style="color:#fca5a5;padding:24px;">Error al cargar el PDF: ' + err.message + '</p>';
        });

    function renderPage(doc, num) {
        return doc.getPage(num).then(function (page) {
            var vp = page.getViewport({ scale: SCALE });
            var pg = document.createElement('div');
            pg.className    = 'pdf-pg';
            pg.dataset.page = num;
            pg.dataset.pw   = vp.width + ',' + vp.height;
            pg.style.width  = vp.width  + 'px';
            pg.style.height = vp.height + 'px';
            var canvas    = document.createElement('canvas');
            canvas.width  = vp.width;
            canvas.height = vp.height;
            pg.appendChild(canvas);
            pdfPages.appendChild(pg);
            return page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
        });
    }

    /* ── Clic en PDF ── */
    function onPdfClick(e) {
        if (firmaDrag.style.display !== 'none' && firmaDrag.contains(e.target)) return;

        var pgs = document.querySelectorAll('.pdf-pg');
        var clickX = e.clientX, clickY = e.clientY;
        var bestPg = null, bestDist = Infinity;

        pgs.forEach(function (pg) {
            var r = pg.getBoundingClientRect();
            if (clickX >= r.left && clickX <= r.right && clickY >= r.top && clickY <= r.bottom) {
                bestPg = pg; bestDist = 0;
            } else if (bestDist > 0) {
                var dx = Math.max(r.left - clickX, 0, clickX - r.right);
                var dy = Math.max(r.top  - clickY, 0, clickY - r.bottom);
                var d  = Math.sqrt(dx*dx + dy*dy);
                if (d < bestDist) { bestDist = d; bestPg = pg; }
            }
        });
        if (!bestPg) return;

        var pagesRect   = pdfPages.getBoundingClientRect();
        var leftInPages = (clickX - pagesRect.left) - (firmaDrag.offsetWidth  / 2);
        var topInPages  = (clickY - pagesRect.top)  - (firmaDrag.offsetHeight / 2);

        firmaDrag.style.display = 'block';
        firmaDrag.style.left    = leftInPages + 'px';
        firmaDrag.style.top     = topInPages  + 'px';

        pdfArea.classList.add('firma-colocada');
        placeHint.style.display = 'none';
        marcarFirmaColocada(bestPg);
    }

    function marcarFirmaColocada(pg) {
        firmaColocada = true;
        var pgRect = pg.getBoundingClientRect();
        var fdRect = firmaDrag.getBoundingClientRect();

        var xPct = ((fdRect.left - pgRect.left) / pgRect.width)  * 100;
        var yPct = ((fdRect.top  - pgRect.top)  / pgRect.height) * 100;
        var wPct = (firmaDrag.offsetWidth  / pgRect.width)  * 100;
        var hPct = (firmaDrag.offsetHeight / pgRect.height) * 100;

        posInput.value = JSON.stringify({
            page: parseInt(pg.dataset.page),
            x: xPct, y: yPct, w: wPct, h: hPct,
            firma_svg: FIRMA_SRC,
        });

        btnConf.disabled = false;
        btnConf.textContent = 'Confirmar firma';
        btnConf.classList.add('ready');
        if (statusTxt) {
            statusTxt.textContent = 'Firma colocada — ajusta posición o tamaño.';
            statusTxt.className = 'status-txt ok';
        }
    }

    /* ── Drag ── */
    firmaDrag.addEventListener('mousedown', function (e) {
        if (e.target === resizeHandle) return;
        e.stopPropagation();
        isDragging    = true;
        dragStartX    = e.clientX; dragStartY    = e.clientY;
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
        dragStartX    = t.clientX; dragStartY    = t.clientY;
        dragStartLeft = parseFloat(firmaDrag.style.left) || 0;
        dragStartTop  = parseFloat(firmaDrag.style.top)  || 0;
        firmaDrag.classList.add('dragging');
    }, { passive: true });

    /* ── Resize ── */
    resizeHandle.addEventListener('mousedown', function (e) {
        e.stopPropagation(); e.preventDefault();
        isResizing = true;
        resStartX  = e.clientX; resStartY  = e.clientY;
        resStartW  = firmaDrag.offsetWidth; resStartH = firmaDrag.offsetHeight;
    });

    resizeHandle.addEventListener('touchstart', function (e) {
        e.stopPropagation();
        var t = e.touches[0];
        isResizing = true;
        resStartX  = t.clientX; resStartY  = t.clientY;
        resStartW  = firmaDrag.offsetWidth; resStartH = firmaDrag.offsetHeight;
    }, { passive: false });

    /* ── Mousemove / Touchmove ── */
    document.addEventListener('mousemove', function (e) {
        if (isResizing) {
            firmaDrag.style.width  = Math.max(60, resStartW + e.clientX - resStartX) + 'px';
            firmaDrag.style.height = Math.max(24, resStartH + e.clientY - resStartY) + 'px';
            if (firmaColocada) actualizarPosicion();
            return;
        }
        if (!isDragging) return;
        firmaDrag.style.left = (dragStartLeft + e.clientX - dragStartX) + 'px';
        firmaDrag.style.top  = (dragStartTop  + e.clientY - dragStartY) + 'px';
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
        firmaDrag.style.left = (dragStartLeft + t.clientX - dragStartX) + 'px';
        firmaDrag.style.top  = (dragStartTop  + t.clientY - dragStartY) + 'px';
        actualizarPosicion();
    }, { passive: true });

    document.addEventListener('mouseup',  function () { isDragging = false; isResizing = false; firmaDrag.classList.remove('dragging'); });
    document.addEventListener('touchend', function () { isDragging = false; isResizing = false; firmaDrag.classList.remove('dragging'); });

    /* ── Actualizar posición tras mover/redimensionar ── */
    function actualizarPosicion() {
        if (!firmaColocada) return;
        var fdRect = firmaDrag.getBoundingClientRect();
        var fdCX = fdRect.left + fdRect.width / 2;
        var fdCY = fdRect.top  + fdRect.height / 2;
        var pgs = document.querySelectorAll('.pdf-pg');
        var targetPg = null;
        pgs.forEach(function (pg) {
            var r = pg.getBoundingClientRect();
            if (fdCX >= r.left && fdCX <= r.right && fdCY >= r.top && fdCY <= r.bottom) targetPg = pg;
        });
        if (!targetPg) targetPg = pgs[0];
        if (!targetPg) return;
        marcarFirmaColocada(targetPg);
    }

    /* ── Confirmar ── */
    btnConf.addEventListener('click', function () {
        if (!firmaColocada || !posInput.value) {
            alert('Coloca tu firma en el documento primero.');
            return;
        }
        actualizarPosicion();
        firmaForm.submit();
    });

    pdfPages.style.position = 'relative';
})();
</script>
@endif

</body>
</html>
