<template>
  <div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-4 md:mb-0">Groups & Ministries</h1>
      <router-link 
        to="/groups/create" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        Create Group
      </router-link>
    </div>

    <!-- Alert Messages -->
    <div v-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      {{ error }}
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search by name..."
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @input="onSearchInput"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="filters.type"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="fetchGroups"
          >
            <option value="">All Types</option>
            <option value="ministry">Ministry</option>
            <option value="committee">Committee</option>
            <option value="small_group">Small Group</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.is_active"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @change="fetchGroups"
          >
            <option value="">All Status</option>
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <div class="flex">
            <select
              v-model="sortBy"
              class="w-full border border-gray-300 rounded-l-md px-3 py-2"
              @change="fetchGroups"
            >
              <option value="name">Name</option>
              <option value="type">Type</option>
              <option value="created_at">Created Date</option>
            </select>
            <button
              @click="toggleSortDirection"
              class="border border-l-0 border-gray-300 rounded-r-md px-3 py-2 bg-gray-50"
            >
              <i :class="sortDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Groups List -->
    <div v-else-if="groups.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="group in groups" :key="group.id" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-start">
            <h3 class="text-lg font-semibold text-gray-800">{{ group.name }}</h3>
            <span 
              class="px-2 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-green-100 text-green-800': group.is_active,
                'bg-red-100 text-red-800': !group.is_active
              }"
            >
              {{ group.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-1">
            {{ capitalizeFirstLetter(group.type.replace('_', ' ')) }}
          </p>
        </div>
        <div class="p-6">
          <p v-if="group.description" class="text-gray-600 text-sm mb-4">
            {{ truncateText(group.description, 100) }}
          </p>
          <div v-if="group.leader" class="flex items-center mb-4">
            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-2">
              <span>{{ getInitials(group.leader.first_name, group.leader.last_name) }}</span>
            </div>
            <div>
              <p class="text-sm text-gray-500">Leader</p>
              <p class="text-sm font-medium">{{ group.leader.first_name }} {{ group.leader.last_name }}</p>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Members</p>
              <p class="text-sm font-medium">{{ group.active_member_count || 0 }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Meeting</p>
              <p class="text-sm font-medium">{{ group.meeting_day || 'Not set' }}</p>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <div class="flex justify-between">
            <router-link 
              :to="`/groups/${group.id}`" 
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              View Details
            </router-link>
            <router-link 
              :to="`/groups/${group.id}/edit`" 
              class="text-gray-600 hover:text-gray-800 text-sm font-medium"
            >
              Edit
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white shadow rounded-lg p-6 text-center">
      <div class="py-12">
        <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
          <i class="fas fa-users-slash text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No groups found</h3>
        <p class="text-gray-500 mb-6">
          {{ searchQuery || filters.type || filters.is_active !== '' 
            ? 'Try adjusting your filters to find what you\'re looking for.' 
            : 'Get started by creating your first group.' }}
        </p>
        <router-link 
          to="/groups/create" 
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          <i class="fas fa-plus mr-2"></i>
          Create Group
        </router-link>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="groups.length > 0" class="mt-6 flex justify-between items-center">
      <div class="text-sm text-gray-500">
        Showing {{ groups.length }} of {{ totalGroups }} groups
      </div>
      <div class="flex space-x-2">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1" 
          :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
          class="px-3 py-1 border border-gray-300 rounded-md text-sm"
        >
          Previous
        </button>
        <button 
          @click="nextPage" 
          :disabled="currentPage === lastPage" 
          :class="{ 'opacity-50 cursor-not-allowed': currentPage === lastPage }"
          class="px-3 py-1 border border-gray-300 rounded-md text-sm"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-gray-500 mb-6">
          Are you sure you want to delete the group "{{ groupToDelete?.name }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="cancelDelete" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="deleteGroup" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700"
            :disabled="deleting"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GroupList',
  data() {
    return {
      groups: [],
      loading: true,
      error: null,
      searchQuery: '',
      filters: {
        type: '',
        is_active: ''
      },
      sortBy: 'name',
      sortDir: 'asc',
      currentPage: 1,
      perPage: 15,
      totalGroups: 0,
      lastPage: 1,
      showDeleteModal: false,
      groupToDelete: null,
      deleting: false,
      searchTimeout: null
    };
  },
  created() {
    this.fetchGroups();
  },
  methods: {
    async fetchGroups() {
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
        
        if (this.filters.type) {
          params.type = this.filters.type;
        }
        
        if (this.filters.is_active !== '') {
          params.is_active = this.filters.is_active;
        }
        
        const response = await axios.get('/api/groups', { params });
        
        if (response.data.status === 'success') {
          this.groups = response.data.data.data;
          this.totalGroups = response.data.data.total;
          this.currentPage = response.data.data.current_page;
          this.lastPage = response.data.data.last_page;
        } else {
          this.error = 'Failed to load groups';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching groups';
      } finally {
        this.loading = false;
      }
    },
    onSearchInput() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.fetchGroups();
      }, 500);
    },
    toggleSortDirection() {
      this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      this.fetchGroups();
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.fetchGroups();
      }
    },
    nextPage() {
      if (this.currentPage < this.lastPage) {
        this.currentPage++;
        this.fetchGroups();
      }
    },
    confirmDelete(group) {
      this.groupToDelete = group;
      this.showDeleteModal = true;
    },
    cancelDelete() {
      this.groupToDelete = null;
      this.showDeleteModal = false;
    },
    async deleteGroup() {
      if (!this.groupToDelete) return;
      
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/api/groups/${this.groupToDelete.id}`);
        
        if (response.data.status === 'success') {
          // Remove the deleted group from the list
          this.groups = this.groups.filter(g => g.id !== this.groupToDelete.id);
          
          // If we deleted the last item on the page, go to the previous page
          if (this.groups.length === 0 && this.currentPage > 1) {
            this.currentPage--;
            await this.fetchGroups();
          } else if (this.totalGroups > 0) {
            // Update total count
            this.totalGroups--;
          }
          
          this.showDeleteModal = false;
          this.groupToDelete = null;
        } else {
          this.error = 'Failed to delete group';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while deleting the group';
      } finally {
        this.deleting = false;
      }
    },
    getInitials(firstName, lastName) {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`;
    },
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    truncateText(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>
