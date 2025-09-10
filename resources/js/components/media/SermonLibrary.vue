<template>
  <div class="sermon-library">
    <!-- Header and Controls -->
    <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Sermon Library</h2>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">Browse and search through our sermon collection</p>
        </div>
        
        <div class="flex space-x-2">
          <button 
            @click="showFilters = !showFilters" 
            class="inline-flex items-center px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
          </button>
          
          <button 
            v-if="isAdmin"
            @click="showAddSermonModal = true" 
            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Sermon
          </button>
        </div>
      </div>
      
      <!-- Search and Filters -->
      <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label for="sermon-search" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Search
          </label>
          <div class="relative">
            <input 
              id="sermon-search" 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search sermons..." 
              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Series Filter -->
        <div>
          <label for="series-filter" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Series
          </label>
          <select 
            id="series-filter" 
            v-model="selectedSeries" 
            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
          >
            <option value="">All Series</option>
            <option v-for="series in seriesList" :key="series.id" :value="series.id">
              {{ series.title }}
            </option>
          </select>
        </div>
        
        <!-- Speaker Filter -->
        <div>
          <label for="speaker-filter" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Speaker
          </label>
          <select 
            id="speaker-filter" 
            v-model="selectedSpeaker" 
            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
          >
            <option value="">All Speakers</option>
            <option v-for="speaker in speakersList" :key="speaker.id" :value="speaker.id">
              {{ speaker.name }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Sermon Grid -->
    <div v-if="filteredSermons.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <SermonCard 
        v-for="sermon in filteredSermons" 
        :key="sermon.id" 
        :sermon="sermon"
        @play="playSermon"
        @watch="watchSermon"
        @view-details="viewSermonDetails"
      />
    </div>
    
    <!-- Empty State -->
    <div v-else class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
      </svg>
      <h3 class="mt-2 text-lg font-medium text-neutral-900 dark:text-white">No sermons found</h3>
      <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
        {{ searchQuery || selectedSeries || selectedSpeaker ? 'Try adjusting your filters to find what you\'re looking for.' : 'There are no sermons available at this time.' }}
      </p>
      <div class="mt-6">
        <button 
          @click="resetFilters" 
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Reset Filters
        </button>
      </div>
    </div>
    
    <!-- Audio Player Modal -->
    <div v-if="currentAudioSermon" class="fixed bottom-0 inset-x-0 bg-white dark:bg-neutral-800 border-t border-neutral-200 dark:border-neutral-700 shadow-lg p-4 z-40">
      <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center mb-4 md:mb-0">
          <div class="w-12 h-12 bg-neutral-200 dark:bg-neutral-700 rounded-md flex-shrink-0 mr-4">
            <img 
              v-if="currentAudioSermon.thumbnailUrl" 
              :src="currentAudioSermon.thumbnailUrl" 
              :alt="currentAudioSermon.title" 
              class="w-full h-full object-cover rounded-md"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
              </svg>
            </div>
          </div>
          
          <div>
            <h3 class="text-sm font-medium text-neutral-900 dark:text-white line-clamp-1">
              {{ currentAudioSermon.title }}
            </h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ currentAudioSermon.speaker }}
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
    
    <!-- Video Player Modal -->
    <div v-if="showVideoModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeVideoPlayer"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
          <div class="bg-white dark:bg-neutral-900 p-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  {{ currentVideoSermon?.title }}
                </h3>
                
                <div class="mt-4 aspect-video bg-black">
                  <!-- Video embed would go here -->
                  <div class="w-full h-full flex items-center justify-center text-white">
                    Video player would be embedded here
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="closeVideoPlayer" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Sermon Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeSermonDetails"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  {{ selectedSermon?.title }}
                </h3>
                
                <div class="mt-4 space-y-4">
                  <div v-if="selectedSermon?.thumbnailUrl" class="w-full aspect-video bg-neutral-100 dark:bg-neutral-900 rounded-md overflow-hidden">
                    <img 
                      :src="selectedSermon.thumbnailUrl" 
                      :alt="selectedSermon.title" 
                      class="w-full h-full object-cover"
                    />
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Speaker</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ selectedSermon?.speaker }}</p>
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Date</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ formatDate(selectedSermon?.date) }}</p>
                  </div>
                  
                  <div v-if="selectedSermon?.series">
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Series</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ selectedSermon?.series }}</p>
                  </div>
                  
                  <div v-if="selectedSermon?.scripture">
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Scripture</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ selectedSermon?.scripture }}</p>
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Description</h4>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-white">{{ selectedSermon?.description }}</p>
                  </div>
                  
                  <div v-if="selectedSermon?.tags && selectedSermon.tags.length > 0">
                    <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Tags</h4>
                    <div class="mt-1 flex flex-wrap gap-1">
                      <span 
                        v-for="tag in selectedSermon.tags" 
                        :key="tag" 
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-300"
                      >
                        {{ tag }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              v-if="selectedSermon?.videoUrl"
              type="button" 
              @click="watchSermon(selectedSermon)"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Watch
            </button>
            <button 
              v-if="selectedSermon?.audioUrl"
              type="button" 
              @click="playSermon(selectedSermon)"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Listen
            </button>
            <button 
              v-if="selectedSermon?.notes"
              type="button" 
              @click="downloadNotes"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Download Notes
            </button>
            <button 
              type="button" 
              @click="closeSermonDetails" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useMediaStore } from '../../stores/media';
