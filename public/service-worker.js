/**
 * Church Management System Service Worker
 * 
 * This service worker enables offline functionality by:
 * 1. Caching critical assets for offline use
 * 2. Providing offline fallbacks for API requests
 * 3. Syncing data when connectivity is restored
 */

// Cache names for different types of resources
const CACHE_NAMES = {
  static: 'church-mgmt-static-v1',
  dynamic: 'church-mgmt-dynamic-v1',
  api: 'church-mgmt-api-v1',
  images: 'church-mgmt-images-v1'
};

// Resources to cache immediately when the service worker is installed
const STATIC_RESOURCES = [
  '/',
  '/index.html',
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  '/js/manifest.js',
  '/js/vendor.js',
  '/images/logo.png',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
  '/images/fallback.png',
  '/favicon.ico'
];

// API routes to cache with network-first strategy
const API_ROUTES = [
  '/api/dashboard-stats',
  '/api/user-profile',
  '/api/church-settings'
];

// Install event - Cache static resources
self.addEventListener('install', event => {
  console.log('[Service Worker] Installing Service Worker...');
  
  // Skip waiting to ensure the new service worker activates immediately
  self.skipWaiting();
  
  event.waitUntil(
    caches.open(CACHE_NAMES.static)
      .then(cache => {
        console.log('[Service Worker] Precaching App Shell');
        return cache.addAll(STATIC_RESOURCES);
      })
      .catch(error => {
        console.error('[Service Worker] Precaching failed:', error);
      })
  );
});

// Activate event - Clean up old caches
self.addEventListener('activate', event => {
  console.log('[Service Worker] Activating Service Worker...');
  
  // Claim clients to ensure the service worker controls all pages immediately
  self.clients.claim();
  
  event.waitUntil(
    caches.keys()
      .then(keyList => {
        return Promise.all(keyList.map(key => {
          // Check if the cache name is not in our current cache names
          if (!Object.values(CACHE_NAMES).includes(key)) {
            console.log('[Service Worker] Removing old cache:', key);
            return caches.delete(key);
          }
        }));
      })
  );
  
  return self.clients.claim();
});

// Fetch event - Handle all network requests
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }
  
  // Skip browser extension requests and other non-http(s) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }
  
  // Handle API requests (network-first strategy with offline fallback)
  if (url.pathname.startsWith('/api/')) {
    handleApiRequest(event);
    return;
  }
  
  // Handle image requests (cache-first strategy with fallback)
  if (
    event.request.destination === 'image' || 
    url.pathname.match(/\.(jpg|jpeg|png|gif|svg|webp)$/i)
  ) {
    handleImageRequest(event);
    return;
  }
  
  // Handle static assets (cache-first strategy)
  if (
    event.request.destination === 'style' ||
    event.request.destination === 'script' ||
    event.request.destination === 'font' ||
    url.pathname.match(/\.(css|js|woff2?|ttf|eot)$/i)
  ) {
    handleStaticRequest(event);
    return;
  }
  
  // Handle HTML navigation (network-first strategy with offline fallback)
  if (event.request.mode === 'navigate' || event.request.destination === 'document') {
    handleNavigationRequest(event);
    return;
  }
  
  // Default strategy (network-first with dynamic caching)
  handleDefaultRequest(event);
});

/**
 * Handle API requests with network-first strategy and offline fallback
 */
