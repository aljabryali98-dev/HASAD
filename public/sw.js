// Service Worker for Hasad ERP Progressive Web App (PWA)
const CACHE_NAME = 'hasad-pwa-v1';

const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/offline.html',
    '/css/hasad-theme.css',
    '/favicon.ico',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

// Install: pre-cache static assets & take control immediately
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[PWA SW] Pre-caching non-fatal warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate: clean up older caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Network-first for dynamic content, cache fallback for offline
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Ignore non-GET and chrome-extension requests
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    // Static assets (CSS, JS, Fonts, Images) -> Cache-first with network fallback
    if (
        request.url.includes('/css/') ||
        request.url.includes('/js/') ||
        request.url.includes('/images/') ||
        request.url.includes('/fonts/') ||
        request.url.includes('cdn.jsdelivr.net') ||
        request.url.includes('fonts.googleapis.com') ||
        request.url.includes('fonts.gstatic.com') ||
        request.url.includes('cdnjs.cloudflare.com')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // HTML Navigation requests -> Network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(request).then((response) => {
                    return response || caches.match('/offline.html');
                });
            })
        );
        return;
    }

    // All other requests -> Network-first
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
