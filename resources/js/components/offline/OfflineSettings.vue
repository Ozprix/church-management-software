<template>
  <div class="offline-settings">
    <h2 class="settings-title">{{ $t('offline.offlineSettings') }}</h2>
    
    <!-- Offline Mode Toggle -->
    <div class="settings-section">
      <OfflineModeToggle />
    </div>
    
    <!-- Data Synchronization -->
    <div class="settings-section">
      <h3 class="section-title">{{ $t('offline.dataSynchronization') }}</h3>
      
      <div class="sync-options">
        <!-- Auto Sync Toggle -->
        <div class="option-group">
          <label class="toggle-label">
            <span class="option-text">{{ $t('offline.autoSync') }}</span>
            <div 
              class="toggle-switch" 
              :class="{ 'is-active': autoSync }"
              @click="toggleAutoSync"
            >
              <div class="toggle-slider"></div>
            </div>
          </label>
          <p class="option-description">{{ $t('offline.autoSyncDescription') }}</p>
        </div>
        
        <!-- Sync Frequency -->
        <div class="option-group" v-if="autoSync">
          <label class="option-label">{{ $t('offline.syncFrequency') }}</label>
          <select v-model="syncFrequency" class="option-select" @change="saveSyncFrequency">
            <option value="5">{{ $t('offline.every', { time: '5 ' + $t('offline.minutes') }) }}</option>
            <option value="15">{{ $t('offline.every', { time: '15 ' + $t('offline.minutes') }) }}</option>
            <option value="30">{{ $t('offline.every', { time: '30 ' + $t('offline.minutes') }) }}</option>
            <option value="60">{{ $t('offline.every', { time: '1 ' + $t('offline.hour') }) }}</option>
            <option value="360">{{ $t('offline.every', { time: '6 ' + $t('offline.hours') }) }}</option>
            <option value="720">{{ $t('offline.every', { time: '12 ' + $t('offline.hours') }) }}</option>
            <option value="1440">{{ $t('offline.every', { time: '24 ' + $t('offline.hours') }) }}</option>
          </select>
        </div>
        
        <!-- Manual Sync Button -->
        <div class="option-group">
          <button 
            class="sync-button" 
            @click="syncAllData" 
            :disabled="isSyncing || !isOnline"
          >
            <i class="fas fa-sync" :class="{ 'fa-spin': isSyncing }"></i>
            {{ isSyncing ? $t('common.syncing') : $t('offline.syncAllData') }}
          </button>
          <p class="option-description" v-if="lastSynced">
            {{ $t('offline.dataLastSynced', { time: formatLastSynced }) }}
          </p>
        </div>
      </div>
    </div>
    
    <!-- Data Storage -->
    <div class="settings-section">
      <h3 class="section-title">{{ $t('offline.dataStorage') }}</h3>
      
      <div class="storage-info">
        <div class="storage-item">
          <div class="storage-label">{{ $t('offline.totalStorage') }}</div>
          <div class="storage-value">{{ formatStorageSize(totalStorage) }}</div>
        </div>
        
        <div class="storage-item">
          <div class="storage-label">{{ $t('offline.availableStorage') }}</div>
          <div class="storage-value">{{ formatStorageSize(availableStorage) }}</div>
        </div>
        
        <div class="storage-progress">
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: storagePercentage + '%' }"
              :class="{ 'warning': storagePercentage > 70, 'danger': storagePercentage > 90 }"
            ></div>
          </div>
          <div class="progress-text">{{ storagePercentage }}% {{ $t('offline.used') }}</div>
        </div>
      </div>
      
      <div class="storage-actions">
        <button class="clear-button" @click="confirmClearOfflineData">
          <i class="fas fa-trash-alt"></i>
          {{ $t('offline.clearOfflineData') }}
        </button>
      </div>
    </div>
    
    <!-- Data Types -->
    <div class="settings-section">
      <h3 class="section-title">{{ $t('offline.dataTypes') }}</h3>
      <p class="section-description">{{ $t('offline.dataTypesDescription') }}</p>
      
      <div class="data-types">
        <div 
          v-for="(type, key) in dataTypes" 
          :key="key"
          class="data-type-item"
        >
          <label class="checkbox-label">
            <input 
              type="checkbox" 
              :checked="type.enabled"
              @change="toggleDataType(key)"
            >
            <span class="checkbox-text">{{ $t(`${key}.title`) }}</span>
          </label>
          <span class="data-count" v-if="type.count">
            {{ type.count }} {{ $t('common.items') }}
            ({{ formatStorageSize(type.size) }})
          </span>
        </div>
      </div>
    </div>
    
    <!-- Clear Data Confirmation Modal -->
    <div v-if="showClearConfirmation" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3>{{ $t('offline.confirmClearTitle') }}</h3>
          <button class="close-button" @click="showClearConfirmation = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>{{ $t('offline.confirmClearMessage') }}</p>
        </div>
        <div class="modal-footer">
          <button class="cancel-button" @click="showClearConfirmation = false">
            {{ $t('common.cancel') }}
          </button>
          <button class="confirm-button" @click="clearOfflineData">
            {{ $t('offline.clearData') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useOffline } from '../../services/offlineService';
import { useI18n } from '../../services/i18nService';
import OfflineModeToggle from './OfflineModeToggle.vue';

// Get services
const offline = useOffline();
const i18n = useI18n();

// Component state
const autoSync = ref(localStorage.getItem('autoSync') === 'true');
const syncFrequency = ref(localStorage.getItem('syncFrequency') || '15');
const isSyncing = ref(false);
const lastSynced = ref(null);
const totalStorage = ref(0);
const availableStorage = ref(0);
const dataTypes = ref({
  members: { enabled: true, count: 0, size: 0 },
  donations: { enabled: true, count: 0, size: 0 },
  events: { enabled: true, count: 0, size: 0 },
  attendance: { enabled: true, count: 0, size: 0 }
});
const showClearConfirmation = ref(false);

// Computed properties
const isOnline = computed(() => offline.isOnline.value);
const storagePercentage = computed(() => {
  if (totalStorage.value === 0) return 0;
  return Math.round((totalStorage.value - availableStorage.value) / totalStorage.value * 100);
});
const formatLastSynced = computed(() => {
  if (!lastSynced.value) return '';
  return i18n.formatDateTime(lastSynced.value, 'PPp');
});

// Toggle auto sync
const toggleAutoSync = () => {
  autoSync.value = !autoSync.value;
  localStorage.setItem('autoSync', autoSync.value);
  
  // Update offline service
  offline.setAutoSync(autoSync.value);
};

// Save sync frequency
const saveSyncFrequency = () => {
  localStorage.setItem('syncFrequency', syncFrequency.value);
  
  // Update offline service
  offline.setSyncFrequency(parseInt(syncFrequency.value));
};

// Toggle data type
const toggleDataType = (key) => {
  dataTypes.value[key].enabled = !dataTypes.value[key].enabled;
  
  // Update offline service
  offline.setDataTypeEnabled(key, dataTypes.value[key].enabled);
};

// Sync all data
const syncAllData = async () => {
  if (isSyncing.value || !isOnline.value) return;
  
  isSyncing.value = true;
  
  try {
    await offline.syncAllData();
    lastSynced.value = new Date();
    localStorage.setItem('lastSynced', lastSynced.value.toISOString());
    
    // Update storage info and data counts
    await loadStorageInfo();
    await loadDataCounts();
  } catch (error) {
    console.error('Error syncing data:', error);
  } finally {
    isSyncing.value = false;
  }
};

// Confirm clear offline data
const confirmClearOfflineData = () => {
  showClearConfirmation.value = true;
};

// Clear offline data
const clearOfflineData = async () => {
  try {
    await offline.clearAllOfflineData();
    showClearConfirmation.value = false;
    
    // Update storage info and data counts
    await loadStorageInfo();
    await loadDataCounts();
  } catch (error) {
    console.error('Error clearing offline data:', error);
  }
};

// Format storage size
const formatStorageSize = (bytes) => {
  if (bytes === 0) return '0 B';
  
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Load storage info
const loadStorageInfo = async () => {
  try {
    const storageInfo = await offline.getStorageInfo();
    totalStorage.value = storageInfo.quota || 0;
    availableStorage.value = storageInfo.available || 0;
  } catch (error) {
    console.error('Error loading storage info:', error);
  }
};

// Load data counts
const loadDataCounts = async () => {
  try {
    for (const key in dataTypes.value) {
      const data = await offline.getOfflineData(key);
      dataTypes.value[key].count = data ? data.length : 0;
      dataTypes.value[key].size = await offline.getDataTypeSize(key);
      dataTypes.value[key].enabled = await offline.isDataTypeEnabled(key);
    }
  } catch (error) {
    console.error('Error loading data counts:', error);
  }
};

// Load initial data
onMounted(async () => {
  // Load last synced time
  const lastSyncedTime = localStorage.getItem('lastSynced');
  if (lastSyncedTime) {
    lastSynced.value = new Date(lastSyncedTime);
  }
  
  // Load storage info
  await loadStorageInfo();
  
  // Load data counts
  await loadDataCounts();
});
</script>

<style scoped>
.offline-settings {
  padding: 1.5rem;
  max-width: 800px;
  margin: 0 auto;
}

.settings-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.settings-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 500;
  margin-top: 0;
  margin-bottom: 1rem;
  color: #1f2937;
}

.section-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.sync-options {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.option-group {
  margin-bottom: 1rem;
}

.toggle-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.option-text {
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

.option-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 0.5rem;
  margin-bottom: 0;
}

.option-label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.option-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #1f2937;
  background-color: white;
}

.sync-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.sync-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.sync-button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.sync-button i {
  margin-right: 0.5rem;
}

.storage-info {
  margin-bottom: 1.5rem;
}

.storage-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.storage-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.storage-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
}

