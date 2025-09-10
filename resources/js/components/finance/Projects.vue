<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Fundraising Projects</h2>
      <button 
        @click="showAddModal = true" 
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Project
      </button>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
      <div class="relative flex-grow">
        <input 
          type="text" 
          v-model="search" 
          placeholder="Search projects..." 
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <span class="absolute right-3 top-2.5 text-gray-400">
          <i class="fas fa-search"></i>
        </span>
      </div>
      <div class="flex gap-2">
        <select 
          v-model="statusFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Status</option>
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
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

    <!-- Projects Grid -->
    <div v-else-if="filteredProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="project in filteredProjects" :key="project.id" class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="h-48 bg-gray-200 relative">
          <img 
            v-if="project.image_path" 
            :src="'/storage/' + project.image_path" 
            class="w-full h-full object-cover"
            alt="Project image"
          />
          <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-project-diagram text-4xl text-gray-400"></i>
          </div>
          <div class="absolute top-2 right-2">
            <span 
              :class="{
                'bg-green-100 text-green-800': project.status === 'active',
                'bg-blue-100 text-blue-800': project.status === 'completed',
                'bg-red-100 text-red-800': project.status === 'cancelled'
              }" 
              class="px-2 py-1 rounded-full text-xs font-medium"
            >
              {{ project.status.charAt(0).toUpperCase() + project.status.slice(1) }}
            </span>
          </div>
        </div>
        <div class="p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ project.name }}</h3>
          <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ project.description || 'No description provided' }}</p>
          
          <div class="mb-3">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Progress</span>
              <span>{{ project.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div 
                class="bg-indigo-600 h-2.5 rounded-full" 
                :style="{ width: project.progress_percentage + '%' }"
              ></div>
            </div>
          </div>
          
          <div class="flex justify-between text-sm mb-4">
            <span class="text-gray-600">
              <span class="font-medium">${{ formatAmount(project.current_amount) }}</span> raised
            </span>
            <span v-if="project.goal_amount" class="text-gray-600">
              Goal: <span class="font-medium">${{ formatAmount(project.goal_amount) }}</span>
            </span>
          </div>
          
          <div class="flex justify-between items-center">
            <div class="text-xs text-gray-500">
              <i class="far fa-calendar-alt mr-1"></i> 
              {{ formatDate(project.start_date) }} 
              <span v-if="project.end_date">- {{ formatDate(project.end_date) }}</span>
            </div>
            <div class="flex space-x-2">
              <button 
                @click="viewProject(project)" 
                class="text-indigo-600 hover:text-indigo-900"
                title="View Details"
              >
                <i class="fas fa-eye"></i>
              </button>
              <button 
                @click="editProject(project)" 
                class="text-blue-600 hover:text-blue-900"
                title="Edit"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button 
                @click="confirmDelete(project)" 
                class="text-red-600 hover:text-red-900"
                title="Delete"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <div class="text-gray-500 mb-4">
        <i class="fas fa-project-diagram text-4xl"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900">No projects found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by creating a new fundraising project.
      </p>
      <button 
        @click="showAddModal = true" 
        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Project
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showEditModal ? 'Edit Project' : 'Add New Project' }}
        </h3>
        <form @submit.prevent="showEditModal ? updateProject() : addProject()">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input 
                type="text" 
                v-model="form.name" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
              <input 
                type="text" 
                v-model="form.code" 
                :placeholder="form.name ? form.name.toLowerCase().replace(/\s+/g, '-') : ''"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate from name</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select 
                v-model="form.status" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Goal Amount ($)</label>
              <input 
                type="number" 
                v-model="form.goal_amount" 
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input 
                type="date" 
                v-model="form.start_date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input 
                type="date" 
                v-model="form.end_date" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea 
                v-model="form.description" 
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Project Image</label>
              <input 
                type="file" 
                @change="handleImageUpload" 
                accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p class="text-xs text-gray-500 mt-1">Optional. Maximum size: 2MB</p>
            </div>
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
          Are you sure you want to delete the project "{{ projectToDelete?.name }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="showDeleteModal = false" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Cancel
          </button>
          <button 
            @click="deleteProject" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            :disabled="loading"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Project Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-medium text-gray-900">{{ selectedProject?.name }}</h3>
          <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div v-if="selectedProject" class="space-y-6">
          <!-- Project Image -->
          <div class="h-60 bg-gray-200 rounded-lg overflow-hidden">
            <img 
              v-if="selectedProject.image_path" 
              :src="'/storage/' + selectedProject.image_path" 
              class="w-full h-full object-cover"
              alt="Project image"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
              <i class="fas fa-project-diagram text-5xl text-gray-400"></i>
            </div>
          </div>
          
          <!-- Project Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-medium text-gray-500">Status</h4>
              <p class="mt-1">
                <span 
                  :class="{
                    'bg-green-100 text-green-800': selectedProject.status === 'active',
                    'bg-blue-100 text-blue-800': selectedProject.status === 'completed',
                    'bg-red-100 text-red-800': selectedProject.status === 'cancelled'
                  }" 
                  class="px-2 py-1 rounded-full text-xs font-medium"
                >
                  {{ selectedProject.status.charAt(0).toUpperCase() + selectedProject.status.slice(1) }}
                </span>
              </p>
            </div>
            <div>
              <h4 class="text-sm font-medium text-gray-500">Code</h4>
              <p class="mt-1 text-gray-900">{{ selectedProject.code }}</p>
            </div>
            <div>
              <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
              <p class="mt-1 text-gray-900">{{ formatDate(selectedProject.start_date) }}</p>
            </div>
            <div>
              <h4 class="text-sm font-medium text-gray-500">End Date</h4>
              <p class="mt-1 text-gray-900">{{ selectedProject.end_date ? formatDate(selectedProject.end_date) : 'Not specified' }}</p>
            </div>
          </div>
          
          <!-- Progress -->
          <div>
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Progress</span>
              <span>{{ selectedProject.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
              <div 
                class="bg-indigo-600 h-2.5 rounded-full" 
                :style="{ width: selectedProject.progress_percentage + '%' }"
              ></div>
            </div>
            <div class="flex justify-between text-sm mb-4">
              <span class="text-gray-600">
                <span class="font-medium">${{ formatAmount(selectedProject.current_amount) }}</span> raised
              </span>
              <span v-if="selectedProject.goal_amount" class="text-gray-600">
                Goal: <span class="font-medium">${{ formatAmount(selectedProject.goal_amount) }}</span>
              </span>
            </div>
          </div>
          
          <!-- Description -->
          <div>
            <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedProject.description || 'No description provided' }}</p>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-between pt-4 border-t border-gray-200">
            <div>
              <button 
                @click="updateProjectStatus(selectedProject.id, selectedProject.status === 'active' ? 'completed' : 'active')" 
                class="px-4 py-2 border border-indigo-600 text-indigo-600 rounded-md text-sm font-medium hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                {{ selectedProject.status === 'active' ? 'Mark as Completed' : 'Reactivate Project' }}
              </button>
            </div>
            <div class="flex space-x-3">
              <button 
                @click="editProject(selectedProject); showDetailsModal = false" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Edit Project
              </button>
              <button 
                @click="showDonations(selectedProject.id)" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                View Donations
              </button>
            </div>
          </div>
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
  name: 'Projects',
  setup() {
    const toast = useToast();
    const projects = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const search = ref('');
    const statusFilter = ref('all');
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);
    const showDetailsModal = ref(false);
    const projectToDelete = ref(null);
    const selectedProject = ref(null);
    const form = ref({
      name: '',
      code: '',
      description: '',
      goal_amount: null,
      start_date: new Date().toISOString().split('T')[0],
      end_date: '',
      status: 'active',
      image: null
    });

    // Fetch all projects
    const fetchProjects = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get('/api/projects', {
          params: { with_stats: true }
        });
        projects.value = response.data.data;
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load projects';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Filtered projects based on search and filters
    const filteredProjects = computed(() => {
      let result = projects.value;
      
      // Apply search filter
      if (search.value) {
        const searchLower = search.value.toLowerCase();
        result = result.filter(project => 
          project.name.toLowerCase().includes(searchLower) || 
          project.code.toLowerCase().includes(searchLower) ||
          (project.description && project.description.toLowerCase().includes(searchLower))
        );
      }
      
      // Apply status filter
      if (statusFilter.value !== 'all') {
        result = result.filter(project => project.status === statusFilter.value);
      }
      
      return result;
    });

    // Handle image upload
    const handleImageUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
          toast.error('Image size should not exceed 2MB');
          event.target.value = '';
          return;
        }
        form.value.image = file;
      }
    };

    // Format amount with commas
    const formatAmount = (amount) => {
      return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    // Add a new project
    const addProject = async () => {
      loading.value = true;
      try {
        // If code is empty, generate from name
        if (!form.value.code && form.value.name) {
          form.value.code = form.value.name.toLowerCase().replace(/\s+/g, '-');
        }
        
        // Create FormData for file upload
        const formData = new FormData();
        Object.keys(form.value).forEach(key => {
          if (key === 'image' && form.value[key]) {
            formData.append('image', form.value[key]);
          } else if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key]);
          }
        });
        
        const response = await axios.post('/api/projects', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        projects.value.unshift(response.data.data);
        toast.success('Project added successfully');
        closeModal();
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to add project';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // View project details
    const viewProject = (project) => {
      selectedProject.value = project;
      showDetailsModal.value = true;
    };

    // Edit a project
    const editProject = (project) => {
      form.value = { 
        ...project,
        image: null // Reset image to prevent issues
      };
      showEditModal.value = true;
    };

    // Update a project
    const updateProject = async () => {
      loading.value = true;
      try {
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('_method', 'PUT'); // Laravel method spoofing
        
        Object.keys(form.value).forEach(key => {
          if (key === 'image' && form.value[key]) {
            formData.append('image', form.value[key]);
          } else if (form.value[key] !== null && form.value[key] !== '' && key !== 'image_path') {
            formData.append(key, form.value[key]);
          }
        });
        
        const response = await axios.post(`/api/projects/${form.value.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        const index = projects.value.findIndex(p => p.id === form.value.id);
        if (index !== -1) {
          projects.value[index] = response.data.data;
        }
        
        // Update selected project if it's the one being edited
        if (selectedProject.value && selectedProject.value.id === form.value.id) {
          selectedProject.value = response.data.data;
        }
        
        toast.success('Project updated successfully');
        closeModal();
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update project';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Confirm delete
    const confirmDelete = (project) => {
      projectToDelete.value = project;
      showDeleteModal.value = true;
    };

    // Delete a project
    const deleteProject = async () => {
      if (!projectToDelete.value) return;
      
      loading.value = true;
      try {
        await axios.delete(`/api/projects/${projectToDelete.value.id}`);
        projects.value = projects.value.filter(p => p.id !== projectToDelete.value.id);
        toast.success('Project deleted successfully');
        showDeleteModal.value = false;
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete project';
        toast.error(error.value);
      } finally {
        loading.value = false;
        projectToDelete.value = null;
      }
    };

    // Update project status
    const updateProjectStatus = async (id, status) => {
      loading.value = true;
      try {
        const response = await axios.patch(`/api/projects/${id}/status`, { status });
        
        // Update project in the list
        const index = projects.value.findIndex(p => p.id === id);
        if (index !== -1) {
          projects.value[index] = response.data.data;
        }
        
        // Update selected project if it's the one being updated
        if (selectedProject.value && selectedProject.value.id === id) {
          selectedProject.value = response.data.data;
        }
        
        toast.success(`Project ${status === 'completed' ? 'marked as completed' : 'reactivated'} successfully`);
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update project status';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // View project donations
    const showDonations = (projectId) => {
      // This would navigate to a donations page filtered by this project
      // For now, just show a toast
      toast.info('Donations view not implemented yet');
    };

    // Close modal and reset form
    const closeModal = () => {
      showAddModal.value = false;
      showEditModal.value = false;
      form.value = {
        name: '',
        code: '',
        description: '',
        goal_amount: null,
        start_date: new Date().toISOString().split('T')[0],
        end_date: '',
        status: 'active',
        image: null
      };
    };

    // Load projects on component mount
    onMounted(fetchProjects);

    return {
      projects,
      loading,
      error,
      search,
      statusFilter,
      showAddModal,
      showEditModal,
      showDeleteModal,
      showDetailsModal,
      form,
      projectToDelete,
      selectedProject,
      filteredProjects,
      fetchProjects,
      handleImageUpload,
      formatAmount,
      formatDate,
      addProject,
      viewProject,
      editProject,
      updateProject,
      confirmDelete,
      deleteProject,
      updateProjectStatus,
      showDonations,
      closeModal
    };
  }
};
</script>
