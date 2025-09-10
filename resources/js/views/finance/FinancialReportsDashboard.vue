<template>
  <div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Financial Reports Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">
          Comprehensive overview of your church's financial performance
        </p>
      </div>
      
      <!-- Date Range Filter -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between">
          <div class="flex items-center space-x-4">
            <div>
              <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
              <input
                type="date"
                id="start-date"
                v-model="filters.startDate"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
            <div>
              <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
              <input
                type="date"
                id="end-date"
                v-model="filters.endDate"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
              <select
                id="category"
                v-model="filters.categoryId"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="mt-4 sm:mt-0">
            <button
              @click="applyFilters"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Apply Filters
            </button>
            <button
              @click="resetFilters"
              class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Reset
            </button>
            <button
              @click="exportReport"
              class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Export
            </button>
          </div>
        </div>
      </div>
      
      <!-- Loading Indicator -->
      <div v-if="loading" class="flex justify-center my-12">
        <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      
      <div v-else>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Donations</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">${{ summaryData.totalDonations.toLocaleString() }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">${{ summaryData.totalExpenses.toLocaleString() }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Net Income</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">${{ summaryData.netIncome.toLocaleString() }}</div>
                      <p :class="[summaryData.netIncome >= 0 ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                        <span class="sr-only">{{ summaryData.netIncome >= 0 ? 'Increased' : 'Decreased' }} by</span>
                        {{ summaryData.netIncome >= 0 ? '+' : '' }}{{ summaryData.netIncomePercentage }}%
                      </p>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Average Donation</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">${{ summaryData.averageDonation.toLocaleString() }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Donations by Category Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Donations by Category</h3>
            <div class="h-80">
              <canvas ref="categoryChart"></canvas>
            </div>
          </div>
          
          <!-- Monthly Donations Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Donations</h3>
            <div class="h-80">
              <canvas ref="monthlyChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Project Funding Progress -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Project Funding Progress</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Goal</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="project in projects" :key="project.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ project.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ project.goal_amount.toLocaleString() }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ project.current_amount.toLocaleString() }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                      <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${project.percentComplete}%` }"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ project.percentComplete }}%</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      project.status === 'active' ? 'bg-green-100 text-green-800' : 
                      project.status === 'completed' ? 'bg-blue-100 text-blue-800' : 
                      'bg-gray-100 text-gray-800',
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                    ]">
                      {{ project.status.charAt(0).toUpperCase() + project.status.slice(1) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            <router-link to="/finance/donations" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
              View all
            </router-link>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="donation in recentDonations" :key="donation.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(donation.donation_date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ donation.is_anonymous ? 'Anonymous' : (donation.member ? donation.member.name : 'N/A') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ donation.category ? donation.category.name : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ donation.project ? donation.project.name : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ donation.amount.toLocaleString() }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      donation.payment_status === 'completed' ? 'bg-green-100 text-green-800' : 
                      donation.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                      donation.payment_status === 'failed' ? 'bg-red-100 text-red-800' : 
                      donation.payment_status === 'refunded' ? 'bg-gray-100 text-gray-800' : 
                      'bg-blue-100 text-blue-800',
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                    ]">
                      {{ donation.payment_status.charAt(0).toUpperCase() + donation.payment_status.slice(1) }}
                    </span>
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
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

export default {
  name: 'FinancialReportsDashboard',
  
  setup() {
    const loading = ref(true);
    const categoryChart = ref(null);
    const monthlyChart = ref(null);
    const categories = ref([]);
    const projects = ref([]);
    const recentDonations = ref([]);
    
    const filters = ref({
      startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split('T')[0],
      endDate: new Date().toISOString().split('T')[0],
      categoryId: '',
    });
    
    const summaryData = ref({
      totalDonations: 0,
      totalExpenses: 0,
      netIncome: 0,
      netIncomePercentage: 0,
      averageDonation: 0,
    });
    
    const categoryChartInstance = ref(null);
    const monthlyChartInstance = ref(null);
    
    // Load data
    const loadData = async () => {
      loading.value = true;
      
      try {
        // Fetch categories
        const categoriesResponse = await axios.get('/api/donation-categories');
        categories.value = categoriesResponse.data.data;
        
        // Fetch projects
        const projectsResponse = await axios.get('/api/projects');
        projects.value = projectsResponse.data.data.map(project => {
          const percentComplete = project.goal_amount > 0 
            ? Math.round((project.current_amount / project.goal_amount) * 100) 
            : 0;
          
          return {
            ...project,
            percentComplete,
          };
        });
        
        // Fetch financial summary
        const summaryResponse = await axios.get('/api/finance/summary', {
          params: {
            start_date: filters.value.startDate,
            end_date: filters.value.endDate,
            category_id: filters.value.categoryId || undefined,
          }
        });
        
        summaryData.value = summaryResponse.data;
        
        // Fetch recent donations
        const donationsResponse = await axios.get('/api/donations', {
          params: {
            limit: 10,
            sort: '-donation_date',
          }
        });
        
        recentDonations.value = donationsResponse.data.data;
        
        // Fetch data for charts
        const chartDataResponse = await axios.get('/api/finance/chart-data', {
          params: {
            start_date: filters.value.startDate,
            end_date: filters.value.endDate,
            category_id: filters.value.categoryId || undefined,
          }
        });
        
        // Update charts
        updateCharts(chartDataResponse.data);
      } catch (error) {
        console.error('Error loading financial data:', error);
        // Use placeholder data for demonstration
        usePlaceholderData();
      } finally {
        loading.value = false;
      }
    };
    
    // Update charts with data
    const updateCharts = (chartData) => {
      // Update category chart
      if (categoryChartInstance.value) {
        categoryChartInstance.value.destroy();
      }
      
      categoryChartInstance.value = new Chart(categoryChart.value, {
        type: 'doughnut',
        data: {
          labels: chartData.categories.map(c => c.name),
          datasets: [{
            data: chartData.categories.map(c => c.total),
            backgroundColor: [
              '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
              '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
            ],
            borderWidth: 1,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right',
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const value = context.raw;
                  return `$${value.toLocaleString()}`;
                }
              }
            }
          }
        }
      });
      
      // Update monthly chart
      if (monthlyChartInstance.value) {
        monthlyChartInstance.value.destroy();
      }
      
      monthlyChartInstance.value = new Chart(monthlyChart.value, {
        type: 'bar',
        data: {
          labels: chartData.monthly.map(m => m.month),
          datasets: [{
            label: 'Donations',
            data: chartData.monthly.map(m => m.donations),
            backgroundColor: '#4F46E5',
            borderColor: '#4338CA',
            borderWidth: 1
          }, {
            label: 'Expenses',
            data: chartData.monthly.map(m => m.expenses),
            backgroundColor: '#EF4444',
            borderColor: '#DC2626',
            borderWidth: 1
          }]
        },
        options: {
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
            tooltip: {
              callbacks: {
                label: function(context) {
                  const value = context.raw;
                  return `${context.dataset.label}: $${value.toLocaleString()}`;
                }
              }
            }
          }
        }
      });
    };
    
    // Use placeholder data for demonstration
    const usePlaceholderData = () => {
      summaryData.value = {
        totalDonations: 25750,
        totalExpenses: 18200,
        netIncome: 7550,
        netIncomePercentage: 12.5,
        averageDonation: 215.50,
      };
      
      projects.value = [
        { id: 1, name: 'Building Renovation', goal_amount: 50000, current_amount: 35000, percentComplete: 70, status: 'active' },
        { id: 2, name: 'Mission Trip', goal_amount: 15000, current_amount: 12000, percentComplete: 80, status: 'active' },
        { id: 3, name: 'Youth Camp', goal_amount: 8000, current_amount: 8000, percentComplete: 100, status: 'completed' },
        { id: 4, name: 'Community Outreach', goal_amount: 5000, current_amount: 2500, percentComplete: 50, status: 'active' },
      ];
      
      recentDonations.value = [
        { id: 1, donation_date: '2025-05-22', member: { name: 'John Doe' }, is_anonymous: false, category: { name: 'Tithe' }, project: null, amount: 500, payment_status: 'completed' },
        { id: 2, donation_date: '2025-05-21', member: { name: 'Jane Smith' }, is_anonymous: false, category: { name: 'Offering' }, project: null, amount: 200, payment_status: 'completed' },
        { id: 3, donation_date: '2025-05-20', member: { name: 'Robert Johnson' }, is_anonymous: false, category: { name: 'Missions' }, project: { name: 'Mission Trip' }, amount: 1000, payment_status: 'completed' },
        { id: 4, donation_date: '2025-05-19', member: null, is_anonymous: true, category: { name: 'Building Fund' }, project: { name: 'Building Renovation' }, amount: 5000, payment_status: 'completed' },
        { id: 5, donation_date: '2025-05-18', member: { name: 'Mary Williams' }, is_anonymous: false, category: { name: 'Youth Ministry' }, project: { name: 'Youth Camp' }, amount: 350, payment_status: 'completed' },
      ];
      
      // Placeholder chart data
      const chartData = {
        categories: [
          { name: 'Tithe', total: 12500 },
          { name: 'Offering', total: 5000 },
          { name: 'Building Fund', total: 7500 },
          { name: 'Missions', total: 3500 },
          { name: 'Youth Ministry', total: 1500 },
        ],
        monthly: [
          { month: 'Jan', donations: 15000, expenses: 12000 },
          { month: 'Feb', donations: 18000, expenses: 13500 },
          { month: 'Mar', donations: 22000, expenses: 15000 },
          { month: 'Apr', donations: 20000, expenses: 16500 },
          { month: 'May', donations: 25750, expenses: 18200 },
        ]
      };
      
      updateCharts(chartData);
    };
    
    // Apply filters
    const applyFilters = () => {
      loadData();
    };
    
    // Reset filters
    const resetFilters = () => {
      filters.value = {
        startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0],
        categoryId: '',
      };
      
      loadData();
    };
    
    // Export report
    const exportReport = () => {
      // Placeholder for export functionality
      alert('Export functionality will be implemented here');
    };
    
    // Format date
    const formatDate = (dateString) => {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    };
    
    onMounted(() => {
      loadData();
    });
    
    return {
      loading,
      categoryChart,
      monthlyChart,
      categories,
      projects,
      recentDonations,
      filters,
      summaryData,
      applyFilters,
      resetFilters,
      exportReport,
      formatDate,
    };
  }
};
</script>
