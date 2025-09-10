<template>
  <div>
    <!-- Header -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-6">
      {{ error }}
    </div>

    <div v-else>
      <!-- Event Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <div class="flex items-center mb-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ event.title }}</h1>
            <span 
              class="ml-3 px-2 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-green-100 text-green-800': isUpcoming && event.is_active,
                'bg-gray-100 text-gray-800': !isUpcoming && event.is_active,
                'bg-red-100 text-red-800': !event.is_active
              }"
            >
              {{ 
                !event.is_active 
                  ? 'Cancelled' 
                  : isUpcoming 
                    ? 'Upcoming' 
                    : 'Past' 
              }}
            </span>
          </div>
          <p class="text-gray-600">{{ group.name }}</p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
          <router-link 
            :to="`/groups/${groupId}/events`" 
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
          >
            Back to Events
          </router-link>
          <router-link 
            :to="`/groups/${groupId}/events/${eventId}/edit`" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            Edit Event
          </router-link>
          <button 
            @click="confirmDelete" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Event Details -->
      <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column - Basic Info -->
            <div class="md:col-span-2">
              <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Information</h3>
                <div class="space-y-4">
                  <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="text-gray-900">{{ event.description || 'No description provided' }}</p>
                  </div>
                  
                  <div>
                    <p class="text-sm text-gray-500">Event Type</p>
                    <div class="flex items-center mt-1">
                      <span 
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-blue-100 text-blue-800': event.event_type === 'meeting',
                          'bg-green-100 text-green-800': event.event_type === 'outreach',
                          'bg-purple-100 text-purple-800': event.event_type === 'social',
                          'bg-yellow-100 text-yellow-800': event.event_type === 'training',
                          'bg-gray-100 text-gray-800': event.event_type === 'other'
                        }"
                      >
                        {{ capitalizeFirstLetter(event.event_type) }}
                      </span>
                    </div>
                  </div>
                  
                  <div>
                    <p class="text-sm text-gray-500">Created By</p>
                    <p class="text-gray-900">{{ event.creator ? `${event.creator.name}` : 'Unknown' }}</p>
                  </div>
                </div>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recurrence</h3>
                <div v-if="event.is_recurring" class="space-y-4">
                  <div>
                    <p class="text-sm text-gray-500">Recurrence Pattern</p>
                    <p class="text-gray-900">{{ formatRecurrencePattern }}</p>
                  </div>
                  
                  <div v-if="event.recurrence_end_date">
                    <p class="text-sm text-gray-500">Recurrence End Date</p>
                    <p class="text-gray-900">{{ formatDate(event.recurrence_end_date) }}</p>
                  </div>
                </div>
                <div v-else class="text-gray-500 italic">
                  This is a one-time event.
                </div>
              </div>
            </div>
            
            <!-- Right Column - Date, Time, Location -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Date & Time</h3>
                <div class="space-y-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                      <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500">Date</p>
                      <p class="text-gray-900 font-medium">{{ formatDate(event.event_date) }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                      <i class="fas fa-clock"></i>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500">Time</p>
                      <p class="text-gray-900 font-medium">
                        {{ formatTime(event.start_time) }}
                        <span v-if="event.end_time"> - {{ formatTime(event.end_time) }}</span>
                      </p>
                      <p v-if="event.start_time && event.end_time" class="text-sm text-gray-500">
                        ({{ event.formatted_duration || calculateDuration() }})
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                    <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div>
                    <p class="text-gray-900">{{ event.location || 'No location specified' }}</p>
                  </div>
                </div>
              </div>
              
              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm text-gray-500">Notify Members</p>
                    <p class="text-gray-900">{{ event.notify_members ? 'Yes' : 'No' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-gray-900">{{ event.is_active ? 'Active' : 'Cancelled' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Group Information -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Group Information</h3>
          <div class="flex items-center">
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
              <span>{{ getGroupInitials(group.name) }}</span>
            </div>
            <div>
              <router-link :to="`/groups/${groupId}`" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                {{ group.name }}
              </router-link>
              <p class="text-gray-600">{{ capitalizeFirstLetter(group.type?.replace('_', ' ') || '') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Delete Event
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this event? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="deleteEvent" 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="deleting"
            >
              <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
            <button 
              @click="cancelDelete" 
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GroupEventDetail',
  
  data() {
    return {
      groupId: this.$route.params.id,
      eventId: this.$route.params.eventId,
      group: {},
      event: {},
      loading: true,
      error: null,
      showDeleteModal: false,
      deleting: false
    };
  },
  
  computed: {
    isUpcoming() {
      if (!this.event.event_date) return false;
      
      const eventDate = new Date(this.event.event_date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      return eventDate >= today;
    },
    
    formatRecurrencePattern() {
      if (!this.event.is_recurring || !this.event.recurrence_pattern) {
        return 'Not recurring';
      }
      
      let pattern = this.capitalizeFirstLetter(this.event.recurrence_pattern);
      
      if (this.event.recurrence_pattern === 'weekly' && this.event.recurrence_day) {
        pattern += ` on ${this.capitalizeFirstLetter(this.event.recurrence_day)}s`;
      } else if (this.event.recurrence_pattern === 'monthly' && this.event.recurrence_day) {
        pattern += ` on day ${this.event.recurrence_day} of the month`;
      }
      
      return pattern;
    }
  },
  
  created() {
    this.fetchGroup();
    this.fetchEvent();
  },
  
  methods: {
    async fetchGroup() {
      try {
        const response = await axios.get(`/api/groups/${this.groupId}`);
        
        if (response.data.status === 'success') {
          this.group = response.data.data;
        } else {
          this.error = 'Failed to load group information';
        }
      } catch (error) {
        console.error('Error fetching group:', error);
        this.error = 'An error occurred while loading group information';
      }
    },
    
    async fetchEvent() {
      try {
        const response = await axios.get(`/api/groups/${this.groupId}/events/${this.eventId}`);
        
        if (response.data.status === 'success') {
          this.event = response.data.data;
        } else {
          this.error = 'Failed to load event information';
        }
      } catch (error) {
        console.error('Error fetching event:', error);
        this.error = 'An error occurred while loading event information';
      } finally {
        this.loading = false;
      }
    },
    
    confirmDelete() {
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
    },
    
    async deleteEvent() {
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/api/groups/${this.groupId}/events/${this.eventId}`);
        
        if (response.data.status === 'success') {
          this.$router.push(`/groups/${this.groupId}/events`);
        }
      } catch (error) {
        console.error('Error deleting event:', error);
      } finally {
        this.deleting = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString(undefined, options);
    },
    
    formatTime(timeString) {
      if (!timeString) return '';
      
      // Convert 24-hour format to 12-hour format
      const [hours, minutes] = timeString.split(':');
      const hour = parseInt(hours, 10);
      const period = hour >= 12 ? 'PM' : 'AM';
      const hour12 = hour % 12 || 12;
      
      return `${hour12}:${minutes} ${period}`;
    },
    
    calculateDuration() {
      if (!this.event.start_time || !this.event.end_time) {
        return '';
      }
      
      const start = this.parseTime(this.event.start_time);
      const end = this.parseTime(this.event.end_time);
      
      if (!start || !end) {
        return '';
      }
      
      let diffInMinutes = (end.getHours() * 60 + end.getMinutes()) - (start.getHours() * 60 + start.getMinutes());
      
      // Handle cases where end time is on the next day
      if (diffInMinutes < 0) {
        diffInMinutes += 24 * 60;
      }
      
      if (diffInMinutes < 60) {
        return `${diffInMinutes} minutes`;
      }
      
      const hours = Math.floor(diffInMinutes / 60);
      const minutes = diffInMinutes % 60;
      
      if (minutes === 0) {
        return `${hours} ${hours === 1 ? 'hour' : 'hours'}`;
      }
      
      return `${hours} ${hours === 1 ? 'hour' : 'hours'} ${minutes} minutes`;
    },
    
    parseTime(timeString) {
      if (!timeString) return null;
      
      const [hours, minutes] = timeString.split(':').map(Number);
      const date = new Date();
      date.setHours(hours, minutes, 0, 0);
      
      return date;
    },
    
    capitalizeFirstLetter(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    
    getGroupInitials(name) {
      if (!name) return '';
      
      return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .substring(0, 2)
        .toUpperCase();
    }
  }
};
</script>
