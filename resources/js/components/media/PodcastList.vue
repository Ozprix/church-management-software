<template>
  <div class="podcast-list">
    <!-- Header -->
    <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Church Podcasts</h2>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">Listen to our latest podcast episodes</p>
        </div>
        
        <div class="flex space-x-2">
          <button 
            v-if="isAdmin"
            @click="showAddPodcastModal = true" 
            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Podcast
          </button>
        </div>
      </div>
    </div>
    
    <!-- Podcast Grid -->
    <div v-if="podcasts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="podcast in podcasts" 
        :key="podcast.id" 
        class="podcast-card bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-200"
      >
        <!-- Cover Art -->
        <div class="aspect-square bg-neutral-100 dark:bg-neutral-900">
          <img 
            v-if="podcast.coverArt" 
            :src="podcast.coverArt" 
            :alt="podcast.title" 
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
          </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            {{ podcast.title }}
          </h3>
          
          <div class="mt-2 flex items-center text-sm text-neutral-500 dark:text-neutral-400">
            <span>{{ podcast.host }}</span>
          </div>
          
          <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300 line-clamp-2">
            {{ podcast.description }}
          </p>
          
          <div class="mt-4 flex items-center justify-between">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ podcast.episodes.length }} episodes
            </div>
            
            <button 
              @click="viewPodcast(podcast)" 
              class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              View Episodes
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
      </svg>
      <h3 class="mt-2 text-lg font-medium text-neutral-900 dark:text-white">No podcasts found</h3>
      <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
        There are no podcasts available at this time.
      </p>
      <div v-if="isAdmin" class="mt-6">
        <button 
          @click="showAddPodcastModal = true" 
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Podcast
        </button>
      </div>
    </div>
    
    <!-- Podcast Details Modal -->
    <div v-if="showPodcastModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closePodcastModal"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    {{ selectedPodcast?.title }}
                  </h3>
                  
                  <div class="flex items-center space-x-2">
                    <a 
                      v-if="selectedPodcast?.rssUrl" 
                      :href="selectedPodcast.rssUrl" 
                      target="_blank" 
                      class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z" />
                      </svg>
                    </a>
                  </div>
                </div>
                
                <div class="mt-4 space-y-4">
                  <div class="flex items-start space-x-4">
                    <div class="w-24 h-24 bg-neutral-100 dark:bg-neutral-900 rounded-md flex-shrink-0">
                      <img 
                        v-if="selectedPodcast?.coverArt" 
                        :src="selectedPodcast.coverArt" 
                        :alt="selectedPodcast.title" 
                        class="w-full h-full object-cover rounded-md"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                      </div>
                    </div>
                    
                    <div>
                      <div class="text-sm text-neutral-500 dark:text-neutral-400">
                        Hosted by
                      </div>
                      <div class="text-sm font-medium text-neutral-900 dark:text-white">
                        {{ selectedPodcast?.host }}
                      </div>
                      
                      <div class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        {{ selectedPodcast?.subscribers }} subscribers
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Description</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ selectedPodcast?.description }}</p>
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Episodes</h4>
                    <div class="mt-2 space-y-2 max-h-60 overflow-y-auto">
                      <div 
                        v-for="episode in selectedPodcast?.episodes" 
                        :key="episode.id" 
                        class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-md"
                      >
                        <div class="flex justify-between items-start">
                          <div>
                            <h5 class="text-sm font-medium text-neutral-900 dark:text-white">{{ episode.title }}</h5>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(episode.date) }} • {{ formatDuration(episode.duration) }}</p>
                          </div>
                          
                          <button 
                            @click="playEpisode(episode)" 
                            class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors duration-200"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                          </button>
                        </div>
                        
                        <p class="mt-1 text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2">
                          {{ episode.description }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="closePodcastModal" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Audio Player Modal -->
    <div v-if="currentEpisode" class="fixed bottom-0 inset-x-0 bg-white dark:bg-neutral-800 border-t border-neutral-200 dark:border-neutral-700 shadow-lg p-4 z-40">
      <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center mb-4 md:mb-0">
          <div class="w-12 h-12 bg-neutral-200 dark:bg-neutral-700 rounded-md flex-shrink-0 mr-4">
            <img 
              v-if="currentPodcast?.coverArt" 
              :src="currentPodcast.coverArt" 
              :alt="currentPodcast.title" 
              class="w-full h-full object-cover rounded-md"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </div>
          </div>
          
          <div>
            <h3 class="text-sm font-medium text-neutral-900 dark:text-white line-clamp-1">
              {{ currentEpisode.title }}
            </h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ currentPodcast?.title }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center space-x-4">
          <button 
            @click="closeAudioPlayer" 
            class="p-2 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useMediaStore } from '../../stores/media';

// Store
const mediaStore = useMediaStore();

// State
const showAddPodcastModal = ref(false);
const showPodcastModal = ref(false);
const selectedPodcast = ref(null);
const currentEpisode = ref(null);
const currentPodcast = ref(null);
const isAdmin = ref(true); // This would typically come from an auth store

// Computed properties
const podcasts = computed(() => mediaStore.podcasts);

// Methods
function formatDate(dateString) {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

function formatDuration(minutes) {
  if (!minutes) return '00:00';
  
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  
  if (hours > 0) {
    return `${hours}:${mins.toString().padStart(2, '0')}`;
  } else {
    return `${mins}:00`;
  }
}

function viewPodcast(podcast) {
  selectedPodcast.value = podcast;
  showPodcastModal.value = true;
}

function closePodcastModal() {
  selectedPodcast.value = null;
  showPodcastModal.value = false;
}

function playEpisode(episode) {
  currentEpisode.value = episode;
  currentPodcast.value = selectedPodcast.value;
  
  // In a real implementation, this would increment the download count
  // and actually play the audio
  alert(`Playing episode: ${episode.title}`);
}

function closeAudioPlayer() {
  currentEpisode.value = null;
  currentPodcast.value = null;
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
