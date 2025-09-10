<template>
  <div class="media-page">
    <div class="container mx-auto px-4 py-6">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Media Library</h1>
        <p class="text-neutral-500 dark:text-neutral-400">Access sermons, podcasts, and devotionals</p>
      </div>
      
      <!-- Tabs -->
      <div class="mb-6 border-b border-neutral-200 dark:border-neutral-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="activeTab = tab.id" 
            :class="[
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.name }}
            <span 
              v-if="tab.count" 
              :class="[
                activeTab === tab.id
                  ? 'bg-primary-100 text-primary-600 dark:bg-primary-900 dark:text-primary-300'
                  : 'bg-neutral-100 text-neutral-900 dark:bg-neutral-700 dark:text-neutral-300',
                'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>
      
      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Sermons Tab -->
        <div v-if="activeTab === 'sermons'">
          <SermonLibrary />
        </div>
        
        <!-- Podcasts Tab -->
        <div v-else-if="activeTab === 'podcasts'">
          <PodcastList />
        </div>
        
        <!-- Devotionals Tab -->
        <div v-else-if="activeTab === 'devotionals'">
          <DevotionalList />
        </div>
        
        <!-- Resources Tab -->
        <div v-else-if="activeTab === 'resources'">
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-neutral-900 dark:text-white">Resources Coming Soon</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
              We're working on adding downloadable resources and study materials.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useMediaStore } from '../stores/media';
import { useRoute, useRouter } from 'vue-router';
import SermonLibrary from '../components/media/SermonLibrary.vue';
import PodcastList from '../components/media/PodcastList.vue';
import DevotionalList from '../components/media/DevotionalList.vue';

// Store
const mediaStore = useMediaStore();
const route = useRoute();
const router = useRouter();

// State
const activeTab = ref('sermons');

// Tabs configuration
const tabs = computed(() => [
  { id: 'sermons', name: 'Sermons', count: mediaStore.sermons.length },
  { id: 'podcasts', name: 'Podcasts', count: mediaStore.podcasts.length },
  { id: 'devotionals', name: 'Devotionals', count: mediaStore.devotionals.length },
  { id: 'resources', name: 'Resources', count: null }
]);

// Watch for route query changes
onMounted(() => {
  // Initialize the media store if needed
  mediaStore.initialize();
  
  // Set active tab from route query if present
  if (route.query.tab && tabs.value.some(tab => tab.id === route.query.tab)) {
    activeTab.value = route.query.tab;
  }
});

// Watch for active tab changes to update route
watch(activeTab, (newTab) => {
  router.replace({ query: { ...route.query, tab: newTab } });
});
</script>
