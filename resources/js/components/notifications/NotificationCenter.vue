<template>
  <div class="notification-center">
    <ErrorBoundary @error="handleError">
    <div class="relative">
      <!-- Notification Bell Icon -->
      <button 
        @click="toggleNotificationPanel" 
        class="p-2 rounded-full text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-300 relative"
        aria-label="Notifications"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Unread Badge -->
        <span 
          v-if="unreadCount > 0" 
          class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-red-500 text-white text-xs font-bold rounded-full h-5 min-w-[1.25rem] flex items-center justify-center px-1"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>
      
      <!-- Notification Panel -->
      <div 
        v-if="isOpen" 
        class="absolute right-0 mt-2 w-80 md:w-96 bg-white dark:bg-neutral-800 rounded-lg shadow-lg overflow-hidden z-50 border border-neutral-200 dark:border-neutral-700 transition-colors duration-300"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center transition-colors duration-300">
          <h3 class="text-lg font-medium text-neutral-800 dark:text-white transition-colors duration-300">Notifications</h3>
          <div class="flex space-x-2">
            <button 
              v-if="unreadCount > 0"
              @click="markAllAsRead" 
              class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-300"
            >
              Mark all as read
            </button>
            <button 
              @click="openSettings" 
              class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors duration-300"
              title="Notification Settings"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Tabs -->
        <div class="border-b border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
          <div class="flex">
            <button 
              @click="activeTab = 'all'" 
              class="flex-1 py-2 px-4 text-center text-sm font-medium transition-colors duration-300"
              :class="activeTab === 'all' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-500' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
            >
              All
            </button>
            <button 
              @click="activeTab = 'unread'" 
              class="flex-1 py-2 px-4 text-center text-sm font-medium transition-colors duration-300"
              :class="activeTab === 'unread' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-500' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
            >
              Unread
              <span v-if="unreadCount > 0" class="ml-1 bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 text-xs rounded-full px-2 py-0.5 transition-colors duration-300">
                {{ unreadCount }}
              </span>
            </button>
          </div>
        </div>
        
        <!-- Notification List -->
        <div class="max-h-[60vh] overflow-y-auto">
          <!-- Loading state -->
          <div v-if="isLoading" class="py-12 text-center">
            <LoadingSpinner size="lg" message="Loading notifications..." />
          </div>
          
          <div v-else-if="filteredNotifications.length === 0" class="py-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-neutral-400 dark:text-neutral-600 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="mt-2 text-neutral-500 dark:text-neutral-400 transition-colors duration-300">
              {{ activeTab === 'unread' ? 'No unread notifications' : 'No notifications' }}
            </p>
          </div>
          
          <div v-else>
            <div 
              v-for="notification in filteredNotifications" 
              :key="notification.id"
              class="border-b border-neutral-200 dark:border-neutral-700 last:border-b-0 transition-colors duration-300"
              :class="{ 'bg-primary-50 dark:bg-primary-900/20': !notification.read }"
            >
              <div class="relative p-4">
                <!-- Category Indicator -->
                <div class="absolute left-4 top-4 w-2 h-2 rounded-full" :class="getCategoryColor(notification.category)"></div>
                
                <!-- Content -->
                <div class="pl-4">
                  <div class="flex justify-between items-start">
                    <h4 class="text-sm font-medium text-neutral-800 dark:text-white transition-colors duration-300">
                      {{ notification.title }}
                    </h4>
                    <div class="flex items-center space-x-2 ml-2">
                      <span class="text-xs text-neutral-500 dark:text-neutral-400 whitespace-nowrap transition-colors duration-300">
                        {{ formatTimeAgo(notification.timestamp) }}
                      </span>
                      <button 
                        @click="removeNotification(notification.id)" 
                        class="text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300 transition-colors duration-300"
                        title="Remove"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300 transition-colors duration-300">
                    {{ notification.message }}
                  </p>
                  
                  <div class="mt-2 flex justify-between items-center">
                    <div>
                      <span 
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium transition-colors duration-300"
                        :class="getCategoryBadgeClass(notification.category)"
                      >
                        {{ getCategoryName(notification.category) }}
                      </span>
                    </div>
                    
                    <div class="flex space-x-2">
                      <button 
                        v-if="!notification.read"
                        @click="markAsRead(notification.id)" 
                        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-300"
                      >
                        Mark as read
                      </button>
                      <button 
                        v-if="notification.link"
                        @click="navigateTo(notification)" 
                        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-300"
                      >
                        View
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 text-center transition-colors duration-300">
          <router-link 
            to="/notifications" 
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium transition-colors duration-300"
            @click="isOpen = false"
          >
            View all notifications
          </router-link>
        </div>
      </div>
    </div>
    
    <!-- Settings Modal -->
    <Modal 
      v-if="showSettings" 
      title="Notification Settings" 
      @close="showSettings = false"
    >
      <div class="space-y-6">
        <!-- Notification Methods -->
        <div>
          <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-3 transition-colors duration-300">Notification Methods</h3>
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label for="email-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Email Notifications
              </label>
              <Toggle 
                id="email-notifications" 
                v-model="settings.email" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="browser-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Browser Notifications
              </label>
              <Toggle 
                id="browser-notifications" 
                v-model="settings.browser" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="desktop-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Desktop Notifications
              </label>
              <Toggle 
                id="desktop-notifications" 
                v-model="settings.desktop" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="mobile-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Mobile Push Notifications
              </label>
              <Toggle 
                id="mobile-notifications" 
                v-model="settings.mobile" 
                @update:modelValue="updateSettings"
              />
            </div>
          </div>
        </div>
        
        <!-- Notification Categories -->
        <div>
          <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-3 transition-colors duration-300">Notification Categories</h3>
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label for="members-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Member Updates
              </label>
              <Toggle 
                id="members-notifications" 
                v-model="settings.categories.members" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="events-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Event Reminders
              </label>
              <Toggle 
                id="events-notifications" 
                v-model="settings.categories.events" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="groups-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Group Activities
              </label>
              <Toggle 
                id="groups-notifications" 
                v-model="settings.categories.groups" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="donations-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Donation Updates
              </label>
              <Toggle 
                id="donations-notifications" 
                v-model="settings.categories.donations" 
                @update:modelValue="updateSettings"
              />
            </div>
            <div class="flex items-center justify-between">
              <label for="system-notifications" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                System Notifications
              </label>
              <Toggle 
                id="system-notifications" 
                v-model="settings.categories.system" 
                @update:modelValue="updateSettings"
              />
            </div>
          </div>
        </div>
        
        <!-- Do Not Disturb -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-white transition-colors duration-300">Do Not Disturb</h3>
            <Toggle 
              id="do-not-disturb" 
              v-model="settings.doNotDisturb.enabled" 
              @update:modelValue="updateSettings"
            />
          </div>
          
          <div v-if="settings.doNotDisturb.enabled" class="grid grid-cols-2 gap-4">
            <div>
              <label for="dnd-start" class="block text-sm text-neutral-700 dark:text-neutral-300 mb-1 transition-colors duration-300">
                Start Time
              </label>
              <input 
                id="dnd-start" 
                v-model="settings.doNotDisturb.startTime" 
                type="time" 
                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-300"
                @change="updateSettings"
              >
            </div>
            <div>
              <label for="dnd-end" class="block text-sm text-neutral-700 dark:text-neutral-300 mb-1 transition-colors duration-300">
                End Time
              </label>
              <input 
                id="dnd-end" 
                v-model="settings.doNotDisturb.endTime" 
                type="time" 
                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-300"
                @change="updateSettings"
              >
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <Button @click="showSettings = false" variant="outline">Cancel</Button>
          <Button @click="saveSettings" variant="primary">Save Settings</Button>
        </div>
      </template>
    </Modal>
    </ErrorBoundary>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../../stores/notifications';
