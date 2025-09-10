<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Create New Pledge</h1>
      <router-link
        to="/pledges"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
      >
        Back to Pledges
      </router-link>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <!-- Error Alert -->
      <div
        v-if="error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Error!</strong>
        <p>{{ error }}</p>
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
            :disabled="loading"
          >
            <span v-if="loading">
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
            <span v-else>Create Pledge</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PledgeCreate',
  data() {
    return {
      pledge: {
        member_id: '',
        campaign_id: '',
        amount: '',
        pledge_date: this.formatDate(new Date()),
        start_date: this.formatDate(new Date()),
        end_date: this.formatDate(new Date(new Date().setFullYear(new Date().getFullYear() + 1))),
        frequency: 'monthly',
        status: 'active',
        notes: ''
      },
      members: [],
      campaigns: [],
      loading: false,
      error: null,
      success: null,
      validationErrors: null,
      dateError: null
    };
  },
  created() {
    this.loadMembers();
    this.loadCampaigns();
  },
  methods: {
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
    
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    async submitForm() {
      // Validate dates
      if (!this.validateDates()) {
        return;
      }
      
      this.loading = true;
      this.error = null;
      this.success = null;
      this.validationErrors = null;
      
      try {
        const response = await axios.post('/api/pledges', this.pledge);
        
        this.success = 'Pledge created successfully!';
        
        // Reset form after successful submission
        setTimeout(() => {
          this.$router.push('/pledges');
        }, 1500);
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'Failed to create pledge';
          
          if (error.response.data.errors) {
            this.validationErrors = error.response.data.errors;
          }
        } else {
          this.error = 'An unexpected error occurred. Please try again.';
        }
        
        console.error('Error creating pledge:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
