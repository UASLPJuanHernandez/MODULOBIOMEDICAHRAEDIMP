// Módulo de editor de formatos — sin Alpine, inicializado vía evento Livewire
(function () {
    var _q = null; // instancia Quill activa

    /* ── Iniciar Quill ── */
    function iniciarEditor(contenido) {
        var toolbarEl = document.getElementById('fmt-toolbar');
        var editorEl  = document.getElementById('fmt-editor');
        if (!toolbarEl || !editorEl) return;
        if (editorEl._qlInited) return;
        editorEl._qlInited = true;

        var Size = Quill.import('attributors/style/size');
        Size.whitelist = ['10px','11px','12px','14px','16px','18px','20px',
                          '24px','28px','32px','36px','48px'];
        Quill.register(Size, true);

        var Font = Quill.import('attributors/style/font');
        Font.whitelist = ['arial','times','courier','georgia','verdana'];
        Quill.register(Font, true);

        _q = new Quill('#fmt-editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: '#fmt-toolbar',
                    handlers: {
                        firma:  function () {
                            document.dispatchEvent(new CustomEvent('fmt:abrir-firma'));
                        },
                        pagina: function () { _agregarPagina(); },
                        image:  function () {
                            var inp = document.getElementById('fmt-img-input');
                            if (inp) inp.click();
                        }
                    }
                }
            }
        });

        if (contenido && contenido.trim()) {
            var looksLike = /<[a-zA-Z][\s\S]*?>/.test(contenido);
            var html = looksLike
                ? contenido
                : contenido.split('\n').map(function (l) {
                    l = l.trim();
                    return '<p>' + (l === '' ? '<br>' : l.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')) + '</p>';
                  }).join('');
            setTimeout(function () {
                if (!_q) return;
                var delta = _q.clipboard.convert(html);
                _q.setContents(delta, 'silent');
                _q.setSelection(0, 0);
            }, 80);
        }
    }

    function _agregarPagina() {
        if (!_q) return;
        var pos = (_q.getSelection(true) || { index: _q.getLength() }).index;
        _q.insertText(pos, '\n', {});
        _q.clipboard.dangerouslyPasteHTML(pos + 1, '<p><br></p><hr class="page-break"><p><br></p>');
        _q.setSelection(pos + 4);
    }

    /* ── API pública usada desde la vista ── */
    window._fmtGuardar = function () {
        if (!_q) return;
        var html    = _q.root.innerHTML;
        var wireEl  = document.querySelector('[wire\\:id]');
        if (wireEl) {
            window.Livewire.find(wireEl.getAttribute('wire:id'))
                .call('guardarRegistroConContenido', html);
        }
    };

    window._fmtInsertarImagen = function (event) {
        if (!_q) return;
        var file = event.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            var range = _q.getSelection(true) || { index: _q.getLength() };
            _q.insertEmbed(range.index, 'image', e.target.result);
            _q.setSelection(range.index + 1);
        };
        reader.readAsDataURL(file);
        event.target.value = '';
    };

    window._fmtInsertarFirma = function (dataUrl) {
        if (!_q || !dataUrl) return;
        var range = _q.getSelection(true) || { index: _q.getLength() };
        _q.insertEmbed(range.index, 'image', dataUrl);
        _q.setSelection(range.index + 1);
    };

    /* ── Escuchar el evento Livewire "fmt:editar" ── */
    document.addEventListener('livewire:init', function () {
        Livewire.on('fmt:editar', function (params) {
            // Livewire 3 pasa params como array con un objeto
            var contenido = '';
            if (Array.isArray(params) && params[0] && params[0].contenido !== undefined) {
                contenido = params[0].contenido;
            } else if (params && params.contenido !== undefined) {
                contenido = params.contenido;
            }

            // Destruir instancia anterior si existe
            _q = null;
            var edEl = document.getElementById('fmt-editor');
            if (edEl) { edEl._qlInited = false; edEl.innerHTML = ''; }

            // Pequeño delay para que Livewire termine el morph del DOM
            setTimeout(function () { iniciarEditor(contenido); }, 100);
        });
    });
})();
