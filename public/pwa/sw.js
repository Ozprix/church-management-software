// Service Worker for Church Management System
const CACHE_NAME = 'chcms-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/images/logo.png',
  '/offline.html',
  // Add other assets that should be available offline
];

// Install event - cache assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        
        // Clone the request
        const fetchRequest = event.request.clone();
        
        return fetch(fetchRequest)
          .then(response => {
            // Check if valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            // Clone the response
            const responseToCache = response.clone();
            
            // Add to cache
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });
              
            return response;
          })
          .catch(() => {
            // If fetch fails, show offline page for navigate requests
            if (event.request.mode === 'navigate') {
              return caches.match('/offline.html');
            }
          });
      })
  );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
  if (event.tag === 'sync-attendance') {
    event.waitUntil(syncAttendance());
  } else if (event.tag === 'sync-donations') {
    event.waitUntil(syncDonations());
  }
});

// Helper functions for background sync
async function syncAttendance() {
  try {
    const attendanceData = await getAttendanceDataFromIndexedDB();
    if (attendanceData && attendanceData.length > 0) {
      const response = await fetch('/api/attendance/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ records: attendanceData }),
      });
      
      if (response.ok) {
        await clearSyncedAttendanceFromIndexedDB();
      }
    }
  } catch (error) {
    console.error('Sync attendance failed:', error);
  }
}

async function syncDonations() {
  try {
    const donationData = await getDonationDataFromIndexedDB();
    if (donationData && donationData.length > 0) {
      const response = await fetch('/api/donations/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ records: donationData }),
      });
      
      if (response.ok) {
        await clearSyncedDonationsFromIndexedDB();
      }
    }
  } catch (error) {
    console.error('Sync donations failed:', error);
  }
}

// These functions would be implemented to work with IndexedDB
// Placeholder implementations
function getAttendanceDataFromIndexedDB() {
  return Promise.resolve([]);
}

function clearSyncedAttendanceFromIndexedDB() {
  return Promise.resolve();
}

function getDonationDataFromIndexedDB() {
  return Promise.resolve([]);
}

function clearSyncedDonationsFromIndexedDB() {
  return Promise.resolve();
}
