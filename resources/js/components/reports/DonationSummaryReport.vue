<template>
  <div class="donation-summary-report">
    <!-- Report Header -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Donation Summary Report</h3>
        <ReportExportOptions reportType="summary" :filters="props.filters" />
      </div>
      
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
      </div>
      
      <div v-else>
        <!-- Report Date Range -->
        <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
          <span v-if="filters.startDate && filters.endDate">
            {{ formatDate(filters.startDate) }} - {{ formatDate(filters.endDate) }}
          </span>
          <span v-else>All Time</span>
        </div>
        
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Donations</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summaryData.totalAmount) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ summaryData.donationCount }} donations</p>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Average Donation</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summaryData.averageAmount) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">per donation</p>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Largest Donation</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summaryData.largestAmount) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ formatDate(summaryData.largestDate) }}</p>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Recurring Donations</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summaryData.recurringAmount) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ summaryData.recurringCount }} recurring</p>
          </div>
        </div>
        
        <!-- Donation Trend Chart -->
        <div class="mb-6">
          <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Donation Trends</h4>
          <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 h-64">
            <canvas ref="trendChart"></canvas>
          </div>
        </div>
        
        <!-- Donation by Category -->
        <div class="mb-6">
          <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Donations by Category</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 h-64">
              <canvas ref="categoryChart"></canvas>
            </div>
            <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-auto max-h-64">
              <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      Category
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      Amount
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      %
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                  <tr v-for="(category, index) in categoryData" :key="index">
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                      <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: category.color }"></div>
                        {{ category.name }}
                      </div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">
                      {{ formatCurrency(category.amount) }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">
                      {{ formatPercentage(category.percentage) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Donation by Payment Method -->
        <div>
          <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Donations by Payment Method</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 h-64">
              <canvas ref="paymentMethodChart"></canvas>
            </div>
            <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-auto max-h-64">
              <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-800">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      Payment Method
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      Amount
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                      %
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                  <tr v-for="(method, index) in paymentMethodData" :key="index">
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                      {{ method.name }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">
                      {{ formatCurrency(method.amount) }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">
                      {{ formatPercentage(method.percentage) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, nextTick } from 'vue';
import { useDonationStore } from '../../stores/donations';
import ReportExportOptions from './ReportExportOptions.vue';
import { useToastService } from '../../services/toastService';
import Chart from 'chart.js/auto';

// Props
const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  }
});

// Store
const donationStore = useDonationStore();
const toast = useToastService();

// State
const isLoading = ref(false);
const trendChart = ref(null);
const categoryChart = ref(null);
const paymentMethodChart = ref(null);
let trendChartInstance = null;
let categoryChartInstance = null;
let paymentMethodChartInstance = null;

// Chart data
const summaryData = reactive({
  totalAmount: 0,
  donationCount: 0,
  averageAmount: 0,
  largestAmount: 0,
  largestDate: null,
  recurringAmount: 0,
  recurringCount: 0
});

const categoryData = ref([]);
const paymentMethodData = ref([]);
const trendData = ref({
  labels: [],
  datasets: []
});

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatPercentage = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'percent',
    minimumFractionDigits: 1,
    maximumFractionDigits: 1
  }).format(value || 0);
};

const generateReportData = async () => {
  try {
    isLoading.value = true;
    
    // Apply filters to get filtered donations
    const filteredDonations = filterDonations(donationStore.donations, props.filters);
    
    // Calculate summary data
    calculateSummaryData(filteredDonations);
    
    // Calculate category data
    calculateCategoryData(filteredDonations);
    
    // Calculate payment method data
    calculatePaymentMethodData(filteredDonations);
    
    // Calculate trend data
    calculateTrendData(filteredDonations);
    
    // Wait for DOM to update
    await nextTick();
    
    // Render charts
    renderCharts();
  } catch (error) {
    console.error('Error generating report data:', error);
    toast.error('Failed to generate report data');
  } finally {
    isLoading.value = false;
  }
};

