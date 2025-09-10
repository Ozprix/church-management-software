<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
      <router-link
        to="/reports/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
      >
        Create New Report
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select
            v-model="filters.type"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadReports"
          >
            <option value="">All Types</option>
            <option v-for="type in reportTypes" :key="type.id" :value="type.id">
              {{ type.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
          <div class="flex space-x-2">
            <div class="flex items-center">
              <input
                type="checkbox"
                id="favorites"
                v-model="filters.favorites"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                @change="loadReports"
              />
              <label for="favorites" class="ml-2 block text-sm text-gray-900">
                Favorites
              </label>
            </div>
            <div class="flex items-center">
              <input
                type="checkbox"
                id="scheduled"
                v-model="filters.scheduled"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                @change="loadReports"
              />
              <label for="scheduled" class="ml-2 block text-sm text-gray-900">
                Scheduled
              </label>
            </div>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <div class="flex space-x-2">
            <select
              v-model="filters.sort_by"
              class="w-2/3 border border-gray-300 rounded-md px-3 py-2"
              @change="loadReports"
            >
              <option value="created_at">Created Date</option>
              <option value="name">Name</option>
              <option value="type">Type</option>
              <option value="last_generated_at">Last Generated</option>
            </select>
            <select
              v-model="filters.sort_dir"
              class="w-1/3 border border-gray-300 rounded-md px-3 py-2"
              @change="loadReports"
            >
              <option value="desc">Desc</option>
              <option value="asc">Asc</option>
            </select>
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button
          @click="resetFilters"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
        >
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading reports...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="reports.length === 0" class="text-center py-8">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No reports found</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new report.</p>
      <div class="mt-6">
        <router-link
          to="/reports/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          Create New Report
        </router-link>
      </div>
    </div>

    <!-- Report List -->
    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="report in reports"
          :key="report.id"
          class="bg-white shadow rounded-lg overflow-hidden"
        >
          <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 truncate">{{ report.name }}</h3>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getTypeClass(report.type)"
              >
                {{ getTypeDisplay(report.type) }}
              </span>
            </div>
            <button
              @click="handleToggleFavorite(report)"
              class="text-gray-500 hover:text-yellow-500 mr-2"
              :class="{ 'text-yellow-500': report.is_favorite }"
            >
              <svg
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                ></path>
              </svg>
            </button>
          </div>
          <div class="p-4">
            <div class="mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Last Generated:</span>
                <span class="text-gray-900">{{ report.last_generated ? report.last_generated : 'Never' }}</span>
              </div>
              <div class="flex justify-between text-sm mt-1">
                <span class="text-gray-500">Format:</span>
                <span class="text-gray-900">{{ report.output_format.toUpperCase() }}</span>
              </div>
              <div class="flex justify-between text-sm mt-1" v-if="report.is_scheduled">
                <span class="text-gray-500">Schedule:</span>
                <span class="text-gray-900">{{ getScheduleDisplay(report.schedule_frequency) }}</span>
              </div>
            </div>
            <div class="flex justify-between mt-4">
              <router-link
                :to="`/reports/${report.id}`"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                View Details
              </router-link>
              <button
                @click="generateReport(report)"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm"
              >
                Generate
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
          >
            Previous
          </button>
          <button
            v-for="page in getPageNumbers()"
            :key="page"
            @click="goToPage(page)"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50"
            :class="{
              'z-10 bg-blue-50 border-blue-500 text-blue-600': page === pagination.current_page,
              'text-gray-500': page !== pagination.current_page
            }"
          >
            {{ page }}
          </button>
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            :class="{
              'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page
            }"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import ReportNavigation from '../../components/reports/ReportNavigation.vue';

export default {
  name: 'ReportList',
  data() {
    return {
      filters: {
        type: '',
        favorites: false,
        scheduled: false,
        sort_by: 'created_at',
        sort_dir: 'desc'
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
      }
    };
  },
  computed: {
    ...mapState('reports', ['reports', 'loading', 'error']),
    ...mapGetters('reports', ['getReportTypes']),
    reportTypes() {
      return this.getReportTypes || [];
    }
  },
  created() {
    this.fetchReportTypes();
    this.loadReports();
  },
  methods: {
    ...mapActions('reports', [
      'fetchReports', 
      'fetchReportTypes', 
      'toggleFavorite'
    ]),
    
    async loadReports() {
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          type: this.filters.type,
          favorites: this.filters.favorites ? 'true' : '',
          scheduled: this.filters.scheduled ? 'true' : '',
          sort_by: this.filters.sort_by,
          sort_dir: this.filters.sort_dir
        };
        
        const response = await this.fetchReports(params);
        
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        };
      } catch (error) {
        console.error('Error loading reports:', error);
      }
    },
    
    resetFilters() {
      this.filters = {
        type: '',
        favorites: false,
        scheduled: false,
        sort_by: 'created_at',
        sort_dir: 'desc'
      };
      this.loadReports();
    },
    
    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.loadReports();
    },
    
    getPageNumbers() {
      const pages = [];
      const totalPages = this.pagination.last_page;
      const currentPage = this.pagination.current_page;
      
      // Show at most 5 page numbers
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, startPage + 4);
      
      // Adjust if we're near the end
      if (endPage - startPage < 4 && startPage > 1) {
        startPage = Math.max(1, endPage - 4);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      return pages;
    },
    
    async handleToggleFavorite(report) {
      try {
        await this.toggleFavorite(report.id);
        // Refresh the reports list
        this.loadReports();
      } catch (error) {
        console.error('Error toggling favorite:', error);
      }
    },
    
    generateReport(report) {
      // Redirect to report view page with generate parameter
      this.$router.push(`/reports/${report.id}?generate=true`);
    },
    
    getTypeDisplay(type) {
      const typeObj = this.reportTypes.find(t => t.id === type);
      return typeObj ? typeObj.name : type.charAt(0).toUpperCase() + type.slice(1);
    },
    
    getTypeClass(type) {
      const classes = {
        financial: 'bg-green-100 text-green-800',
        donation: 'bg-blue-100 text-blue-800',
        expense: 'bg-red-100 text-red-800',
        attendance: 'bg-purple-100 text-purple-800',
        membership: 'bg-indigo-100 text-indigo-800',
        pledge: 'bg-yellow-100 text-yellow-800',
        campaign: 'bg-pink-100 text-pink-800',
        custom: 'bg-gray-100 text-gray-800'
      };
      
      return classes[type] || 'bg-gray-100 text-gray-800';
    },
    
    getScheduleDisplay(frequency) {
      const displays = {
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        quarterly: 'Quarterly'
      };
      
      return displays[frequency] || frequency;
    }
  }
};
</script>
