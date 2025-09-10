<template>
  <div class="devotional-list">
    <!-- Header -->
    <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Daily Devotionals</h2>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">Spiritual guidance for your daily walk</p>
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
            @click="showAddDevotionalModal = true" 
            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Devotional
          </button>
        </div>
      </div>
      
      <!-- Search and Filters -->
      <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label for="devotional-search" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Search
          </label>
          <div class="relative">
            <input 
              id="devotional-search" 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search devotionals..." 
              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Author Filter -->
        <div>
          <label for="author-filter" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Author
          </label>
          <select 
            id="author-filter" 
            v-model="selectedAuthor" 
            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
          >
            <option value="">All Authors</option>
            <option v-for="author in authorsList" :key="author" :value="author">
              {{ author }}
            </option>
          </select>
        </div>
        
        <!-- Date Range Filter -->
        <div>
          <label for="date-filter" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Date Range
          </label>
          <select 
            id="date-filter" 
            v-model="dateRange" 
            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
          >
            <option value="all">All Time</option>
            <option value="week">Past Week</option>
            <option value="month">Past Month</option>
            <option value="quarter">Past 3 Months</option>
            <option value="year">Past Year</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Featured Devotional -->
    <div v-if="featuredDevotional" class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="md:flex">
        <!-- Image -->
        <div class="md:w-1/3 bg-neutral-100 dark:bg-neutral-900">
          <img 
            v-if="featuredDevotional.imageUrl" 
            :src="featuredDevotional.imageUrl" 
            :alt="featuredDevotional.title" 
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full min-h-[200px] flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
        
        <!-- Content -->
        <div class="p-6 md:w-2/3">
          <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100">
              Featured
            </div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">
              {{ formatDate(featuredDevotional.date) }}
            </div>
          </div>
          
          <h3 class="text-xl font-semibold text-neutral-900 dark:text-white">
            {{ featuredDevotional.title }}
          </h3>
          
          <div class="mt-2 flex items-center text-sm text-neutral-500 dark:text-neutral-400">
            <span>By {{ featuredDevotional.author }}</span>
          </div>
          
          <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
            {{ featuredDevotional.excerpt }}
          </p>
          
          <div class="mt-4">
            <button 
              @click="viewDevotional(featuredDevotional)" 
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Read Now
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Devotional Grid -->
    <div v-if="filteredDevotionals.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="devotional in filteredDevotionals" 
        :key="devotional.id" 
        class="devotional-card bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-200"
      >
        <!-- Image -->
        <div class="aspect-video bg-neutral-100 dark:bg-neutral-900">
          <img 
            v-if="devotional.imageUrl" 
            :src="devotional.imageUrl" 
            :alt="devotional.title" 
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
          <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ formatDate(devotional.date) }}
            </div>
            
            <div v-if="devotional.scripture" class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ devotional.scripture }}
            </div>
          </div>
          
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white line-clamp-1">
            {{ devotional.title }}
          </h3>
          
          <div class="mt-1 flex items-center text-sm text-neutral-500 dark:text-neutral-400">
            <span>By {{ devotional.author }}</span>
          </div>
          
          <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300 line-clamp-2">
            {{ devotional.excerpt }}
          </p>
          
          <div class="mt-4 flex items-center justify-between">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ devotional.readTime }} min read
            </div>
            
            <button 
              @click="viewDevotional(devotional)" 
              class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Read
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else-if="!featuredDevotional" class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
      </svg>
      <h3 class="mt-2 text-lg font-medium text-neutral-900 dark:text-white">No devotionals found</h3>
      <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
        {{ searchQuery || selectedAuthor || dateRange !== 'all' ? 'Try adjusting your filters to find what you\'re looking for.' : 'There are no devotionals available at this time.' }}
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
    
    <!-- Devotional Modal -->
    <div v-if="showDevotionalModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeDevotionalModal"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[80vh] overflow-y-auto">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-xl leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    {{ selectedDevotional?.title }}
                  </h3>
                  
                  <div class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ formatDate(selectedDevotional?.date) }}
                  </div>
                </div>
                
                <div class="flex items-center text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                  <span>By {{ selectedDevotional?.author }}</span>
                  <span class="mx-2">•</span>
                  <span>{{ selectedDevotional?.readTime }} min read</span>
                </div>
                
                <div v-if="selectedDevotional?.imageUrl" class="mb-6">
                  <img 
                    :src="selectedDevotional.imageUrl" 
                    :alt="selectedDevotional.title" 
                    class="w-full h-auto rounded-lg"
                  />
                </div>
                
                <div v-if="selectedDevotional?.scripture" class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                  <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-2">Scripture</h4>
                  <p class="text-neutral-900 dark:text-white italic">{{ selectedDevotional.scripture }}</p>
                  <p v-if="selectedDevotional?.scriptureText" class="mt-2 text-neutral-600 dark:text-neutral-300">
                    "{{ selectedDevotional.scriptureText }}"
                  </p>
                </div>
                
                <div class="devotional-content prose dark:prose-invert max-w-none">
                  <p v-if="selectedDevotional?.content" class="text-neutral-600 dark:text-neutral-300 whitespace-pre-line">
                    {{ selectedDevotional.content }}
                  </p>
                </div>
                
                <div v-if="selectedDevotional?.prayer" class="mt-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                  <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-2">Prayer</h4>
                  <p class="text-neutral-600 dark:text-neutral-300 italic">{{ selectedDevotional.prayer }}</p>
                </div>
                
                <div v-if="selectedDevotional?.tags && selectedDevotional.tags.length > 0" class="mt-6">
                  <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-2">Tags</h4>
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="tag in selectedDevotional.tags" 
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
          
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              v-if="isAdmin"
              type="button" 
              @click="editDevotional" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Edit
            </button>
            <button 
              type="button" 
              @click="shareDevotional" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Share
            </button>
            <button 
              type="button" 
              @click="closeDevotionalModal" 
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

