<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link 
        to="/expenses" 
        class="text-blue-600 hover:text-blue-800 mr-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Expenses
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Record New Expense</h1>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="saveExpense">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Basic Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Basic Information</h2>
          </div>

          <!-- Title -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
              Title *
            </label>
            <input 
              id="title" 
              v-model="expense.title" 
              type="text" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.title" class="text-red-500 text-xs mt-1">
              {{ validationErrors.title[0] }}
            </p>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
              Amount *
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600">$</span>
              <input 
                id="amount" 
                v-model="expense.amount" 
                type="number" 
                min="0.01" 
                step="0.01" 
                required
                class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              >
            </div>
            <p v-if="validationErrors.amount" class="text-red-500 text-xs mt-1">
              {{ validationErrors.amount[0] }}
            </p>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
              Category *
            </label>
            <select 
              id="category" 
              v-model="expense.category" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">Select Category</option>
              <option value="utilities">Utilities</option>
              <option value="rent">Rent</option>
              <option value="maintenance">Maintenance</option>
              <option value="salaries">Salaries</option>
              <option value="office_supplies">Office Supplies</option>
              <option value="events">Events</option>
              <option value="missions">Missions</option>
              <option value="education">Education</option>
              <option value="outreach">Outreach</option>
              <option value="worship">Worship</option>
              <option value="other">Other</option>
            </select>
            <p v-if="validationErrors.category" class="text-red-500 text-xs mt-1">
              {{ validationErrors.category[0] }}
            </p>
          </div>

          <!-- Payment Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-4">Payment Information</h2>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="payment_method">
              Payment Method *
            </label>
            <select 
              id="payment_method" 
              v-model="expense.payment_method" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">Select Payment Method</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="credit_card">Credit Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="online">Online</option>
              <option value="other">Other</option>
            </select>
            <p v-if="validationErrors.payment_method" class="text-red-500 text-xs mt-1">
              {{ validationErrors.payment_method[0] }}
            </p>
          </div>

          <!-- Expense Date -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="expense_date">
              Expense Date *
            </label>
            <input 
              id="expense_date" 
              v-model="expense.expense_date" 
              type="date" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.expense_date" class="text-red-500 text-xs mt-1">
              {{ validationErrors.expense_date[0] }}
            </p>
          </div>

          <!-- Vendor -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="vendor">
              Vendor
            </label>
            <input 
              id="vendor" 
              v-model="expense.vendor" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.vendor" class="text-red-500 text-xs mt-1">
              {{ validationErrors.vendor[0] }}
            </p>
          </div>

          <!-- Budget -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="budget_id">
              Budget
            </label>
            <select 
              id="budget_id" 
              v-model="expense.budget_id" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">No Budget</option>
              <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                {{ budget.name }} ({{ formatCurrency(budget.amount) }})
              </option>
            </select>
            <p v-if="validationErrors.budget_id" class="text-red-500 text-xs mt-1">
              {{ validationErrors.budget_id[0] }}
            </p>
          </div>

          <!-- Approved By -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="approved_by">
              Approved By
            </label>
            <input 
              id="approved_by" 
              v-model="expense.approved_by" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.approved_by" class="text-red-500 text-xs mt-1">
              {{ validationErrors.approved_by[0] }}
            </p>
          </div>

          <!-- Receipt -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="receipt">
              Receipt
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                    <span>Upload a file</span>
                    <input 
                      id="receipt" 
                      name="receipt" 
                      type="file" 
                      class="sr-only"
                      @change="handleFileUpload"
                      accept="image/jpeg,image/png,image/jpg,application/pdf"
                    >
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">
                  PNG, JPG, JPEG, PDF up to 2MB
                </p>
              </div>
            </div>
            <div v-if="receiptFile" class="mt-2 text-sm text-gray-600">
              Selected file: {{ receiptFile.name }}
            </div>
            <p v-if="validationErrors.receipt" class="text-red-500 text-xs mt-1">
              {{ validationErrors.receipt[0] }}
            </p>
          </div>

          <!-- Recurring Expense -->
          <div class="md:col-span-2">
            <div class="flex items-center">
              <input 
                id="recurring" 
                v-model="expense.recurring" 
                type="checkbox" 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="recurring" class="ml-2 block text-sm text-gray-700">
                This is a recurring expense
              </label>
            </div>
          </div>

          <!-- Recurring Frequency -->
          <div v-if="expense.recurring">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="recurring_frequency">
              Recurring Frequency
            </label>
            <select 
              id="recurring_frequency" 
              v-model="expense.recurring_frequency" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="annually">Annually</option>
            </select>
            <p v-if="validationErrors.recurring_frequency" class="text-red-500 text-xs mt-1">
              {{ validationErrors.recurring_frequency[0] }}
            </p>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
              Description
            </label>
            <textarea 
              id="description" 
              v-model="expense.description" 
              rows="3" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            ></textarea>
            <p v-if="validationErrors.description" class="text-red-500 text-xs mt-1">
              {{ validationErrors.description[0] }}
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end">
          <button 
            type="button" 
            @click="$router.push('/expenses')" 
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            :disabled="saving"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Record Expense</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ExpenseCreate',
  data() {
    return {
      expense: {
        title: '',
        amount: '',
        category: '',
        payment_method: '',
        expense_date: new Date().toISOString().slice(0, 10), // Today's date
        vendor: '',
        budget_id: '',
        approved_by: '',
        description: '',
        recurring: false,
        recurring_frequency: 'monthly'
      },
      receiptFile: null,
      budgets: [],
      saving: false,
      error: null,
      validationErrors: {}
    };
  },
  created() {
    this.fetchBudgets();
  },
  methods: {
    async fetchBudgets() {
      try {
        const response = await axios.get('/budgets');
        
        if (response.data.status === 'success') {
          this.budgets = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching budgets:', error);
      }
    },
    
    handleFileUpload(event) {
      this.receiptFile = event.target.files[0];
    },
    
    formatCurrency(value) {
      if (!value) return '$0.00';
      return `$${parseFloat(value).toFixed(2)}`;
    },
    
    async saveExpense() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        const formData = new FormData();
        
        // Add all expense data to the form
        Object.keys(this.expense).forEach(key => {
          formData.append(key, this.expense[key]);
        });
        
        // Add receipt file if selected
        if (this.receiptFile) {
          formData.append('receipt', this.receiptFile);
        }
        
        const response = await axios.post('/expenses', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data.status === 'success') {
          // Redirect to the expense list with a success message
          this.$router.push({
            path: '/expenses',
            query: { 
              message: 'Expense recorded successfully',
              type: 'success'
            }
          });
        }
      } catch (error) {
        if (error.response?.status === 422) {
          // Validation errors
          this.validationErrors = error.response.data.errors || {};
          this.error = 'Please correct the errors in the form.';
        } else {
          this.error = error.response?.data?.message || 'An error occurred while saving the expense.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
