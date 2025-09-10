<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                {{ isNewEvent ? 'Create Event' : 'Edit Event' }}
              </h3>
              
              <div class="mt-4 space-y-4">
                <!-- Event Title -->
                <div>
                  <label for="event-title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Title *
                  </label>
                  <input 
                    type="text" 
                    id="event-title" 
                    v-model="formData.title" 
                    class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                    placeholder="Event title"
                    required
                  />
                  <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                </div>
                
                <!-- Date and Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="event-start" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      Start *
                    </label>
                    <input 
                      type="datetime-local" 
                      id="event-start" 
                      v-model="formData.start" 
                      class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      required
                    />
                    <p v-if="errors.start" class="mt-1 text-sm text-red-600">{{ errors.start }}</p>
                  </div>
                  
                  <div>
                    <label for="event-end" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      End *
                    </label>
                    <input 
                      type="datetime-local" 
                      id="event-end" 
                      v-model="formData.end" 
                      class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      required
                    />
                    <p v-if="errors.end" class="mt-1 text-sm text-red-600">{{ errors.end }}</p>
                  </div>
                </div>
                
                <!-- All Day Toggle -->
                <div class="flex items-center">
                  <input 
                    type="checkbox" 
                    id="event-all-day" 
                    v-model="formData.allDay" 
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                  />
                  <label for="event-all-day" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    All day event
                  </label>
                </div>
                
                <!-- Location -->
                <div>
                  <label for="event-location" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Location
                  </label>
                  <input 
                    type="text" 
                    id="event-location" 
                    v-model="formData.location" 
                    class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                    placeholder="Event location"
                  />
                </div>
                
                <!-- Description -->
                <div>
                  <label for="event-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Description
                  </label>
                  <textarea 
                    id="event-description" 
                    v-model="formData.description" 
                    rows="3" 
                    class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                    placeholder="Event description"
                  ></textarea>
                </div>
                
                <!-- Category -->
                <div>
                  <label for="event-category" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Category
                  </label>
                  <div class="mt-1 relative">
                    <select 
                      id="event-category" 
                      v-model="formData.category" 
                      class="block w-full bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 pl-3 pr-10 text-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    >
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                  </div>
                </div>
                
                <!-- Color -->
                <div>
                  <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Color
                  </label>
                  <div class="mt-1 flex flex-wrap gap-2">
                    <button 
                      v-for="category in categories" 
                      :key="category.id"
                      type="button"
                      @click="formData.color = category.color"
                      class="w-6 h-6 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      :class="{ 'ring-2 ring-offset-2': formData.color === category.color }"
                      :style="{ backgroundColor: category.color }"
                      :aria-label="`Select ${category.name} color`"
                    ></button>
                  </div>
                </div>
                
                <!-- Organizer -->
                <div>
                  <label for="event-organizer" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Organizer
                  </label>
                  <input 
                    type="text" 
                    id="event-organizer" 
                    v-model="formData.organizer" 
                    class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                    placeholder="Event organizer"
                  />
                </div>
                
                <!-- Resources -->
                <div>
                  <label for="event-resources" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Resources
                  </label>
                  <select 
                    id="event-resources" 
                    v-model="formData.resources" 
                    multiple
                    class="mt-1 block w-full bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 text-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  >
                    <option v-for="resource in resources" :key="resource.id" :value="resource.name">
                      {{ resource.name }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Hold Ctrl/Cmd to select multiple resources</p>
                </div>
                
                <!-- Public/Private Toggle -->
                <div class="flex items-center">
                  <input 
                    type="checkbox" 
                    id="event-public" 
                    v-model="formData.isPublic" 
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                  />
                  <label for="event-public" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    Public event (visible to all members)
                  </label>
                </div>
                
                <!-- Recurring Event -->
                <div>
                  <div class="flex items-center">
                    <input 
                      type="checkbox" 
                      id="event-recurring" 
                      v-model="isRecurring" 
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                    <label for="event-recurring" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                      Recurring event
                    </label>
                  </div>
                  
                  <div v-if="isRecurring" class="mt-2 pl-6 space-y-3">
                    <div>
                      <label for="recurring-frequency" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Frequency
                      </label>
                      <select 
                        id="recurring-frequency" 
                        v-model="formData.recurring.frequency" 
                        class="mt-1 block w-full bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 text-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                      >
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                      </select>
                    </div>
                    
                    <div v-if="formData.recurring.frequency === 'weekly'">
                      <label for="recurring-day" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Day of Week
                      </label>
                      <select 
                        id="recurring-day" 
                        v-model="formData.recurring.day" 
                        class="mt-1 block w-full bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 text-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                      >
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                      </select>
                    </div>
                    
                    <div>
                      <label for="recurring-interval" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Repeat every
                      </label>
                      <div class="mt-1 flex items-center">
                        <input 
                          type="number" 
                          id="recurring-interval" 
                          v-model="formData.recurring.interval" 
                          min="1" 
                          class="block w-20 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                        />
                        <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                          {{ intervalLabel }}
                        </span>
                      </div>
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
            @click="save" 
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            {{ isNewEvent ? 'Create' : 'Update' }}
          </button>
          <button 
            type="button" 
            @click="close" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
          <button 
            v-if="!isNewEvent" 
            type="button" 
            @click="confirmDelete" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-red-700 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-900 dark:hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  event: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  },
  resources: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'save', 'delete']);