// Store
const mediaStore = useMediaStore();

// State
const searchQuery = ref('');
const selectedAuthor = ref('');
const dateRange = ref('all');
const showFilters = ref(false);
const showAddDevotionalModal = ref(false);
const showDevotionalModal = ref(false);
const selectedDevotional = ref(null);
const isAdmin = ref(true); // This would typically come from an auth store

// Computed properties
const devotionals = computed(() => mediaStore.devotionals);

const featuredDevotional = computed(() => {
  return mediaStore.featuredDevotional;
});

const authorsList = computed(() => {
  const authors = new Set();
  devotionals.value.forEach(devotional => {
    if (devotional.author) {
      authors.add(devotional.author);
    }
  });
  return Array.from(authors);
});

const filteredDevotionals = computed(() => {
  let filtered = [...devotionals.value];
  
  // Filter out the featured devotional
  if (featuredDevotional.value) {
    filtered = filtered.filter(d => d.id !== featuredDevotional.value.id);
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(devotional => 
      devotional.title.toLowerCase().includes(query) ||
      devotional.excerpt.toLowerCase().includes(query) ||
      (devotional.content && devotional.content.toLowerCase().includes(query)) ||
      (devotional.scripture && devotional.scripture.toLowerCase().includes(query)) ||
      (devotional.author && devotional.author.toLowerCase().includes(query)) ||
      (devotional.tags && devotional.tags.some(tag => tag.toLowerCase().includes(query)))
    );
  }
  
  // Filter by author
  if (selectedAuthor.value) {
    filtered = filtered.filter(devotional => 
      devotional.author === selectedAuthor.value
    );
  }
  
  // Filter by date range
  if (dateRange.value !== 'all') {
    const now = new Date();
    let cutoffDate;
    
    switch (dateRange.value) {
      case 'week':
        cutoffDate = new Date(now.setDate(now.getDate() - 7));
        break;
      case 'month':
        cutoffDate = new Date(now.setMonth(now.getMonth() - 1));
        break;
      case 'quarter':
        cutoffDate = new Date(now.setMonth(now.getMonth() - 3));
        break;
      case 'year':
        cutoffDate = new Date(now.setFullYear(now.getFullYear() - 1));
        break;
      default:
        cutoffDate = new Date(0); // Beginning of time
    }
    
    filtered = filtered.filter(devotional => {
      const devotionalDate = new Date(devotional.date);
      return devotionalDate >= cutoffDate;
    });
  }
  
  // Sort by date (newest first)
  filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
  
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
  selectedAuthor.value = '';
  dateRange.value = 'all';
}

function viewDevotional(devotional) {
  selectedDevotional.value = devotional;
  showDevotionalModal.value = true;
  mediaStore.incrementDevotionalViews(devotional.id);
}

function closeDevotionalModal() {
  selectedDevotional.value = null;
  showDevotionalModal.value = false;
}

function editDevotional() {
  // In a real implementation, this would open an edit form
  alert(`Editing devotional: ${selectedDevotional.value.title}`);
}

function shareDevotional() {
  // In a real implementation, this would open a share dialog
  alert(`Sharing devotional: ${selectedDevotional.value.title}`);
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

.prose {
  max-width: 65ch;
  color: inherit;
}

.prose p {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}
</style>
