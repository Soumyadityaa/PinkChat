const CACHE_NAME = 'pinkchat-pwa-v1';
const STATIC_ASSETS = [
    './style.css',
    './script.js',
    './icon.svg',
    './manifest.json'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // Always use live network for POST requests and PHP dynamic endpoints
    if (
        req.method !== 'GET' ||
        url.pathname.endsWith('.php')
    ) {
        event.respondWith(fetch(req));
        return;
    }

    // Network-first with cache fallback for static assets (CSS, JS, SVG)
    event.respondWith(
        fetch(req)
            .then((networkRes) => {
                const resClone = networkRes.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(req, resClone));
                return networkRes;
            })
            .catch(() => caches.match(req))
    );
});