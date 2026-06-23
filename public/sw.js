const CACHE = 'condux-v1';
const OFFLINE_URL = '/';

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll([OFFLINE_URL])).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    e.respondWith(
        fetch(e.request).catch(() => caches.match(e.request).then(r => r || caches.match(OFFLINE_URL)))
    );
});

// Push notification recebida
self.addEventListener('push', e => {
    let data = { title: 'Condux', body: 'Nova notificação', url: '/' };
    try { data = Object.assign(data, e.data.json()); } catch {}

    e.waitUntil(
        self.registration.showNotification(data.title, {
            body:    data.body,
            icon:    '/icons/icon-192.png',
            badge:   '/icons/icon-192.png',
            tag:     data.tag  || 'condux',
            data:    { url: data.url || '/' },
            vibrate: [200, 100, 200],
        })
    );
});

// Clique na notificação → abre/foca a janela
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const target = e.notification.data?.url || '/';
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(ws => {
            for (const w of ws) {
                if (new URL(w.url).pathname === target) { w.focus(); return; }
            }
            clients.openWindow(target);
        })
    );
});
