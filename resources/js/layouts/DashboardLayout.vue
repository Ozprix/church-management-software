<template>
  <div class="min-h-screen bg-neutral-100 dark:bg-neutral-900 transition-colors duration-300">
    <!-- Side Navigation -->
    <SideNavigation :sidebarOpen="sidebarOpen" @toggle-sidebar="toggleSidebar" />
    
    <!-- Main Content -->
    <div class="md:pl-64 flex flex-col flex-1">
      <!-- Top Navigation -->
      <TopNavigation :sidebarOpen="sidebarOpen" @toggle-sidebar="toggleSidebar">
        <!-- Theme Switch in the top navigation -->
        <template #actions>
          <ThemeSwitch />
        </template>
      </TopNavigation>
      
      <!-- Page Content -->
      <main class="flex-1">
        <router-view></router-view>
      </main>
      
      <!-- Footer -->
      <footer class="bg-white dark:bg-neutral-800 shadow p-4 text-center text-neutral-500 dark:text-neutral-400 text-sm transition-colors duration-300">
        <p>&copy; {{ new Date().getFullYear() }} {{ churchName }}. All rights reserved.</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useSettingsStore } from '../stores/settings';
import SideNavigation from '../components/layout/SideNavigation.vue';
import TopNavigation from '../components/layout/TopNavigation.vue';
import ThemeSwitch from '../components/ui/ThemeSwitch.vue';

const settingsStore = useSettingsStore();
const sidebarOpen = ref(false);

// Get church name from settings
const churchName = computed(() => settingsStore.churchName || 'Church Management System');

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}
</script>
