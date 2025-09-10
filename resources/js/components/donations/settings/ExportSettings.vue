<template>
  <div class="export-settings">
    <!-- Error Boundary wrapper -->
    <ErrorBoundary @error="handleError">
      <!-- Export Settings Form -->
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
          <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Export Settings</h3>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Configure how donation data is exported to CSV, Excel, and PDF formats
          </p>
        </div>
        
        <div class="p-4 sm:p-6">
          <!-- Form Validator wrapper -->
          <FormValidator
            :rules="validationRules"
            :model-value="exportSettings"
            v-slot="{ validate, errors, hasFieldError, getFieldError }"
            show-summary
          >
            <form @submit.prevent="validate() && saveSettings()">
          <div class="space-y-6">
            <!-- CSV Export Settings -->
            <div>
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">CSV Export Settings</h4>
              <div class="space-y-4">
                <FormField
                  id="csv-delimiter"
                  label="CSV Delimiter"
                  :error="getFieldError('csvDelimiter')"
                >
                  <select 
                    id="csv-delimiter" 
                    v-model="exportSettings.csvDelimiter" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
                  >
                    <option value=",">Comma (,)</option>
                    <option value=";">Semicolon (;)</option>
                    <option value="\t">Tab</option>
                    <option value="|">Pipe (|)</option>
                  </select>
                </FormField>
                
                <FormField
                  id="csv-encoding"
                  label="CSV Encoding"
                  :error="getFieldError('csvEncoding')"
                >
                  <select 
                    id="csv-encoding" 
                    v-model="exportSettings.csvEncoding" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
                  >
                    <option value="UTF-8">UTF-8</option>
                    <option value="UTF-16">UTF-16</option>
                    <option value="ISO-8859-1">ISO-8859-1 (Latin-1)</option>
                  </select>
                </FormField>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="include-header" 
                      v-model="exportSettings.includeHeader" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="include-header" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Include header row
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Include column names in the first row of the CSV file.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Excel Export Settings -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Excel Export Settings</h4>
              <div class="space-y-4">
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="auto-filter" 
                      v-model="exportSettings.excelAutoFilter" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="auto-filter" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Enable auto-filter
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Add filtering capability to column headers in Excel.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="freeze-header" 
                      v-model="exportSettings.excelFreezeHeader" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="freeze-header" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Freeze header row
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Keep the header row visible while scrolling.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="auto-width" 
                      v-model="exportSettings.excelAutoWidth" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="auto-width" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Auto-adjust column widths
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Automatically adjust column widths based on content.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- PDF Export Settings -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">PDF Export Settings</h4>
              <div class="space-y-4">
                <FormField
                  id="pdf-page-size"
                  label="Page Size"
                  :error="getFieldError('pdfPageSize')"
                  help-text="Select the paper size for PDF exports"
                >
                  <select 
                    id="pdf-page-size" 
                    v-model="exportSettings.pdfPageSize" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
                  >
                    <option value="A4">A4</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                  </select>
                </FormField>
                
                <FormField
                  id="pdf-orientation"
                  label="Page Orientation"
                  :error="getFieldError('pdfOrientation')"
                  help-text="Choose between portrait (vertical) or landscape (horizontal) orientation"
                >
                  <select 
                    id="pdf-orientation" 
                    v-model="exportSettings.pdfOrientation" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
                  >
                    <option value="portrait">Portrait</option>
                    <option value="landscape">Landscape</option>
                  </select>
                </FormField>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="include-logo-pdf" 
                      v-model="exportSettings.pdfIncludeLogo" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="include-logo-pdf" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Include organization logo
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Add the organization logo to the PDF header.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="include-footer" 
                      v-model="exportSettings.pdfIncludeFooter" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="include-footer" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Include page numbers and date
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Add page numbers and export date to the PDF footer.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Field Selection -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Default Fields to Export</h4>
              <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                Select which fields should be included in exports by default
              </p>
              
              <div class="space-y-4">
                <div class="grid grid-cols-1 gap-y-2 sm:grid-cols-2 sm:gap-x-4">
                  <div v-for="field in availableFields" :key="field.id" class="flex items-start">
                    <div class="flex items-center h-5">
                      <input 
                        :id="'field-' + field.id" 
                        v-model="exportSettings.selectedFields" 
                        :value="field.id" 
                        type="checkbox" 
                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label :for="'field-' + field.id" class="font-medium text-neutral-700 dark:text-neutral-300">
                        {{ field.label }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Save Button -->
            <div class="pt-6 flex justify-end">
              <button 
                type="button" 
                @click="resetForm"
                class="mr-3 inline-flex justify-center py-2 px-4 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Reset
              </button>
              <button 
                type="submit" 
                class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                :disabled="isSaving"
              >
                <LoadingSpinner v-if="isSaving" size="sm" inline class="mr-1" />
                {{ isSaving ? 'Saving...' : 'Save Settings' }}
              </button>
            </div>
          </div>
            </form>
          </FormValidator>
        </div>
      </div>
    </ErrorBoundary>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useDonationStore } from '../../../stores/donations';
