// Módulo PDF overlay — visor + editor de campos sobre PDF con PDF.js
(function () {
    'use strict';

    /* ── Estado global del módulo ── */
    var _pdf      = null;
    var _campos   = [];     // [{id, page, x, y, w, h, label}]  — x,y,w,h en % de página
    var _valores  = {};     // {id: string}
    var _nextId   = 1;
    var _addMode  = false;
    var _visor    = false;

    /* ── Estado de arrastre y redimensión (trabajan en px) ── */
    var _drag = null;   // {el, campo, sx, sy, cx_px, cy_px, wW, wH}
    var _rsz  = null;   // {el, campo, sx, sy, cw_px, ch_px, wW, wH}

    var WORKER_SRC    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var SCALE         = 1.5;
    var VISOR_OFFSET_Y = 4;   // px extra hacia abajo en el visor (ajusta si el texto aparece arriba)


    
    /* ===== Inicialización ===== */
    function _init(opts) {
        _campos  = (opts.campos  || []).map(function (c) { return Object.assign({}, c); });
        _valores = Object.assign({}, opts.valores || {});
        _visor   = !!opts.modoVisor;
        _addMode = false;

        _nextId = 1;
        _campos.forEach(function (c) {
            var n = parseInt((c.id + '').replace(/\D/g, ''), 10) || 0;
            if (n >= _nextId) _nextId = n + 1;
        });

        var containerId = opts.containerId || (_visor ? 'pdf-overlay-viewer' : 'pdf-overlay-editor');
        var container   = document.getElementById(containerId);
        if (!container) return;

        var pagesWrap = container.querySelector('.pdf-pages-wrap');
        if (!pagesWrap) return;
        pagesWrap.innerHTML = '';

        var btnAdd = document.getElementById('btn-pdf-add');
        if (btnAdd) btnAdd.setAttribute('data-active', 'false');

        if (typeof pdfjsLib === 'undefined') {
            pagesWrap.innerHTML = '<p style="color:#ef4444;padding:16px">PDF.js no está cargado.</p>';
            return;
        }

        pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_SRC;

        pdfjsLib.getDocument({ url: opts.url, withCredentials: true }).promise
            .then(function (doc) {
                _pdf = doc;
                _renderTodas(pagesWrap);
            })
            .catch(function (err) {
                pagesWrap.innerHTML =
                    '<p style="color:#ef4444;padding:16px">Error al cargar el PDF: ' + err.message + '</p>';
            });
    }

    /* ===== Renderizar todas las páginas ===== */
    function _renderTodas(wrap) {
        var promises = [];
        for (var i = 1; i <= _pdf.numPages; i++) {
            promises.push(_renderPagina(i, wrap));
        }
        Promise.all(promises).then(_renderTodosCampos);
    }

    function _renderPagina(num, wrap) {
        return _pdf.getPage(num).then(function (page) {
            var vp = page.getViewport({ scale: SCALE });

            /* contenedor de página — dimensiones explícitas en px */
            var pw = document.createElement('div');
            pw.className    = 'pdf-pw';
            pw.dataset.page = num;
            pw.dataset.pw   = vp.width + ',' + vp.height;  // guardar dims para conversión
            pw.style.cssText =
                'position:relative;' +
                'width:' + vp.width + 'px;height:' + vp.height + 'px;' +
                'margin:0 auto 16px;box-shadow:0 4px 20px rgba(0,0,0,.3);' +
                'background:#fff;flex-shrink:0;';

            /* canvas */
            var canvas    = document.createElement('canvas');
            canvas.width  = vp.width;
            canvas.height = vp.height;
            canvas.style.cssText = 'display:block;position:absolute;top:0;left:0;';

            /* overlay — mismas dimensiones explícitas en px */
            var ov = document.createElement('div');
            ov.className    = 'pdf-ov';
            ov.dataset.page = num;
            ov.style.cssText =
                'position:absolute;top:0;left:0;' +
                'width:' + vp.width + 'px;height:' + vp.height + 'px;' +
                'overflow:visible;' +
                'pointer-events:' + (_visor ? 'none' : 'all') + ';';

            if (!_visor) {
                ov.addEventListener('click', _onOverlayClick);
            }

            pw.appendChild(canvas);
            pw.appendChild(ov);
            wrap.appendChild(pw);

            return page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
        });
    }

    /* ===== Renderizar todos los campos ===== */
    function _renderTodosCampos() {
        document.querySelectorAll('.pdf-campo').forEach(function (el) { el.remove(); });
        _campos.forEach(_renderCampo);
    }

    /* convierte % a px usando las dimensiones almacenadas en pw.dataset.pw */
    function _pct2px(pw, campo) {
        var dims = (pw.dataset.pw || '0,0').split(',');
        var pwW  = parseFloat(dims[0]) || pw.offsetWidth;
        var pwH  = parseFloat(dims[1]) || pw.offsetHeight;
        return {
            x:  campo.x / 100 * pwW,
            y:  campo.y / 100 * pwH,
            w:  campo.w / 100 * pwW,
            h:  campo.h / 100 * pwH,
            pwW: pwW,
            pwH: pwH
        };
    }

    function _renderCampo(campo) {
        var pw = document.querySelector('.pdf-pw[data-page="' + campo.page + '"]');
        if (!pw) return;
        var ov = pw.querySelector('.pdf-ov');
        if (!ov) return;

        var px = _pct2px(pw, campo);

        var el = document.createElement('div');
        el.className  = 'pdf-campo';
        el.dataset.id = campo.id;

        /* ── posición y tamaño siempre en PÍXELES absolutos ── */
        el.style.cssText =
            'position:absolute;' +
            'left:' + px.x + 'px;' +
            'top:'  + (px.y + (_visor ? VISOR_OFFSET_Y : 0)) + 'px;' +
            'width:' + px.w + 'px;' +
            'min-height:' + Math.max(px.h, 24) + 'px;' +
            'box-sizing:border-box;z-index:10;overflow:visible;';

        var esFirma = campo.tipo === 'firma' || campo.tipo === 'firma_jefe';

        if (_visor) {
            /* ── Modo solo lectura ── */
            if (esFirma) {
                var imgV = document.createElement('img');
                imgV.style.cssText =
                    'width:100%;height:100%;object-fit:contain;display:block;' +
                    'mix-blend-mode:multiply;';
                imgV.src = _valores[campo.id] || '';
                el.appendChild(imgV);
            } else {
                el.style.cssText +=
                    'font-size:11pt;color:#111;padding:2px 4px;' +
                    'white-space:pre-wrap;word-break:break-word;line-height:1.4;';
                el.textContent = _valores[campo.id] || '';
            }

        } else {
            /* ── Modo editor ── */
            el.style.cssText +=
                'background:rgba(99,102,241,.07);' +
                'border:1.5px solid rgba(99,102,241,.5);' +
                'border-radius:3px;cursor:default;';

            /* botón eliminar */
            var del = document.createElement('button');
            del.type        = 'button';
            del.textContent = '×';
            del.style.cssText =
                'position:absolute;top:-8px;right:-8px;width:16px;height:16px;' +
                'border-radius:50%;background:#ef4444;color:#fff;border:none;' +
                'cursor:pointer;font-size:13px;line-height:16px;text-align:center;' +
                'z-index:20;padding:0;display:none;';
            del.addEventListener('click', function (e) {
                e.stopPropagation();
                _campos = _campos.filter(function (c) { return c.id !== campo.id; });
                delete _valores[campo.id];
                el.remove();
            });

            /* handle de redimensión */
            var rsz = document.createElement('div');
            rsz.style.cssText =
                'position:absolute;bottom:0;right:0;width:10px;height:10px;' +
                'background:#6366f1;cursor:se-resize;z-index:20;border-radius:2px 0 3px 0;display:none;';
            rsz.addEventListener('mousedown', _onResizeStart);

            /* grip para mover — ÚNICO punto desde donde se arrastra */
            var grip = document.createElement('div');
            grip.innerHTML = '<svg width="8" height="10" viewBox="0 0 8 10" fill="#fff">' +
                '<circle cx="2.5" cy="2" r="1"/><circle cx="5.5" cy="2" r="1"/>' +
                '<circle cx="2.5" cy="5" r="1"/><circle cx="5.5" cy="5" r="1"/>' +
                '<circle cx="2.5" cy="8" r="1"/><circle cx="5.5" cy="8" r="1"/>' +
                '</svg>';
            grip.style.cssText =
                'position:absolute;bottom:0;left:0;width:14px;height:18px;' +
                'cursor:grab;z-index:20;background:#6366f1;border-radius:0 0 0 3px;' +
                'display:none;align-items:center;justify-content:center;';

            /* ── Drag solo desde el grip ── */
            grip.addEventListener('mousedown', function (e) {
                e.stopPropagation();
                e.preventDefault();
                var pw   = el.closest('.pdf-pw');
                var dims = (pw.dataset.pw || '0,0').split(',');
                _drag = {
                    el: el, campo: campo,
                    sx: e.clientX, sy: e.clientY,
                    cx_px: parseFloat(el.style.left) || 0,
                    cy_px: parseFloat(el.style.top)  || 0,
                    wW: parseFloat(dims[0]) || pw.offsetWidth,
                    wH: parseFloat(dims[1]) || pw.offsetHeight
                };
                grip.style.cursor = 'grabbing';
                document.addEventListener('mousemove', _onDragMove);
                document.addEventListener('mouseup', function _upOnce() {
                    grip.style.cursor = 'grab';
                    document.removeEventListener('mouseup', _upOnce);
                });
                document.addEventListener('mouseup', _onDragEnd);
            });

            if (esFirma) {
                /* ── Campo tipo firma: muestra imagen + botón cambiar ── */
                var imgE = document.createElement('img');
                imgE.className    = 'pdf-firma-img';
                imgE.style.cssText =
                    'width:100%;height:100%;object-fit:contain;display:block;' +
                    'mix-blend-mode:multiply;pointer-events:none;';
                imgE.src = _valores[campo.id] || '';

                var redibujar = document.createElement('button');
                redibujar.type  = 'button';
                redibujar.title = 'Cambiar firma';
                redibujar.innerHTML =
                    '<svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                    'd="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828' +
                    'L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>';
                redibujar.style.cssText =
                    'position:absolute;top:-8px;left:10px;width:18px;height:18px;' +
                    'border-radius:50%;background:#7c3aed;color:#fff;border:none;' +
                    'cursor:pointer;z-index:20;padding:2px;display:none;' +
                    'align-items:center;justify-content:center;';
                redibujar.addEventListener('click',     function (e) { e.stopPropagation(); campo.tipo === 'firma_jefe' ? _abrirPickerJefa(campo.id) : _abrirPickerFirma(campo.id); });
                redibujar.addEventListener('mousedown', function (e) { e.stopPropagation(); });

                el.appendChild(imgE);
                el.appendChild(redibujar);
                el.appendChild(del);
                el.appendChild(rsz);
                el.appendChild(grip);

                el.addEventListener('mouseenter', function () {
                    del.style.display       = '';
                    rsz.style.display       = '';
                    grip.style.display      = 'flex';
                    redibujar.style.display = 'flex';
                });
                el.addEventListener('mouseleave', function () {
                    del.style.display       = 'none';
                    rsz.style.display       = 'none';
                    grip.style.display      = 'none';
                    redibujar.style.display = 'none';
                });
            } else {
                /* ── Campo tipo texto: textarea normal ── */
                var inp = document.createElement('textarea');
                inp.className   = 'pdf-campo-inp';
                inp.placeholder = 'Escribir aquí...';
                inp.value       = _valores[campo.id] || '';
                inp.rows        = 1;
                inp.style.cssText =
                    'display:block;width:100%;background:transparent;border:none;outline:none;' +
                    'font-size:11pt;font-family:inherit;color:#111;' +
                    'padding:2px 4px;resize:none;overflow:hidden;min-height:22px;box-sizing:border-box;';
                inp.addEventListener('input', function () {
                    _valores[campo.id] = inp.value;
                    inp.style.height   = 'auto';
                    inp.style.height   = inp.scrollHeight + 'px';
                });
                inp.addEventListener('mousedown', function (e) { e.stopPropagation(); });
                inp.addEventListener('click',     function (e) { e.stopPropagation(); });

                el.appendChild(grip);
                grip.style.display = 'none';
                el.appendChild(inp);
                el.appendChild(del);
                el.appendChild(rsz);

                el.addEventListener('mouseenter', function () {
                    del.style.display  = '';
                    rsz.style.display  = '';
                    grip.style.display = 'flex';
                });
                el.addEventListener('mouseleave', function () {
                    del.style.display  = 'none';
                    rsz.style.display  = 'none';
                    grip.style.display = 'none';
                });
            }
        }

        ov.appendChild(el);
    }

    /* ===== Click en overlay — colocar campo ===== */
    function _onOverlayClick(e) {
        if (!_addMode) return;

        var ov   = e.currentTarget;
        var page = parseInt(ov.dataset.page, 10);

        /* usar coordenadas del pw (no del overlay) para máxima precisión */
        var pw    = ov.parentElement;
        var rect  = pw.getBoundingClientRect();
        var x_px  = e.clientX - rect.left;
        var y_px  = e.clientY - rect.top;
        var pwW   = parseFloat((pw.dataset.pw || '0,0').split(',')[0]) || pw.offsetWidth;
        var pwH   = parseFloat((pw.dataset.pw || '0,0').split(',')[1]) || pw.offsetHeight;

        var c = {
            id:    'f' + _nextId++,
            page:  page,
            x:     Math.max(0, x_px / pwW * 100),
            y:     Math.max(0, y_px / pwH * 100),
            w:     25,
            h:     5,
            label: 'Campo ' + (_campos.length + 1)
        };
        _campos.push(c);
        _renderCampo(c);

        _addMode = false;
        var btn = document.getElementById('btn-pdf-add');
        if (btn) btn.setAttribute('data-active', 'false');
        _setCursor('default');
        e.stopPropagation();
    }

    function _setCursor(cur) {
        document.querySelectorAll('.pdf-ov').forEach(function (o) { o.style.cursor = cur; });
    }

    /* ===== Arrastre (trabaja en px) ===== */
    function _onDragStart(e) {
        if (e.target.tagName === 'TEXTAREA' ||
            e.target.tagName === 'BUTTON'   ||
            e.target.style.cursor === 'se-resize') return;

        var el    = e.currentTarget;
        var cId   = el.dataset.id;
        var campo = _campos.find(function (c) { return c.id === cId; });
        if (!campo) return;

        var pw = el.closest('.pdf-pw');
        var dims = (pw.dataset.pw || '0,0').split(',');
        _drag = {
            el: el, campo: campo,
            sx: e.clientX, sy: e.clientY,
            cx_px: parseFloat(el.style.left) || 0,
            cy_px: parseFloat(el.style.top)  || 0,
            wW: parseFloat(dims[0]) || pw.offsetWidth,
            wH: parseFloat(dims[1]) || pw.offsetHeight
        };

        document.addEventListener('mousemove', _onDragMove);
        document.addEventListener('mouseup',   _onDragEnd);
        e.preventDefault();
    }

    function _onDragMove(e) {
        if (!_drag) return;
        var dx   = e.clientX - _drag.sx;
        var dy   = e.clientY - _drag.sy;
        var newX = Math.max(0, _drag.cx_px + dx);
        var newY = Math.max(0, _drag.cy_px + dy);

        _drag.campo.x       = newX / _drag.wW * 100;
        _drag.campo.y       = newY / _drag.wH * 100;
        _drag.el.style.left = newX + 'px';
        _drag.el.style.top  = newY + 'px';
    }

    function _onDragEnd() {
        _drag = null;
        document.removeEventListener('mousemove', _onDragMove);
        document.removeEventListener('mouseup',   _onDragEnd);
    }

    /* ===== Redimensión (trabaja en px) ===== */
    function _onResizeStart(e) {
        e.stopPropagation();
        var el    = e.currentTarget.closest('.pdf-campo');
        var cId   = el.dataset.id;
        var campo = _campos.find(function (c) { return c.id === cId; });
        if (!campo) return;

        var pw   = el.closest('.pdf-pw');
        var dims = (pw.dataset.pw || '0,0').split(',');
        _rsz = {
            el: el, campo: campo,
            sx: e.clientX, sy: e.clientY,
            cw_px: parseFloat(el.style.width)     || 0,
            ch_px: parseFloat(el.style.minHeight) || 0,
            wW: parseFloat(dims[0]) || pw.offsetWidth,
            wH: parseFloat(dims[1]) || pw.offsetHeight
        };
        document.addEventListener('mousemove', _onResizeMove);
        document.addEventListener('mouseup',   _onResizeEnd);
        e.preventDefault();
    }

    function _onResizeMove(e) {
        if (!_rsz) return;
        var newW = Math.max(40,  _rsz.cw_px + (e.clientX - _rsz.sx));
        var newH = Math.max(22,  _rsz.ch_px + (e.clientY - _rsz.sy));

        _rsz.campo.w = newW / _rsz.wW * 100;
        _rsz.campo.h = newH / _rsz.wH * 100;
        _rsz.el.style.width     = newW + 'px';
        _rsz.el.style.minHeight = newH + 'px';
    }

    function _onResizeEnd() {
        _rsz = null;
        document.removeEventListener('mousemove', _onResizeMove);
        document.removeEventListener('mouseup',   _onResizeEnd);
    }

    /* ===== Leer valores actuales de los textarea (no toca campos firma) ===== */
    function _leerValores() {
        document.querySelectorAll('.pdf-campo-inp').forEach(function (inp) {
            var el = inp.closest('.pdf-campo');
            if (el) _valores[el.dataset.id] = inp.value;
        });
    }

    /* ===== Llamar método Livewire ===== */
    function _lwCall(method) {
        var args   = Array.prototype.slice.call(arguments, 1);
        var wireEl = document.querySelector('[wire\\:id]');
        if (!wireEl) return;
        var comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
        comp.call.apply(comp, [method].concat(args));
    }

    /* ===== Utilidad: normalizar firma a data URL ===== */
    function _firmaADataUrl(firma) {
        if (!firma) return '';
        if (firma.indexOf('data:') === 0) return firma;
        /* SVG raw → data URL */
        var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 70" width="200" height="70">' +
                  firma + '</svg>';
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    }

    /* ===== Modal de firma: panel ingenieros + panel canvas ===== */
    function _inyectarModalFirma() {
        if (document.getElementById('fmt-firma-modal')) return;

        var modal = document.createElement('div');
        modal.id = 'fmt-firma-modal';
        modal.style.cssText =
            'display:none;position:fixed;inset:0;z-index:99999;' +
            'background:rgba(0,0,0,.55);align-items:center;justify-content:center;';

        modal.innerHTML =
            '<div id="fmt-firma-box" style="background:#fff;border-radius:14px;' +
                 'max-width:540px;width:94%;box-shadow:0 24px 64px rgba(0,0,0,.35);overflow:hidden;">' +
                '<div style="padding:22px 24px 20px;">' +
                    '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">' +
                        '<h3 style="margin:0;font-size:16px;font-weight:700;color:#111;">Seleccionar firma</h3>' +
                        '<button id="fmt-firma-cerrar" type="button" ' +
                            'style="background:none;border:none;cursor:pointer;font-size:22px;' +
                                   'color:#9ca3af;line-height:1;padding:0;">×</button>' +
                    '</div>' +
                    '<div id="fmt-firma-grid" style="' +
                        'display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));' +
                        'gap:10px;max-height:360px;overflow-y:auto;padding-right:4px;' +
                        'margin-bottom:16px;"></div>' +
                    '<div style="display:flex;justify-content:flex-end;">' +
                        '<button id="fmt-firma-cancelar" type="button" ' +
                            'style="padding:8px 16px;border:1px solid #d1d5db;border-radius:8px;' +
                                   'font-size:13px;font-weight:600;cursor:pointer;' +
                                   'background:#fff;color:#374151;">Cancelar</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        var _cerrar = function () { modal.style.display = 'none'; };
        document.getElementById('fmt-firma-cerrar').addEventListener('click', _cerrar);
        document.getElementById('fmt-firma-cancelar').addEventListener('click', _cerrar);
        modal.addEventListener('click', function (e) { if (e.target === modal) _cerrar(); });
    }

    /* Recorta los píxeles transparentes/blancos alrededor de la firma y
       devuelve un dataURL ajustado + las dimensiones naturales del recorte */
    function _recortarFirma(dataUrl, callback) {
        /* SVG data URLs no necesitan recorte — devolver tal cual */
        if (dataUrl.indexOf('data:image/svg') === 0) {
            var tmp = new Image();
            tmp.onload = function () { callback(dataUrl, tmp.naturalWidth, tmp.naturalHeight); };
            tmp.src    = dataUrl;
            return;
        }

        var img = new Image();
        img.onload = function () {
            var W = img.naturalWidth;
            var H = img.naturalHeight;

            /* dibujar en canvas auxiliar */
            var cv  = document.createElement('canvas');
            cv.width = W; cv.height = H;
            var ctx = cv.getContext('2d');
            ctx.drawImage(img, 0, 0);

            var px    = ctx.getImageData(0, 0, W, H).data;
            var minX  = W, minY = H, maxX = 0, maxY = 0;
            var found = false;

            for (var y = 0; y < H; y++) {
                for (var x = 0; x < W; x++) {
                    var i = (y * W + x) * 4;
                    /* píxel visible: alpha > 20 O (RGB claramente distinto de blanco) */
                    var a = px[i + 3];
                    var r = px[i], g = px[i + 1], b = px[i + 2];
                    var esVisible = a > 20 || (a > 0 && (r < 240 || g < 240 || b < 240));
                    if (esVisible) {
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                        found = true;
                    }
                }
            }

            if (!found) { callback(dataUrl, W, H); return; }

            /* margen de 4 px alrededor del trazo */
            var pad = 4;
            minX = Math.max(0, minX - pad);
            minY = Math.max(0, minY - pad);
            maxX = Math.min(W - 1, maxX + pad);
            maxY = Math.min(H - 1, maxY + pad);

            var cW = maxX - minX + 1;
            var cH = maxY - minY + 1;

            var out = document.createElement('canvas');
            out.width = cW; out.height = cH;
            out.getContext('2d').drawImage(cv, minX, minY, cW, cH, 0, 0, cW, cH);

            callback(out.toDataURL('image/png'), cW, cH);
        };
        img.src = dataUrl;
    }

    function _aplicarFirma(dataUrl, campoIdExistente) {
        _recortarFirma(dataUrl, function (urlRecortada, natW, natH) {
            if (campoIdExistente) {
                /* actualizar firma existente: solo el src y el valor */
                _valores[campoIdExistente] = urlRecortada;
                var el = document.querySelector('.pdf-campo[data-id="' + campoIdExistente + '"]');
                if (el) {
                    var img2 = el.querySelector('.pdf-firma-img');
                    if (img2) img2.src = urlRecortada;
                    /* recalcular tamaño del campo según nuevas dimensiones */
                    var pw   = el.closest('.pdf-pw');
                    var dims = (pw.dataset.pw || '0,0').split(',');
                    var pwW  = parseFloat(dims[0]) || pw.offsetWidth;
                    var pwH  = parseFloat(dims[1]) || pw.offsetHeight;
                    var wPct = Math.min(20, natW / pwW * 100);
                    var hPct = wPct * (natH / natW) * (pwW / pwH);
                    var campo = _campos.find(function (c) { return c.id === campoIdExistente; });
                    if (campo) { campo.w = wPct; campo.h = hPct; }
                    el.style.width      = (wPct / 100 * pwW) + 'px';
                    el.style.minHeight  = (hPct / 100 * pwH) + 'px';
                }
            } else {
                /* nuevo campo firma: calcular tamaño según imagen recortada */
                var pw1   = document.querySelector('.pdf-pw[data-page="1"]');
                var pwW1  = 800, pwH1 = 1132;
                if (pw1) {
                    var d = (pw1.dataset.pw || '800,1132').split(',');
                    pwW1  = parseFloat(d[0]) || pw1.offsetWidth;
                    pwH1  = parseFloat(d[1]) || pw1.offsetHeight;
                }
                var wPct1 = Math.min(20, natW / pwW1 * 100);
                var hPct1 = wPct1 * (natH / natW) * (pwW1 / pwH1);

                var id = 'f' + _nextId++;
                var c  = {
                    id:    id,
                    page:  1,
                    x:     5,
                    y:     5,
                    w:     wPct1,
                    h:     hPct1,
                    label: 'Firma',
                    tipo:  'firma'
                };
                _campos.push(c);
                _valores[id] = urlRecortada;
                _renderCampo(c);
            }
        });
    }

    function _abrirPickerFirma(campoIdExistente) {
        _inyectarModalFirma();

        var modal      = document.getElementById('fmt-firma-modal');
        var grid       = document.getElementById('fmt-firma-grid');
        var ingenieros = window._fmtIngenieros || [];

        grid.innerHTML = '';

        if (ingenieros.length === 0) {
            grid.innerHTML =
                '<p style="grid-column:1/-1;font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">' +
                'No hay ingenieros con firma registrada.</p>';
        } else {
            ingenieros.forEach(function (ing) {
                if (!ing.firma) return;

                var dataUrl = _firmaADataUrl(ing.firma);

                var card = document.createElement('button');
                card.type = 'button';
                card.style.cssText =
                    'border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 8px;' +
                    'background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s;' +
                    'display:flex;flex-direction:column;align-items:center;gap:6px;width:100%;';
                card.addEventListener('mouseenter', function () {
                    card.style.borderColor = '#7c3aed';
                    card.style.boxShadow   = '0 0 0 3px rgba(124,58,237,.15)';
                });
                card.addEventListener('mouseleave', function () {
                    card.style.borderColor = '#e5e7eb';
                    card.style.boxShadow   = 'none';
                });

                var preview = document.createElement('img');
                preview.src = dataUrl;
                preview.style.cssText =
                    'max-height:52px;width:auto;max-width:100%;display:block;mix-blend-mode:multiply;';

                var nombre = document.createElement('span');
                nombre.textContent = ing.nombre;
                nombre.style.cssText =
                    'font-size:11px;font-weight:600;color:#374151;white-space:nowrap;' +
                    'overflow:hidden;text-overflow:ellipsis;max-width:120px;display:block;';

                card.appendChild(preview);
                card.appendChild(nombre);

                if (ing.cargo) {
                    var cargo = document.createElement('span');
                    cargo.textContent = ing.cargo;
                    cargo.style.cssText =
                        'font-size:10px;color:#9ca3af;white-space:nowrap;' +
                        'overflow:hidden;text-overflow:ellipsis;max-width:120px;display:block;';
                    card.appendChild(cargo);
                }

                card.addEventListener('click', function () {
                    modal.style.display = 'none';
                    _aplicarFirma(dataUrl, campoIdExistente);
                });

                grid.appendChild(card);
            });

            /* si todos tenían firma vacía el grid queda vacío */
            if (!grid.hasChildNodes()) {
                grid.innerHTML =
                    '<p style="grid-column:1/-1;font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">' +
                    'No hay ingenieros con firma registrada.</p>';
            }
        }

        modal.style.display = 'flex';
    }

    /* ===== Modal de firma jefa que recibe ===== */
    function _inyectarModalJefa() {
        if (document.getElementById('fmt-jefa-modal')) return;

        var modal = document.createElement('div');
        modal.id = 'fmt-jefa-modal';
        modal.style.cssText =
            'display:none;position:fixed;inset:0;z-index:99999;' +
            'background:rgba(0,0,0,.55);align-items:center;justify-content:center;';

        modal.innerHTML =
            '<div style="background:#fff;border-radius:14px;' +
                 'max-width:540px;width:94%;box-shadow:0 24px 64px rgba(0,0,0,.35);overflow:hidden;">' +
                '<div style="padding:22px 24px 20px;">' +
                    '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">' +
                        '<h3 style="margin:0;font-size:16px;font-weight:700;color:#111;">Firma — Jefa de Servicio que recibe</h3>' +
                        '<button id="fmt-jefa-cerrar" type="button" ' +
                            'style="background:none;border:none;cursor:pointer;font-size:22px;' +
                                   'color:#9ca3af;line-height:1;padding:0;">×</button>' +
                    '</div>' +
                    '<div id="fmt-jefa-grid" style="' +
                        'display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));' +
                        'gap:10px;max-height:360px;overflow-y:auto;padding-right:4px;' +
                        'margin-bottom:16px;"></div>' +
                    '<div style="display:flex;justify-content:flex-end;">' +
                        '<button id="fmt-jefa-cancelar" type="button" ' +
                            'style="padding:8px 16px;border:1px solid #d1d5db;border-radius:8px;' +
                                   'font-size:13px;font-weight:600;cursor:pointer;' +
                                   'background:#fff;color:#374151;">Cancelar</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        var _cerrar = function () { modal.style.display = 'none'; };
        document.getElementById('fmt-jefa-cerrar').addEventListener('click', _cerrar);
        document.getElementById('fmt-jefa-cancelar').addEventListener('click', _cerrar);
        modal.addEventListener('click', function (e) { if (e.target === modal) _cerrar(); });
    }

    function _aplicarFirmaJefa(dataUrl, campoIdExistente) {
        _recortarFirma(dataUrl, function (urlRecortada, natW, natH) {
            if (campoIdExistente) {
                _valores[campoIdExistente] = urlRecortada;
                var el = document.querySelector('.pdf-campo[data-id="' + campoIdExistente + '"]');
                if (el) {
                    var img2 = el.querySelector('.pdf-firma-img');
                    if (img2) img2.src = urlRecortada;
                    var pw   = el.closest('.pdf-pw');
                    var dims = (pw.dataset.pw || '0,0').split(',');
                    var pwW  = parseFloat(dims[0]) || pw.offsetWidth;
                    var pwH  = parseFloat(dims[1]) || pw.offsetHeight;
                    var wPct = Math.min(20, natW / pwW * 100);
                    var hPct = wPct * (natH / natW) * (pwW / pwH);
                    var campo = _campos.find(function (c) { return c.id === campoIdExistente; });
                    if (campo) { campo.w = wPct; campo.h = hPct; }
                    el.style.width     = (wPct / 100 * pwW) + 'px';
                    el.style.minHeight = (hPct / 100 * pwH) + 'px';
                }
            } else {
                var pw1  = document.querySelector('.pdf-pw[data-page="1"]');
                var pwW1 = 800, pwH1 = 1132;
                if (pw1) {
                    var d = (pw1.dataset.pw || '800,1132').split(',');
                    pwW1  = parseFloat(d[0]) || pw1.offsetWidth;
                    pwH1  = parseFloat(d[1]) || pw1.offsetHeight;
                }
                var wPct1 = Math.min(20, natW / pwW1 * 100);
                var hPct1 = wPct1 * (natH / natW) * (pwW1 / pwH1);

                var id = 'f' + _nextId++;
                var c  = {
                    id:    id,
                    page:  1,
                    x:     5,
                    y:     5,
                    w:     wPct1,
                    h:     hPct1,
                    label: 'Firma recibe',
                    tipo:  'firma_jefe'
                };
                _campos.push(c);
                _valores[id] = urlRecortada;
                _renderCampo(c);
            }
        });
    }

    function _abrirPickerJefa(campoIdExistente) {
        _inyectarModalJefa();

        var modal = document.getElementById('fmt-jefa-modal');
        var grid  = document.getElementById('fmt-jefa-grid');
        var jefas = window._fmtJefas || [];

        grid.innerHTML = '';

        if (jefas.length === 0) {
            grid.innerHTML =
                '<p style="grid-column:1/-1;font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">' +
                'No hay Jefas de Servicio con firma registrada.</p>';
        } else {
            jefas.forEach(function (jefa) {
                if (!jefa.firma) return;

                var dataUrl = _firmaADataUrl(jefa.firma);

                var card = document.createElement('button');
                card.type = 'button';
                card.style.cssText =
                    'border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 8px;' +
                    'background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s;' +
                    'display:flex;flex-direction:column;align-items:center;gap:6px;width:100%;';
                card.addEventListener('mouseenter', function () {
                    card.style.borderColor = '#059669';
                    card.style.boxShadow   = '0 0 0 3px rgba(5,150,105,.15)';
                });
                card.addEventListener('mouseleave', function () {
                    card.style.borderColor = '#e5e7eb';
                    card.style.boxShadow   = 'none';
                });

                var preview = document.createElement('img');
                preview.src = dataUrl;
                preview.style.cssText =
                    'max-height:52px;width:auto;max-width:100%;display:block;mix-blend-mode:multiply;';

                var nombre = document.createElement('span');
                nombre.textContent = jefa.nombre;
                nombre.style.cssText =
                    'font-size:11px;font-weight:600;color:#374151;white-space:nowrap;' +
                    'overflow:hidden;text-overflow:ellipsis;max-width:120px;display:block;';

                card.appendChild(preview);
                card.appendChild(nombre);

                if (jefa.cargo) {
                    var area = document.createElement('span');
                    area.textContent = jefa.cargo;
                    area.style.cssText =
                        'font-size:10px;color:#9ca3af;white-space:nowrap;' +
                        'overflow:hidden;text-overflow:ellipsis;max-width:120px;display:block;';
                    card.appendChild(area);
                }

                card.addEventListener('click', function () {
                    modal.style.display = 'none';
                    _aplicarFirmaJefa(dataUrl, campoIdExistente);
                });

                grid.appendChild(card);
            });

            if (!grid.hasChildNodes()) {
                grid.innerHTML =
                    '<p style="grid-column:1/-1;font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">' +
                    'No hay Jefas de Servicio con firma registrada.</p>';
            }
        }

        modal.style.display = 'flex';
    }

    /* ===== API pública ===== */
    window.pdfOverlay = {

        toggleAddField: function () {
            _addMode = !_addMode;
            var btn = document.getElementById('btn-pdf-add');
            if (btn) btn.setAttribute('data-active', String(_addMode));
            _setCursor(_addMode ? 'crosshair' : 'default');
        },

        abrirPickerFirma: function () {
            _abrirPickerFirma(null);
        },

        abrirPickerFirmaJefa: function () {
            _abrirPickerJefa(null);
        },

        enviarFirmaIngeniero: function () {
            _leerValores();
            var firmaCampo = _campos.find(function (c) { return c.tipo === 'firma'; });
            var firmaData  = firmaCampo ? _valores[firmaCampo.id] : null;

            if (!firmaData && window._firmaExistente) {
                /* _firmaExistente puede ser JSON {imagen,posicion} o data URL directo */
                var existing = window._firmaExistente;
                if (existing && existing.indexOf('{') === 0) {
                    try { existing = JSON.parse(existing).imagen || existing; } catch (e) {}
                }
                firmaData = existing;
            }

            if (!firmaData) {
                alert('Agrega tu firma antes de enviar el reporte.');
                return;
            }

            /* Pasar la posición si el campo fue colocado manualmente en el PDF */
            var firmaPosicion = firmaCampo
                ? JSON.stringify({ page: firmaCampo.page || 1, x: firmaCampo.x, y: firmaCampo.y, w: firmaCampo.w, h: firmaCampo.h })
                : null;

            _lwCall('guardarFirmaYEnviar', firmaData, firmaPosicion);
        },

        guardarPlantilla: function () {
            _leerValores();
            var camposPlantilla = _campos.filter(function (c) { return c.tipo !== 'firma' && c.tipo !== 'firma_jefe'; });
            _lwCall('guardarCamposPdf', JSON.stringify(camposPlantilla));
        },

        guardarRegistro: function () {
            _leerValores();
            _lwCall('guardarRegistroPdf', JSON.stringify(_campos), JSON.stringify(_valores));
        },

        guardarBorrador: function () {
            _leerValores();
            _lwCall('guardarBorradorPdf', JSON.stringify(_campos), JSON.stringify(_valores));
        },

        initViewer: function (opts) {
            setTimeout(function () {
                var cId = opts.containerId || 'pdf-overlay-viewer';
                if (!document.getElementById(cId)) return;
                _init({
                    containerId: cId,
                    url:         opts.url,
                    campos:      opts.campos  || [],
                    valores:     opts.valores || {},
                    modoVisor:   true,
                });
            }, 120);
        }
    };

    /* ===== Evento DOM directo (para páginas de firma sin dispatch Livewire) ===== */
    document.addEventListener('fmt:init-direct', function (e) {
        var p = e.detail;
        if (p.ingenieros) window._fmtIngenieros = p.ingenieros;
        setTimeout(function () {
            if (!document.getElementById('pdf-overlay-editor')) return;
            _init({ url: p.url, campos: p.campos || [], valores: p.valores || {}, modoVisor: false });
        }, 50);
    });

    /* ===== Escuchar eventos Livewire ===== */
    document.addEventListener('livewire:init', function () {

        Livewire.on('fmt:editar-pdf', function (params) {
            var p = Array.isArray(params) ? params[0] : params;
            if (p.ingenieros) window._fmtIngenieros = p.ingenieros;
            if (p.jefas)      window._fmtJefas      = p.jefas;
            setTimeout(function () {
                if (!document.getElementById('pdf-overlay-editor')) return;
                _init({ url: p.url, campos: p.campos || [], valores: p.valores || {}, modoVisor: false });
            }, 120);
        });

        Livewire.on('fmt:ver-pdf', function (params) {
            var p = Array.isArray(params) ? params[0] : params;
            setTimeout(function () {
                if (!document.getElementById('pdf-overlay-viewer')) return;
                _init({ url: p.url, campos: p.campos || [], valores: p.valores || {}, modoVisor: true });
            }, 120);
        });

        Livewire.on('fmt:ver-mantenimiento', function (params) {
            var p = Array.isArray(params) ? params[0] : params;
            setTimeout(function () {
                if (!document.getElementById('pdf-overlay-mantenimiento')) return;
                _init({
                    containerId: 'pdf-overlay-mantenimiento',
                    url:         p.url,
                    campos:      p.campos  || [],
                    valores:     p.valores || {},
                    modoVisor:   true,
                });
            }, 200);
        });
    });

})();