import SermonCard from './SermonCard.vue';

// Store
const mediaStore = useMediaStore();

// State
const searchQuery = ref('');
const selectedSeries = ref('');
const selectedSpeaker = ref('');
const showFilters = ref(false);
const currentAudioSermon = ref(null);
const currentVideoSermon = ref(null);
const showVideoModal = ref(false);
const selectedSermon = ref(null);
const showDetailsModal = ref(false);
const showAddSermonModal = ref(false);
const isAdmin = ref(true); // This would typically come from an auth store

// Computed properties
const sermons = computed(() => mediaStore.sermons);

const seriesList = computed(() => mediaStore.series);

const speakersList = computed(() => mediaStore.speakers);

const filteredSermons = computed(() => {
  let filtered = [...sermons.value];
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(sermon => 
      sermon.title.toLowerCase().includes(query) ||
      sermon.description.toLowerCase().includes(query) ||
      sermon.speaker.toLowerCase().includes(query) ||
      (sermon.scripture && sermon.scripture.toLowerCase().includes(query)) ||
      (sermon.tags && sermon.tags.some(tag => tag.toLowerCase().includes(query)))
    );
  }
  
  // Filter by series
  if (selectedSeries.value) {
    const series = mediaStore.getSeriesById(selectedSeries.value);
    if (series) {
      filtered = filtered.filter(sermon => series.sermonIds.includes(sermon.id));
    }
  }
  
  // Filter by speaker
  if (selectedSpeaker.value) {
    const speaker = mediaStore.getSpeakerById(selectedSpeaker.value);
    if (speaker) {
      filtered = filtered.filter(sermon => speaker.sermonIds.includes(sermon.id));
    }
  }
  
  return filtered;
});

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

function resetFilters() {
  searchQuery.value = '';
  selectedSeries.value = '';
  selectedSpeaker.value = '';
}

function playSermon(sermon) {
  currentAudioSermon.value = sermon;
  mediaStore.incrementSermonViews(sermon.id);
  closeSermonDetails();
}

function closeAudioPlayer() {
  currentAudioSermon.value = null;
}

function watchSermon(sermon) {
  currentVideoSermon.value = sermon;
  showVideoModal.value = true;
  mediaStore.incrementSermonViews(sermon.id);
  closeSermonDetails();
}

function closeVideoPlayer() {
  currentVideoSermon.value = null;
  showVideoModal.value = false;
}

function viewSermonDetails(sermon) {
  selectedSermon.value = sermon;
  showDetailsModal.value = true;
}

function closeSermonDetails() {
  selectedSermon.value = null;
  showDetailsModal.value = false;
}

function downloadNotes() {
  if (selectedSermon.value && selectedSermon.value.notes) {
    // In a real implementation, this would trigger a download
    mediaStore.incrementSermonDownloads(selectedSermon.value.id);
    alert(`Notes would be downloaded from: ${selectedSermon.value.notes}`);
  }
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
