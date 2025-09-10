<template>
  <div class="category-settings">
    <!-- Categories Table -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <!-- Table Header -->
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Donation Categories</h3>
        <button 
          @click="showAddCategoryModal = true"
          class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Category
        </button>
      </div>
      
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
          <thead class="bg-neutral-50 dark:bg-neutral-900">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Color
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Description
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
            <tr v-for="category in donationStore.donationCategories" :key="category.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-6 h-6 rounded" :style="{ backgroundColor: category.color }"></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                {{ category.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ category.description }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                {{ getCategoryUsage(category.id) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button 
                  @click="editCategory(category)" 
                  class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
                >
                  Edit
                </button>
                <button 
                  @click="confirmDeleteCategory(category)" 
                  class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                  :disabled="isCategoryInUse(category.id)"
                  :class="{ 'opacity-50 cursor-not-allowed': isCategoryInUse(category.id) }"
                >
                  Delete
                </button>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-if="donationStore.donationCategories.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                <div class="flex flex-col items-center">
                  <svg class="h-10 w-10 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No categories</h3>
                  <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Get started by adding a new donation category.
                  </p>
                  <div class="mt-6">
                    <button
                      @click="showAddCategoryModal = true"
                      class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                      Add Category
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Add/Edit Category Modal -->
    <div v-if="showAddCategoryModal || showEditCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModals"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveCategory">
            <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    {{ showEditCategoryModal ? 'Edit Category' : 'Add New Category' }}
                  </h3>
                  <div class="mt-4 space-y-4">
                    <!-- Name -->
                    <div>
                      <label for="category-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Name
                      </label>
                      <input 
                        type="text" 
                        id="category-name" 
                        v-model="categoryForm.name" 
                        class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                        required
                      />
                    </div>
                    
                    <!-- Description -->
                    <div>
                      <label for="category-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Description
                      </label>
                      <textarea 
                        id="category-description" 
                        v-model="categoryForm.description" 
                        rows="3" 
                        class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      ></textarea>
                    </div>
                    
                    <!-- Color -->
                    <div>
                      <label for="category-color" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Color
                      </label>
                      <div class="mt-1 flex items-center space-x-2">
                        <div class="w-8 h-8 rounded" :style="{ backgroundColor: categoryForm.color }"></div>
                        <input 
                          type="color" 
                          id="category-color" 
                          v-model="categoryForm.color" 
                          class="h-8 w-8 border-0 p-0 focus:outline-none focus:ring-0"
                        />
                      </div>
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
                {{ showEditCategoryModal ? 'Save Changes' : 'Add Category' }}
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
                  Delete Category
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Are you sure you want to delete this category? This action cannot be undone.
                  </p>
                  <p v-if="isCategoryInUse(selectedCategory?.id)" class="mt-2 text-sm text-red-500">
                    This category is currently in use and cannot be deleted.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deleteCategory" 
              :disabled="isCategoryInUse(selectedCategory?.id)"
              :class="[
                isCategoryInUse(selectedCategory?.id) ? 'bg-red-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700',
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
const showAddCategoryModal = ref(false);
const showEditCategoryModal = ref(false);
const showDeleteModal = ref(false);
const selectedCategory = ref(null);
const categoryForm = reactive({
  id: null,
  name: '',
  description: '',
  color: '#4F46E5'
});

// Methods
const getCategoryUsage = (categoryId) => {
  const donationCount = donationStore.donations.filter(d => d.categoryId === categoryId).length;
  const campaignCount = donationStore.campaigns.filter(c => c.categoryId === categoryId).length;
  
  if (donationCount === 0 && campaignCount === 0) {
    return 'Not used';
  }
  
  const parts = [];
  if (donationCount > 0) {
    parts.push(`${donationCount} donation${donationCount !== 1 ? 's' : ''}`);
  }
  if (campaignCount > 0) {
    parts.push(`${campaignCount} campaign${campaignCount !== 1 ? 's' : ''}`);
  }
  
  return parts.join(', ');
};

const isCategoryInUse = (categoryId) => {
  if (!categoryId) return false;
  
  return donationStore.donations.some(d => d.categoryId === categoryId) ||
         donationStore.campaigns.some(c => c.categoryId === categoryId);
};

const editCategory = (category) => {
  selectedCategory.value = category;
  categoryForm.id = category.id;
  categoryForm.name = category.name;
  categoryForm.description = category.description;
  categoryForm.color = category.color;
  showEditCategoryModal.value = true;
};

const confirmDeleteCategory = (category) => {
  if (isCategoryInUse(category.id)) {
    toast.show({
      type: 'error',
      title: 'Cannot Delete',
      message: 'This category is in use and cannot be deleted.'
    });
    return;
  }
  
  selectedCategory.value = category;
  showDeleteModal.value = true;
};

const deleteCategory = () => {
  if (selectedCategory.value && !isCategoryInUse(selectedCategory.value.id)) {
    const result = donationStore.deleteDonationCategory(selectedCategory.value.id);
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Category Deleted',
        message: 'The donation category has been deleted successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error deleting the category.'
      });
    }
    
    showDeleteModal.value = false;
    selectedCategory.value = null;
  }
};

const saveCategory = () => {
  if (showEditCategoryModal.value) {
    // Update existing category
    const result = donationStore.updateDonationCategory(categoryForm.id, {
      name: categoryForm.name,
      description: categoryForm.description,
      color: categoryForm.color
    });
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Category Updated',
        message: 'The donation category has been updated successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error updating the category.'
      });
    }
  } else {
    // Add new category
    const result = donationStore.addDonationCategory({
      name: categoryForm.name,
      description: categoryForm.description,
      color: categoryForm.color
    });
    
    if (result) {
      toast.show({
        type: 'success',
        title: 'Category Added',
        message: 'The new donation category has been added successfully.'
      });
    } else {
      toast.show({
        type: 'error',
        title: 'Error',
        message: 'There was an error adding the category.'
      });
    }
  }
  
  closeModals();
};

const closeModals = () => {
  showAddCategoryModal.value = false;
  showEditCategoryModal.value = false;
  selectedCategory.value = null;
  
  // Reset form
  categoryForm.id = null;
  categoryForm.name = '';
  categoryForm.description = '';
  categoryForm.color = '#4F46E5';
};
</script>
