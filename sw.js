// Service Worker for Hazel Stock Management PWA
const CACHE_NAME = 'hazel-stock-v1.0.0';
const RUNTIME_CACHE = 'hazel-runtime-v1';

// Assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/index.php',
  '/login.php',
  '/view-records.php',
  '/manage-employees.php',
  '/manage-materials.php',
  '/add-stock.php',
  '/css/style.css',
  '/assets/hazel-logo.png',
  '/assets/phuriboss.jpg',
  '/offline.html'
];

// Install event - cache static assets
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[SW] Installation complete');
        return self.skipWaiting(); // Activate immediately
      })
      .catch(error => {
        console.error('[SW] Installation failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Activation complete');
        return self.clients.claim(); // Take control immediately
      })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // Skip API calls for POST/PUT/DELETE (only cache GET)
  if (request.method !== 'GET') {
    return;
  }

  // Handle API requests differently
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirst(request));
    return;
  }

  // Handle photo uploads
  if (url.pathname.startsWith('/stock-photos/')) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Handle static assets and pages
  event.respondWith(cacheFirst(request));
});

// Cache First Strategy (for static assets)
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('[SW] Serving from cache:', request.url);
      return cachedResponse;
    }

    console.log('[SW] Fetching from network:', request.url);
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('[SW] Fetch failed, serving offline page:', error);
    
    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      return caches.match('/offline.html');
    }
    
    // Return cached version if available
    return caches.match(request);
  }
}

// Network First Strategy (for API calls)
async function networkFirst(request) {
  try {
    console.log('[SW] API call - network first:', request.url);
    const networkResponse = await fetch(request);
    
    // Cache successful API responses
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed, trying cache:', request.url);
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline response for API calls
    return new Response(
      JSON.stringify({
        success: false,
        message: 'ไม่มีการเชื่อมต่ออินเทอร์เน็ต',
        offline: true
      }),
      {
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Handle messages from clients
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
      })
    );
  }
});

// Background sync for offline data submission
self.addEventListener('sync', event => {
  if (event.tag === 'sync-stock-data') {
    event.waitUntil(syncStockData());
  }
});

async function syncStockData() {
  console.log('[SW] Syncing offline stock data...');
  // This would sync any offline data stored in IndexedDB
  // Implementation depends on your offline storage strategy
}

console.log('[SW] Service Worker loaded');
