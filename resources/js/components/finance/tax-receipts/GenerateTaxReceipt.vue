<template>
  <div class="generate-tax-receipt">
    <button 
      @click="showModal = true" 
      class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center"
      :disabled="donation.tax_receipt_id || !isEligible"
      :class="{ 'opacity-50 cursor-not-allowed': donation.tax_receipt_id || !isEligible }"
    >
      <i class="fas fa-file-invoice mr-1"></i>
      <span v-if="donation.tax_receipt_id">View Receipt</span>
      <span v-else>Generate Receipt</span>
    </button>

    <!-- Generate/View Tax Receipt Modal -->
    <div v-if="showModal" class="fixed inset-0 z-10 overflow-y-auto">
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
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  {{ donation.tax_receipt_id ? 'Tax Receipt' : 'Generate Tax Receipt' }}
                </h3>
                <div class="mt-2">
                  <div v-if="!donation.tax_receipt_id">
                    <p class="text-sm text-gray-500 mb-4">
                      Generate a tax receipt for this donation. The receipt will be available for download and can be sent to the donor via email.
                    </p>
                    <div v-if="!isEligible" class="p-3 bg-yellow-50 border border-yellow-200 rounded-md mb-4">
                      <p class="text-sm text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        This donation is not eligible for a tax receipt. Only donations with a 'completed' payment status can receive tax receipts.
                      </p>
                    </div>
                  </div>
                  
                  <div v-if="receipt" class="mt-4">
                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <p class="text-xs text-gray-500">Receipt Number</p>
                          <p class="text-sm font-medium">{{ receipt.receipt_number }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Issue Date</p>
                          <p class="text-sm font-medium">{{ formatDate(receipt.issue_date) }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Amount</p>
                          <p class="text-sm font-medium">${{ formatAmount(receipt.amount) }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Status</p>
                          <p class="text-sm font-medium">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                              :class="{
                                'bg-green-100 text-green-800': receipt.status === 'issued',
                                'bg-blue-100 text-blue-800': receipt.status === 'sent',
                                'bg-red-100 text-red-800': receipt.status === 'voided'
                              }">
                              {{ capitalizeFirstLetter(receipt.status) }}
                            </span>
                          </p>
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex justify-center space-x-4 mt-4">
                      <button 
                        @click="downloadReceipt" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        :disabled="receipt.status === 'voided'"
                        :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' }"
                      >
                        <i class="fas fa-download mr-2"></i> Download
                      </button>
                      <button 
                        @click="sendReceiptByEmail" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        :disabled="receipt.status === 'voided' || receipt.status === 'sent'"
                        :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' || receipt.status === 'sent' }"
                      >
                        <i class="fas fa-envelope mr-2"></i> Send Email
                      </button>
                      <button 
                        @click="confirmVoidReceipt" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        :disabled="receipt.status === 'voided'"
                        :class="{ 'opacity-50 cursor-not-allowed': receipt.status === 'voided' }"
                      >
                        <i class="fas fa-ban mr-2"></i> Void
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              v-if="!donation.tax_receipt_id && isEligible"
              @click="generateReceipt" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="loading"
            >
              <span v-if="loading">
                <i class="fas fa-spinner fa-spin mr-2"></i> Generating...
              </span>
              <span v-else>Generate Receipt</span>
            </button>
            <button 
              @click="closeModal" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Void Receipt Confirmation Modal -->
    <div v-if="showVoidModal" class="fixed inset-0 z-20 overflow-y-auto">
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
              :disabled="voidLoading"
            >
              <span v-if="voidLoading">
                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
              </span>
              <span v-else>Void Receipt</span>
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
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'GenerateTaxReceipt',
  props: {
    donation: {
      type: Object,
      required: true
    }
  },
  emits: ['receipt-updated'],
  setup(props, { emit }) {
    const toast = useToast();
    const showModal = ref(false);
    const showVoidModal = ref(false);
    const loading = ref(false);
    const voidLoading = ref(false);
    const receipt = ref(null);

    const isEligible = computed(() => {
      return props.donation.payment_status === 'completed';
    });

    const fetchReceipt = async () => {
      if (!props.donation.tax_receipt_id) return;
      
      try {
        const response = await axios.get(`/api/tax-receipts/${props.donation.tax_receipt_id}`);
        receipt.value = response.data;
      } catch (error) {
        console.error('Error fetching tax receipt:', error);
        toast.error('Failed to load tax receipt details');
      }
    };

    const generateReceipt = async () => {
      loading.value = true;
      try {
        const response = await axios.post(`/api/donations/${props.donation.id}/tax-receipt`);
        receipt.value = response.data;
        toast.success('Tax receipt generated successfully');
        emit('receipt-updated', response.data);
      } catch (error) {
        console.error('Error generating tax receipt:', error);
        toast.error('Failed to generate tax receipt');
      } finally {
        loading.value = false;
      }
    };

    const downloadReceipt = async () => {
      try {
        const response = await axios.get(`/api/tax-receipts/${receipt.value.id}/download`, {
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `tax-receipt-${receipt.value.receipt_number}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        toast.success('Tax receipt downloaded successfully');
      } catch (error) {
        console.error('Error downloading tax receipt:', error);
        toast.error('Failed to download tax receipt');
      }
    };

    const sendReceiptByEmail = async () => {
      try {
        await axios.post(`/api/tax-receipts/${receipt.value.id}/send`);
        toast.success('Tax receipt sent by email successfully');
        fetchReceipt(); // Refresh receipt data to update status
      } catch (error) {
        console.error('Error sending tax receipt:', error);
        toast.error('Failed to send tax receipt by email');
      }
    };

    const confirmVoidReceipt = () => {
      showVoidModal.value = true;
    };

    const voidReceipt = async () => {
      voidLoading.value = true;
      try {
        await axios.post(`/api/tax-receipts/${receipt.value.id}/void`);
        toast.success('Tax receipt voided successfully');
        showVoidModal.value = false;
        fetchReceipt(); // Refresh receipt data to update status
        emit('receipt-updated', { ...receipt.value, status: 'voided' });
      } catch (error) {
        console.error('Error voiding tax receipt:', error);
        toast.error('Failed to void tax receipt');
      } finally {
        voidLoading.value = false;
      }
    };

    const closeModal = () => {
      showModal.value = false;
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

    onMounted(() => {
      fetchReceipt();
    });

    return {
      showModal,
      showVoidModal,
      loading,
      voidLoading,
      receipt,
      isEligible,
      generateReceipt,
      downloadReceipt,
      sendReceiptByEmail,
      confirmVoidReceipt,
      voidReceipt,
      closeModal,
      formatDate,
      formatAmount,
      capitalizeFirstLetter
    };
  }
};
</script>
