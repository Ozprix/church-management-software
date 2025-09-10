<template>
  <div class="printable-report">
    <!-- Print Header - Only visible when printing -->
    <div class="print-only">
      <div class="flex justify-between items-center mb-8 border-b border-neutral-200 pb-6">
        <div>
          <h1 class="text-2xl font-bold text-neutral-900">{{ reportTitle }}</h1>
          <p class="text-neutral-500 mt-1">{{ formatDateRange }}</p>
          <p v-if="reportDescription" class="text-neutral-600 mt-2 text-sm">{{ reportDescription }}</p>
        </div>
        <div class="text-right">
          <img 
            v-if="churchLogo" 
            :src="churchLogo" 
            alt="Church Logo" 
            class="h-16 inline-block"
          />
          <h2 class="text-xl font-semibold text-neutral-900">{{ churchName }}</h2>
          <p class="text-neutral-500">{{ formatDate(new Date()) }}</p>
          <p v-if="churchAddress" class="text-neutral-500 text-sm">{{ churchAddress }}</p>
        </div>
      </div>
    </div>
    
    <!-- Screen Controls - Only visible on screen -->
    <div class="screen-only mb-6 flex justify-between items-center bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ reportTitle }}</h1>
      <div class="flex space-x-3">
        <button 
          @click="print" 
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Print
        </button>
        <button 
          @click="goBack" 
          class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back
        </button>
      </div>
    </div>
    
    <!-- Applied Filters - Conditionally shown -->
    <div v-if="showFilters && Object.keys(filters).length > 0" class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <h2 class="text-lg font-medium text-neutral-900 dark:text-white mb-3">Applied Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-if="filters.dateRange || filters.startDate" class="text-sm">
          <span class="font-medium text-neutral-700 dark:text-neutral-300">Date Range:</span>
          <span class="text-neutral-600 dark:text-neutral-400 ml-2">{{ formatDateRange }}</span>
        </div>
        <div v-if="filters.categoryIds && filters.categoryIds.length" class="text-sm">
          <span class="font-medium text-neutral-700 dark:text-neutral-300">Categories:</span>
          <span class="text-neutral-600 dark:text-neutral-400 ml-2">{{ formatCategories }}</span>
        </div>
        <div v-if="filters.paymentMethodIds && filters.paymentMethodIds.length" class="text-sm">
          <span class="font-medium text-neutral-700 dark:text-neutral-300">Payment Methods:</span>
          <span class="text-neutral-600 dark:text-neutral-400 ml-2">{{ formatPaymentMethods }}</span>
        </div>
        <div v-if="filters.campaignIds && filters.campaignIds.length" class="text-sm">
          <span class="font-medium text-neutral-700 dark:text-neutral-300">Campaigns:</span>
          <span class="text-neutral-600 dark:text-neutral-400 ml-2">{{ formatCampaigns }}</span>
        </div>
        <div v-if="filters.minAmount || filters.maxAmount" class="text-sm">
          <span class="font-medium text-neutral-700 dark:text-neutral-300">Amount Range:</span>
          <span class="text-neutral-600 dark:text-neutral-400 ml-2">
            {{ filters.minAmount ? formatCurrency(filters.minAmount) : '$0' }} - 
            {{ filters.maxAmount ? formatCurrency(filters.maxAmount) : 'Any' }}
          </span>
        </div>
      </div>
    </div>
    
    <!-- Report Content -->
    <div class="report-content">
      <component 
        :is="reportComponent" 
        v-if="reportComponent"
        :filters="filters"
        :print-mode="true"
      />
      
      <div v-else class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-neutral-800 dark:text-white">Report Not Found</h3>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
          The requested report type is not available or could not be loaded.
        </p>
      </div>
    </div>
    
    <!-- Print Footer - Only visible when printing -->
    <div class="print-only mt-8 pt-4 border-t border-neutral-200 text-center text-neutral-500 text-sm">
      <p>{{ churchName }} - {{ formatDate(new Date()) }}</p>
      <p class="mt-1">This report was generated by the Church Management System</p>
      <p v-if="reportId" class="mt-1">Report ID: {{ reportId }}</p>
      <div v-if="showSignature" class="mt-4 flex justify-center">
        <div class="text-center">
          <div class="border-b border-neutral-400 w-48 mx-auto mb-1"></div>
          <p>Authorized Signature</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useDonationStore } from '../../stores/donations';
