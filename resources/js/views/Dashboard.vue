<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-800 mb-6">Church Management Dashboard</h1>
          
          <!-- Quick Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50 p-6 rounded-lg shadow">
              <h3 class="text-lg font-semibold text-blue-800">Members</h3>
              <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-bold">{{ stats.members }}</span>
                <span class="ml-2 text-sm text-blue-600">registered</span>
              </div>
            </div>
            
            <div class="bg-green-50 p-6 rounded-lg shadow">
              <h3 class="text-lg font-semibold text-green-800">Donations</h3>
              <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-bold">${{ stats.donations.toLocaleString() }}</span>
                <span class="ml-2 text-sm text-green-600">this month</span>
              </div>
            </div>
            
            <div class="bg-purple-50 p-6 rounded-lg shadow">
              <h3 class="text-lg font-semibold text-purple-800">Events</h3>
              <div class="mt-2 flex items-baseline">
                <span class="text-3xl font-bold">{{ stats.events }}</span>
                <span class="ml-2 text-sm text-purple-600">upcoming</span>
              </div>
            </div>
          </div>
          
          <!-- Financial Management Section -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Financial Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <router-link to="/finance/donations" class="bg-white p-4 border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                  <div class="rounded-full bg-green-100 p-3 mr-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-800">Donations</h3>
                    <p class="text-sm text-gray-500">Manage all donations and gifts</p>
                  </div>
                </div>
              </router-link>
              
              <router-link to="/finance/donation-categories" class="bg-white p-4 border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                  <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-800">Categories</h3>
                    <p class="text-sm text-gray-500">Manage donation categories</p>
                  </div>
                </div>
              </router-link>
              
              <router-link to="/finance/projects" class="bg-white p-4 border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center">
                  <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-800">Projects</h3>
                    <p class="text-sm text-gray-500">Manage fundraising projects</p>
                  </div>
                </div>
              </router-link>
            </div>
          </div>
          
          <!-- Recent Activity -->
          <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Activity</h2>
            <div class="bg-gray-50 rounded-lg p-4">
              <div v-if="recentActivity.length > 0">
                <div v-for="(activity, index) in recentActivity" :key="index" class="mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
                  <div class="flex items-start">
                    <div class="rounded-full bg-gray-200 p-2 mr-3">
                      <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-gray-800">{{ activity.description }}</p>
                      <p class="text-xs text-gray-500">{{ activity.time }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                No recent activity to display
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'Dashboard',
  
  setup() {
    const stats = ref({
      members: 0,
      donations: 0,
      events: 0
    });
    
    const recentActivity = ref([]);
    const loading = ref(true);
    const error = ref(null);
    
    const fetchDashboardData = async () => {
      try {
        loading.value = true;
        const response = await axios.get('/api/dashboard');
        
        if (response.data) {
          stats.value = response.data.stats || { members: 0, donations: 0, events: 0 };
          recentActivity.value = response.data.recentActivity || [];
        }
      } catch (err) {
        console.error('Error fetching dashboard data:', err);
        error.value = 'Failed to load dashboard data';
        
        // Set some dummy data for demonstration
        stats.value = {
          members: 125,
          donations: 15750,
          events: 3
        };
        
        recentActivity.value = [
          { description: 'New donation of $250 received from John Doe', time: '2 hours ago' },
          { description: 'New project "Church Renovation" created', time: '1 day ago' },
          { description: 'New donation category "Missions" added', time: '2 days ago' }
        ];
      } finally {
        loading.value = false;
      }
    };
    
    onMounted(() => {
      fetchDashboardData();
    });
    
    return {
      stats,
      recentActivity,
      loading,
      error
    };
  }
};
</script>
