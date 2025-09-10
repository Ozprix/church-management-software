<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    <div class="flex items-center mb-6">
      <router-link :to="`/reports/${reportId}`" class="text-blue-600 hover:text-blue-800 mr-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Edit Report</h1>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading report details...</p>
    </div>

    <div v-else class="bg-white shadow rounded-lg p-6">
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
            :to="`/reports/${reportId}`"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            :disabled="saving"
          >
            <span v-if="saving">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            </span>
            <span v-else>Save Changes</span>
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
  name: 'ReportEdit',
  components: {
    ReportNavigation
  },
  data() {
    return {
      reportId: this.$route.params.id,
      form: {
        name: '',
        type: '',
        description: '',
        output_format: 'pdf',
        is_favorite: false,
        is_scheduled: false,
        schedule_frequency: 'monthly',
        parameters: {
          start_date: '',
          end_date: '',
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
      categories: [],
      groups: [],
      campaigns: [],
      saving: false
    };
  },
  computed: {
    ...mapState('reports', ['loading', 'error', 'report']),
    ...mapGetters('reports', ['getReportTypes']),
    reportTypes() {
      return this.getReportTypes || [];
    },
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
    this.loadCategories();
    this.loadGroups();
    this.loadCampaigns();
    this.loadReport();
  },
  methods: {
    ...mapActions('reports', ['fetchReportTypes', 'fetchReport', 'updateReport']),
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
    async loadReport() {
      try {
        await this.fetchReport(this.reportId);
        
        // Set form fields from report
        this.form.name = this.report.name;
        this.form.type = this.report.type;
        this.form.description = this.report.description || '';
        this.form.output_format = this.report.output_format || 'pdf';
        this.form.is_favorite = this.report.is_favorite || false;
        this.form.is_scheduled = this.report.is_scheduled || false;
        this.form.schedule_frequency = this.report.schedule_frequency || 'monthly';
        
        // Parse parameters JSON
        if (this.report.parameters) {
          try {
            const params = JSON.parse(this.report.parameters);
            this.form.parameters = {
              ...this.form.parameters,
              ...params
            };
          } catch (e) {
            console.error('Error parsing report parameters:', e);
          }
        }
      } catch (error) {
        console.error('Error loading report:', error);
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
      this.saving = true;
      
      try {
        // Convert parameters to JSON string for storage
        const formData = {
          ...this.form,
          parameters: JSON.stringify(this.form.parameters)
        };
        
        await this.updateReport({
          id: this.reportId,
          data: formData
        });
        
        // Redirect to the report view page
        this.$router.push(`/reports/${this.reportId}`);
      } catch (error) {
        console.error('Error updating report:', error);
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