import { useNotificationService } from '../../services/notificationService';
import { useToastService } from '../../services/toastService';
import Modal from '../ui/Modal.vue';
import Button from '../ui/Button.vue';
import Toggle from '../ui/Toggle.vue';
import ErrorBoundary from '../error/ErrorBoundary.vue';
import LoadingSpinner from '../ui/LoadingSpinner.vue';

const router = useRouter();
const notificationStore = useNotificationStore();
const notificationService = useNotificationService();
const toast = useToastService();

// Loading state
const isLoading = ref(true);
const showSettings = ref(false);
const settings = ref({ ...notificationStore.settings });

// Computed properties
const unreadCount = computed(() => notificationStore.unreadCount);

const filteredNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notificationStore.unreadNotifications;
  }
  return notificationStore.notifications;
});

// Methods
function toggleNotificationPanel() {
  isOpen.value = !isOpen.value;
}

function markAsRead(id) {
  notificationService.markAsRead(id);
}

function markAllAsRead() {
  notificationService.markAllAsRead();
}

function removeNotification(id) {
  notificationService.remove(id);
}

function navigateTo(notification) {
  if (notification.link) {
    isOpen.value = false;
    markAsRead(notification.id);
    router.push(notification.link);
  }
}

function openSettings() {
  showSettings.value = true;
  isOpen.value = false;
}

