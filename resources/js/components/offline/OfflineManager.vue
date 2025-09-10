<template>
  <div class="offline-manager">
    <!-- Offline status indicator -->
    <div 
      class="offline-status" 
      :class="{ 'is-offline': isOfflineMode, 'has-pending': hasPending }"
    >
      <div class="status-icon">
        <i v-if="isOfflineMode" class="fas fa-wifi-slash"></i>
        <i v-else-if="hasPending" class="fas fa-sync"></i>
        <i v-else class="fas fa-wifi"></i>
      </div>
      <div class="status-text">
        <span v-if="isOfflineMode">{{ $t('common.offline') }}</span>
        <span v-else-if="hasPending">{{ $t('offline.pendingData') }}</span>
        <span v-else>{{ $t('common.online') }}</span>
      </div>
      <button 
        v-if="hasPending && isOnline" 
        class="sync-button"
        @click="syncData"
        :disabled="isSyncing"
      >
        <i class="fas fa-sync" :class="{ 'fa-spin': isSyncing }"></i>
        {{ isSyncing ? $t('common.syncing') : $t('offline.syncNow') }}
      </button>
    </div>

    <!-- Pending data details (expandable) -->
    <div v-if="hasPending && showDetails" class="pending-details">
      <h3>{{ $t('offline.pendingOfflineData') }}</h3>
      
      <div v-if="pendingDonations.length > 0" class="pending-section">
        <h4>{{ $t('donations.title') }} ({{ pendingDonations.length }})</h4>
        <ul class="pending-list">
          <li v-for="(donation, index) in pendingDonations" :key="index">
            <span class="item-title">{{ formatDonation(donation) }}</span>
            <span class="item-date">{{ formatDate(donation.timestamp) }}</span>
          </li>
        </ul>
      </div>
      
      <div v-if="pendingAttendance.length > 0" class="pending-section">
        <h4>{{ $t('attendance.title') }} ({{ pendingAttendance.length }})</h4>
        <ul class="pending-list">
          <li v-for="(record, index) in pendingAttendance" :key="index">
            <span class="item-title">{{ formatAttendance(record) }}</span>
            <span class="item-date">{{ formatDate(record.timestamp) }}</span>
          </li>
        </ul>
      </div>
      
      <div v-if="pendingMembers.length > 0" class="pending-section">
        <h4>{{ $t('offline.memberUpdates') }} ({{ pendingMembers.length }})</h4>
        <ul class="pending-list">
          <li v-for="(member, index) in pendingMembers" :key="index">
            <span class="item-title">{{ formatMember(member) }}</span>
            <span class="item-date">{{ formatDate(member.timestamp) }}</span>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Toggle details button -->
    <button 
      v-if="hasPending" 
      class="toggle-details" 
      @click="showDetails = !showDetails"
    >
      {{ showDetails ? $t('offline.hideDetails') : $t('offline.showDetails') }}
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useOffline } from '../../services/offlineService';
import { useI18n } from '../../services/i18nService';
import { format, formatDistance } from 'date-fns';

// Get services
const offline = useOffline();
const i18n = useI18n();

// Component state
const showDetails = ref(false);
const isSyncing = ref(false);
const pendingDonations = ref([]);
const pendingAttendance = ref([]);
const pendingMembers = ref([]);

// Computed properties from offline service
const isOnline = computed(() => offline.isOnline);
const isOfflineMode = computed(() => offline.isOfflineMode);

// Computed property to check if there is pending data
const hasPending = computed(() => {
  return pendingDonations.value.length > 0 || 
         pendingAttendance.value.length > 0 || 
         pendingMembers.value.length > 0;
});

// Watch for online status changes
watch(isOnline, (newValue) => {
  if (newValue) {
    // When coming back online, refresh pending data
    loadPendingData();
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
  if (!isOnline.value || isSyncing.value) return;
  
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

// Format donation for display
const formatDonation = (donation) => {
  if (!donation || !donation.data) return 'Unknown donation';
  
  const amount = donation.data.amount ? `$${donation.data.amount}` : 'Amount not specified';
  const donor = donation.data.donor_name || 'Anonymous';
  
  return `${amount} from ${donor}`;
};

// Format attendance for display
const formatAttendance = (record) => {
  if (!record || !record.data) return 'Unknown attendance record';
  
  const event = record.data.event_name || 'Unnamed event';
  const count = record.data.count || 0;
  
  return `${event}: ${count} attendees`;
};

// Format member for display
const formatMember = (member) => {
  if (!member || !member.data) return 'Unknown member update';
  
  const name = member.data.name || 'Unnamed member';
  const action = member.data.id ? 'Update' : 'New member';
  
  return `${action}: ${name}`;
};

// Format date for display
const formatDate = (timestamp) => {
  if (!timestamp) return '';
  
  const date = new Date(timestamp);
  
  // Use i18n date formatting with relative time
  return i18n.formatRelativeTime(date);
};

// Load pending data on component mount
onMounted(() => {
  loadPendingData();
});
</script>

<style scoped>
.offline-manager {
  margin-bottom: 1rem;
  font-family: var(--font-family, 'Figtree', sans-serif);
}

.offline-status {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  background-color: #f3f4f6;
  margin-bottom: 0.5rem;
}

.offline-status.is-offline {
  background-color: #fee2e2;
  border-left: 4px solid #ef4444;
}

.offline-status.has-pending {
  background-color: #fef3c7;
  border-left: 4px solid #f59e0b;
}

.status-icon {
  margin-right: 0.75rem;
  font-size: 1.25rem;
}

.offline-status.is-offline .status-icon {
  color: #ef4444;
}

.offline-status.has-pending .status-icon {
  color: #f59e0b;
}

.status-text {
  flex: 1;
  font-weight: 500;
}

.sync-button {
  background-color: #2563eb;
  color: white;
  border: none;
  border-radius: 0.25rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.sync-button:hover {
  background-color: #1d4ed8;
}

.sync-button:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}

.sync-button i {
  margin-right: 0.25rem;
}

.pending-details {
  background-color: #f9fafb;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 0.5rem;
}

.pending-details h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.125rem;
  color: #1f2937;
}

.pending-section {
  margin-bottom: 1rem;
}

.pending-section h4 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  font-size: 1rem;
  color: #4b5563;
}

.pending-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.pending-list li {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.pending-list li:last-child {
  border-bottom: none;
}

.item-title {
  font-weight: 500;
}

.item-date {
  color: #6b7280;
  font-size: 0.875rem;
}

.toggle-details {
  background: none;
  border: none;
  color: #2563eb;
  padding: 0;
  font-size: 0.875rem;
  cursor: pointer;
  text-decoration: underline;
}

.toggle-details:hover {
  color: #1d4ed8;
}

/* Dark mode support */
:global(.dark) .offline-status {
  background-color: #1f2937;
  color: #e5e7eb;
}

:global(.dark) .offline-status.is-offline {
  background-color: #7f1d1d;
  border-left-color: #ef4444;
}

:global(.dark) .offline-status.has-pending {
  background-color: #78350f;
  border-left-color: #f59e0b;
}

:global(.dark) .pending-details {
  background-color: #1f2937;
  color: #e5e7eb;
}

:global(.dark) .pending-details h3 {
  color: #f3f4f6;
}

:global(.dark) .pending-section h4 {
  color: #d1d5db;
}

:global(.dark) .pending-list li {
  border-bottom-color: #374151;
}

:global(.dark) .item-date {
  color: #9ca3af;
}

:global(.dark) .toggle-details {
  color: #3b82f6;
}

:global(.dark) .toggle-details:hover {
  color: #60a5fa;
}
</style>
