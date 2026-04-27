console.log('[ciclo] pizarron-ciclo.js cargado');

// Indicador visual temporal para debug
const dbg = document.createElement('div');
dbg.id = 'ciclo-debug';
dbg.style.cssText = 'position:fixed;bottom:60px;right:10px;background:red;color:white;padding:6px 10px;font-size:13px;z-index:99999;border-radius:6px;font-family:monospace;';
dbg.textContent = 'ciclo: cargado';
document.body.appendChild(dbg);

// CSS en <head>: inmune a Livewire morph
const cicloStyle = document.createElement('style');
cicloStyle.textContent =
    'body:not(.ciclo-calendario) #seccion-calendario{display:none!important}' +
    'body.ciclo-calendario #seccion-pizarron{display:none!important}';
document.head.appendChild(cicloStyle);

let lastCount    = 0;
let enCalendario = false;
let tick         = 0;
let calTick      = 0;

function initCiclo() {
    lastCount = document.querySelectorAll('[data-reporte]').length;
    console.log('[ciclo] iniciado, reportes:', lastCount);

    // Tick cada segundo
    setInterval(() => {
        if (!enCalendario) {
            tick++;
            const d = document.getElementById('ciclo-debug');
            if (d) d.textContent = 'pizarron: ' + tick + 's / 60s';
            if (tick >= 60) {
                enCalendario = true;
                calTick = 0;
                document.body.classList.add('ciclo-calendario');
            }
        } else {
            calTick++;
            const d = document.getElementById('ciclo-debug');
            if (d) d.textContent = 'calendario: ' + calTick + 's / 30s';
            if (calTick >= 30) {
                enCalendario = false;
                tick = 0;
                document.body.classList.remove('ciclo-calendario');
            }
        }
    }, 1000);

    // Revisar reportes nuevos cada 3 segundos
    setInterval(() => {
        const newCount = document.querySelectorAll('[data-reporte]').length;
        if (newCount > lastCount) {
            console.log('[ciclo] reporte nuevo -> pizarron');
            enCalendario = false;
            tick = 0;
            document.body.classList.remove('ciclo-calendario');
        }
        lastCount = newCount;
    }, 3000);

    // Refresh Livewire cada 8 segundos
    setInterval(() => {
        if (window.Livewire) {
            Livewire.all().forEach(c => c.$refresh());
        }
    }, 8000);
}

// Arrancar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCiclo);
} else {
    initCiclo();
}
