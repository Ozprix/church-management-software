<template>
  <div class="donation-reports">
    <!-- Report Type Selection -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 mb-6">
      <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-4">Donation Reports</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div 
          v-for="reportType in reportTypes" 
          :key="reportType.id"
          @click="selectReportType(reportType.id)"
          :class="[
            'cursor-pointer p-4 rounded-lg border transition-colors duration-200',
            selectedReportType === reportType.id 
              ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' 
              : 'border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/50'
          ]"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="reportType.icon" />
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="text-base font-medium text-neutral-900 dark:text-white">{{ reportType.name }}</h4>
              <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                {{ reportType.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Report Filters -->
    <DonationReportFilters 
      v-model:filters="filters"
      @apply-filters="applyFilters"
    />
    
    <!-- Report Content -->
    <div v-if="selectedReportType === 'summary'">
      <DonationSummaryReport :filters="filters" />
    </div>
    
    <div v-else-if="selectedReportType === 'donor'">
      <DonorContributionReport />
    </div>
    
    <div v-else-if="selectedReportType === 'pledge'">
      <PledgeFulfillmentReport />
    </div>
    
    <div v-else-if="selectedReportType === 'campaign'">
      <CampaignProgressReport />
    </div>
    
    <div v-else-if="selectedReportType === 'category'">
      <CategoryAnalysisReport />
    </div>
    
    <!-- Report Actions -->
    <div class="mt-6 flex justify-between">
      <button 
        @click="navigateToSavedReports" 
        class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
        </svg>
        View Saved Reports
      </button>
      
      <button 
        @click="saveReport" 
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
        </svg>
        Save Report
      </button>
    </div>
    
    <!-- Save Report Modal -->
    <div v-if="showSaveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showSaveModal = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="confirmSaveReport">
            <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    Save Report
                  </h3>
                  <div class="mt-4">
                    <label for="report-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      Report Name
                    </label>
                    <input 
                      type="text" 
                      id="report-name" 
                      v-model="saveReportName" 
                      class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      placeholder="Enter a name for this report"
                      required
                    />
                  </div>
                  <div class="mt-4">
                    <label for="report-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      Description (Optional)
                    </label>
                    <textarea 
                      id="report-description" 
                      v-model="saveReportDescription" 
                      rows="3" 
                      class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      placeholder="Add a description for this report"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button 
                type="submit" 
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Save
              </button>
              <button 
                type="button" 
                @click="showSaveModal = false" 
                class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useReportsStore } from '../../stores/reports';
import { useDonationStore } from '../../stores/donations';
import { useToastService } from '../../services/toastService';
import DonationReportFilters from './DonationReportFilters.vue';
import DonationSummaryReport from './DonationSummaryReport.vue';
import DonorContributionReport from './DonorContributionReport.vue';
import PledgeFulfillmentReport from './PledgeFulfillmentReport.vue';
import CampaignProgressReport from './CampaignProgressReport.vue';
import CategoryAnalysisReport from './CategoryAnalysisReport.vue';

// Store
const reportsStore = useReportsStore();
const donationStore = useDonationStore();
const toast = useToastService();

// State
const selectedReportType = ref('summary');
const filters = reactive({
  dateRange: 'this-month',
  startDate: null,
  endDate: null,
  categoryIds: ['all'],
  paymentMethodIds: ['all'],
  campaignIds: ['all'],
  minAmount: null,
  maxAmount: null,
  recurringOnly: false,
  taxDeductibleOnly: false,
  receiptSent: null
});

const showSaveModal = ref(false);
const saveReportName = ref('');
const saveReportDescription = ref('');

// Report types
const reportTypes = [
  {
    id: 'summary',
    name: 'Donation Summary',
    description: 'Overview of all donations with key metrics and trends',
    icon: 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'
  },
  {
    id: 'donor',
    name: 'Donor Contributions',
    description: 'Detailed contribution history for individual donors',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
  },
  {
    id: 'pledge',
    name: 'Pledge Fulfillment',
    description: 'Track pledge progress and fulfillment status',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
  },
  {
    id: 'campaign',
    name: 'Campaign Progress',
    description: 'Monitor fundraising campaign progress and metrics',
    icon: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'
  },
  {
    id: 'category',
    name: 'Category Analysis',
    description: 'Analyze donations by category over time',
    icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'
  }
];

// Methods
const selectReportType = (reportType) => {
  selectedReportType.value = reportType;
};

const applyFilters = (newFilters) => {
  Object.assign(filters, newFilters);
};

const saveReport = () => {
  showSaveModal.value = true;
};

const navigateToSavedReports = () => {
  router.push({ name: 'saved-reports' });
};

const confirmSaveReport = async () => {
  if (!saveReportName.value.trim()) {
    return;
  }
  
  try {
    // Prepare report data
    const reportData = {
      name: saveReportName.value,
      description: saveReportDescription.value,
      reportType: selectedReportType.value,
      filters: { ...filters },
    };
    
    // Call API to save the report
    await donationStore.reports.saveReport(reportData);
    
    // Close modal
    showSaveModal.value = false;
    
    // Reset form
    saveReportName.value = '';
    saveReportDescription.value = '';
    
    // Show success message with link to saved reports
    toast.success('Report saved successfully. View it in your saved reports.');
  } catch (error) {
    console.error('Error saving report:', error);
    toast.error('Failed to save report');
  }
};

// Helper functions
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

const calculateCategoryBreakdown = (donations) => {
  const breakdown = {};
  
  donations.forEach(donation => {
    const categoryId = donation.categoryId;
    if (!breakdown[categoryId]) {
      const category = donationStore.donationCategories.find(c => c.id === categoryId);
      breakdown[categoryId] = {
        id: categoryId,
        name: category?.name || 'Unknown',
        color: category?.color || '#cccccc',
        amount: 0,
        count: 0
      };
    }
    
    breakdown[categoryId].amount += donation.amount;
    breakdown[categoryId].count += 1;
  });
  
  return Object.values(breakdown);
};

const calculatePaymentMethodBreakdown = (donations) => {
  const breakdown = {};
  
  donations.forEach(donation => {
    const methodId = donation.paymentMethodId;
    if (!breakdown[methodId]) {
      const method = donationStore.paymentMethods.find(m => m.id === methodId);
      breakdown[methodId] = {
        id: methodId,
        name: method?.name || 'Unknown',
        amount: 0,
        count: 0
      };
    }
    
    breakdown[methodId].amount += donation.amount;
    breakdown[methodId].count += 1;
  });
  
  return Object.values(breakdown);
};

const calculateMonthlyTrend = (donations) => {
  const trend = {};
  
  // Sort donations by date
  const sortedDonations = [...donations].sort((a, b) => new Date(a.date) - new Date(b.date));
  
  sortedDonations.forEach(donation => {
    const date = new Date(donation.date);
    const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    
    if (!trend[monthKey]) {
      trend[monthKey] = {
        month: date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
        amount: 0,
        count: 0
      };
    }
    
    trend[monthKey].amount += donation.amount;
    trend[monthKey].count += 1;
  });
  
  return Object.values(trend);
};
</script>
