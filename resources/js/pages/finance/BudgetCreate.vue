<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Create New Budget</h1>
      <router-link
        to="/budgets"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
      >
        Back to Budgets
      </router-link>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <!-- Error Alert -->
      <div
        v-if="error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <strong class="font-bold">Error!</strong>
        <p>{{ error }}</p>
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
            :disabled="loading"
          >
            <span v-if="loading">
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
            <span v-else>Create Budget</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BudgetCreate',
  data() {
    return {
      budget: {
        name: '',
        description: '',
        amount: '',
        start_date: '',
        end_date: '',
        category: '',
        status: 'active'
      },
      categories: [],
      loading: false,
      error: null,
      success: null,
      validationErrors: null,
      dateError: null
    };
  },
  created() {
    this.loadCategories();
    
    // Set default dates to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    this.budget.start_date = this.formatDate(firstDay);
    this.budget.end_date = this.formatDate(lastDay);
  },
  methods: {
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
    
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    async submitForm() {
      // Validate dates
      if (!this.validateDates()) {
        return;
      }
      
      this.loading = true;
      this.error = null;
      this.success = null;
      this.validationErrors = null;
      
      try {
        const response = await axios.post('/api/budgets', this.budget);
        
        this.success = 'Budget created successfully!';
        
        // Reset form after successful submission
        setTimeout(() => {
          this.$router.push('/budgets');
        }, 1500);
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'Failed to create budget';
          
          if (error.response.data.errors) {
            this.validationErrors = error.response.data.errors;
          }
        } else {
          this.error = 'An unexpected error occurred. Please try again.';
        }
        
        console.error('Error creating budget:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
