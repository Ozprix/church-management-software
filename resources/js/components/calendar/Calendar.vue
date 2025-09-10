<template>
  <div class="calendar-component">
    <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <!-- Calendar Title and Navigation -->
        <div class="flex items-center space-x-4">
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">
            {{ calendarTitle }}
          </h2>
          <div class="flex items-center space-x-2">
            <button 
              @click="prev" 
              class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
              aria-label="Previous"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button 
              @click="today" 
              class="px-3 py-1 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded transition-colors duration-200"
            >
              Today
            </button>
            <button 
              @click="next" 
              class="p-1 rounded-full text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200"
              aria-label="Next"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
        
        <!-- View Options and Actions -->
        <div class="flex items-center space-x-4">
          <div class="flex border border-neutral-300 dark:border-neutral-600 rounded-md overflow-hidden">
            <button 
              @click="setView('month')" 
              class="px-3 py-1 text-sm font-medium transition-colors duration-200"
              :class="calendarView === 'month' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
            >
              Month
            </button>
            <button 
              @click="setView('week')" 
              class="px-3 py-1 text-sm font-medium transition-colors duration-200"
              :class="calendarView === 'week' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
            >
              Week
            </button>
            <button 
              @click="setView('day')" 
              class="px-3 py-1 text-sm font-medium transition-colors duration-200"
              :class="calendarView === 'day' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
            >
              Day
            </button>
            <button 
              @click="setView('list')" 
              class="px-3 py-1 text-sm font-medium transition-colors duration-200"
              :class="calendarView === 'list' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
            >
              List
            </button>
          </div>
          
          <button 
            @click="openNewEventModal" 
            class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Event
          </button>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="mt-4 flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4">
        <!-- Category Filter -->
        <div class="flex-1">
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Categories
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="category in categories"
              :key="category.id"
              @click="toggleCategoryFilter(category.id)"
              class="px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 flex items-center"
              :class="[
                isCategorySelected(category.id) 
                  ? 'bg-opacity-100 text-white' 
                  : 'bg-opacity-20 text-neutral-800 dark:text-neutral-200',
                { 'ring-2 ring-offset-2': isCategorySelected(category.id) }
              ]"
              :style="{ backgroundColor: isCategorySelected(category.id) ? category.color : 'transparent', borderColor: category.color }"
            >
              <span class="w-2 h-2 rounded-full mr-1" :style="{ backgroundColor: category.color }"></span>
              {{ category.name }}
            </button>
          </div>
        </div>
        
        <!-- Search -->
        <div class="flex-1">
          <label for="search-events" class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            Search
          </label>
          <div class="relative">
            <input 
              id="search-events" 
              v-model="searchTerm" 
              type="text" 
              placeholder="Search events..." 
              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white transition-colors duration-200"
              @input="updateSearchFilter"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- FullCalendar Component -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <FullCalendar 
        ref="fullCalendar"
        :options="calendarOptions"
      />
    </div>
    
    <!-- Event Modal -->
    <EventModal 
      v-if="eventModalOpen"
      :event="selectedEvent"
      :categories="categories"
      :resources="resources"
      @close="closeEventModal"
      @save="saveEvent"
      @delete="deleteEvent"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useEventsStore } from '../../stores/events';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import EventModal from './EventModal.vue';

const eventsStore = useEventsStore();

// Refs
const fullCalendar = ref(null);
const searchTerm = ref('');
const selectedCategories = ref([]);

// Computed properties
const calendarView = computed(() => eventsStore.calendarView);
const events = computed(() => eventsStore.calendarEvents);
const categories = computed(() => eventsStore.categories);
const resources = computed(() => eventsStore.resources);
const eventModalOpen = computed(() => eventsStore.eventModalOpen);
const selectedEvent = computed(() => eventsStore.selectedEvent);

const calendarTitle = computed(() => {
  if (!fullCalendar.value) return '';
  
  const calendarApi = fullCalendar.value.getApi();
  return calendarApi.view.title;
});

const calendarOptions = computed(() => {
  return {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: calendarView.value,
    headerToolbar: false, // We're using our own custom header
    events: events.value,
    editable: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    initialDate: eventsStore.selectedDate,
    firstDay: 0, // Start week on Sunday
    height: 'auto',
    themeSystem: 'standard',
    
    // Event handlers
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
    datesSet: handleDatesSet,
    
    // Customize event rendering
    eventContent: renderEventContent,
    
    // Dark mode support
    eventClassNames: function(arg) {
      return document.documentElement.classList.contains('dark') ? 'fc-event-dark' : '';
    }
  };
});

// Methods
function setView(view) {
  eventsStore.setCalendarView(view);
  if (fullCalendar.value) {
    const calendarApi = fullCalendar.value.getApi();
    calendarApi.changeView(view);
  }
}

function prev() {
  if (fullCalendar.value) {
    const calendarApi = fullCalendar.value.getApi();
    calendarApi.prev();
  }
}

function next() {
  if (fullCalendar.value) {
    const calendarApi = fullCalendar.value.getApi();
    calendarApi.next();
  }
}

function today() {
  if (fullCalendar.value) {
    const calendarApi = fullCalendar.value.getApi();
    calendarApi.today();
  }
}

function handleDateSelect(selectInfo) {
  const newEvent = {
    id: 'new-event-' + Date.now(),
    title: '',
    start: selectInfo.startStr,
    end: selectInfo.endStr,
    allDay: selectInfo.allDay,
    color: '#4F46E5', // Default color
    category: 'worship', // Default category
    description: '',
    location: '',
    organizer: '',
    resources: [],
    reminders: [],
    isPublic: true
  };
  
  eventsStore.openEventModal(newEvent);
}