import { useToastService } from '../../../services/toastService';
import ErrorBoundary from '../../../components/error/ErrorBoundary.vue';
import FormValidator from '../../../components/form/FormValidator.vue';
import FormField from '../../../components/form/FormField.vue';
import LoadingSpinner from '../../../components/ui/LoadingSpinner.vue';

// Store
const donationStore = useDonationStore();
const toast = useToastService();

// Available fields for export
const availableFields = [
  { id: 'id', label: 'ID' },
  { id: 'date', label: 'Date' },
  { id: 'amount', label: 'Amount' },
  { id: 'memberId', label: 'Member ID' },
  { id: 'memberName', label: 'Member Name' },
  { id: 'categoryId', label: 'Category ID' },
  { id: 'categoryName', label: 'Category Name' },
  { id: 'paymentMethodId', label: 'Payment Method ID' },
  { id: 'paymentMethodName', label: 'Payment Method Name' },
  { id: 'notes', label: 'Notes' },
  { id: 'receiptNumber', label: 'Receipt Number' },
  { id: 'createdAt', label: 'Created At' },
  { id: 'updatedAt', label: 'Updated At' }
];

// Get export settings from store or use defaults
const exportSettings = reactive({
  // CSV Settings
  csvDelimiter: donationStore.exportSettings?.csvDelimiter || ',',
  csvEncoding: donationStore.exportSettings?.csvEncoding || 'UTF-8',
  includeHeader: donationStore.exportSettings?.includeHeader !== false, // Default to true
  
  // Excel Settings
  excelAutoFilter: donationStore.exportSettings?.excelAutoFilter !== false, // Default to true
  excelFreezeHeader: donationStore.exportSettings?.excelFreezeHeader !== false, // Default to true
  excelAutoWidth: donationStore.exportSettings?.excelAutoWidth !== false, // Default to true
  
  // PDF Settings
  pdfPageSize: donationStore.exportSettings?.pdfPageSize || 'A4',
  pdfOrientation: donationStore.exportSettings?.pdfOrientation || 'portrait',
  pdfIncludeLogo: donationStore.exportSettings?.pdfIncludeLogo !== false, // Default to true
  pdfIncludeFooter: donationStore.exportSettings?.pdfIncludeFooter !== false, // Default to true
  
  // Selected Fields
  selectedFields: donationStore.exportSettings?.selectedFields || [
    'id', 'date', 'amount', 'memberName', 'categoryName', 'paymentMethodName', 'notes'
  ]
});

// Form state
const isSaving = ref(false);
const originalSettings = JSON.stringify(donationStore.exportSettings || {});

// Validation rules
const validationRules = {
  csvDelimiter: [
    { required: true, message: 'Please select a CSV delimiter' }
  ],
  csvEncoding: [
    { required: true, message: 'Please select a CSV encoding' }
  ],
  pdfPageSize: [
    { required: true, message: 'Please select a page size' }
  ],
  pdfOrientation: [
    { required: true, message: 'Please select a page orientation' }
  ],
  selectedFields: [
    { 
      validator: (value) => Array.isArray(value) && value.length > 0,
      message: 'Please select at least one field to export'
    }
  ]
};

// Save settings
const saveSettings = async () => {
  try {
    isSaving.value = true;
    
    // Simulate API delay (remove in production)
    await new Promise(resolve => setTimeout(resolve, 800));
    
    donationStore.updateExportSettings(exportSettings);
    
    toast.success('Export settings have been updated successfully.');
  } catch (error) {
    toast.error('Failed to save settings. Please try again.');
    console.error('Error saving export settings:', error);
  } finally {
    isSaving.value = false;
  }
};

// Reset form to original values
const resetForm = () => {
  const originalValues = JSON.parse(originalSettings);
  
  // Reset to original values or defaults
  exportSettings.csvDelimiter = originalValues?.csvDelimiter || ',';
  exportSettings.csvEncoding = originalValues?.csvEncoding || 'UTF-8';
  exportSettings.includeHeader = originalValues?.includeHeader !== false;
  exportSettings.excelAutoFilter = originalValues?.excelAutoFilter !== false;
  exportSettings.excelFreezeHeader = originalValues?.excelFreezeHeader !== false;
  exportSettings.excelAutoWidth = originalValues?.excelAutoWidth !== false;
  exportSettings.pdfPageSize = originalValues?.pdfPageSize || 'A4';
  exportSettings.pdfOrientation = originalValues?.pdfOrientation || 'portrait';
  exportSettings.pdfIncludeLogo = originalValues?.pdfIncludeLogo !== false;
  exportSettings.pdfIncludeFooter = originalValues?.pdfIncludeFooter !== false;
  exportSettings.selectedFields = originalValues?.selectedFields || [
    'id', 'date', 'amount', 'memberName', 'categoryName', 'paymentMethodName', 'notes'
  ];
  
  toast.info('Form has been reset to original values.');
};

// Handle errors from ErrorBoundary
const handleError = ({ error }) => {
  console.error('Error in ExportSettings component:', error);
  toast.error('An error occurred while managing export settings.');
};
</script>
