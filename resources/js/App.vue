<template>
  <div class='min-h-screen bg-neutral-100 dark:bg-neutral-900 transition-colors duration-300'>
    <!-- Toast Container for notifications -->
    <ToastContainer position="top-right" :max-toasts="5" />
    <!-- Loading Screen -->
    <LoadingScreen 
      :loading="isLoading" 
      message="Welcome to ChCMS"
      subMessage="Preparing your church management experience"
      :duration="2000"
      @progress-complete="isLoading = false"
    />
    
    <!-- Offline Status Bar -->
    <OfflineStatusBar />
    
    <!-- Main Content -->
    <div :class="{ 'opacity-0': isLoading, 'opacity-100': !isLoading }" class="transition-opacity duration-500">
      <Navbar v-if='isAuthenticated' />
      <div class='py-6'>
        <div class='max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
          <!-- Offline Manager (only shown on dashboard) -->
          <OfflineManager v-if="isAuthenticated && isDashboard" class="mb-4" />
          
          <router-view v-slot="{ Component }">
            <transition 
              name="fade" 
              mode="out-in"
              @before-leave="beforeLeave"
              @enter="enter"
              @after-enter="afterEnter"
            >
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </div>
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from './stores/auth';
import { useSettingsStore } from './stores/settings';
import { useOffline } from './services/offlineService';
import Navbar from './components/layout/Navbar.vue';
import Footer from './components/layout/Footer.vue';
import LoadingScreen from './components/layout/LoadingScreen.vue';
import ToastContainer from './components/ui/ToastContainer.vue';
import OfflineStatusBar from './components/layout/OfflineStatusBar.vue';
import OfflineManager from './components/offline/OfflineManager.vue';

// Use Pinia stores and services
const authStore = useAuthStore();
const settingsStore = useSettingsStore();
const offline = useOffline();
const route = useRoute();
const isLoading = ref(true);

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const isDashboard = computed(() => route.path === '/dashboard' || route.path === '/');
    
// Page transition methods
const beforeLeave = (el) => {
  el.style.opacity = 0;
  el.style.transform = 'translateY(20px)';
};

const enter = (el, done) => {
  el.style.opacity = 0;
  el.style.transform = 'translateY(20px)';
  setTimeout(() => {
    el.style.transition = 'all 0.3s ease';
    el.style.opacity = 1;
    el.style.transform = 'translateY(0)';
    done();
  }, 50);
};

const afterEnter = (el) => {
  el.style.transition = '';
};

// Watch for dark mode changes
watch(
  () => settingsStore.darkMode,
  (newValue) => {
    if (newValue === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }
);

onMounted(() => {
  // Fetch user data if token exists
  if (authStore.token) {
    authStore.fetchUser();
  }
  
  // Initialize dark mode from settings or system preference
  if (settingsStore.darkMode === null) {
    // Check system preference if no preference is set
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    settingsStore.setDarkMode(prefersDark);
  } else {
    // Apply existing preference
    settingsStore.updateTheme();
  }
  
  // Add sacred geometry pattern to body background
  document.body.classList.add('bg-sacred-pattern', 'bg-fixed', 'bg-no-repeat', 'bg-cover', 'bg-opacity-5', 'bg-blend-overlay');
  
  // Listen for system preference changes if user hasn't set a preference
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (settingsStore.darkMode === null) {
      settingsStore.setDarkMode(e.matches);
    }
  });
});
</script>

<style>
/* Global transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Better focus styles */
*:focus-visible {
  outline: 2px solid rgb(14, 165, 233);
  outline-offset: 2px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

::-webkit-scrollbar-thumb {
  background: rgba(14, 165, 233, 0.5);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(14, 165, 233, 0.7);
}

.dark ::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.dark ::-webkit-scrollbar-thumb {
  background: rgba(14, 165, 233, 0.3);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(14, 165, 233, 0.5);
}
</style>
