<template>
  <div class="campaigns-list">
    <!-- Campaigns Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Add Campaign Card -->
      <div 
        @click="showAddCampaignModal = true"
        class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer"
      >
        <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 flex items-center justify-center">
          <svg class="h-16 w-16 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </div>
        <div class="p-4 text-center">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Add New Campaign</h3>
          <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
            Create a new fundraising campaign for your church
          </p>
        </div>
      </div>
      
      <!-- Campaign Cards -->
      <div 
        v-for="campaign in donationStore.campaigns" 
        :key="campaign.id" 
        class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-200"
      >
        <!-- Campaign Image -->
        <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 relative">
          <img 
            v-if="campaign.image" 
            :src="campaign.image" 
            :alt="campaign.name" 
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="h-16 w-16 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
            </svg>
          </div>
          
          <!-- Status Badge -->
          <div class="absolute top-2 right-2">
            <span 
              class="px-2 py-1 text-xs font-medium rounded-full"
              :class="getStatusClass(campaign.status)"
            >
              {{ formatStatus(campaign.status) }}
            </span>
          </div>
        </div>
        
        <!-- Campaign Content -->
        <div class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span 
              class="px-2 py-0.5 text-xs font-medium rounded-full"
              :style="getCategoryStyle(campaign.categoryId)"
            >
              {{ getCategoryName(campaign.categoryId) }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ formatDateRange(campaign.startDate, campaign.endDate) }}
            </span>
          </div>
          
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            {{ campaign.name }}
          </h3>
          
          <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300 line-clamp-2">
            {{ campaign.description }}
          </p>
          
          <!-- Progress Bar -->
          <div class="mt-4">
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium text-neutral-900 dark:text-white">{{ formatCurrency(campaign.raised) }}</span>
              <span class="text-neutral-500 dark:text-neutral-400">of {{ formatCurrency(campaign.goal) }}</span>
            </div>
            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 mt-1">
              <div 
                class="h-2.5 rounded-full" 
                :class="getProgressColorClass(campaign)"
                :style="{ width: `${calculateProgress(campaign)}%` }"
              ></div>
            </div>
            <div class="text-xs mt-1 text-right text-neutral-500 dark:text-neutral-400">
              {{ calculateProgress(campaign) }}% Complete
            </div>
          </div>
          
          <!-- Actions -->
          <div class="mt-4 flex items-center justify-between">
            <button 
              @click="viewCampaign(campaign)" 
              class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              View Details
            </button>
            
            <div>
              <button 
                @click="editCampaign(campaign)" 
                class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
              >
                Edit
              </button>
              <button 
                @click="confirmDeleteCampaign(campaign)" 
                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="donationStore.campaigns.length === 0" class="md:col-span-2 lg:col-span-3">
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
          <svg class="h-12 w-12 mx-auto text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
          </svg>
          <h3 class="mt-2 text-lg font-medium text-neutral-900 dark:text-white">No campaigns</h3>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Get started by creating a new fundraising campaign.
          </p>
          <div class="mt-6">
            <button
              @click="showAddCampaignModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Create Campaign
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Campaign Detail Modal -->
    <div v-if="showCampaignDetailModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showCampaignDetailModal = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-neutral-800">
            <!-- Campaign Image -->
            <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 relative">
              <img 
                v-if="selectedCampaign?.image" 
                :src="selectedCampaign.image" 
                :alt="selectedCampaign?.name" 
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="h-16 w-16 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
              </div>
              
              <!-- Status Badge -->
              <div class="absolute top-2 right-2">
                <span 
                  v-if="selectedCampaign"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(selectedCampaign.status)"
                >
                  {{ formatStatus(selectedCampaign.status) }}
                </span>
              </div>
            </div>
            
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                  <div class="flex items-center justify-between mb-2">
                    <span 
                      v-if="selectedCampaign"
                      class="px-2 py-0.5 text-xs font-medium rounded-full"
                      :style="getCategoryStyle(selectedCampaign.categoryId)"
                    >
                      {{ getCategoryName(selectedCampaign?.categoryId) }}
                    </span>
                    <span v-if="selectedCampaign" class="text-xs text-neutral-500 dark:text-neutral-400">
                      {{ formatDateRange(selectedCampaign.startDate, selectedCampaign.endDate) }}
                    </span>
                  </div>
                  
                  <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                    {{ selectedCampaign?.name }}
                  </h3>
                  
                  <div class="mt-4">
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                      {{ selectedCampaign?.description }}
                    </p>
                  </div>
                  
                  <!-- Progress Bar -->
                  <div class="mt-6">
                    <div class="flex items-center justify-between text-sm">
                      <span v-if="selectedCampaign" class="font-medium text-neutral-900 dark:text-white">{{ formatCurrency(selectedCampaign.raised) }}</span>
                      <span v-if="selectedCampaign" class="text-neutral-500 dark:text-neutral-400">of {{ formatCurrency(selectedCampaign.goal) }}</span>
                    </div>
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 mt-1">
                      <div 
                        v-if="selectedCampaign"
                        class="h-2.5 rounded-full" 
                        :class="getProgressColorClass(selectedCampaign)"
                        :style="{ width: `${calculateProgress(selectedCampaign)}%` }"
                      ></div>
                    </div>
                    <div v-if="selectedCampaign" class="text-xs mt-1 text-right text-neutral-500 dark:text-neutral-400">
                      {{ calculateProgress(selectedCampaign) }}% Complete
                    </div>
                  </div>
                  
                  <!-- Recent Donations -->
                  <div class="mt-6">
                    <h4 class="text-sm font-medium text-neutral-900 dark:text-white">Recent Donations</h4>
                    <div class="mt-2 space-y-2">
                      <div 
                        v-for="donation in getCampaignDonations(selectedCampaign?.id).slice(0, 3)" 
                        :key="donation.id" 
                        class="bg-neutral-50 dark:bg-neutral-700 p-2 rounded-md"
                      >
                        <div class="flex justify-between items-center">
                          <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ donation.memberName }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(donation.date) }}</div>
                          </div>
                          <div class="text-sm font-medium text-neutral-900 dark:text-white">
                            {{ formatCurrency(donation.amount) }}
                          </div>
                        </div>
                      </div>
                      
                      <div v-if="getCampaignDonations(selectedCampaign?.id).length === 0" class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-2">
                        No donations yet for this campaign.
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button 
                type="button" 
                @click="showCampaignDetailModal = false" 
                class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Add/Edit Campaign Modal -->
    <div v-if="showAddCampaignModal || showEditCampaignModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModals"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  {{ showEditCampaignModal ? 'Edit Campaign' : 'Create New Campaign' }}
                </h3>
                <div class="mt-4">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Campaign form will be implemented in the next step.
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
              {{ showEditCampaignModal ? 'Save Changes' : 'Create Campaign' }}
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
                  Delete Campaign
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Are you sure you want to delete this campaign? This action cannot be undone.
                  </p>
                  <p v-if="hasDonations" class="mt-2 text-sm text-red-500">
                    This campaign has donations associated with it and cannot be deleted.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deleteCampaign" 
              :disabled="hasDonations"
              :class="[
                hasDonations ? 'bg-red-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700',
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
import { ref, computed } from 'vue';
import { useDonationStore } from '../../stores/donations';

