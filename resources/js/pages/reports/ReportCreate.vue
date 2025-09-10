<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    <div class="flex items-center mb-6">
      <router-link to="/reports" class="text-blue-600 hover:text-blue-800 mr-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </router-link>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Report</h1>
        <router-link 
          to="/reports/templates"
          class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
        >
          <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
          </svg>
          Manage Templates
        </router-link>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Template Selection -->
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Use Template</h2>
      </div>
      <div class="p-4">
        <div v-if="loadingTemplates" class="text-center py-4">
          <div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-blue-600"></div>
          <p class="mt-1 text-sm text-gray-600">Loading templates...</p>
        </div>
        <div v-else-if="templates.length === 0" class="text-center py-4">
          <p class="text-sm text-gray-500">No templates available. <router-link to="/reports/templates" class="text-blue-600 hover:text-blue-800">Create a template</router-link> to streamline report creation.</p>
        </div>
        <div v-else>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select a template</label>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div 
              v-for="template in templates" 
              :key="template.id"
              class="border rounded-md p-3 cursor-pointer transition-colors hover:bg-blue-50 hover:border-blue-300"
              :class="{ 'bg-blue-50 border-blue-300 ring-2 ring-blue-200': selectedTemplateId === template.id }"
              @click="selectTemplate(template)"
            >
              <div class="flex justify-between items-start">
                <h3 class="font-medium text-gray-900">{{ template.name }}</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="getTypeClass(template.type)">
                  {{ getTypeDisplay(template.type) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-1">{{ template.description || 'No description' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Details -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Report Details</h2>
      </div>
      <form @submit.prevent="submitForm">
        <!-- Basic Information -->
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Basic Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Report Name</label>
              <input
                type="text"
                id="name"
                v-model="form.name"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                required
              />
            </div>
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
              <select
                id="type"
                v-model="form.type"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                required
                @change="updateParameterFields"
              >
                <option value="" disabled>Select a report type</option>
                <option v-for="type in reportTypes" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="mt-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              rows="3"
            ></textarea>
          </div>
        </div>

        <!-- Report Parameters -->
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Report Parameters</h2>
          
          <!-- Date Range Parameters -->
          <div v-if="showDateRange" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input
                type="date"
                id="start_date"
                v-model="form.parameters.start_date"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              />
            </div>
            <div>
              <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input
                type="date"
                id="end_date"
                v-model="form.parameters.end_date"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              />
            </div>
          </div>

          <!-- Financial Parameters -->
          <div v-if="showFinancialParams" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                id="category"
                v-model="form.parameters.category"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-1">Minimum Amount</label>
              <input
                type="number"
                id="min_amount"
                v-model="form.parameters.min_amount"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                min="0"
                step="0.01"
              />
            </div>
          </div>

          <!-- Attendance Parameters -->
          <div v-if="showAttendanceParams" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="event_type" class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
              <select
                id="event_type"
                v-model="form.parameters.event_type"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All Events</option>
                <option value="service">Service</option>
                <option value="bible_study">Bible Study</option>
                <option value="prayer">Prayer Meeting</option>
                <option value="special">Special Event</option>
              </select>
            </div>
            <div>
              <label for="group_by" class="block text-sm font-medium text-gray-700 mb-1">Group By</label>
              <select
                id="group_by"
                v-model="form.parameters.group_by"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="day">Day</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
                <option value="quarter">Quarter</option>
                <option value="year">Year</option>
              </select>
            </div>
          </div>

          <!-- Membership Parameters -->
          <div v-if="showMembershipParams" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Member Status</label>
              <select
                id="status"
                v-model="form.parameters.status"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="visitor">Visitor</option>
                <option value="new">New Member</option>
              </select>
            </div>
            <div>
              <label for="group" class="block text-sm font-medium text-gray-700 mb-1">Group</label>
              <select
                id="group"
                v-model="form.parameters.group"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All Groups</option>
                <option v-for="group in groups" :key="group.id" :value="group.id">
                  {{ group.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Campaign/Pledge Parameters -->
          <div v-if="showCampaignParams" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="campaign" class="block text-sm font-medium text-gray-700 mb-1">Campaign</label>
              <select
                id="campaign"
                v-model="form.parameters.campaign"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All Campaigns</option>
                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                  {{ campaign.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="fulfillment_status" class="block text-sm font-medium text-gray-700 mb-1">Fulfillment Status</label>
              <select
                id="fulfillment_status"
                v-model="form.parameters.fulfillment_status"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">All</option>
                <option value="complete">Complete</option>
                <option value="partial">Partial</option>
                <option value="unfulfilled">Unfulfilled</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Output Options -->
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Output Options</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="output_format" class="block text-sm font-medium text-gray-700 mb-1">Format</label>
              <select
                id="output_format"
                v-model="form.output_format"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                required
              >
                <option value="pdf">PDF</option>
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
              </select>
            </div>
            <div>
              <label for="chart_type" class="block text-sm font-medium text-gray-700 mb-1">Chart Type</label>
              <select
                id="chart_type"
                v-model="form.parameters.chart_type"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">No Chart</option>
                <option value="bar">Bar Chart</option>
                <option value="line">Line Chart</option>
                <option value="pie">Pie Chart</option>
                <option value="area">Area Chart</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Scheduling Options -->
        <div class="mb-6">
          <div class="flex items-center mb-4">
            <input
              type="checkbox"
              id="is_scheduled"
              v-model="form.is_scheduled"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_scheduled" class="ml-2 block text-lg font-medium text-gray-700">
              Schedule this report
            </label>
          </div>
          
          <div v-if="form.is_scheduled" class="pl-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="schedule_frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                <select
                  id="schedule_frequency"
                  v-model="form.schedule_frequency"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  required
                >
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                </select>
              </div>
              <div>
                <label for="email_recipients" class="block text-sm font-medium text-gray-700 mb-1">Email Recipients</label>
                <input
                  type="text"
                  id="email_recipients"
                  v-model="form.parameters.email_recipients"
                  placeholder="comma-separated emails"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Save as Favorite -->
        <div class="mb-6">
          <div class="flex items-center">
            <input
              type="checkbox"
              id="is_favorite"
              v-model="form.is_favorite"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_favorite" class="ml-2 block text-sm font-medium text-gray-700">
              Save as favorite
            </label>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
          <router-link
            to="/reports"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            :disabled="loading"
          >
            <span v-if="loading">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating...
            </span>
            <span v-else>Create Report</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import ReportNavigation from '../../components/reports/ReportNavigation.vue';

export default {
  name: 'ReportCreate',
  components: {
    ReportNavigation
  },
  data() {
    return {
      form: {
        name: '',
        type: '',
        description: '',
        output_format: 'pdf',
        is_favorite: false,
        is_scheduled: false,
        schedule_frequency: 'monthly',
        parameters: {
          start_date: this.getDefaultStartDate(),
          end_date: this.getDefaultEndDate(),
          category: '',
          min_amount: '',
          event_type: '',
          group_by: 'month',
          status: '',
          group: '',
          campaign: '',
          fulfillment_status: '',
          chart_type: '',
          email_recipients: ''
        }
      },
      reportTypes: [],
      templates: [],
      loadingTemplates: false,
      selectedTemplateId: null,
      categories: [],
      groups: [],
      campaigns: []
    };
  },
  computed: {
    ...mapState('reports', ['loading', 'error']),
    ...mapGetters('reports', ['getReportTypes']),
    showDateRange() {
      return this.form.type !== '';
    },
    showFinancialParams() {
      return ['financial', 'donation', 'expense'].includes(this.form.type);
    },
    showAttendanceParams() {
      return this.form.type === 'attendance';
    },
    showMembershipParams() {
      return this.form.type === 'membership';
    },
    showCampaignParams() {
      return ['pledge', 'campaign'].includes(this.form.type);
    }
  },
  created() {
    this.fetchReportTypes();
    this.fetchTemplates();
    
    // Check if template_id is provided in the query parameters
    const templateId = this.$route.query.template_id;
    if (templateId) {
      this.selectedTemplateId = templateId;
      this.fetchTemplateAndApply(templateId);
    }
  },
  methods: {
    ...mapActions('reports', ['fetchReportTypes', 'createReport']),
    getDefaultStartDate() {
      const date = new Date();
      date.setMonth(date.getMonth() - 1);
      return date.toISOString().split('T')[0];
    },
    getDefaultEndDate() {
      return new Date().toISOString().split('T')[0];
    },
    async fetchReportTypes() {
      try {
        const response = await this.$store.dispatch('reports/fetchReportTypes');
        this.reportTypes = response.data;
      } catch (error) {
        console.error('Error fetching report types:', error);
      }
    },
    
    async fetchTemplates() {
      this.loadingTemplates = true;
      try {
        const response = await this.$store.dispatch('reports/fetchTemplates');
        this.templates = response.data || [];
      } catch (error) {
        console.error('Error fetching templates:', error);
      } finally {
        this.loadingTemplates = false;
      }
    },
    
    async fetchTemplateAndApply(templateId) {
      try {
        const response = await this.$store.dispatch('reports/fetchTemplate', templateId);
        const template = response.data;
        if (template) {
          this.selectTemplate(template);
        }
      } catch (error) {
        console.error('Error fetching template:', error);
      }
    },
    
    selectTemplate(template) {
      this.selectedTemplateId = template.id;
      
      // Apply template values to the form
      this.form.name = template.name;
      this.form.type = template.type;
      this.form.description = template.description || '';
      
      // Apply template parameters if available
      if (Array.isArray(template.parameters) && template.parameters.length > 0) {
        const params = {};
        template.parameters.forEach(param => {
          if (param.name && param.value !== undefined) {
            params[param.name] = param.value;
          }
        });
        this.form.parameters = params;
      }
      
      // Update the type parameters if needed
      this.updateTypeParams();
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
    async loadCategories() {
      try {
        const response = await this.$axios.get('/categories');
        this.categories = response.data.data;
      } catch (error) {
        console.error('Error loading categories:', error);
      }
    },
    async loadGroups() {
      try {
        const response = await this.$axios.get('/groups');
        this.groups = response.data.data;
      } catch (error) {
        console.error('Error loading groups:', error);
      }
    },
    async loadCampaigns() {
      try {
        const response = await this.$axios.get('/campaigns');
        this.campaigns = response.data.data;
      } catch (error) {
        console.error('Error loading campaigns:', error);
      }
    },
    updateParameterFields() {
      // Reset any type-specific parameters when type changes
      if (!this.showFinancialParams) {
        this.form.parameters.category = '';
        this.form.parameters.min_amount = '';
      }
      if (!this.showAttendanceParams) {
        this.form.parameters.event_type = '';
      }
      if (!this.showMembershipParams) {
        this.form.parameters.status = '';
        this.form.parameters.group = '';
      }
      if (!this.showCampaignParams) {
        this.form.parameters.campaign = '';
        this.form.parameters.fulfillment_status = '';
      }
    },
    async submitForm() {
      try {
        // Convert parameters to JSON string for storage
        const formData = {
          ...this.form,
          parameters: JSON.stringify(this.form.parameters)
        };
        
        const response = await this.createReport(formData);
        
        // Redirect to the report view page
        this.$router.push(`/reports/${response.data.id}`);
      } catch (error) {
        console.error('Error creating report:', error);
      }
    }
  }
};
</script>