const filterDonations = (donations, filters) => {
  return donations.filter(donation => {
    // Date range filter
    if (filters.startDate && new Date(donation.date) < new Date(filters.startDate)) {
      return false;
    }
    if (filters.endDate && new Date(donation.date) > new Date(filters.endDate)) {
      return false;
    }
    
    // Category filter
    if (filters.categoryIds && !filters.categoryIds.includes('all') && !filters.categoryIds.includes(donation.categoryId)) {
      return false;
    }
    
    // Payment method filter
    if (filters.paymentMethodIds && !filters.paymentMethodIds.includes('all') && !filters.paymentMethodIds.includes(donation.paymentMethodId)) {
      return false;
    }
    
    // Campaign filter
    if (filters.campaignIds && !filters.campaignIds.includes('all')) {
      if (filters.campaignIds.includes('none') && donation.campaignId) {
        return false;
      }
      if (!filters.campaignIds.includes('none') && !filters.campaignIds.includes(donation.campaignId)) {
        return false;
      }
    }
    
    // Amount range filter
    if (filters.minAmount && donation.amount < parseFloat(filters.minAmount)) {
      return false;
    }
    if (filters.maxAmount && donation.amount > parseFloat(filters.maxAmount)) {
      return false;
    }
    
    // Recurring filter
    if (filters.recurringOnly && !donation.isRecurring) {
      return false;
    }
    
    // Tax deductible filter
    if (filters.taxDeductibleOnly && !donation.taxDeductible) {
      return false;
    }
    
    // Receipt sent filter
    if (filters.receiptSent !== null && donation.receiptSent !== filters.receiptSent) {
      return false;
    }
    
    return true;
  });
};

const calculateSummaryData = (filteredDonations) => {
  // Total amount and count
  summaryData.totalAmount = filteredDonations.reduce((sum, donation) => sum + donation.amount, 0);
  summaryData.donationCount = filteredDonations.length;
  
  // Average amount
  summaryData.averageAmount = filteredDonations.length > 0 ? summaryData.totalAmount / filteredDonations.length : 0;
  
  // Largest donation
  const largestDonation = filteredDonations.reduce((largest, donation) => {
    return donation.amount > (largest?.amount || 0) ? donation : largest;
  }, null);
  
  summaryData.largestAmount = largestDonation?.amount || 0;
  summaryData.largestDate = largestDonation?.date || null;
  
  // Recurring donations
  const recurringDonations = filteredDonations.filter(donation => donation.isRecurring);
  summaryData.recurringAmount = recurringDonations.reduce((sum, donation) => sum + donation.amount, 0);
  summaryData.recurringCount = recurringDonations.length;
};

const calculateCategoryData = (filteredDonations) => {
  // Group donations by category
  const categoriesMap = {};
  
  filteredDonations.forEach(donation => {
    const categoryId = donation.categoryId;
    if (!categoriesMap[categoryId]) {
      const category = donationStore.donationCategories.find(c => c.id === categoryId);
      categoriesMap[categoryId] = {
        id: categoryId,
        name: category?.name || 'Unknown',
        color: category?.color || '#cccccc',
        amount: 0,
        count: 0
      };
    }
    
    categoriesMap[categoryId].amount += donation.amount;
    categoriesMap[categoryId].count += 1;
  });
  
  // Convert to array and calculate percentages
  const totalAmount = summaryData.totalAmount;
  categoryData.value = Object.values(categoriesMap)
    .map(category => ({
      ...category,
      percentage: totalAmount > 0 ? category.amount / totalAmount : 0
    }))
    .sort((a, b) => b.amount - a.amount);
};

const calculatePaymentMethodData = (filteredDonations) => {
  // Group donations by payment method
  const methodsMap = {};
  
  filteredDonations.forEach(donation => {
    const methodId = donation.paymentMethodId;
    if (!methodsMap[methodId]) {
      const method = donationStore.paymentMethods.find(m => m.id === methodId);
      methodsMap[methodId] = {
        id: methodId,
        name: method?.name || 'Unknown',
        amount: 0,
        count: 0
      };
    }
    
    methodsMap[methodId].amount += donation.amount;
    methodsMap[methodId].count += 1;
  });
  
  // Convert to array and calculate percentages
  const totalAmount = summaryData.totalAmount;
  paymentMethodData.value = Object.values(methodsMap)
    .map(method => ({
      ...method,
      percentage: totalAmount > 0 ? method.amount / totalAmount : 0
    }))
    .sort((a, b) => b.amount - a.amount);
};

