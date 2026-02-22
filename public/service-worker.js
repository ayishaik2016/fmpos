self.addEventListener("install", event => {
  event.waitUntil(
    caches.open("laravel-pwa-v1").then(cache => {
      return cache.addAll([
        "/",
        "/assets/css/app.css",
        "/assets/js/app.js",
        "/offline" // custom offline page
      ]);
    })
  );
});

self.addEventListener("fetch", event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request).catch(() => caches.match("/offline"));
    })
  );
});