// Form data
const formData = ref({
  id: '',
  title: '',
  start: '',
  end: '',
  allDay: false,
  location: '',
  description: '',
  category: 'worship',
  color: '#4F46E5',
  organizer: '',
  resources: [],
  isPublic: true,
  recurring: {
    frequency: 'weekly',
    day: 0,
    interval: 1
  }
});

// Form validation
const errors = ref({
  title: '',
  start: '',
  end: ''
});

// Recurring event toggle
const isRecurring = ref(false);

// Computed properties
const isNewEvent = computed(() => {
  return formData.value.id.startsWith('new-event-');
});

const intervalLabel = computed(() => {
  switch (formData.value.recurring.frequency) {
    case 'daily':
      return 'day(s)';
    case 'weekly':
      return 'week(s)';
    case 'monthly':
      return 'month(s)';
    case 'yearly':
      return 'year(s)';
    default:
      return '';
  }
});

// Initialize form data from props
watch(() => props.event, (newEvent) => {
  if (newEvent) {
    // Format dates for datetime-local input
    const startDate = new Date(newEvent.start);
    const endDate = new Date(newEvent.end);
    
    const formatDateForInput = (date) => {
      return date.toISOString().slice(0, 16);
    };
    
    formData.value = {
      id: newEvent.id,
      title: newEvent.title,
      start: formatDateForInput(startDate),
      end: formatDateForInput(endDate),
      allDay: newEvent.allDay,
      location: newEvent.location || '',
      description: newEvent.description || '',
      category: newEvent.category || 'worship',
      color: newEvent.color || '#4F46E5',
      organizer: newEvent.organizer || '',
      resources: newEvent.resources || [],
      isPublic: newEvent.isPublic !== undefined ? newEvent.isPublic : true,
      recurring: newEvent.recurring || {
        frequency: 'weekly',
        day: 0,
        interval: 1
      }
    };
    
    isRecurring.value = !!newEvent.recurring;
  }
}, { immediate: true });

// Methods
function validateForm() {
  let isValid = true;
  errors.value = {
    title: '',
    start: '',
    end: ''
  };
  
  if (!formData.value.title.trim()) {
    errors.value.title = 'Title is required';
    isValid = false;
  }
  
  if (!formData.value.start) {
    errors.value.start = 'Start date is required';
    isValid = false;
  }
  
  if (!formData.value.end) {
    errors.value.end = 'End date is required';
    isValid = false;
  }
  
  if (formData.value.start && formData.value.end) {
    const startDate = new Date(formData.value.start);
    const endDate = new Date(formData.value.end);
    
    if (endDate < startDate) {
      errors.value.end = 'End date must be after start date';
      isValid = false;
    }
  }
  
  return isValid;
}

function save() {
  if (!validateForm()) {
    return;
  }
  
  // Prepare event data
  const eventData = { ...formData.value };
  
  // Handle recurring event
  if (!isRecurring.value) {
    eventData.recurring = null;
  }
  
  // Convert dates to ISO strings
  eventData.start = new Date(eventData.start).toISOString();
  eventData.end = new Date(eventData.end).toISOString();
  
  emit('save', eventData);
}

function close() {
  emit('close');
}

function confirmDelete() {
  if (confirm('Are you sure you want to delete this event?')) {
    emit('delete', formData.value.id);
  }
}
</script>