const calculateTrendData = (filteredDonations) => {
  // Determine time grouping based on date range
  let groupBy = 'month'; // default
  
  if (props.filters.startDate && props.filters.endDate) {
    const startDate = new Date(props.filters.startDate);
    const endDate = new Date(props.filters.endDate);
    const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    
    if (daysDiff <= 31) {
      groupBy = 'day';
    } else if (daysDiff <= 365) {
      groupBy = 'month';
    } else {
      groupBy = 'year';
    }
  }
  
  // Group donations by time period
  const timeGroups = {};
  const sortedDonations = [...filteredDonations].sort((a, b) => new Date(a.date) - new Date(b.date));
  
  sortedDonations.forEach(donation => {
    const date = new Date(donation.date);
    let key;
    
    if (groupBy === 'day') {
      key = date.toISOString().split('T')[0]; // YYYY-MM-DD
    } else if (groupBy === 'month') {
      key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`; // YYYY-MM
    } else {
      key = `${date.getFullYear()}`; // YYYY
    }
    
    if (!timeGroups[key]) {
      timeGroups[key] = {
        amount: 0,
        count: 0
      };
    }
    
    timeGroups[key].amount += donation.amount;
    timeGroups[key].count += 1;
  });
  
  // Convert to arrays for chart
  const labels = [];
  const amounts = [];
  const counts = [];
  
  Object.keys(timeGroups).sort().forEach(key => {
    let label;
    
    if (groupBy === 'day') {
      const date = new Date(key);
      label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    } else if (groupBy === 'month') {
      const [year, month] = key.split('-');
      label = new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    } else {
      label = key;
    }
    
    labels.push(label);
    amounts.push(timeGroups[key].amount);
    counts.push(timeGroups[key].count);
  });
  
  trendData.value = {
    labels,
    datasets: [
      {
        label: 'Amount',
        data: amounts,
        backgroundColor: 'rgba(79, 70, 229, 0.2)',
        borderColor: 'rgba(79, 70, 229, 1)',
        borderWidth: 2,
        yAxisID: 'y'
      },
      {
        label: 'Count',
        data: counts,
        backgroundColor: 'rgba(16, 185, 129, 0.2)',
        borderColor: 'rgba(16, 185, 129, 1)',
        borderWidth: 2,
        yAxisID: 'y1'
      }
    ]
  };
};

const renderCharts = () => {
  // Destroy existing charts
  if (trendChartInstance) trendChartInstance.destroy();
  if (categoryChartInstance) categoryChartInstance.destroy();
  if (paymentMethodChartInstance) paymentMethodChartInstance.destroy();
  
  // Trend Chart
  if (trendChart.value) {
    trendChartInstance = new Chart(trendChart.value, {
      type: 'line',
      data: trendData.value,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'Amount'
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            grid: {
              drawOnChartArea: false
            },
            title: {
              display: true,
              text: 'Count'
            }
          }
        }
      }
    });
  }
  
  // Category Chart
  if (categoryChart.value) {
    categoryChartInstance = new Chart(categoryChart.value, {
      type: 'doughnut',
      data: {
        labels: categoryData.value.map(c => c.name),
        datasets: [{
          data: categoryData.value.map(c => c.amount),
          backgroundColor: categoryData.value.map(c => c.color)
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });
  }
  
  // Payment Method Chart
  if (paymentMethodChart.value) {
    const colors = [
      '#4F46E5', '#10B981', '#F59E0B', '#EF4444', 
      '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1'
    ];
    
    paymentMethodChartInstance = new Chart(paymentMethodChart.value, {
      type: 'pie',
      data: {
        labels: paymentMethodData.value.map(m => m.name),
        datasets: [{
          data: paymentMethodData.value.map(m => m.amount),
          backgroundColor: paymentMethodData.value.map((_, i) => colors[i % colors.length])
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });
  }
};

const exportReport = (format) => {
  // Export and print functionality now handled by ReportExportOptions component
};

const printReport = () => {
  window.print();
};

// Watch for filter changes
watch(() => props.filters, () => {
  generateReportData();
}, { deep: true });

// Initialize
onMounted(() => {
  generateReportData();
});
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .donation-summary-report,
  .donation-summary-report * {
    visibility: visible;
  }
  .donation-summary-report {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
