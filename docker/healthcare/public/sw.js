const CACHE = 'his-cache-v1';

const ASSETS = [
    '/',
    '/offline',
];

self.addEventListener('install', (event) => {

    event.waitUntil(
        caches.open(CACHE)
            .then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', (event) => {

    event.respondWith(

        caches.match(event.request)
            .then(response => {

                return response || fetch(event.request);
            })
    );
});