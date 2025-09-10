<template>
  <div class="donation-insights">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Donation Insights</h3>
        <div class="flex space-x-2">
          <select 
            v-model="timeRange" 
            class="form-select text-sm rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
          >
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
            <option value="year">This Year</option>
            <option value="custom">Custom Range</option>
          </select>
          <button 
            @click="refreshData" 
            class="p-1 rounded-md text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
            title="Refresh Data"
          >
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      
      <div class="p-4 sm:p-6">
        <!-- Date Range Picker (shown when custom range is selected) -->
        <div v-if="timeRange === 'custom'" class="mb-6 flex space-x-4">
          <div class="w-1/2">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Start Date</label>
            <input 
              type="date" 
              v-model="startDate" 
              class="form-input w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
            />
          </div>
          <div class="w-1/2">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">End Date</label>
            <input 
              type="date" 
              v-model="endDate" 
              class="form-input w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
            />
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="spinner"></div>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
          <div class="text-red-500 dark:text-red-400 mb-2">
            <i class="fas fa-exclamation-circle text-3xl"></i>
          </div>
          <h4 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Unable to Load Data</h4>
          <p class="text-neutral-600 dark:text-neutral-400 mb-4">{{ error }}</p>
          <button 
            @click="refreshData" 
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md"
          >
            Try Again
          </button>
        </div>
        
        <!-- Data Visualization -->
        <div v-else class="space-y-8">
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Donations</p>
                  <h4 class="text-2xl font-bold text-neutral-900 dark:text-white">${{ formatNumber(summary.totalAmount) }}</h4>
                </div>
                <div class="text-primary-500 dark:text-primary-400">
                  <i class="fas fa-hand-holding-heart text-2xl"></i>
                </div>
              </div>
              <div class="mt-2 flex items-center">
                <span 
                  :class="summary.percentChange >= 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                  class="text-sm font-medium flex items-center"
                >
                  <i :class="summary.percentChange >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                  {{ Math.abs(summary.percentChange) }}%
                </span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">vs previous period</span>
              </div>
            </div>
            
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Donors</p>
                  <h4 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ summary.totalDonors }}</h4>
                </div>
                <div class="text-primary-500 dark:text-primary-400">
                  <i class="fas fa-users text-2xl"></i>
                </div>
              </div>
              <div class="mt-2 flex items-center">
                <span 
                  :class="summary.donorChange >= 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                  class="text-sm font-medium flex items-center"
                >
                  <i :class="summary.donorChange >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                  {{ Math.abs(summary.donorChange) }}%
                </span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">vs previous period</span>
              </div>
            </div>
            
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">Average Donation</p>
                  <h4 class="text-2xl font-bold text-neutral-900 dark:text-white">${{ formatNumber(summary.averageDonation) }}</h4>
                </div>
                <div class="text-primary-500 dark:text-primary-400">
                  <i class="fas fa-chart-line text-2xl"></i>
                </div>
              </div>
              <div class="mt-2 flex items-center">
                <span 
                  :class="summary.averageChange >= 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                  class="text-sm font-medium flex items-center"
                >
                  <i :class="summary.averageChange >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                  {{ Math.abs(summary.averageChange) }}%
                </span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">vs previous period</span>
              </div>
            </div>
          </div>
          
          <!-- Charts -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Donation Trends Chart -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
              <h4 class="text-base font-medium text-neutral-900 dark:text-white mb-4">Donation Trends</h4>
              <div class="h-64">
                <canvas ref="trendChart"></canvas>
              </div>
            </div>
            
            <!-- Donation Categories Chart -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
              <h4 class="text-base font-medium text-neutral-900 dark:text-white mb-4">Donation Categories</h4>
              <div class="h-64">
                <canvas ref="categoryChart"></canvas>
              </div>
            </div>
          </div>
          
          <!-- Campaign Progress -->
          <div v-if="campaigns.length > 0">
            <h4 class="text-base font-medium text-neutral-900 dark:text-white mb-4">Active Campaign Progress</h4>
            <div class="space-y-4">
              <div 
                v-for="campaign in campaigns" 
                :key="campaign.id" 
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4"
              >
                <div class="flex justify-between items-center mb-2">
                  <h5 class="text-sm font-medium text-neutral-900 dark:text-white">{{ campaign.name }}</h5>
                  <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(campaign.endDate) }}</span>
                </div>
                <div class="flex items-center mb-2">
                  <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 mr-2">
                    <div 
                      class="bg-primary-600 h-2.5 rounded-full" 
                      :style="{ width: `${campaign.percentComplete}%` }"
                    ></div>
                  </div>
                  <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                    {{ campaign.percentComplete }}%
                  </span>
                </div>
                <div class="flex justify-between text-xs text-neutral-500 dark:text-neutral-400">
                  <span>${{ formatNumber(campaign.amountRaised) }} raised</span>
                  <span>Goal: ${{ formatNumber(campaign.goal) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useDonationStore } from '../../stores/donations';
import { useToast } from '../../composables/useToast';
import Chart from 'chart.js/auto';

// Services and stores
const donationStore = useDonationStore();
const toast = useToast();

// Component state
const loading = ref(true);
const error = ref(null);
const timeRange = ref('month');
const startDate = ref('');
const endDate = ref('');
const trendChart = ref(null);
const categoryChart = ref(null);
const trendChartInstance = ref(null);
const categoryChartInstance = ref(null);

// Data
const summary = reactive({
  totalAmount: 0,
  totalDonors: 0,
  averageDonation: 0,
  percentChange: 0,
  donorChange: 0,
  averageChange: 0
});

const campaigns = ref([]);

// Methods
const formatNumber = (value) => {
  return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const calculateDateRange = () => {
  const now = new Date();
  let start, end;
  
  switch (timeRange.value) {
    case 'week':
      start = new Date(now);
      start.setDate(now.getDate() - now.getDay());
      end = new Date(now);
      break;
    case 'month':
      start = new Date(now.getFullYear(), now.getMonth(), 1);
      end = new Date(now);
      break;
    case 'quarter':
      const quarter = Math.floor(now.getMonth() / 3);
      start = new Date(now.getFullYear(), quarter * 3, 1);
      end = new Date(now);
      break;
    case 'year':
      start = new Date(now.getFullYear(), 0, 1);
      end = new Date(now);
      break;
    case 'custom':
      start = startDate.value ? new Date(startDate.value) : new Date(now.getFullYear(), now.getMonth(), 1);
      end = endDate.value ? new Date(endDate.value) : new Date(now);
      break;
  }
  
  return { start, end };
};

const refreshData = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const { start, end } = calculateDateRange();
    
    // Format dates for API
    const startStr = start.toISOString().split('T')[0];
    const endStr = end.toISOString().split('T')[0];
    
    // Set date inputs for custom range
    if (timeRange.value === 'custom') {
      startDate.value = startDate.value || startStr;
      endDate.value = endDate.value || endStr;
    }
    
    // Fetch donation data
    const donationData = await donationStore.getDonationInsights(startStr, endStr);
    
    // Update summary data
    Object.assign(summary, donationData.summary);
    
    // Update campaigns data
    campaigns.value = donationData.campaigns || [];
    
    // Update charts
    updateCharts(donationData);
    
    loading.value = false;
  } catch (err) {
    console.error('Error fetching donation insights:', err);
    error.value = 'Failed to load donation insights. Please try again.';
    loading.value = false;
  }
};

