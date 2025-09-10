<template>
  <div class="donation-settings">
    <!-- Settings Header -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden mb-6">
      <div class="px-4 py-5 sm:px-6 border-b border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Donation Settings</h3>
        <p class="mt-1 max-w-2xl text-sm text-neutral-500 dark:text-neutral-400">
          Configure categories, payment methods, and other donation settings
        </p>
      </div>
    </div>
    
    <!-- Settings Tabs -->
    <div class="mb-6 border-b border-neutral-200 dark:border-neutral-700">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.id" 
          @click="activeTab = tab.id" 
          :class="[
            activeTab === tab.id
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>
    
    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Categories Tab -->
      <div v-if="activeTab === 'categories'">
        <CategorySettings />
      </div>
      
      <!-- Payment Methods Tab -->
      <div v-else-if="activeTab === 'payment-methods'">
        <PaymentMethodSettings />
      </div>
      
      <!-- Receipt Settings Tab -->
      <div v-else-if="activeTab === 'receipts'">
        <ReceiptSettings />
      </div>
      
      <!-- Export Settings Tab -->
      <div v-else-if="activeTab === 'exports'">
        <ExportSettings />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CategorySettings from './settings/CategorySettings.vue';
import PaymentMethodSettings from './settings/PaymentMethodSettings.vue';
import ReceiptSettings from './settings/ReceiptSettings.vue';
import ExportSettings from './settings/ExportSettings.vue';

// State
const activeTab = ref('categories');

// Tabs configuration
const tabs = [
  { id: 'categories', name: 'Categories' },
  { id: 'payment-methods', name: 'Payment Methods' },
  { id: 'receipts', name: 'Receipts' },
  { id: 'exports', name: 'Exports' }
];
</script>
