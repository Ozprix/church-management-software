<template>
  <div class="donor-contribution-report">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
      <h3 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4">Donor Contribution Report</h3>
      
      <!-- Donor Selection -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Select Donor</label>
        <div class="relative">
          <select 
            v-model="selectedDonorId"
            class="block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
            @change="loadDonorContributions"
          >
            <option value="">Select a donor</option>
            <option v-for="donor in donors" :key="donor.id" :value="donor.id">
              {{ donor.name }}
            </option>
          </select>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>
      
      <!-- No Data State -->
      <div v-else-if="!selectedDonorId || !donorContributions.length" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No data available</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          {{ selectedDonorId ? 'No contributions found for this donor.' : 'Select a donor to view their contribution history.' }}
        </p>
      </div>
      
      <!-- Donor Data -->
      <div v-else>
        <!-- Donor Summary -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Total Contributions</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(donorSummary.totalAmount) }}</p>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Contribution Count</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ donorSummary.count }}</p>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Average Contribution</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(donorSummary.averageAmount) }}</p>
          </div>
        </div>
        
        <!-- Contribution Trend Chart -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Contribution History</h4>
          <div class="h-64 bg-white dark:bg-neutral-800">
            <canvas ref="contributionChart"></canvas>
          </div>
        </div>
        
        <!-- Contribution by Category -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Contributions by Category</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="h-64 bg-white dark:bg-neutral-800">
              <canvas ref="categoryChart"></canvas>
            </div>
            <div class="overflow-hidden">
              <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Percentage</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                  <tr v-for="(category, index) in categoryBreakdown" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">{{ category.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(category.amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatPercentage(category.percentage) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Recent Contributions -->
        <div>
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Recent Contributions</h4>
          <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
              <thead class="bg-neutral-50 dark:bg-neutral-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Payment Method</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                <tr v-for="donation in donorContributions.slice(0, 10)" :key="donation.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ formatDate(donation.date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ donation.category }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ donation.payment_method }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(donation.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useDonationsStore } from '../../stores/donations';
import Chart from 'chart.js/auto';

const donationsStore = useDonationsStore();

// State
const selectedDonorId = ref('');
const donors = ref([]);
const donorContributions = ref([]);
const loading = ref(false);
const contributionChart = ref(null);
const categoryChart = ref(null);
let contributionChartInstance = null;
let categoryChartInstance = null;

// Computed
const donorSummary = computed(() => {
  if (!donorContributions.value.length) {
    return { totalAmount: 0, count: 0, averageAmount: 0 };
  }
  
  const totalAmount = donorContributions.value.reduce((sum, donation) => sum + parseFloat(donation.amount), 0);
  const count = donorContributions.value.length;
  
  return {
    totalAmount,
    count,
    averageAmount: totalAmount / count
  };
});

const categoryBreakdown = computed(() => {
  if (!donorContributions.value.length) return [];
  
  const categories = {};
  const totalAmount = donorContributions.value.reduce((sum, donation) => sum + parseFloat(donation.amount), 0);
  
  donorContributions.value.forEach(donation => {
    const category = donation.category;
    if (!categories[category]) {
      categories[category] = 0;
    }
    categories[category] += parseFloat(donation.amount);
  });
  
  return Object.keys(categories).map(name => ({
    name,
    amount: categories[name],
    percentage: (categories[name] / totalAmount) * 100
  })).sort((a, b) => b.amount - a.amount);
});

// Methods
const loadDonors = async () => {
  try {
    const response = await donationsStore.getDonors();
    donors.value = response.data;
  } catch (error) {
    console.error('Error loading donors:', error);
  }
};

const loadDonorContributions = async () => {
  if (!selectedDonorId.value) {
    donorContributions.value = [];
    return;
  }
  
  loading.value = true;
  
  try {
    const response = await donationsStore.getDonorContributions(selectedDonorId.value);
    donorContributions.value = response.data;
    renderCharts();
  } catch (error) {
    console.error('Error loading donor contributions:', error);
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  if (donorContributions.value.length === 0) return;
  
  // Destroy existing chart instances to prevent duplicates
  if (contributionChartInstance) {
    contributionChartInstance.destroy();
  }
  
  if (categoryChartInstance) {
    categoryChartInstance.destroy();
  }
  
  // Prepare data for contribution history chart
  const contributionData = prepareContributionHistoryData();
  
  // Render contribution history chart
  contributionChartInstance = new Chart(contributionChart.value, {
    type: 'line',
    data: {
      labels: contributionData.labels,
      datasets: [{
        label: 'Contribution Amount',
        data: contributionData.data,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return formatCurrency(context.raw);
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return formatCurrency(value, true);
            }
          }
        }
      }
    }
  });
  
  // Render category breakdown chart
  categoryChartInstance = new Chart(categoryChart.value, {
    type: 'doughnut',
    data: {
      labels: categoryBreakdown.value.map(c => c.name),
      datasets: [{
        data: categoryBreakdown.value.map(c => c.amount),
        backgroundColor: [
          '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
          '#ec4899', '#06b6d4', '#d946ef', '#f97316', '#14b8a6'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              const value = context.raw;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return `${context.label}: ${formatCurrency(value)} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
};

const prepareContributionHistoryData = () => {
  // Group donations by month
  const donationsByMonth = {};
  
  donorContributions.value.forEach(donation => {
    const date = new Date(donation.date);
    const monthYear = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    
    if (!donationsByMonth[monthYear]) {
      donationsByMonth[monthYear] = 0;
    }
    
    donationsByMonth[monthYear] += parseFloat(donation.amount);
  });
  
  // Sort months chronologically
  const sortedMonths = Object.keys(donationsByMonth).sort();
  
  // Format labels and prepare data
  const labels = sortedMonths.map(monthYear => {
    const [year, month] = monthYear.split('-');
    return `${getMonthName(parseInt(month) - 1)} ${year}`;
  });
  
  const data = sortedMonths.map(monthYear => donationsByMonth[monthYear]);
  
  return { labels, data };
};

const getMonthName = (monthIndex) => {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return months[monthIndex];
};

const formatCurrency = (value, abbreviated = false) => {
  if (value === null || value === undefined) return '-';
  
  if (abbreviated && value >= 1000) {
    return '$' + (value / 1000).toFixed(1) + 'k';
  }
  
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(value);
};

const formatPercentage = (value) => {
  return value.toFixed(1) + '%';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(date);
};

// Watch for changes to selectedDonorId
watch(selectedDonorId, () => {
  loadDonorContributions();
});

// Lifecycle hooks
onMounted(() => {
  loadDonors();
});
</script>
