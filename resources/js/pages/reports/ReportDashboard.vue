<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Analytics Dashboard</h1>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
      <p>{{ error }}</p>
    </div>
    
    <!-- Favorite Reports Section -->
    <div class="bg-white shadow rounded-lg mb-6 p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Favorite Reports</h2>
      <div v-if="favoriteReports.length === 0" class="text-gray-500 italic">
        No favorite reports yet. Mark reports as favorites to see them here.
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="report in favoriteReports" :key="report.id" class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
          <div class="flex justify-between items-start">
            <h3 class="font-medium text-gray-900">{{ report.name }}</h3>
            <div class="text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-1">{{ report.description || 'No description' }}</p>
          <p class="text-xs text-gray-500 mt-2">Type: {{ report.type }}</p>
          <div class="mt-3 flex justify-end">
            <router-link :to="`/reports/${report.id}`" class="text-sm text-blue-600 hover:text-blue-800">View Report</router-link>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Reports Section -->
    <div class="bg-white shadow rounded-lg mb-6 p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Reports</h2>
      <div v-if="recentReports.length === 0" class="text-gray-500 italic">
        No reports created yet.
      </div>
      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="report in recentReports" :key="report.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ report.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ report.type }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ new Date(report.created_at).toLocaleDateString() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <router-link :to="`/reports/${report.id}`" class="text-blue-600 hover:text-blue-900 mr-3">View</router-link>
                <router-link :to="`/reports/${report.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit</router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else>
      <!-- Date Range Selector -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between">
          <div class="flex items-center space-x-4">
            <h2 class="text-lg font-semibold text-gray-700">Analytics Period</h2>
            <div class="relative">
              <select 
                v-model="period" 
                @change="loadDashboardData"
                class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="week">Last 7 days</option>
                <option value="month">Last 30 days</option>
                <option value="quarter">Last 3 months</option>
                <option value="year">Last 12 months</option>
                <option value="custom">Custom Range</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
          </div>
          <div v-if="period === 'custom'" class="flex items-center space-x-2 mt-2 sm:mt-0">
            <input 
              type="date" 
              v-model="startDate" 
              class="border border-gray-300 rounded-md px-3 py-2"
            />
            <span class="text-gray-500">to</span>
            <input 
              type="date" 
              v-model="endDate" 
              class="border border-gray-300 rounded-md px-3 py-2"
            />
            <button 
              @click="loadDashboardData" 
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
            >
              Apply
            </button>
          </div>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 bg-blue-50 border-b border-blue-100">
            <h3 class="text-lg font-semibold text-blue-800">Total Donations</h3>
          </div>
          <div class="p-4">
            <p class="text-3xl font-bold text-gray-800">{{ formatCurrency(metrics.totalDonations) }}</p>
            <div class="flex items-center mt-2">
              <span :class="metrics.donationTrend > 0 ? 'text-green-600' : 'text-red-600'">
                <svg v-if="metrics.donationTrend > 0" class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                </svg>
                {{ Math.abs(metrics.donationTrend) }}% from previous period
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 bg-green-50 border-b border-green-100">
            <h3 class="text-lg font-semibold text-green-800">Average Attendance</h3>
          </div>
          <div class="p-4">
            <p class="text-3xl font-bold text-gray-800">{{ metrics.avgAttendance }}</p>
            <div class="flex items-center mt-2">
              <span :class="metrics.attendanceTrend > 0 ? 'text-green-600' : 'text-red-600'">
                <svg v-if="metrics.attendanceTrend > 0" class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                </svg>
                {{ Math.abs(metrics.attendanceTrend) }}% from previous period
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 bg-purple-50 border-b border-purple-100">
            <h3 class="text-lg font-semibold text-purple-800">New Members</h3>
          </div>
          <div class="p-4">
            <p class="text-3xl font-bold text-gray-800">{{ metrics.newMembers }}</p>
            <div class="flex items-center mt-2">
              <span :class="metrics.memberTrend > 0 ? 'text-green-600' : 'text-red-600'">
                <svg v-if="metrics.memberTrend > 0" class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                </svg>
                {{ Math.abs(metrics.memberTrend) }}% from previous period
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 bg-red-50 border-b border-red-100">
            <h3 class="text-lg font-semibold text-red-800">Pledge Fulfillment</h3>
          </div>
          <div class="p-4">
            <p class="text-3xl font-bold text-gray-800">{{ metrics.pledgeFulfillment }}%</p>
            <div class="flex items-center mt-2">
              <span :class="metrics.pledgeTrend > 0 ? 'text-green-600' : 'text-red-600'">
                <svg v-if="metrics.pledgeTrend > 0" class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                </svg>
                {{ Math.abs(metrics.pledgeTrend) }}% from previous period
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Donations Chart -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Donations Over Time</h3>
          </div>
          <div class="p-4">
            <canvas ref="donationsChart" height="250"></canvas>
          </div>
        </div>

        <!-- Attendance Chart -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Attendance Trends</h3>
          </div>
          <div class="p-4">
            <canvas ref="attendanceChart" height="250"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Reports & Favorite Reports -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Reports -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Reports</h3>
          </div>
          <div class="p-4">
            <div v-if="recentReports.length === 0" class="text-center py-4 text-gray-500">
              No reports generated yet
            </div>
            <ul v-else class="divide-y divide-gray-200">
              <li v-for="report in recentReports" :key="report.id" class="py-3">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">{{ report.name }}</h4>
                    <div class="flex items-center mt-1">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2"
                        :class="getTypeClass(report.type)"
                      >
                        {{ report.type_display }}
                      </span>
                      <span class="text-xs text-gray-500">{{ formatDate(report.last_generated_at) }}</span>
                    </div>
                  </div>
                  <router-link
                    :to="`/reports/${report.id}`"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    View
                  </router-link>
                </div>
              </li>
            </ul>
          </div>
          <div class="p-4 bg-gray-50 border-t border-gray-200">
            <router-link
              to="/reports"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
            >
              View all reports
              <svg class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
              </svg>
            </router-link>
          </div>
        </div>

        <!-- Favorite Reports -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Favorite Reports</h3>
          </div>
          <div class="p-4">
            <div v-if="favoriteReports.length === 0" class="text-center py-4 text-gray-500">
              No favorite reports yet
            </div>
            <ul v-else class="divide-y divide-gray-200">
              <li v-for="report in favoriteReports" :key="report.id" class="py-3">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">{{ report.name }}</h4>
                    <div class="flex items-center mt-1">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2"
                        :class="getTypeClass(report.type)"
                      >
                        {{ report.type_display }}
                      </span>
                      <span class="text-xs text-gray-500">{{ formatDate(report.last_generated_at) }}</span>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="generateFavoriteReport(report)"
                      class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-xs"
                    >
                      Generate
                    </button>
                    <router-link
                      :to="`/reports/${report.id}`"
                      class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                      View
                    </router-link>
                  </div>
                </div>
              </li>
            </ul>
          </div>
          <div class="p-4 bg-gray-50 border-t border-gray-200">
            <router-link
              to="/reports/create"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
            >
              Create new report
              <svg class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 01-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import Chart from 'chart.js/auto';
