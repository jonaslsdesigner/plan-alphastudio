const CACHE_NAME = 'alpha-planilhas-v5';
const BASE_PATH = new URL(self.registration.scope).pathname;
const STATIC_ASSETS = [
  BASE_PATH,
  `${BASE_PATH}manifest.webmanifest`,
  `${BASE_PATH}assets/css/app.css`,
  `${BASE_PATH}assets/js/app.js`,
  `${BASE_PATH}icon.svg`
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        return response;
      })
      .catch(() => caches.match(event.request).then((cached) => cached || caches.match(BASE_PATH)))
  );
});