.storage-progress {
  margin-top: 1rem;
}

.progress-bar {
  height: 0.5rem;
  background-color: #e5e7eb;
  border-radius: 0.25rem;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #3b82f6;
  transition: width 0.3s ease;
}

.progress-fill.warning {
  background-color: #f59e0b;
}

.progress-fill.danger {
  background-color: #ef4444;
}

.progress-text {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
  text-align: right;
}

.storage-actions {
  margin-top: 1rem;
}

.clear-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: #f3f4f6;
  color: #ef4444;
  border: 1px solid #ef4444;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.clear-button:hover {
  background-color: #fee2e2;
}

.clear-button i {
  margin-right: 0.5rem;
}

.data-types {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.data-type-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.checkbox-label input {
  margin-right: 0.5rem;
}

.checkbox-text {
  font-weight: 500;
  color: #374151;
}

.data-count {
  font-size: 0.75rem;
  color: #6b7280;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-container {
  width: 100%;
  max-width: 28rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.close-button {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  font-size: 1.25rem;
}

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  margin: 0;
  color: #4b5563;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  gap: 0.75rem;
}

.cancel-button {
  padding: 0.5rem 1rem;
  background-color: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
}

.confirm-button {
  padding: 0.5rem 1rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
}

/* Dark mode support */
:global(.dark) .settings-title {
  color: #f3f4f6;
}

:global(.dark) .settings-section {
  background-color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

:global(.dark) .section-title {
  color: #f3f4f6;
}

:global(.dark) .section-description {
  color: #9ca3af;
}

:global(.dark) .option-text {
  color: #e5e7eb;
}

:global(.dark) .toggle-switch {
  background-color: #4b5563;
}

:global(.dark) .option-description {
  color: #9ca3af;
}

:global(.dark) .option-label {
  color: #e5e7eb;
}

:global(.dark) .option-select {
  border-color: #4b5563;
  color: #f3f4f6;
  background-color: #374151;
}

:global(.dark) .storage-label {
  color: #9ca3af;
}

:global(.dark) .storage-value {
  color: #f3f4f6;
}

:global(.dark) .progress-bar {
  background-color: #4b5563;
}

:global(.dark) .progress-text {
  color: #9ca3af;
}

:global(.dark) .clear-button {
  background-color: #374151;
  color: #f87171;
  border-color: #f87171;
}

:global(.dark) .clear-button:hover {
  background-color: #4b5563;
}

:global(.dark) .data-type-item {
  background-color: #374151;
}

:global(.dark) .checkbox-text {
  color: #e5e7eb;
}

:global(.dark) .data-count {
  color: #9ca3af;
}

:global(.dark) .modal-container {
  background-color: #1f2937;
}

:global(.dark) .modal-header {
  border-bottom-color: #4b5563;
}

:global(.dark) .modal-header h3 {
  color: #f3f4f6;
}

:global(.dark) .close-button {
  color: #9ca3af;
}

:global(.dark) .modal-body p {
  color: #d1d5db;
}

:global(.dark) .modal-footer {
  border-top-color: #4b5563;
}

:global(.dark) .cancel-button {
  background-color: #374151;
  color: #d1d5db;
  border-color: #4b5563;
}

:global(.dark) .confirm-button {
  background-color: #ef4444;
}
</style>
