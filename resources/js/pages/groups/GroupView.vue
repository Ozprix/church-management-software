<template>
  <div>
    <!-- Header -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-6">
      {{ error }}
    </div>

    <div v-else>
      <!-- Group Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ group.name }}</h1>
          <p class="text-gray-600">
            {{ capitalizeFirstLetter(group.type.replace('_', ' ')) }}
            <span 
              class="ml-2 px-2 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-green-100 text-green-800': group.is_active,
                'bg-red-100 text-red-800': !group.is_active
              }"
            >
              {{ group.is_active ? 'Active' : 'Inactive' }}
            </span>
          </p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
          <router-link 
            :to="`/groups/${group.id}/events`" 
            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-calendar-alt mr-2"></i> Events
          </router-link>
          <router-link 
            :to="`/groups/${group.id}/attendances`" 
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-calendar-check mr-2"></i> Attendance
          </router-link>
          <router-link 
            :to="`/groups/${group.id}/edit`" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            Edit Group
          </router-link>
          <button 
            @click="confirmDelete" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button 
              v-for="tab in tabs" 
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id 
                  ? 'border-blue-500 text-blue-600' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Details Tab -->
        <div v-if="activeTab === 'details'" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Group Information</h3>
              <div class="space-y-4">
                <div v-if="group.description">
                  <p class="text-sm text-gray-500">Description</p>
                  <p class="text-gray-900">{{ group.description }}</p>
                </div>
                <div v-if="group.leader">
                  <p class="text-sm text-gray-500">Leader</p>
                  <div class="flex items-center mt-1">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-2">
                      <span>{{ getInitials(group.leader.first_name, group.leader.last_name) }}</span>
                    </div>
                    <router-link :to="`/members/${group.leader.id}`" class="text-blue-600 hover:text-blue-800">
                      {{ group.leader.first_name }} {{ group.leader.last_name }}
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Meeting Details</h3>
              <div class="space-y-4">
                <div>
                  <p class="text-sm text-gray-500">Meeting Day</p>
                  <p class="text-gray-900">{{ group.meeting_day || 'Not specified' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Meeting Time</p>
                  <p class="text-gray-900">{{ formatTime(group.meeting_time) || 'Not specified' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Meeting Location</p>
                  <p class="text-gray-900">{{ group.meeting_location || 'Not specified' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Events Tab -->
        <div v-if="activeTab === 'events'" class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Group Events</h3>
            <router-link 
              :to="`/groups/${group.id}/events`" 
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
            >
              <i class="fas fa-calendar-alt mr-2"></i> View All Events
            </router-link>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6 text-center">
            <div class="mb-4">
              <i class="fas fa-calendar-alt text-4xl text-blue-500"></i>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Schedule Group Events</h4>
            <p class="text-gray-600 mb-4">Plan meetings, outreach activities, social gatherings, and more for your group members.</p>
            <div class="flex justify-center space-x-4">
              <router-link 
                :to="`/groups/${group.id}/events`" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                View Events
              </router-link>
              <router-link 
                :to="`/groups/${group.id}/events/new`" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
              >
                Create New Event
              </router-link>
            </div>
          </div>
        </div>
        
        <!-- Member Roles Tab -->
        <div v-if="activeTab === 'roles'" class="p-6">
          <group-member-roles :group-id="$route.params.id"></group-member-roles>
        </div>
        
        <!-- Attendance Tab -->
        <div v-if="activeTab === 'attendance'" class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Group Attendance</h3>
            <router-link 
              :to="`/groups/${group.id}/attendances`" 
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
            >
              <i class="fas fa-calendar-check mr-2"></i> View Attendance Records
            </router-link>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6 text-center">
            <div class="mb-4">
              <i class="fas fa-calendar-alt text-4xl text-blue-500"></i>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Track Group Attendance</h4>
            <p class="text-gray-600 mb-4">Record attendance for group meetings, track member participation, and monitor growth over time.</p>
            <div class="flex justify-center space-x-4">
              <router-link 
                :to="`/groups/${group.id}/attendances`" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                View Records
              </router-link>
              <router-link 
                :to="`/groups/${group.id}/attendances/new`" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
              >
                Record New Attendance
              </router-link>
            </div>
          </div>
        </div>

        <!-- Members Tab -->
        <div v-if="activeTab === 'members'" class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Group Members</h3>
            <button
              @click="showAddMemberModal = true"
              class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded"
            >
              Add Member
            </button>
          </div>

          <!-- Loading Members -->
          <div v-if="loadingMembers" class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
          </div>

          <!-- Members List -->
          <div v-else-if="members.length > 0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Member
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Role
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Join Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="member in members" :key="member.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                            <span>{{ getInitials(member.member.first_name, member.member.last_name) }}</span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <router-link :to="`/members/${member.member.id}`" class="text-blue-600 hover:text-blue-800">
                            {{ member.member.first_name }} {{ member.member.last_name }}
                          </router-link>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      {{ capitalizeFirstLetter(member.role.replace('_', ' ')) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      {{ formatDate(member.join_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800': member.is_active,
                          'bg-red-100 text-red-800': !member.is_active
                        }"
                      >
                        {{ member.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button 
                        @click="editMember(member)" 
                        class="text-blue-600 hover:text-blue-800 mr-3"
                      >
                        Edit
                      </button>
                      <button 
                        @click="confirmRemoveMember(member)" 
                        class="text-red-600 hover:text-red-800"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Empty Members State -->
          <div v-else class="text-center py-8">
            <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
              <i class="fas fa-users-slash text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No members yet</h3>
            <p class="text-gray-500 mb-6">Add members to this group to get started.</p>
            <button
              @click="showAddMemberModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Member
            </button>
          </div>
        </div>
        
        <!-- Analytics Tab -->
        <div v-if="activeTab === 'analytics'" class="p-6">
          <group-analytics :group-id="$route.params.id"></group-analytics>
        </div>
        
        <!-- Communication Tab -->
        <div v-if="activeTab === 'communication'" class="p-6">
          <group-communication :group-id="$route.params.id"></group-communication>
        </div>
      </div>
    </div>

    <!-- Add Member Modal -->
    <div v-if="showAddMemberModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Member to Group</h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
          <div class="relative">
            <input
              type="text"
              v-model="memberSearch"
              placeholder="Search for a member..."
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              @input="searchMembers"
            />
            <div v-if="filteredMembers.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300 max-h-60 overflow-auto">
              <div 
                v-for="member in filteredMembers" 
                :key="member.id" 
                class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                @click="selectMember(member)"
              >
                {{ member.first_name }} {{ member.last_name }}
              </div>
            </div>
          </div>
          <div v-if="selectedMember" class="mt-2 flex items-center">
            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-2">
              <span>{{ getInitials(selectedMember.first_name, selectedMember.last_name) }}</span>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium">{{ selectedMember.first_name }} {{ selectedMember.last_name }}</p>
            </div>
            <button type="button" @click="clearSelectedMember" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select
            v-model="newMember.role"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
          >
            <option value="member">Member</option>
            <option value="leader">Leader</option>
            <option value="assistant_leader">Assistant Leader</option>
            <option value="secretary">Secretary</option>
            <option value="treasurer">Treasurer</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Join Date</label>
          <input
            type="date"
            v-model="newMember.join_date"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea
            v-model="newMember.notes"
            rows="2"
            class="w-full border border-gray-300 rounded-md px-3 py-2"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3">
          <button 
            @click="cancelAddMember" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="addMember" 
            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
            :disabled="!selectedMember || addingMember"
          >
            {{ addingMember ? 'Adding...' : 'Add Member' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-gray-500 mb-6">
          Are you sure you want to delete the group "{{ group.name }}"? This action cannot be undone.
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

    <!-- Remove Member Confirmation Modal -->
    <div v-if="showRemoveMemberModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Remove Member</h3>
        <p class="text-gray-500 mb-6">
          Are you sure you want to remove {{ memberToRemove?.member.first_name }} {{ memberToRemove?.member.last_name }} from this group?
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="cancelRemoveMember" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="removeMember" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700"
            :disabled="removingMember"
          >
            {{ removingMember ? 'Removing...' : 'Remove' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import GroupMemberRoles from '../../components/group/GroupMemberRoles.vue';
import GroupAnalytics from '../../components/group/GroupAnalytics.vue';
import GroupCommunication from '../../components/group/GroupCommunication.vue';

export default {
  name: 'GroupView',
  components: {
    GroupMemberRoles,
    GroupAnalytics,
    GroupCommunication
  },
  data() {
    return {
      group: {},
      members: [],
      availableMembers: [],
      filteredMembers: [],
      loading: true,
      error: null,
      activeTab: 'details',
      tabs: [
        { id: 'details', name: 'Details' },
        { id: 'members', name: 'Members' },
        { id: 'roles', name: 'Member Roles' },
        { id: 'events', name: 'Events' },
        { id: 'attendance', name: 'Attendance' },
        { id: 'analytics', name: 'Analytics' },
        { id: 'communication', name: 'Communication' }
      ],
      showDeleteModal: false,
      deleting: false,
      showAddMemberModal: false,
      memberSearch: '',
      availableMembers: [],
      filteredMembers: [],
      selectedMember: null,
      newMember: {
        role: 'member',
        join_date: new Date().toISOString().slice(0, 10),
        notes: '',
        is_active: true
      },
      addingMember: false,
      showRemoveMemberModal: false,
      memberToRemove: null,
      removingMember: false,
      searchTimeout: null
    };
  },
  created() {
    this.fetchGroup();
  },
  methods: {
    async fetchGroup() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/groups/${this.$route.params.id}`);
        
        if (response.data.status === 'success') {
          this.group = response.data.data;
          
          if (this.activeTab === 'members') {
            this.fetchMembers();
          }
        } else {
          this.error = 'Failed to load group';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching group';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchMembers() {
      this.loadingMembers = true;
      
      try {
        const response = await axios.get(`/api/groups/${this.$route.params.id}/members`);
        
        if (response.data.status === 'success') {
          this.members = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching members:', error);
      } finally {
        this.loadingMembers = false;
      }
      
      // Also fetch available members for adding
      this.fetchAvailableMembers();
    },
    
    async fetchAvailableMembers() {
      try {
        const response = await axios.get('/api/members', {
          params: {
            per_page: 100,
            status: 'active'
          }
        });
        
        if (response.data.status === 'success') {
          this.availableMembers = response.data.data.data;
        }
      } catch (error) {
        console.error('Error fetching available members:', error);
      }
    },
    
    searchMembers() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        if (!this.memberSearch) {
          this.filteredMembers = [];
          return;
        }
        
        const search = this.memberSearch.toLowerCase();
        this.filteredMembers = this.availableMembers.filter(member => {
          const fullName = `${member.first_name} ${member.last_name}`.toLowerCase();
          return fullName.includes(search);
        }).slice(0, 10); // Limit to 10 results
      }, 300);
    },
    
    selectMember(member) {
      this.selectedMember = member;
      this.memberSearch = `${member.first_name} ${member.last_name}`;
      this.filteredMembers = [];
    },
    
    clearSelectedMember() {
      this.selectedMember = null;
      this.memberSearch = '';
    },
    
    async addMember() {
      if (!this.selectedMember) return;
      
      this.addingMember = true;
      
      try {
        const data = {
          member_id: this.selectedMember.id,
          role: this.newMember.role,
          join_date: this.newMember.join_date,
          notes: this.newMember.notes,
          is_active: true
        };
        
        const response = await axios.post(`/api/groups/${this.$route.params.id}/members`, data);
        
        if (response.data.status === 'success') {
          // Refresh members list
          this.fetchMembers();
          this.showAddMemberModal = false;
          this.resetNewMemberForm();
        }
      } catch (error) {
        console.error('Error adding member:', error);
      } finally {
        this.addingMember = false;
      }
    },
    
    resetNewMemberForm() {
      this.selectedMember = null;
      this.memberSearch = '';
      this.newMember = {
        role: 'member',
        join_date: new Date().toISOString().slice(0, 10),
        notes: '',
        is_active: true
      };
    },
    
    cancelAddMember() {
      this.showAddMemberModal = false;
      this.resetNewMemberForm();
    },
    
    editMember(member) {
      // Implementation for editing member role/status would go here
      console.log('Edit member:', member);
    },
    
    confirmRemoveMember(member) {
      this.memberToRemove = member;
      this.showRemoveMemberModal = true;
    },
    
    cancelRemoveMember() {
      this.memberToRemove = null;
      this.showRemoveMemberModal = false;
    },
    
    async removeMember() {
      if (!this.memberToRemove) return;
      
      this.removingMember = true;
      
      try {
        const response = await axios.delete(`/api/groups/${this.$route.params.id}/members/${this.memberToRemove.member_id}`);
        
        if (response.data.status === 'success') {
          // Remove from local list
          this.members = this.members.filter(m => m.member_id !== this.memberToRemove.member_id);
          this.showRemoveMemberModal = false;
          this.memberToRemove = null;
        }
      } catch (error) {
        console.error('Error removing member:', error);
      } finally {
        this.removingMember = false;
      }
    },
    
    confirmDelete() {
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
    },
    
    async deleteGroup() {
      this.deleting = true;
      
      try {
        const response = await axios.delete(`/api/groups/${this.$route.params.id}`);
        
        if (response.data.status === 'success') {
          // Redirect to groups list
          this.$router.push({
            path: '/groups',
            query: { 
              message: 'Group deleted successfully',
              type: 'success'
            }
          });
        }
      } catch (error) {
        console.error('Error deleting group:', error);
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
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    
    formatTime(timeString) {
      if (!timeString) return null;
      return timeString;
    }
  }
};
</script>