// Store
const donationStore = useDonationStore();

// State
const showAddCampaignModal = ref(false);
const showEditCampaignModal = ref(false);
const showCampaignDetailModal = ref(false);
const showDeleteModal = ref(false);
const selectedCampaign = ref(null);
const hasDonations = ref(false);

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
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

const calculateProgress = (campaign) => {
  if (campaign.goal <= 0) return 0;
  const percentage = (campaign.raised / campaign.goal) * 100;
  return Math.min(Math.round(percentage), 100);
};

const getProgressColorClass = (campaign) => {
  const progress = calculateProgress(campaign);
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

const formatStatus = (status) => {
  const statuses = {
    'active': 'Active',
    'completed': 'Completed',
    'planned': 'Planned',
    'cancelled': 'Cancelled'
  };
  
  return statuses[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'completed': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'planned': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  };
  
  return classes[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200';
};

const getCampaignDonations = (campaignId) => {
  if (!campaignId) return [];
  return donationStore.donations.filter(donation => donation.campaignId === campaignId);
};

const viewCampaign = (campaign) => {
  selectedCampaign.value = campaign;
  showCampaignDetailModal.value = true;
};

const editCampaign = (campaign) => {
  selectedCampaign.value = campaign;
  showEditCampaignModal.value = true;
};

const confirmDeleteCampaign = (campaign) => {
  selectedCampaign.value = campaign;
  
  // Check if there are donations associated with this campaign
  hasDonations.value = donationStore.donations.some(donation => donation.campaignId === campaign.id);
  
  showDeleteModal.value = true;
};

const deleteCampaign = () => {
  if (selectedCampaign.value && !hasDonations.value) {
    donationStore.deleteCampaign(selectedCampaign.value.id);
    showDeleteModal.value = false;
    selectedCampaign.value = null;
  }
};

const closeModals = () => {
  showAddCampaignModal.value = false;
  showEditCampaignModal.value = false;
  showCampaignDetailModal.value = false;
  selectedCampaign.value = null;
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
