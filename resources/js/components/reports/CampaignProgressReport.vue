<template>
  <div class="campaign-progress-report">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
      <h3 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4">Campaign Progress Report</h3>
      
      <!-- Campaign Selection -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Select Campaign</label>
        <div class="relative">
          <select 
            v-model="selectedCampaignId"
            class="block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
            @change="loadCampaignData"
          >
            <option value="">Select a campaign</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }}
            </option>
          </select>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>
      
      <!-- No Data State -->
      <div v-else-if="!selectedCampaignId || !campaignData" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No campaign data available</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          {{ selectedCampaignId ? 'No data found for this campaign.' : 'Select a campaign to view progress details.' }}
        </p>
      </div>
      
      <!-- Campaign Data -->
      <div v-else>
        <!-- Campaign Header -->
        <div class="mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ campaignData.name }}</h2>
              <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                {{ formatDateRange(campaignData.start_date, campaignData.end_date) }}
              </p>
            </div>
            <div class="mt-4 md:mt-0">
              <span :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                campaignData.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                campaignData.status === 'upcoming' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                campaignData.status === 'completed' ? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' :
                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
              ]">
                {{ formatStatus(campaignData.status) }}
              </span>
            </div>
          </div>
          <p class="mt-2 text-neutral-700 dark:text-neutral-300">{{ campaignData.description }}</p>
        </div>
        
        <!-- Campaign Progress -->
        <div class="mb-6">
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 border border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-4">
              <div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Fundraising Progress</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                  {{ formatPercentage(campaignData.progress) }} of {{ formatCurrency(campaignData.goal_amount) }} goal
                </p>
              </div>
              <div class="mt-2 md:mt-0 text-right">
                <p class="text-3xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(campaignData.amount_raised) }}</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                  {{ formatCurrency(campaignData.goal_amount - campaignData.amount_raised) }} remaining
                </p>
              </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-4 mb-2">
              <div 
                class="h-4 rounded-full" 
                :style="{ width: `${Math.min(campaignData.progress, 100)}%` }"
                :class="[
                  campaignData.progress >= 90 ? 'bg-green-500' :
                  campaignData.progress >= 50 ? 'bg-blue-500' :
                  campaignData.progress >= 25 ? 'bg-yellow-500' :
                  'bg-red-500'
                ]"
              ></div>
            </div>
            
            <!-- Time Remaining -->
            <div class="mt-4 flex justify-between text-sm text-neutral-500 dark:text-neutral-400">
              <span>Started {{ formatDate(campaignData.start_date) }}</span>
              <span>{{ campaignData.days_remaining > 0 ? `${campaignData.days_remaining} days remaining` : 'Campaign ended' }}</span>
            </div>
          </div>
        </div>
        
        <!-- Summary Stats -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Total Donors</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ campaignData.donor_count }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
              {{ campaignData.new_donor_count }} new donors
            </p>
          </div>
          <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Total Donations</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ campaignData.donation_count }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
              Avg {{ formatCurrency(campaignData.average_donation) }}
            </p>
          </div>
          <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Largest Donation</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(campaignData.largest_donation) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
              {{ formatDate(campaignData.largest_donation_date) }}
            </p>
          </div>
          <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Pledges</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(campaignData.pledged_amount) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
              {{ campaignData.pledge_count }} active pledges
            </p>
          </div>
        </div>
        
        <!-- Donation Trend Chart -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Donation Trend</h4>
          <div class="h-64 bg-white dark:bg-neutral-800">
            <canvas ref="trendChart"></canvas>
          </div>
        </div>
        
        <!-- Donation Source Breakdown -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Donation Sources</h4>
            <div class="h-64 bg-white dark:bg-neutral-800">
              <canvas ref="sourceChart"></canvas>
            </div>
          </div>
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Payment Methods</h4>
            <div class="h-64 bg-white dark:bg-neutral-800">
              <canvas ref="methodChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Top Donors -->
        <div>
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Top Donors</h4>
          <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
              <thead class="bg-neutral-50 dark:bg-neutral-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Donor</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Donations</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Last Donation</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                <tr v-for="donor in campaignData.top_donors" :key="donor.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">{{ donor.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(donor.total_amount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ donor.donation_count }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatDate(donor.last_donation_date) }}</td>
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
import { ref, onMounted, watch } from 'vue';
import { useDonationsStore } from '../../stores/donations';
import Chart from 'chart.js/auto';

const donationsStore = useDonationsStore();

// State
const selectedCampaignId = ref('');
const campaigns = ref([]);
const campaignData = ref(null);
const loading = ref(false);
const trendChart = ref(null);
const sourceChart = ref(null);
const methodChart = ref(null);
let trendChartInstance = null;
let sourceChartInstance = null;
let methodChartInstance = null;

// Methods
const loadCampaigns = async () => {
  try {
    const response = await donationsStore.getCampaigns();
    campaigns.value = response.data;
  } catch (error) {
    console.error('Error loading campaigns:', error);
  }
};

const loadCampaignData = async () => {
  if (!selectedCampaignId.value) {
    campaignData.value = null;
    return;
  }
  
  loading.value = true;
  
  try {
    const response = await donationsStore.getCampaignDetails(selectedCampaignId.value);
    campaignData.value = response.data;
    renderCharts();
  } catch (error) {
    console.error('Error loading campaign data:', error);
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  if (!campaignData.value) return;
  
  // Destroy existing chart instances to prevent duplicates
  if (trendChartInstance) {
    trendChartInstance.destroy();
  }
  
  if (sourceChartInstance) {
    sourceChartInstance.destroy();
  }
  
  if (methodChartInstance) {
    methodChartInstance.destroy();
  }
  
  // Render donation trend chart
  const trendData = prepareTrendData();
  
  trendChartInstance = new Chart(trendChart.value, {
    type: 'line',
    data: {
      labels: trendData.labels,
      datasets: [{
        label: 'Daily Donations',
        data: trendData.data,
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
  
  // Render donation source chart
  sourceChartInstance = new Chart(sourceChart.value, {
    type: 'doughnut',
    data: {
      labels: campaignData.value.sources.map(s => s.name),
      datasets: [{
        data: campaignData.value.sources.map(s => s.amount),
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
  
  // Render payment method chart
  methodChartInstance = new Chart(methodChart.value, {
    type: 'pie',
    data: {
      labels: campaignData.value.payment_methods.map(m => m.name),
      datasets: [{
        data: campaignData.value.payment_methods.map(m => m.amount),
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

const prepareTrendData = () => {
  const trendData = campaignData.value.donation_trend;
  return {
    labels: trendData.map(item => formatDate(item.date, true)),
    data: trendData.map(item => item.amount)
  };
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

const formatDate = (dateString, short = false) => {
  if (!dateString) return '-';
  
  const date = new Date(dateString);
  
  if (short) {
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric'
    }).format(date);
  }
  
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(date);
};

const formatDateRange = (startDate, endDate) => {
  if (!startDate || !endDate) return '';
  
  const start = formatDate(startDate);
  const end = formatDate(endDate);
  
  return `${start} - ${end}`;
};

const formatStatus = (status) => {
  const statusMap = {
    active: 'Active',
    upcoming: 'Upcoming',
    completed: 'Completed',
    cancelled: 'Cancelled'
  };
  
  return statusMap[status] || status;
};

// Watch for changes to selectedCampaignId
watch(selectedCampaignId, () => {
  loadCampaignData();
});

// Lifecycle hooks
onMounted(() => {
  loadCampaigns();
});
</script>
