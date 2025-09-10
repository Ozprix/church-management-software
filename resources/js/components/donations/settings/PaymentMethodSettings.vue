<template>
  <div class="payment-method-settings">
    <!-- Payment Methods Table -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <!-- Table Header -->
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Payment Methods</h3>
        <button 
          @click="showAddMethodModal = true"
          class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Method
        </button>
      </div>
      
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
          <thead class="bg-neutral-50 dark:bg-neutral-900">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Icon
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Usage
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
            <tr v-for="method in donationStore.paymentMethods" :key="method.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-center w-8 h-8 bg-neutral-100 dark:bg-neutral-700 rounded-full">
                  <svg v-if="method.icon === 'cash'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <svg v-else-if="method.icon === 'check'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="method.icon === 'credit-card'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <svg v-else-if="method.icon === 'bank'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                  </svg>
                  <svg v-else-if="method.icon === 'mobile'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <svg v-else-if="method.icon === 'globe'" class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else class="w-4 h-4 text-neutral-600 dark:text-neutral-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                {{ method.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ getMethodUsage(method.id) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button 
                  @click="editMethod(method)" 
                  class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
                >
                  Edit
                </button>
                <button 
                  @click="confirmDeleteMethod(method)" 
                  class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                  :disabled="isMethodInUse(method.id)"
                  :class="{ 'opacity-50 cursor-not-allowed': isMethodInUse(method.id) }"
                >
                  Delete
                </button>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-if="donationStore.paymentMethods.length === 0">
              <td colspan="4" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                <div class="flex flex-col items-center">
                  <svg class="h-10 w-10 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No payment methods</h3>
                  <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Get started by adding a new payment method.
                  </p>
                  <div class="mt-6">
                    <button
                      @click="showAddMethodModal = true"
                      class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                      Add Payment Method
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Add/Edit Method Modal -->
    <div v-if="showAddMethodModal || showEditMethodModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModals"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveMethod">
            <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    {{ showEditMethodModal ? 'Edit Payment Method' : 'Add New Payment Method' }}
                  </h3>
                  <div class="mt-4 space-y-4">
                    <!-- Name -->
                    <div>
                      <label for="method-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Name
                      </label>
                      <input 
                        type="text" 
                        id="method-name" 
                        v-model="methodForm.name" 
                        class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                        required
                      />
                    </div>
                    
                    <!-- Icon -->
                    <div>
                      <label for="method-icon" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Icon
                      </label>
                      <select 
                        id="method-icon" 
                        v-model="methodForm.icon" 
                        class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      >
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="credit-card">Credit Card</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile">Mobile Money</option>
                        <option value="globe">Online Payment</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button 
                type="submit" 
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                {{ showEditMethodModal ? 'Save Changes' : 'Add Method' }}
              </button>
              <button 
                type="button" 
                @click="closeModals" 
                class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
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
                  Delete Payment Method
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Are you sure you want to delete this payment method? This action cannot be undone.
                  </p>
                  <p v-if="isMethodInUse(selectedMethod?.id)" class="mt-2 text-sm text-red-500">
                    This payment method is currently in use and cannot be deleted.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deleteMethod" 
              :disabled="isMethodInUse(selectedMethod?.id)"
              :class="[
                isMethodInUse(selectedMethod?.id) ? 'bg-red-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700',
                'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm'
              ]"
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
import { ref, reactive } from 'vue';
import { useDonationStore } from '../../../stores/donations';
import { useToastService } from '../../../services/toastService';

// Store
const donationStore = useDonationStore();
const toast = useToastService();

// State
const showAddMethodModal = ref(false);
const showEditMethodModal = ref(false);
const showDeleteModal = ref(false);
const selectedMethod = ref(null);
const methodForm = reactive({
  id: null,
  name: '',
  icon: 'cash'
});

// Methods
const getMethodUsage = (methodId) => {
  const donationCount = donationStore.donations.filter(d => d.paymentMethodId === methodId).length;
  
  if (donationCount === 0) {
    return 'Not used';
  }
  
  return `${donationCount} donation${donationCount !== 1 ? 's' : ''}`;
};

const isMethodInUse = (methodId) => {
  if (!methodId) return false;
  
  return donationStore.donations.some(d => d.paymentMethodId === methodId);
};

const editMethod = (method) => {
  selectedMethod.value = method;
  methodForm.id = method.id;
  methodForm.name = method.name;
  methodForm.icon = method.icon;
  showEditMethodModal.value = true;
};

const confirmDeleteMethod = (method) => {
  if (isMethodInUse(method.id)) {
    toast.show({
      type: 'error',
      title: 'Cannot Delete',
      message: 'This payment method is in use and cannot be deleted.'
    });
    return;
  }
  
  selectedMethod.value = method;
  showDeleteModal.value = true;
};

const deleteMethod = () => {
  if (selectedMethod.value && !isMethodInUse(selectedMethod.value.id)) {
    const result = donationStore.deletePaymentMethod(selectedMethod.value.id);
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Method Deleted',
        message: 'The payment method has been deleted successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error deleting the payment method.'
      });
    }
    
    showDeleteModal.value = false;
    selectedMethod.value = null;
  }
};

const saveMethod = () => {
  if (showEditMethodModal.value) {
    // Update existing method
    const result = donationStore.updatePaymentMethod(methodForm.id, {
      name: methodForm.name,
      icon: methodForm.icon
    });
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Method Updated',
        message: 'The payment method has been updated successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error updating the payment method.'
      });
    }
  } else {
    // Add new method
    const result = donationStore.addPaymentMethod({
      name: methodForm.name,
      icon: methodForm.icon
    });
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Method Added',
        message: 'The new payment method has been added successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error adding the payment method.'
      });
    }
  }
  
  closeModals();
};

const closeModals = () => {
  showAddMethodModal.value = false;
  showEditMethodModal.value = false;
  selectedMethod.value = null;
  
  // Reset form
  methodForm.id = null;
  methodForm.name = '';
  methodForm.icon = 'cash';
};
</script>
