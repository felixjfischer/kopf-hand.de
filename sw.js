/**
 * Service Worker für nautimo.de
 * Offline-Funktionalität für Event-Kalender und Spa-Buchungen
 * 
 * @author Kopf & Hand
 * @version 1.0
 */

const CACHE_NAME = 'nautimo-v1';
const CACHE_URLS = [
    '/',
    '/style.css',
    '/wp-content/themes/divi/style.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'
];

// Service Worker Installation
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(CACHE_URLS))
    );
});

// Cache-First-Strategie für statische Ressourcen
self.addEventListener('fetch', event => {
    // Nur GET-Requests cachen
    if (event.request.method !== 'GET') return;
    
    // Keine Admin-Bereiche cachen
    if (event.request.url.includes('/wp-admin/')) return;
    
    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                // Cache-Hit: Sofort zurückgeben
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // Cache-Miss: Netzwerk-Request
                return fetch(event.request)
                    .then(response => {
                        // Nur erfolgreiche Responses cachen
                        if (response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME)
                                .then(cache => cache.put(event.request, responseClone));
                        }
                        return response;
                    })
                    .catch(() => {
                        // Offline-Fallback für Event-Seiten
                        if (event.request.url.includes('/events/')) {
                            return new Response(
                                '<h1>Offline</h1><p>Diese Veranstaltung ist offline nicht verfügbar.</p>',
                                { headers: { 'Content-Type': 'text/html' } }
                            );
                        }
                    });
            })
    );
});

// Alte Caches bereinigen
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});