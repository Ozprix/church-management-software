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
      <h1 class="text-2xl font-bold text-gray-800">Edit Event</h1>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error Alert -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Form -->
    <div v-else class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="updateEvent">
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

          <!-- Attendance Information -->
          <div v-if="attendanceCount > 0" class="md:col-span-2 bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center text-blue-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <span class="font-semibold">This event has {{ attendanceCount }} attendance records.</span>
            </div>
            <p class="mt-2 text-sm text-blue-700">
              Note: Changing the date and time will not affect existing attendance records.
            </p>
            <div class="mt-2">
              <router-link 
                :to="`/attendance/events/${eventId}`" 
                class="text-blue-600 hover:text-blue-800 font-medium"
              >
                View Attendance Records
              </router-link>
            </div>
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
            <span v-else>Update Event</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EventEdit',
  data() {
    return {
      eventId: this.$route.params.id,
      event: {
        name: '',
        type: '',
        description: '',
        start_time: '',
        end_time: '',
        location: ''
      },
      attendanceCount: 0,
      loading: true,
      saving: false,
      error: null,
      validationErrors: {}
    };
  },
  created() {
    this.fetchEvent();
  },
  methods: {
    formatDateTimeForInput(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toISOString().slice(0, 16);
    },
    
    async fetchEvent() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/events/${this.eventId}`);
        
        if (response.data.status === 'success') {
          const eventData = response.data.data;
          
          // Format dates for datetime-local input
          eventData.start_time = this.formatDateTimeForInput(eventData.start_time);
          eventData.end_time = this.formatDateTimeForInput(eventData.end_time);
          
          this.event = eventData;
          this.attendanceCount = eventData.attendance_count || 0;
        } else {
          this.error = 'Failed to load event';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching the event';
      } finally {
        this.loading = false;
      }
    },
    
    async updateEvent() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        const response = await axios.put(`/events/${this.eventId}`, this.event);
        
        if (response.data.status === 'success') {
          // Redirect to the event list with a success message
          this.$router.push({
            path: '/events',
            query: { 
              message: 'Event updated successfully',
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
          this.error = error.response?.data?.message || 'An error occurred while updating the event.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
