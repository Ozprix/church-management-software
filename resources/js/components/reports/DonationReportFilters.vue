<template>
  <div class="donation-report-filters bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 mb-6">
    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-4">Report Filters</h3>
    
    <form @submit.prevent="applyFilters">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Date Range -->
        <div>
          <label for="date-range" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Date Range
          </label>
          <select 
            id="date-range" 
            v-model="filters.dateRange" 
            class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
            @change="handleDateRangeChange"
          >
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="this-week">This Week</option>
            <option value="last-week">Last Week</option>
            <option value="this-month">This Month</option>
            <option value="last-month">Last Month</option>
            <option value="this-quarter">This Quarter</option>
            <option value="last-quarter">Last Quarter</option>
            <option value="this-year">This Year</option>
            <option value="last-year">Last Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        
        <!-- Custom Date Range (shown when dateRange is 'custom') -->
        <div v-if="filters.dateRange === 'custom'" class="col-span-1 md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="start-date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Start Date
            </label>
            <input 
              type="date" 
              id="start-date" 
              v-model="filters.startDate" 
              class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label for="end-date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              End Date
            </label>
            <input 
              type="date" 
              id="end-date" 
              v-model="filters.endDate" 
              class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
            />
          </div>
        </div>
        
        <!-- Categories -->
        <div>
          <label for="categories" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Categories
          </label>
          <select 
            id="categories" 
            v-model="filters.categoryIds" 
            multiple
            class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
          >
            <option value="all">All Categories</option>
            <option 
              v-for="category in donationStore.donationCategories" 
              :key="category.id" 
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Hold Ctrl/Cmd to select multiple</p>
        </div>
        
        <!-- Payment Methods -->
        <div>
          <label for="payment-methods" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Payment Methods
          </label>
          <select 
            id="payment-methods" 
            v-model="filters.paymentMethodIds" 
            multiple
            class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
          >
            <option value="all">All Payment Methods</option>
            <option 
              v-for="method in donationStore.paymentMethods" 
              :key="method.id" 
              :value="method.id"
            >
              {{ method.name }}
            </option>
          </select>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Hold Ctrl/Cmd to select multiple</p>
        </div>
        
        <!-- Campaigns -->
        <div>
          <label for="campaigns" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Campaigns
          </label>
          <select 
            id="campaigns" 
            v-model="filters.campaignIds" 
            multiple
            class="block w-full rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
          >
            <option value="all">All Campaigns</option>
            <option value="none">No Campaign</option>
            <option 
              v-for="campaign in donationStore.campaigns" 
              :key="campaign.id" 
              :value="campaign.id"
            >
              {{ campaign.name }}
            </option>
          </select>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Hold Ctrl/Cmd to select multiple</p>
        </div>
        
        <!-- Amount Range -->
        <div class="col-span-1 md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="min-amount" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Minimum Amount
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-neutral-500 dark:text-neutral-400 sm:text-sm">$</span>
              </div>
              <input 
                type="number" 
                id="min-amount" 
                v-model="filters.minAmount" 
                min="0" 
                step="0.01"
                class="block w-full pl-7 rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
              />
            </div>
          </div>
          <div>
            <label for="max-amount" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Maximum Amount
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-neutral-500 dark:text-neutral-400 sm:text-sm">$</span>
              </div>
              <input 
                type="number" 
                id="max-amount" 
                v-model="filters.maxAmount" 
                min="0" 
                step="0.01"
                class="block w-full pl-7 rounded-md border-neutral-300 dark:border-neutral-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
              />
            </div>
          </div>
        </div>
        
        <!-- Additional Filters -->
        <div class="col-span-1 md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="flex items-center">
            <input 
              id="recurring-only" 
              v-model="filters.recurringOnly" 
              type="checkbox" 
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
            />
            <label for="recurring-only" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
              Recurring Donations Only
            </label>
          </div>
          <div class="flex items-center">
            <input 
              id="tax-deductible-only" 
              v-model="filters.taxDeductibleOnly" 
              type="checkbox" 
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
            />
            <label for="tax-deductible-only" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
              Tax Deductible Only
            </label>
          </div>
          <div class="flex items-center">
            <input 
              id="receipt-sent" 
              v-model="filters.receiptSent" 
              type="checkbox" 
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
            />
            <label for="receipt-sent" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
              Receipt Sent
            </label>
          </div>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="mt-6 flex justify-end space-x-3">
        <button 
          type="button" 
          @click="resetFilters" 
          class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Reset
        </button>
        <button 
          type="submit" 
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Apply Filters
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useDonationStore } from '../../stores/donations';
import { useToastService } from '../../services/toastService';

// Props
const props = defineProps({
  initialFilters: {
    type: Object,
    default: () => ({})
  }
});

// Emits
const emit = defineEmits(['update:filters', 'apply-filters']);

// Store
const donationStore = useDonationStore();
const toast = useToastService();

