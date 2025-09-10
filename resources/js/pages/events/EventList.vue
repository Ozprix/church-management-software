<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Event Management</h1>
      <router-link 
        to="/events/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        Create New Event
      </router-link>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="search">
            Search
          </label>
          <input 
            id="search" 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search by name, description, or location" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @input="debounceSearch"
          >
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="type_filter">
            Event Type
          </label>
          <select 
            id="type_filter" 
            v-model="filters.type" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchEvents"
          >
            <option value="">All Types</option>
            <option value="service">Service</option>
            <option value="meeting">Meeting</option>
            <option value="class">Class</option>
            <option value="outreach">Outreach</option>
            <option value="social">Social</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="date_range">
            Date Range
          </label>
          <div class="flex space-x-2">
            <input 
              type="date" 
              v-model="filters.start_date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <span class="self-center">to</span>
            <input 
              type="date" 
              v-model="filters.end_date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button 
          @click="fetchEvents" 
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
        >
          Apply Filters
        </button>
        <button 
          @click="resetFilters" 
          class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Events Grid -->
    <div v-else-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="event in events" :key="event.id" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
          <div class="flex justify-between items-start">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ event.name }}</h2>
            <span 
              class="px-2 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-blue-100 text-blue-800': event.type === 'service',
                'bg-green-100 text-green-800': event.type === 'meeting',
                'bg-purple-100 text-purple-800': event.type === 'class',
                'bg-yellow-100 text-yellow-800': event.type === 'outreach',
                'bg-pink-100 text-pink-800': event.type === 'social',
                'bg-gray-100 text-gray-800': !event.type || event.type === 'other'
              }"
            >
              {{ event.type || 'Other' }}
            </span>
          </div>
          <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ event.description || 'No description provided' }}</p>
          <div class="flex items-center text-gray-500 text-sm mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(event.start_time) }}</span>
          </div>
          <div class="flex items-center text-gray-500 text-sm mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ formatTime(event.start_time) }} - {{ formatTime(event.end_time) }}</span>
          </div>
          <div class="flex items-center text-gray-500 text-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ event.location || 'No location specified' }}</span>
          </div>
          <div class="flex items-center text-gray-500 text-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>{{ event.attendance_count || 0 }} attendees</span>
          </div>
          <div class="flex justify-between">
            <router-link 
              :to="`/attendance/events/${event.id}`" 
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              View Attendance
            </router-link>
            <div>
              <router-link 
                :to="`/events/${event.id}/edit`" 
                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium mr-3"
              >
                Edit
              </router-link>
              <button 
                @click="confirmDelete(event)" 
                class="text-red-600 hover:text-red-900 text-sm font-medium"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow-md p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by creating a new event.
      </p>
      <div class="mt-6">
        <router-link 
          to="/events/create" 
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Create Event
        </router-link>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="events.length > 0" class="mt-6 bg-white rounded-lg shadow-md px-4 py-3 flex items-center justify-between">
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
            <span class="font-medium">{{ Math.min(currentPage * perPage, totalEvents) }}</span>
            of
            <span class="font-medium">{{ totalEvents }}</span>
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

    <!-- Delete Confirmation Modal -->
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
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Delete Event
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete the event "{{ eventToDelete?.name }}"? This action cannot be undone.
                  </p>
                  <p v-if="eventToDelete?.attendance_count > 0" class="mt-2 text-sm text-red-500 font-semibold">
                    This event has {{ eventToDelete.attendance_count }} attendance records. You must delete those records first.
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
              :disabled="deleting || (eventToDelete?.attendance_count > 0)"
              :class="{ 'opacity-50 cursor-not-allowed': eventToDelete?.attendance_count > 0 }"
            >
              <span v-if="deleting">Deleting...</span>
              <span v-else>Delete</span>
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
  name: 'EventList',
  data() {
    return {
      events: [],
      loading: true,
      error: null,
      searchQuery: '',
      filters: {
        type: '',
        start_date: '',
        end_date: ''
      },
      currentPage: 1,
      perPage: 9,
      totalEvents: 0,
      lastPage: 1,
      showDeleteModal: false,
      eventToDelete: null,
      deleting: false,
      searchTimeout: null
    };
  },
  computed: {
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
    
    // Set default date range to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    this.filters.start_date = this.formatDateForInput(firstDay);
    this.filters.end_date = this.formatDateForInput(lastDay);
  },
  methods: {
    async fetchEvents() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage
        };
        
        // Add filters if they are set
        if (this.searchQuery) {
          params.search = this.searchQuery;
        }
        
        if (this.filters.type) {
          params.type = this.filters.type;
        }
        
        if (this.filters.start_date) {
          params.start_date = this.filters.start_date;
        }
        
        if (this.filters.end_date) {
          params.end_date = this.filters.end_date;
        }
        
        const response = await axios.get('/events', { params });
        
        if (response.data.status === 'success') {
          this.events = response.data.data.data;
          this.totalEvents = response.data.data.total;
          this.currentPage = response.data.data.current_page;
          this.lastPage = response.data.data.last_page;
        } else {
          this.error = 'Failed to load events';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching events';
      } finally {
        this.loading = false;
      }
    },
    
    changePage(page) {
      if (page === '...') return;
      if (page >= 1 && page <= this.lastPage) {
        this.currentPage = page;
        this.fetchEvents();
      }
    },
    
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1; // Reset to first page when searching
        this.fetchEvents();
      }, 300);
    },
    
    resetFilters() {
      this.searchQuery = '';
      this.filters = {
        type: '',
        start_date: '',
        end_date: ''
      };
      this.currentPage = 1;
      this.fetchEvents();
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
    
    confirmDelete(event) {
      this.eventToDelete = event;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.eventToDelete = null;
    },
    
    async deleteEvent() {
      if (!this.eventToDelete || this.eventToDelete.attendance_count > 0) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/events/${this.eventToDelete.id}`);
        
        if (response.data.status === 'success') {
          // Remove the deleted event from the list
          this.events = this.events.filter(e => e.id !== this.eventToDelete.id);
          
          // If we deleted the last item on the page, go to the previous page
          if (this.events.length === 0 && this.currentPage > 1) {
            this.currentPage--;
            await this.fetchEvents();
          } else if (this.totalEvents > 0) {
            // Update total count
            this.totalEvents--;
          }
          
          this.showDeleteModal = false;
          this.eventToDelete = null;
        } else {
          this.error = 'Failed to delete event';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while deleting the event';
      } finally {
        this.deleting = false;
      }
    }
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
