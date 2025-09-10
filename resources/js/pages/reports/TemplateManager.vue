<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Report Templates</h1>
      <button 
        @click="showCreateForm = !showCreateForm" 
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
      >
        <svg v-if="!showCreateForm" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <svg v-else class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        {{ showCreateForm ? 'Cancel' : 'Create New Template' }}
      </button>
    </div>
    
    <!-- Create Template Form -->
    <div v-if="showCreateForm" class="bg-white shadow rounded-lg mb-6 overflow-hidden">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">{{ editingId ? 'Edit Template' : 'Create New Template' }}</h2>
      </div>
      <div class="p-6">
        <form @submit.prevent="saveTemplate">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
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
                  rows="3"
                  placeholder="Monthly summary of all donations categorized by fund"
                ></textarea>
              </div>
            </div>
            
            <div>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Default Time Period</label>
                <select 
                  v-model="newTemplate.timePeriod" 
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="last_week">Last Week</option>
                  <option value="last_month">Last Month</option>
                  <option value="last_quarter">Last Quarter</option>
                  <option value="last_year">Last Year</option>
                  <option value="current_month">Current Month</option>
                  <option value="current_quarter">Current Quarter</option>
                  <option value="current_year">Current Year</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Parameters</label>
            <div class="bg-gray-50 p-4 border border-gray-300 rounded-md">
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
                class="mt-2 inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
              >
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Parameter
              </button>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end">
            <button 
              type="button"
              @click="showCreateForm = false"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none mr-3"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
              :disabled="saving"
            >
              <span v-if="saving" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
              </span>
              <span v-else>{{ editingId ? 'Update Template' : 'Save Template' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Template List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Your Templates</h2>
      </div>
      
      <div class="p-4">
        <div v-if="loading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Loading templates...</p>
        </div>
        
        <div v-else-if="templates.length === 0" class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No templates</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new report template.</p>
          <div class="mt-6">
            <button 
              @click="showCreateForm = true" 
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Create New Template
            </button>
          </div>
        </div>
        
        <div v-else>
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Format</th>
                  <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                  <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="template in templates" :key="template.id" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ template.name }}
                    <p v-if="template.description" class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ template.description }}</p>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getTypeClass(template.type)">
                      {{ getTypeDisplay(template.type) }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ template.format.toUpperCase() }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ formatDate(template.created_at) }}
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <div class="flex justify-end space-x-3">
                      <button 
                        @click="createReportFromTemplate(template)" 
                        class="text-blue-600 hover:text-blue-900"
                        title="Create report from template"
                      >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </button>
                      <button 
                        @click="editTemplate(template)" 
                        class="text-indigo-600 hover:text-indigo-900"
                        title="Edit template"
                      >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button 
                        @click="confirmDeleteTemplate(template)" 
                        class="text-red-600 hover:text-red-900"
                        title="Delete template"
                      >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import ReportNavigation from '../../components/reports/ReportNavigation.vue';

export default {
  name: 'TemplateManager',
  components: {
    ReportNavigation
  },
  data() {
    return {
      showCreateForm: false,
      saving: false,
      editingId: null,
      newTemplate: this.getEmptyTemplate()
    };
  },
  computed: {
    ...mapState({
      loading: state => state.reports.loading,
      error: state => state.reports.error,
      templates: state => state.reports.templates
    })
  },
  mounted() {
    this.fetchTemplates();
  },
  methods: {
    ...mapActions('reports', [
      'fetchTemplates',
      'createTemplate',
      'updateTemplate',
      'deleteTemplate'
    ]),
    
    getEmptyTemplate() {
      return {
        name: '',
        type: '',
        description: '',
        format: 'pdf',
        chartType: 'bar',
        timePeriod: 'last_month',
        parameters: []
      };
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
          await this.updateTemplate({
            id: this.editingId,
            data: templateData
          });
          this.$toast.success('Template updated successfully');
        } else {
          await this.createTemplate(templateData);
          this.$toast.success('Template created successfully');
        }
        
        this.showCreateForm = false;
        this.newTemplate = this.getEmptyTemplate();
        this.editingId = null;
        await this.fetchTemplates();
      } catch (error) {
        console.error('Error saving template:', error);
        this.$toast.error('Failed to save template. Please try again.');
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
        timePeriod: template.time_period || 'last_month',
        parameters: Array.isArray(template.parameters) 
          ? template.parameters.map(p => ({ name: p.name, value: p.value }))
          : []
      };
      this.showCreateForm = true;
      
      // Scroll to the form
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    async confirmDeleteTemplate(template) {
      if (confirm(`Are you sure you want to delete the template "${template.name}"?`)) {
        try {
          await this.deleteTemplate(template.id);
          this.$toast.success('Template deleted successfully');
          await this.fetchTemplates();
        } catch (error) {
          console.error('Error deleting template:', error);
          this.$toast.error('Failed to delete template. Please try again.');
        }
      }
    },
    
    createReportFromTemplate(template) {
      // Navigate to create report page with template ID
      this.$router.push({
        name: 'reports.create',
        query: { template_id: template.id }
      });
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
    },
    
    getTypeClass(type) {
      const classes = {
        financial: 'bg-green-100 text-green-800',
        donation: 'bg-blue-100 text-blue-800',
        expense: 'bg-red-100 text-red-800',
        attendance: 'bg-purple-100 text-purple-800',
        membership: 'bg-indigo-100 text-indigo-800',
        pledge: 'bg-yellow-100 text-yellow-800',
        campaign: 'bg-pink-100 text-pink-800',
        custom: 'bg-gray-100 text-gray-800'
      };
      
      return classes[type] || 'bg-gray-100 text-gray-800';
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    }
  }
};
</script>
