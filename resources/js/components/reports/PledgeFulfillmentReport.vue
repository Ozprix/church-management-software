<template>
  <div class="pledge-fulfillment-report">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-neutral-800 dark:text-white">Pledge Fulfillment Report</h3>
        
        <!-- Export Options -->
        <div class="flex space-x-2">
          <button 
            @click="showExportOptions = true"
            class="inline-flex items-center px-3 py-1.5 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <i class="fas fa-file-export mr-2"></i>
            Export
          </button>
          <button 
            @click="showHelpTour"
            class="inline-flex items-center px-3 py-1.5 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <i class="fas fa-question-circle mr-2"></i>
            Help
          </button>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Campaign</label>
          <div class="relative">
            <select 
              v-model="selectedCampaignId"
              class="block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
            >
              <option value="">All Campaigns</option>
              <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                {{ campaign.name }}
              </option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
          <div class="relative">
            <select 
              v-model="selectedStatus"
              class="block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
            >
              <option value="">All Statuses</option>
              <option value="fulfilled">Fulfilled</option>
              <option value="in_progress">In Progress</option>
              <option value="at_risk">At Risk</option>
              <option value="unfulfilled">Unfulfilled</option>
            </select>
          </div>
        </div>
        
        <div class="flex items-end">
          <button 
            @click="loadPledgeData"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Apply Filters
          </button>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>
      
      <!-- No Data State -->
      <div v-else-if="!pledges.length" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No pledges found</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Try adjusting your filters or check back later.
        </p>
      </div>
      
      <!-- Pledge Data -->
      <div v-else>
        <!-- Summary Cards -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Total Pledges</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summary.totalPledged) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ summary.pledgeCount }} pledges</p>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Amount Received</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summary.amountReceived) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ formatPercentage(summary.fulfillmentRate) }} of total</p>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Amount Remaining</h4>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(summary.amountRemaining) }}</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ formatPercentage(100 - summary.fulfillmentRate) }} of total</p>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Fulfillment Rate</h4>
            <div class="flex items-center">
              <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ formatPercentage(summary.fulfillmentRate) }}</p>
              <div class="ml-2 flex-shrink-0">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  summary.fulfillmentRate >= 90 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                  summary.fulfillmentRate >= 70 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                ]">
                  {{ summary.fulfillmentRate >= 90 ? 'Excellent' : summary.fulfillmentRate >= 70 ? 'Good' : 'Needs Attention' }}
                </span>
              </div>
            </div>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Overall performance</p>
          </div>
        </div>
        
        <!-- Fulfillment Progress Chart -->
        <div class="mb-6">
          <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Fulfillment Progress</h4>
          <div class="h-64 bg-white dark:bg-neutral-800">
            <canvas ref="fulfillmentChart"></canvas>
          </div>
        </div>
        
        <!-- Pledge Status Breakdown -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Pledge Status Distribution</h4>
            <div class="h-64 bg-white dark:bg-neutral-800">
              <canvas ref="statusChart"></canvas>
            </div>
          </div>
          <div>
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Status Breakdown</h4>
            <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
              <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Count</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Percentage</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                  <tr v-for="(status, index) in statusBreakdown" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        status.name === 'Fulfilled' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                        status.name === 'In Progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                        status.name === 'At Risk' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                      ]">
                        {{ status.name }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ status.count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(status.amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatPercentage(status.percentage) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Pledge List -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-medium text-neutral-800 dark:text-white">Pledge Details</h4>
            
            <!-- Search and Filter -->
            <div class="flex space-x-2">
              <div class="relative">
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Search donors..." 
                  class="block w-full pl-10 pr-3 py-1.5 border border-neutral-300 dark:border-neutral-600 rounded-md text-sm placeholder-neutral-500 dark:placeholder-neutral-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-neutral-400 dark:text-neutral-500"></i>
                </div>
              </div>
              <select 
                v-model="sortBy" 
                class="form-select text-sm rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
              >
                <option value="donor_name">Sort by Name</option>
                <option value="amount">Sort by Amount</option>
                <option value="progress">Sort by Progress</option>
                <option value="status">Sort by Status</option>
              </select>
            </div>
          </div>
          <div class="overflow-hidden border border-neutral-200 dark:border-neutral-700 rounded-lg">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
              <thead class="bg-neutral-50 dark:bg-neutral-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Donor</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Campaign</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Pledged</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Received</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Remaining</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Progress</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                <tr v-for="pledge in filteredAndSortedPledges" :key="pledge.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">{{ pledge.donor_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ pledge.campaign_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(pledge.amount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(pledge.amount_received) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">{{ formatCurrency(pledge.amount - pledge.amount_received) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5">
                      <div class="h-2.5 rounded-full" 
                        :style="{ width: `${(pledge.amount_received / pledge.amount) * 100}%` }"
                        :class="[
                          (pledge.amount_received / pledge.amount) >= 0.9 ? 'bg-green-500' :
                          (pledge.amount_received / pledge.amount) >= 0.7 ? 'bg-yellow-500' :
                          'bg-red-500'
                        ]">
                      </div>
                    </div>
                    <div class="text-xs text-center mt-1 text-neutral-500 dark:text-neutral-400">
                      {{ formatPercentage((pledge.amount_received / pledge.amount) * 100) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      pledge.status === 'fulfilled' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                      pledge.status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                      pledge.status === 'at_risk' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                    ]">
                      {{ formatStatus(pledge.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Export Options Modal -->
    <Modal 
      v-if="showExportOptions" 
      title="Export Report"
      @close="showExportOptions = false"
    >
      <div class="space-y-4 p-2">
        <div>
          <h4 class="text-base font-medium text-neutral-800 dark:text-white mb-2">Export Format</h4>
          <div class="grid grid-cols-3 gap-3">
            <div 
              v-for="format in exportFormats" 
              :key="format.id"
              @click="selectedExportFormat = format.id"
              class="border rounded-lg p-3 cursor-pointer transition-colors duration-200 flex flex-col items-center justify-center"
              :class="selectedExportFormat === format.id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700'"
            >
              <i :class="[format.icon, 'text-2xl mb-2', selectedExportFormat === format.id ? 'text-primary-500' : 'text-neutral-500 dark:text-neutral-400']"></i>
              <span :class="selectedExportFormat === format.id ? 'text-primary-700 dark:text-primary-300' : 'text-neutral-700 dark:text-neutral-300'">{{ format.name }}</span>
            </div>
          </div>
        </div>
        
        <div>
          <h4 class="text-base font-medium text-neutral-800 dark:text-white mb-2">Export Options</h4>
          <div class="space-y-2">
            <label class="flex items-center">
              <input type="checkbox" v-model="exportOptions.includeSummary" class="form-checkbox h-4 w-4 text-primary-600 rounded focus:ring-primary-500 dark:focus:ring-offset-neutral-800">
              <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Include Summary</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" v-model="exportOptions.includeCharts" class="form-checkbox h-4 w-4 text-primary-600 rounded focus:ring-primary-500 dark:focus:ring-offset-neutral-800">
              <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Include Charts (PDF only)</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" v-model="exportOptions.includeDetails" class="form-checkbox h-4 w-4 text-primary-600 rounded focus:ring-primary-500 dark:focus:ring-offset-neutral-800">
              <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Include Pledge Details</span>
            </label>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <button 
            @click="showExportOptions = false" 
            class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Cancel
          </button>
          <button 
            @click="exportReport" 
            class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Export
          </button>
        </div>
      </template>
    </Modal>
    
    <!-- Feature Tour Component -->
    <FeatureTour
      ref="featureTour"
      tour-id="pledgeFulfillmentReport"
      :steps="tourSteps"
      :auto-start="false"
      @complete="onTourComplete"
      @skip="onTourSkip"
    />
    
    <!-- Contextual Help -->
    <ContextualHelp />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useDonationsStore } from '../../stores/donations';
import { useOnboardingStore } from '../../stores/onboarding';
import { useToast } from '../../composables/useToast';
import Modal from '../../components/ui/Modal.vue';
import FeatureTour from '../../components/onboarding/FeatureTour.vue';
import ContextualHelp from '../../components/onboarding/ContextualHelp.vue';
import Chart from 'chart.js/auto';

const donationsStore = useDonationsStore();

// State
const selectedCampaignId = ref('');
const selectedStatus = ref('');
const campaigns = ref([]);
const pledges = ref([]);
const loading = ref(false);
const fulfillmentChart = ref(null);
const statusChart = ref(null);
let fulfillmentChartInstance = null;
let statusChartInstance = null;

// Export formats
const exportFormats = [
  { id: 'pdf', name: 'PDF', icon: 'fas fa-file-pdf' },
  { id: 'excel', name: 'Excel', icon: 'fas fa-file-excel' },
  { id: 'csv', name: 'CSV', icon: 'fas fa-file-csv' }
];

// Tour steps
const tourSteps = [
  {
    title: 'Pledge Fulfillment Report',
    description: 'This report helps you track the progress of pledge fulfillment across all campaigns.',
    target: '.pledge-fulfillment-report',
    position: 'bottom'
  },
  {
    title: 'Filtering Options',
    description: 'Filter the report by campaign or pledge status to focus on specific data.',
    target: '.pledge-fulfillment-report .grid.grid-cols-1.md\\:grid-cols-3',
    position: 'bottom'
  },
  {
    title: 'Summary Cards',
    description: 'These cards provide a quick overview of your pledge fulfillment metrics.',
    target: '.pledge-fulfillment-report .grid.grid-cols-1.md\\:grid-cols-4',
    position: 'bottom'
  },
  {
    title: 'Progress Charts',
    description: 'Visual representations of your pledge fulfillment data help identify trends and patterns.',
    target: '.pledge-fulfillment-report canvas',
    position: 'right'
  },
  {
    title: 'Pledge Details',
    description: 'View detailed information about each pledge, including progress and status.',
    target: '.pledge-fulfillment-report table',
    position: 'top'
  },
  {
    title: 'Export Options',
    description: 'Export your report in various formats to share with church leadership.',
    target: '.pledge-fulfillment-report button:first-of-type',
    position: 'left'
  }
];

// Computed
const summary = computed(() => {
  if (!pledges.value.length) {
    return {
      totalPledged: 0,
      amountReceived: 0,
      amountRemaining: 0,
      fulfillmentRate: 0,
      pledgeCount: 0
    };
  }
  
  const totalPledged = pledges.value.reduce((sum, pledge) => sum + parseFloat(pledge.amount), 0);
  const amountReceived = pledges.value.reduce((sum, pledge) => sum + parseFloat(pledge.amount_received), 0);
  const amountRemaining = totalPledged - amountReceived;
  const fulfillmentRate = (amountReceived / totalPledged) * 100;
  
  return {
    totalPledged,
    amountReceived,
    amountRemaining,
    fulfillmentRate,
    pledgeCount: pledges.value.length
  };
});

// Filtered and sorted pledges
const filteredAndSortedPledges = computed(() => {
  // First filter by search query
  let result = pledges.value;
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(pledge => 
      pledge.donor_name.toLowerCase().includes(query)
    );
  }
  
  // Then sort
  return result.sort((a, b) => {
    if (sortBy.value === 'donor_name') {
      return a.donor_name.localeCompare(b.donor_name);
    } else if (sortBy.value === 'amount') {
      return b.amount - a.amount;
    } else if (sortBy.value === 'progress') {
      const progressA = a.amount_received / a.amount;
      const progressB = b.amount_received / b.amount;
      return progressB - progressA;
    } else if (sortBy.value === 'status') {
      // Sort by status priority: fulfilled, in_progress, at_risk, unfulfilled
      const statusOrder = { fulfilled: 0, in_progress: 1, at_risk: 2, unfulfilled: 3 };
      return statusOrder[a.status] - statusOrder[b.status];
    }
    return 0;
  });
});

const statusBreakdown = computed(() => {
  if (!pledges.value.length) return [];
  
  const statuses = {
    fulfilled: { name: 'Fulfilled', count: 0, amount: 0 },
    in_progress: { name: 'In Progress', count: 0, amount: 0 },
    at_risk: { name: 'At Risk', count: 0, amount: 0 },
    unfulfilled: { name: 'Unfulfilled', count: 0, amount: 0 }
  };
  
  const totalAmount = pledges.value.reduce((sum, pledge) => sum + parseFloat(pledge.amount), 0);
  
  pledges.value.forEach(pledge => {
    statuses[pledge.status].count++;
    statuses[pledge.status].amount += parseFloat(pledge.amount);
  });
  
  return Object.values(statuses).map(status => ({
    ...status,
    percentage: (status.amount / totalAmount) * 100
  })).filter(status => status.count > 0);
});

// Methods
const showHelpTour = () => {
  if (featureTour.value) {
    featureTour.value.startTour();
  }
};

const onTourComplete = () => {
  toast.success('Tour completed! You now know how to use the Pledge Fulfillment Report.');
  onboardingStore.markFeatureTourCompleted('pledgeFulfillmentReport');
};

const onTourSkip = () => {
  toast.info('Tour skipped. You can restart it anytime from the Help button.');
};

const exportReport = async () => {
  try {
    // Show loading toast
    toast.info('Preparing export...', { duration: 3000 });
    
    // Call the export function from the donations store
    await donationsStore.exportPledgeFulfillmentReport({
      format: selectedExportFormat.value,
      campaignId: selectedCampaignId.value,
      status: selectedStatus.value,
      options: exportOptions.value
    });
    
    // Close modal and show success message
    showExportOptions.value = false;
    toast.success(`Report exported successfully as ${selectedExportFormat.value.toUpperCase()}`);
  } catch (error) {
    console.error('Export error:', error);
    toast.error('Failed to export report. Please try again.');
  }
};

const loadCampaigns = async () => {
  try {
    const response = await donationsStore.getCampaigns();
    campaigns.value = response.data;
  } catch (error) {
    console.error('Error loading campaigns:', error);
  }
};

const loadPledgeData = async () => {
  loading.value = true;
  
  try {
    const params = {};
    
    if (selectedCampaignId.value) {
      params.campaign_id = selectedCampaignId.value;
    }
    
    if (selectedStatus.value) {
      params.status = selectedStatus.value;
    }
    
    const response = await donationsStore.getPledges(params);
    pledges.value = response.data;
    renderCharts();
  } catch (error) {
    console.error('Error loading pledge data:', error);
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  if (pledges.value.length === 0) return;
  
  // Destroy existing chart instances to prevent duplicates
  if (fulfillmentChartInstance) {
    fulfillmentChartInstance.destroy();
  }
  
  if (statusChartInstance) {
    statusChartInstance.destroy();
  }
  
  // Render fulfillment progress chart
  const campaignData = prepareCampaignData();
  
  fulfillmentChartInstance = new Chart(fulfillmentChart.value, {
    type: 'bar',
    data: {
      labels: campaignData.labels,
      datasets: [
        {
          label: 'Pledged',
          data: campaignData.pledged,
          backgroundColor: 'rgba(107, 114, 128, 0.5)',
          borderColor: 'rgb(107, 114, 128)',
          borderWidth: 1
        },
        {
          label: 'Received',
          data: campaignData.received,
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgb(59, 130, 246)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
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
  
  // Render status distribution chart
  statusChartInstance = new Chart(statusChart.value, {
    type: 'doughnut',
    data: {
      labels: statusBreakdown.value.map(s => s.name),
      datasets: [{
        data: statusBreakdown.value.map(s => s.amount),
        backgroundColor: [
          '#10b981', // Fulfilled - Green
          '#3b82f6', // In Progress - Blue
          '#f59e0b', // At Risk - Yellow
          '#ef4444'  // Unfulfilled - Red
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

const prepareCampaignData = () => {
  // Group pledges by campaign
  const campaignPledges = {};
  
  pledges.value.forEach(pledge => {
    const campaignName = pledge.campaign_name;
    
    if (!campaignPledges[campaignName]) {
      campaignPledges[campaignName] = {
        pledged: 0,
        received: 0
      };
    }
    
    campaignPledges[campaignName].pledged += parseFloat(pledge.amount);
    campaignPledges[campaignName].received += parseFloat(pledge.amount_received);
  });
  
  // Prepare data for chart
  const labels = Object.keys(campaignPledges);
  const pledged = labels.map(campaign => campaignPledges[campaign].pledged);
  const received = labels.map(campaign => campaignPledges[campaign].received);
  
  return { labels, pledged, received };
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

const formatStatus = (status) => {
  const statusMap = {
    fulfilled: 'Fulfilled',
    in_progress: 'In Progress',
    at_risk: 'At Risk',
    unfulfilled: 'Unfulfilled'
  };
  
  return statusMap[status] || status;
};

// Lifecycle hooks
onMounted(() => {
  loadCampaigns();
  loadPledgeData();
  
  // Auto-start tour if it hasn't been completed yet
  if (!onboardingStore.featureTours.pledgeFulfillmentReport) {
    // Delay to ensure the page is fully rendered
    setTimeout(() => {
      showHelpTour();
    }, 1000);
  }
});

// Watch for filter changes
watch([selectedCampaignId, selectedStatus], () => {
  // We don't auto-load here because we have an Apply button
});
</script>
