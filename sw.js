const CACHE_NAME = 'scheduling-system-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/manifest.json',
    'https://cdn.tailwindcss.com',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    '/app/Resources/image/logo/scheduling-logo.png',
    '/app/Resources/image/icons/icon-72x72.png',
    '/app/Resources/image/icons/icon-96x96.png',
    '/app/Resources/image/icons/icon-128x128.png',
    '/app/Resources/image/icons/icon-144x144.png',
    '/app/Resources/image/icons/icon-152x152.png',
    '/app/Resources/image/icons/icon-192x192.png',
    '/app/Resources/image/icons/icon-384x384.png',
    '/app/Resources/image/icons/icon-512x512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response;
                }
                return fetch(event.request)
                    .then(response => {
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME)
                            .then(cache => {
                                cache.put(event.request, responseToCache);
                            });
                        return response;
                    });
            })
    );
}); 