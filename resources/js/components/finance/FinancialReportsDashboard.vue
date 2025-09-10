<template>
  <div class="financial-reports-dashboard">
    <div class="mb-4">
      <h1 class="text-2xl font-bold mb-2">Financial Reports Dashboard</h1>
      <p class="text-gray-600">Comprehensive overview of the church's financial health</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            type="date"
            v-model="filters.startDate"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            type="date"
            v-model="filters.endDate"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filters.categoryId"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="applyFilters"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md"
          >
            Apply Filters
          </button>
          <button
            @click="resetFilters"
            class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-md"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Financial Summary Cards -->
    <div v-if="!loading.summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Donations</p>
            <h3 class="text-2xl font-bold mt-1">${{ formatNumber(summary.totalDonations) }}</h3>
          </div>
          <div class="p-2 bg-green-100 rounded-md">
            <i class="fas fa-hand-holding-usd text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Expenses</p>
            <h3 class="text-2xl font-bold mt-1">${{ formatNumber(summary.totalExpenses) }}</h3>
          </div>
          <div class="p-2 bg-red-100 rounded-md">
            <i class="fas fa-file-invoice-dollar text-red-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500">Net Income</p>
            <h3 class="text-2xl font-bold mt-1" :class="summary.netIncome >= 0 ? 'text-green-600' : 'text-red-600'">
              ${{ formatNumber(summary.netIncome) }}
            </h3>
            <p class="text-sm mt-1" :class="summary.netIncomePercentage >= 0 ? 'text-green-600' : 'text-red-600'">
              <i :class="summary.netIncomePercentage >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(summary.netIncomePercentage) }}% from previous period
            </p>
          </div>
          <div class="p-2" :class="summary.netIncome >= 0 ? 'bg-green-100' : 'bg-red-100'">
            <i class="fas fa-chart-line" :class="summary.netIncome >= 0 ? 'text-green-600' : 'text-red-600'"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-medium text-gray-500">Average Donation</p>
            <h3 class="text-2xl font-bold mt-1">${{ formatNumber(summary.averageDonation) }}</h3>
          </div>
          <div class="p-2 bg-blue-100 rounded-md">
            <i class="fas fa-calculator text-blue-600"></i>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div v-for="i in 4" :key="i" class="bg-white rounded-lg shadow-md p-6 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Monthly Income Chart -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold mb-4">Monthly Income</h3>
        <div v-if="!loading.chartData" style="height: 300px;">
          <line-chart
            :chart-data="monthlyChartData"
            :options="monthlyChartOptions"
          ></line-chart>
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <div class="spinner"></div>
        </div>
      </div>

      <!-- Donations by Category Chart -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold mb-4">Donations by Category</h3>
        <div v-if="!loading.chartData" style="height: 300px;">
          <pie-chart
            :chart-data="categoryChartData"
            :options="categoryChartOptions"
          ></pie-chart>
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <div class="spinner"></div>
        </div>
      </div>
    </div>

    <!-- Projects Funding Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Active Projects Funding Status</h3>
        <router-link
          to="/projects"
          class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
        >
          View All Projects
        </router-link>
      </div>
      <div v-if="!loading.projects" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Project Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Goal
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Current Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Progress
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="project in projects" :key="project.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ project.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${{ formatNumber(project.goal_amount) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${{ formatNumber(project.current_amount) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div
                    class="bg-indigo-600 h-2.5 rounded-full"
                    :style="{ width: project.percent_complete + '%' }"
                  ></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ project.percent_complete }}% Complete</div>
              </td>
            </tr>
            <tr v-if="projects.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                No active projects found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="animate-pulse">
        <div class="h-10 bg-gray-200 rounded mb-4"></div>
        <div v-for="i in 3" :key="i" class="h-16 bg-gray-200 rounded mb-2"></div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h3 class="text-lg font-bold mb-4">Export Reports</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select
            v-model="exportOptions.reportType"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option value="summary">Summary Report</option>
            <option value="detailed">Detailed Report</option>
            <option value="project">Project Report</option>
          </select>
        </div>
        <div v-if="exportOptions.reportType === 'project'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
          <select
            v-model="exportOptions.projectId"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option v-for="project in projects" :key="project.id" :value="project.id">
              {{ project.name }}
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="generatePdfReport"
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md mr-2"
            :disabled="loading.export"
          >
            <i class="far fa-file-pdf mr-1"></i> PDF
          </button>
          <button
            @click="exportToExcel"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md"
            :disabled="loading.export"
          >
            <i class="far fa-file-excel mr-1"></i> Excel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Line as LineChart, Pie as PieChart } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { useToast } from 'vue-toastification';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

