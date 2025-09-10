<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Budget Details</h1>
      <div>
        <router-link
          :to="`/budgets/${$route.params.id}/edit`"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md mr-2"
        >
          Edit Budget
        </router-link>
        <router-link
          to="/budgets"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
        >
          Back to Budgets
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading budget details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <div v-else>
      <!-- Budget Overview Card -->
      <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ budget.name }}</h2>
              <div class="mt-1 flex items-center">
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
                <span
                  class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                >
                  {{ budget.category }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500">Date Range</div>
              <div class="text-gray-800">
                {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
              </div>
            </div>
          </div>

          <p v-if="budget.description" class="mt-4 text-gray-600">
            {{ budget.description }}
          </p>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Total Budget</p>
              <p class="text-xl font-bold">${{ formatNumber(budget.amount) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Spent Amount</p>
              <p class="text-xl font-bold">${{ formatNumber(budget.spent_amount) }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
              <p class="text-sm text-gray-500">Remaining</p>
              <p class="text-xl font-bold">${{ formatNumber(budget.amount - budget.spent_amount) }}</p>
            </div>
          </div>

          <div class="mt-4">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Budget Utilization</span>
              <span class="text-sm font-medium text-gray-700">{{ utilizationPercentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div
                class="h-2.5 rounded-full"
                :class="utilizationColorClass"
                :style="{ width: Math.min(100, utilizationPercentage) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Expenses -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Related Expenses</h3>
        </div>

        <!-- Empty State for Expenses -->
        <div v-if="budget.expenses && budget.expenses.length === 0" class="p-6 text-center">
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
          <h3 class="mt-2 text-sm font-medium text-gray-900">No expenses found</h3>
          <p class="mt-1 text-sm text-gray-500">No expenses have been recorded for this budget yet.</p>
          <div class="mt-6">
            <router-link
              to="/expenses/create"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Create New Expense
            </router-link>
          </div>
        </div>

        <!-- Expense List -->
        <div v-else-if="budget.expenses && budget.expenses.length > 0">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Title
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
                  Date
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
              <tr v-for="expense in budget.expenses" :key="expense.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-medium text-gray-900">{{ expense.title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                  >
                    {{ expense.category }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                  ${{ formatNumber(expense.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                  {{ formatDate(expense.expense_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <router-link
                    :to="`/expenses/${expense.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BudgetDetail',
  data() {
    return {
      budget: {
        id: null,
        name: '',
        description: '',
        amount: 0,
        spent_amount: 0,
        start_date: '',
        end_date: '',
        category: '',
        status: '',
        expenses: []
      },
      loading: true,
      error: null
    };
  },
  computed: {
    utilizationPercentage() {
      if (!this.budget.amount || this.budget.amount <= 0) return 0;
      return Math.round((this.budget.spent_amount / this.budget.amount) * 100);
    },
    utilizationColorClass() {
      if (this.utilizationPercentage > 90) return 'bg-red-600';
      if (this.utilizationPercentage > 75) return 'bg-yellow-600';
      return 'bg-green-600';
    }
  },
  created() {
    this.loadBudget();
  },
  methods: {
    async loadBudget() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/budgets/${this.$route.params.id}`);
        this.budget = response.data.data;
      } catch (error) {
        this.error = 'Failed to load budget details. Please try again.';
        console.error('Error loading budget:', error);
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
  }
};
</script>
