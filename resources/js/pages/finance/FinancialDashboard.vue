<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Financial Dashboard</h1>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-wrap items-center">
        <div class="w-full md:w-auto mr-4 mb-2 md:mb-0">
          <label class="block text-gray-700 text-sm font-bold mb-2">
            Date Range
          </label>
          <div class="flex space-x-2">
            <input 
              v-model="filters.start_date" 
              type="date" 
              class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchData"
            >
            <span class="self-center">to</span>
            <input 
              v-model="filters.end_date" 
              type="date" 
              class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              @change="fetchData"
            >
          </div>
        </div>
        <button 
          @click="resetFilters" 
          class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Income -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Income</p>
              <p class="text-2xl font-bold text-gray-800">${{ formatNumber(summary.total_income) }}</p>
            </div>
          </div>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Expenses</p>
              <p class="text-2xl font-bold text-gray-800">${{ formatNumber(summary.total_expenses) }}</p>
            </div>
          </div>
        </div>

        <!-- Net Income -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Net Income</p>
              <p 
                class="text-2xl font-bold"
                :class="summary.net_income >= 0 ? 'text-green-600' : 'text-red-600'"
              >
                ${{ formatNumber(summary.net_income) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Total Donors -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Donors</p>
              <p class="text-2xl font-bold text-gray-800">{{ summary.total_donors }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Income vs Expenses Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Income vs Expenses</h2>
          <div class="h-64">
            <canvas ref="incomeExpensesChart"></canvas>
          </div>
        </div>

        <!-- Income by Category Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Income by Category</h2>
          <div class="h-64">
            <canvas ref="incomeCategoryChart"></canvas>
          </div>
        </div>

        <!-- Expenses by Category Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Expenses by Category</h2>
          <div class="h-64">
            <canvas ref="expenseCategoryChart"></canvas>
          </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Monthly Trend</h2>
          <div class="h-64">
            <canvas ref="monthlyTrendChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Top Donors and Top Expenses -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Donors -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Top Donors</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Donor
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Donations
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(donor, index) in topDonors" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ donor.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${{ formatNumber(donor.amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ donor.count }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Top Expenses -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-700 mb-4">Recent Expenses</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Title
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(expense, index) in recentExpenses" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ expense.title }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatCategory(expense.category) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${{ formatNumber(expense.amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(expense.date) }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <router-link 
            to="/donations/create" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded text-center"
          >
            Record Donation
          </router-link>
          <router-link 
            to="/expenses/create" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded text-center"
          >
            Record Expense
          </router-link>
          <router-link 
            to="/donations" 
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center"
          >
            View All Donations
          </router-link>
          <router-link 
            to="/expenses" 
            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded text-center"
          >
            View All Expenses
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Chart from 'chart.js/auto';

export default {
  name: 'FinancialDashboard',
  data() {
    return {
      filters: {
        start_date: '',
        end_date: ''
      },
      loading: true,
      error: null,
      summary: {
        total_income: 0,
        total_expenses: 0,
        net_income: 0,
        total_donors: 0
      },
      incomeByCategory: [],
      expensesByCategory: [],
      monthlyData: [],
      topDonors: [],
      recentExpenses: [],
      charts: {
        incomeExpenses: null,
        incomeCategory: null,
        expenseCategory: null,
        monthlyTrend: null
      }
    };
  },
  mounted() {
    // Set default date range to current year
    const currentYear = new Date().getFullYear();
    this.filters.start_date = `${currentYear}-01-01`;
    this.filters.end_date = `${currentYear}-12-31`;
    
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Fetch financial summary
        const summaryResponse = await axios.get('/finance/summary', {
          params: this.filters
        });
        
        if (summaryResponse.data.status === 'success') {
          this.summary = summaryResponse.data.data.summary;
          this.incomeByCategory = summaryResponse.data.data.income_by_category;
          this.expensesByCategory = summaryResponse.data.data.expenses_by_category;
          this.monthlyData = summaryResponse.data.data.monthly_data;
          this.topDonors = summaryResponse.data.data.top_donors;
          this.recentExpenses = summaryResponse.data.data.recent_expenses;
          
          // Initialize charts after data is loaded
          this.$nextTick(() => {
            this.initCharts();
          });
        } else {
          this.error = 'Failed to load financial data';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching financial data';
      } finally {
        this.loading = false;
      }
    },
    
    initCharts() {
      // Destroy existing charts if they exist
      Object.values(this.charts).forEach(chart => {
        if (chart) {
          chart.destroy();
        }
      });
      
      // Income vs Expenses Chart
      this.charts.incomeExpenses = new Chart(this.$refs.incomeExpensesChart, {
        type: 'bar',
        data: {
          labels: ['Income', 'Expenses'],
          datasets: [{
            label: 'Amount',
            data: [this.summary.total_income, this.summary.total_expenses],
            backgroundColor: [
              'rgba(75, 192, 192, 0.6)',
              'rgba(255, 99, 132, 0.6)'
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
      
      // Income by Category Chart
      this.charts.incomeCategory = new Chart(this.$refs.incomeCategoryChart, {
        type: 'pie',
        data: {
          labels: this.incomeByCategory.map(item => item.category),
          datasets: [{
            label: 'Income',
            data: this.incomeByCategory.map(item => item.amount),
            backgroundColor: [
              'rgba(75, 192, 192, 0.6)',
              'rgba(54, 162, 235, 0.6)',
              'rgba(153, 102, 255, 0.6)',
              'rgba(255, 159, 64, 0.6)',
              'rgba(255, 99, 132, 0.6)',
              'rgba(255, 206, 86, 0.6)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
      
      // Expenses by Category Chart
      this.charts.expenseCategory = new Chart(this.$refs.expenseCategoryChart, {
        type: 'pie',
        data: {
          labels: this.expensesByCategory.map(item => this.formatCategory(item.category)),
          datasets: [{
            label: 'Expenses',
            data: this.expensesByCategory.map(item => item.amount),
            backgroundColor: [
              'rgba(255, 99, 132, 0.6)',
              'rgba(255, 159, 64, 0.6)',
              'rgba(255, 206, 86, 0.6)',
              'rgba(75, 192, 192, 0.6)',
              'rgba(54, 162, 235, 0.6)',
              'rgba(153, 102, 255, 0.6)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
      
      // Monthly Trend Chart
      this.charts.monthlyTrend = new Chart(this.$refs.monthlyTrendChart, {
        type: 'line',
        data: {
          labels: this.monthlyData.map(item => `${item.month}/${item.year}`),
          datasets: [
            {
              label: 'Income',
              data: this.monthlyData.map(item => item.income),
              borderColor: 'rgba(75, 192, 192, 1)',
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              tension: 0.1,
              fill: true
            },
            {
              label: 'Expenses',
              data: this.monthlyData.map(item => item.expenses),
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              tension: 0.1,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    },
    
    resetFilters() {
      const currentYear = new Date().getFullYear();
      this.filters.start_date = `${currentYear}-01-01`;
      this.filters.end_date = `${currentYear}-12-31`;
      this.fetchData();
    },
    
    formatNumber(value) {
      if (value === undefined || value === null) return '0.00';
      return parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    formatCategory(category) {
      if (!category) return '';
      
      // Convert snake_case to Title Case
      return category
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    }
  }
};
</script>
