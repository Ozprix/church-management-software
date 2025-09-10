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
      <!-- Header with attendance info and actions -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <div class="flex items-center mb-2">
            <router-link :to="`/groups/${groupId}/attendances`" class="text-blue-600 hover:text-blue-800 mr-2">
              <i class="fas fa-arrow-left"></i>
            </router-link>
            <h1 class="text-2xl font-bold text-gray-800">{{ group.name }} - Attendance Details</h1>
          </div>
          <p class="text-gray-600">{{ formatDate(attendance.attendance_date) }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
          <router-link 
            :to="`/groups/${groupId}/attendances/${attendanceId}/edit`" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-edit mr-2"></i> Edit
          </router-link>
          <button 
            @click="confirmDelete" 
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center"
          >
            <i class="fas fa-trash-alt mr-2"></i> Delete
          </button>
        </div>
      </div>

      <!-- Attendance Summary -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Meeting Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div>
            <p class="text-sm text-gray-500 mb-1">Meeting Type</p>
            <p class="text-base font-medium">
              <span class="px-2 py-1 text-xs rounded-full capitalize" 
                :class="{
                  'bg-blue-100 text-blue-800': attendance.meeting_type === 'regular',
                  'bg-purple-100 text-purple-800': attendance.meeting_type === 'special',
                  'bg-green-100 text-green-800': attendance.meeting_type === 'online'
                }"
              >
                {{ attendance.meeting_type }}
              </span>
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500 mb-1">Meeting Time</p>
            <p class="text-base font-medium">
              {{ attendance.start_time ? formatTime(attendance.start_time) : 'N/A' }}
              {{ attendance.end_time ? ' - ' + formatTime(attendance.end_time) : '' }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Attendees</p>
            <p class="text-base font-medium">{{ attendance.total_attendees }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 mb-1">Recorded By</p>
            <p class="text-base font-medium">
              {{ attendance.recorder ? attendance.recorder.name : 'Unknown' }}
            </p>
          </div>
        </div>
        
        <div v-if="attendance.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-500 mb-1">Meeting Notes</p>
          <p class="text-base">{{ attendance.notes }}</p>
        </div>
      </div>

      <!-- Attendance Statistics -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Attendance Summary</h2>
        
        <div class="flex flex-wrap -mx-2">
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-green-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Present</p>
              <p class="text-2xl font-bold text-green-600">{{ presentCount }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ Math.round((presentCount / totalMemberCount) * 100) }}% of members
              </p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-red-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Absent</p>
              <p class="text-2xl font-bold text-red-600">{{ absentCount }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ Math.round((absentCount / totalMemberCount) * 100) }}% of members
              </p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-yellow-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Excused</p>
              <p class="text-2xl font-bold text-yellow-600">{{ excusedCount }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ Math.round((excusedCount / totalMemberCount) * 100) }}% of members
              </p>
            </div>
          </div>
          <div class="w-full md:w-1/4 px-2 mb-4">
            <div class="bg-purple-50 rounded-lg p-4 h-full">
              <p class="text-sm text-gray-500 mb-1">Visitors</p>
              <p class="text-2xl font-bold text-purple-600">{{ visitorCount }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ firstTimeVisitorCount }} first-time
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Details Tabs -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
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

        <!-- Members Tab -->
        <div v-if="activeTab === 'members'" class="p-6">
          <div class="mb-4">
            <input
              type="text"
              v-model="memberSearch"
              placeholder="Search members..."
              class="w-full md:w-1/3 border border-gray-300 rounded-md px-3 py-2"
              @input="filterMembers"
            />
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Role
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Notes
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="filteredMemberDetails.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                    No members found.
                  </td>
                </tr>
                <tr v-for="detail in filteredMemberDetails" :key="detail.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                        <span>{{ getInitials(detail.member.first_name, detail.member.last_name) }}</span>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ detail.member.first_name }} {{ detail.member.last_name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ detail.member.phone || detail.member.email || 'No contact info' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full capitalize" 
                      :class="{
                        'bg-green-100 text-green-800': detail.member.pivot?.role === 'leader',
                        'bg-blue-100 text-blue-800': detail.member.pivot?.role === 'member',
                        'bg-purple-100 text-purple-800': detail.member.pivot?.role === 'assistant',
                        'bg-yellow-100 text-yellow-800': detail.member.pivot?.role === 'volunteer'
                      }"
                    >
                      {{ detail.member.pivot?.role || 'member' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full capitalize" 
                      :class="{
                        'bg-green-100 text-green-800': detail.attendance_status === 'present',
                        'bg-red-100 text-red-800': detail.attendance_status === 'absent',
                        'bg-yellow-100 text-yellow-800': detail.attendance_status === 'excused'
                      }"
                    >
                      {{ detail.attendance_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ detail.notes || 'No notes' }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Visitors Tab -->
        <div v-if="activeTab === 'visitors'" class="p-6">
          <div v-if="visitorDetails.length === 0" class="text-center py-4 text-gray-500">
            No visitors recorded for this meeting.
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="detail in visitorDetails" 
              :key="detail.id" 
              class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
            >
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="font-medium text-gray-900">{{ detail.visitor_name }}</h4>
                  <div class="mt-1 space-y-1">
                    <p v-if="detail.visitor_phone" class="text-sm text-gray-600">
                      <i class="fas fa-phone mr-2 text-gray-400"></i> {{ detail.visitor_phone }}
                    </p>
                    <p v-if="detail.visitor_email" class="text-sm text-gray-600">
                      <i class="fas fa-envelope mr-2 text-gray-400"></i> {{ detail.visitor_email }}
                    </p>
                  </div>
                </div>
                <span 
                  v-if="detail.is_first_time" 
                  class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800"
                >
                  First Time
                </span>
              </div>
              
              <div v-if="detail.notes" class="mt-3 p-3 bg-gray-50 rounded text-sm text-gray-700">
                {{ detail.notes }}
              </div>
            </div>
          </div>
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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'GroupAttendanceDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const groupId = route.params.id;
    const attendanceId = route.params.attendanceId;
    
    const group = ref({});
    const attendance = ref({});
    const attendanceDetails = ref([]);
    
    const loading = ref(true);
    const error = ref(null);
    const showDeleteModal = ref(false);
    const deleting = ref(false);
    
    const activeTab = ref('members');
    const tabs = [
      { id: 'members', name: 'Members' },
      { id: 'visitors', name: 'Visitors' }
    ];
    
    const memberSearch = ref('');
    
    // Computed properties for statistics
    const memberDetails = computed(() => {
      return attendanceDetails.value.filter(detail => detail.member_id !== null);
    });
    
    const visitorDetails = computed(() => {
      return attendanceDetails.value.filter(detail => detail.visitor_name !== null);
    });
    
    const presentCount = computed(() => {
      return memberDetails.value.filter(detail => detail.attendance_status === 'present').length;
    });
    
    const absentCount = computed(() => {
      return memberDetails.value.filter(detail => detail.attendance_status === 'absent').length;
    });
    
    const excusedCount = computed(() => {
      return memberDetails.value.filter(detail => detail.attendance_status === 'excused').length;
    });
    
    const totalMemberCount = computed(() => {
      return memberDetails.value.length;
    });
    
    const visitorCount = computed(() => {
      return visitorDetails.value.length;
    });
    
    const firstTimeVisitorCount = computed(() => {
      return visitorDetails.value.filter(detail => detail.is_first_time).length;
    });
    
    // Filtered member details based on search
    const filteredMemberDetails = computed(() => {
      if (!memberSearch.value) {
        return memberDetails.value;
      }
      
      const search = memberSearch.value.toLowerCase();
      return memberDetails.value.filter(detail => {
        const fullName = `${detail.member.first_name} ${detail.member.last_name}`.toLowerCase();
        return fullName.includes(search);
      });
    });
    
    // Fetch group details
    const fetchGroup = async () => {
      try {
        const response = await axios.get(`/api/groups/${groupId}`);
        
        if (response.data.status === 'success') {
          group.value = response.data.data;
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching group details';
      }
    };
    
    // Fetch attendance details
    const fetchAttendance = async () => {
      try {
        const response = await axios.get(`/api/group-attendances/${attendanceId}`);
        
        if (response.data.status === 'success') {
          attendance.value = response.data.data;
          attendanceDetails.value = response.data.data.attendance_details || [];
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching attendance details';
      }
    };
    
    // Filter members based on search
    const filterMembers = () => {
      // This is handled by the computed property filteredMemberDetails
    };
    
    // Confirm delete
    const confirmDelete = () => {
      showDeleteModal.value = true;
    };
    
    // Delete attendance
    const deleteAttendance = async () => {
      deleting.value = true;
      
      try {
        const response = await axios.delete(`/api/group-attendances/${attendanceId}`);
        
        if (response.data.status === 'success') {
          router.push({
            path: `/groups/${groupId}/attendances`,
            query: {
              message: 'Attendance record deleted successfully',
              type: 'success'
            }
          });
        } else {
          error.value = 'Failed to delete attendance record';
          showDeleteModal.value = false;
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while deleting the attendance record';
        showDeleteModal.value = false;
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
        month: 'long',
        day: 'numeric',
        weekday: 'long'
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
    
    // Get initials from name
    const getInitials = (firstName, lastName) => {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`;
    };
    
    onMounted(async () => {
      loading.value = true;
      await fetchGroup();
      await fetchAttendance();
      loading.value = false;
    });
    
    return {
      groupId,
      attendanceId,
      group,
      attendance,
      attendanceDetails,
      loading,
      error,
      showDeleteModal,
      deleting,
      activeTab,
      tabs,
      memberSearch,
      memberDetails,
      visitorDetails,
      presentCount,
      absentCount,
      excusedCount,
      totalMemberCount,
      visitorCount,
      firstTimeVisitorCount,
      filteredMemberDetails,
      filterMembers,
      confirmDelete,
      deleteAttendance,
      formatDate,
      formatTime,
      getInitials
    };
  }
};
</script>