import DonationSummaryReport from './DonationSummaryReport.vue';
import DonorContributionReport from './DonorContributionReport.vue';
import PledgeFulfillmentReport from './PledgeFulfillmentReport.vue';
import CampaignProgressReport from './CampaignProgressReport.vue';
import CategoryAnalysisReport from './CategoryAnalysisReport.vue';

const router = useRouter();
const route = useRoute();
const donationStore = useDonationStore();

// State
const reportType = ref('');
const reportId = ref('');
const reportDescription = ref('');
const filters = ref({});
const showFilters = ref(true);
const churchName = ref('Your Church Name');
const churchLogo = ref(null); // URL to church logo
const churchAddress = ref('');
const showSignature = ref(false);

// Get report component based on type
const reportComponent = computed(() => {
  switch (reportType.value) {
    case 'summary':
      return DonationSummaryReport;
    case 'donor':
      return DonorContributionReport;
    case 'pledge':
      return PledgeFulfillmentReport;
    case 'campaign':
      return CampaignProgressReport;
    case 'category':
      return CategoryAnalysisReport;
    default:
      return null;
  }
});

// Get report title based on type
const reportTitle = computed(() => {
  switch (reportType.value) {
    case 'summary':
      return 'Donation Summary Report';
    case 'donor':
      return 'Donor Contribution Report';
    case 'pledge':
      return 'Pledge Fulfillment Report';
    case 'campaign':
      return 'Campaign Progress Report';
    case 'category':
      return 'Category Analysis Report';
    default:
      return 'Donation Report';
  }
});

// Format date range for display
const formatDateRange = computed(() => {
  if (filters.value.dateRange) {
    switch (filters.value.dateRange) {
      case 'today':
        return 'Today';
      case 'yesterday':
        return 'Yesterday';
      case 'this-week':
        return 'This Week';
      case 'last-week':
        return 'Last Week';
      case 'this-month':
        return 'This Month';
      case 'last-month':
        return 'Last Month';
      case 'this-quarter':
        return 'This Quarter';
      case 'last-quarter':
        return 'Last Quarter';
      case 'this-year':
        return 'This Year';
      case 'last-year':
        return 'Last Year';
      case 'all-time':
        return 'All Time';
      default:
        return 'Custom Date Range';
    }
  } else if (filters.value.startDate && filters.value.endDate) {
    return `${formatDate(filters.value.startDate)} - ${formatDate(filters.value.endDate)}`;
  } else if (filters.value.startDate) {
    return `From ${formatDate(filters.value.startDate)}`;
  } else if (filters.value.endDate) {
    return `Until ${formatDate(filters.value.endDate)}`;
  }
  
  return 'All Time';
});

// Format categories for display
const formatCategories = computed(() => {
  if (!filters.value.categoryIds || !filters.value.categoryIds.length) {
    return 'All Categories';
  }
  
  if (filters.value.categoryIds.includes('all')) {
    return 'All Categories';
  }
  
  // In a real implementation, we would fetch category names from the store
  return filters.value.categoryIds.join(', ');
});

// Format payment methods for display
const formatPaymentMethods = computed(() => {
  if (!filters.value.paymentMethodIds || !filters.value.paymentMethodIds.length) {
    return 'All Payment Methods';
  }
  
  if (filters.value.paymentMethodIds.includes('all')) {
    return 'All Payment Methods';
  }
  
  // In a real implementation, we would fetch payment method names from the store
  return filters.value.paymentMethodIds.join(', ');
});

