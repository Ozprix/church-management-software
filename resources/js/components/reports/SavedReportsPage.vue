<template>
  <div class="saved-reports-page">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Saved Reports</h2>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Access your saved reports for quick reference and sharing
          </p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="relative">
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search reports..." 
              class="block w-full pl-10 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md leading-5 bg-white dark:bg-neutral-900 placeholder-neutral-500 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:text-white sm:text-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="!savedReports.length" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No saved reports</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          You haven't saved any reports yet. Generate a report and save it for quick access later.
        </p>
        <div class="mt-6">
          <button 
            @click="navigateToReports" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create a Report
          </button>
        </div>
      </div>
      
      <!-- Reports List -->
      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="report in filteredReports" 
            :key="report.id"
            class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
          >
            <div class="p-5">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-lg font-medium text-neutral-900 dark:text-white">{{ report.name }}</h3>
                  <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ formatReportType(report.reportType) }}
                  </p>
                </div>
                <div class="relative" ref="menuRefs">
                  <button 
                    @click="toggleMenu(report.id)" 
                    class="p-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-800 focus:outline-none"
                  >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>
                  <div 
                    v-if="activeMenu === report.id" 
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 z-10"
                  >
                    <div class="py-1">
                      <button 
                        @click="viewReport(report)" 
                        class="w-full text-left block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                      >
                        <div class="flex items-center">
                          <svg class="mr-3 h-5 w-5 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                          View Report
                        </div>
                      </button>
                      <button 
                        @click="exportReport(report, 'pdf')" 
                        class="w-full text-left block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                      >
                        <div class="flex items-center">
                          <svg class="mr-3 h-5 w-5 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                          </svg>
                          Export as PDF
                        </div>
                      </button>
                      <button 
                        @click="printReport(report)" 
                        class="w-full text-left block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                      >
                        <div class="flex items-center">
                          <svg class="mr-3 h-5 w-5 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                          </svg>
                          Print Report
                        </div>
                      </button>
                      <div class="border-t border-neutral-200 dark:border-neutral-700"></div>
                      <button 
                        @click="confirmDeleteReport(report)" 
                        class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                      >
                        <div class="flex items-center">
                          <svg class="mr-3 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Delete Report
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mt-4">
                <p class="text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2">
                  {{ report.description || 'No description provided' }}
                </p>
              </div>
              
              <div class="mt-4 flex items-center text-xs text-neutral-500 dark:text-neutral-400">
                <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Created {{ formatDate(report.createdAt) }}</span>
              </div>
              
              <div class="mt-6 flex justify-between items-center">
                <div>
                  <span 
                    v-if="report.filters && report.filters.dateRange"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                  >
                    {{ formatDateRange(report.filters) }}
                  </span>
                </div>
                <button 
                  @click="viewReport(report)" 
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                  View Report
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  Delete Report
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Are you sure you want to delete this report? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deleteReport" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button 
              type="button" 
              @click="showDeleteModal = false" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Loading Overlay -->
    <div v-if="isExporting" class="fixed inset-0 bg-neutral-800 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 max-w-sm w-full">
        <div class="flex items-center justify-center mb-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
        </div>
        <h3 class="text-lg font-medium text-center text-neutral-900 dark:text-white">{{ exportingMessage }}</h3>
        <p class="mt-2 text-sm text-center text-neutral-500 dark:text-neutral-400">
          Please wait while we process your request...
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useDonationStore } from '../../stores/donations';
import { useToastService } from '../../services/toastService';

const router = useRouter();
const donationStore = useDonationStore();
const toast = useToastService();

// State
const savedReports = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const activeMenu = ref(null);
const menuRefs = ref([]);
const showDeleteModal = ref(false);
const reportToDelete = ref(null);
const isExporting = ref(false);
const exportingMessage = ref('');

// Computed
const filteredReports = computed(() => {
  if (!searchQuery.value) {
    return savedReports.value;
  }
  
  const query = searchQuery.value.toLowerCase();
  return savedReports.value.filter(report => 
    report.name.toLowerCase().includes(query) || 
    (report.description && report.description.toLowerCase().includes(query)) ||
    formatReportType(report.reportType).toLowerCase().includes(query)
  );
});

// Methods
const loadSavedReports = async () => {
  loading.value = true;
  
  try {
    const response = await donationStore.reports.getSavedReports();
    if (response && response.data) {
      savedReports.value = response.data;
    }
  } catch (error) {
    console.error('Error loading saved reports:', error);
    toast.error('Failed to load saved reports');
  } finally {
    loading.value = false;
  }
};

