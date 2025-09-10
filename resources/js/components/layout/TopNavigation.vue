<template>
  <nav class="bg-white dark:bg-neutral-800 shadow-sm transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Mobile menu button -->
          <div class="flex items-center md:hidden">
            <button @click="$emit('toggle-sidebar')" class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 dark:text-neutral-500 hover:text-neutral-500 dark:hover:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-colors duration-300">
              <span class="sr-only">Open main menu</span>
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
          
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center">
            <span class="text-xl font-bold text-primary-600 dark:text-primary-400 md:hidden transition-colors duration-300">CMS</span>
          </div>
          
          <!-- Navigation Links (Desktop) -->
          <div class="hidden md:ml-6 md:flex md:space-x-8">
            <router-link to="/" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-600 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-300">Dashboard</router-link>
            <router-link to="/members" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-600 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-300">Members</router-link>
            <router-link to="/finance" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-600 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-300">Finance</router-link>
            <router-link to="/events" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-600 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-300">Events</router-link>
            <router-link to="/theme-customizer" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-600 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
              Themes
            </router-link>
          </div>
        </div>
        
        <!-- Right side menu -->
        <div class="flex items-center">
          <!-- Actions slot for ThemeSwitch -->
          <slot name="actions"></slot>
          
          <!-- Notifications -->
          <div class="ml-3 relative">
            <NotificationCenter />
          </div>
          
          <!-- Search -->
          <div class="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-end">
            <div class="max-w-lg w-full lg:max-w-xs">
              <label for="search" class="sr-only">Search</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-neutral-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md leading-5 bg-white dark:bg-neutral-700 placeholder-neutral-500 dark:placeholder-neutral-400 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:placeholder-neutral-400 dark:focus:placeholder-neutral-500 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors duration-300" placeholder="Search" type="search">
              </div>
            </div>
          </div>
          
          <!-- Profile dropdown -->
          <div class="ml-3 relative">
            <div>
              <button @click="toggleDropdown" type="button" class="bg-white dark:bg-neutral-700 rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-neutral-800 transition-colors duration-300" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                <span class="sr-only">Open user menu</span>
                <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-neutral-100 dark:bg-neutral-600 transition-colors duration-300">
                  <svg class="h-full w-full text-neutral-300 dark:text-neutral-400 transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </span>
              </button>
            </div>
            
            <!-- Dropdown menu -->
            <div v-if="dropdownOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 dark:ring-opacity-80 focus:outline-none transition-colors duration-300" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
              <router-link to="/profile" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300" role="menuitem">Your Profile</router-link>
              <router-link to="/settings" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300" role="menuitem">Settings</router-link>
              <a href="#" @click.prevent="logout" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300" role="menuitem">Sign out</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';
import NotificationCenter from '../notifications/NotificationCenter.vue';

// Props
defineProps({
  sidebarOpen: {
    type: Boolean,
    required: true
  }
});

// Emits
defineEmits(['toggle-sidebar']);

// Store access
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

// State
const dropdownOpen = ref(false);

// Methods
function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value;
}

function logout() {
  authStore.logout();
  // Redirect to login page
  window.location.href = '/auth/login';
}
</script>
