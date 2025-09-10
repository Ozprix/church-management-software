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
      <!-- Form Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">
            {{ isEditing ? 'Edit Event' : 'Create New Event' }}
          </h1>
          <p class="text-gray-600">
            {{ isEditing ? 'Update event details for ' + group.name : 'Schedule a new event for ' + group.name }}
          </p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
          <router-link 
            :to="isEditing ? `/groups/${groupId}/events/${eventId}` : `/groups/${groupId}/events`" 
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
          >
            Cancel
          </router-link>
        </div>
      </div>

      <!-- Event Form -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <form @submit.prevent="saveEvent">
          <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Event Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Event Title <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="title" 
                    v-model="event.title" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                  <p v-if="validationErrors.title" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.title[0] }}
                  </p>
                </div>
                
                <div>
                  <label for="event_type" class="block text-sm font-medium text-gray-700 mb-1">
                    Event Type <span class="text-red-500">*</span>
                  </label>
                  <select 
                    id="event_type" 
                    v-model="event.event_type" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                    <option value="meeting">Meeting</option>
                    <option value="outreach">Outreach</option>
                    <option value="social">Social</option>
                    <option value="training">Training</option>
                    <option value="other">Other</option>
                  </select>
                  <p v-if="validationErrors.event_type" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.event_type[0] }}
                  </p>
                </div>
                
                <div class="md:col-span-2">
                  <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                  </label>
                  <textarea 
                    id="description" 
                    v-model="event.description" 
                    rows="3" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  ></textarea>
                  <p v-if="validationErrors.description" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.description[0] }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Date and Time -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Date and Time</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Event Date <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="date" 
                    id="event_date" 
                    v-model="event.event_date" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                  <p v-if="validationErrors.event_date" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.event_date[0] }}
                  </p>
                </div>
                
                <div>
                  <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                    Start Time <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="time" 
                    id="start_time" 
                    v-model="event.start_time" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                  <p v-if="validationErrors.start_time" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.start_time[0] }}
                  </p>
                </div>
                
                <div>
                  <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                    End Time
                  </label>
                  <input 
                    type="time" 
                    id="end_time" 
                    v-model="event.end_time" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                  <p v-if="validationErrors.end_time" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.end_time[0] }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Location -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
              <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                  Event Location
                </label>
                <input 
                  type="text" 
                  id="location" 
                  v-model="event.location" 
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="e.g., Church Hall, Conference Room, etc."
                >
                <p v-if="validationErrors.location" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.location[0] }}
                </p>
              </div>
            </div>
            
            <!-- Recurrence -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Recurrence</h3>
                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                  <input 
                    type="checkbox" 
                    id="is_recurring" 
                    v-model="event.is_recurring" 
                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                  />
                  <label 
                    for="is_recurring" 
                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"
                  ></label>
                </div>
              </div>
              
              <div v-if="event.is_recurring" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="recurrence_pattern" class="block text-sm font-medium text-gray-700 mb-1">
                    Recurrence Pattern <span class="text-red-500">*</span>
                  </label>
                  <select 
                    id="recurrence_pattern" 
                    v-model="event.recurrence_pattern" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                  <p v-if="validationErrors.recurrence_pattern" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.recurrence_pattern[0] }}
                  </p>
                </div>
                
                <div v-if="event.recurrence_pattern === 'weekly' || event.recurrence_pattern === 'monthly'">
                  <label for="recurrence_day" class="block text-sm font-medium text-gray-700 mb-1">
                    Recurrence Day <span class="text-red-500">*</span>
                  </label>
                  <select 
                    v-if="event.recurrence_pattern === 'weekly'"
                    id="recurrence_day" 
                    v-model="event.recurrence_day" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  >
                    <option value="sunday">Sunday</option>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                  </select>
                  <input 
                    v-else
                    type="number" 
                    id="recurrence_day" 
                    v-model="event.recurrence_day" 
                    min="1"
                    max="31"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Day of month (1-31)"
                    required
                  >
                  <p v-if="validationErrors.recurrence_day" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.recurrence_day[0] }}
                  </p>
                </div>
                
                <div>
                  <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700 mb-1">
                    End Recurrence
                  </label>
                  <input 
                    type="date" 
                    id="recurrence_end_date" 
                    v-model="event.recurrence_end_date" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :min="event.event_date"
                  >
                  <p v-if="validationErrors.recurrence_end_date" class="mt-1 text-sm text-red-600">
                    {{ validationErrors.recurrence_end_date[0] }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Additional Options -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Options</h3>
              <div class="space-y-4">
                <div class="flex items-center">
                  <input 
                    id="notify_members" 
                    type="checkbox" 
                    v-model="event.notify_members" 
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                  <label for="notify_members" class="ml-2 block text-sm text-gray-900">
                    Notify group members about this event
                  </label>
                </div>
                
                <div class="flex items-center">
                  <input 
                    id="is_active" 
                    type="checkbox" 
                    v-model="event.is_active" 
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Event is active
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Form Actions -->
          <div class="px-6 py-3 bg-gray-50 text-right">
            <button
              type="button"
              @click="$router.go(-1)"
              class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="saving"
            >
              <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
              {{ saving ? 'Saving...' : (isEditing ? 'Update Event' : 'Create Event') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GroupEventForm',
  
  data() {
    return {
      groupId: this.$route.params.id,
      eventId: this.$route.params.eventId,
      isEditing: !!this.$route.params.eventId,
      group: {},
      event: {
        title: '',
        description: '',
        event_date: new Date().toISOString().slice(0, 10),
        start_time: '18:00',
        end_time: '19:30',
        location: '',
        event_type: 'meeting',
        is_recurring: false,
        recurrence_pattern: 'weekly',
        recurrence_day: '',
        recurrence_end_date: '',
        notify_members: true,
        is_active: true
      },
      loading: true,
      saving: false,
      error: null,
      validationErrors: {}
    };
  },
  
  created() {
    this.fetchGroup();
    
    if (this.isEditing) {
      this.fetchEvent();
    } else {
      // Set default recurrence day based on the selected event date
      this.setDefaultRecurrenceDay();
      this.loading = false;
    }
  },
  
  watch: {
    'event.event_date'() {
      if (!this.isEditing && !this.event.recurrence_day) {
        this.setDefaultRecurrenceDay();
      }
    },
    
    'event.recurrence_pattern'() {
      this.setDefaultRecurrenceDay();
    }
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
          const eventData = response.data.data;
          
          // Format date and time fields
          this.event = {
            ...eventData,
            event_date: eventData.event_date,
            start_time: eventData.start_time,
            end_time: eventData.end_time || '',
            recurrence_end_date: eventData.recurrence_end_date || ''
          };
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
    
    setDefaultRecurrenceDay() {
      if (!this.event.event_date) return;
      
      const eventDate = new Date(this.event.event_date);
      
      if (this.event.recurrence_pattern === 'weekly') {
        const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        this.event.recurrence_day = days[eventDate.getDay()];
      } else if (this.event.recurrence_pattern === 'monthly') {
        this.event.recurrence_day = eventDate.getDate().toString();
      }
    },
    
    async saveEvent() {
      this.saving = true;
      this.validationErrors = {};
      
      try {
        const eventData = { ...this.event };
        
        // Only include recurrence fields if is_recurring is true
        if (!eventData.is_recurring) {
          delete eventData.recurrence_pattern;
          delete eventData.recurrence_day;
          delete eventData.recurrence_end_date;
        }
        
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/api/groups/${this.groupId}/events/${this.eventId}`, eventData);
        } else {
          response = await axios.post(`/api/groups/${this.groupId}/events`, eventData);
        }
        
        if (response.data.status === 'success') {
          // Redirect to the events list or event detail page
          if (this.isEditing) {
            this.$router.push(`/groups/${this.groupId}/events/${this.eventId}`);
          } else {
            this.$router.push(`/groups/${this.groupId}/events`);
          }
        }
      } catch (error) {
        console.error('Error saving event:', error);
        
        if (error.response && error.response.data && error.response.data.errors) {
          this.validationErrors = error.response.data.errors;
        } else {
          this.error = 'An error occurred while saving the event';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>

<style scoped>
.toggle-checkbox:checked {
  right: 0;
  border-color: #3b82f6;
}
.toggle-checkbox:checked + .toggle-label {
  background-color: #3b82f6;
}
.toggle-checkbox {
  right: 0;
  transition: all 0.3s;
}
.toggle-label {
  transition: all 0.3s;
}
</style>
