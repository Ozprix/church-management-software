<template>
  <div class="member-tax-receipts">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Tax Receipts</h2>
        <div class="flex space-x-2">
          <button 
            @click="openGenerateAnnualModal" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
          >
            Generate Annual Receipt
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
            <select v-model="filters.year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
              <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="filters.type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
              <option value="">All Types</option>
              <option value="single">Single Donation</option>
              <option value="annual">Annual</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="filters.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
              <option value="">All Statuses</option>
              <option value="issued">Issued</option>
              <option value="sent">Sent</option>
              <option value="voided">Voided</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                Loading tax receipts...
              </td>
            </tr>
            <tr v-else-if="receipts.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                No tax receipts found
              </td>
            </tr>
            <tr v-for="receipt in receipts" :key="receipt.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ receipt.receipt_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                  :class="receipt.is_annual ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                  {{ receipt.is_annual ? 'Annual' : 'Single' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(receipt.issue_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${{ formatAmount(receipt.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                  :class="{
                    'bg-green-100 text-green-800': receipt.status === 'issued',
                    'bg-blue-100 text-blue-800': receipt.status === 'sent',
                    'bg-red-100 text-red-800': receipt.status === 'voided'
                  }">
                  {{ capitalizeFirstLetter(receipt.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex space-x-2">
                  <button 
                    @click="downloadReceipt(receipt.id)" 
                    class="text-blue-600 hover:text-blue-900"
                    :disabled="receipt.status === 'voided'"
                    :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' }"
                  >
                    <i class="fas fa-download"></i>
                  </button>
                  <button 
                    @click="sendReceipt(receipt.id)" 
                    class="text-green-600 hover:text-green-900"
                    :disabled="receipt.status === 'voided' || receipt.status === 'sent'"
                    :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' || receipt.status === 'sent' }"
                  >
                    <i class="fas fa-envelope"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Annual Summary -->
      <div v-if="annualSummary.length > 0" class="p-4 border-t border-gray-200">
        <h3 class="text-md font-semibold text-gray-800 mb-3">Annual Donation Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(year, index) in annualSummary" :key="index" class="bg-gray-50 p-3 rounded-md">
            <h4 class="font-medium text-gray-700">{{ year.year }} Tax Year</h4>
            <div class="mt-2 grid grid-cols-2 gap-2">
              <div>
                <p class="text-xs text-gray-500">Total Donations</p>
                <p class="text-sm font-medium">${{ formatAmount(year.total_amount) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Donation Count</p>
                <p class="text-sm font-medium">{{ year.donation_count }}</p>
              </div>
            </div>
            <div class="mt-2">
              <button 
                v-if="!year.has_receipt"
                @click="generateAnnualReceipt(year.year)" 
                class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              >
                Generate Receipt
              </button>
              <div v-else class="text-xs text-green-600">
                <i class="fas fa-check-circle mr-1"></i> Receipt Generated
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Generate Annual Receipt Modal -->
    <div v-if="showGenerateAnnualModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-file-invoice-dollar text-blue-600"></i>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Generate Annual Tax Receipt</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Generate an annual tax receipt for all your donations in the selected year.
                  </p>
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Year</label>
                    <select v-model="annualReceiptYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                      <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                  </div>
                  
                  <div v-if="yearSummary" class="mt-4 bg-gray-50 p-3 rounded-md">
                    <h4 class="font-medium text-gray-700">{{ annualReceiptYear }} Summary</h4>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                      <div>
                        <p class="text-xs text-gray-500">Total Donations</p>
                        <p class="text-sm font-medium">${{ formatAmount(yearSummary.total_amount) }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Donation Count</p>
                        <p class="text-sm font-medium">{{ yearSummary.donation_count }}</p>
                      </div>
                    </div>
                    <div v-if="yearSummary.has_receipt" class="mt-2 p-2 bg-yellow-50 text-yellow-700 text-xs rounded">
                      <i class="fas fa-exclamation-triangle mr-1"></i> You already have an annual receipt for this year. Generating a new one will void the previous receipt.
                    </div>
                    <div v-if="yearSummary.donation_count === 0" class="mt-2 p-2 bg-red-50 text-red-700 text-xs rounded">
                      <i class="fas fa-exclamation-circle mr-1"></i> You don't have any donations for this year.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="generateAnnualReceipt(annualReceiptYear)" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="generatingReceipt || (yearSummary && yearSummary.donation_count === 0)"
              :class="{ 'opacity-50 cursor-not-allowed': generatingReceipt || (yearSummary && yearSummary.donation_count === 0) }"
            >
              <span v-if="generatingReceipt">
                <i class="fas fa-spinner fa-spin mr-2"></i> Generating...
              </span>
              <span v-else>Generate Receipt</span>
            </button>
            <button 
              @click="showGenerateAnnualModal = false" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'MemberTaxReceipts',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    }
  },
  setup(props) {
    const toast = useToast();
    const receipts = ref([]);
    const loading = ref(true);
    const filters = reactive({
      year: new Date().getFullYear(),
      type: '',
      status: ''
    });
    const annualSummary = ref([]);
    const showGenerateAnnualModal = ref(false);
    const annualReceiptYear = ref(new Date().getFullYear());
    const generatingReceipt = ref(false);
    const yearSummary = ref(null);

    // Computed properties
    const availableYears = computed(() => {
      const currentYear = new Date().getFullYear();
      const years = [];
      for (let i = currentYear; i >= currentYear - 5; i--) {
        years.push(i);
      }
      return years;
    });

    // Methods
    const fetchReceipts = async () => {
      loading.value = true;
      try {
        const params = {
          year: filters.year,
          type: filters.type,
          status: filters.status
        };
        
        const response = await axios.get(`/api/members/${props.memberId}/tax-receipts`, { params });
        receipts.value = response.data;
      } catch (error) {
        console.error('Error fetching tax receipts:', error);
        toast.error('Failed to load tax receipts');
      } finally {
        loading.value = false;
      }
    };

    const fetchAnnualSummary = async () => {
      try {
        const response = await axios.get(`/api/members/${props.memberId}/donations/annual-summary`);
        annualSummary.value = response.data;
      } catch (error) {
        console.error('Error fetching annual summary:', error);
      }
    };

    const fetchYearSummary = async (year) => {
      try {
        const response = await axios.get(`/api/members/${props.memberId}/donations/year-summary`, {
          params: { year }
        });
        yearSummary.value = response.data;
      } catch (error) {
        console.error('Error fetching year summary:', error);
        yearSummary.value = null;
      }
    };

    const downloadReceipt = async (receiptId) => {
      try {
        const response = await axios.get(`/api/tax-receipts/${receiptId}/download`, {
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `tax-receipt-${receiptId}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        toast.success('Tax receipt downloaded successfully');
      } catch (error) {
        console.error('Error downloading tax receipt:', error);
        toast.error('Failed to download tax receipt');
      }
    };

    const sendReceipt = async (receiptId) => {
      try {
        await axios.post(`/api/tax-receipts/${receiptId}/send`);
        toast.success('Tax receipt sent by email successfully');
        fetchReceipts();
      } catch (error) {
        console.error('Error sending tax receipt:', error);
        toast.error('Failed to send tax receipt by email');
      }
    };

    const openGenerateAnnualModal = () => {
      annualReceiptYear.value = new Date().getFullYear() - 1;
      fetchYearSummary(annualReceiptYear.value);
      showGenerateAnnualModal.value = true;
    };

    const generateAnnualReceipt = async (year) => {
      generatingReceipt.value = true;
      try {
        await axios.post(`/api/members/${props.memberId}/annual-tax-receipt`, {
          year: year
        });
        
        toast.success(`Annual tax receipt for ${year} generated successfully`);
        showGenerateAnnualModal.value = false;
        
        // Update filters to show the newly generated receipt
        filters.year = year;
        filters.type = 'annual';
        fetchReceipts();
        fetchAnnualSummary();
      } catch (error) {
        console.error('Error generating annual tax receipt:', error);
        toast.error('Failed to generate annual tax receipt');
      } finally {
        generatingReceipt.value = false;
      }
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };

    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2);
    };

    const capitalizeFirstLetter = (string) => {
      return string.charAt(0).toUpperCase() + string.slice(1);
    };

    // Watch for filter changes
    watch(filters, () => {
      fetchReceipts();
    }, { deep: true });

    watch(annualReceiptYear, () => {
      fetchYearSummary(annualReceiptYear.value);
    });

    // Lifecycle hooks
    onMounted(() => {
      fetchReceipts();
      fetchAnnualSummary();
    });

    return {
      receipts,
      loading,
      filters,
      annualSummary,
      availableYears,
      showGenerateAnnualModal,
      annualReceiptYear,
      generatingReceipt,
      yearSummary,
      fetchReceipts,
      downloadReceipt,
      sendReceipt,
      openGenerateAnnualModal,
      generateAnnualReceipt,
      formatDate,
      formatAmount,
      capitalizeFirstLetter
    };
  }
};
</script>
