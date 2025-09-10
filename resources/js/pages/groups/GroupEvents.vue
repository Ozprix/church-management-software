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
      <!-- Events Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ group.name }} - Events</h1>
          <p class="text-gray-600">Manage events and meetings for this group</p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
          <router-link 
            :to="`/groups/${$route.params.id}`" 
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
          >
            Back to Group
          </router-link>
          <router-link 
            :to="`/groups/${$route.params.id}/events/new`" 
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-plus mr-2"></i> Create Event
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex flex-wrap gap-2 mb-4 md:mb-0">
            <button 
              @click="filter = 'all'" 
              :class="[
                filter === 'all' 
                  ? 'bg-blue-100 text-blue-800 border-blue-300' 
                  : 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200',
                'px-3 py-1 rounded border text-sm font-medium'
              ]"
            >
              All Events
            </button>
            <button 
              @click="filter = 'upcoming'" 
              :class="[
                filter === 'upcoming' 
                  ? 'bg-green-100 text-green-800 border-green-300' 
                  : 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200',
                'px-3 py-1 rounded border text-sm font-medium'
              ]"
            >
              Upcoming
            </button>
            <button 
              @click="filter = 'past'" 
              :class="[
                filter === 'past' 
                  ? 'bg-gray-200 text-gray-800 border-gray-300' 
                  : 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200',
                'px-3 py-1 rounded border text-sm font-medium'
              ]"
            >
              Past
            </button>
          </div>
          <div class="flex items-center">
            <select 
              v-model="eventType" 
              class="border border-gray-300 rounded px-3 py-1 mr-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="meeting">Meeting</option>
              <option value="outreach">Outreach</option>
              <option value="social">Social</option>
              <option value="training">Training</option>
              <option value="other">Other</option>
            </select>
            <div class="relative">
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search events..." 
                class="border border-gray-300 rounded pl-8 pr-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
              <i class="fas fa-search absolute left-3 top-2 text-gray-400"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Events List -->
      <div v-if="filteredEvents.length === 0" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="mb-4">
          <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
        <p class="text-gray-600 mb-4">
          {{ 
            filter === 'upcoming' 
              ? 'There are no upcoming events scheduled for this group.' 
              : filter === 'past' 
                ? 'There are no past events recorded for this group.'
                : 'There are no events matching your search criteria.'
          }}
        </p>
        <router-link 
          :to="`/groups/${$route.params.id}/events/new`" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
        >
          <i class="fas fa-plus mr-2"></i> Schedule an Event
        </router-link>
      </div>

      <div v-else class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Event
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date & Time
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Location
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="event in filteredEvents" :key="event.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0 mr-3">
                      <div 
                        class="h-10 w-10 rounded-full flex items-center justify-center text-white"
                        :class="{
                          'bg-blue-500': event.event_type === 'meeting',
                          'bg-green-500': event.event_type === 'outreach',
                          'bg-purple-500': event.event_type === 'social',
                          'bg-yellow-500': event.event_type === 'training',
                          'bg-gray-500': event.event_type === 'other'
                        }"
                      >
                        <i 
                          :class="{
                            'fas fa-users': event.event_type === 'meeting',
                            'fas fa-hands-helping': event.event_type === 'outreach',
                            'fas fa-glass-cheers': event.event_type === 'social',
                            'fas fa-chalkboard-teacher': event.event_type === 'training',
                            'fas fa-calendar-day': event.event_type === 'other'
                          }"
                        ></i>
                      </div>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ event.title }}</div>
                      <div v-if="event.description" class="text-sm text-gray-500 truncate max-w-xs">
                        {{ event.description }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(event.event_date) }}</div>
                  <div class="text-sm text-gray-500">
                    {{ formatTime(event.start_time) }}
                    <span v-if="event.end_time"> - {{ formatTime(event.end_time) }}</span>
                  </div>
                  <div v-if="event.is_recurring" class="text-xs text-blue-600 mt-1">
                    <i class="fas fa-sync-alt mr-1"></i> 
                    {{ capitalizeFirstLetter(event.recurrence_pattern) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ event.location || 'Not specified' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
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
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': isUpcoming(event) && event.is_active,
                      'bg-gray-100 text-gray-800': !isUpcoming(event) && event.is_active,
                      'bg-red-100 text-red-800': !event.is_active
                    }"
                  >
                    {{ 
                      !event.is_active 
                        ? 'Cancelled' 
                        : isUpcoming(event) 
                          ? 'Upcoming' 
                          : 'Past' 
                    }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <router-link 
                    :to="`/groups/${$route.params.id}/events/${event.id}`" 
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    View
                  </router-link>
                  <router-link 
                    :to="`/groups/${$route.params.id}/events/${event.id}/edit`" 
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </router-link>
                  <button 
                    @click="confirmDelete(event)" 
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button 
              @click="changePage(pagination.current_page - 1)" 
              :disabled="pagination.current_page === 1"
              :class="[
                pagination.current_page === 1 
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                  : 'bg-white text-gray-700 hover:bg-gray-50',
                'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md'
              ]"
            >
              Previous
            </button>
            <button 
              @click="changePage(pagination.current_page + 1)" 
              :disabled="pagination.current_page === pagination.last_page"
              :class="[
                pagination.current_page === pagination.last_page 
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                  : 'bg-white text-gray-700 hover:bg-gray-50',
                'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md'
              ]"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ pagination.from }}</span>
                to
                <span class="font-medium">{{ pagination.to }}</span>
                of
                <span class="font-medium">{{ pagination.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="changePage(1)"
                  :disabled="pagination.current_page === 1"
                  :class="[
                    pagination.current_page === 1 
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                      : 'bg-white text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 text-sm font-medium'
                  ]"
                >
                  <span class="sr-only">First</span>
                  <i class="fas fa-angle-double-left"></i>
                </button>
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  :class="[
                    pagination.current_page === 1 
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                      : 'bg-white text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium'
                  ]"
                >
                  <span class="sr-only">Previous</span>
                  <i class="fas fa-angle-left"></i>
                </button>
                
                <template v-for="page in paginationRange" :key="page">
                  <button
                    @click="changePage(page)"
                    :class="[
                      page === pagination.current_page 
                        ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' 
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                </template>
                
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  :class="[
                    pagination.current_page === pagination.last_page 
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                      : 'bg-white text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium'
                  ]"
                >
                  <span class="sr-only">Next</span>
                  <i class="fas fa-angle-right"></i>
                </button>
                <button
                  @click="changePage(pagination.last_page)"
                  :disabled="pagination.current_page === pagination.last_page"
                  :class="[
                    pagination.current_page === pagination.last_page 
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                      : 'bg-white text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 text-sm font-medium'
                  ]"
                >
                  <span class="sr-only">Last</span>
                  <i class="fas fa-angle-double-right"></i>
                </button>
              </nav>
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
                    Are you sure you want to delete the event "{{ eventToDelete?.title }}"? This action cannot be undone.
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
  name: 'GroupEvents',
  
  data() {
    return {
      group: {},
      events: [],
      loading: true,
      error: null,
      filter: 'all',
      eventType: '',
      searchQuery: '',
      pagination: {
        current_page: 1,
        from: 1,
        to: 1,
        total: 0,
        last_page: 1,
        per_page: 10
      },
      showDeleteModal: false,
      eventToDelete: null,
      deleting: false
    };
  },
  
  computed: {
    filteredEvents() {
      let filtered = [...this.events];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(event => 
          event.title.toLowerCase().includes(query) || 
          (event.description && event.description.toLowerCase().includes(query)) ||
          (event.location && event.location.toLowerCase().includes(query))
        );
      }
      
      // Apply event type filter
      if (this.eventType) {
        filtered = filtered.filter(event => event.event_type === this.eventType);
      }
      
      return filtered;
    },
    
    paginationRange() {
      const range = [];
      const maxVisiblePages = 5;
      const halfVisible = Math.floor(maxVisiblePages / 2);
      
      let start = Math.max(1, this.pagination.current_page - halfVisible);
      let end = Math.min(this.pagination.last_page, start + maxVisiblePages - 1);
      
      if (end - start + 1 < maxVisiblePages) {
        start = Math.max(1, end - maxVisiblePages + 1);
      }
      
      for (let i = start; i <= end; i++) {
        range.push(i);
      }
      
      return range;
    }
  },
  
  watch: {
    filter() {
      this.fetchEvents();
    },
    
    '$route.params.id'() {
      this.fetchGroup();
      this.fetchEvents();
    }
  },
  
  created() {
    this.fetchGroup();
    this.fetchEvents();
  },
  
  methods: {
    async fetchGroup() {
      this.loading = true;
      
      try {
        const response = await axios.get(`/api/groups/${this.$route.params.id}`);
        
        if (response.data.status === 'success') {
          this.group = response.data.data;
        } else {
          this.error = 'Failed to load group information';
        }
      } catch (error) {
        console.error('Error fetching group:', error);
        this.error = 'An error occurred while loading group information';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchEvents() {
      this.loading = true;
      
      try {
        const params = {
          per_page: this.pagination.per_page,
          page: this.pagination.current_page
        };
        
        if (this.filter !== 'all') {
          params.filter = this.filter;
        }
        
        if (this.eventType) {
          params.type = this.eventType;
        }
        
        const response = await axios.get(`/api/groups/${this.$route.params.id}/events`, { params });
        
        if (response.data.status === 'success') {
          this.events = response.data.data.data;
          this.pagination = {
            current_page: response.data.data.current_page,
            from: response.data.data.from,
            to: response.data.data.to,
            total: response.data.data.total,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page
          };
        } else {
          this.error = 'Failed to load events';
        }
      } catch (error) {
        console.error('Error fetching events:', error);
        this.error = 'An error occurred while loading events';
      } finally {
        this.loading = false;
      }
    },
    
    changePage(page) {
      if (page < 1 || page > this.pagination.last_page) {
        return;
      }
      
      this.pagination.current_page = page;
      this.fetchEvents();
    },
    
    confirmDelete(event) {
      this.eventToDelete = event;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.eventToDelete = null;
      this.showDeleteModal = false;
    },
    
    async deleteEvent() {
      if (!this.eventToDelete) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/api/groups/${this.$route.params.id}/events/${this.eventToDelete.id}`);
        
        if (response.data.status === 'success') {
          // Remove from local list
          this.events = this.events.filter(e => e.id !== this.eventToDelete.id);
          this.showDeleteModal = false;
          this.eventToDelete = null;
        }
      } catch (error) {
        console.error('Error deleting event:', error);
      } finally {
        this.deleting = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString();
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
    
    isUpcoming(event) {
      if (!event.event_date) return false;
      
      const eventDate = new Date(event.event_date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      return eventDate >= today;
    },
    
    capitalizeFirstLetter(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    }
  }
};
</script>
