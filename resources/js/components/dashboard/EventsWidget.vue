<template>
  <DashboardWidget 
    :title="title" 
    :loading="loading" 
    :refreshable="refreshable"
    @refresh="$emit('refresh')"
    type="events"
  >
    <template #icon>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </template>
    
    <div class="events-content">
      <div v-if="!events || events.length === 0" class="flex flex-col items-center justify-center py-8 text-neutral-500 dark:text-neutral-400 transition-colors duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p>No upcoming events</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="(event, index) in displayedEvents" 
          :key="index"
          class="event-item p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors duration-300"
        >
          <div class="flex items-start">
            <div class="event-date mr-4 flex-shrink-0 w-14 h-14 rounded-lg bg-primary-100 dark:bg-primary-900 flex flex-col items-center justify-center text-center transition-colors duration-300">
              <div class="text-sm font-medium text-primary-800 dark:text-primary-300 transition-colors duration-300">{{ formatMonth(event.date) }}</div>
              <div class="text-xl font-bold text-primary-600 dark:text-primary-400 transition-colors duration-300">{{ formatDay(event.date) }}</div>
            </div>
            
            <div class="flex-grow min-w-0">
              <h4 class="text-base font-medium text-neutral-900 dark:text-white truncate transition-colors duration-300">
                {{ event.title }}
              </h4>
              <div class="flex items-center text-sm text-neutral-500 dark:text-neutral-400 mt-1 transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ formatTime(event.date) }}</span>
              </div>
              <div v-if="event.location" class="flex items-center text-sm text-neutral-500 dark:text-neutral-400 mt-1 transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ event.location }}</span>
              </div>
            </div>
            
            <div v-if="event.type" class="ml-2 flex-shrink-0">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getEventTypeClass(event.type)"
              >
                {{ event.type }}
              </span>
            </div>
          </div>
        </div>
        
        <div v-if="events.length > limit" class="text-center pt-2">
          <button 
            @click="showAllEvents = !showAllEvents" 
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium transition-colors duration-300"
          >
            {{ showAllEvents ? 'Show Less' : `Show All (${events.length})` }}
          </button>
        </div>
      </div>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { ref, computed } from 'vue';
import DashboardWidget from './DashboardWidget.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Upcoming Events'
  },
  events: {
    type: Array,
    default: () => []
  },
  limit: {
    type: Number,
    default: 3
  },
  loading: {
    type: Boolean,
    default: false
  },
  refreshable: {
    type: Boolean,
    default: true
  }
});

defineEmits(['refresh']);

const showAllEvents = ref(false);

const displayedEvents = computed(() => {
  if (showAllEvents.value) {
    return props.events;
  }
  return props.events.slice(0, props.limit);
});

// Format date functions
const formatMonth = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('default', { month: 'short' });
};

const formatDay = (dateString) => {
  const date = new Date(dateString);
  return date.getDate();
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('default', { hour: '2-digit', minute: '2-digit' });
};

// Get event type class
const getEventTypeClass = (type) => {
  const typeClasses = {
    'Service': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'Meeting': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'Workshop': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'Outreach': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'Special': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
  };
  
  return typeClasses[type] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-300';
};
</script>
