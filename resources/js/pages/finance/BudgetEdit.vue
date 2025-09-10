<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Edit Budget</h1>
      <router-link
        to="/budgets"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
      >
        Back to Budgets
      </router-link>
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

    <div v-else class="bg-white shadow rounded-lg p-6">
      <!-- Error Alert -->
      <div
        v-if="formError"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Error!</strong>
        <p>{{ formError }}</p>
        <ul v-if="validationErrors" class="list-disc pl-5 mt-2">
          <li v-for="(errors, field) in validationErrors" :key="field">
            {{ field }}: {{ errors.join(', ') }}
          </li>
        </ul>
      </div>

      <!-- Success Alert -->
      <div
        v-if="success"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Success!</strong>
        <p>{{ success }}</p>
      </div>

      <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Budget Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
              Budget Name <span class="text-red-600">*</span>
            </label>
            <input
              type="text"
              id="name"
              v-model="budget.name"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <!-- Budget Category -->
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
              Category <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <input
                type="text"
                id="category"
                v-model="budget.category"
                list="category-list"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <datalist id="category-list">
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </datalist>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              You can select an existing category or enter a new one
            </p>
          </div>

          <!-- Budget Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
              Budget Amount <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">$</span>
              </div>
              <input
                type="number"
                id="amount"
                v-model="budget.amount"
                step="0.01"
                min="0.01"
                class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>
          </div>

          <!-- Spent Amount (Read-only) -->
          <div>
            <label for="spent_amount" class="block text-sm font-medium text-gray-700 mb-1">
              Spent Amount
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">$</span>
              </div>
              <input
                type="number"
                id="spent_amount"
                v-model="budget.spent_amount"
                step="0.01"
                min="0"
                class="w-full bg-gray-100 border border-gray-300 rounded-md pl-7 pr-3 py-2"
                readonly
              />
            </div>
            <p class="text-xs text-gray-500 mt-1">
              This amount is calculated from expenses and cannot be directly edited
            </p>
          </div>

          <!-- Budget Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
              Status <span class="text-red-600">*</span>
            </label>
            <select
              id="status"
              v-model="budget.status"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
              Start Date <span class="text-red-600">*</span>
            </label>
            <input
              type="date"
              id="start_date"
              v-model="budget.start_date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <!-- End Date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
              End Date <span class="text-red-600">*</span>
            </label>
            <input
              type="date"
              id="end_date"
              v-model="budget.end_date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
              :min="budget.start_date"
            />
            <p v-if="dateError" class="text-red-500 text-xs mt-1">{{ dateError }}</p>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-6">
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Description
          </label>
          <textarea
            id="description"
            v-model="budget.description"
            rows="4"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Budget Utilization -->
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-700 mb-2">Budget Utilization</h3>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-sm text-gray-500">Utilization:</span>
                <span class="ml-2 font-medium">{{ utilizationPercentage }}%</span>
              </div>
              <div>
                <span class="text-sm text-gray-500">Remaining:</span>
                <span class="ml-2 font-medium">${{ formatNumber(remainingAmount) }}</span>
              </div>
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

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            @click="$router.push('/budgets')"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md mr-2"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
            :disabled="submitting"
          >
            <span v-if="submitting">
              <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Saving...
            </span>
            <span v-else>Update Budget</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BudgetEdit',
  data() {
    return {
      budget: {
        id: null,
        name: '',
        description: '',
        amount: '',
        spent_amount: 0,
        start_date: '',
        end_date: '',
        category: '',
        status: 'active'
      },
      categories: [],
      loading: true,
      submitting: false,
      error: null,
      formError: null,
      success: null,
      validationErrors: null,
      dateError: null
    };
  },
  computed: {
    utilizationPercentage() {
      if (!this.budget.amount || this.budget.amount <= 0) return 0;
      return Math.round((this.budget.spent_amount / this.budget.amount) * 100);
    },
    remainingAmount() {
      return this.budget.amount - this.budget.spent_amount;
    },
    utilizationColorClass() {
      if (this.utilizationPercentage > 90) return 'bg-red-600';
      if (this.utilizationPercentage > 75) return 'bg-yellow-600';
      return 'bg-green-600';
    }
  },
  created() {
    this.loadBudget();
    this.loadCategories();
  },
  methods: {
    async loadBudget() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/budgets/${this.$route.params.id}`);
        this.budget = response.data.data;
        
        // Format dates for input fields
        if (this.budget.start_date) {
          this.budget.start_date = this.formatDateForInput(new Date(this.budget.start_date));
        }
        
        if (this.budget.end_date) {
          this.budget.end_date = this.formatDateForInput(new Date(this.budget.end_date));
        }
      } catch (error) {
        this.error = 'Failed to load budget details. Please try again.';
        console.error('Error loading budget:', error);
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
    
    validateDates() {
      const startDate = new Date(this.budget.start_date);
      const endDate = new Date(this.budget.end_date);
      
      if (endDate < startDate) {
        this.dateError = 'End date must be after start date';
        return false;
      }
      
      this.dateError = null;
      return true;
    },
    
    formatDateForInput(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    formatNumber(value) {
      return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    async submitForm() {
      // Validate dates
      if (!this.validateDates()) {
        return;
      }
      
      this.submitting = true;
      this.formError = null;
      this.success = null;
      this.validationErrors = null;
      
      try {
        const response = await axios.put(`/api/budgets/${this.budget.id}`, this.budget);
        
        this.success = 'Budget updated successfully!';
        
        // Redirect after successful update
        setTimeout(() => {
          this.$router.push('/budgets');
        }, 1500);
      } catch (error) {
        if (error.response && error.response.data) {
          this.formError = error.response.data.message || 'Failed to update budget';
          
          if (error.response.data.errors) {
            this.validationErrors = error.response.data.errors;
          }
        } else {
          this.formError = 'An unexpected error occurred. Please try again.';
        }
        
        console.error('Error updating budget:', error);
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>