function updateSettings() {
  // This is called whenever a setting is changed
  // We don't save immediately to allow for multiple changes
}

async function saveSettings() {
  try {
    // Show loading state
    const savingToast = toast.show({
      type: 'info',
      title: 'Saving',
      message: 'Updating your notification preferences...',
      duration: 0 // Don't auto-close
    });
    
    // Simulate API delay (remove in production)
    await new Promise(resolve => setTimeout(resolve, 800));
    
    // Update settings
    notificationStore.updateSettings(settings);
    
    // Close modal and show success message
    showSettings.value = false;
    toast.remove(savingToast);
    toast.success('Notification settings updated successfully');
  } catch (error) {
    console.error('Error saving notification settings:', error);
    toast.error('Failed to save notification settings. Please try again.');
  }
}

function formatTimeAgo(timestamp) {
  const now = new Date();
  const date = new Date(timestamp);
  const seconds = Math.floor((now - date) / 1000);
  
  let interval = Math.floor(seconds / 31536000);
  if (interval >= 1) {
    return interval === 1 ? '1 year ago' : `${interval} years ago`;
  }
  
  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) {
    return interval === 1 ? '1 month ago' : `${interval} months ago`;
  }
  
  interval = Math.floor(seconds / 86400);
  if (interval >= 1) {
    return interval === 1 ? '1 day ago' : `${interval} days ago`;
  }
  
  interval = Math.floor(seconds / 3600);
  if (interval >= 1) {
    return interval === 1 ? '1 hour ago' : `${interval} hours ago`;
  }
  
  interval = Math.floor(seconds / 60);
  if (interval >= 1) {
    return interval === 1 ? '1 minute ago' : `${interval} minutes ago`;
  }
  
  return 'Just now';
}

function getCategoryColor(category) {
  const colors = {
    members: 'bg-blue-500',
    events: 'bg-purple-500',
    groups: 'bg-green-500',
    donations: 'bg-yellow-500',
    system: 'bg-neutral-500'
  };
  
  return colors[category] || 'bg-primary-500';
}

function getCategoryBadgeClass(category) {
  const classes = {
    members: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    events: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    groups: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    donations: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    system: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-300'
  };
  
  return classes[category] || 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300';
}

function getCategoryName(category) {
  const names = {
    members: 'Member',
    events: 'Event',
    groups: 'Group',
    donations: 'Donation',
    system: 'System'
  };
  
  return names[category] || 'Notification';
}

// Click outside handler to close the notification panel
function handleClickOutside(event) {
  const notificationCenter = document.querySelector('.notification-center');
  if (isOpen.value && notificationCenter && !notificationCenter.contains(event.target)) {
    isOpen.value = false;
  }
}

// Handle errors from ErrorBoundary
const handleError = ({ error }) => {
  console.error('Error in NotificationCenter component:', error);
  toast.error('An error occurred while managing notifications.');
};

// Lifecycle hooks
onMounted(async () => {
  document.addEventListener('click', handleClickOutside);
  
  try {
    // Initialize notification service
    await notificationService.init();
  } catch (error) {
    console.error('Error initializing notifications:', error);
    toast.error('Failed to load notifications. Please refresh the page.');
  } finally {
    // Hide loading state after a slight delay for better UX
    setTimeout(() => {
      isLoading.value = false;
    }, 500);
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Watch for changes to notification settings
watch(() => notificationStore.settings, (newSettings) => {
  settings.value = { ...newSettings };
}, { deep: true });
</script>

<style scoped>
.notification-center {
  position: relative;
}
</style>
