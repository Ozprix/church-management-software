<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Edit Group</h1>
      <router-link 
        :to="`/groups/${$route.params.id}`" 
        class="text-blue-600 hover:text-blue-800 font-medium flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i> Back to Group
      </router-link>
    </div>

    <!-- Alert Messages -->
    <div v-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      {{ error }}
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Form -->
    <div v-else class="bg-white shadow rounded-lg overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="updateGroup">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Group Name -->
            <div class="col-span-2">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-600">*</span></label>
              <input
                id="name"
                v-model="group.name"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.name }"
                required
              />
              <p v-if="validationErrors.name" class="mt-1 text-sm text-red-600">{{ validationErrors.name[0] }}</p>
            </div>

            <!-- Group Type -->
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Group Type <span class="text-red-600">*</span></label>
              <select
                id="type"
                v-model="group.type"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.type }"
                required
              >
                <option value="" disabled>Select a type</option>
                <option value="ministry">Ministry</option>
                <option value="committee">Committee</option>
                <option value="small_group">Small Group</option>
                <option value="other">Other</option>
              </select>
              <p v-if="validationErrors.type" class="mt-1 text-sm text-red-600">{{ validationErrors.type[0] }}</p>
            </div>

            <!-- Group Leader -->
            <div>
              <label for="leader_id" class="block text-sm font-medium text-gray-700 mb-1">Group Leader</label>
              <div class="relative">
                <input
                  type="text"
                  v-model="leaderSearch"
                  placeholder="Search for a member..."
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.leader_id }"
                  @focus="showLeaderResults = true"
                  @input="searchLeaders"
                />
                <div v-if="showLeaderResults && filteredLeaders.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300 max-h-60 overflow-auto">
                  <div 
                    v-for="leader in filteredLeaders" 
                    :key="leader.id" 
                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                    @click="selectLeader(leader)"
                  >
                    {{ leader.first_name }} {{ leader.last_name }}
                  </div>
                </div>
              </div>
              <div v-if="selectedLeader" class="mt-2 flex items-center">
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-2">
                  <span>{{ getInitials(selectedLeader.first_name, selectedLeader.last_name) }}</span>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium">{{ selectedLeader.first_name }} {{ selectedLeader.last_name }}</p>
                </div>
                <button type="button" @click="clearSelectedLeader" class="text-gray-400 hover:text-gray-600">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <p v-if="validationErrors.leader_id" class="mt-1 text-sm text-red-600">{{ validationErrors.leader_id[0] }}</p>
            </div>

            <!-- Meeting Day -->
            <div>
              <label for="meeting_day" class="block text-sm font-medium text-gray-700 mb-1">Meeting Day</label>
              <select
                id="meeting_day"
                v-model="group.meeting_day"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.meeting_day }"
              >
                <option value="">Select a day</option>
                <option value="Sunday">Sunday</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Varies">Varies</option>
              </select>
              <p v-if="validationErrors.meeting_day" class="mt-1 text-sm text-red-600">{{ validationErrors.meeting_day[0] }}</p>
            </div>

            <!-- Meeting Time -->
            <div>
              <label for="meeting_time" class="block text-sm font-medium text-gray-700 mb-1">Meeting Time</label>
              <input
                id="meeting_time"
                v-model="group.meeting_time"
                type="time"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.meeting_time }"
              />
              <p v-if="validationErrors.meeting_time" class="mt-1 text-sm text-red-600">{{ validationErrors.meeting_time[0] }}</p>
            </div>

            <!-- Meeting Location -->
            <div>
              <label for="meeting_location" class="block text-sm font-medium text-gray-700 mb-1">Meeting Location</label>
              <input
                id="meeting_location"
                v-model="group.meeting_location"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.meeting_location }"
              />
              <p v-if="validationErrors.meeting_location" class="mt-1 text-sm text-red-600">{{ validationErrors.meeting_location[0] }}</p>
            </div>

            <!-- Status -->
            <div>
              <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <div class="flex items-center mt-2">
                <input
                  id="is_active"
                  v-model="group.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
              </div>
              <p v-if="validationErrors.is_active" class="mt-1 text-sm text-red-600">{{ validationErrors.is_active[0] }}</p>
            </div>

            <!-- Description -->
            <div class="col-span-2">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                id="description"
                v-model="group.description"
                rows="4"
                class="w-full border border-gray-300 rounded-md px-3 py-2"
                :class="{ 'border-red-500': validationErrors.description }"
              ></textarea>
              <p v-if="validationErrors.description" class="mt-1 text-sm text-red-600">{{ validationErrors.description[0] }}</p>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <router-link
              :to="`/groups/${$route.params.id}`"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 mr-3"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
              :disabled="saving"
            >
              {{ saving ? 'Saving...' : 'Update Group' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GroupEdit',
  data() {
    return {
      group: {
        id: null,
        name: '',
        description: '',
        type: '',
        leader_id: '',
        meeting_day: '',
        meeting_time: '',
        meeting_location: '',
        is_active: true
      },
      leaderSearch: '',
      leaders: [],
      filteredLeaders: [],
      selectedLeader: null,
      showLeaderResults: false,
      loading: true,
      saving: false,
      error: null,
      validationErrors: {},
      searchTimeout: null
    };
  },
  created() {
    document.addEventListener('click', this.hideLeaderResults);
    this.fetchGroup();
    this.fetchMembers();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.hideLeaderResults);
  },
  methods: {
    async fetchGroup() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/groups/${this.$route.params.id}`);
        
        if (response.data.status === 'success') {
          this.group = response.data.data;
          
          // Set leader search if leader exists
          if (this.group.leader) {
            this.selectedLeader = this.group.leader;
            this.leaderSearch = `${this.group.leader.first_name} ${this.group.leader.last_name}`;
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
      try {
        const response = await axios.get('/api/members', {
          params: {
            per_page: 100,
            status: 'active'
          }
        });
        
        if (response.data.status === 'success') {
          this.leaders = response.data.data.data;
        }
      } catch (error) {
        console.error('Error fetching members:', error);
      }
    },
    searchLeaders() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        if (!this.leaderSearch) {
          this.filteredLeaders = [];
          return;
        }
        
        const search = this.leaderSearch.toLowerCase();
        this.filteredLeaders = this.leaders.filter(leader => {
          const fullName = `${leader.first_name} ${leader.last_name}`.toLowerCase();
          return fullName.includes(search);
        }).slice(0, 10); // Limit to 10 results
      }, 300);
    },
    selectLeader(leader) {
      this.selectedLeader = leader;
      this.group.leader_id = leader.id;
      this.leaderSearch = `${leader.first_name} ${leader.last_name}`;
      this.showLeaderResults = false;
    },
    clearSelectedLeader() {
      this.selectedLeader = null;
      this.group.leader_id = '';
      this.leaderSearch = '';
    },
    hideLeaderResults(event) {
      if (!event.target.closest('.relative')) {
        this.showLeaderResults = false;
      }
    },
    getInitials(firstName, lastName) {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`;
    },
    async updateGroup() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        const response = await axios.put(`/api/groups/${this.$route.params.id}`, this.group);
        
        if (response.data.status === 'success') {
          // Redirect to the group view with a success message
          this.$router.push({
            path: `/groups/${this.$route.params.id}`,
            query: { 
              message: 'Group updated successfully',
              type: 'success'
            }
          });
        }
      } catch (error) {
        if (error.response?.status === 422) {
          // Validation errors
          this.validationErrors = error.response.data.errors || {};
          this.error = 'Please correct the errors in the form.';
        } else {
          this.error = error.response?.data?.message || 'An error occurred while updating the group.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
