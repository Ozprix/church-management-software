<template>
  <div class="keyboard-shortcuts">
    <!-- Keyboard Shortcut Trigger -->
    <div @keydown.?="toggleShortcutsModal" tabindex="-1" class="hidden">
      <!-- Hidden element to capture the ? key press globally -->
    </div>
    
    <!-- Keyboard Shortcuts Modal -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  Keyboard Shortcuts
                </h3>
                <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300">
                  <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Shortcut Categories -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Global Shortcuts -->
                <div>
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-4">Global Shortcuts</h4>
                  <ul class="space-y-3">
                    <li v-for="(shortcut, index) in globalShortcuts" :key="index" class="flex justify-between items-center">
                      <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ shortcut.description }}</span>
                      <div class="flex items-center space-x-1">
                        <template v-for="(key, keyIndex) in shortcut.keys" :key="keyIndex">
                          <kbd class="px-2 py-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded-md shadow-sm">{{ key }}</kbd>
                          <span v-if="keyIndex < shortcut.keys.length - 1" class="text-neutral-400">+</span>
                        </template>
                      </div>
                    </li>
                  </ul>
                </div>
                
                <!-- Navigation Shortcuts -->
                <div>
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-4">Navigation Shortcuts</h4>
                  <ul class="space-y-3">
                    <li v-for="(shortcut, index) in navigationShortcuts" :key="index" class="flex justify-between items-center">
                      <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ shortcut.description }}</span>
                      <div class="flex items-center space-x-1">
                        <template v-for="(key, keyIndex) in shortcut.keys" :key="keyIndex">
                          <kbd class="px-2 py-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded-md shadow-sm">{{ key }}</kbd>
                          <span v-if="keyIndex < shortcut.keys.length - 1" class="text-neutral-400">+</span>
                        </template>
                      </div>
                    </li>
                  </ul>
                </div>
                
                <!-- Page-Specific Shortcuts -->
                <div v-if="currentPageShortcuts.length > 0">
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-4">{{ currentPageTitle }} Shortcuts</h4>
                  <ul class="space-y-3">
                    <li v-for="(shortcut, index) in currentPageShortcuts" :key="index" class="flex justify-between items-center">
                      <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ shortcut.description }}</span>
                      <div class="flex items-center space-x-1">
                        <template v-for="(key, keyIndex) in shortcut.keys" :key="keyIndex">
                          <kbd class="px-2 py-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded-md shadow-sm">{{ key }}</kbd>
                          <span v-if="keyIndex < shortcut.keys.length - 1" class="text-neutral-400">+</span>
                        </template>
                      </div>
                    </li>
                  </ul>
                </div>
                
                <!-- Data Management Shortcuts -->
                <div>
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-4">Data Management Shortcuts</h4>
                  <ul class="space-y-3">
                    <li v-for="(shortcut, index) in dataShortcuts" :key="index" class="flex justify-between items-center">
                      <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ shortcut.description }}</span>
                      <div class="flex items-center space-x-1">
                        <template v-for="(key, keyIndex) in shortcut.keys" :key="keyIndex">
                          <kbd class="px-2 py-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded-md shadow-sm">{{ key }}</kbd>
                          <span v-if="keyIndex < shortcut.keys.length - 1" class="text-neutral-400">+</span>
                        </template>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
              
              <!-- Tip -->
              <div class="mt-6 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                      Press <kbd class="px-2 py-0.5 text-xs font-semibold text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded-md shadow-sm">?</kbd> at any time to show this shortcuts panel.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';

// State
const showModal = ref(false);
const route = useRoute();

// Shortcut data
const globalShortcuts = [
  { description: 'Show keyboard shortcuts', keys: ['?'] },
  { description: 'Toggle dark mode', keys: ['Ctrl', 'D'] },
  { description: 'Open help panel', keys: ['Shift', 'H'] },
  { description: 'Search', keys: ['/'] },
  { description: 'Go to dashboard', keys: ['G', 'H'] },
  { description: 'Go to settings', keys: ['G', 'S'] },
  { description: 'Save current changes', keys: ['Ctrl', 'S'] },
  { description: 'Refresh page data', keys: ['F5'] }
];

const navigationShortcuts = [
  { description: 'Go to members', keys: ['G', 'M'] },
  { description: 'Go to donations', keys: ['G', 'D'] },
  { description: 'Go to reports', keys: ['G', 'R'] },
  { description: 'Go to events', keys: ['G', 'E'] },
  { description: 'Go to notifications', keys: ['G', 'N'] },
  { description: 'Go to previous page', keys: ['Alt', '←'] },
  { description: 'Go to next page', keys: ['Alt', '→'] }
];

