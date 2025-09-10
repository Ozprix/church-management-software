<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Pledge Details</h1>
      <div>
        <router-link
          :to="`/pledges/${$route.params.id}/edit`"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md mr-2"
        >
          Edit Pledge
        </router-link>
        <router-link
          to="/pledges"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
        >
          Back to Pledges
        </router-link>
      </div>
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

    <div v-else>
      <!-- Pledge Overview Card -->
      <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">
                Pledge by {{ pledge.member ? pledge.member.first_name + ' ' + pledge.member.last_name : 'Unknown' }}
              </h2>
              <div class="mt-1 flex items-center">
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
                <span
                  class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                >
                  {{ formatFrequency(pledge.frequency) }}
                </span>
                <span
                  v-if="pledge.campaign"
                  class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800"
                >
                  {{ pledge.campaign.name }}
                </span>
                <span
                  v-else
                  class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                >
                  General Fund
                </span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500">Pledge Date</div>
              <div class="text-gray-800">
                {{ formatDate(pledge.pledge_date) }}
              </div>
            </div>
          </div>

          <p v-if="pledge.notes" class="mt-4 text-gray-600">
            {{ pledge.notes }}
          </p>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Pledged Amount</p>
              <p class="text-xl font-bold">${{ formatNumber(pledge.amount) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Donated Amount</p>
              <p class="text-xl font-bold">${{ formatNumber(donatedAmount) }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Remaining</p>
              <p class="text-xl font-bold">${{ formatNumber(remainingAmount) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Date Range</p>
              <p class="text-base font-semibold">
                {{ formatDate(pledge.start_date) }} - {{ formatDate(pledge.end_date) }}
              </p>
            </div>
          </div>

          <div class="mt-4">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Fulfillment Progress</span>
              <span class="text-sm font-medium text-gray-700">{{ Math.round(fulfillmentPercentage) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div
                class="h-2.5 rounded-full"
                :class="fulfillmentColorClass"
                :style="{ width: Math.min(100, fulfillmentPercentage) + '%' }"
              ></div>
            </div>
          </div>

          <div class="mt-6" v-if="nextPaymentDate && pledge.status === 'active'">
            <div class="bg-indigo-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Next Expected Payment</p>
              <p class="text-lg font-semibold">{{ formatDate(nextPaymentDate) }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ daysUntilNextPayment }} days from now
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Member Information -->
      <div v-if="pledge.member" class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Member Information</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-gray-500">Name</p>
              <p class="font-medium">{{ pledge.member.first_name }} {{ pledge.member.last_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="font-medium">{{ pledge.member.email || 'Not provided' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Phone</p>
              <p class="font-medium">{{ pledge.member.phone || 'Not provided' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Member ID</p>
              <p class="font-medium">{{ pledge.member.id }}</p>
            </div>
          </div>
          <div class="mt-4">
            <router-link
              :to="`/members/${pledge.member.id}`"
              class="text-indigo-600 hover:text-indigo-900"
            >
              View Member Profile
            </router-link>
          </div>
        </div>
      </div>

      <!-- Related Donations -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Related Donations</h3>
          <router-link
            :to="`/donations/create?member_id=${pledge.member_id}&pledge_id=${pledge.id}`"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
          >
            Record Payment
          </router-link>
        </div>

        <!-- Empty State for Donations -->
        <div v-if="!pledge.donations || pledge.donations.length === 0" class="p-6 text-center">
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
          <h3 class="mt-2 text-sm font-medium text-gray-900">No donations found</h3>
          <p class="mt-1 text-sm text-gray-500">No donations have been recorded for this pledge yet.</p>
          <div class="mt-6">
            <router-link
              :to="`/donations/create?member_id=${pledge.member_id}&pledge_id=${pledge.id}`"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Record First Payment
            </router-link>
          </div>
        </div>

        <!-- Donation List -->
        <div v-else-if="pledge.donations && pledge.donations.length > 0">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Date
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
                  Payment Method
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Notes
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
              <tr v-for="donation in pledge.donations" :key="donation.id">
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                  {{ formatDate(donation.donation_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">
                  ${{ formatNumber(donation.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                  >
                    {{ donation.payment_method }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                  {{ donation.notes || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link
                    :to="`/donations/${donation.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PledgeDetail',
  data() {
    return {
      pledge: {
        id: null,
        member_id: '',
        campaign_id: '',
        amount: 0,
        pledge_date: '',
        start_date: '',
        end_date: '',
        frequency: '',
        status: '',
        notes: '',
        member: null,
        campaign: null,
        donations: []
      },
      loading: true,
      error: null,
      nextPaymentDate: null
    };
  },
  computed: {
    donatedAmount() {
      if (!this.pledge.donations || this.pledge.donations.length === 0) return 0;
      return this.pledge.donations.reduce((sum, donation) => sum + parseFloat(donation.amount), 0);
    },
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
    },
    daysUntilNextPayment() {
      if (!this.nextPaymentDate) return 0;
      const today = new Date();
      const nextDate = new Date(this.nextPaymentDate);
      const diffTime = Math.abs(nextDate - today);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }
  },
  created() {
    this.loadPledge();
  },
  methods: {
    async loadPledge() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/pledges/${this.$route.params.id}`);
        this.pledge = response.data.data;
        
        // Calculate next payment date
        if (this.pledge.status === 'active' && this.pledge.frequency !== 'one-time') {
          this.calculateNextPaymentDate();
        }
      } catch (error) {
        this.error = 'Failed to load pledge details. Please try again.';
        console.error('Error loading pledge:', error);
      } finally {
        this.loading = false;
      }
    },
    
    calculateNextPaymentDate() {
      if (!this.pledge.donations || this.pledge.donations.length === 0) {
        // If no donations yet, start from the start date
        this.nextPaymentDate = this.pledge.start_date;
        return;
      }
      
      // Sort donations by date (newest first)
      const sortedDonations = [...this.pledge.donations].sort((a, b) => {
        return new Date(b.donation_date) - new Date(a.donation_date);
      });
      
      // Get the most recent donation date
      const lastDonationDate = new Date(sortedDonations[0].donation_date);
      
      // Calculate next payment date based on frequency
      switch (this.pledge.frequency) {
        case 'weekly':
          this.nextPaymentDate = new Date(lastDonationDate.setDate(lastDonationDate.getDate() + 7));
          break;
        case 'biweekly':
          this.nextPaymentDate = new Date(lastDonationDate.setDate(lastDonationDate.getDate() + 14));
          break;
        case 'monthly':
          this.nextPaymentDate = new Date(lastDonationDate.setMonth(lastDonationDate.getMonth() + 1));
          break;
        case 'quarterly':
          this.nextPaymentDate = new Date(lastDonationDate.setMonth(lastDonationDate.getMonth() + 3));
          break;
        case 'annually':
          this.nextPaymentDate = new Date(lastDonationDate.setFullYear(lastDonationDate.getFullYear() + 1));
          break;
        default:
          this.nextPaymentDate = null;
      }
      
      // Format the date as YYYY-MM-DD
      if (this.nextPaymentDate) {
        this.nextPaymentDate = this.formatDateForApi(this.nextPaymentDate);
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    
    formatDateForApi(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
