<template>
  <div class="pledge-campaign-manager">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
        <div>
          <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Pledge Campaigns</h3>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Create and manage pledge campaigns for your church
          </p>
        </div>
        <button 
          @click="showCreateModal = true" 
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <i class="fas fa-plus mr-2"></i> New Campaign
        </button>
      </div>
      
      <!-- Campaign List -->
      <div v-if="loading" class="p-6 flex justify-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-500"></div>
      </div>
      
      <div v-else-if="campaigns.length === 0" class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-neutral-100 dark:bg-neutral-700">
          <i class="fas fa-hand-holding-heart text-neutral-500 dark:text-neutral-400"></i>
        </div>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No campaigns</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Get started by creating a new pledge campaign.
        </p>
        <div class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <i class="fas fa-plus mr-2"></i> New Campaign
          </button>
        </div>
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
          <thead class="bg-neutral-50 dark:bg-neutral-900">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Campaign
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Goal
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Progress
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Dates
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
            <tr v-for="campaign in campaigns" :key="campaign.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                    <i :class="campaign.icon || 'fas fa-hand-holding-heart'"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-neutral-900 dark:text-white">
                      {{ campaign.name }}
                    </div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                      {{ truncate(campaign.description, 60) }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-neutral-900 dark:text-white">{{ formatCurrency(campaign.goal) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5">
                  <div class="bg-primary-600 h-2.5 rounded-full" :style="{ width: `${Math.min(100, (campaign.current_amount / campaign.goal) * 100)}%` }"></div>
                </div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                  {{ formatCurrency(campaign.current_amount) }} ({{ formatPercentage(campaign.current_amount / campaign.goal) }})
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-neutral-900 dark:text-white">
                  {{ formatDate(campaign.start_date) }} - {{ formatDate(campaign.end_date) }}
                </div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                  {{ getRemainingDays(campaign.end_date) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClasses(campaign.status)">
                  {{ formatStatus(campaign.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <button 
                    @click="viewCampaign(campaign)" 
                    class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button 
                    @click="editCampaign(campaign)" 
                    class="text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-300"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    @click="confirmDelete(campaign)" 
                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Create/Edit Campaign Modal -->
    <Modal v-if="showCreateModal || showEditModal" :title="modalTitle" @close="closeModal">
      <div class="space-y-4">
        <FormField
          id="campaign-name"
          label="Campaign Name"
          :error="formErrors.name"
          required
        >
          <input 
            id="campaign-name" 
            v-model="campaignForm.name" 
            type="text" 
            class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
            placeholder="e.g., Building Fund 2025"
          />
        </FormField>
        
        <FormField
          id="campaign-description"
          label="Description"
          :error="formErrors.description"
        >
          <textarea 
            id="campaign-description" 
            v-model="campaignForm.description" 
            rows="3" 
            class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
            placeholder="Describe the purpose of this campaign"
          ></textarea>
        </FormField>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField
            id="campaign-goal"
            label="Goal Amount"
            :error="formErrors.goal"
            required
          >
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-neutral-500 dark:text-neutral-400 sm:text-sm">$</span>
              </div>
              <input 
                id="campaign-goal" 
                v-model="campaignForm.goal" 
                type="number" 
                min="0" 
                step="0.01" 
                class="pl-7 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
                placeholder="0.00"
              />
            </div>
          </FormField>
          
          <FormField
            id="campaign-icon"
            label="Icon"
            :error="formErrors.icon"
          >
            <select 
              id="campaign-icon" 
              v-model="campaignForm.icon" 
              class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
            >
              <option value="fas fa-hand-holding-heart">Giving Hand</option>
              <option value="fas fa-church">Church</option>
              <option value="fas fa-building">Building</option>
              <option value="fas fa-cross">Cross</option>
              <option value="fas fa-bible">Bible</option>
              <option value="fas fa-hands-helping">Helping Hands</option>
              <option value="fas fa-seedling">Seedling</option>
              <option value="fas fa-graduation-cap">Education</option>
              <option value="fas fa-globe-americas">Missions</option>
            </select>
          </FormField>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField
            id="campaign-start-date"
            label="Start Date"
            :error="formErrors.start_date"
            required
          >
            <input 
              id="campaign-start-date" 
              v-model="campaignForm.start_date" 
              type="date" 
              class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
            />
          </FormField>
          
          <FormField
            id="campaign-end-date"
            label="End Date"
            :error="formErrors.end_date"
            required
          >
            <input 
              id="campaign-end-date" 
              v-model="campaignForm.end_date" 
              type="date" 
              class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-neutral-700 dark:text-white"
            />
          </FormField>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <button 
            @click="closeModal" 
            class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Cancel
          </button>
          <button 
            @click="saveCampaign" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            {{ showEditModal ? 'Update Campaign' : 'Create Campaign' }}
          </button>
        </div>
      </template>
    </Modal>
    
    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete Campaign"
      :message="`Are you sure you want to delete the campaign '${campaignToDelete?.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      confirm-variant="danger"
      @confirm="deleteCampaign"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useDonationsStore } from '../../../stores/donations';
import { useToast } from '../../../composables/useToast';
import Modal from '../../ui/Modal.vue';
import ConfirmationModal from '../../ui/ConfirmationModal.vue';
import FormField from '../../ui/FormField.vue';

// Router and stores
const router = useRouter();
const donationsStore = useDonationsStore();
const toast = useToast();

// Component state
const loading = ref(false);
const campaigns = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const campaignToDelete = ref(null);
const formErrors = ref({});

// Form state
const campaignForm = ref({
  name: '',
  description: '',
  goal: '',
  icon: 'fas fa-hand-holding-heart',
  start_date: '',
  end_date: '',
  status: 'active'
});

// Computed properties
const modalTitle = computed(() => {
  return showEditModal.value ? 'Edit Campaign' : 'Create New Campaign';
});

// Methods
const loadCampaigns = async () => {
  loading.value = true;
  try {
    const response = await donationsStore.fetchPledgeCampaigns();
    campaigns.value = response.data;
  } catch (error) {
    toast.error('Failed to load campaigns');
    console.error('Error loading campaigns:', error);
  } finally {
    loading.value = false;
  }
};

const viewCampaign = (campaign) => {
  router.push({ name: 'pledge-campaign-details', params: { id: campaign.id } });
};

const editCampaign = (campaign) => {
  campaignForm.value = { ...campaign };
  showEditModal.value = true;
};

const confirmDelete = (campaign) => {
  campaignToDelete.value = campaign;
  showDeleteModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  resetForm();
};

const resetForm = () => {
  campaignForm.value = {
    name: '',
    description: '',
    goal: '',
    icon: 'fas fa-hand-holding-heart',
    start_date: '',
    end_date: '',
    status: 'active'
  };
  formErrors.value = {};
};

const validateForm = () => {
  const errors = {};
  
  if (!campaignForm.value.name) {
    errors.name = 'Campaign name is required';
  }
  
  if (!campaignForm.value.goal || parseFloat(campaignForm.value.goal) <= 0) {
    errors.goal = 'Goal must be greater than zero';
  }
  
  if (!campaignForm.value.start_date) {
    errors.start_date = 'Start date is required';
  }
  
  if (!campaignForm.value.end_date) {
    errors.end_date = 'End date is required';
  } else if (campaignForm.value.start_date && campaignForm.value.end_date && 
             new Date(campaignForm.value.end_date) <= new Date(campaignForm.value.start_date)) {
    errors.end_date = 'End date must be after start date';
  }
  
  formErrors.value = errors;
  return Object.keys(errors).length === 0;
};

const saveCampaign = async () => {
  if (!validateForm()) return;
  
  try {
    if (showEditModal.value) {
      await donationsStore.updatePledgeCampaign(campaignForm.value);
      toast.success('Campaign updated successfully');
    } else {
      await donationsStore.createPledgeCampaign(campaignForm.value);
      toast.success('Campaign created successfully');
    }
    
    closeModal();
    loadCampaigns();
  } catch (error) {
    toast.error(showEditModal.value ? 'Failed to update campaign' : 'Failed to create campaign');
    console.error('Error saving campaign:', error);
  }
};

const deleteCampaign = async () => {
  if (!campaignToDelete.value) return;
  
  try {
    await donationsStore.deletePledgeCampaign(campaignToDelete.value.id);
    toast.success('Campaign deleted successfully');
    showDeleteModal.value = false;
    loadCampaigns();
  } catch (error) {
    toast.error('Failed to delete campaign');
    console.error('Error deleting campaign:', error);
  }
};

// Utility functions
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value);
};

const formatPercentage = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'percent',
    minimumFractionDigits: 0,
    maximumFractionDigits: 1
  }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(date);
};

const getRemainingDays = (endDateString) => {
  if (!endDateString) return '';
  
  const endDate = new Date(endDateString);
  const today = new Date();
  
  // Clear time portion for accurate day calculation
  endDate.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);
  
  const diffTime = endDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) {
    return 'Ended';
  } else if (diffDays === 0) {
    return 'Ends today';
  } else if (diffDays === 1) {
    return '1 day left';
  } else {
    return `${diffDays} days left`;
  }
};

const formatStatus = (status) => {
  const statusMap = {
    'active': 'Active',
    'completed': 'Completed',
    'upcoming': 'Upcoming',
    'cancelled': 'Cancelled'
  };
  
  return statusMap[status] || status;
};

const getStatusClasses = (status) => {
  const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
  
  const statusClassMap = {
    'active': `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200`,
    'completed': `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200`,
    'upcoming': `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200`,
    'cancelled': `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200`
  };
  
  return statusClassMap[status] || `${baseClasses} bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200`;
};

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

// Lifecycle hooks
onMounted(() => {
  loadCampaigns();
});
</script>
