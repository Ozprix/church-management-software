<template>
  <div 
    v-if="showBar" 
    class="offline-status-bar" 
    :class="{ 'is-offline': isOfflineMode, 'has-pending': hasPending }"
  >
    <div class="status-content">
      <div class="status-icon">
        <i v-if="isOfflineMode" class="fas fa-wifi-slash"></i>
        <i v-else-if="hasPending" class="fas fa-sync"></i>
        <i v-else class="fas fa-check-circle"></i>
      </div>
      <div class="status-message">
        <span v-if="isOfflineMode">You are offline. Some features may be limited.</span>
        <span v-else-if="hasPending">
          {{ pendingCount }} item{{ pendingCount !== 1 ? 's' : '' }} waiting to sync.
          <button @click="syncData" :disabled="isSyncing" class="sync-button">
            <i class="fas fa-sync" :class="{ 'fa-spin': isSyncing }"></i>
            {{ isSyncing ? 'Syncing...' : 'Sync now' }}
          </button>
        </span>
        <span v-else-if="wasOffline">Back online! All data has been synchronized.</span>
      </div>
    </div>
    <button class="close-button" @click="dismissBar">
      <i class="fas fa-times"></i>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useOffline } from '../../services/offlineService';

// Get offline service
const offline = useOffline();

// Component state
const showBar = ref(false);
const isSyncing = ref(false);
const pendingDonations = ref([]);
const pendingAttendance = ref([]);
const pendingMembers = ref([]);
const dismissedTimestamp = ref(0);

// Computed properties from offline service
const isOfflineMode = computed(() => offline.isOfflineMode);
const wasOffline = computed(() => offline.wasOffline);

// Computed property to check if there is pending data
const hasPending = computed(() => {
  return pendingDonations.value.length > 0 || 
         pendingAttendance.value.length > 0 || 
         pendingMembers.value.length > 0;
});

// Computed property for total pending count
const pendingCount = computed(() => {
  return pendingDonations.value.length + 
         pendingAttendance.value.length + 
         pendingMembers.value.length;
});

// Watch for offline status changes
watch(isOfflineMode, (newValue) => {
  if (newValue) {
    // When going offline, show the bar
    showBar.value = true;
  } else {
    // When coming back online, refresh pending data
    loadPendingData();
    
    // Show the bar when back online
    showBar.value = true;
  }
});

// Watch for pending data changes
watch(hasPending, (newValue) => {
  // Only show the bar if there is pending data and it hasn't been dismissed recently
  if (newValue && Date.now() - dismissedTimestamp.value > 60000) {
    showBar.value = true;
  } else if (!newValue && !isOfflineMode.value && !wasOffline.value) {
    // Hide the bar if there's no pending data and we're not offline
    // Keep a short delay to show the "Back online" message
    setTimeout(() => {
      showBar.value = false;
    }, 5000);
  }
});

// Watch for wasOffline changes
watch(wasOffline, (newValue) => {
  if (newValue) {
    // When back online after being offline, show the bar
    showBar.value = true;
    
    // Hide the bar after 5 seconds if there's no pending data
    if (!hasPending.value) {
      setTimeout(() => {
        showBar.value = false;
        // Reset wasOffline after hiding the bar
        offline.wasOffline = false;
      }, 5000);
    }
  }
});

// Load pending offline data
const loadPendingData = async () => {
  try {
    pendingDonations.value = await offline.getOfflineData(offline.STORES.donations);
    pendingAttendance.value = await offline.getOfflineData(offline.STORES.attendance);
    pendingMembers.value = await offline.getOfflineData(offline.STORES.members);
  } catch (error) {
    console.error('Error loading pending offline data:', error);
  }
};

// Sync all pending data
const syncData = async () => {
  if (!offline.isOnline || isSyncing.value) return;
  
  isSyncing.value = true;
  
  try {
    await offline.syncOfflineData();
    await loadPendingData();
  } catch (error) {
    console.error('Error syncing offline data:', error);
  } finally {
    isSyncing.value = false;
  }
};

// Dismiss the bar
const dismissBar = () => {
  showBar.value = false;
  dismissedTimestamp.value = Date.now();
};

// Load pending data on component mount
onMounted(() => {
  loadPendingData();
  
  // Show the bar if offline or has pending data
  if (isOfflineMode.value || hasPending.value) {
    showBar.value = true;
  }
});
</script>

<style scoped>
.offline-status-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  z-index: 1000;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background-color 0.3s, transform 0.3s;
  transform: translateY(0);
  box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
}

.offline-status-bar.is-offline {
  background-color: #ef4444;
}

.offline-status-bar.has-pending {
  background-color: #f59e0b;
}

.status-content {
  display: flex;
  align-items: center;
}

.status-icon {
  margin-right: 0.75rem;
  font-size: 1.25rem;
}

.status-message {
  font-size: 0.875rem;
  font-weight: 500;
}

.sync-button {
  background: none;
  border: 1px solid white;
  color: white;
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
  margin-left: 0.5rem;
  font-size: 0.75rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.sync-button:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.sync-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.sync-button i {
  margin-right: 0.25rem;
}

.close-button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.close-button:hover {
  opacity: 1;
}

@media (max-width: 640px) {
  .offline-status-bar {
    padding: 0.5rem;
  }
  
  .status-message {
    font-size: 0.75rem;
  }
}
</style>
