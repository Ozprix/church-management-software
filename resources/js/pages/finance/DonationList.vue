<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Donations</h1>
      <router-link 
        to="/donations/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add Donation
      </router-link>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="search">
            Search
          </label>
          <input 
            id="search" 
            v-model="filters.search" 
            type="text" 
            placeholder="Search by donor name or notes"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @input="debouncedFetchDonations"
          >
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">
            Date Range
          </label>
          <div class="flex space-x-2">
            <input 
              v-model="filters.start_date" 
              type="date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchDonations"
            >
            <span class="self-center">to</span>
            <input 
              v-model="filters.end_date" 
              type="date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchDonations"
            >
          </div>
        </div>

        <!-- Payment Method -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="payment_method">
            Payment Method
          </label>
          <select 
            id="payment_method" 
            v-model="filters.payment_method" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchDonations"
          >
            <option value="">All Payment Methods</option>
            <option value="cash">Cash</option>
            <option value="check">Check</option>
            <option value="credit_card">Credit Card</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="online">Online</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <!-- Advanced Filters -->
      <div v-if="showAdvancedFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <!-- Amount Range -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">
            Amount Range
          </label>
          <div class="flex space-x-2">
            <input 
              v-model.number="filters.min_amount" 
              type="number" 
              placeholder="Min"
              min="0"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchDonations"
            >
            <span class="self-center">to</span>
            <input 
              v-model.number="filters.max_amount" 
              type="number" 
              placeholder="Max"
              min="0"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchDonations"
            >
          </div>
        </div>

        <!-- Campaign Filter -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="campaign">
            Campaign
          </label>
          <select 
            id="campaign" 
            v-model="filters.campaign_id" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchDonations"
          >
            <option value="">All Campaigns</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }}
            </option>
          </select>
        </div>

        <!-- Recurring Filter -->
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="is_recurring">
            Recurring Status
          </label>
          <select 
            id="is_recurring" 
            v-model="filters.is_recurring" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchDonations"
          >
            <option value="">All Donations</option>
            <option value="true">Recurring Only</option>
            <option value="false">One-time Only</option>
          </select>
        </div>
      </div>

      <div class="flex justify-between mt-4">
        <button 
          @click="showAdvancedFilters = !showAdvancedFilters" 
          class="text-blue-600 hover:text-blue-800"
        >
          {{ showAdvancedFilters ? 'Hide Advanced Filters' : 'Show Advanced Filters' }}
        </button>
        
        <button 
          @click="resetFilters" 
          class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
        >
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Statistics Summary -->
    <div class="bg-blue-50 rounded-lg shadow-md p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
          <p class="text-gray-600 text-sm">Total Donations</p>
          <p class="text-2xl font-bold text-blue-700">{{ donations.meta ? donations.meta.total : 0 }}</p>
        </div>
        <div class="text-center">
          <p class="text-gray-600 text-sm">Total Amount</p>
          <p class="text-2xl font-bold text-blue-700">${{ totalAmount.toFixed(2) }}</p>
        </div>
        <div class="text-center">
          <p class="text-gray-600 text-sm">Average Donation</p>
          <p class="text-2xl font-bold text-blue-700">${{ averageDonation.toFixed(2) }}</p>
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

    <!-- Empty State -->
    <div v-else-if="donations.data && donations.data.length === 0" class="bg-gray-100 rounded-lg p-8 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <p class="text-gray-600 text-lg mb-4">No donations found</p>
      <p class="text-gray-500 mb-4">Try adjusting your filters or add a new donation.</p>
      <router-link 
        to="/donations/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add Donation
      </router-link>
    </div>

    <!-- Donations Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Donor
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Payment Method
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Campaign
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Recurring
            </th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="donation in donations.data" :key="donation.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ donation.member ? `${donation.member.first_name} ${donation.member.last_name}` : 'Unknown' }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ donation.member ? donation.member.email : '' }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">${{ donation.amount.toFixed(2) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ formatDate(donation.donation_date) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                {{ formatPaymentMethod(donation.payment_method) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ donation.campaign ? donation.campaign.name : 'General Fund' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span v-if="donation.is_recurring" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ donation.recurring_frequency || 'Monthly' }}
              </span>
              <span v-else>One-time</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link 
                :to="`/donations/${donation.id}/edit`" 
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </router-link>
              <button 
                @click="confirmDelete(donation)" 
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="donations.meta && donations.meta.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ (donations.meta.current_page - 1) * donations.meta.per_page + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(donations.meta.current_page * donations.meta.per_page, donations.meta.total) }}</span>
              of
              <span class="font-medium">{{ donations.meta.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button
                @click="changePage(donations.meta.current_page - 1)"
                :disabled="donations.meta.current_page === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': donations.meta.current_page === 1 }"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
              <button
                v-for="page in paginationPages"
                :key="page"
                @click="changePage(page)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50"
                :class="page === donations.meta.current_page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'text-gray-500'"
              >
                {{ page }}
              </button>
              <button
                @click="changePage(donations.meta.current_page + 1)"
                :disabled="donations.meta.current_page === donations.meta.last_page"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': donations.meta.current_page === donations.meta.last_page }"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
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
                  Delete Donation
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this donation? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="deleteDonation" 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="deleting"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
            <button 
              @click="showDeleteModal = false" 
              type="button" 
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
import { debounce } from 'lodash';

export default {
  name: 'DonationList',
  data() {
    return {
      donations: {
        data: [],
        meta: null
      },
      campaigns: [],
      filters: {
        search: '',
        start_date: '',
        end_date: '',
        payment_method: '',
        min_amount: null,
        max_amount: null,
        campaign_id: '',
        is_recurring: '',
        page: 1,
        per_page: 10
      },
      showAdvancedFilters: false,
      loading: true,
      error: null,
      showDeleteModal: false,
      donationToDelete: null,
      deleting: false,
      totalAmount: 0
    };
  },
  computed: {
    paginationPages() {
      if (!this.donations.meta) return [];
      
      const currentPage = this.donations.meta.current_page;
      const lastPage = this.donations.meta.last_page;
      
      // Show at most 5 page numbers
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(lastPage, startPage + 4);
      
      if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
      }
      
      return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
    },
    averageDonation() {
      if (!this.donations.data || this.donations.data.length === 0) return 0;
      return this.totalAmount / (this.donations.meta ? this.donations.meta.total : 1);
    }
  },
  created() {
    // Set default date range to current year
    const currentYear = new Date().getFullYear();
    this.filters.start_date = `${currentYear}-01-01`;
    this.filters.end_date = `${currentYear}-12-31`;
    
    this.fetchDonations();
    this.fetchCampaigns();
    
    // Create debounced version of fetchDonations for search input
    this.debouncedFetchDonations = debounce(this.fetchDonations, 300);
  },
  methods: {
    async fetchDonations() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = { ...this.filters };
        
        // Remove empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null) {
            delete params[key];
          }
        });
        
        const response = await axios.get('/donations', { params });
        
        if (response.data.status === 'success') {
          this.donations = {
            data: response.data.data.data,
            meta: response.data.data
          };
          this.totalAmount = response.data.total_amount || 0;
        } else {
          this.error = 'Failed to load donations';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching donations';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchCampaigns() {
      try {
        const response = await axios.get('/campaigns');
        
        if (response.data.status === 'success') {
          this.campaigns = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching campaigns:', error);
      }
    },
    
    changePage(page) {
      if (page < 1 || (this.donations.meta && page > this.donations.meta.last_page)) {
        return;
      }
      
      this.filters.page = page;
      this.fetchDonations();
    },
    
    resetFilters() {
      const currentYear = new Date().getFullYear();
      
      this.filters = {
        search: '',
        start_date: `${currentYear}-01-01`,
        end_date: `${currentYear}-12-31`,
        payment_method: '',
        min_amount: null,
        max_amount: null,
        campaign_id: '',
        is_recurring: '',
        page: 1,
        per_page: 10
      };
      
      this.fetchDonations();
    },
    
    confirmDelete(donation) {
      this.donationToDelete = donation;
      this.showDeleteModal = true;
    },
    
    async deleteDonation() {
      if (!this.donationToDelete) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/donations/${this.donationToDelete.id}`);
        
        if (response.data.status === 'success') {
          this.showDeleteModal = false;
          this.fetchDonations();
        } else {
          this.error = 'Failed to delete donation';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while deleting the donation';
      } finally {
        this.deleting = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    formatPaymentMethod(method) {
      if (!method) return '';
      
      // Convert snake_case to Title Case
      return method
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    }
  }
};
</script>
