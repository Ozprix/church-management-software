<template>
  <div class="report-generator">
    <div v-if="!selectedReport && !showDonationReports" class="report-selection">
      <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4">Select a Report Template</h2>
      
      <!-- Report Categories -->
      <div class="mb-6">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="category in categories"
            :key="category"
            @click="selectedCategory = category === selectedCategory ? null : category"
            class="px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200"
            :class="[
              selectedCategory === category 
                ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300' 
                : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700'
            ]"
          >
            {{ formatCategory(category) }}
          </button>
        </div>
      </div>
      
      <!-- Special Report Categories -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div 
          @click="showDonationReports = true"
          class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 cursor-pointer hover:shadow-md transition-shadow duration-200 flex items-center"
        >
          <div class="flex-shrink-0 h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Donation Reports</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Comprehensive reports for donations, pledges, and campaigns</p>
            <div class="mt-2">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                New
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Standard Report Templates -->
      <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Standard Reports</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="report in filteredReports" 
          :key="report.id"
          @click="selectReport(report.id)"
          class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 cursor-pointer hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-start">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
              <svg-icon :name="report.icon" class="h-5 w-5" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-neutral-900 dark:text-white">{{ report.name }}</h3>
              <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ report.description }}</p>
              <div class="mt-2 flex items-center text-xs text-neutral-500 dark:text-neutral-400">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300">
                  {{ formatCategory(report.category) }}
                </span>
                <span class="ml-2">{{ report.fields.length }} data points</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else-if="showDonationReports" class="donation-reports-container">
      <div class="mb-4 flex items-center">
        <button 
          @click="showDonationReports = false" 
          class="mr-4 inline-flex items-center text-sm font-medium text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
        >
          <svg class="mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Reports
        </button>
        <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Donation Reports</h2>
      </div>
      
      <DonationReports />
    </div>
    
    <div v-else-if="selectedReport" class="report-configuration">
      <div class="flex items-center justify-between mb-6">
        <button 
          @click="selectedReport = null" 
          class="flex items-center text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to templates
        </button>
        
        <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">{{ selectedReport.name }}</h2>
        
        <div></div> <!-- Empty div for flex alignment -->
      </div>
      
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
        <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Report Options</h3>
        
        <!-- Date Range -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Date Range</label>
          <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
            <div class="flex-1">
              <label for="start-date" class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">Start Date</label>
              <input 
                id="start-date" 
                v-model="dateRange.start" 
                type="date" 
                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
              >
            </div>
            <div class="flex-1">
              <label for="end-date" class="block text-xs text-neutral-500 dark:text-neutral-400 mb-1">End Date</label>
              <input 
                id="end-date" 
                v-model="dateRange.end" 
                type="date" 
                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
              >
            </div>
          </div>
        </div>
        
        <!-- Additional Options -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Include Fields</label>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div 
              v-for="field in selectedReport.fields" 
              :key="field.name"
              class="flex items-center"
            >
              <input 
                :id="field.name" 
                v-model="selectedFields[field.name]" 
                type="checkbox" 
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded"
              >
              <label :for="field.name" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                {{ field.label }}
              </label>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button 
            @click="generateReport" 
            :disabled="isGenerating"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isGenerating ? 'Generating...' : 'Generate Report' }}</span>
          </button>
        </div>
      </div>
      
      <!-- Generated Report Preview -->
      <div v-if="reportData" class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-medium text-neutral-800 dark:text-white">{{ reportData.name }}</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
              Generated on {{ formatDate(reportData.generatedAt) }} • 
              {{ formatDateRange(reportData.dateRange.start, reportData.dateRange.end) }}
            </p>
          </div>
          
          <div class="flex space-x-2">
            <div class="relative" ref="exportDropdownRef">
              <button 
                @click="showExportDropdown = !showExportDropdown" 
                class="inline-flex items-center px-3 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm leading-4 font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div 
                v-if="showExportDropdown" 
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 z-10"
              >
                <div class="py-1">
                  <button 
                    v-for="format in exportFormats" 
                    :key="format.id"
                    @click="exportReport(format.id)"
                    class="w-full text-left px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                  >
                    <svg-icon :name="format.icon" class="h-4 w-4 mr-2" />
                    {{ format.name }}
                  </button>
                </div>
              </div>
            </div>
            
            <button 
              @click="saveReport" 
              class="inline-flex items-center px-3 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm leading-4 font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
              </svg>
              Save
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div 
              v-for="field in selectedReport.fields.filter(f => f.type === 'number' || f.type === 'currency')" 
              :key="field.name"
              v-show="selectedFields[field.name]"
              class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4"
            >
              <h4 class="text-sm font-medium text-neutral-500 dark:text-neutral-400">{{ field.label }}</h4>
              <div class="mt-1 flex items-baseline">
                <p class="text-2xl font-semibold text-neutral-900 dark:text-white">
                  {{ formatFieldValue(field, reportData.data[field.name]) }}
                </p>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div 
              v-for="field in selectedReport.fields.filter(f => f.type === 'chart')" 
              :key="field.name"
              v-show="selectedFields[field.name]"
              class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-4"
            >
              <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">{{ field.label }}</h4>
              <div class="h-64">
                <chart-placeholder :type="field.chartType" :data="reportData.data[field.name]" />
              </div>
            </div>
          </div>
          
          <div 
            v-for="field in selectedReport.fields.filter(f => f.type === 'table')" 
            :key="field.name"
            v-show="selectedFields[field.name]"
            class="mb-6"
          >
            <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">{{ field.label }}</h4>
            <div class="overflow-x-auto">
              <table-placeholder :data="reportData.data[field.name]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useReportsStore } from '../../stores/reports';
