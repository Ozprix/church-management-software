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
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <div class="flex items-center mb-2">
            <router-link :to="`/groups/${groupId}/attendances`" class="text-blue-600 hover:text-blue-800 mr-2">
              <i class="fas fa-arrow-left"></i>
            </router-link>
            <h1 class="text-2xl font-bold text-gray-800">
              {{ isEditing ? 'Edit Attendance' : 'Record Attendance' }}
            </h1>
          </div>
          <p class="text-gray-600">{{ group.name }}</p>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form @submit.prevent="submitForm">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Meeting Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Date -->
              <div>
                <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Meeting Date <span class="text-red-600">*</span>
                </label>
                <input
                  id="attendance_date"
                  v-model="formData.attendance_date"
                  type="date"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.attendance_date }"
                  required
                  :disabled="isEditing"
                />
                <p v-if="validationErrors.attendance_date" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.attendance_date[0] }}
                </p>
              </div>

              <!-- Meeting Type -->
              <div>
                <label for="meeting_type" class="block text-sm font-medium text-gray-700 mb-1">
                  Meeting Type
                </label>
                <select
                  id="meeting_type"
                  v-model="formData.meeting_type"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.meeting_type }"
                >
                  <option value="regular">Regular</option>
                  <option value="special">Special</option>
                  <option value="online">Online</option>
                </select>
                <p v-if="validationErrors.meeting_type" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.meeting_type[0] }}
                </p>
              </div>

              <!-- Start Time -->
              <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                  Start Time
                </label>
                <input
                  id="start_time"
                  v-model="formData.start_time"
                  type="time"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.start_time }"
                />
                <p v-if="validationErrors.start_time" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.start_time[0] }}
                </p>
              </div>

              <!-- End Time -->
              <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                  End Time
                </label>
                <input
                  id="end_time"
                  v-model="formData.end_time"
                  type="time"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.end_time }"
                />
                <p v-if="validationErrors.end_time" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.end_time[0] }}
                </p>
              </div>

              <!-- Notes -->
              <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                  Notes
                </label>
                <textarea
                  id="notes"
                  v-model="formData.notes"
                  rows="2"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                  :class="{ 'border-red-500': validationErrors.notes }"
                ></textarea>
                <p v-if="validationErrors.notes" class="mt-1 text-sm text-red-600">
                  {{ validationErrors.notes[0] }}
                </p>
              </div>
            </div>
          </div>

          <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold text-gray-800">Member Attendance</h2>
              <div class="flex items-center space-x-2">
                <button
                  type="button"
                  @click="markAllPresent"
                  class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-1 px-3 rounded"
                >
                  Mark All Present
                </button>
                <button
                  type="button"
                  @click="markAllAbsent"
                  class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1 px-3 rounded"
                >
                  Mark All Absent
                </button>
              </div>
            </div>

            <!-- Search and filter -->
            <div class="mb-4">
              <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                <div class="flex-1">
                  <input
                    type="text"
                    v-model="memberSearch"
                    placeholder="Search members..."
                    class="w-full border border-gray-300 rounded-md px-3 py-2"
                    @input="filterMembers"
                  />
                </div>
                <div>
                  <select
                    v-model="memberFilter"
                    class="w-full md:w-auto border border-gray-300 rounded-md px-3 py-2"
                    @change="filterMembers"
                  >
                    <option value="all">All Members</option>
                    <option value="active">Active Members</option>
                    <option value="leaders">Leaders</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Members list -->
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
                  <tr v-if="filteredMembers.length === 0">
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                      No members found.
                    </td>
                  </tr>
                  <tr v-for="(member, index) in filteredMembers" :key="member.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                          <span>{{ getInitials(member.first_name, member.last_name) }}</span>
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            {{ member.first_name }} {{ member.last_name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ member.phone || member.email || 'No contact info' }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs rounded-full capitalize" 
                        :class="{
                          'bg-green-100 text-green-800': member.pivot.role === 'leader',
                          'bg-blue-100 text-blue-800': member.pivot.role === 'member',
                          'bg-purple-100 text-purple-800': member.pivot.role === 'assistant',
                          'bg-yellow-100 text-yellow-800': member.pivot.role === 'volunteer'
                        }"
                      >
                        {{ member.pivot.role }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <select
                        v-model="memberAttendance[index].attendance_status"
                        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
                        :class="{
                          'bg-green-50 border-green-300': memberAttendance[index].attendance_status === 'present',
                          'bg-red-50 border-red-300': memberAttendance[index].attendance_status === 'absent',
                          'bg-yellow-50 border-yellow-300': memberAttendance[index].attendance_status === 'excused'
                        }"
                      >
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="excused">Excused</option>
                      </select>
                    </td>
                    <td class="px-6 py-4">
                      <input
                        type="text"
                        v-model="memberAttendance[index].notes"
                        placeholder="Add notes..."
                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold text-gray-800">Visitors</h2>
              <button
                type="button"
                @click="addVisitor"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded flex items-center"
              >
                <i class="fas fa-plus mr-1"></i> Add Visitor
              </button>
            </div>

            <!-- Visitors list -->
            <div v-if="visitors.length === 0" class="text-center py-4 text-gray-500">
              No visitors recorded for this meeting.
            </div>
            
            <div v-else class="space-y-4">
              <div v-for="(visitor, index) in visitors" :key="index" class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between">
                  <h3 class="font-medium text-gray-900">Visitor {{ index + 1 }}</h3>
                  <button
                    type="button"
                    @click="removeVisitor(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Name <span class="text-red-600">*</span>
                    </label>
                    <input
                      v-model="visitor.visitor_name"
                      type="text"
                      class="w-full border border-gray-300 rounded-md px-3 py-2"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Phone
                    </label>
                    <input
                      v-model="visitor.visitor_phone"
                      type="text"
                      class="w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Email
                    </label>
                    <input
                      v-model="visitor.visitor_email"
                      type="email"
                      class="w-full border border-gray-300 rounded-md px-3 py-2"
                    />
                  </div>
                </div>
                
                <div class="mt-3 flex items-center">
                  <input
                    :id="`first_time_${index}`"
                    v-model="visitor.is_first_time"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                  />
                  <label :for="`first_time_${index}`" class="ml-2 text-sm text-gray-700">
                    First-time visitor
                  </label>
                </div>
                
                <div class="mt-3">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Notes
                  </label>
                  <textarea
                    v-model="visitor.notes"
                    rows="2"
                    class="w-full border border-gray-300 rounded-md px-3 py-2"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 flex justify-end">
            <router-link
              :to="`/groups/${groupId}/attendances`"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              :disabled="saving"
            >
              <span v-if="saving">Saving...</span>
              <span v-else>{{ isEditing ? 'Update Attendance' : 'Save Attendance' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'GroupAttendanceForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const groupId = route.params.id;
    const attendanceId = route.params.attendanceId;
    const isEditing = computed(() => !!attendanceId);
    
    const group = ref({});
    const members = ref([]);
    const filteredMembers = ref([]);
    const memberAttendance = ref([]);
    const visitors = ref([]);
    
    const loading = ref(true);
    const saving = ref(false);
    const error = ref(null);
    const validationErrors = ref({});
    
    const memberSearch = ref('');
    const memberFilter = ref('all');
    
    const formData = ref({
      group_id: groupId,
      attendance_date: new Date().toISOString().split('T')[0],
      start_time: '',
      end_time: '',
      meeting_type: 'regular',
      notes: ''
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
    
    // Fetch group members
    const fetchMembers = async () => {
      try {
        const response = await axios.get(`/api/groups/${groupId}/members`);
        
        if (response.data.status === 'success') {
          members.value = response.data.data;
          filteredMembers.value = [...members.value];
          
          // Initialize member attendance array
          memberAttendance.value = members.value.map(member => ({
            member_id: member.id,
            attendance_status: 'present',
            notes: ''
          }));
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching group members';
      }
    };
    
    // Fetch attendance details if editing
    const fetchAttendance = async () => {
      if (!isEditing.value) return;
      
      try {
        const response = await axios.get(`/api/group-attendances/${attendanceId}`);
        
        if (response.data.status === 'success') {
          const attendance = response.data.data;
          
          // Set form data
          formData.value = {
            group_id: groupId,
            attendance_date: attendance.attendance_date,
            start_time: attendance.start_time,
            end_time: attendance.end_time,
            meeting_type: attendance.meeting_type,
            notes: attendance.notes
          };
          
          // Set member attendance
          const details = attendance.attendance_details || [];
          
          // Process member attendance
          details.forEach(detail => {
            if (detail.member_id) {
              // Find the index of this member in the memberAttendance array
              const index = memberAttendance.value.findIndex(m => m.member_id === detail.member_id);
              
              if (index !== -1) {
                memberAttendance.value[index].attendance_status = detail.attendance_status;
                memberAttendance.value[index].notes = detail.notes;
              }
            } else if (detail.visitor_name) {
              // Add to visitors array
              visitors.value.push({
                visitor_name: detail.visitor_name,
                visitor_phone: detail.visitor_phone,
                visitor_email: detail.visitor_email,
                is_first_time: detail.is_first_time,
                notes: detail.notes,
                attendance_status: detail.attendance_status || 'present'
              });
            }
          });
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while fetching attendance details';
      }
    };
    
    // Filter members based on search and filter
    const filterMembers = () => {
      let filtered = [...members.value];
      
      // Apply search filter
      if (memberSearch.value) {
        const search = memberSearch.value.toLowerCase();
        filtered = filtered.filter(member => {
          const fullName = `${member.first_name} ${member.last_name}`.toLowerCase();
          return fullName.includes(search);
        });
      }
      
      // Apply member type filter
      if (memberFilter.value === 'active') {
        filtered = filtered.filter(member => member.pivot.is_active);
      } else if (memberFilter.value === 'leaders') {
        filtered = filtered.filter(member => member.pivot.role === 'leader');
      }
      
      filteredMembers.value = filtered;
    };
    
    // Mark all members as present
    const markAllPresent = () => {
      memberAttendance.value.forEach((attendance, index) => {
        attendance.attendance_status = 'present';
      });
    };
    
    // Mark all members as absent
    const markAllAbsent = () => {
      memberAttendance.value.forEach((attendance, index) => {
        attendance.attendance_status = 'absent';
      });
    };
    
    // Add a new visitor
    const addVisitor = () => {
      visitors.value.push({
        visitor_name: '',
        visitor_phone: '',
        visitor_email: '',
        is_first_time: false,
        notes: '',
        attendance_status: 'present'
      });
    };
    
    // Remove a visitor
    const removeVisitor = (index) => {
      visitors.value.splice(index, 1);
    };
    
    // Get initials from name
    const getInitials = (firstName, lastName) => {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`;
    };
    
    // Submit the form
    const submitForm = async () => {
      saving.value = true;
      validationErrors.value = {};
      
      try {
        // Prepare member details
        const memberDetails = memberAttendance.value.map(attendance => ({
          member_id: attendance.member_id,
          attendance_status: attendance.attendance_status,
          notes: attendance.notes
        }));
        
        // Add visitor details
        const visitorDetails = visitors.value.map(visitor => ({
          visitor_name: visitor.visitor_name,
          visitor_phone: visitor.visitor_phone,
          visitor_email: visitor.visitor_email,
          is_first_time: visitor.is_first_time,
          notes: visitor.notes,
          attendance_status: visitor.attendance_status
        }));
        
        // Combine member and visitor details
        const allDetails = [...memberDetails, ...visitorDetails];
        
        let response;
        
        if (isEditing.value) {
          // Update attendance
          response = await axios.put(`/api/group-attendances/${attendanceId}`, formData.value);
          
          // Update attendance details
          if (response.data.status === 'success') {
            await axios.post(`/api/group-attendances/${attendanceId}/details`, {
              details: allDetails
            });
          }
        } else {
          // Create new attendance with details
          response = await axios.post('/api/group-attendances', {
            ...formData.value,
            member_details: allDetails
          });
        }
        
        if (response.data.status === 'success') {
          // Redirect to attendance list
          router.push({
            path: `/groups/${groupId}/attendances`,
            query: {
              message: isEditing.value ? 'Attendance updated successfully' : 'Attendance recorded successfully',
              type: 'success'
            }
          });
        }
      } catch (err) {
        if (err.response?.status === 422) {
          validationErrors.value = err.response.data.errors || {};
          error.value = 'Please correct the errors in the form.';
        } else {
          error.value = err.response?.data?.message || 'An error occurred while saving the attendance record.';
        }
      } finally {
        saving.value = false;
      }
    };
    
    onMounted(async () => {
      loading.value = true;
      await fetchGroup();
      await fetchMembers();
      
      if (isEditing.value) {
        await fetchAttendance();
      }
      
      loading.value = false;
    });
    
    return {
      groupId,
      attendanceId,
      isEditing,
      group,
      members,
      filteredMembers,
      memberAttendance,
      visitors,
      loading,
      saving,
      error,
      validationErrors,
      memberSearch,
      memberFilter,
      formData,
      filterMembers,
      markAllPresent,
      markAllAbsent,
      addVisitor,
      removeVisitor,
      getInitials,
      submitForm
    };
  }
};
</script>
