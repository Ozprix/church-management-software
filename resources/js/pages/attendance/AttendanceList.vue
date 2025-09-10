<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Attendance Management</h1>
      <router-link 
        to="/attendance/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        Record Attendance
      </router-link>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="event_filter">
            Filter by Event
          </label>
          <select 
            id="event_filter" 
            v-model="filters.event_id" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchAttendance"
          >
            <option value="">All Events</option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.name }} ({{ formatDate(event.start_time) }})
            </option>
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
        <div class="flex items-end">
          <button 
            @click="fetchAttendance" 
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

    <!-- Attendance Table -->
    <div v-else-if="attendances.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Member
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Event
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Check-in Time
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Check-out Time
            </th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="attendance in attendances" :key="attendance.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img 
                    v-if="attendance.member?.profile_photo" 
                    :src="'/storage/' + attendance.member.profile_photo" 
                    class="h-10 w-10 rounded-full object-cover"
                    alt="Profile photo"
                  >
                  <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-500 text-sm font-medium">
                      {{ attendance.member?.first_name?.charAt(0) }}{{ attendance.member?.last_name?.charAt(0) }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ attendance.member?.first_name }} {{ attendance.member?.last_name }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ attendance.event?.name }}</div>
              <div class="text-sm text-gray-500">{{ formatDate(attendance.event?.start_time) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDateTime(attendance.check_in_time) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ attendance.check_out_time ? formatDateTime(attendance.check_out_time) : 'Not checked out' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button 
                v-if="!attendance.check_out_time"
                @click="checkOut(attendance)" 
                class="text-green-600 hover:text-green-900 mr-3"
              >
                Check Out
              </button>
              <button 
                @click="confirmDelete(attendance)" 
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
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
              <span class="font-medium">{{ Math.min(currentPage * perPage, totalAttendances) }}</span>
              of
              <span class="font-medium">{{ totalAttendances }}</span>
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
    <div v-else class="bg-white rounded-lg shadow-md p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No attendance records found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by recording attendance for an event.
      </p>
      <div class="mt-6">
        <router-link 
          to="/attendance/create" 
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Record Attendance
        </router-link>
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
                  Delete Attendance Record
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this attendance record? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="deleteAttendance" 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="deleting"
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
  name: 'AttendanceList',
  data() {
    return {
      attendances: [],
      events: [],
      loading: true,
      error: null,
      filters: {
        event_id: '',
        start_date: '',
        end_date: ''
      },
      currentPage: 1,
      perPage: 15,
      totalAttendances: 0,
      lastPage: 1,
      showDeleteModal: false,
      attendanceToDelete: null,
      deleting: false,
      checkingOut: false
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
    this.fetchAttendance();
    
    // Set default date range to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    this.filters.start_date = this.formatDateForInput(firstDay);
    this.filters.end_date = this.formatDateForInput(lastDay);
  },
  methods: {
    async fetchEvents() {
      try {
        const response = await axios.get('/events', {
          params: { per_page: 100 } // Get a large number of events for the dropdown
        });
        
        if (response.data.status === 'success') {
          this.events = response.data.data.data;
        }
      } catch (error) {
        this.error = 'Failed to load events. Please refresh the page and try again.';
      }
    },
    
    async fetchAttendance() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage
        };
        
        // Add filters if they are set
        if (this.filters.event_id) {
          params.event_id = this.filters.event_id;
        }
        
        if (this.filters.start_date && this.filters.end_date) {
          params.start_date = this.filters.start_date;
          params.end_date = this.filters.end_date;
        }
        
        const response = await axios.get('/attendance', { params });
        
        if (response.data.status === 'success') {
          this.attendances = response.data.data.data;
          this.totalAttendances = response.data.data.total;
          this.currentPage = response.data.data.current_page;
          this.lastPage = response.data.data.last_page;
        } else {
          this.error = 'Failed to load attendance records';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching attendance records';
      } finally {
        this.loading = false;
      }
    },
    
    changePage(page) {
      if (page === '...') return;
      if (page >= 1 && page <= this.lastPage) {
        this.currentPage = page;
        this.fetchAttendance();
      }
    },
    
    resetFilters() {
      this.filters = {
        event_id: '',
        start_date: '',
        end_date: ''
      };
      this.currentPage = 1;
      this.fetchAttendance();
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
    
    formatDateTime(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    
    formatDateForInput(date) {
      return date.toISOString().split('T')[0];
    },
    
    async checkOut(attendance) {
      if (this.checkingOut) return;
      
      this.checkingOut = true;
      
      try {
        const response = await axios.put(`/attendance/${attendance.id}`, {
          check_out_time: new Date().toISOString()
        });
        
        if (response.data.status === 'success') {
          // Update the attendance record in the list
          const index = this.attendances.findIndex(a => a.id === attendance.id);
          if (index !== -1) {
            this.attendances[index] = response.data.data;
          }
        } else {
          this.error = 'Failed to check out member';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while checking out the member';
      } finally {
        this.checkingOut = false;
      }
    },
    
    confirmDelete(attendance) {
      this.attendanceToDelete = attendance;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.attendanceToDelete = null;
    },
    
    async deleteAttendance() {
      if (!this.attendanceToDelete) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/attendance/${this.attendanceToDelete.id}`);
        
        if (response.data.status === 'success') {
          // Remove the deleted attendance from the list
          this.attendances = this.attendances.filter(a => a.id !== this.attendanceToDelete.id);
          
          // If we deleted the last item on the page, go to the previous page
          if (this.attendances.length === 0 && this.currentPage > 1) {
            this.currentPage--;
            await this.fetchAttendance();
          } else if (this.totalAttendances > 0) {
            // Update total count
            this.totalAttendances--;
          }
          
          this.showDeleteModal = false;
          this.attendanceToDelete = null;
        } else {
          this.error = 'Failed to delete attendance record';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while deleting the attendance record';
      } finally {
        this.deleting = false;
      }
    }
  }
};
</script>
