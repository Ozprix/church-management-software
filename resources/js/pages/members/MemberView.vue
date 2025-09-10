<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-else>
      <!-- Header with member info and actions -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div class="flex items-center mb-4 md:mb-0">
          <div class="h-16 w-16 rounded-full overflow-hidden bg-gray-200 mr-4">
            <img 
              v-if="member.profile_image" 
              :src="member.profile_image" 
              :alt="`${member.first_name} ${member.last_name}`" 
              class="h-full w-full object-cover"
            >
            <div v-else class="h-full w-full flex items-center justify-center bg-blue-100 text-blue-500">
              <span class="text-2xl font-bold">{{ member.first_name[0] }}{{ member.last_name[0] }}</span>
            </div>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ member.first_name }} {{ member.last_name }}</h1>
            <p class="text-gray-600">Member since {{ formatDate(member.membership_date) }}</p>
          </div>
        </div>
        <div class="flex space-x-2">
          <router-link 
            :to="`/members/${member.id}/edit`" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            Edit Member
          </router-link>
          <button 
            @click="confirmDelete" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Member details tabs -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button 
              v-for="tab in tabs" 
              :key="tab.id" 
              @click="activeTab = tab.id" 
              class="py-4 px-6 text-center border-b-2 font-medium text-sm"
              :class="activeTab === tab.id ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Profile tab -->
        <div v-if="activeTab === 'profile'" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-gray-500">Full Name</p>
                  <p class="text-base text-gray-900">{{ member.first_name }} {{ member.middle_name ? member.middle_name + ' ' : '' }}{{ member.last_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <p class="text-base text-gray-900">{{ member.email || 'Not provided' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Phone</p>
                  <p class="text-base text-gray-900">{{ member.phone || 'Not provided' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Date of Birth</p>
                  <p class="text-base text-gray-900">{{ member.date_of_birth ? formatDate(member.date_of_birth) : 'Not provided' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Gender</p>
                  <p class="text-base text-gray-900">{{ member.gender || 'Not provided' }}</p>
                </div>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Membership Details</h3>
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-gray-500">Membership Status</p>
                  <p class="text-base">
                    <span 
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': member.membership_status === 'active',
                        'bg-red-100 text-red-800': member.membership_status === 'inactive',
                        'bg-yellow-100 text-yellow-800': member.membership_status === 'pending',
                        'bg-blue-100 text-blue-800': member.membership_status === 'transferred'
                      }"
                    >
                      {{ member.membership_status }}
                    </span>
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Membership Date</p>
                  <p class="text-base text-gray-900">{{ formatDate(member.membership_date) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Family</p>
                  <p class="text-base text-gray-900">
                    <router-link 
                      v-if="member.family" 
                      :to="`/families/${member.family.id}`" 
                      class="text-blue-600 hover:text-blue-800"
                    >
                      {{ member.family.name }}
                    </router-link>
                    <span v-else>Not assigned to a family</span>
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Address</p>
                  <p class="text-base text-gray-900">{{ member.address || 'Not provided' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Donations tab -->
        <div v-if="activeTab === 'donations'" class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Donation History</h3>
          <!-- Donations will be displayed here -->
          <div v-if="loadingDonations" class="py-4 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600">Loading donations...</p>
          </div>
          <div v-else-if="donations.length === 0" class="py-4 text-center">
            <p class="text-gray-600">No donations found for this member.</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="donation in donations" :key="donation.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(donation.donation_date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ formatAmount(donation.amount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ donation.category ? donation.category.name : 'Uncategorized' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ donation.payment_method }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': donation.payment_status === 'completed',
                        'bg-yellow-100 text-yellow-800': donation.payment_status === 'pending',
                        'bg-red-100 text-red-800': donation.payment_status === 'failed',
                        'bg-gray-100 text-gray-800': !donation.payment_status
                      }"
                    >
                      {{ donation.payment_status || 'N/A' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tax Receipts tab -->
        <div v-if="activeTab === 'tax-receipts'" class="p-6">
          <member-tax-receipts :member-id="member.id" />
        </div>

        <!-- Attendance tab -->
        <div v-if="activeTab === 'attendance'" class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance History</h3>
          <p class="text-gray-600">Attendance records will be displayed here.</p>
        </div>

        <!-- Groups tab -->
        <div v-if="activeTab === 'groups'" class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Group Memberships</h3>
          <div v-if="loadingGroups" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600">Loading groups...</p>
          </div>
          <div v-else-if="memberGroups.length === 0" class="py-4 text-center">
            <p class="text-gray-600">This member is not part of any groups.</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="group in memberGroups" :key="group.id" class="border rounded-lg overflow-hidden shadow-sm">
              <div class="p-4">
                <h4 class="font-bold text-lg mb-1">{{ group.name }}</h4>
                <p class="text-gray-600 text-sm mb-2">{{ group.description }}</p>
                <div class="flex justify-between items-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="{
                      'bg-green-100 text-green-800': group.pivot.is_active,
                      'bg-red-100 text-red-800': !group.pivot.is_active
                    }">
                    {{ group.pivot.is_active ? 'Active' : 'Inactive' }}
                  </span>
                  <span class="text-sm text-gray-500">{{ group.pivot.role }}</span>
                </div>
                <div class="mt-3">
                  <router-link :to="`/groups/${group.id}`" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View Group
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Management tab -->
        <div v-if="activeTab === 'management'" class="p-6">
          <member-management-tabs :member-id="member.id" initial-tab="skills" />
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Deletion</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete {{ member.first_name }} {{ member.last_name }}? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="cancelDelete" 
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded"
          >
            Cancel
          </button>
          <button 
            @click="deleteMember" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
            :disabled="deleting"
          >
            <span v-if="deleting">Deleting...</span>
            <span v-else>Delete</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MemberManagementTabs from '../../components/member/MemberManagementTabs.vue';
import MemberTaxReceipts from '../../components/members/MemberTaxReceipts.vue';

export default {
  name: 'MemberView',
  components: {
    MemberManagementTabs,
    MemberTaxReceipts
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    
    const member = ref({});
    const loading = ref(true);
    const error = ref(null);
    const activeTab = ref('profile');
    const showDeleteModal = ref(false);
    const deleting = ref(false);
    
    const donations = ref([]);
    const loadingDonations = ref(false);
    
    const memberGroups = ref([]);
    const loadingGroups = ref(false);
    
    const tabs = [
      { id: 'profile', name: 'Profile' },
      { id: 'donations', name: 'Donations' },
      { id: 'tax-receipts', name: 'Tax Receipts' },
      { id: 'attendance', name: 'Attendance' },
      { id: 'groups', name: 'Groups' },
      { id: 'management', name: 'Management' }
    ];
    
    const fetchMember = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        const response = await axios.get(`/api/members/${route.params.id}`);
        
        if (response.data.success) {
          member.value = response.data.data;
        } else {
          error.value = 'Failed to load member details';
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching member details';
      } finally {
        loading.value = false;
      }
    };
    
    const fetchDonations = async () => {
      if (activeTab.value !== 'donations') return;
      
      loadingDonations.value = true;
      
      try {
        const response = await axios.get(`/api/members/${route.params.id}/donations`);
        
        if (response.data.success) {
          donations.value = response.data.data;
        } else {
          // Just show empty state, no error
          donations.value = [];
        }
      } catch (err) {
        console.error('Error fetching donations:', err);
        donations.value = [];
      } finally {
        loadingDonations.value = false;
      }
    };
    
    const fetchMemberGroups = async () => {
      if (activeTab.value !== 'groups') return;
      
      loadingGroups.value = true;
      
      try {
        const response = await axios.get(`/api/members/${route.params.id}/groups`);
        
        if (response.data.status === 'success') {
          memberGroups.value = response.data.data;
        } else {
          // Just show empty state, no error
          memberGroups.value = [];
        }
      } catch (err) {
        console.error('Error fetching member groups:', err);
        memberGroups.value = [];
      } finally {
        loadingGroups.value = false;
      }
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    };
    
    const formatAmount = (amount) => {
      return parseFloat(amount).toFixed(2);
    };
    
    const confirmDelete = () => {
      showDeleteModal.value = true;
    };
    
    const cancelDelete = () => {
      showDeleteModal.value = false;
    };
    
    const deleteMember = async () => {
      deleting.value = true;
      
      try {
        const response = await axios.delete(`/api/members/${route.params.id}`);
        
        if (response.data.success) {
          router.push('/members');
        } else {
          error.value = 'Failed to delete member';
          showDeleteModal.value = false;
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while deleting the member';
        showDeleteModal.value = false;
      } finally {
        deleting.value = false;
      }
    };
    
    onMounted(() => {
      fetchMember();
    });
    
    // Watch for tab changes to load data as needed
    const watchActiveTab = (newTab) => {
      if (newTab === 'donations') {
        fetchDonations();
      } else if (newTab === 'groups') {
        fetchMemberGroups();
      }
    };
    
    return {
      member,
      loading,
      error,
      activeTab,
      tabs,
      donations,
      loadingDonations,
      memberGroups,
      loadingGroups,
      showDeleteModal,
      deleting,
      formatDate,
      formatAmount,
      confirmDelete,
      cancelDelete,
      deleteMember,
      watchActiveTab
    };
  }
};
</script>