const dataShortcuts = [
  { description: 'Create new item', keys: ['N'] },
  { description: 'Edit selected item', keys: ['E'] },
  { description: 'Delete selected item', keys: ['Del'] },
  { description: 'Export data', keys: ['Ctrl', 'E'] },
  { description: 'Print current view', keys: ['Ctrl', 'P'] },
  { description: 'Select all items', keys: ['Ctrl', 'A'] },
  { description: 'Deselect all items', keys: ['Esc'] }
];

// Page-specific shortcuts
const pageShortcuts = {
  dashboard: [
    { description: 'Toggle edit mode', keys: ['E'] },
    { description: 'Toggle layout', keys: ['L'] },
    { description: 'Refresh all widgets', keys: ['R'] }
  ],
  members: [
    { description: 'Add new member', keys: ['N'] },
    { description: 'View member details', keys: ['V'] },
    { description: 'Filter by active members', keys: ['F', 'A'] },
    { description: 'Filter by inactive members', keys: ['F', 'I'] }
  ],
  donations: [
    { description: 'Add new donation', keys: ['N'] },
    { description: 'Batch entry mode', keys: ['B'] },
    { description: 'Generate receipt', keys: ['G', 'R'] },
    { description: 'View donation details', keys: ['V'] }
  ],
  reports: [
    { description: 'Generate report', keys: ['G'] },
    { description: 'Save report', keys: ['S'] },
    { description: 'Export to PDF', keys: ['E', 'P'] },
    { description: 'Export to Excel', keys: ['E', 'X'] }
  ],
  events: [
    { description: 'Add new event', keys: ['N'] },
    { description: 'View calendar', keys: ['C'] },
    { description: 'View list', keys: ['L'] },
    { description: 'Record attendance', keys: ['A'] }
  ],
  settings: [
    { description: 'Save settings', keys: ['S'] },
    { description: 'Reset to defaults', keys: ['R'] }
  ]
};

// Computed properties
const currentPageTitle = computed(() => {
  const path = route.path.split('/')[1] || 'dashboard';
  return path.charAt(0).toUpperCase() + path.slice(1);
});

const currentPageShortcuts = computed(() => {
  const path = route.path.split('/')[1] || 'dashboard';
  return pageShortcuts[path] || [];
});

// Methods
const toggleShortcutsModal = () => {
  showModal.value = !showModal.value;
};

// Handle keyboard events
const handleKeyDown = (event) => {
  // Show shortcuts modal when ? is pressed
  if (event.key === '?' && !event.ctrlKey && !event.altKey && !event.metaKey) {
    toggleShortcutsModal();
    event.preventDefault();
  }
  
  // Handle other global shortcuts
  if (!showModal.value) {
    // Toggle dark mode: Ctrl+D
    if (event.ctrlKey && event.key === 'd') {
      // This would be implemented by emitting an event or calling a store method
      event.preventDefault();
    }
    
    // Open help panel: Shift+H
    if (event.shiftKey && event.key === 'H') {
      // This would be implemented by emitting an event or calling a method
      event.preventDefault();
    }
    
    // Navigation shortcuts: G+X (where X is the destination)
    if (event.key === 'g') {
      // Start listening for the second key
      const handleSecondKey = (e) => {
        if (e.key === 'h') {
          // Go to dashboard
          // router.push('/');
        } else if (e.key === 'm') {
          // Go to members
          // router.push('/members');
        } else if (e.key === 'd') {
          // Go to donations
          // router.push('/donations');
        } else if (e.key === 'r') {
          // Go to reports
          // router.push('/reports');
        } else if (e.key === 'e') {
          // Go to events
          // router.push('/events');
        } else if (e.key === 's') {
          // Go to settings
          // router.push('/settings');
        } else if (e.key === 'n') {
          // Go to notifications
          // router.push('/notifications');
        }
        
        // Remove the event listener after handling the second key
        document.removeEventListener('keydown', handleSecondKey);
      };
      
      // Add event listener for the second key
      document.addEventListener('keydown', handleSecondKey, { once: true });
      event.preventDefault();
    }
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

// Expose component methods
defineExpose({
  showShortcuts: () => {
    showModal.value = true;
  }
});
</script>

<style scoped>
/* Additional styling if needed */
</style>
