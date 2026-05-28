import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Escuchar canal privado de notificaciones administrativas
    window.Echo.private('admin-notifications')
        .listen('.admin.notification', (e) => {
            console.debug('Admin notification recibida', e);
            if (!window.dispatchEvent) return;
            const detail = { title: e.title, message: e.message, action: e.action, data: e.data, user: e.user, at: e.timestamp };
            window.dispatchEvent(new CustomEvent('admin-notification', { detail }));

            if (!document.querySelector('#admin-realtime-notifications')) {
                const c = document.createElement('div');
                c.id = 'admin-realtime-notifications';
                c.style.position = 'fixed'; c.style.top = '1rem'; c.style.right = '1rem'; c.style.zIndex = 9999; c.style.display='flex'; c.style.flexDirection='column'; c.style.gap='8px';
                document.body.appendChild(c);
            }
            const wrap = document.createElement('div');
            wrap.style.background = '#1f2937';
            wrap.style.color = 'white';
            wrap.style.padding = '10px 14px';
            wrap.style.borderRadius = '6px';
            wrap.style.boxShadow = '0 4px 12px rgba(0,0,0,0.25)';
            wrap.style.fontSize = '13px';
            wrap.innerHTML = `<strong>${detail.title}</strong><br>${detail.message}`;
            document.getElementById('admin-realtime-notifications').appendChild(wrap);
            setTimeout(()=>{ wrap.remove(); }, 8000);
        });
} catch (err) {
    console.warn('Reverb no disponible, notificaciones en tiempo real desactivadas.', err);
}
