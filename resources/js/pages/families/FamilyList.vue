<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Family Management</h1>
      <router-link 
        to="/families/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        Add New Family
      </router-link>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="w-full md:w-1/2">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="search">
            Search
          </label>
          <input 
            id="search" 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search by family name" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @input="debounceSearch"
          >
        </div>
        <div class="w-full md:w-1/3">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="sort">
            Sort By
          </label>
          <select 
            id="sort" 
            v-model="sortBy" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            @change="fetchFamilies"
          >
            <option value="name">Family Name</option>
            <option value="created_at">Date Added</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Families Table -->
    <div v-else-if="families.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Family Name
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Head of Family
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Address
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Members
            </th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="family in families" :key="family.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                {{ family.name }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="family.head_member" class="text-sm text-gray-900">
                {{ family.head_member.first_name }} {{ family.head_member.last_name }}
              </div>
              <div v-else class="text-sm text-gray-500">
                Not specified
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ formatAddress(family) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ family.members ? family.members.length : 0 }} members
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link 
                :to="`/families/${family.id}`" 
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                View
              </router-link>
              <router-link 
                :to="`/families/${family.id}/edit`" 
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </router-link>
              <button 
                @click="confirmDelete(family)" 
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
          >
            Previous
          </button>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === lastPage"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage === lastPage }"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(currentPage * perPage, totalFamilies) }}</span>
              of
              <span class="font-medium">{{ totalFamilies }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button 
                @click="changePage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
              >
                <span class="sr-only">Previous</span>
                &laquo;
              </button>
              <button 
                v-for="page in paginationPages" 
                :key="page"
                @click="changePage(page)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                :class="page === currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'text-gray-500 hover:bg-gray-50'"
              >
                {{ page }}
              </button>
              <button 
                @click="changePage(currentPage + 1)" 
                :disabled="currentPage === lastPage"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === lastPage }"
              >
                <span class="sr-only">Next</span>
                &raquo;
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow-md p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No families found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by creating a new family.
      </p>
      <div class="mt-6">
        <router-link 
          to="/families/create" 
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Family
        </router-link>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Delete Family
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete the family "{{ familyToDelete?.name }}"? This action cannot be undone. All family relationships will be removed, but individual members will not be deleted.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="deleteFamily" 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="deleting"
            >
              <span v-if="deleting">Deleting...</span>
              <span v-else>Delete</span>
            </button>
            <button 
              @click="cancelDelete" 
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FamilyList',
  data() {
    return {
      families: [],
      loading: true,
      error: null,
      searchQuery: '',
      sortBy: 'name',
      sortDir: 'asc',
      currentPage: 1,
      perPage: 15,
      totalFamilies: 0,
      lastPage: 1,
      showDeleteModal: false,
      familyToDelete: null,
      deleting: false,
      searchTimeout: null
    };
  },
  computed: {
    paginationPages() {
      const pages = [];
      const maxVisiblePages = 5;
      
      if (this.lastPage <= maxVisiblePages) {
        // Show all pages if there are fewer than maxVisiblePages
        for (let i = 1; i <= this.lastPage; i++) {
          pages.push(i);
        }
      } else {
        // Always show first page
        pages.push(1);
        
        // Calculate start and end of middle pages
        let startPage = Math.max(2, this.currentPage - 1);
        let endPage = Math.min(this.lastPage - 1, this.currentPage + 1);
        
        // Adjust if we're near the beginning
        if (this.currentPage <= 3) {
          endPage = 4;
        }
        
        // Adjust if we're near the end
        if (this.currentPage >= this.lastPage - 2) {
          startPage = this.lastPage - 3;
        }
        
        // Add ellipsis after first page if needed
        if (startPage > 2) {
          pages.push('...');
        }
        
        // Add middle pages
        for (let i = startPage; i <= endPage; i++) {
          pages.push(i);
        }
        
        // Add ellipsis before last page if needed
        if (endPage < this.lastPage - 1) {
          pages.push('...');
        }
        
        // Always show last page
        pages.push(this.lastPage);
      }
      
      return pages;
    }
  },
  created() {
    this.fetchFamilies();
  },
  methods: {
    async fetchFamilies() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage,
          sort_by: this.sortBy,
          sort_dir: this.sortDir
        };
        
        if (this.searchQuery) {
          params.search = this.searchQuery;
        }
        
        const response = await axios.get('/families', { params });
        
        if (response.data.status === 'success') {
          this.families = response.data.data.data;
          this.totalFamilies = response.data.data.total;
          this.currentPage = response.data.data.current_page;
          this.lastPage = response.data.data.last_page;
        } else {
          this.error = 'Failed to load families';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching families';
      } finally {
        this.loading = false;
      }
    },
    
    changePage(page) {
      if (page === '...') return;
      if (page >= 1 && page <= this.lastPage) {
        this.currentPage = page;
        this.fetchFamilies();
      }
    },
    
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1; // Reset to first page when searching
        this.fetchFamilies();
      }, 300);
    },
    
    formatAddress(family) {
      const parts = [];
      
      if (family.address) parts.push(family.address);
      if (family.city) parts.push(family.city);
      
      if (family.state && family.zip) {
        parts.push(`${family.state}, ${family.zip}`);
      } else if (family.state) {
        parts.push(family.state);
      } else if (family.zip) {
        parts.push(family.zip);
      }
      
      if (family.country) parts.push(family.country);
      
      return parts.length > 0 ? parts.join(', ') : 'No address provided';
    },
    
    confirmDelete(family) {
      this.familyToDelete = family;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.familyToDelete = null;
    },
    
    async deleteFamily() {
      if (!this.familyToDelete) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/families/${this.familyToDelete.id}`);
        
        if (response.data.status === 'success') {
          // Remove the deleted family from the list
          this.families = this.families.filter(f => f.id !== this.familyToDelete.id);
          
          // If we deleted the last item on the page, go to the previous page
          if (this.families.length === 0 && this.currentPage > 1) {
            this.currentPage--;
            await this.fetchFamilies();
          } else if (this.totalFamilies > 0) {
            // Update total count
            this.totalFamilies--;
          }
          
          this.showDeleteModal = false;
          this.familyToDelete = null;
        } else {
          this.error = 'Failed to delete family';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while deleting the family';
      } finally {
        this.deleting = false;
      }
    }
  }
};
</script>
