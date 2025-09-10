<template>
  <div class="tax-receipt-list">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Tax Receipts</h2>
        <div class="flex space-x-2">
          <button 
            @click="openGenerateAllAnnualModal" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
          >
            Generate Annual Receipts
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input 
              type="text" 
              v-model="filters.search" 
              placeholder="Receipt #, Member name..." 
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
            >
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                Loading tax receipts...
              </td>
            </tr>
            <tr v-else-if="receipts.length === 0">
              <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                No tax receipts found
              </td>
            </tr>
            <tr v-for="receipt in receipts" :key="receipt.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ receipt.receipt_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ receipt.member.first_name }} {{ receipt.member.last_name }}
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
                  <button 
                    @click="confirmVoidReceipt(receipt.id)" 
                    class="text-red-600 hover:text-red-900"
                    :disabled="receipt.status === 'voided'"
                    :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' }"
                  >
                    <i class="fas fa-ban"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6 flex items-center justify-between">
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
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
              >
                <span class="sr-only">Previous</span>
                <i class="fas fa-chevron-left"></i>
              </button>
              <button
                v-for="page in paginationPages"
                :key="page"
                @click="changePage(page)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50"
                :class="{ 'bg-blue-50 text-blue-600': page === pagination.current_page, 'text-gray-500': page !== pagination.current_page }"
              >
                {{ page }}
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
              >
                <span class="sr-only">Next</span>
                <i class="fas fa-chevron-right"></i>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Generate All Annual Receipts Modal -->
    <div v-if="showGenerateAllAnnualModal" class="fixed inset-0 z-10 overflow-y-auto">
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
                <h3 class="text-lg leading-6 font-medium text-gray-900">Generate Annual Tax Receipts</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    This will generate annual tax receipts for all members who made donations in the selected year.
                  </p>
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Year</label>
                    <select v-model="annualReceiptYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                      <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="generateAllAnnualReceipts" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="generatingReceipts"
            >
              <span v-if="generatingReceipts">
                <i class="fas fa-spinner fa-spin mr-2"></i> Generating...
              </span>
              <span v-else>Generate Receipts</span>
            </button>
            <button 
              @click="showGenerateAllAnnualModal = false" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Void Receipt Confirmation Modal -->
    <div v-if="showVoidModal" class="fixed inset-0 z-10 overflow-y-auto">
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
                <h3 class="text-lg leading-6 font-medium text-gray-900">Void Tax Receipt</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to void this tax receipt? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="voidReceipt" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Void Receipt
            </button>
            <button 
              @click="showVoidModal = false" 
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
  name: 'TaxReceiptList',
  setup() {
    const toast = useToast();
    const receipts = ref([]);
    const loading = ref(true);
    const filters = reactive({
      year: new Date().getFullYear(),
      type: '',
      status: '',
      search: '',
    });
    const pagination = reactive({
      current_page: 1,
      from: 0,
      to: 0,
      total: 0,
      last_page: 1,
      per_page: 10,
    });
    const showGenerateAllAnnualModal = ref(false);
    const showVoidModal = ref(false);
    const selectedReceiptId = ref(null);
    const annualReceiptYear = ref(new Date().getFullYear() - 1);
    const generatingReceipts = ref(false);

    // Computed properties
    const availableYears = computed(() => {
      const currentYear = new Date().getFullYear();
      const years = [];
      for (let i = currentYear; i >= currentYear - 5; i--) {
        years.push(i);
      }
      return years;
    });

    const paginationPages = computed(() => {
      const pages = [];
      const maxPages = 5;
      const halfMaxPages = Math.floor(maxPages / 2);
      
      let startPage = Math.max(1, pagination.current_page - halfMaxPages);
      let endPage = Math.min(pagination.last_page, startPage + maxPages - 1);
      
      if (endPage - startPage + 1 < maxPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      return pages;
    });

    // Methods
    const fetchReceipts = async () => {
      loading.value = true;
      try {
        const params = {
          page: pagination.current_page,
          per_page: pagination.per_page,
          year: filters.year,
          type: filters.type,
          status: filters.status,
          search: filters.search,
        };
        
        const response = await axios.get('/api/tax-receipts', { params });
        receipts.value = response.data.data;
        
        pagination.current_page = response.data.current_page;
        pagination.from = response.data.from;
        pagination.to = response.data.to;
        pagination.total = response.data.total;
        pagination.last_page = response.data.last_page;
        pagination.per_page = response.data.per_page;
      } catch (error) {
        console.error('Error fetching tax receipts:', error);
        toast.error('Failed to load tax receipts');
      } finally {
        loading.value = false;
      }
    };

    const changePage = (page) => {
      if (page < 1 || page > pagination.last_page) return;
      pagination.current_page = page;
      fetchReceipts();
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

    const confirmVoidReceipt = (receiptId) => {
      selectedReceiptId.value = receiptId;
      showVoidModal.value = true;
    };

    const voidReceipt = async () => {
      try {
        await axios.post(`/api/tax-receipts/${selectedReceiptId.value}/void`);
        toast.success('Tax receipt voided successfully');
        showVoidModal.value = false;
        fetchReceipts();
      } catch (error) {
        console.error('Error voiding tax receipt:', error);
        toast.error('Failed to void tax receipt');
      }
    };

    const openGenerateAllAnnualModal = () => {
      annualReceiptYear.value = new Date().getFullYear() - 1;
      showGenerateAllAnnualModal.value = true;
    };

    const generateAllAnnualReceipts = async () => {
      generatingReceipts.value = true;
      try {
        await axios.post('/api/tax-receipts/generate-all-annual', {
          year: annualReceiptYear.value
        });
        
        toast.success(`Annual tax receipts for ${annualReceiptYear.value} generated successfully`);
        showGenerateAllAnnualModal.value = false;
        
        // Update filters to show the newly generated receipts
        filters.year = annualReceiptYear.value;
        filters.type = 'annual';
        pagination.current_page = 1;
        fetchReceipts();
      } catch (error) {
        console.error('Error generating annual tax receipts:', error);
        toast.error('Failed to generate annual tax receipts');
      } finally {
        generatingReceipts.value = false;
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
      pagination.current_page = 1;
      fetchReceipts();
    }, { deep: true });

    // Lifecycle hooks
    onMounted(() => {
      fetchReceipts();
    });

    return {
      receipts,
      loading,
      filters,
      pagination,
      availableYears,
      paginationPages,
      showGenerateAllAnnualModal,
      showVoidModal,
      selectedReceiptId,
      annualReceiptYear,
      generatingReceipts,
      fetchReceipts,
      changePage,
      downloadReceipt,
      sendReceipt,
      confirmVoidReceipt,
      voidReceipt,
      openGenerateAllAnnualModal,
      generateAllAnnualReceipts,
      formatDate,
      formatAmount,
      capitalizeFirstLetter
    };
  }
};
</script>
