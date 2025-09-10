<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Donation Categories</h2>
      <button 
        @click="showAddModal = true" 
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Category
      </button>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
      <div class="relative flex-grow">
        <input 
          type="text" 
          v-model="search" 
          placeholder="Search categories..." 
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <span class="absolute right-3 top-2.5 text-gray-400">
          <i class="fas fa-search"></i>
        </span>
      </div>
      <div class="flex gap-2">
        <select 
          v-model="activeFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Status</option>
          <option value="true">Active</option>
          <option value="false">Inactive</option>
        </select>
        <select 
          v-model="taxDeductibleFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Tax Status</option>
          <option value="true">Tax Deductible</option>
          <option value="false">Non-Deductible</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline"> {{ error }}</span>
    </div>

    <!-- Categories Table -->
    <div v-else-if="filteredCategories.length > 0" class="overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead class="bg-gray-100">
          <tr>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Name</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Code</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Status</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Tax Deductible</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Default</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50">
            <td class="py-3 px-4 text-sm text-gray-900">{{ category.name }}</td>
            <td class="py-3 px-4 text-sm text-gray-500">{{ category.code }}</td>
            <td class="py-3 px-4 text-sm">
              <span 
                :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                class="px-2 py-1 rounded-full text-xs font-medium"
              >
                {{ category.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="py-3 px-4 text-sm">
              <span 
                :class="category.is_tax_deductible ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'" 
                class="px-2 py-1 rounded-full text-xs font-medium"
              >
                {{ category.is_tax_deductible ? 'Deductible' : 'Non-Deductible' }}
              </span>
            </td>
            <td class="py-3 px-4 text-sm">
              <span v-if="category.is_default" class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">
                Default
              </span>
              <button 
                v-else 
                @click="setAsDefault(category.id)" 
                class="text-indigo-600 hover:text-indigo-900 text-xs"
              >
                Set as Default
              </button>
            </td>
            <td class="py-3 px-4 text-sm">
              <div class="flex space-x-2">
                <button 
                  @click="editCategory(category)" 
                  class="text-blue-600 hover:text-blue-900"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  @click="confirmDelete(category)" 
                  class="text-red-600 hover:text-red-900"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <div class="text-gray-500 mb-4">
        <i class="fas fa-folder-open text-4xl"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900">No categories found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by creating a new donation category.
      </p>
      <button 
        @click="showAddModal = true" 
        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Category
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showEditModal ? 'Edit Category' : 'Add New Category' }}
        </h3>
        <form @submit.prevent="showEditModal ? updateCategory() : addCategory()">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input 
              type="text" 
              v-model="form.name" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
            <input 
              type="text" 
              v-model="form.code" 
              :placeholder="form.name ? form.name.toLowerCase().replace(/\s+/g, '-') : ''"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
            <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate from name</p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            ></textarea>
          </div>
          <div class="mb-4 flex items-center">
            <input 
              type="checkbox" 
              id="is_active" 
              v-model="form.is_active" 
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
          </div>
          <div class="mb-4 flex items-center">
            <input 
              type="checkbox" 
              id="is_tax_deductible" 
              v-model="form.is_tax_deductible" 
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="is_tax_deductible" class="ml-2 block text-sm text-gray-900">Tax Deductible</label>
          </div>
          <div class="mb-6 flex items-center">
            <input 
              type="checkbox" 
              id="is_default" 
              v-model="form.is_default" 
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="is_default" class="ml-2 block text-sm text-gray-900">Set as Default Category</label>
          </div>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :disabled="loading"
            >
              {{ showEditModal ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Delete</h3>
        <p class="text-sm text-gray-500 mb-4">
          Are you sure you want to delete the category "{{ categoryToDelete?.name }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="showDeleteModal = false" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Cancel
          </button>
          <button 
            @click="deleteCategory" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            :disabled="loading"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'DonationCategories',
  setup() {
    const toast = useToast();
    const categories = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const search = ref('');
    const activeFilter = ref('all');
    const taxDeductibleFilter = ref('all');
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);
    const categoryToDelete = ref(null);
    const form = ref({
      name: '',
      code: '',
      description: '',
      is_active: true,
      is_tax_deductible: true,
      is_default: false
    });

    // Fetch all categories
    const fetchCategories = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get('/api/donation-categories');
        categories.value = response.data.data;
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load categories';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Filtered categories based on search and filters
    const filteredCategories = computed(() => {
      let result = categories.value;
      
      // Apply search filter
      if (search.value) {
        const searchLower = search.value.toLowerCase();
        result = result.filter(category => 
          category.name.toLowerCase().includes(searchLower) || 
          category.code.toLowerCase().includes(searchLower) ||
          (category.description && category.description.toLowerCase().includes(searchLower))
        );
      }
      
      // Apply active filter
      if (activeFilter.value !== 'all') {
        const isActive = activeFilter.value === 'true';
        result = result.filter(category => category.is_active === isActive);
      }
      
      // Apply tax deductible filter
      if (taxDeductibleFilter.value !== 'all') {
        const isTaxDeductible = taxDeductibleFilter.value === 'true';
        result = result.filter(category => category.is_tax_deductible === isTaxDeductible);
      }
      
      return result;
    });

    // Add a new category
    const addCategory = async () => {
      loading.value = true;
      try {
        // If code is empty, generate from name
        if (!form.value.code && form.value.name) {
          form.value.code = form.value.name.toLowerCase().replace(/\s+/g, '-');
        }
        
        const response = await axios.post('/api/donation-categories', form.value);
        categories.value.push(response.data.data);
        toast.success('Category added successfully');
        closeModal();
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to add category';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Edit a category
    const editCategory = (category) => {
      form.value = { ...category };
      showEditModal.value = true;
    };

    // Update a category
    const updateCategory = async () => {
      loading.value = true;
      try {
        const response = await axios.put(`/api/donation-categories/${form.value.id}`, form.value);
        const index = categories.value.findIndex(c => c.id === form.value.id);
        if (index !== -1) {
          categories.value[index] = response.data.data;
        }
        toast.success('Category updated successfully');
        closeModal();
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update category';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Confirm delete
    const confirmDelete = (category) => {
      categoryToDelete.value = category;
      showDeleteModal.value = true;
    };

    // Delete a category
    const deleteCategory = async () => {
      if (!categoryToDelete.value) return;
      
      loading.value = true;
      try {
        await axios.delete(`/api/donation-categories/${categoryToDelete.value.id}`);
        categories.value = categories.value.filter(c => c.id !== categoryToDelete.value.id);
        toast.success('Category deleted successfully');
        showDeleteModal.value = false;
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete category';
        toast.error(error.value);
      } finally {
        loading.value = false;
        categoryToDelete.value = null;
      }
    };

    // Set a category as default
    const setAsDefault = async (id) => {
      loading.value = true;
      try {
        const response = await axios.post(`/api/donation-categories/${id}/set-default`);
        // Update all categories to reflect the new default
        categories.value = categories.value.map(category => ({
          ...category,
          is_default: category.id === id
        }));
        toast.success('Default category set successfully');
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to set default category';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Close modal and reset form
    const closeModal = () => {
      showAddModal.value = false;
      showEditModal.value = false;
      form.value = {
        name: '',
        code: '',
        description: '',
        is_active: true,
        is_tax_deductible: true,
        is_default: false
      };
    };

    // Load categories on component mount
    onMounted(fetchCategories);

    return {
      categories,
      loading,
      error,
      search,
      activeFilter,
      taxDeductibleFilter,
      showAddModal,
      showEditModal,
      showDeleteModal,
      form,
      categoryToDelete,
      filteredCategories,
      fetchCategories,
      addCategory,
      editCategory,
      updateCategory,
      confirmDelete,
      deleteCategory,
      setAsDefault,
      closeModal
    };
  }
};
</script>
