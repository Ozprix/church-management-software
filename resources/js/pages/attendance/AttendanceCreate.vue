<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link 
        to="/attendance" 
        class="text-blue-600 hover:text-blue-800 mr-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Attendance List
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Record Attendance</h1>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Success Alert -->
    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline">{{ successMessage }}</span>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Select Event</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="event_id">
              Event *
            </label>
            <select 
              id="event_id" 
              v-model="selectedEvent" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
              @change="loadEventDetails"
            >
              <option value="">Select an Event</option>
              <option v-for="event in events" :key="event.id" :value="event.id">
                {{ event.name }} ({{ formatDate(event.start_time) }})
              </option>
            </select>
          </div>
          <div v-if="eventDetails">
            <div class="bg-gray-100 p-4 rounded h-full">
              <h3 class="font-semibold text-gray-800">{{ eventDetails.name }}</h3>
              <p class="text-sm text-gray-600 mt-2">{{ eventDetails.description }}</p>
              <div class="mt-2 text-sm">
                <div><span class="font-semibold">Date:</span> {{ formatDate(eventDetails.start_time) }}</div>
                <div><span class="font-semibold">Time:</span> {{ formatTime(eventDetails.start_time) }} - {{ formatTime(eventDetails.end_time) }}</div>
                <div><span class="font-semibold">Location:</span> {{ eventDetails.location }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-700">Record Attendance</h2>
          <div class="flex items-center">
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search members..." 
              class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2"
              @input="searchMembers"
            >
            <select 
              v-model="filterStatus" 
              class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchMembers"
            >
              <option value="">All Members</option>
              <option value="active">Active Members</option>
              <option value="pending">Pending Members</option>
              <option value="inactive">Inactive Members</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Members Table -->
        <div v-else-if="members.length > 0" class="bg-white border rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input 
                    type="checkbox" 
                    :checked="allSelected" 
                    @change="toggleAllMembers" 
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                  >
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Member
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check-in Time
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="member in members" :key="member.id" :class="{ 'bg-blue-50': selectedMembers.includes(member.id) }">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="checkbox" 
                    :value="member.id" 
                    v-model="selectedMembers" 
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                    :disabled="attendedMembers.includes(member.id)"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <img 
                        v-if="member.profile_photo" 
                        :src="'/storage/' + member.profile_photo" 
                        class="h-10 w-10 rounded-full object-cover"
                        alt="Profile photo"
                      >
                      <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500 text-sm font-medium">
                          {{ member.first_name.charAt(0) }}{{ member.last_name.charAt(0) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ member.first_name }} {{ member.last_name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ member.email }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': member.membership_status === 'active',
                      'bg-red-100 text-red-800': member.membership_status === 'inactive',
                      'bg-yellow-100 text-yellow-800': member.membership_status === 'pending',
                      'bg-gray-100 text-gray-800': member.membership_status === 'transferred'
                    }"
                  >
                    {{ member.membership_status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="attendedMembers.includes(member.id)" class="text-green-600">
                    Already checked in
                  </span>
                  <input 
                    v-else
                    type="datetime-local" 
                    v-model="checkInTimes[member.id]" 
                    class="shadow appearance-none border rounded py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    :disabled="!selectedMembers.includes(member.id)"
                  >
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination -->
          <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button 
                @click="changePage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
              >
                Previous
              </button>
              <button 
                @click="changePage(currentPage + 1)" 
                :disabled="currentPage === lastPage"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === lastPage }"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(currentPage * perPage, totalMembers) }}</span>
                  of
                  <span class="font-medium">{{ totalMembers }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button 
                    @click="changePage(currentPage - 1)" 
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
                  >
                    <span class="sr-only">Previous</span>
                    &laquo;
                  </button>
                  <button 
                    v-for="page in paginationPages" 
                    :key="page"
                    @click="changePage(page)"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                    :class="page === currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'text-gray-500 hover:bg-gray-50'"
                  >
                    {{ page }}
                  </button>
                  <button 
                    @click="changePage(currentPage + 1)" 
                    :disabled="currentPage === lastPage"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': currentPage === lastPage }"
                  >
                    <span class="sr-only">Next</span>
                    &raquo;
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white border rounded-lg p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No members found</h3>
          <p class="mt-1 text-sm text-gray-500">
            Try adjusting your search or filter to find members.
          </p>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-8 flex justify-end">
        <button 
          type="button" 
          @click="$router.push('/attendance')" 
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2"
        >
          Cancel
        </button>
        <button 
          type="button" 
          @click="saveAttendance" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          :disabled="saving || selectedMembers.length === 0 || !selectedEvent"
        >
          <span v-if="saving">Saving...</span>
          <span v-else>Save Attendance</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AttendanceCreate',
  data() {
    return {
      events: [],
      members: [],
      selectedEvent: '',
      eventDetails: null,
      selectedMembers: [],
      checkInTimes: {},
      attendedMembers: [],
      searchQuery: '',
      filterStatus: 'active',
      loading: true,
      saving: false,
      error: null,
      successMessage: null,
      currentPage: 1,
      perPage: 15,
      totalMembers: 0,
      lastPage: 1,
      searchTimeout: null
    };
  },
  computed: {
    allSelected() {
      return this.members.length > 0 && 
             this.members.every(member => 
               this.selectedMembers.includes(member.id) || 
               this.attendedMembers.includes(member.id)
             );
    },
    paginationPages() {
      const pages = [];
      const maxVisiblePages = 5;
      
      if (this.lastPage <= maxVisiblePages) {
        // Show all pages if there are fewer than maxVisiblePages
        for (let i = 1; i <= this.lastPage; i++) {
          pages.push(i);
        }
      } else {
        // Always show first page
        pages.push(1);
        
        // Calculate start and end of middle pages
        let startPage = Math.max(2, this.currentPage - 1);
        let endPage = Math.min(this.lastPage - 1, this.currentPage + 1);
        
        // Adjust if we're near the beginning
        if (this.currentPage <= 3) {
          endPage = 4;
        }
        
        // Adjust if we're near the end
        if (this.currentPage >= this.lastPage - 2) {
          startPage = this.lastPage - 3;
        }
        
        // Add ellipsis after first page if needed
        if (startPage > 2) {
          pages.push('...');
        }
        
        // Add middle pages
        for (let i = startPage; i <= endPage; i++) {
          pages.push(i);
        }
        
        // Add ellipsis before last page if needed
        if (endPage < this.lastPage - 1) {
          pages.push('...');
        }
        
        // Always show last page
        pages.push(this.lastPage);
      }
      
      return pages;
    }
  },
  created() {
    this.fetchEvents();
    this.fetchMembers();
    
    // Set default check-in time to now for all members
    const now = new Date();
    const formattedNow = this.formatDateTimeForInput(now);
    
    this.defaultCheckInTime = formattedNow;
  },
  methods: {
    async fetchEvents() {
      try {
        const response = await axios.get('/events', {
          params: { 
            per_page: 100,
            // Get events from today onwards
            start_date: this.formatDateForInput(new Date())
          }
        });
        
        if (response.data.status === 'success') {
          this.events = response.data.data.data;
        }
      } catch (error) {
        this.error = 'Failed to load events. Please refresh the page and try again.';
      }
    },
    
    async loadEventDetails() {
      if (!this.selectedEvent) {
        this.eventDetails = null;
        return;
      }
      
      try {
        const response = await axios.get(`/events/${this.selectedEvent}`);
        
        if (response.data.status === 'success') {
          this.eventDetails = response.data.data;
          
          // Check which members have already attended this event
          this.checkAttendedMembers();
        }
      } catch (error) {
        this.error = 'Failed to load event details.';
      }
    },
    
    async checkAttendedMembers() {
      if (!this.selectedEvent) return;
      
      try {
        const response = await axios.get(`/attendance/events/${this.selectedEvent}`, {
          params: { per_page: 1000 } // Get all attendees for this event
        });
        
        if (response.data.status === 'success') {
          // Get IDs of members who already attended
          this.attendedMembers = response.data.data.attendances.data.map(a => a.member_id);
          
          // Remove already attended members from selection
          this.selectedMembers = this.selectedMembers.filter(id => !this.attendedMembers.includes(id));
        }
      } catch (error) {
        this.error = 'Failed to check already attended members.';
      }
    },
    
    async fetchMembers() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage
        };
        
        if (this.searchQuery) {
          params.search = this.searchQuery;
        }
        
        if (this.filterStatus) {
          params.status = this.filterStatus;
        }
        
        const response = await axios.get('/members', { params });
        
        if (response.data.status === 'success') {
          this.members = response.data.data.data;
          this.totalMembers = response.data.data.total;
          this.currentPage = response.data.data.current_page;
          this.lastPage = response.data.data.last_page;
          
          // Initialize check-in times for new members
          this.members.forEach(member => {
            if (!this.checkInTimes[member.id]) {
              this.checkInTimes[member.id] = this.defaultCheckInTime;
            }
          });
          
          // If we have a selected event, check which members have already attended
          if (this.selectedEvent) {
            this.checkAttendedMembers();
          }
        } else {
          this.error = 'Failed to load members';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching members';
      } finally {
        this.loading = false;
      }
    },
    
    searchMembers() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1; // Reset to first page when searching
        this.fetchMembers();
      }, 300);
    },
    
    changePage(page) {
      if (page === '...') return;
      if (page >= 1 && page <= this.lastPage) {
        this.currentPage = page;
        this.fetchMembers();
      }
    },
    
    toggleAllMembers() {
      if (this.allSelected) {
        // Deselect all
        this.selectedMembers = [];
      } else {
        // Select all that aren't already attended
        this.selectedMembers = this.members
          .filter(member => !this.attendedMembers.includes(member.id))
          .map(member => member.id);
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    },
    
    formatTime(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    
    formatDateForInput(date) {
      return date.toISOString().split('T')[0];
    },
    
    formatDateTimeForInput(date) {
      return date.toISOString().slice(0, 16);
    },
    
    async saveAttendance() {
      if (!this.selectedEvent || this.selectedMembers.length === 0) {
        this.error = 'Please select an event and at least one member';
        return;
      }
      
      this.saving = true;
      this.error = null;
      this.successMessage = null;
      
      try {
        // Create attendance records for each selected member
        const promises = this.selectedMembers.map(memberId => {
          return axios.post('/attendance', {
            event_id: this.selectedEvent,
            member_id: memberId,
            check_in_time: this.checkInTimes[memberId] || this.defaultCheckInTime
          });
        });
        
        const results = await Promise.allSettled(promises);
        
        // Count successes and failures
        const successes = results.filter(result => result.status === 'fulfilled').length;
        const failures = results.filter(result => result.status === 'rejected').length;
        
        if (successes > 0) {
          this.successMessage = `Successfully recorded attendance for ${successes} member${successes !== 1 ? 's' : ''}.`;
          
          // Update the list of already attended members
          await this.checkAttendedMembers();
          
          // Clear selection
          this.selectedMembers = [];
        }
        
        if (failures > 0) {
          this.error = `Failed to record attendance for ${failures} member${failures !== 1 ? 's' : ''}.`;
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while saving attendance';
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
