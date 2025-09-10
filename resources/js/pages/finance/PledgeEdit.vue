<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Edit Pledge</h1>
      <router-link
        to="/pledges"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
      >
        Back to Pledges
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading pledge details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <div v-else class="bg-white shadow rounded-lg p-6">
      <!-- Error Alert -->
      <div
        v-if="formError"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Error!</strong>
        <p>{{ formError }}</p>
        <ul v-if="validationErrors" class="list-disc pl-5 mt-2">
          <li v-for="(errors, field) in validationErrors" :key="field">
            {{ field }}: {{ errors.join(', ') }}
          </li>
        </ul>
      </div>

      <!-- Success Alert -->
      <div
        v-if="success"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Success!</strong>
        <p>{{ success }}</p>
      </div>

      <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Member -->
          <div>
            <label for="member_id" class="block text-sm font-medium text-gray-700 mb-1">
              Member <span class="text-red-600">*</span>
            </label>
            <select
              id="member_id"
              v-model="pledge.member_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="" disabled>Select a member</option>
              <option v-for="member in members" :key="member.id" :value="member.id">
                {{ member.first_name }} {{ member.last_name }}
              </option>
            </select>
          </div>

          <!-- Campaign -->
          <div>
            <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-1">
              Campaign
            </label>
            <select
              id="campaign_id"
              v-model="pledge.campaign_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">General Fund</option>
              <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                {{ campaign.name }}
              </option>
            </select>
          </div>

          <!-- Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
              Pledge Amount <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">$</span>
              </div>
              <input
                type="number"
                id="amount"
                v-model="pledge.amount"
                step="0.01"
                min="0.01"
                class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>
          </div>

          <!-- Frequency -->
          <div>
            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">
              Frequency <span class="text-red-600">*</span>
            </label>
            <select
              id="frequency"
              v-model="pledge.frequency"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="one-time">One Time</option>
              <option value="weekly">Weekly</option>
              <option value="biweekly">Bi-weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="annually">Annually</option>
            </select>
          </div>

          <!-- Pledge Date -->
          <div>
            <label for="pledge_date" class="block text-sm font-medium text-gray-700 mb-1">
              Pledge Date <span class="text-red-600">*</span>
            </label>
            <input
              type="date"
              id="pledge_date"
              v-model="pledge.pledge_date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
              Status <span class="text-red-600">*</span>
            </label>
            <select
              id="status"
              v-model="pledge.status"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
              Start Date <span class="text-red-600">*</span>
            </label>
            <input
              type="date"
              id="start_date"
              v-model="pledge.start_date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <!-- End Date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
              End Date <span class="text-red-600">*</span>
            </label>
            <input
              type="date"
              id="end_date"
              v-model="pledge.end_date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
              :min="pledge.start_date"
            />
            <p v-if="dateError" class="text-red-500 text-xs mt-1">{{ dateError }}</p>
          </div>
        </div>

        <!-- Pledge Fulfillment -->
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-700 mb-2">Pledge Fulfillment</h3>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-sm text-gray-500">Donated:</span>
                <span class="ml-2 font-medium">${{ formatNumber(donatedAmount) }}</span>
              </div>
              <div>
                <span class="text-sm text-gray-500">Remaining:</span>
                <span class="ml-2 font-medium">${{ formatNumber(remainingAmount) }}</span>
              </div>
              <div>
                <span class="text-sm text-gray-500">Fulfillment:</span>
                <span class="ml-2 font-medium">{{ Math.round(fulfillmentPercentage) }}%</span>
              </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div
                class="h-2.5 rounded-full"
                :class="fulfillmentColorClass"
                :style="{ width: Math.min(100, fulfillmentPercentage) + '%' }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="mt-6">
          <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
            Notes
          </label>
          <textarea
            id="notes"
            v-model="pledge.notes"
            rows="4"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            @click="$router.push('/pledges')"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md mr-2"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
            :disabled="submitting"
          >
            <span v-if="submitting">
              <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Saving...
            </span>
            <span v-else>Update Pledge</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PledgeEdit',
  data() {
    return {
      pledge: {
        id: null,
        member_id: '',
        campaign_id: '',
        amount: '',
        pledge_date: '',
        start_date: '',
        end_date: '',
        frequency: '',
        status: '',
        notes: ''
      },
      members: [],
      campaigns: [],
      donations: [],
      donatedAmount: 0,
      loading: true,
      submitting: false,
      error: null,
      formError: null,
      success: null,
      validationErrors: null,
      dateError: null
    };
  },
  computed: {
    remainingAmount() {
      return this.pledge.amount - this.donatedAmount;
    },
    fulfillmentPercentage() {
      if (!this.pledge.amount || this.pledge.amount <= 0) return 0;
      return (this.donatedAmount / this.pledge.amount) * 100;
    },
    fulfillmentColorClass() {
      if (this.fulfillmentPercentage >= 100) return 'bg-green-600';
      if (this.fulfillmentPercentage >= 75) return 'bg-blue-600';
      if (this.fulfillmentPercentage >= 50) return 'bg-yellow-600';
      return 'bg-red-600';
    }
  },
  created() {
    this.loadPledge();
    this.loadMembers();
    this.loadCampaigns();
  },
  methods: {
    async loadPledge() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/pledges/${this.$route.params.id}`);
        this.pledge = response.data.data;
        this.donations = this.pledge.donations || [];
        this.donatedAmount = this.donations.reduce((sum, donation) => sum + parseFloat(donation.amount), 0);
        
        // Format dates for input fields
        if (this.pledge.pledge_date) {
          this.pledge.pledge_date = this.formatDateForInput(new Date(this.pledge.pledge_date));
        }
        
        if (this.pledge.start_date) {
          this.pledge.start_date = this.formatDateForInput(new Date(this.pledge.start_date));
        }
        
        if (this.pledge.end_date) {
          this.pledge.end_date = this.formatDateForInput(new Date(this.pledge.end_date));
        }
      } catch (error) {
        this.error = 'Failed to load pledge details. Please try again.';
        console.error('Error loading pledge:', error);
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
    
    validateDates() {
      const startDate = new Date(this.pledge.start_date);
      const endDate = new Date(this.pledge.end_date);
      
      if (endDate < startDate) {
        this.dateError = 'End date must be after start date';
        return false;
      }
      
      this.dateError = null;
      return true;
    },
    
    formatDateForInput(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    async submitForm() {
      // Validate dates
      if (!this.validateDates()) {
        return;
      }
      
      this.submitting = true;
      this.formError = null;
      this.success = null;
      this.validationErrors = null;
      
      try {
        const response = await axios.put(`/api/pledges/${this.pledge.id}`, this.pledge);
        
        this.success = 'Pledge updated successfully!';
        
        // Redirect after successful update
        setTimeout(() => {
          this.$router.push('/pledges');
        }, 1500);
      } catch (error) {
        if (error.response && error.response.data) {
          this.formError = error.response.data.message || 'Failed to update pledge';
          
          if (error.response.data.errors) {
            this.validationErrors = error.response.data.errors;
          }
        } else {
          this.formError = 'An unexpected error occurred. Please try again.';
        }
        
        console.error('Error updating pledge:', error);
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>