import DonationReports from './DonationReports.vue';
import SvgIcon from '../ui/SvgIcon.vue';
import ChartPlaceholder from './ChartPlaceholder.vue';
import TablePlaceholder from './TablePlaceholder.vue';

const reportsStore = useReportsStore();

// State
const selectedCategory = ref(null);
const selectedReport = ref(null);
const reportData = ref(null);
const showDonationReports = ref(false);
const dateRange = ref({ ...reportsStore.dateRange });
const selectedFields = ref({});
const showExportDropdown = ref(false);
const exportDropdownRef = ref(null);
const isGenerating = computed(() => reportsStore.isGenerating);
const exportFormats = computed(() => reportsStore.exportFormats);

// Computed
const categories = computed(() => reportsStore.getAllCategories);

const filteredReports = computed(() => {
  if (!selectedCategory.value) {
    return reportsStore.reportTemplates;
  }
  return reportsStore.getReportsByCategory(selectedCategory.value);
});

// Methods
function formatCategory(category) {
  return category.charAt(0).toUpperCase() + category.slice(1);
}

function selectReport(reportId) {
  const report = reportsStore.getReportById(reportId);
  selectedReport.value = report;
  
  // Initialize selected fields
  selectedFields.value = {};
  report.fields.forEach(field => {
    selectedFields.value[field.name] = true;
  });
  
  reportData.value = null;
}

async function generateReport() {
  try {
    const options = {
      dateRange: { ...dateRange.value },
      fields: Object.keys(selectedFields.value).filter(key => selectedFields.value[key])
    };
    
    const data = await reportsStore.generateReport(selectedReport.value.id, options);
    reportData.value = data;
  } catch (error) {
    console.error('Error generating report:', error);
    // Show error toast
  }
}

function saveReport() {
  if (!reportData.value) return;
  
  try {
    const savedReport = reportsStore.saveReport(reportData.value);
    // Show success toast
  } catch (error) {
    console.error('Error saving report:', error);
    // Show error toast
  }
}

async function exportReport(format) {
  if (!reportData.value) return;
  
  try {
    showExportDropdown.value = false;
    const result = await reportsStore.exportReport(reportData.value, format);
    // Show success toast
  } catch (error) {
    console.error('Error exporting report:', error);
    // Show error toast
  }
}

function formatDate(dateString) {
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

// Watch for changes to date range
watch(dateRange, (newRange) => {
  reportsStore.setDateRange(newRange.start, newRange.end);
}, { deep: true });
</script>
