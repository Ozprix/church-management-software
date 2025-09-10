<template>
  <div class="sermon-card bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Thumbnail -->
    <div class="relative aspect-video bg-neutral-100 dark:bg-neutral-900">
      <img 
        v-if="sermon.thumbnailUrl" 
        :src="sermon.thumbnailUrl" 
        :alt="sermon.title" 
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
      </div>
      
      <!-- Duration badge -->
      <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
        {{ formatDuration(sermon.duration) }}
      </div>
      
      <!-- Series badge (if applicable) -->
      <div v-if="sermon.series" class="absolute top-2 left-2 bg-primary-600 text-white text-xs px-2 py-1 rounded">
        {{ sermon.series }}
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-4">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-white line-clamp-2">
        {{ sermon.title }}
      </h3>
      
      <div class="mt-2 flex items-center text-sm text-neutral-500 dark:text-neutral-400">
        <span>{{ sermon.speaker }}</span>
        <span class="mx-2">•</span>
        <span>{{ formatDate(sermon.date) }}</span>
      </div>
      
      <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300 line-clamp-2">
        {{ sermon.description }}
      </p>
      
      <!-- Scripture reference -->
      <div v-if="sermon.scripture" class="mt-2 text-sm text-primary-600 dark:text-primary-400 font-medium">
        {{ sermon.scripture }}
      </div>
      
      <!-- Tags -->
      <div v-if="sermon.tags && sermon.tags.length > 0" class="mt-3 flex flex-wrap gap-1">
        <span 
          v-for="tag in sermon.tags" 
          :key="tag" 
          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-300"
        >
          {{ tag }}
        </span>
      </div>
      
      <!-- Stats and actions -->
      <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-4 text-xs text-neutral-500 dark:text-neutral-400">
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            {{ sermon.views }}
          </div>
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ sermon.downloads }}
          </div>
        </div>
        
        <div class="flex space-x-2">
          <button 
            v-if="sermon.audioUrl" 
            @click="playSermon" 
            class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
            aria-label="Play"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
          <button 
            v-if="sermon.videoUrl" 
            @click="watchSermon" 
            class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
            aria-label="Watch"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
          <button 
            @click="viewDetails" 
            class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
            aria-label="Details"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { useMediaStore } from '../../stores/media';

const props = defineProps({
  sermon: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['play', 'watch', 'view-details']);

const mediaStore = useMediaStore();

// Methods
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

function formatDate(dateString) {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

function playSermon() {
  mediaStore.incrementSermonViews(props.sermon.id);
  emit('play', props.sermon);
}

function watchSermon() {
  mediaStore.incrementSermonViews(props.sermon.id);
  emit('watch', props.sermon);
}

function viewDetails() {
  emit('view-details', props.sermon);
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
