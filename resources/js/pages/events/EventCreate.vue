<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link 
        to="/events" 
        class="text-blue-600 hover:text-blue-800 mr-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Events
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Create New Event</h1>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="saveEvent">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Event Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Event Information</h2>
          </div>

          <!-- Event Name -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
              Event Name *
            </label>
            <input 
              id="name" 
              v-model="event.name" 
              type="text" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.name" class="text-red-500 text-xs mt-1">
              {{ validationErrors.name[0] }}
            </p>
          </div>

          <!-- Event Type -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
              Event Type
            </label>
            <select 
              id="type" 
              v-model="event.type" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">Select Type</option>
              <option value="service">Service</option>
              <option value="meeting">Meeting</option>
              <option value="class">Class</option>
              <option value="outreach">Outreach</option>
              <option value="social">Social</option>
              <option value="other">Other</option>
            </select>
            <p v-if="validationErrors.type" class="text-red-500 text-xs mt-1">
              {{ validationErrors.type[0] }}
            </p>
          </div>

          <!-- Location -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
              Location
            </label>
            <input 
              id="location" 
              v-model="event.location" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.location" class="text-red-500 text-xs mt-1">
              {{ validationErrors.location[0] }}
            </p>
          </div>

          <!-- Date and Time -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-4">Date and Time</h2>
          </div>

          <!-- Start Time -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="start_time">
              Start Date and Time *
            </label>
            <input 
              id="start_time" 
              v-model="event.start_time" 
              type="datetime-local" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.start_time" class="text-red-500 text-xs mt-1">
              {{ validationErrors.start_time[0] }}
            </p>
          </div>

          <!-- End Time -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="end_time">
              End Date and Time *
            </label>
            <input 
              id="end_time" 
              v-model="event.end_time" 
              type="datetime-local" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.end_time" class="text-red-500 text-xs mt-1">
              {{ validationErrors.end_time[0] }}
            </p>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
              Description
            </label>
            <textarea 
              id="description" 
              v-model="event.description" 
              rows="4" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            ></textarea>
            <p v-if="validationErrors.description" class="text-red-500 text-xs mt-1">
              {{ validationErrors.description[0] }}
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end">
          <button 
            type="button" 
            @click="$router.push('/events')" 
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            :disabled="saving"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Create Event</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EventCreate',
  data() {
    return {
      event: {
        name: '',
        type: '',
        description: '',
        start_time: '',
        end_time: '',
        location: ''
      },
      saving: false,
      error: null,
      validationErrors: {}
    };
  },
  created() {
    // Set default start and end times to the next hour rounded to the nearest hour
    const now = new Date();
    const startTime = new Date(now);
    startTime.setHours(startTime.getHours() + 1);
    startTime.setMinutes(0);
    startTime.setSeconds(0);
    
    const endTime = new Date(startTime);
    endTime.setHours(endTime.getHours() + 1);
    
    this.event.start_time = this.formatDateTimeForInput(startTime);
    this.event.end_time = this.formatDateTimeForInput(endTime);
  },
  methods: {
    formatDateTimeForInput(date) {
      return date.toISOString().slice(0, 16);
    },
    
    async saveEvent() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        const response = await axios.post('/events', this.event);
        
        if (response.data.status === 'success') {
          // Redirect to the event list with a success message
          this.$router.push({
            path: '/events',
            query: { 
              message: 'Event created successfully',
              type: 'success'
            }
          });
        }
      } catch (error) {
        if (error.response?.status === 422) {
          // Validation errors
          this.validationErrors = error.response.data.errors || {};
          this.error = 'Please correct the errors in the form.';
        } else {
          this.error = error.response?.data?.message || 'An error occurred while saving the event.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
