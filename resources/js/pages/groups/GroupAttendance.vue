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
      <!-- Header with group info and actions -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <div class="flex items-center mb-2">
            <router-link :to="`/groups/${groupId}`" class="text-blue-600 hover:text-blue-800 mr-2">
              <i class="fas fa-arrow-left"></i>
            </router-link>
            <h1 class="text-2xl font-bold text-gray-800">{{ group.name }} - Attendance</h1>
          </div>
          <p class="text-gray-600">{{ group.description }}</p>
        </div>
        <div class="mt-4 md:mt-0">
          <button 
            @click="showAttendanceForm = true" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-plus mr-2"></i> Record Attendance
          </button>
        </div>
      </div>

      <!-- Attendance Stats -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Attendance Statistics</h2>
        
        <div class="flex flex-wrap -mx-2">
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-blue-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Total Meetings</p>
              <p class="text-2xl font-bold text-blue-600">{{ stats.total_meetings }}</p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-green-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Average Attendance</p>
              <p class="text-2xl font-bold text-green-600">{{ stats.avg_attendance }}</p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-yellow-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Average Visitors</p>
              <p class="text-2xl font-bold text-yellow-600">{{ stats.avg_visitors }}</p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-purple-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">First-Time Visitors</p>
              <p class="text-2xl font-bold text-purple-600">{{ stats.total_first_timers }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div class="flex space-x-2 mb-4">
            <button 
              v-for="period in periods" 
              :key="period.value" 
              @click="selectedPeriod = period.value; fetchAttendanceStats()"
              class="px-3 py-1 text-sm rounded-full"
              :class="selectedPeriod === period.value ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            >
              {{ period.label }}
            </button>
          </div>
          
          <div class="h-64">
            <!-- Attendance chart would go here -->
            <div v-if="Object.keys(stats.attendance_trend).length === 0" class="h-full flex items-center justify-center">
              <p class="text-gray-500">No attendance data available for the selected period</p>
            </div>
            <div v-else class="h-full">
              <!-- Chart placeholder -->
              <div class="h-full bg-gray-100 rounded-lg flex items-center justify-center">
                <p class="text-gray-500">Attendance chart would be displayed here</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-wrap items-center">
          <div class="w-full md:w-1/3 lg:w-1/4 mb-4 md:mb-0 md:pr-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <div class="flex space-x-2">
              <input 
                type="date" 
                v-model="filters.date_from" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
              >
              <input 
                type="date" 
                v-model="filters.date_to" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
              >
            </div>
          </div>
          <div class="w-full md:w-1/4 mb-4 md:mb-0 md:px-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Type</label>
            <select 
              v-model="filters.meeting_type" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="">All Types</option>
              <option value="regular">Regular</option>
              <option value="special">Special</option>
              <option value="online">Online</option>
            </select>
          </div>
          <div class="w-full md:w-1/4 mb-4 md:mb-0 md:px-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
            <select 
              v-model="filters.sort_by" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="attendance_date">Date</option>
              <option value="total_attendees">Attendance</option>
              <option value="total_visitors">Visitors</option>
            </select>
          </div>
          <div class="w-full md:w-1/4 lg:w-1/4 md:pl-2 flex items-end">
            <button 
              @click="fetchAttendances()" 
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full md:w-auto"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance List -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="loadingAttendances" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
        </div>
        
        <div v-else-if="attendances.length === 0" class="p-6 text-center">
          <p class="text-gray-500">No attendance records found for this group.</p>
          <button 
            @click="showAttendanceForm = true" 
            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <i class="fas fa-plus mr-2"></i> Record First Attendance
          </button>
        </div>
        
        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meeting Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendees</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitors</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First-Timers</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatDate(attendance.attendance_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full capitalize" 
                      :class="{
                        'bg-blue-100 text-blue-800': attendance.meeting_type === 'regular',
                        'bg-purple-100 text-purple-800': attendance.meeting_type === 'special',
                        'bg-green-100 text-green-800': attendance.meeting_type === 'online'
                      }"
                    >
                      {{ attendance.meeting_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">
                      {{ attendance.start_time ? formatTime(attendance.start_time) : 'N/A' }}
                      {{ attendance.end_time ? ' - ' + formatTime(attendance.end_time) : '' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ attendance.total_attendees }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ attendance.total_visitors }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ attendance.total_first_timers }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button 
                      @click="viewAttendance(attendance.id)" 
                      class="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      View
                    </button>
                    <button 
                      @click="editAttendance(attendance.id)" 
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Edit
                    </button>
                    <button 
                      @click="confirmDelete(attendance.id)" 
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Form Modal -->
    <div v-if="showAttendanceForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Record Attendance</h3>
          <button @click="showAttendanceForm = false" class="text-gray-400 hover:text-gray-500">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <p class="text-gray-600 mb-4">This form will be implemented in GroupAttendanceForm.vue</p>
        
        <div class="mt-4 flex justify-end">
          <button 
            @click="showAttendanceForm = false" 
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2"
          >
            Cancel
          </button>
          <button 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            Save Attendance
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Attendance Record</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete this attendance record? This action cannot be undone.
            </p>
          </div>
          <div class="mt-4 flex justify-center space-x-4">
            <button 
              @click="showDeleteModal = false" 
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
            >
              Cancel
            </button>
            <button 
              @click="deleteAttendance" 
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
  </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'GroupAttendance',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const groupId = route.params.id;
    
    const group = ref({});
    const attendances = ref([]);
    const stats = ref({
      total_meetings: 0,
      avg_attendance: 0,
      avg_visitors: 0,
      total_first_timers: 0,
      attendance_trend: {}
    });
    
    const loading = ref(true);
    const loadingAttendances = ref(false);
    const error = ref(null);
    
    const showAttendanceForm = ref(false);
    const showDeleteModal = ref(false);
    const deleting = ref(false);
    const attendanceToDelete = ref(null);
    
    const selectedPeriod = ref('3months');
    const periods = [
      { value: '1month', label: '1 Month' },
      { value: '3months', label: '3 Months' },
      { value: '6months', label: '6 Months' },
      { value: '1year', label: '1 Year' }
    ];
    
    const filters = ref({
      date_from: '',
      date_to: '',
      meeting_type: '',
      sort_by: 'attendance_date',
      sort_dir: 'desc'
    });
    
    // Fetch group details
    const fetchGroup = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        const response = await axios.get(`/api/groups/${groupId}`);
        
        if (response.data.status === 'success') {
          group.value = response.data.data;
        } else {
          error.value = 'Failed to load group details';
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching group details';
      } finally {
        loading.value = false;
      }
    };
    
    // Fetch attendance records
    const fetchAttendances = async () => {
      loadingAttendances.value = true;
      
      try {
        const response = await axios.get(`/api/groups/${groupId}/attendances`, {
          params: filters.value
        });
        
        if (response.data.status === 'success') {
          attendances.value = response.data.data;
        } else {
          attendances.value = [];
        }
      } catch (err) {
        console.error('Error fetching attendances:', err);
        attendances.value = [];
      } finally {
        loadingAttendances.value = false;
      }
    };
    
    // Fetch attendance statistics
    const fetchAttendanceStats = async () => {
      try {
        const response = await axios.get(`/api/groups/${groupId}/attendance-stats`, {
          params: { period: selectedPeriod.value }
        });
        
        if (response.data.status === 'success') {
          stats.value = response.data.data;
        }
      } catch (err) {
        console.error('Error fetching attendance stats:', err);
      }
    };
    
    // View attendance details
    const viewAttendance = (attendanceId) => {
      router.push(`/groups/${groupId}/attendances/${attendanceId}`);
    };
    
    // Edit attendance
    const editAttendance = (attendanceId) => {
      router.push(`/groups/${groupId}/attendances/${attendanceId}/edit`);
    };
    
    // Confirm delete
    const confirmDelete = (attendanceId) => {
      attendanceToDelete.value = attendanceId;
      showDeleteModal.value = true;
    };
    
    // Delete attendance
    const deleteAttendance = async () => {
      if (!attendanceToDelete.value) return;
      
      deleting.value = true;
      
      try {
        const response = await axios.delete(`/api/group-attendances/${attendanceToDelete.value}`);
        
        if (response.data.status === 'success') {
          showDeleteModal.value = false;
          fetchAttendances();
          fetchAttendanceStats();
        } else {
          error.value = 'Failed to delete attendance record';
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while deleting the attendance record';
      } finally {
        deleting.value = false;
      }
    };
    
    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    };
    
    // Format time
    const formatTime = (timeString) => {
      if (!timeString) return '';
      
      // Extract hours and minutes from time string (HH:MM:SS)
      const [hours, minutes] = timeString.split(':');
      
      // Create a date object and set the hours and minutes
      const date = new Date();
      date.setHours(hours);
      date.setMinutes(minutes);
      
      // Format the time
      return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
      }).format(date);
    };
    
    // Watch for changes in the selected period
    watch(selectedPeriod, () => {
      fetchAttendanceStats();
    });
    
    onMounted(() => {
      fetchGroup();
      fetchAttendances();
      fetchAttendanceStats();
    });
    
    return {
      groupId,
      group,
      attendances,
      stats,
      loading,
      loadingAttendances,
      error,
      showAttendanceForm,
      showDeleteModal,
      deleting,
      selectedPeriod,
      periods,
      filters,
      fetchAttendances,
      fetchAttendanceStats,
      viewAttendance,
      editAttendance,
      confirmDelete,
      deleteAttendance,
      formatDate,
      formatTime
    };
  }
};
</script>