const toggleMenu = (reportId) => {
  if (activeMenu.value === reportId) {
    activeMenu.value = null;
  } else {
    activeMenu.value = reportId;
  }
};

const handleClickOutside = (event) => {
  if (activeMenu.value !== null && !event.target.closest('.relative')) {
    activeMenu.value = null;
  }
};

const viewReport = (report) => {
  activeMenu.value = null;
  
  // Navigate to the appropriate report view with the saved filters
  router.push({
    name: 'reports',
    query: {
      type: report.reportType,
      savedReportId: report.id
    }
  });
};

const exportReport = async (report, format) => {
  activeMenu.value = null;
  isExporting.value = true;
  exportingMessage.value = `Preparing ${format.toUpperCase()} export`;
  
  try {
    // Call API to export the report
    const response = await donationStore.reports.exportReport(format, {
      reportType: report.reportType,
      ...report.filters,
      savedReportId: report.id
    });
    
    // Download the file
    downloadFile(response, format, report.name);
    
    toast.success(`Report successfully exported as ${format.toUpperCase()}`);
  } catch (error) {
    console.error(`Error exporting report as ${format}:`, error);
    toast.error(`Failed to export report: ${error.message || 'Unknown error'}`);
  } finally {
    isExporting.value = false;
  }
};

const printReport = (report) => {
  activeMenu.value = null;
  
  // Create query params for the print view
  const queryParams = new URLSearchParams({
    reportType: report.reportType,
    savedReportId: report.id,
    autoPrint: 'true'
  });
  
  // Add filters if available
  if (report.filters) {
    Object.entries(report.filters).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        if (Array.isArray(value)) {
          queryParams.append(key, value.join(','));
        } else {
          queryParams.append(key, value);
        }
      }
    });
  }
  
  // Open print view in new window
  const printWindow = window.open(`/reports/print?${queryParams.toString()}`, '_blank');
  
  if (!printWindow) {
    toast.error('Pop-up blocked. Please allow pop-ups for this site to use the print feature.');
  }
};

const confirmDeleteReport = (report) => {
  activeMenu.value = null;
  reportToDelete.value = report;
  showDeleteModal.value = true;
};

const deleteReport = async () => {
  if (!reportToDelete.value) return;
  
  try {
    await donationStore.reports.deleteSavedReport(reportToDelete.value.id);
    
    // Remove from local array
    const index = savedReports.value.findIndex(r => r.id === reportToDelete.value.id);
    if (index !== -1) {
      savedReports.value.splice(index, 1);
    }
    
    toast.success('Report deleted successfully');
  } catch (error) {
    console.error('Error deleting report:', error);
    toast.error('Failed to delete report');
  } finally {
    showDeleteModal.value = false;
    reportToDelete.value = null;
  }
};

const navigateToReports = () => {
  router.push({ name: 'reports' });
};

const downloadFile = (response, format, reportName) => {
  // Create a blob from the response data
  const blob = new Blob([response], { 
    type: format === 'pdf' ? 'application/pdf' : 
          format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 
          'text/csv'
  });
  
  // Create a URL for the blob
  const url = window.URL.createObjectURL(blob);
  
  // Create a temporary link element
  const link = document.createElement('a');
  link.href = url;
  
  // Set the filename based on report name and format
  const date = new Date().toISOString().split('T')[0];
  const sanitizedName = reportName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
  const extension = format === 'excel' ? 'xlsx' : format;
  link.download = `${sanitizedName}_${date}.${extension}`;
  
  // Append to the document, click it, and remove it
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Release the URL object
  window.URL.revokeObjectURL(url);
};

const formatReportType = (type) => {
  switch (type) {
    case 'summary':
      return 'Donation Summary';
    case 'donor':
      return 'Donor Contribution';
    case 'pledge':
      return 'Pledge Fulfillment';
    case 'campaign':
      return 'Campaign Progress';
    case 'category':
      return 'Category Analysis';
    default:
      return type.charAt(0).toUpperCase() + type.slice(1);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 1) {
    return 'Today';
  } else if (diffDays === 1) {
    return 'Yesterday';
  } else if (diffDays < 7) {
    return `${diffDays} days ago`;
  } else if (diffDays < 30) {
    const weeks = Math.floor(diffDays / 7);
    return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`;
  } else {
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
};

const formatDateRange = (filters) => {
  if (!filters) return '';
  
  if (filters.dateRange) {
    switch (filters.dateRange) {
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
        return 'Custom Range';
    }
  } else if (filters.startDate && filters.endDate) {
    return 'Custom Range';
  }
  
  return 'All Time';
};

// Lifecycle hooks
onMounted(() => {
  loadSavedReports();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
