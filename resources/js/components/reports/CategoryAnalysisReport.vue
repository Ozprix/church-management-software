<template>
  <div class="category-analysis-report">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
      <h3 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4">Category Analysis Report</h3>
      
      <!-- Time Period Selection -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Time Period</label>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <button 
            v-for="period in timePeriods" 
            :key="period.value"
            @click="selectTimePeriod(period.value)"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              selectedPeriod === period.value 
                ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300' 
                : 'bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700'
            ]"
          >
            {{ period.label }}
          </button>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>
      
      <!-- No Data State -->
      <div v-else-if="!categoryData.length" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No data available</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Try selecting a different time period or check back later.
        </p>
      </div>
      
      <!-- Category Data -->
      <div v-else>
        <!-- Overall Summary -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Overall Summary</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
              <h5 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Total Donations</h5>
              <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summary.totalAmount) }}</p>
              <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ summary.donationCount }} donations</p>
            </div>
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
              <h5 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Top Category</h5>
              <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ summary.topCategory.name }}</p>
              <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ formatCurrency(summary.topCategory.amount) }} ({{ formatPercentage(summary.topCategory.percentage) }})</p>
            </div>
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
              <h5 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Category Diversity</h5>
              <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ summary.categoryCount }}</p>
              <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Active categories</p>
            </div>
          </div>
        </div>
        
        <!-- Category Distribution -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Category Distribution</h4>
            <div class="h-64 bg-white dark:bg-neutral-800">
              <canvas ref="distributionChart"></canvas>
            </div>
          </div>
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Category Breakdown</h4>
            <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
              <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Percentage</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Count</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                  <tr v-for="category in categoryData" :key="category.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="h-3 w-3 rounded-full mr-2" :style="{ backgroundColor: category.color }"></div>
                        <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ category.name }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(category.amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatPercentage(category.percentage) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ category.count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Category Trends Over Time -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Category Trends Over Time</h4>
          <div class="h-80 bg-white dark:bg-neutral-800">
            <canvas ref="trendChart"></canvas>
          </div>
        </div>
        
        <!-- Category Growth Analysis -->
        <div>
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Category Growth Analysis</h4>
          <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
              <thead class="bg-neutral-50 dark:bg-neutral-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Previous Period</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Current Period</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Growth</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Trend</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                <tr v-for="category in growthData" :key="category.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-3 w-3 rounded-full mr-2" :style="{ backgroundColor: category.color }"></div>
                      <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ category.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(category.previousAmount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(category.currentAmount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      category.growthPercentage > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                      category.growthPercentage < 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                      'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'
                    ]">
                      {{ category.growthPercentage > 0 ? '+' : '' }}{{ formatPercentage(category.growthPercentage) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <svg v-if="category.growthPercentage > 0" class="h-5 w-5 text-green-500 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else-if="category.growthPercentage < 0" class="h-5 w-5 text-red-500 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="h-5 w-5 text-neutral-400 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10a1 1 0 01-1 1H3a1 1 0 110-2h14a1 1 0 011 1z" clip-rule="evenodd" />
                    </svg>
                  </td>
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
import { useDonationStore } from '../../stores/donations';
import Chart from 'chart.js/auto';

const donationStore = useDonationStore();

// State
const selectedPeriod = ref('this-year');
const categoryData = ref([]);
const growthData = ref([]);
const loading = ref(false);
const distributionChart = ref(null);
const trendChart = ref(null);
let distributionChartInstance = null;
let trendChartInstance = null;

// Time period options
const timePeriods = [
  { label: 'This Month', value: 'this-month' },
  { label: 'Last Month', value: 'last-month' },
  { label: 'This Quarter', value: 'this-quarter' },
  { label: 'This Year', value: 'this-year' },
  { label: 'Last Year', value: 'last-year' },
  { label: 'All Time', value: 'all-time' }
];

// Computed
const summary = computed(() => {
  if (!categoryData.value.length) {
    return {
      totalAmount: 0,
      donationCount: 0,
      categoryCount: 0,
      topCategory: { name: 'N/A', amount: 0, percentage: 0 }
    };
  }
  
  const totalAmount = categoryData.value.reduce((sum, category) => sum + category.amount, 0);
  const donationCount = categoryData.value.reduce((sum, category) => sum + category.count, 0);
  const categoryCount = categoryData.value.length;
  const topCategory = [...categoryData.value].sort((a, b) => b.amount - a.amount)[0];
  
  return {
    totalAmount,
    donationCount,
    categoryCount,
    topCategory: {
      name: topCategory.name,
      amount: topCategory.amount,
      percentage: (topCategory.amount / totalAmount) * 100
    }
  };
});

// Methods
const selectTimePeriod = (period) => {
  selectedPeriod.value = period;
  loadCategoryData();
};

const loadCategoryData = async () => {
  loading.value = true;
  
  try {
    const params = { period: selectedPeriod.value };
    const response = await donationStore.reports.getCategoryAnalysis(params);
    
    categoryData.value = response.data.categories;
    growthData.value = response.data.growth;
    
    renderCharts();
  } catch (error) {
    console.error('Error loading category analysis data:', error);
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  if (!categoryData.value.length) return;
  
  // Destroy existing chart instances to prevent duplicates
  if (distributionChartInstance) {
    distributionChartInstance.destroy();
  }
  
  if (trendChartInstance) {
    trendChartInstance.destroy();
  }
  
  // Render distribution chart
  distributionChartInstance = new Chart(distributionChart.value, {
    type: 'doughnut',
    data: {
      labels: categoryData.value.map(c => c.name),
      datasets: [{
        data: categoryData.value.map(c => c.amount),
        backgroundColor: categoryData.value.map(c => c.color || getRandomColor())
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
  
  // Render trend chart
  const trendData = prepareTrendData();
  
  trendChartInstance = new Chart(trendChart.value, {
    type: 'line',
    data: {
      labels: trendData.labels,
      datasets: trendData.datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
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
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': ' + formatCurrency(context.raw);
            }
          }
        }
      }
    }
  });
};

const prepareTrendData = () => {
  // This is a placeholder for the actual API data
  // In a real implementation, this would come from the API response
  
  const trendResponse = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: categoryData.value.slice(0, 5).map((category, index) => ({
      label: category.name,
      data: generateRandomData(6, category.amount / 6),
      borderColor: category.color || getRandomColor(),
      backgroundColor: hexToRgba(category.color || getRandomColor(), 0.1),
      tension: 0.4,
      fill: false
    }))
  };
  
  return trendResponse;
};

const generateRandomData = (count, baseValue) => {
  return Array.from({ length: count }, () => baseValue * (0.7 + Math.random() * 0.6));
};

const getRandomColor = () => {
  const colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#d946ef', '#f97316', '#14b8a6'
  ];
  return colors[Math.floor(Math.random() * colors.length)];
};

const hexToRgba = (hex, alpha = 1) => {
  if (!hex) return `rgba(59, 130, 246, ${alpha})`;
  
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
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

// Watch for changes to selectedPeriod
watch(selectedPeriod, () => {
  loadCategoryData();
});

// Lifecycle hooks
onMounted(() => {
  // Initialize with sample data for development
  const sampleCategories = [
    { id: 'cat-1', name: 'Tithe', amount: 12500, percentage: 45.5, count: 78, color: '#4F46E5' },
    { id: 'cat-2', name: 'Offering', amount: 8200, percentage: 29.8, count: 120, color: '#10B981' },
    { id: 'cat-3', name: 'Building Fund', amount: 3700, percentage: 13.5, count: 25, color: '#F59E0B' },
    { id: 'cat-4', name: 'Missions', amount: 1800, percentage: 6.5, count: 15, color: '#EF4444' },
    { id: 'cat-5', name: 'Benevolence', amount: 1300, percentage: 4.7, count: 12, color: '#8B5CF6' }
  ];
  
  const sampleGrowthData = [
    { id: 'cat-1', name: 'Tithe', previousAmount: 11200, currentAmount: 12500, growthPercentage: 11.6, color: '#4F46E5' },
    { id: 'cat-2', name: 'Offering', previousAmount: 7500, currentAmount: 8200, growthPercentage: 9.3, color: '#10B981' },
    { id: 'cat-3', name: 'Building Fund', previousAmount: 4200, currentAmount: 3700, growthPercentage: -11.9, color: '#F59E0B' },
    { id: 'cat-4', name: 'Missions', previousAmount: 1500, currentAmount: 1800, growthPercentage: 20.0, color: '#EF4444' },
    { id: 'cat-5', name: 'Benevolence', previousAmount: 1300, currentAmount: 1300, growthPercentage: 0, color: '#8B5CF6' }
  ];
  
  categoryData.value = sampleCategories;
  growthData.value = sampleGrowthData;
  
  // In production, we would call loadCategoryData() here
  // loadCategoryData();
  
  // Render charts with sample data
  setTimeout(() => {
    renderCharts();
  }, 100);
});
</script>