export default {
  name: 'FinancialReportsDashboard',
  components: {
    LineChart,
    PieChart
  },
  data() {
    return {
      filters: {
        startDate: this.getDefaultStartDate(),
        endDate: this.getDefaultEndDate(),
        categoryId: ''
      },
      summary: {
        totalDonations: 0,
        totalExpenses: 0,
        netIncome: 0,
        netIncomePercentage: 0,
        averageDonation: 0
      },
      chartData: {
        categories: [],
        monthly: []
      },
      projects: [],
      categories: [],
      exportOptions: {
        reportType: 'summary',
        projectId: null
      },
      loading: {
        summary: true,
        chartData: true,
        projects: true,
        export: false
      }
    };
  },
  computed: {
    monthlyChartData() {
      const labels = this.chartData.monthly.map(item => item.month);
      return {
        labels,
        datasets: [
          {
            label: 'Donations',
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderColor: 'rgb(99, 102, 241)',
            data: this.chartData.monthly.map(item => item.donations),
            tension: 0.4
          },
          {
            label: 'Expenses',
            backgroundColor: 'rgba(239, 68, 68, 0.2)',
            borderColor: 'rgb(239, 68, 68)',
            data: this.chartData.monthly.map(item => item.expenses),
            tension: 0.4
          }
        ]
      };
    },
    monthlyChartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          legend: {
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed.y !== null) {
                  label += '$' + context.parsed.y.toLocaleString();
                }
                return label;
              }
            }
          }
        }
      };
    },
    categoryChartData() {
      return {
        labels: this.chartData.categories.map(category => category.name),
        datasets: [
          {
            backgroundColor: [
              '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
              '#EC4899', '#06B6D4', '#84CC16', '#3B82F6', '#F97316'
            ],
            data: this.chartData.categories.map(category => category.total)
          }
        ]
      };
    },
    categoryChartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.raw !== null) {
                  label += '$' + context.raw.toLocaleString();
                }
                return label;
              }
            }
          }
        }
      };
    }
  },
  created() {
    this.toast = useToast();
    this.fetchCategories();
    this.fetchData();
  },
  methods: {
    getDefaultStartDate() {
      const date = new Date();
      date.setMonth(date.getMonth() - 1);
      return date.toISOString().split('T')[0];
    },
    getDefaultEndDate() {
      return new Date().toISOString().split('T')[0];
    },
    formatNumber(value) {
      return parseFloat(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    async fetchCategories() {
      try {
        const response = await axios.get('/api/donation-categories');
        this.categories = response.data.data;
      } catch (error) {
        this.toast.error('Failed to load donation categories');
        console.error(error);
      }
    },
    async fetchData() {
      this.loading.summary = true;
      this.loading.chartData = true;
      this.loading.projects = true;
      
      await Promise.all([
        this.fetchSummary(),
        this.fetchChartData(),
        this.fetchProjects()
      ]);
    },
    async fetchSummary() {
      try {
        const response = await axios.get('/api/finance/reports/summary', {
          params: {
            start_date: this.filters.startDate,
            end_date: this.filters.endDate,
            category_id: this.filters.categoryId || null
          }
        });
        this.summary = response.data;
      } catch (error) {
        this.toast.error('Failed to load financial summary');
        console.error(error);
      } finally {
        this.loading.summary = false;
      }
    },
    async fetchChartData() {
      try {
        const response = await axios.get('/api/finance/reports/chart-data', {
          params: {
            start_date: this.filters.startDate,
            end_date: this.filters.endDate,
            category_id: this.filters.categoryId || null
          }
        });
        this.chartData = response.data;
      } catch (error) {
        this.toast.error('Failed to load chart data');
        console.error(error);
      } finally {
        this.loading.chartData = false;
      }
    },
    async fetchProjects() {
      try {
        const response = await axios.get('/api/projects', {
          params: { status: 'active' }
        });
        this.projects = response.data.data;
        
        // Set default project ID for export if none is selected
        if (this.projects.length > 0 && !this.exportOptions.projectId) {
          this.exportOptions.projectId = this.projects[0].id;
        }
      } catch (error) {
        this.toast.error('Failed to load projects');
        console.error(error);
      } finally {
        this.loading.projects = false;
      }
    },
    applyFilters() {
      this.fetchData();
    },
    resetFilters() {
      this.filters = {
        startDate: this.getDefaultStartDate(),
        endDate: this.getDefaultEndDate(),
        categoryId: ''
      };
      this.fetchData();
    },
    async generatePdfReport() {
      this.loading.export = true;
      try {
        const response = await axios.post('/api/finance/reports/generate-pdf', {
          start_date: this.filters.startDate,
          end_date: this.filters.endDate,
          category_id: this.filters.categoryId || null,
          report_type: this.exportOptions.reportType,
          project_id: this.exportOptions.reportType === 'project' ? this.exportOptions.projectId : null
        }, {
          responseType: 'blob'
        });
        
        // Create a blob URL and trigger download
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `financial_report_${this.exportOptions.reportType}_${new Date().toISOString().split('T')[0]}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.toast.success('PDF report generated successfully');
      } catch (error) {
        this.toast.error('Failed to generate PDF report');
        console.error(error);
      } finally {
        this.loading.export = false;
      }
    },
    async exportToExcel() {
      this.loading.export = true;
      try {
        const response = await axios.post('/api/finance/reports/export-excel', {
          start_date: this.filters.startDate,
          end_date: this.filters.endDate,
          category_id: this.filters.categoryId || null,
          report_type: this.exportOptions.reportType === 'project' ? 'donations' : this.exportOptions.reportType
        }, {
          responseType: 'blob'
        });
        
        // Create a blob URL and trigger download
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `financial_data_${this.exportOptions.reportType}_${new Date().toISOString().split('T')[0]}.xlsx`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.toast.success('Excel report generated successfully');
      } catch (error) {
        this.toast.error('Failed to export to Excel');
        console.error(error);
      } finally {
        this.loading.export = false;
      }
    }
  }
};
</script>

<style scoped>
.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border-left-color: #4F46E5;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
