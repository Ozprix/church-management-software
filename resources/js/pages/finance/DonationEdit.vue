<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link 
        to="/donations" 
        class="text-blue-600 hover:text-blue-800 mr-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Donations
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Edit Donation</h1>
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
      <form @submit.prevent="updateDonation">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Donor Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Donor Information</h2>
          </div>

          <!-- Member Selection -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="member_id">
              Select Member *
            </label>
            <div class="relative">
              <input 
                type="text" 
                v-model="memberSearch" 
                @input="searchMembers" 
                placeholder="Search by name or email" 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              >
              <div v-if="showMemberResults && filteredMembers.length > 0" class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-auto">
                <ul class="py-1">
                  <li 
                    v-for="member in filteredMembers" 
                    :key="member.id" 
                    @click="selectMember(member)"
                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                  >
                    {{ member.first_name }} {{ member.last_name }} ({{ member.email }})
                  </li>
                </ul>
              </div>
            </div>
            <div v-if="selectedMember" class="mt-2 p-2 bg-blue-50 rounded flex justify-between items-center">
              <div>
                <span class="font-medium">{{ selectedMember.first_name }} {{ selectedMember.last_name }}</span>
                <span class="text-sm text-gray-600 ml-2">{{ selectedMember.email }}</span>
              </div>
              <button 
                type="button" 
                @click="clearSelectedMember" 
                class="text-red-600 hover:text-red-800"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
            <p v-if="validationErrors.member_id" class="text-red-500 text-xs mt-1">
              {{ validationErrors.member_id[0] }}
            </p>
          </div>

          <!-- Donation Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-4">Donation Information</h2>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
              Amount *
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600">$</span>
              <input 
                id="amount" 
                v-model="donation.amount" 
                type="number" 
                min="0.01" 
                step="0.01" 
                required
                class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              >
            </div>
            <p v-if="validationErrors.amount" class="text-red-500 text-xs mt-1">
              {{ validationErrors.amount[0] }}
            </p>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="payment_method">
              Payment Method *
            </label>
            <select 
              id="payment_method" 
              v-model="donation.payment_method" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">Select Payment Method</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="credit_card">Credit Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="online">Online</option>
              <option value="other">Other</option>
            </select>
            <p v-if="validationErrors.payment_method" class="text-red-500 text-xs mt-1">
              {{ validationErrors.payment_method[0] }}
            </p>
          </div>

          <!-- Transaction ID -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="transaction_id">
              Transaction ID
            </label>
            <input 
              id="transaction_id" 
              v-model="donation.transaction_id" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.transaction_id" class="text-red-500 text-xs mt-1">
              {{ validationErrors.transaction_id[0] }}
            </p>
          </div>

          <!-- Donation Date -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="donation_date">
              Donation Date *
            </label>
            <input 
              id="donation_date" 
              v-model="donation.donation_date" 
              type="date" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.donation_date" class="text-red-500 text-xs mt-1">
              {{ validationErrors.donation_date[0] }}
            </p>
          </div>

          <!-- Campaign -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="campaign_id">
              Campaign
            </label>
            <select 
              id="campaign_id" 
              v-model="donation.campaign_id" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">General Fund</option>
              <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                {{ campaign.name }}
              </option>
            </select>
            <p v-if="validationErrors.campaign_id" class="text-red-500 text-xs mt-1">
              {{ validationErrors.campaign_id[0] }}
            </p>
          </div>

          <!-- Recurring Donation -->
          <div class="md:col-span-2">
            <div class="flex items-center">
              <input 
                id="is_recurring" 
                v-model="donation.is_recurring" 
                type="checkbox" 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="is_recurring" class="ml-2 block text-sm text-gray-700">
                This is a recurring donation
              </label>
            </div>
          </div>

          <!-- Recurring Frequency -->
          <div v-if="donation.is_recurring">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="recurring_frequency">
              Recurring Frequency
            </label>
            <select 
              id="recurring_frequency" 
              v-model="donation.recurring_frequency" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="annually">Annually</option>
            </select>
            <p v-if="validationErrors.recurring_frequency" class="text-red-500 text-xs mt-1">
              {{ validationErrors.recurring_frequency[0] }}
            </p>
          </div>

          <!-- Notes -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
              Notes
            </label>
            <textarea 
              id="notes" 
              v-model="donation.notes" 
              rows="3" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            ></textarea>
            <p v-if="validationErrors.notes" class="text-red-500 text-xs mt-1">
              {{ validationErrors.notes[0] }}
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end">
          <button 
            type="button" 
            @click="$router.push('/donations')" 
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
            <span v-else>Update Donation</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { debounce } from 'lodash';

export default {
  name: 'DonationEdit',
  data() {
    return {
      donationId: this.$route.params.id,
      donation: {
        member_id: '',
        amount: '',
        payment_method: '',
        transaction_id: '',
        campaign_id: '',
        donation_date: '',
        is_recurring: false,
        recurring_frequency: 'monthly',
        notes: ''
      },
      memberSearch: '',
      members: [],
      filteredMembers: [],
      showMemberResults: false,
      selectedMember: null,
      campaigns: [],
      loading: true,
      saving: false,
      error: null,
      validationErrors: {}
    };
  },
  created() {
    this.fetchDonation();
    this.fetchCampaigns();
    this.debouncedSearchMembers = debounce(this.fetchMembers, 300);
  },
  methods: {
    async fetchDonation() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/donations/${this.donationId}`);
        
        if (response.data.status === 'success') {
          const donationData = response.data.data;
          
          // Format date for date input
          if (donationData.donation_date) {
            donationData.donation_date = this.formatDateForInput(donationData.donation_date);
          }
          
          this.donation = donationData;
          
          // Set selected member
          if (donationData.member) {
            this.selectedMember = donationData.member;
            this.memberSearch = `${donationData.member.first_name} ${donationData.member.last_name}`;
          }
        } else {
          this.error = 'Failed to load donation';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching the donation';
      } finally {
        this.loading = false;
      }
    },
    
    formatDateForInput(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      return date.toISOString().slice(0, 10);
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
    
    searchMembers() {
      if (this.memberSearch.length < 2) {
        this.filteredMembers = [];
        this.showMemberResults = false;
        return;
      }
      
      this.showMemberResults = true;
      this.debouncedSearchMembers();
    },
    
    async fetchMembers() {
      if (this.memberSearch.length < 2) return;
      
      try {
        const response = await axios.get('/members', {
          params: { search: this.memberSearch }
        });
        
        if (response.data.status === 'success') {
          this.members = response.data.data.data;
          this.filteredMembers = this.members;
        }
      } catch (error) {
        console.error('Error fetching members:', error);
      }
    },
    
    selectMember(member) {
      this.selectedMember = member;
      this.donation.member_id = member.id;
      this.memberSearch = `${member.first_name} ${member.last_name}`;
      this.showMemberResults = false;
    },
    
    clearSelectedMember() {
      this.selectedMember = null;
      this.donation.member_id = '';
      this.memberSearch = '';
    },
    
    async updateDonation() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        const response = await axios.put(`/donations/${this.donationId}`, this.donation);
        
        if (response.data.status === 'success') {
          // Redirect to the donation list with a success message
          this.$router.push({
            path: '/donations',
            query: { 
              message: 'Donation updated successfully',
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
          this.error = error.response?.data?.message || 'An error occurred while updating the donation.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