import ReportNavigation from '../../components/reports/ReportNavigation.vue';

export default {
  name: 'ReportDashboard',
  data() {
    return {
      period: 'month',
      startDate: this.getDefaultStartDate(),
      endDate: this.getDefaultEndDate(),
      donationsChart: null,
      attendanceChart: null,
      recentReports: [],
      favoriteReports: []
    };
  },
  computed: {
    ...mapState({
      loading: state => state.reports.loading,
      error: state => state.reports.error,
      metrics: state => state.reports.metrics,
      donationsChart: state => state.reports.donationsChart,
      attendanceChart: state => state.reports.attendanceChart,
      favoriteReports: state => state.reports.favoriteReports,
      recentReports: state => state.reports.recentReports
    }),
    ...mapGetters({
      isLoading: 'reports/isLoading',
      getError: 'reports/getError',
      getMetrics: 'reports/getMetrics',
      getDonationsChart: 'reports/getDonationsChart',
      getAttendanceChart: 'reports/getAttendanceChart',
      getFavoriteReports: 'reports/getFavoriteReports',
      getRecentReports: 'reports/getRecentReports'
    }),
    getDefaultStartDate() {
      const date = new Date();
      date.setMonth(date.getMonth() - 1);
      return date.toISOString().split('T')[0];
    },
    getDefaultEndDate() {
      return new Date().toISOString().split('T')[0];
    }
  },
  async mounted() {
    try {
      await this.loadDashboardData();
    } catch (error) {
      console.error('Error loading dashboard data:', error);
    }
  },
  methods: {
    ...mapActions('reports', [
      'fetchMetrics',
      'fetchDonationsChart',
      'fetchAttendanceChart',
      'fetchFavoriteReports',
      'fetchRecentReports'
    ]),
    async loadDashboardData() {
      try {
        // Prepare date parameters
        const params = {
          start_date: this.startDate,
          end_date: this.endDate
        };
        
        // Fetch all data in parallel
        await Promise.all([
          this.fetchMetrics(params),
          this.fetchDonationsChart(params),
          this.fetchAttendanceChart(params),
          this.fetchFavoriteReports(),
          this.fetchRecentReports()
        ]);
        
        // Initialize charts after data is loaded
        this.initCharts();
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      }
    },
    
    initCharts() {
      this.renderDonationsChart();
      this.renderAttendanceChart();
    },
    
    renderDonationsChart() {
      if (this.donationsChart) {
        this.donationsChart.destroy();
      }
      
      if (!this.$refs.donationsChart) return;
      
      const ctx = this.$refs.donationsChart.getContext('2d');
      
      this.donationsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.donationsData.map(item => item.label),
          datasets: [
            {
              label: 'Donations',
              data: this.donationsData.map(item => item.amount),
              borderColor: 'rgba(59, 130, 246, 1)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              fill: true,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => this.formatCurrency(value, false)
              }
            }
          }
        }
      });
    },
    
    renderAttendanceChart() {
      if (this.attendanceChart) {
        this.attendanceChart.destroy();
      }
      
      if (!this.$refs.attendanceChart) return;
      
      const ctx = this.$refs.attendanceChart.getContext('2d');
      
      this.attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.attendanceData.map(item => item.label),
          datasets: [
            {
              label: 'Attendance',
              data: this.attendanceData.map(item => item.count),
              backgroundColor: 'rgba(16, 185, 129, 0.7)'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    },
    
    generateFavoriteReport(report) {
      this.$router.push(`/reports/${report.id}?generate=true`);
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Never';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    
    formatCurrency(value, symbol = true) {
      if (value === undefined || value === null) return '';
      return new Intl.NumberFormat('en-US', {
        style: symbol ? 'currency' : 'decimal',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value);
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
    }
  }
};
</script>