const updateCharts = (data) => {
  // Destroy existing charts
  if (trendChartInstance.value) {
    trendChartInstance.value.destroy();
  }
  
  if (categoryChartInstance.value) {
    categoryChartInstance.value.destroy();
  }
  
  // Create trend chart
  if (trendChart.value) {
    const ctx = trendChart.value.getContext('2d');
    
    // Get system theme
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#e5e5e5' : '#374151';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    
    trendChartInstance.value = new Chart(ctx, {
      type: 'line',
      data: {
        labels: data.trends.labels,
        datasets: [{
          label: 'Donations',
          data: data.trends.values,
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
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
            mode: 'index',
            intersect: false,
            callbacks: {
              label: function(context) {
                return `$${context.raw.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              color: gridColor
            },
            ticks: {
              color: textColor
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: gridColor
            },
            ticks: {
              color: textColor,
              callback: function(value) {
                return '$' + value.toLocaleString('en-US');
              }
            }
          }
        }
      }
    });
  }
  
  // Create category chart
  if (categoryChart.value) {
    const ctx = categoryChart.value.getContext('2d');
    
    // Get system theme
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#e5e5e5' : '#374151';
    
    categoryChartInstance.value = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: data.categories.labels,
        datasets: [{
          data: data.categories.values,
          backgroundColor: [
            '#4f46e5', // primary
            '#10b981', // green
            '#f59e0b', // yellow
            '#ef4444', // red
            '#8b5cf6', // purple
            '#06b6d4', // cyan
            '#f97316', // orange
            '#ec4899'  // pink
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: textColor,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const value = context.raw;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `$${value.toLocaleString('en-US', { minimumFractionDigits: 2 })} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  }
};

// Watch for changes in time range
watch(timeRange, () => {
  if (timeRange.value !== 'custom') {
    refreshData();
  }
});

// Watch for changes in custom date range
watch([startDate, endDate], () => {
  if (timeRange.value === 'custom' && startDate.value && endDate.value) {
    refreshData();
  }
});

// Lifecycle hooks
onMounted(() => {
  // Set default date range for custom option
  const now = new Date();
  const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
  
  startDate.value = firstDayOfMonth.toISOString().split('T')[0];
  endDate.value = now.toISOString().split('T')[0];
  
  // Initial data fetch
  refreshData();
  
  // Handle theme changes
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.attributeName === 'class' && document.documentElement.classList.contains('dark') !== undefined) {
        // Theme changed, update charts
        refreshData();
      }
    });
  });
  
  observer.observe(document.documentElement, { attributes: true });
  
  // Clean up
  onBeforeUnmount(() => {
    observer.disconnect();
    if (trendChartInstance.value) {
      trendChartInstance.value.destroy();
    }
    if (categoryChartInstance.value) {
      categoryChartInstance.value.destroy();
    }
  });
});
</script>

<style scoped>
.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-left-color: #4f46e5;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.dark .spinner {
  border-color: rgba(255, 255, 255, 0.1);
  border-left-color: #4f46e5;
}
</style>
