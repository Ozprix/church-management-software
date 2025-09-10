<template>
  <button 
    @click="toggleDarkMode" 
    class="relative inline-flex items-center justify-center p-2 rounded-md text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none transition-colors"
    :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
  >
    <!-- Sun icon (shown in dark mode) -->
    <svg 
      xmlns="http://www.w3.org/2000/svg" 
      class="h-5 w-5 transition-opacity duration-300 ease-in-out" 
      :class="{ 'opacity-0 absolute': isDarkMode, 'opacity-100 relative': !isDarkMode }"
      fill="none" 
      viewBox="0 0 24 24" 
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    
    <!-- Moon icon (shown in light mode) -->
    <svg 
      xmlns="http://www.w3.org/2000/svg" 
      class="h-5 w-5 transition-opacity duration-300 ease-in-out" 
      :class="{ 'opacity-100 relative': isDarkMode, 'opacity-0 absolute': !isDarkMode }"
      fill="none" 
      viewBox="0 0 24 24" 
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { useSettingsStore } from '../../stores/settings';

// Use settings store
const settingsStore = useSettingsStore();

// Computed property to check if dark mode is enabled
const isDarkMode = computed(() => settingsStore.isDarkMode);

// Function to toggle dark mode
const toggleDarkMode = () => {
  settingsStore.toggleDarkMode();
};

</script>
