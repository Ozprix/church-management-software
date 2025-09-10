<template>
  <div class="offline-mode-toggle">
    <div class="toggle-container">
      <label class="toggle-label">
        <span class="toggle-text">{{ $t('offline.offlineMode') }}</span>
        <div 
          class="toggle-switch" 
          :class="{ 'is-active': isOfflineMode }"
          @click="toggleOfflineMode"
        >
          <div class="toggle-slider"></div>
        </div>
      </label>
    </div>
    
    <div v-if="showInfo" class="toggle-info">
      <div class="info-icon" @click="toggleInfoPanel">
        <i class="fas fa-info-circle"></i>
      </div>
      
      <div v-if="showInfoPanel" class="info-panel">
        <h4>{{ $t('offline.offlineMode') }}</h4>
        <p>{{ $t('offline.offlineModeDescription') }}</p>
        
        <div class="data-status">
          <h5>{{ $t('offline.dataAvailableOffline') }}:</h5>
          <ul class="data-list">
            <li v-for="(status, type) in offlineDataStatus" :key="type" class="data-item">
              <span class="data-type">{{ $t(`${type}.title`) }}</span>
              <span class="data-indicator" :class="{ 'is-available': status.available }">
                <i :class="status.available ? 'fas fa-check' : 'fas fa-times'"></i>
                {{ status.available ? $t('common.available') : $t('common.unavailable') }}
              </span>
              <span class="data-count" v-if="status.available && status.count">
                ({{ status.count }} {{ $t('common.items') }})
              </span>
            </li>
          </ul>
        </div>
        
        <div class="last-synced" v-if="lastSynced">
          {{ $t('offline.dataLastSynced', { time: formatLastSynced }) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useOffline } from '../../services/offlineService';
import { useI18n } from '../../services/i18nService';

// Get services
const offline = useOffline();
const i18n = useI18n();

// Component state
const showInfo = ref(true);
const showInfoPanel = ref(false);
const offlineDataStatus = ref({});
const lastSynced = ref(null);

// Computed properties
const isOfflineMode = computed(() => offline.isOfflineMode.value);
const formatLastSynced = computed(() => {
  if (!lastSynced.value) return '';
  return i18n.formatDateTime(lastSynced.value, 'PPp');
});

// Toggle offline mode
const toggleOfflineMode = async () => {
  try {
    if (isOfflineMode.value) {
      await offline.disableOfflineMode();
    } else {
      await offline.enableOfflineMode();
    }
  } catch (error) {
    console.error('Error toggling offline mode:', error);
  }
};

// Toggle info panel
const toggleInfoPanel = () => {
  showInfoPanel.value = !showInfoPanel.value;
  
  if (showInfoPanel.value) {
    loadOfflineDataStatus();
  }
};

// Load offline data status
const loadOfflineDataStatus = async () => {
  try {
    // Get status for each data type
    const members = await offline.getOfflineDataStatus('members');
    const donations = await offline.getOfflineDataStatus('donations');
    const events = await offline.getOfflineDataStatus('events');
    const attendance = await offline.getOfflineDataStatus('attendance');
    
    // Update status
    offlineDataStatus.value = {
      members,
      donations,
      events,
      attendance
    };
    
    // Get last synced time
    lastSynced.value = await offline.getLastSyncedTime();
  } catch (error) {
    console.error('Error loading offline data status:', error);
  }
};

// Load initial data
onMounted(() => {
  loadOfflineDataStatus();
});
</script>

<style scoped>
.offline-mode-toggle {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.toggle-container {
  display: flex;
  align-items: center;
}

.toggle-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.toggle-text {
  margin-right: 0.75rem;
  font-weight: 500;
  color: #374151;
}

.toggle-switch {
  position: relative;
  width: 3rem;
  height: 1.5rem;
  background-color: #e5e7eb;
  border-radius: 9999px;
  transition: background-color 0.2s;
}

.toggle-switch.is-active {
  background-color: #3b82f6;
}

.toggle-slider {
  position: absolute;
  top: 0.25rem;
  left: 0.25rem;
  width: 1rem;
  height: 1rem;
  background-color: white;
  border-radius: 9999px;
  transition: transform 0.2s;
}

.toggle-switch.is-active .toggle-slider {
  transform: translateX(1.5rem);
}

.toggle-info {
  position: relative;
  margin-left: 1rem;
}

.info-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.2s;
}

.info-icon:hover {
  color: #3b82f6;
}

.info-panel {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  width: 20rem;
  padding: 1rem;
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  z-index: 10;
}

.info-panel h4 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.info-panel p {
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.data-status {
  margin-bottom: 1rem;
}

.data-status h5 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
}

.data-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.data-item {
  display: flex;
  align-items: center;
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.data-type {
  flex: 1;
  color: #4b5563;
}

.data-indicator {
  display: flex;
  align-items: center;
  margin-left: 0.5rem;
  color: #ef4444;
}

.data-indicator.is-available {
  color: #10b981;
}

.data-indicator i {
  margin-right: 0.25rem;
}

.data-count {
  margin-left: 0.5rem;
  color: #6b7280;
  font-size: 0.75rem;
}

.last-synced {
  font-size: 0.75rem;
  color: #6b7280;
  text-align: right;
}

/* Dark mode support */
:global(.dark) .toggle-text {
  color: #e5e7eb;
}

:global(.dark) .toggle-switch {
  background-color: #4b5563;
}

:global(.dark) .info-icon {
  color: #9ca3af;
}

:global(.dark) .info-icon:hover {
  color: #60a5fa;
}

:global(.dark) .info-panel {
  background-color: #1f2937;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}

:global(.dark) .info-panel h4 {
  color: #f3f4f6;
}

:global(.dark) .info-panel p {
  color: #d1d5db;
}

:global(.dark) .data-status h5 {
  color: #e5e7eb;
}

:global(.dark) .data-type {
  color: #d1d5db;
}

:global(.dark) .data-count {
  color: #9ca3af;
}

:global(.dark) .last-synced {
  color: #9ca3af;
}
</style>
