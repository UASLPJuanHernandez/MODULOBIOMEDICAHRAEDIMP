<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor de posiciones — Overlay 144</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #1a1a2e; color: #e2e8f0; display: flex; height: 100vh; overflow: hidden; }

/* ── Panel izquierdo: canvas PDF ── */
#canvas-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow: auto;
    background: #16213e;
}
#stage {
    position: relative;
    box-shadow: 0 8px 40px rgba(0,0,0,0.6);
    cursor: crosshair;
    flex-shrink: 0;
}
#pdf-canvas { display: block; }
#boxes-layer {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
}
.field-box {
    position: absolute;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    font-weight: 700;
    cursor: move;
    pointer-events: all;
    user-select: none;
    overflow: hidden;
    white-space: nowrap;
    border-radius: 2px;
}
.field-box.selected { outline: 2px solid white; outline-offset: 1px; z-index: 100 !important; }
.field-box .resize-handle {
    position: absolute;
    right: 0; top: 0; bottom: 0;
    width: 6px;
    cursor: ew-resize;
    background: rgba(255,255,255,0.4);
}
/* colores por tipo */
.tipo-texto    { background: rgba(59,130,246,0.3);  border-color: #3b82f6; color: #93c5fd; }
.tipo-bloque   { background: rgba(34,197,94,0.3);   border-color: #22c55e; color: #86efac; }
.tipo-ovalo    { background: rgba(239,68,68,0.3);   border-color: #ef4444; color: #fca5a5; border-radius: 50%; }
.tipo-checkbox { background: rgba(249,115,22,0.3);  border-color: #f97316; color: #fdba74; }
.tipo-firma    { background: rgba(168,85,247,0.3);  border-color: #a855f7; color: #d8b4fe; }

/* ── Panel derecho: controles ── */
#panel {
    width: 300px;
    flex-shrink: 0;
    background: #0f3460;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #1e4080;
    overflow: hidden;
}
#panel-header {
    padding: 12px 16px;
    background: #0a2744;
    border-bottom: 1px solid #1e4080;
    font-size: 13px;
    font-weight: 700;
}
#fields-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}
.field-row {
    padding: 8px 10px;
    border-radius: 6px;
    margin-bottom: 4px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background 0.1s;
    font-size: 11px;
}
.field-row:hover { background: rgba(255,255,255,0.06); }
.field-row.active { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }
.field-row .row-label { font-weight: 600; margin-bottom: 5px; display: flex; align-items: center; gap: 6px; }
.dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-texto    { background: #3b82f6; }
.dot-bloque   { background: #22c55e; }
.dot-ovalo    { background: #ef4444; }
.dot-checkbox { background: #f97316; }
.dot-firma    { background: #a855f7; }

.inputs-row { display: flex; gap: 4px; flex-wrap: wrap; }
.input-group { display: flex; flex-direction: column; gap: 2px; }
.input-group label { font-size: 9px; color: #94a3b8; text-transform: uppercase; }
.input-group input {
    width: 52px;
    padding: 3px 5px;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 4px;
    color: white;
    font-size: 11px;
    text-align: center;
}
.input-group input:focus { outline: none; border-color: #3b82f6; }
.input-group select {
    width: 52px;
    padding: 3px 4px;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 4px;
    color: white;
    font-size: 11px;
}

#panel-footer {
    padding: 12px 16px;
    border-top: 1px solid #1e4080;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.btn-save {
    width: 100%;
    padding: 10px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.btn-save:hover { background: #1d4ed8; }
.btn-preview {
    width: 100%;
    padding: 8px;
    background: transparent;
    color: #94a3b8;
    border: 1px solid #334155;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    display: block;
}
.btn-preview:hover { background: rgba(255,255,255,0.05); color: white; }
#status { font-size: 11px; color: #64748b; text-align: center; }
</style>
</head>
<body>

<div id="canvas-wrap">
    <div id="stage">
        <canvas id="pdf-canvas"></canvas>
        <div id="boxes-layer"></div>
    </div>
</div>

<div id="panel">
    <div id="panel-header">
        Overlay Editor — Formulario 144
        <div style="font-size:10px;color:#64748b;font-weight:400;margin-top:2px;">Arrastra · Redimensiona · Guarda</div>
    </div>
    <div id="fields-list"></div>
    <div id="panel-footer">
        <button class="btn-save" onclick="guardar()">Guardar posiciones</button>
        <a href="/bitacora/{{ $bitacoraId }}/debug?t={{ time() }}" target="_blank" class="btn-preview">Ver debug PDF</a>
        <a href="/bitacora/{{ $bitacoraId }}/preview?t={{ time() }}" target="_blank" class="btn-preview">Ver preview real</a>
        <div id="status">Listo</div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// ── Dimensiones reales del PDF en mm ──
const PDF_W = 216.49;
const PDF_H = 137.87;

// Posiciones cargadas del servidor
let positions = @json($positions);

// Estado
let scale = 1;
let selectedKey = null;
let dragging = null; // {key, startX, startY, origX, origY}
let resizing = null; // {key, startX, origW}

// ── 1. Renderizar template con PDF.js ──────────────────────────────
const canvas = document.getElementById('pdf-canvas');
const ctx = canvas.getContext('2d');

pdfjsLib.getDocument('/overlay-template').promise.then(pdf => {
    pdf.getPage(1).then(page => {
        const maxW = document.getElementById('canvas-wrap').clientWidth - 32;
        const viewport = page.getViewport({ scale: 1 });
        scale = maxW / viewport.width;
        const vp = page.getViewport({ scale });

        canvas.width  = vp.width;
        canvas.height = vp.height;

        page.render({ canvasContext: ctx, viewport: vp }).promise.then(() => {
            renderBoxes();
            renderList();
        });
    });
});

// ── 2. Convertir mm → px y px → mm ────────────────────────────────
function mmToPx(mm) {
    // PDF.js renderiza en puntos (1pt = 1/72 inch), el PDF está en mm
    // Usamos el ratio real del canvas
    return (mm / PDF_W) * canvas.width;
}
function pxToMm(px, axis) {
    if (axis === 'x') return Math.round((px / canvas.width)  * PDF_W * 10) / 10;
    return              Math.round((px / canvas.height) * PDF_H * 10) / 10;
}

// ── 3. Render boxes ────────────────────────────────────────────────
function renderBoxes() {
    const layer = document.getElementById('boxes-layer');
    layer.innerHTML = '';

    const all = getAllFields();
    all.forEach(f => {
        const el = document.createElement('div');
        el.className = 'field-box tipo-' + f.tipo;
        el.dataset.key = f.key;
        if (f.key === selectedKey) el.classList.add('selected');

        const left = mmToPx(f.x ?? (f.cx - f.rx));
        const top  = mmToPx(f.y ?? (f.cy - 4.5));
        const w    = mmToPx(f.w ?? (f.rx * 2));
        const h    = mmToPx(f.h ?? (f.tipo === 'texto' ? 5 : f.tipo === 'ovalo' ? 9 : f.h ?? 9));

        el.style.left   = left + 'px';
        el.style.top    = top  + 'px';
        el.style.width  = w    + 'px';
        el.style.height = h    + 'px';
        el.style.zIndex = f.tipo === 'texto' ? 10 : f.tipo === 'firma' ? 5 : 8;

        el.textContent = f.label;

        // Resize handles (ancho y alto)
        if (['texto','bloque','firma'].includes(f.tipo)) {
            const handleW = document.createElement('div');
            handleW.className = 'resize-handle';
            handleW.title = 'Ancho';
            handleW.addEventListener('mousedown', e => startResize(e, f.key, 'w'));
            el.appendChild(handleW);

            const handleH = document.createElement('div');
            handleH.style.cssText = 'position:absolute;left:0;right:0;bottom:0;height:5px;cursor:ns-resize;background:rgba(255,255,255,0.3)';
            handleH.title = 'Alto';
            handleH.addEventListener('mousedown', e => startResize(e, f.key, 'h'));
            el.appendChild(handleH);
        }

        el.addEventListener('mousedown', e => {
            if (e.target.classList.contains('resize-handle')) return;
            startDrag(e, f.key);
        });
        el.addEventListener('click', () => selectField(f.key));

        layer.appendChild(el);
    });
}

// ── 4. Lista lateral ───────────────────────────────────────────────
const TIPOS_COLOR = {texto:'dot-texto', bloque:'dot-bloque', ovalo:'dot-ovalo', checkbox:'dot-checkbox', firma:'dot-firma'};

function renderList() {
    const list = document.getElementById('fields-list');
    list.innerHTML = '';
    const all = getAllFields();
    all.forEach(f => {
        const row = document.createElement('div');
        row.className = 'field-row' + (f.key === selectedKey ? ' active' : '');
        row.dataset.key = f.key;

        let inputs = '';
        if (f.tipo === 'ovalo') {
            inputs = `
                <div class="inputs-row">
                    <div class="input-group"><label>CX</label><input type="number" step="0.5" value="${f.cx}" onchange="updateField('${f.key}','cx',+this.value)"></div>
                    <div class="input-group"><label>CY</label><input type="number" step="0.5" value="${f.cy}" onchange="updateField('${f.key}','cy',+this.value)"></div>
                    <div class="input-group"><label>RX</label><input type="number" step="0.5" value="${f.rx}" onchange="updateField('${f.key}','rx',+this.value)"></div>
                </div>`;
        } else if (f.tipo === 'checkbox') {
            inputs = `
                <div class="inputs-row">
                    <div class="input-group"><label>X</label><input type="number" step="0.5" value="${f.x}" onchange="updateField('${f.key}','x',+this.value)"></div>
                    <div class="input-group"><label>Y</label><input type="number" step="0.5" value="${f.y}" onchange="updateField('${f.key}','y',+this.value)"></div>
                    <div class="input-group"><label>W</label><input type="number" step="0.5" value="${f.w ?? 6}" onchange="updateField('${f.key}','w',+this.value)"></div>
                    <div class="input-group"><label>H</label><input type="number" step="0.5" value="${f.h ?? 5}" onchange="updateField('${f.key}','h',+this.value)"></div>
                </div>`;
        } else {
            const alignSel = f.tipo === 'texto' ? `
                <div class="input-group"><label>Align</label>
                <select onchange="updateField('${f.key}','align',this.value)">
                    <option ${f.align==='L'?'selected':''}>L</option>
                    <option ${f.align==='C'?'selected':''}>C</option>
                    <option ${f.align==='R'?'selected':''}>R</option>
                </select></div>` : '';
            inputs = `
                <div class="inputs-row">
                    <div class="input-group"><label>X</label><input type="number" step="0.5" value="${f.x}" onchange="updateField('${f.key}','x',+this.value)"></div>
                    <div class="input-group"><label>Y</label><input type="number" step="0.5" value="${f.y}" onchange="updateField('${f.key}','y',+this.value)"></div>
                    <div class="input-group"><label>W</label><input type="number" step="0.5" value="${f.w}" onchange="updateField('${f.key}','w',+this.value)"></div>
                    <div class="input-group"><label>H</label><input type="number" step="0.5" value="${f.h ?? 5}" onchange="updateField('${f.key}','h',+this.value)"></div>
                    ${f.tipo === 'bloque' ? `<div class="input-group"><label>LineH</label><input type="number" step="0.5" value="${f.lineH ?? 5}" onchange="updateField('${f.key}','lineH',+this.value)"></div>` : ''}
                    ${alignSel}
                </div>`;
        }

        row.innerHTML = `<div class="row-label"><span class="dot ${TIPOS_COLOR[f.tipo]}"></span>${f.label}</div>${inputs}`;
        row.addEventListener('click', e => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;
            selectField(f.key);
        });
        list.appendChild(row);
    });
}

// ── 5. Helpers de datos ────────────────────────────────────────────
function getAllFields() {
    const all = [];
    positions.textos.forEach(f => all.push({...f, tipo:'texto'}));
    all.push({...positions.justificacion,  key:'justificacion',  label:'Justificación (bloque 1)', tipo:'bloque'});
    all.push({...positions.justificacion2, key:'justificacion2', label:'Justificación (bloque 2)', tipo:'bloque'});
    positions.ovalos.forEach(f => all.push({...f, tipo:'ovalo'}));
    positions.checkboxes.forEach(f => all.push({...f, tipo:'checkbox'}));
    positions.firmas.forEach(f => all.push({...f, tipo:'firma'}));
    return all;
}

function getField(key) {
    return getAllFields().find(f => f.key === key);
}

function updateField(key, prop, val) {
    const section = findSection(key);
    if (!section) return;
    if (Array.isArray(section)) {
        const idx = section.findIndex(f => f.key === key);
        if (idx >= 0) section[idx][prop] = val;
    } else {
        section[prop] = val;
    }
    renderBoxes();
}

function findSection(key) {
    if (key === 'justificacion')  return positions.justificacion;
    if (key === 'justificacion2') return positions.justificacion2;
    if (positions.textos.find(f => f.key === key))     return positions.textos;
    if (positions.ovalos.find(f => f.key === key))     return positions.ovalos;
    if (positions.checkboxes.find(f => f.key === key)) return positions.checkboxes;
    if (positions.firmas.find(f => f.key === key))     return positions.firmas;
    return null;
}

function selectField(key) {
    selectedKey = key;
    renderBoxes();
    renderList();
    // Scroll al field en la lista
    const row = document.querySelector(`.field-row[data-key="${key}"]`);
    if (row) row.scrollIntoView({block:'nearest'});
}

// ── 6. Drag ────────────────────────────────────────────────────────
function startDrag(e, key) {
    e.preventDefault();
    selectField(key);
    const f = getField(key);
    dragging = {
        key,
        startX: e.clientX,
        startY: e.clientY,
        origX: f.cx !== undefined ? f.cx - f.rx : f.x,
        origY: f.cy !== undefined ? f.cy : f.y,
    };
}

function startResize(e, key, axis) {
    e.preventDefault();
    e.stopPropagation();
    selectField(key);
    const f = getField(key);
    resizing = { key, axis, startX: e.clientX, startY: e.clientY, origW: f.w ?? (f.rx * 2), origH: f.h ?? 5 };
}

document.addEventListener('mousemove', e => {
    if (dragging) {
        const dx = pxToMm(e.clientX - dragging.startX, 'x');
        const dy = pxToMm(e.clientY - dragging.startY, 'x'); // same scale
        const newX = Math.max(0, Math.round((dragging.origX + dx) * 2) / 2);
        const newY = Math.max(0, Math.round((dragging.origY + dy) * 2) / 2);
        const f = getField(dragging.key);
        if (f.cx !== undefined) {
            updateField(dragging.key, 'cx', newX + f.rx);
            updateField(dragging.key, 'cy', newY);
        } else {
            updateField(dragging.key, 'x', newX);
            updateField(dragging.key, 'y', newY);
        }
        renderList();
    }
    if (resizing) {
        if (resizing.axis === 'w') {
            const dx = pxToMm(e.clientX - resizing.startX, 'x');
            const newW = Math.max(2, Math.round((resizing.origW + dx) * 2) / 2);
            updateField(resizing.key, 'w', newW);
        } else {
            const dy = pxToMm(e.clientY - resizing.startY, 'x');
            const newH = Math.max(1, Math.round((resizing.origH + dy) * 2) / 2);
            updateField(resizing.key, 'h', newH);
        }
        renderList();
    }
});

document.addEventListener('mouseup', () => { dragging = null; resizing = null; });

// ── 7. Guardar ─────────────────────────────────────────────────────
function guardar() {
    document.getElementById('status').textContent = 'Guardando…';
    fetch('/overlay-editor/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(positions)
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('status').textContent = d.ok ? '✓ Guardado' : '✗ Error: ' + d.error;
        setTimeout(() => document.getElementById('status').textContent = 'Listo', 3000);
    })
    .catch(() => document.getElementById('status').textContent = '✗ Error de red');
}
</script>
</body>
</html>