function handleEventClick(clickInfo) {
  const eventId = clickInfo.event.id;
  const event = eventsStore.getEventById(eventId);
  
  if (event) {
    eventsStore.openEventModal(event);
  }
}

function handleEventDrop(dropInfo) {
  const eventId = dropInfo.event.id;
  const event = eventsStore.getEventById(eventId);
  
  if (event) {
    const updatedEvent = {
      ...event,
      start: dropInfo.event.startStr,
      end: dropInfo.event.endStr,
      allDay: dropInfo.event.allDay
    };
    
    eventsStore.updateEvent(updatedEvent);
  }
}

function handleEventResize(resizeInfo) {
  const eventId = resizeInfo.event.id;
  const event = eventsStore.getEventById(eventId);
  
  if (event) {
    const updatedEvent = {
      ...event,
      end: resizeInfo.event.endStr
    };
    
    eventsStore.updateEvent(updatedEvent);
  }
}

function handleDatesSet(dateInfo) {
  eventsStore.setSelectedDate(dateInfo.startStr);
}

function renderEventContent(eventInfo) {
  const timeText = eventInfo.timeText;
  const title = eventInfo.event.title;
  
  return {
    html: `
      <div class="fc-event-main-content">
        ${timeText ? `<div class="fc-event-time">${timeText}</div>` : ''}
        <div class="fc-event-title">${title}</div>
      </div>
    `
  };
}

function openNewEventModal() {
  const now = new Date();
  const start = new Date(now.setMinutes(Math.ceil(now.getMinutes() / 30) * 30));
  const end = new Date(new Date(start).setHours(start.getHours() + 1));
  
  const newEvent = {
    id: 'new-event-' + Date.now(),
    title: '',
    start: start.toISOString(),
    end: end.toISOString(),
    allDay: false,
    color: '#4F46E5', // Default color
    category: 'worship', // Default category
    description: '',
    location: '',
    organizer: '',
    resources: [],
    reminders: [],
    isPublic: true
  };
  
  eventsStore.openEventModal(newEvent);
}

function closeEventModal() {
  eventsStore.closeEventModal();
}

function saveEvent(event) {
  if (event.id.startsWith('new-event-')) {
    // This is a new event
    eventsStore.addEvent(event);
  } else {
    // This is an existing event
    eventsStore.updateEvent(event);
  }
  
  eventsStore.closeEventModal();
  refreshCalendar();
}

function deleteEvent(eventId) {
  eventsStore.deleteEvent(eventId);
  eventsStore.closeEventModal();
  refreshCalendar();
}

function refreshCalendar() {
  if (fullCalendar.value) {
    const calendarApi = fullCalendar.value.getApi();
    calendarApi.refetchEvents();
  }
}

function toggleCategoryFilter(categoryId) {
  const index = selectedCategories.value.indexOf(categoryId);
  
  if (index === -1) {
    selectedCategories.value.push(categoryId);
  } else {
    selectedCategories.value.splice(index, 1);
  }
  
  updateCategoryFilter();
}

function isCategorySelected(categoryId) {
  return selectedCategories.value.includes(categoryId);
}

function updateCategoryFilter() {
  eventsStore.updateFilter({ categories: selectedCategories.value });
  refreshCalendar();
}

function updateSearchFilter() {
  eventsStore.updateFilter({ search: searchTerm.value });
  refreshCalendar();
}

// Watch for dark mode changes to refresh calendar
const darkModeObserver = new MutationObserver((mutations) => {
  mutations.forEach((mutation) => {
    if (mutation.attributeName === 'class' && fullCalendar.value) {
      refreshCalendar();
    }
  });
});

// Lifecycle hooks
onMounted(() => {
  // Observe dark mode changes
  darkModeObserver.observe(document.documentElement, { attributes: true });
  
  // Initialize with all categories selected
  selectedCategories.value = [];
});

// Clean up
watch(() => eventsStore.filter, (newFilter) => {
  selectedCategories.value = newFilter.categories;
  searchTerm.value = newFilter.search;
}, { deep: true, immediate: true });
</script>

<style>
/* FullCalendar Dark Mode Styles */
.fc-event-dark .fc-event-main {
  color: #fff;
}

.dark .fc-theme-standard .fc-scrollgrid,
.dark .fc-theme-standard td,
.dark .fc-theme-standard th {
  border-color: #4b5563;
}

.dark .fc-theme-standard .fc-scrollgrid {
  border-color: #4b5563;
}

.dark .fc .fc-col-header-cell-cushion,
.dark .fc .fc-daygrid-day-number,
.dark .fc .fc-list-day-text,
.dark .fc .fc-list-day-side-text {
  color: #e5e7eb;
}

.dark .fc .fc-day-other .fc-daygrid-day-top {
  opacity: 0.5;
}

.dark .fc-theme-standard .fc-list-day-cushion {
  background-color: #374151;
}

.dark .fc-theme-standard .fc-list {
  border-color: #4b5563;
}

.dark .fc-theme-standard .fc-list-event:hover td {
  background-color: #374151;
}

.dark .fc-theme-standard .fc-list-empty {
  background-color: #1f2937;
  color: #e5e7eb;
}

.dark .fc-timegrid-slot-label-cushion,
.dark .fc-timegrid-axis-cushion {
  color: #e5e7eb;
}

.dark .fc-highlight {
  background-color: rgba(79, 70, 229, 0.2);
}
</style>
