<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Pledge Management</h1>
      <router-link
        to="/pledges/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
      >
        Create New Pledge
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
          <select
            v-model="filters.member_id"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadPledges"
          >
            <option value="">All Members</option>
            <option v-for="member in members" :key="member.id" :value="member.id">
              {{ member.first_name }} {{ member.last_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Campaign</label>
          <select
            v-model="filters.campaign_id"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadPledges"
          >
            <option value="">All Campaigns</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadPledges"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <div class="flex space-x-2">
            <input
              type="date"
              v-model="filters.start_date"
              class="w-1/2 border border-gray-300 rounded-md px-3 py-2"
            />
            <input
              type="date"
              v-model="filters.end_date"
              class="w-1/2 border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button
          @click="loadPledges"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2"
        >
          Apply Filters
        </button>
        <button
          @click="resetFilters"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Pledge Statistics Card -->
    <div class="bg-white shadow rounded-lg p-4 mb-6" v-if="statistics">
      <h2 class="text-xl font-semibold mb-4">Pledge Statistics</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Total Pledges</p>
          <p class="text-xl font-bold">{{ statistics.total_pledges }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Total Pledged Amount</p>
          <p class="text-xl font-bold">${{ formatNumber(statistics.total_pledged_amount) }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Active Pledges</p>
          <p class="text-xl font-bold">{{ statistics.active_pledges }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Completed Pledges</p>
          <p class="text-xl font-bold">{{ statistics.completed_pledges }}</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading pledges...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="pledges.length === 0" class="text-center py-8">
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
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No pledges found</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new pledge.</p>
      <div class="mt-6">
        <router-link
          to="/pledges/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          Create New Pledge
        </router-link>
      </div>
    </div>

    <!-- Pledge List -->
    <div v-else class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Member
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Campaign
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Amount
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Frequency
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Date Range
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="pledge in pledges" :key="pledge.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="font-medium text-gray-900">
                {{ pledge.member ? pledge.member.first_name + ' ' + pledge.member.last_name : 'Unknown' }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
              >
                {{ pledge.campaign ? pledge.campaign.name : 'General Fund' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
              ${{ formatNumber(pledge.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
              {{ formatFrequency(pledge.frequency) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(pledge.start_date) }} - {{ formatDate(pledge.end_date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="{
                  'bg-green-100 text-green-800': pledge.status === 'active',
                  'bg-blue-100 text-blue-800': pledge.status === 'completed',
                  'bg-red-100 text-red-800': pledge.status === 'cancelled'
                }"
              >
                {{ pledge.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <router-link
                :to="`/pledges/${pledge.id}`"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                View
              </router-link>
              <router-link
                :to="`/pledges/${pledge.id}/edit`"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </router-link>
              <button
                @click="confirmDelete(pledge)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
            to
            <span class="font-medium">
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
            </span>
            of
            <span class="font-medium">{{ pagination.total }}</span>
            results
          </div>
          <div>
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
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-red-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Pledge</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this pledge? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deletePledge"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button
              @click="showDeleteModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
  name: 'PledgeList',
  data() {
    return {
      pledges: [],
      members: [],
      campaigns: [],
      statistics: null,
      loading: true,
      error: null,
      filters: {
        member_id: '',
        campaign_id: '',
        status: '',
        start_date: '',
        end_date: '',
        sort_by: 'pledge_date',
        sort_dir: 'desc'
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      showDeleteModal: false,
      pledgeToDelete: null
    };
  },
  created() {
    this.loadMembers();
    this.loadCampaigns();
    this.loadStatistics();
    this.loadPledges();
  },
  methods: {
    async loadPledges() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          ...this.filters
        };
        
        const response = await axios.get('/api/pledges', { params });
        
        this.pledges = response.data.data.data;
        this.pagination = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          per_page: response.data.data.per_page,
          total: response.data.data.total
        };
      } catch (error) {
        this.error = 'Failed to load pledges. Please try again.';
        console.error('Error loading pledges:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async loadMembers() {
      try {
        const response = await axios.get('/api/members');
        this.members = response.data.data.data;
      } catch (error) {
        console.error('Error loading members:', error);
      }
    },
    
    async loadCampaigns() {
      try {
        const response = await axios.get('/api/campaigns');
        this.campaigns = response.data.data.data;
      } catch (error) {
        console.error('Error loading campaigns:', error);
      }
    },
    
    async loadStatistics() {
      try {
        const response = await axios.get('/api/pledges/statistics');
        this.statistics = response.data.data;
      } catch (error) {
        console.error('Error loading pledge statistics:', error);
      }
    },
    
    resetFilters() {
      this.filters = {
        member_id: '',
        campaign_id: '',
        status: '',
        start_date: '',
        end_date: '',
        sort_by: 'pledge_date',
        sort_dir: 'desc'
      };
      this.loadPledges();
    },
    
    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.loadPledges();
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
    
    confirmDelete(pledge) {
      this.pledgeToDelete = pledge;
      this.showDeleteModal = true;
    },
    
    async deletePledge() {
      if (!this.pledgeToDelete) return;
      
      try {
        await axios.delete(`/api/pledges/${this.pledgeToDelete.id}`);
        this.showDeleteModal = false;
        this.pledgeToDelete = null;
        this.loadPledges();
        this.loadStatistics();
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.error = error.response.data.message;
        } else {
          this.error = 'Failed to delete pledge. Please try again.';
        }
        console.error('Error deleting pledge:', error);
      }
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    
    formatFrequency(frequency) {
      switch (frequency) {
        case 'one-time':
          return 'One Time';
        case 'weekly':
          return 'Weekly';
        case 'biweekly':
          return 'Bi-weekly';
        case 'monthly':
          return 'Monthly';
        case 'quarterly':
          return 'Quarterly';
        case 'annually':
          return 'Annually';
        default:
          return frequency;
      }
    }
  }
};
</script>
