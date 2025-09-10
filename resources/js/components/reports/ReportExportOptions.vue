<template>
  <div class="report-export-options">
    <div class="relative inline-block text-left">
      <button 
        @click="toggleDropdown"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        :class="{ 'ring-2 ring-offset-2 ring-primary-500': showDropdown }"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
        </svg>
        Export Report
      </button>
      
      <div 
        v-if="showDropdown" 
        class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
        ref="dropdownRef"
      >
        <div class="py-1">
          <button 
            v-for="option in exportOptions" 
            :key="option.value"
            @click="exportReport(option.value)"
            class="w-full text-left block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 h-6 w-6 flex items-center justify-center">
                <svg v-if="option.value === 'pdf'" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M7 3C5.9 3 5 3.9 5 5v14c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7.4L13.4 3H7zm0 2h5v4h5v10H7V5zm2 6v2h6v-2H9zm0 4v2h6v-2H9z"/>
                </svg>
                <svg v-else-if="option.value === 'excel'" class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14 2H6C4.9 2 4 2.9 4 4v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 2l5 5h-5V4zM7 15l2-4h2l2 4h2l-3-6 3-6h-2l-2 4h-2L7 3H5l3 6-3 6h2z"/>
                </svg>
                <svg v-else-if="option.value === 'csv'" class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14 2H6C4.9 2 4 2.9 4 4v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 2l5 5h-5V4zM8 20V8h1v12H8zm3 0V8h1v12h-1zm3 0V8h1v12h-1z"/>
                </svg>
                <svg v-else class="h-5 w-5 text-neutral-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14 2H6C4.9 2 4 2.9 4 4v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 2l5 5h-5V4zm-2 12v-2h5v2h-5zm0-5V9h5v2h-5z"/>
                </svg>
              </div>
              <div class="ml-3">
                <p class="font-medium">{{ option.label }}</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ option.description }}</p>
              </div>
            </div>
          </button>
        </div>
        
        <div class="border-t border-neutral-200 dark:border-neutral-700">
          <div class="px-4 py-3">
            <div class="flex items-center mb-2">
              <input 
                id="include-filters" 
                type="checkbox" 
                v-model="includeFilters"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 rounded"
              >
              <label for="include-filters" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                Include filters
              </label>
            </div>
            <div class="flex items-center">
              <input 
                id="include-charts" 
                type="checkbox" 
                v-model="includeCharts"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 rounded"
              >
              <label for="include-charts" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                Include charts
              </label>
            </div>
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
        <h3 class="text-lg font-medium text-center text-neutral-900 dark:text-white">Preparing Export</h3>
        <p class="mt-2 text-sm text-center text-neutral-500 dark:text-neutral-400">
          Please wait while we generate your {{ currentExportFormat }} file...
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useDonationStore } from '../../stores/donations';
import { useToastService } from '../../services/toastService';

const props = defineProps({
  reportType: {
    type: String,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

// Store and services
const donationStore = useDonationStore();
const toast = useToastService();

// State
const showDropdown = ref(false);
const dropdownRef = ref(null);
const includeFilters = ref(true);
const includeCharts = ref(true);
const isExporting = ref(false);
const currentExportFormat = ref('');

// Export options
const exportOptions = [
  {
    label: 'PDF Document',
    value: 'pdf',
    description: 'Export as PDF document'
  },
  {
    label: 'Excel Spreadsheet',
    value: 'excel',
    description: 'Export as Excel (.xlsx) file'
  },
  {
    label: 'CSV File',
    value: 'csv',
    description: 'Export as comma-separated values'
  },
  {
    label: 'Print View',
    value: 'print',
    description: 'Open printer-friendly view'
  }
];

// Methods
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target) && !event.target.closest('button[class*="bg-primary-600"]')) {
    showDropdown.value = false;
  }
};

const exportReport = async (format) => {
  showDropdown.value = false;
  isExporting.value = true;
  currentExportFormat.value = format === 'excel' ? 'Excel' : format.toUpperCase();
  
  try {
    // Prepare export parameters
    const params = {
      ...props.filters,
      reportType: props.reportType,
      includeFilters: includeFilters.value,
      includeCharts: includeCharts.value
    };
    
    if (format === 'print') {
      // Open print view in new window
      openPrintView(params);
      isExporting.value = false;
      return;
    }
    
    // Call API to generate export file
    const response = await donationStore.reports.exportReport(format, params);
    
    // Download the file
    downloadFile(response, format);
    
    toast.success(`Report successfully exported as ${format.toUpperCase()}`);
  } catch (error) {
    console.error(`Error exporting report as ${format}:`, error);
    toast.error(`Failed to export report: ${error.message || 'Unknown error'}`);
  } finally {
    isExporting.value = false;
  }
};

const openPrintView = (params) => {
  // Create a query string from params
  const queryString = Object.entries(params)
    .filter(([_, value]) => value !== null && value !== undefined)
    .map(([key, value]) => {
      if (Array.isArray(value)) {
        return `${key}=${encodeURIComponent(value.join(','))}`;
      }
      return `${key}=${encodeURIComponent(value)}`;
    })
    .join('&');
  
  // Open print view in new window
  const printWindow = window.open(`/reports/print?${queryString}`, '_blank');
  
  // Automatically trigger print dialog when content is loaded
  if (printWindow) {
    printWindow.onload = () => {
      setTimeout(() => {
        printWindow.print();
      }, 1000);
    };
  } else {
    toast.error('Pop-up blocked. Please allow pop-ups for this site to use the print feature.');
  }
};

const downloadFile = (response, format) => {
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
  
  // Set the filename based on report type and format
  const date = new Date().toISOString().split('T')[0];
  const reportName = props.reportType.charAt(0).toUpperCase() + props.reportType.slice(1);
  const extension = format === 'excel' ? 'xlsx' : format;
  link.download = `${reportName}_Report_${date}.${extension}`;
  
  // Append to the document, click it, and remove it
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Release the URL object
  window.URL.revokeObjectURL(url);
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
