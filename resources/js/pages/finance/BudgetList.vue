<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Budget Management</h1>
      <router-link
        to="/budgets/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
      >
        Create New Budget
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filters.category"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadBudgets"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="loadBudgets"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            type="date"
            v-model="filters.start_date"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            type="date"
            v-model="filters.end_date"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
          />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button
          @click="loadBudgets"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mr-2"
        >
          Apply Filters
        </button>
        <button
          @click="resetFilters"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Budget Overview Card -->
    <div class="bg-white shadow rounded-lg p-4 mb-6" v-if="overview">
      <h2 class="text-xl font-semibold mb-4">Budget Overview</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Total Budget</p>
          <p class="text-xl font-bold">${{ formatNumber(overview.total_budget) }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Total Spent</p>
          <p class="text-xl font-bold">${{ formatNumber(overview.total_spent) }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Remaining</p>
          <p class="text-xl font-bold">${{ formatNumber(overview.remaining) }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <p class="text-sm text-gray-500">Utilization</p>
          <p class="text-xl font-bold">{{ Math.round(overview.utilization_percentage) }}%</p>
          <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
            <div
              class="bg-blue-600 h-2.5 rounded-full"
              :style="{ width: Math.min(100, overview.utilization_percentage) + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading budgets...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="budgets.length === 0" class="text-center py-8">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No budgets found</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new budget.</p>
      <div class="mt-6">
        <router-link
          to="/budgets/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          Create New Budget
        </router-link>
      </div>
    </div>

    <!-- Budget List -->
    <div v-else class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Name
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Category
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Amount
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Spent
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Utilization
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Date Range
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="budget in budgets" :key="budget.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="font-medium text-gray-900">{{ budget.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
              >
                {{ budget.category }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
              ${{ formatNumber(budget.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
              ${{ formatNumber(budget.spent_amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span class="mr-2 text-sm">{{ Math.round(getUtilizationPercentage(budget)) }}%</span>
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full"
                    :class="getUtilizationColorClass(budget)"
                    :style="{ width: Math.min(100, getUtilizationPercentage(budget)) + '%' }"
                  ></div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="{
                  'bg-green-100 text-green-800': budget.status === 'active',
                  'bg-yellow-100 text-yellow-800': budget.status === 'inactive',
                  'bg-gray-100 text-gray-800': budget.status === 'completed'
                }"
              >
                {{ budget.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <router-link
                :to="`/budgets/${budget.id}`"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                View
              </router-link>
              <router-link
                :to="`/budgets/${budget.id}/edit`"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </router-link>
              <button
                @click="confirmDelete(budget)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
            to
            <span class="font-medium">
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
            </span>
            of
            <span class="font-medium">{{ pagination.total }}</span>
            results
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
              >
                Previous
              </button>
              <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="goToPage(page)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50"
                :class="{
                  'z-10 bg-blue-50 border-blue-500 text-blue-600': page === pagination.current_page,
                  'text-gray-500': page !== pagination.current_page
                }"
              >
                {{ page }}
              </button>
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{
                  'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page
                }"
              >
                Next
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-red-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Budget</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this budget? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deleteBudget"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button
              @click="showDeleteModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BudgetList',
  data() {
    return {
      budgets: [],
      categories: [],
      overview: null,
      loading: true,
      error: null,
      filters: {
        category: '',
        status: '',
        start_date: '',
        end_date: '',
        sort_by: 'start_date',
        sort_dir: 'desc'
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      showDeleteModal: false,
      budgetToDelete: null
    };
  },
  created() {
    this.loadCategories();
    this.loadBudgetOverview();
    this.loadBudgets();
  },
  methods: {
    async loadBudgets() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          ...this.filters
        };
        
        const response = await axios.get('/api/budgets', { params });
        
        this.budgets = response.data.data.data;
        this.pagination = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          per_page: response.data.data.per_page,
          total: response.data.data.total
        };
      } catch (error) {
        this.error = 'Failed to load budgets. Please try again.';
        console.error('Error loading budgets:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async loadCategories() {
      try {
        const response = await axios.get('/api/budgets/categories');
        this.categories = response.data.data;
      } catch (error) {
        console.error('Error loading categories:', error);
      }
    },
    
    async loadBudgetOverview() {
      try {
        const response = await axios.get('/api/budgets/overview');
        this.overview = response.data.data;
      } catch (error) {
        console.error('Error loading budget overview:', error);
      }
    },
    
    resetFilters() {
      this.filters = {
        category: '',
        status: '',
        start_date: '',
        end_date: '',
        sort_by: 'start_date',
        sort_dir: 'desc'
      };
      this.loadBudgets();
    },
    
    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      this.loadBudgets();
    },
    
    getPageNumbers() {
      const pages = [];
      const totalPages = this.pagination.last_page;
      const currentPage = this.pagination.current_page;
      
      // Show at most 5 page numbers
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, startPage + 4);
      
      // Adjust if we're near the end
      if (endPage - startPage < 4 && startPage > 1) {
        startPage = Math.max(1, endPage - 4);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      return pages;
    },
    
    confirmDelete(budget) {
      this.budgetToDelete = budget;
      this.showDeleteModal = true;
    },
    
    async deleteBudget() {
      if (!this.budgetToDelete) return;
      
      try {
        await axios.delete(`/api/budgets/${this.budgetToDelete.id}`);
        this.showDeleteModal = false;
        this.budgetToDelete = null;
        this.loadBudgets();
        this.loadBudgetOverview();
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.error = error.response.data.message;
        } else {
          this.error = 'Failed to delete budget. Please try again.';
        }
        console.error('Error deleting budget:', error);
      }
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    
    getUtilizationPercentage(budget) {
      return budget.amount > 0 ? (budget.spent_amount / budget.amount) * 100 : 0;
    },
    
    getUtilizationColorClass(budget) {
      const percentage = this.getUtilizationPercentage(budget);
      if (percentage > 90) return 'bg-red-600';
      if (percentage > 75) return 'bg-yellow-600';
      return 'bg-green-600';
    }
  }
};
</script>
