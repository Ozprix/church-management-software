<template>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h2 class="text-lg font-semibold text-gray-800">Report Templates</h2>
      <button 
        @click="showCreateForm = !showCreateForm" 
        class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
      >
        <svg v-if="!showCreateForm" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <svg v-else class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        {{ showCreateForm ? 'Cancel' : 'New Template' }}
      </button>
    </div>
    
    <!-- Create Template Form -->
    <div v-if="showCreateForm" class="p-4 bg-gray-50 border-b border-gray-200">
      <form @submit.prevent="saveTemplate">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
          <input 
            v-model="newTemplate.name" 
            type="text" 
            required
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="Monthly Donations Summary"
          >
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select 
            v-model="newTemplate.type" 
            required
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="">Select a report type</option>
            <option value="financial">Financial Summary</option>
            <option value="donation">Donation Report</option>
            <option value="expense">Expense Report</option>
            <option value="attendance">Attendance Report</option>
            <option value="membership">Membership Report</option>
            <option value="pledge">Pledge Report</option>
            <option value="campaign">Campaign Report</option>
            <option value="custom">Custom Report</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea 
            v-model="newTemplate.description" 
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            rows="2"
            placeholder="Monthly summary of all donations categorized by fund"
          ></textarea>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Default Format</label>
          <select 
            v-model="newTemplate.format" 
            required
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="pdf">PDF Document</option>
            <option value="excel">Excel Spreadsheet</option>
            <option value="json">JSON Data</option>
            <option value="html">HTML Report</option>
            <option value="csv">CSV Data</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Default Chart Type</label>
          <select 
            v-model="newTemplate.chartType" 
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="bar">Bar Chart</option>
            <option value="line">Line Chart</option>
            <option value="pie">Pie Chart</option>
            <option value="doughnut">Doughnut Chart</option>
            <option value="polarArea">Polar Area Chart</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Parameters</label>
          <div class="bg-white p-3 border border-gray-300 rounded-md">
            <div v-for="(param, index) in newTemplate.parameters" :key="index" class="flex items-center mb-2">
              <input 
                v-model="param.name" 
                type="text" 
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm mr-2"
                placeholder="Parameter name"
              >
              <input 
                v-model="param.value" 
                type="text" 
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm mr-2"
                placeholder="Default value"
              >
              <button 
                @click="removeParameter(index)" 
                type="button"
                class="text-red-600 hover:text-red-800"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
            <button 
              @click="addParameter" 
              type="button"
              class="mt-2 inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
            >
              <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add Parameter
            </button>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button 
            type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
            :disabled="saving"
          >
            <span v-if="saving" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            </span>
            <span v-else>Save Template</span>
          </button>
        </div>
      </form>
    </div>
    
    <!-- Template List -->
    <div class="p-4">
      <div v-if="loading" class="text-center py-4">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-600">Loading templates...</p>
      </div>
      
      <div v-else-if="templates.length === 0" class="text-center py-4">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No templates</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new report template.</p>
      </div>
      
      <div v-else class="space-y-4">
        <div v-for="template in templates" :key="template.id" class="border rounded-md p-4 hover:bg-gray-50 transition-colors">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-medium text-gray-900">{{ template.name }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ template.description || 'No description' }}</p>
              <div class="mt-2 flex items-center text-xs text-gray-500">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                  {{ getTypeDisplay(template.type) }}
                </span>
                <span>Format: {{ template.format.toUpperCase() }}</span>
              </div>
            </div>
            <div class="flex space-x-2">
              <button 
                @click="useTemplate(template)" 
                class="text-blue-600 hover:text-blue-800"
                title="Use this template"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </button>
              <button 
                @click="editTemplate(template)" 
                class="text-indigo-600 hover:text-indigo-800"
                title="Edit template"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button 
                @click="confirmDeleteTemplate(template)" 
                class="text-red-600 hover:text-red-800"
                title="Delete template"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReportTemplateManager',
  props: {
    reportId: {
      type: [String, Number],
      default: null
    }
  },
  data() {
    return {
      templates: [],
      loading: false,
      saving: false,
      showCreateForm: false,
      newTemplate: this.getEmptyTemplate(),
      editingId: null
    };
  },
  mounted() {
    this.fetchTemplates();
  },
  methods: {
    getEmptyTemplate() {
      return {
        name: '',
        type: '',
        description: '',
        format: 'pdf',
        chartType: 'bar',
        parameters: []
      };
    },
    
    async fetchTemplates() {
      this.loading = true;
      try {
        const response = await this.$store.dispatch('reports/fetchTemplates');
        this.templates = response.data || [];
      } catch (error) {
        console.error('Error fetching templates:', error);
      } finally {
        this.loading = false;
      }
    },
    
    addParameter() {
      this.newTemplate.parameters.push({ name: '', value: '' });
    },
    
    removeParameter(index) {
      this.newTemplate.parameters.splice(index, 1);
    },
    
    async saveTemplate() {
      this.saving = true;
      
      try {
        // Filter out empty parameters
        const validParameters = this.newTemplate.parameters.filter(p => p.name.trim() !== '');
        const templateData = {
          ...this.newTemplate,
          parameters: validParameters
        };
        
        if (this.editingId) {
          await this.$store.dispatch('reports/updateTemplate', {
            id: this.editingId,
            data: templateData
          });
        } else {
          await this.$store.dispatch('reports/createTemplate', templateData);
        }
        
        this.showCreateForm = false;
        this.newTemplate = this.getEmptyTemplate();
        this.editingId = null;
        await this.fetchTemplates();
        
        this.$emit('template-saved');
      } catch (error) {
        console.error('Error saving template:', error);
      } finally {
        this.saving = false;
      }
    },
    
    editTemplate(template) {
      this.editingId = template.id;
      this.newTemplate = {
        name: template.name,
        type: template.type,
        description: template.description || '',
        format: template.format || 'pdf',
        chartType: template.chart_type || 'bar',
        parameters: Array.isArray(template.parameters) 
          ? template.parameters.map(p => ({ name: p.name, value: p.value }))
          : []
      };
      this.showCreateForm = true;
    },
    
    async confirmDeleteTemplate(template) {
      if (confirm(`Are you sure you want to delete the template "${template.name}"?`)) {
        try {
          await this.$store.dispatch('reports/deleteTemplate', template.id);
          await this.fetchTemplates();
        } catch (error) {
          console.error('Error deleting template:', error);
        }
      }
    },
    
    useTemplate(template) {
      this.$emit('use-template', template);
    },
    
    getTypeDisplay(type) {
      const types = {
        financial: 'Financial',
        donation: 'Donation',
        expense: 'Expense',
        attendance: 'Attendance',
        membership: 'Membership',
        pledge: 'Pledge',
        campaign: 'Campaign',
        custom: 'Custom'
      };
      
      return types[type] || type.charAt(0).toUpperCase() + type.slice(1);
    }
  }
};
</script>
