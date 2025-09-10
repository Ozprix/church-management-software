<template>
  <div class="tax-receipt-page">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
      <div class="p-4 border-b border-gray-200 bg-gray-50">
        <h1 class="text-2xl font-semibold text-gray-800">Tax Receipts</h1>
        <p class="text-gray-600 mt-1">Manage and generate tax receipts for donations</p>
      </div>
      
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-blue-800">{{ stats.total }}</h3>
                <p class="text-sm text-blue-600">Total Receipts</p>
              </div>
              <div class="text-blue-500">
                <i class="fas fa-file-invoice-dollar text-3xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-green-50 p-4 rounded-lg border border-green-100">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-green-800">${{ formatAmount(stats.totalAmount) }}</h3>
                <p class="text-sm text-green-600">Total Amount</p>
              </div>
              <div class="text-green-500">
                <i class="fas fa-dollar-sign text-3xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-purple-800">{{ stats.annualCount }}</h3>
                <p class="text-sm text-purple-600">Annual Receipts</p>
              </div>
              <div class="text-purple-500">
                <i class="fas fa-calendar-alt text-3xl"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <tax-receipt-list />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TaxReceiptList from './TaxReceiptList.vue';

export default {
  name: 'TaxReceiptPage',
  components: {
    TaxReceiptList
  },
  setup() {
    const stats = ref({
      total: 0,
      totalAmount: 0,
      annualCount: 0
    });
    
    const fetchStats = async () => {
      try {
        const response = await axios.get('/api/tax-receipts/stats');
        stats.value = response.data;
      } catch (error) {
        console.error('Error fetching tax receipt stats:', error);
      }
    };
    
    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2);
    };
    
    onMounted(() => {
      fetchStats();
    });
    
    return {
      stats,
      formatAmount
    };
  }
};
</script>