// Format campaigns for display
const formatCampaigns = computed(() => {
  if (!filters.value.campaignIds || !filters.value.campaignIds.length) {
    return 'All Campaigns';
  }
  
  if (filters.value.campaignIds.includes('all')) {
    return 'All Campaigns';
  }
  
  // In a real implementation, we would fetch campaign names from the store
  return filters.value.campaignIds.join(', ');
});

// Methods
const parseQueryParams = () => {
  // Get report type from query params
  reportType.value = route.query.type || 'summary';
  
  // Get report ID if available (for saved reports)
  reportId.value = route.query.id || '';
  
  // Get report description if available
  reportDescription.value = route.query.description || '';
  
  // Parse filters from query params
  if (route.query.filters) {
    try {
      filters.value = JSON.parse(decodeURIComponent(route.query.filters));
    } catch (error) {
      console.error('Error parsing filters:', error);
      filters.value = {};
    }
  }
  
  // Show filters option
  showFilters.value = route.query.showFilters !== 'false';
  
  // If we have a report ID, try to load the saved report
  if (reportId.value) {
    loadSavedReport(reportId.value);
  }
};

const loadChurchInfo = async () => {
  try {
    // Try to load church info from settings
    const settings = await donationStore.getReceiptSettings();
    
    if (settings) {
      churchName.value = settings.orgName || 'Your Church Name';
      churchAddress.value = settings.orgAddress || '';
      showSignature.value = settings.includeSignature || false;
      
      // You would load the church logo from your assets or API if includeLogo is true
      if (settings.includeLogo) {
        // churchLogo.value = '/path/to/logo.png';
      }
    }
  } catch (error) {
    console.error('Error loading church info:', error);
    // Fallback to defaults
    churchName.value = 'Your Church Name';
  }
};

const print = () => {
  window.print();
};

const goBack = () => {
  router.back();
};

const formatDate = (date) => {
  if (!date) return '';
  
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '-';
  
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(value);
};

// Load a saved report by ID
const loadSavedReport = async (id) => {
  try {
    const response = await donationStore.reports.getSavedReportById(id);
    
    if (response && response.data) {
      const savedReport = response.data;
      
      // Update report type and filters from saved report
      reportType.value = savedReport.reportType || reportType.value;
      reportDescription.value = savedReport.description || '';
      
      if (savedReport.filters) {
        filters.value = savedReport.filters;
      }
    }
  } catch (error) {
    console.error('Error loading saved report:', error);
  }
};

// Lifecycle hooks
onMounted(() => {
  parseQueryParams();
  loadChurchInfo();
  
  // Auto-print if specified in query params
  if (route.query.autoPrint === 'true') {
    setTimeout(() => {
      print();
    }, 1000);
  }
});
</script>

<style scoped>
/* Print-specific styles */
@media print {
  .screen-only {
    display: none !important;
  }
  
  .print-only {
    display: block !important;
  }
  
  .printable-report {
    padding: 20px;
    font-family: 'Arial', sans-serif;
  }
  
  /* Ensure charts and tables look good in print */
  .report-content {
    page-break-inside: avoid;
  }
  
  /* Ensure charts are visible */
  canvas {
    max-width: 100%;
    height: auto !important;
  }
  
  /* Remove shadows and borders */
  .bg-white, .dark\:bg-neutral-800, .rounded-lg, .shadow-sm, .border {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
  }
  
  /* Ensure text is visible */
  .text-neutral-900, .dark\:text-white, .text-neutral-700, .dark\:text-neutral-300,
  .text-neutral-600, .dark\:text-neutral-400, .text-neutral-500 {
    color: #000 !important;
  }
}

/* Screen styles */
@media screen {
  .print-only {
    display: none !important;
  }
  
  .printable-report {
    padding: 1rem;
    max-width: 1200px;
    margin: 0 auto;
  }
}
</style>
