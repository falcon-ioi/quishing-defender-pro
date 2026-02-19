const CACHE_NAME = 'quishing-pro-v2';
const ASSETS = [
    '/',
    '/index.html',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json'
];

// Install
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Fetch
self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;

    e.respondWith(
        caches.match(e.request).then(cached => {
            const fetched = fetch(e.request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
                }
                return response;
            }).catch(() => cached);

            return cached || fetched;
        })
    );
});

// Push notifications
self.addEventListener('push', e => {
    const data = e.data?.json() || {};
    const options = {
        body: data.body || 'Quishing Defender Alert',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-72.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/' }
    };

    e.waitUntil(self.registration.showNotification(data.title || 'Quishing Defender', options));
});

// Notification click
self.addEventListener('notificationclick', e => {
    e.notification.close();
    e.waitUntil(clients.openWindow(e.notification.data.url));
});