// Default date ranges
const getDateRange = (range) => {
  const today = new Date();
  const startDate = new Date();
  const endDate = new Date();
  
  switch (range) {
    case 'today':
      return { startDate: today.toISOString().split('T')[0], endDate: today.toISOString().split('T')[0] };
    case 'yesterday':
      startDate.setDate(today.getDate() - 1);
      endDate.setDate(today.getDate() - 1);
      return { startDate: startDate.toISOString().split('T')[0], endDate: endDate.toISOString().split('T')[0] };
    case 'this-week':
      startDate.setDate(today.getDate() - today.getDay());
      return { startDate: startDate.toISOString().split('T')[0], endDate: today.toISOString().split('T')[0] };
    case 'last-week':
      startDate.setDate(today.getDate() - today.getDay() - 7);
      endDate.setDate(today.getDate() - today.getDay() - 1);
      return { startDate: startDate.toISOString().split('T')[0], endDate: endDate.toISOString().split('T')[0] };
    case 'this-month':
      startDate.setDate(1);
      return { startDate: startDate.toISOString().split('T')[0], endDate: today.toISOString().split('T')[0] };
    case 'last-month':
      startDate.setMonth(today.getMonth() - 1);
      startDate.setDate(1);
      endDate.setDate(0);
      return { startDate: startDate.toISOString().split('T')[0], endDate: endDate.toISOString().split('T')[0] };
    case 'this-quarter':
      startDate.setMonth(Math.floor(today.getMonth() / 3) * 3);
      startDate.setDate(1);
      return { startDate: startDate.toISOString().split('T')[0], endDate: today.toISOString().split('T')[0] };
    case 'last-quarter':
      startDate.setMonth(Math.floor(today.getMonth() / 3) * 3 - 3);
      startDate.setDate(1);
      endDate.setMonth(Math.floor(today.getMonth() / 3) * 3);
      endDate.setDate(0);
      return { startDate: startDate.toISOString().split('T')[0], endDate: endDate.toISOString().split('T')[0] };
    case 'this-year':
      startDate.setMonth(0);
      startDate.setDate(1);
      return { startDate: startDate.toISOString().split('T')[0], endDate: today.toISOString().split('T')[0] };
    case 'last-year':
      startDate.setFullYear(today.getFullYear() - 1);
      startDate.setMonth(0);
      startDate.setDate(1);
      endDate.setFullYear(today.getFullYear() - 1);
      endDate.setMonth(11);
      endDate.setDate(31);
      return { startDate: startDate.toISOString().split('T')[0], endDate: endDate.toISOString().split('T')[0] };
    case 'all':
    default:
      return { startDate: null, endDate: null };
  }
};

// Initialize filters with default values
const filters = reactive({
  dateRange: 'this-month',
  startDate: getDateRange('this-month').startDate,
  endDate: getDateRange('this-month').endDate,
  categoryIds: ['all'],
  paymentMethodIds: ['all'],
  campaignIds: ['all'],
  minAmount: null,
  maxAmount: null,
  recurringOnly: false,
  taxDeductibleOnly: false,
  receiptSent: null,
  ...props.initialFilters
});

// Handle date range change
const handleDateRangeChange = () => {
  if (filters.dateRange !== 'custom') {
    const { startDate, endDate } = getDateRange(filters.dateRange);
    filters.startDate = startDate;
    filters.endDate = endDate;
  }
};

// Apply filters
const applyFilters = () => {
  // Validate date range if custom
  if (filters.dateRange === 'custom' && filters.startDate && filters.endDate) {
    if (new Date(filters.startDate) > new Date(filters.endDate)) {
      toast.error('Start date cannot be after end date');
      return;
    }
  }
  
  // Validate amount range
  if (filters.minAmount && filters.maxAmount && parseFloat(filters.minAmount) > parseFloat(filters.maxAmount)) {
    toast.error('Minimum amount cannot be greater than maximum amount');
    return;
  }
  
  // Emit the filtered data
  emit('update:filters', { ...filters });
  emit('apply-filters', { ...filters });
};

// Reset filters to default
const resetFilters = () => {
  Object.assign(filters, {
    dateRange: 'this-month',
    startDate: getDateRange('this-month').startDate,
    endDate: getDateRange('this-month').endDate,
    categoryIds: ['all'],
    paymentMethodIds: ['all'],
    campaignIds: ['all'],
    minAmount: null,
    maxAmount: null,
    recurringOnly: false,
    taxDeductibleOnly: false,
    receiptSent: null
  });
  
  // Emit the reset event with default filters
  emit('update:filters', { ...filters });
  emit('apply-filters', { ...filters });
};

// Watch for initialFilters changes
watch(() => props.initialFilters, (newFilters) => {
  if (newFilters) {
    Object.assign(filters, newFilters);
  }
}, { deep: true });
</script>
