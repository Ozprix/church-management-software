/**
 * Offline Service
 * 
 * A service for managing offline functionality throughout the application.
 * Provides utilities for:
 * - Detecting network status
 * - Registering service worker
 * - Storing data for offline use
 * - Syncing data when back online
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useToast } from 'vue-toastification';

// IndexedDB database name and version
const DB_NAME = 'church-mgmt-offline';
const DB_VERSION = 1;

// Store names for different types of data
const STORES = {
  donations: 'offline-donations',
  attendance: 'offline-attendance',
  members: 'offline-members',
  settings: 'offline-settings'
};

/**
 * Create a composable function for offline functionality
 * @returns {Object} - Offline utilities
 */
export function useOffline() {
  const toast = useToast();
  
  // Network status
  const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);
  const wasOffline = ref(false);
  
  // Service worker registration status
  const serviceWorkerRegistered = ref(false);
  const serviceWorkerRegistration = ref(null);
  
  // IndexedDB database
  let db = null;
  
  // Computed property for offline mode
  const isOfflineMode = computed(() => !isOnline.value);
  
  /**
   * Update online status and show appropriate notifications
   */
  const updateOnlineStatus = () => {
    const previousStatus = isOnline.value;
    isOnline.value = typeof navigator !== 'undefined' ? navigator.onLine : true;
    
    // Show notification when status changes
    if (previousStatus !== isOnline.value) {
      if (isOnline.value) {
        toast.success('You are back online! Syncing your data...', {
          timeout: 3000
        });
        wasOffline.value = true;
        syncOfflineData();
      } else {
        toast.info('You are offline. Some features may be limited.', {
          timeout: 5000
        });
      }
    }
  };
  
  /**
   * Register the service worker for offline functionality
   */
  const registerServiceWorker = async () => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/service-worker.js');
        
        console.log('Service Worker registered with scope:', registration.scope);
        serviceWorkerRegistered.value = true;
        serviceWorkerRegistration.value = registration;
        
        // Listen for service worker updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              toast.info('New content is available. Please refresh the page.', {
                timeout: 0,
                closeButton: true
              });
            }
          });
        });
        
        return registration;
      } catch (error) {
        console.error('Service Worker registration failed:', error);
        serviceWorkerRegistered.value = false;
        return null;
      }
    }
    
    return null;
  };
  
  /**
   * Open the IndexedDB database for offline storage
   */
  const openDatabase = () => {
    return new Promise((resolve, reject) => {
      if (db) {
        resolve(db);
        return;
      }
      
      if (!('indexedDB' in window)) {
        reject('IndexedDB not supported');
        return;
      }
      
      const request = indexedDB.open(DB_NAME, DB_VERSION);
      
      request.onerror = event => {
        console.error('Error opening IndexedDB:', event.target.error);
        reject('Error opening offline database');
      };
      
      request.onsuccess = event => {
        db = event.target.result;
        resolve(db);
      };
      
      request.onupgradeneeded = event => {
        const database = event.target.result;
        
        // Create object stores for offline data if they don't exist
        if (!database.objectStoreNames.contains(STORES.donations)) {
          database.createObjectStore(STORES.donations, { keyPath: 'id', autoIncrement: true });
        }
        
        if (!database.objectStoreNames.contains(STORES.attendance)) {
          database.createObjectStore(STORES.attendance, { keyPath: 'id', autoIncrement: true });
        }
        
        if (!database.objectStoreNames.contains(STORES.members)) {
          database.createObjectStore(STORES.members, { keyPath: 'id', autoIncrement: true });
        }
        
        if (!database.objectStoreNames.contains(STORES.settings)) {
          database.createObjectStore(STORES.settings, { keyPath: 'key' });
        }
      };
    });
  };
  
  /**
   * Store data for offline use
   * @param {string} storeName - Name of the store to save data to
   * @param {Object} data - Data to store
   * @returns {Promise<number>} - ID of the stored item
   */
  const storeOfflineData = async (storeName, data) => {
    try {
      const database = await openDatabase();
      
      return new Promise((resolve, reject) => {
        const transaction = database.transaction(storeName, 'readwrite');
        const store = transaction.objectStore(storeName);
        
        // Add timestamp to track when the data was stored
        const item = {
          data,
          timestamp: new Date().toISOString()
        };
        
        const request = store.add(item);
        
        request.onsuccess = event => {
          resolve(event.target.result);
        };
        
        request.onerror = event => {
          console.error('Error storing offline data:', event.target.error);
          reject('Error storing data for offline use');
        };
      });
    } catch (error) {
      console.error('Failed to store offline data:', error);
      throw error;
    }
  };
  
  /**
   * Get all offline data from a store
   * @param {string} storeName - Name of the store to get data from
   * @returns {Promise<Array>} - Array of stored items
   */
  const getOfflineData = async (storeName) => {
    try {
      const database = await openDatabase();
      
      return new Promise((resolve, reject) => {
        const transaction = database.transaction(storeName, 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();
        
        request.onsuccess = event => {
          resolve(event.target.result);
        };
        
        request.onerror = event => {
          console.error('Error getting offline data:', event.target.error);
          reject('Error retrieving offline data');
        };
      });
    } catch (error) {
      console.error('Failed to get offline data:', error);
      throw error;
    }
  };
  
  /**
   * Delete an item from offline storage
   * @param {string} storeName - Name of the store
   * @param {number} id - ID of the item to delete
   * @returns {Promise<void>}
   */
  const deleteOfflineData = async (storeName, id) => {
    try {
      const database = await openDatabase();
      
      return new Promise((resolve, reject) => {
        const transaction = database.transaction(storeName, 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);
        
        request.onsuccess = () => {
          resolve();
        };
        
        request.onerror = event => {
          console.error('Error deleting offline data:', event.target.error);
          reject('Error deleting offline data');
        };
      });
    } catch (error) {
      console.error('Failed to delete offline data:', error);
      throw error;
    }
  };
  
  /**
   * Clear all data from an offline store
   * @param {string} storeName - Name of the store to clear
   * @returns {Promise<void>}
   */
  const clearOfflineStore = async (storeName) => {
    try {
      const database = await openDatabase();
      
      return new Promise((resolve, reject) => {
        const transaction = database.transaction(storeName, 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.clear();
        
        request.onsuccess = () => {
          resolve();
        };
        
        request.onerror = event => {
          console.error('Error clearing offline store:', event.target.error);
          reject('Error clearing offline data');
        };
      });
    } catch (error) {
      console.error('Failed to clear offline store:', error);
      throw error;
    }
  };
  
  /**
   * Save a donation for offline processing
   * @param {Object} donation - Donation data
   * @returns {Promise<number>} - ID of the stored donation
   */
  const saveDonationOffline = async (donation) => {
    try {
      const id = await storeOfflineData(STORES.donations, donation);
      
      toast.success('Donation saved for processing when online', {
        timeout: 3000
      });
      
      return id;
    } catch (error) {
      toast.error('Failed to save donation offline: ' + error.message, {
        timeout: 5000
      });
      throw error;
    }
  };
  
  /**
   * Save attendance record for offline processing
   * @param {Object} attendance - Attendance data
   * @returns {Promise<number>} - ID of the stored attendance record
   */
  const saveAttendanceOffline = async (attendance) => {
    try {
      const id = await storeOfflineData(STORES.attendance, attendance);
      
      toast.success('Attendance record saved for processing when online', {
        timeout: 3000
      });
      
      return id;
    } catch (error) {
      toast.error('Failed to save attendance offline: ' + error.message, {
        timeout: 5000
      });
      throw error;
    }
  };
  
  /**
   * Save member data for offline processing
   * @param {Object} member - Member data
   * @returns {Promise<number>} - ID of the stored member data
   */
  const saveMemberOffline = async (member) => {
    try {
      const id = await storeOfflineData(STORES.members, member);
      
      toast.success('Member data saved for processing when online', {
        timeout: 3000
      });
      
      return id;
    } catch (error) {
      toast.error('Failed to save member data offline: ' + error.message, {
        timeout: 5000
      });
      throw error;
    }
  };
  
  /**
   * Sync all offline data when back online
   */
  const syncOfflineData = async () => {
    if (!isOnline.value) {
      return;
    }
    
    try {
      // Trigger background sync if service worker is registered
      if (serviceWorkerRegistration.value) {
        if ('sync' in serviceWorkerRegistration.value) {
          await serviceWorkerRegistration.value.sync.register('sync-donations');
          await serviceWorkerRegistration.value.sync.register('sync-attendance');
          await serviceWorkerRegistration.value.sync.register('sync-members');
        } else {
          // Fallback if background sync is not supported
          await syncDonations();
          await syncAttendance();
          await syncMembers();
        }
      } else {
        // Fallback if service worker is not registered
        await syncDonations();
        await syncAttendance();
        await syncMembers();
      }
    } catch (error) {
      console.error('Error syncing offline data:', error);
      toast.error('Error syncing some offline data. Will try again later.', {
        timeout: 5000
      });
    }
  };
  
  /**
   * Sync offline donations when back online
   */
  const syncDonations = async () => {
    try {
      const offlineDonations = await getOfflineData(STORES.donations);
      
      if (offlineDonations.length === 0) {
        return;
      }
      
      console.log('Syncing donations:', offlineDonations);
      
      let successCount = 0;
      
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
            await deleteOfflineData(STORES.donations, donation.id);
            successCount++;
          } else {
            console.error('Failed to sync donation:', await response.text());
          }
        } catch (error) {
          console.error('Failed to sync donation:', donation, error);
        }
      }
      
      if (successCount > 0) {
        toast.success(`Successfully synced ${successCount} donation(s)`, {
          timeout: 3000
        });
      }
    } catch (error) {
      console.error('Error syncing donations:', error);
    }
  };
  
  /**
   * Sync offline attendance records when back online
   */
  const syncAttendance = async () => {
    try {
      const offlineAttendance = await getOfflineData(STORES.attendance);
      
      if (offlineAttendance.length === 0) {
        return;
      }
      
      console.log('Syncing attendance:', offlineAttendance);
      
      let successCount = 0;
      
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
            await deleteOfflineData(STORES.attendance, record.id);
            successCount++;
          } else {
            console.error('Failed to sync attendance:', await response.text());
          }
        } catch (error) {
          console.error('Failed to sync attendance:', record, error);
        }
      }
      
      if (successCount > 0) {
        toast.success(`Successfully synced ${successCount} attendance record(s)`, {
          timeout: 3000
        });
      }
    } catch (error) {
      console.error('Error syncing attendance:', error);
    }
  };
  
  /**
   * Sync offline member updates when back online
   */
  const syncMembers = async () => {
    try {
      const offlineMembers = await getOfflineData(STORES.members);
      
      if (offlineMembers.length === 0) {
        return;
      }
      
      console.log('Syncing members:', offlineMembers);
      
      let successCount = 0;
      
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
            await deleteOfflineData(STORES.members, member.id);
            successCount++;
          } else {
            console.error('Failed to sync member:', await response.text());
          }
        } catch (error) {
          console.error('Failed to sync member:', member, error);
        }
      }
      
      if (successCount > 0) {
        toast.success(`Successfully synced ${successCount} member record(s)`, {
          timeout: 3000
        });
      }
    } catch (error) {
      console.error('Error syncing members:', error);
    }
  };
  
  /**
   * Check if there is any pending offline data
   * @returns {Promise<boolean>} - True if there is pending data
   */
  const hasPendingOfflineData = async () => {
    try {
      const donations = await getOfflineData(STORES.donations);
      const attendance = await getOfflineData(STORES.attendance);
      const members = await getOfflineData(STORES.members);
      
      return donations.length > 0 || attendance.length > 0 || members.length > 0;
    } catch (error) {
      console.error('Error checking pending offline data:', error);
      return false;
    }
  };
  
  /**
   * Setup event listeners for online/offline events
   */
  const setupListeners = () => {
    if (typeof window === 'undefined') return;
    
    // Initial update
    updateOnlineStatus();
    
    // Add online/offline event listeners
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
  };
  
  /**
   * Cleanup event listeners
   */
  const cleanupListeners = () => {
    if (typeof window === 'undefined') return;
    
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
  };
  
  /**
   * Initialize offline functionality
   */
  const initialize = async () => {
    setupListeners();
    await registerServiceWorker();
    await openDatabase();
    
    // Check if we need to sync data
    if (isOnline.value) {
      const pending = await hasPendingOfflineData();
      if (pending) {
        toast.info('Syncing your offline data...', {
          timeout: 3000
        });
        await syncOfflineData();
      }
    }
  };
  
  // Setup and cleanup in component lifecycle
  if (typeof onMounted === 'function') {
    onMounted(() => {
      initialize();
    });
  }
  
  if (typeof onUnmounted === 'function') {
    onUnmounted(() => {
      cleanupListeners();
    });
  }
  
  return {
    // Network status
    isOnline,
    isOfflineMode,
    wasOffline,
    
    // Service worker
    serviceWorkerRegistered,
    serviceWorkerRegistration,
    registerServiceWorker,
    
    // Offline data management
    storeOfflineData,
    getOfflineData,
    deleteOfflineData,
    clearOfflineStore,
    
    // Specific data type handlers
    saveDonationOffline,
    saveAttendanceOffline,
    saveMemberOffline,
    
    // Sync functions
    syncOfflineData,
    syncDonations,
    syncAttendance,
    syncMembers,
    hasPendingOfflineData,
    
    // Store names for reference
    STORES
  };
}

// Create a Vue plugin
export const OfflinePlugin = {
  install(app) {
    const offline = useOffline();
    
    // Add to global properties
    app.config.globalProperties.$offline = offline;
    
    // Provide to components
    app.provide('offline', offline);
  }
};

// Export singleton instance for direct import
export default useOffline;
