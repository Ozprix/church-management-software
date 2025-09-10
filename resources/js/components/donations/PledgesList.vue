<template>
  <div class="pledges-list">
    <!-- Basic Table Structure -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <!-- Table Header -->
      <div class="px-4 py-5 sm:px-6 border-b border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Pledges</h3>
        <p class="mt-1 max-w-2xl text-sm text-neutral-500 dark:text-neutral-400">
          Manage member pledges and track fulfillment progress
        </p>
      </div>
      
      <!-- Actions Bar -->
      <div class="px-4 py-3 bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex justify-between items-center">
          <button 
            @click="showAddPledgeModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Pledge
          </button>
          
          <span class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ donationStore.pledges.length }} pledges
          </span>
        </div>
      </div>
      
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
          <thead class="bg-neutral-50 dark:bg-neutral-900">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Member
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Amount
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Fulfilled
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Progress
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Category
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Frequency
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Period
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
            <tr v-for="pledge in donationStore.pledges" :key="pledge.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ pledge.memberName }}</div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">ID: {{ pledge.memberId }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                {{ formatCurrency(pledge.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ formatCurrency(pledge.fulfilled) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5">
                  <div 
                    class="h-2.5 rounded-full" 
                    :class="getProgressColorClass(pledge)"
                    :style="{ width: `${calculateProgress(pledge)}%` }"
                  ></div>
                </div>
                <div class="text-xs mt-1 text-neutral-500 dark:text-neutral-400">
                  {{ calculateProgress(pledge) }}%
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :style="getCategoryStyle(pledge.categoryId)"
                >
                  {{ getCategoryName(pledge.categoryId) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ formatFrequency(pledge.frequency) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ formatDateRange(pledge.startDate, pledge.endDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button 
                  @click="viewPledge(pledge)" 
                  class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
                >
                  View
                </button>
                <button 
                  @click="editPledge(pledge)" 
                  class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
                >
                  Edit
                </button>
                <button 
                  @click="confirmDeletePledge(pledge)" 
                  class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                >
                  Delete
                </button>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-if="donationStore.pledges.length === 0">
              <td colspan="8" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                <div class="flex flex-col items-center">
                  <svg class="h-10 w-10 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No pledges</h3>
                  <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Get started by adding a new pledge.
                  </p>
                  <div class="mt-6">
                    <button
                      @click="showAddPledgeModal = true"
                      class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                      Add Pledge
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination Placeholder -->
      <div class="px-4 py-3 bg-neutral-50 dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-700 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-neutral-700 dark:text-neutral-300">
                Showing <span class="font-medium">1</span> to <span class="font-medium">{{ Math.min(donationStore.pledges.length, 10) }}</span> of <span class="font-medium">{{ donationStore.pledges.length }}</span> results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-700">
                  <span class="sr-only">Previous</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700">1</a>
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-700">
                  <span class="sr-only">Next</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </a>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Placeholder for Add/Edit Pledge Modal -->
    <div v-if="showAddPledgeModal || showEditPledgeModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModals"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  {{ showEditPledgeModal ? 'Edit Pledge' : 'Add New Pledge' }}
                </h3>
                <div class="mt-4">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Pledge form will be implemented in the next step.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              {{ showEditPledgeModal ? 'Save Changes' : 'Add Pledge' }}
            </button>
            <button 
              type="button" 
              @click="closeModals" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  Delete Pledge
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Are you sure you want to delete this pledge? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deletePledge" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button 
              type="button" 
              @click="showDeleteModal = false" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDonationStore } from '../../stores/donations';

// Store
const donationStore = useDonationStore();

// State
const showAddPledgeModal = ref(false);
const showEditPledgeModal = ref(false);
const showDeleteModal = ref(false);
const selectedPledge = ref(null);

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
};

const calculateProgress = (pledge) => {
  if (pledge.amount <= 0) return 0;
  const percentage = (pledge.fulfilled / pledge.amount) * 100;
  return Math.min(Math.round(percentage), 100);
};

const getProgressColorClass = (pledge) => {
  const progress = calculateProgress(pledge);
  if (progress >= 100) return 'bg-green-600 dark:bg-green-500';
  if (progress >= 75) return 'bg-green-500 dark:bg-green-400';
  if (progress >= 50) return 'bg-yellow-500 dark:bg-yellow-400';
  if (progress >= 25) return 'bg-yellow-400 dark:bg-yellow-300';
  return 'bg-red-500 dark:bg-red-400';
};

const getCategoryName = (categoryId) => {
  const category = donationStore.getCategoryById(categoryId);
  return category ? category.name : 'Unknown';
};

const getCategoryStyle = (categoryId) => {
  const category = donationStore.getCategoryById(categoryId);
  if (!category) return {};
  
  return {
    backgroundColor: `${category.color}20`, // 20% opacity
    color: category.color
  };
};

const formatFrequency = (frequency) => {
  if (!frequency) return 'One-time';
  
  const frequencies = {
    'weekly': 'Weekly',
    'biweekly': 'Bi-weekly',
    'monthly': 'Monthly',
    'quarterly': 'Quarterly',
    'annually': 'Annually'
  };
  
  return frequencies[frequency] || frequency;
};

const formatDateRange = (startDate, endDate) => {
  if (!startDate || !endDate) return 'N/A';
  
  const start = new Date(startDate);
  const end = new Date(endDate);
  
  const startStr = start.toLocaleDateString(undefined, { 
    month: 'short', 
    year: 'numeric' 
  });
  
  const endStr = end.toLocaleDateString(undefined, { 
    month: 'short', 
    year: 'numeric' 
  });
  
  return `${startStr} - ${endStr}`;
};

const viewPledge = (pledge) => {
  // This will be implemented in a future step
  alert(`View pledge: ${pledge.id}`);
};

const editPledge = (pledge) => {
  selectedPledge.value = pledge;
  showEditPledgeModal.value = true;
};

const confirmDeletePledge = (pledge) => {
  selectedPledge.value = pledge;
  showDeleteModal.value = true;
};

const deletePledge = () => {
  if (selectedPledge.value) {
    donationStore.deletePledge(selectedPledge.value.id);
    showDeleteModal.value = false;
    selectedPledge.value = null;
  }
};

const closeModals = () => {
  showAddPledgeModal.value = false;
  showEditPledgeModal.value = false;
  selectedPledge.value = null;
};
</script>