function handleApiRequest(event) {
  // Check if this is a critical API that should be cached
  const isCriticalApi = API_ROUTES.some(route => 
    event.request.url.includes(route)
  );
  
  if (isCriticalApi) {
    // Network-first strategy for critical APIs
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Clone the response to store in cache
          const clonedResponse = response.clone();
          
          caches.open(CACHE_NAMES.api)
            .then(cache => {
              cache.put(event.request, clonedResponse);
            });
          
          return response;
        })
        .catch(error => {
          console.log('[Service Worker] API fetch failed, falling back to cache', error);
          
          return caches.match(event.request)
            .then(cachedResponse => {
              if (cachedResponse) {
                // Add offline header to indicate this is cached data
                const headers = new Headers(cachedResponse.headers);
                headers.append('X-Offline-Data', 'true');
                
                return new Response(cachedResponse.body, {
                  status: cachedResponse.status,
                  statusText: cachedResponse.statusText + ' (Offline)',
                  headers
                });
              }
              
              // If no cached response, return a JSON error
              return new Response(JSON.stringify({
                error: 'You are offline and this data is not available offline.',
                offline: true,
                timestamp: new Date().toISOString()
              }), {
                status: 503,
                headers: { 'Content-Type': 'application/json' }
              });
            });
        })
    );
  } else {
    // For non-critical APIs, just try network and fail gracefully
    event.respondWith(
      fetch(event.request)
        .catch(error => {
          console.log('[Service Worker] Non-critical API fetch failed', error);
          
          return new Response(JSON.stringify({
            error: 'You are offline. This feature requires internet connection.',
            offline: true,
            timestamp: new Date().toISOString()
          }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
          });
        })
    );
  }
}

/**
 * Handle image requests with cache-first strategy and fallback image
 */
function handleImageRequest(event) {
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        
        return fetch(event.request)
          .then(response => {
            // Cache the fetched image
            const clonedResponse = response.clone();
            
            caches.open(CACHE_NAMES.images)
              .then(cache => {
                cache.put(event.request, clonedResponse);
              });
            
            return response;
          })
          .catch(error => {
            console.log('[Service Worker] Image fetch failed, using fallback', error);
            
            // Return fallback image
            return caches.match('/images/fallback.png');
          });
      })
  );
}

/**
 * Handle static assets with cache-first strategy
 */
function handleStaticRequest(event) {
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        
        return fetch(event.request)
          .then(response => {
            // Cache the fetched static asset
            const clonedResponse = response.clone();
            
            caches.open(CACHE_NAMES.static)
              .then(cache => {
                cache.put(event.request, clonedResponse);
              });
            
            return response;
          });
      })
  );
}

/**
 * Handle HTML navigation with network-first strategy and offline fallback
 */
function handleNavigationRequest(event) {
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache the fetched page
        const clonedResponse = response.clone();
        
        caches.open(CACHE_NAMES.dynamic)
          .then(cache => {
            cache.put(event.request, clonedResponse);
          });
        
        return response;
      })
      .catch(error => {
        console.log('[Service Worker] Navigation fetch failed, falling back to cache', error);
        
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            // If no cached version, show offline page
            return caches.match('/offline.html');
          });
      })
  );
}

/**
 * Handle all other requests with network-first and dynamic caching
 */
function handleDefaultRequest(event) {
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache the response
        const clonedResponse = response.clone();
        
        caches.open(CACHE_NAMES.dynamic)
          .then(cache => {
            cache.put(event.request, clonedResponse);
          });
        
        return response;
      })
      .catch(error => {
        console.log('[Service Worker] Default fetch failed, falling back to cache', error);
        
        return caches.match(event.request);
      })
  );
}

// Background sync for offline data submission
self.addEventListener('sync', event => {
  console.log('[Service Worker] Background Sync', event);
  
  if (event.tag === 'sync-donations') {
    event.waitUntil(syncDonations());
  } else if (event.tag === 'sync-attendance') {
    event.waitUntil(syncAttendance());
  } else if (event.tag === 'sync-members') {
    event.waitUntil(syncMembers());
  }
});

// Push notification support
self.addEventListener('push', event => {
  console.log('[Service Worker] Push Notification received', event);
  
  let data = { title: 'New Notification', body: 'Something happened in your church app.' };
  
  if (event.data) {
    data = JSON.parse(event.data.text());
  }
  
  const options = {
    body: data.body,
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/'
    }
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', event => {
  const notification = event.notification;
  const action = event.action;
  const url = notification.data.url;
  
  console.log('[Service Worker] Notification click', action);
  
  notification.close();
  
  // Open the app and navigate to the specified URL
  event.waitUntil(
    clients.matchAll({ type: 'window' })
      .then(clientList => {
        // Check if there's already a window open
        for (const client of clientList) {
          if (client.url === url && 'focus' in client) {
            return client.focus();
          }
        }
        
        // If no window is open, open a new one
        if (clients.openWindow) {
          return clients.openWindow(url);
        }
      })
  );
});

