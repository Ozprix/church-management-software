<template>
  <div class="events-page">
    <div class="container mx-auto px-4 py-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Church Events</h1>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
          Manage and view all church events and activities
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Upcoming Events</h2>
            
            <div class="space-y-3">
              <div v-for="event in upcomingEvents" :key="event.id" class="p-3 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 cursor-pointer transition-colors duration-200" @click="viewEvent(event)">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: event.color }"></div>
                  <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ event.title }}</span>
                </div>
                <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                  {{ formatEventDate(event.start, event.end, event.allDay) }}
                </div>
                <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                  {{ event.location }}
                </div>
              </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
              <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">Categories</h3>
              <div class="space-y-2">
                <div v-for="category in categories" :key="category.id" class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: category.color }"></div>
                  <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ category.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Main Calendar -->
        <div class="lg:col-span-3">
          <Calendar />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useEventsStore } from '../stores/events';
import Calendar from '../components/calendar/Calendar.vue';

const eventsStore = useEventsStore();

// Computed properties
const upcomingEvents = computed(() => eventsStore.getUpcomingEvents(5));
const categories = computed(() => eventsStore.categories);

// Methods
function formatEventDate(start, end, allDay) {
  const startDate = new Date(start);
  const endDate = new Date(end);
  
  const options = { 
    month: 'short', 
    day: 'numeric',
    hour: allDay ? undefined : '2-digit',
    minute: allDay ? undefined : '2-digit'
  };
  
  if (allDay) {
    if (isSameDay(startDate, endDate)) {
      return startDate.toLocaleDateString(undefined, options);
    } else {
      return `${startDate.toLocaleDateString(undefined, options)} - ${endDate.toLocaleDateString(undefined, options)}`;
    }
  } else {
    if (isSameDay(startDate, endDate)) {
      return `${startDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })} - ${endDate.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}`;
    } else {
      return `${startDate.toLocaleDateString(undefined, options)} - ${endDate.toLocaleDateString(undefined, options)}`;
    }
  }
}

function isSameDay(date1, date2) {
  return date1.getFullYear() === date2.getFullYear() &&
         date1.getMonth() === date2.getMonth() &&
         date1.getDate() === date2.getDate();
}

function viewEvent(event) {
  eventsStore.openEventModal(event);
}
</script>
