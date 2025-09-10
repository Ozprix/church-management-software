<template>
  <div class="reports-page">
    <div class="container mx-auto px-4 py-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h1 class="text-2xl font-bold text-neutral-800 dark:text-white mb-4 md:mb-0">
          Reports
        </h1>
        <div class="flex space-x-3">
          <Button 
            @click="activeTab = 'saved'" 
            variant="outline"
            size="sm"
            v-if="activeTab !== 'saved' && savedReports.length > 0"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
            Saved Reports
          </Button>
          <Button 
            @click="activeTab = 'generate'" 
            variant="primary"
            size="sm"
            v-if="activeTab !== 'generate'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generate New Report
          </Button>
        </div>
      </div>
      
      <!-- Tabs -->
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 mb-6">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
          <div class="flex">
            <button 
              @click="activeTab = 'generate'" 
              class="flex-1 py-2 px-4 text-center text-sm font-medium transition-colors duration-300"
              :class="activeTab === 'generate' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-500' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
            >
              Generate Report
            </button>
            <button 
              @click="activeTab = 'saved'" 
              class="flex-1 py-2 px-4 text-center text-sm font-medium transition-colors duration-300"
              :class="activeTab === 'saved' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-500' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
            >
              Saved Reports
              <span v-if="savedReports.length > 0" class="ml-1 bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 text-xs rounded-full px-2 py-0.5 transition-colors duration-300">
                {{ savedReports.length }}
              </span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Generate Report Tab -->
      <div v-if="activeTab === 'generate'">
        <ReportGenerator />
      </div>
      
      <!-- Saved Reports Tab -->
      <div v-else-if="activeTab === 'saved'">
        <div v-if="savedReports.length === 0" class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-neutral-800 dark:text-white">No saved reports</h3>
          <p class="mt-2 text-neutral-600 dark:text-neutral-400">
            Generate and save reports to access them here for future reference.
          </p>
          <div class="mt-6">
            <Button @click="activeTab = 'generate'" variant="primary">
              Generate New Report
            </Button>
          </div>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="report in savedReports" 
            :key="report.id"
            class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden"
          >
            <div class="p-4 border-b border-neutral-200 dark:border-neutral-700">
              <div class="flex items-start">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
                  <svg-icon :name="getCategoryIcon(report.category)" class="h-5 w-5" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-neutral-900 dark:text-white">{{ report.name }}</h3>
                  <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Saved on {{ formatDate(report.savedAt) }}
                  </p>
                </div>
              </div>
            </div>
            
            <div class="p-4">
              <p class="text-sm text-neutral-600 dark:text-neutral-400">
                <span class="font-medium">Date Range:</span> {{ formatDateRange(report.dateRange.start, report.dateRange.end) }}
              </p>
              
              <div class="mt-4 flex justify-between">
                <Button @click="viewReport(report)" variant="outline" size="sm">
                  View
                </Button>
                <Button @click="deleteReport(report.id)" variant="danger" size="sm">
                  Delete
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- View Saved Report Modal -->
      <Modal 
        v-if="showReportModal" 
        :title="selectedReport?.name || 'Report'" 
        size="xl"
        @close="closeReportModal"
      >
        <div class="p-4">
          <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
              Generated on {{ formatDate(selectedReport?.generatedAt) }} • 
              {{ formatDateRange(selectedReport?.dateRange.start, selectedReport?.dateRange.end) }}
            </p>
            
            <div class="relative" ref="exportDropdownRef">
              <Button 
                @click="showExportDropdown = !showExportDropdown" 
                variant="outline"
                size="sm"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </Button>
              
              <div 
                v-if="showExportDropdown" 
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 z-10"
              >
                <div class="py-1">
                  <button 
                    v-for="format in exportFormats" 
                    :key="format.id"
                    @click="exportReport(selectedReport, format.id)"
                    class="w-full text-left px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                  >
                    <svg-icon :name="format.icon" class="h-4 w-4 mr-2" />
                    {{ format.name }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div 
              v-for="field in getReportFields(selectedReport, 'number', 'currency')" 
              :key="field.name"
              class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4"
            >
              <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">{{ field.label }}</h4>
              <div class="mt-1 flex items-baseline">
                <p class="text-2xl font-semibold text-neutral-900 dark:text-white">
                  {{ formatFieldValue(field, selectedReport?.data[field.name]) }}
                </p>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div 
              v-for="field in getReportFields(selectedReport, 'chart')" 
              :key="field.name"
              class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-4"
            >
              <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">{{ field.label }}</h4>
              <div class="h-64">
                <chart-placeholder :type="field.chartType" :data="selectedReport?.data[field.name]" />
              </div>
            </div>
          </div>
          
          <div 
            v-for="field in getReportFields(selectedReport, 'table')" 
            :key="field.name"
            class="mb-6"
          >
            <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">{{ field.label }}</h4>
            <div class="overflow-x-auto">
              <table-placeholder :data="selectedReport?.data[field.name]" />
            </div>
          </div>
        </div>
        
        <template #footer>
          <div class="flex justify-end space-x-3">
            <Button @click="closeReportModal" variant="outline">Close</Button>
          </div>
        </template>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useReportsStore } from '../stores/reports';