// Helper functions for background sync

/**
 * Sync offline donations when online
 */
async function syncDonations() {
  try {
    const db = await openDatabase();
    const offlineDonations = await db.getAll('offline-donations');
    
    console.log('[Service Worker] Syncing donations', offlineDonations);
    
    for (const donation of offlineDonations) {
      try {
        const response = await fetch('/api/donations', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Offline-Sync': 'true'
          },
          body: JSON.stringify(donation.data)
        });
        
        if (response.ok) {
          // Remove from offline store if successfully synced
          await db.delete('offline-donations', donation.id);
        }
      } catch (error) {
        console.error('[Service Worker] Failed to sync donation', donation, error);
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error syncing donations', error);
  }
}

/**
 * Sync offline attendance records when online
 */
async function syncAttendance() {
  try {
    const db = await openDatabase();
    const offlineAttendance = await db.getAll('offline-attendance');
    
    console.log('[Service Worker] Syncing attendance', offlineAttendance);
    
    for (const record of offlineAttendance) {
      try {
        const response = await fetch('/api/attendance', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Offline-Sync': 'true'
          },
          body: JSON.stringify(record.data)
        });
        
        if (response.ok) {
          // Remove from offline store if successfully synced
          await db.delete('offline-attendance', record.id);
        }
      } catch (error) {
        console.error('[Service Worker] Failed to sync attendance', record, error);
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error syncing attendance', error);
  }
}

/**
 * Sync offline member updates when online
 */
async function syncMembers() {
  try {
    const db = await openDatabase();
    const offlineMembers = await db.getAll('offline-members');
    
    console.log('[Service Worker] Syncing members', offlineMembers);
    
    for (const member of offlineMembers) {
      try {
        const response = await fetch('/api/members' + (member.data.id ? `/${member.data.id}` : ''), {
          method: member.data.id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Offline-Sync': 'true'
          },
          body: JSON.stringify(member.data)
        });
        
        if (response.ok) {
          // Remove from offline store if successfully synced
          await db.delete('offline-members', member.id);
        }
      } catch (error) {
        console.error('[Service Worker] Failed to sync member', member, error);
      }
    }
  } catch (error) {
    console.error('[Service Worker] Error syncing members', error);
  }
}

/**
 * Open IndexedDB database for offline storage
 */
function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('church-mgmt-offline', 1);
    
    request.onerror = event => {
      reject('Error opening offline database');
    };
    
    request.onsuccess = event => {
      const db = event.target.result;
      
      // Create a simple wrapper for the database
      const dbWrapper = {
        getAll: (storeName) => {
          return new Promise((resolve, reject) => {
            const transaction = db.transaction(storeName, 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();
            
            request.onsuccess = event => {
              resolve(event.target.result);
            };
            
            request.onerror = event => {
              reject('Error getting data from ' + storeName);
            };
          });
        },
        delete: (storeName, id) => {
          return new Promise((resolve, reject) => {
            const transaction = db.transaction(storeName, 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(id);
            
            request.onsuccess = event => {
              resolve();
            };
            
            request.onerror = event => {
              reject('Error deleting data from ' + storeName);
            };
          });
        }
      };
      
      resolve(dbWrapper);
    };
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      
      // Create object stores for offline data
      if (!db.objectStoreNames.contains('offline-donations')) {
        db.createObjectStore('offline-donations', { keyPath: 'id', autoIncrement: true });
      }
      
      if (!db.objectStoreNames.contains('offline-attendance')) {
        db.createObjectStore('offline-attendance', { keyPath: 'id', autoIncrement: true });
      }
      
      if (!db.objectStoreNames.contains('offline-members')) {
        db.createObjectStore('offline-members', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}