import ReportGenerator from '../components/reports/ReportGenerator.vue';
import ChartPlaceholder from '../components/reports/ChartPlaceholder.vue';
import TablePlaceholder from '../components/reports/TablePlaceholder.vue';
import SvgIcon from '../components/ui/SvgIcon.vue';
import Button from '../components/ui/Button.vue';
import Modal from '../components/ui/Modal.vue';

const reportsStore = useReportsStore();

// State
const activeTab = ref('generate');
const showReportModal = ref(false);
const selectedReport = ref(null);
const showExportDropdown = ref(false);
const exportDropdownRef = ref(null);

// Computed
const savedReports = computed(() => reportsStore.savedReports);
const exportFormats = computed(() => reportsStore.exportFormats);

// Methods
function viewReport(report) {
  selectedReport.value = report;
  showReportModal.value = true;
}

function closeReportModal() {
  showReportModal.value = false;
  selectedReport.value = null;
}

function deleteReport(reportId) {
  if (confirm('Are you sure you want to delete this report?')) {
    reportsStore.deleteSavedReport(reportId);
  }
}

async function exportReport(report, format) {
  if (!report) return;
  
  try {
    showExportDropdown.value = false;
    const result = await reportsStore.exportReport(report, format);
    // Show success toast
  } catch (error) {
    console.error('Error exporting report:', error);
    // Show error toast
  }
}

function formatDate(dateString) {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatDateRange(start, end) {
  if (!start || !end) return '';
  
  const startDate = new Date(start);
  const endDate = new Date(end);
  
  const startFormatted = startDate.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  });
  
  const endFormatted = endDate.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  });
  
  return `${startFormatted} - ${endFormatted}`;
}

function formatFieldValue(field, value) {
  if (field.type === 'currency') {
    return new Intl.NumberFormat('en-US', { 
      style: 'currency', 
      currency: 'USD' 
    }).format(value);
  }
  
  return value;
}

function getCategoryIcon(category) {
  const icons = {
    members: 'users',
    finance: 'chart-pie',
    events: 'calendar',
    groups: 'users-group',
    donations: 'gift'
  };
  
  return icons[category] || 'chart-pie';
}

function getReportFields(report, ...types) {
  if (!report || !report.templateId) return [];
  
  const template = reportsStore.getReportById(report.templateId);
  if (!template) return [];
  
  return template.fields.filter(field => types.includes(field.type));
}

// Click outside handler for export dropdown
function handleClickOutside(event) {
  if (exportDropdownRef.value && !exportDropdownRef.value.contains(event.target)) {
    showExportDropdown.value = false;
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
