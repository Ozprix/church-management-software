<template>
  <div class="attendance-tracker">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 mb-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-white">Attendance Tracker</h2>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">Track attendance for events and services</p>
        </div>
        
        <div class="flex space-x-2">
          <button 
            v-if="!currentSession.isActive" 
            @click="startSession" 
            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Start Check-in
          </button>
          
          <button 
            v-else 
            @click="endSession" 
            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            End Check-in
          </button>
          
          <button 
            @click="showReports = !showReports" 
            class="inline-flex items-center px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            {{ showReports ? 'Hide Reports' : 'Show Reports' }}
          </button>
        </div>
      </div>
      
      <!-- Active Session Info -->
      <div v-if="currentSession.isActive" class="mt-4 p-3 bg-green-50 dark:bg-green-900 dark:bg-opacity-20 border border-green-200 dark:border-green-800 rounded-md">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Active Check-in Session</h3>
            <div class="mt-2 text-sm text-green-700 dark:text-green-400">
              <p>Event: {{ currentEventName }}</p>
              <p>Started: {{ formatTime(currentSession.startTime) }}</p>
              <p>Checked in: {{ currentSession.checkedInCount }} people</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Check-in Form -->
    <div v-if="currentSession.isActive" class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4 mb-6">
      <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Check-in</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Member Check-in -->
        <div class="border-r border-neutral-200 dark:border-neutral-700 pr-6">
          <h4 class="text-sm font-medium text-neutral-800 dark:text-white mb-2">Member Check-in</h4>
          
          <div class="mb-4">
            <label for="member-search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Search Member</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input 
                type="text" 
                id="member-search" 
                v-model="memberSearch" 
                class="block w-full pr-10 border-neutral-300 dark:border-neutral-600 rounded-md focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                placeholder="Search by name or ID"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Member List -->
          <div class="mt-2 max-h-60 overflow-y-auto border border-neutral-200 dark:border-neutral-700 rounded-md">
            <div v-if="filteredMembers.length === 0" class="p-4 text-sm text-neutral-500 dark:text-neutral-400 text-center">
              No members found
            </div>
            <ul v-else class="divide-y divide-neutral-200 dark:divide-neutral-700">
              <li 
                v-for="member in filteredMembers" 
                :key="member.id" 
                class="p-3 flex justify-between items-center hover:bg-neutral-50 dark:hover:bg-neutral-700 cursor-pointer"
                @click="checkInMember(member)"
              >
                <div>
                  <div class="text-sm font-medium text-neutral-800 dark:text-white">{{ member.name }}</div>
                  <div class="text-xs text-neutral-500 dark:text-neutral-400">ID: {{ member.id }}</div>
                </div>
                <button 
                  class="inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                  Check in
                </button>
              </li>
            </ul>
          </div>
        </div>
        
        <!-- Guest Check-in -->
        <div>
          <h4 class="text-sm font-medium text-neutral-800 dark:text-white mb-2">Guest Check-in</h4>
          
          <div class="mb-4">
            <label for="guest-count" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Number of Guests</label>
            <div class="mt-1 flex rounded-md shadow-sm">
              <input 
                type="number" 
                id="guest-count" 
                v-model="guestCount" 
                min="1" 
                class="block w-full border-neutral-300 dark:border-neutral-600 rounded-md focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
              />
              <button 
                @click="checkInGuests" 
                class="ml-3 inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Check in Guests
              </button>
            </div>
          </div>
          
          <!-- Categories -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Categories</label>
            <div class="space-y-2">
              <div v-for="category in settings.defaultCategories" :key="category" class="flex items-center">
                <input 
                  :id="'category-' + category" 
                  type="number" 
                  v-model="categories[category]" 
                  min="0" 
                  class="w-20 border-neutral-300 dark:border-neutral-600 rounded-md focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                />
                <label :for="'category-' + category" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                  {{ formatCategoryName(category) }}
                </label>
              </div>
            </div>
          </div>
          
          <!-- Notes -->
          <div>
            <label for="attendance-notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Notes</label>
            <textarea 
              id="attendance-notes" 
              v-model="notes" 
              rows="3" 
              class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
              placeholder="Add any notes about today's attendance"
            ></textarea>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Reports Section -->
    <div v-if="showReports" class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
      <h3 class="text-lg font-medium text-neutral-800 dark:text-white mb-4">Attendance Reports</h3>
      
      <!-- Report Tabs -->
      <div class="border-b border-neutral-200 dark:border-neutral-700">
        <nav class="-mb-px flex space-x-8">
          <button 
            @click="activeTab = 'recent'" 
            class="py-2 px-1 border-b-2 font-medium text-sm" 
            :class="activeTab === 'recent' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
          >
            Recent Records
          </button>
          <button 
            @click="activeTab = 'trends'" 
            class="py-2 px-1 border-b-2 font-medium text-sm" 
            :class="activeTab === 'trends' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
          >
            Trends
          </button>
          <button 
            @click="activeTab = 'members'" 
            class="py-2 px-1 border-b-2 font-medium text-sm" 
            :class="activeTab === 'members' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
          >
            Member Attendance
          </button>
        </nav>
      </div>
      
      <!-- Tab Content -->
      <div class="mt-4">
        <!-- Recent Records Tab -->
        <div v-if="activeTab === 'recent'" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Event</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Total</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Members</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Guests</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
              <tr v-for="record in latestRecords" :key="record.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-white">{{ formatDate(record.date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-white">{{ getEventName(record.eventId) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-white">{{ record.totalCount }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-white">{{ record.membersPresent.length }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-white">{{ record.guestCount }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">{{ record.notes }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Trends Tab -->
        <div v-if="activeTab === 'trends'" class="space-y-6">
          <div>
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-sm font-medium text-neutral-800 dark:text-white">Weekly Attendance</h4>
              <div class="flex space-x-2">
                <button 
                  @click="trendPeriod = 'weekly'" 
                  class="px-2 py-1 text-xs rounded-md" 
                  :class="trendPeriod === 'weekly' ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200'"
                >
                  Weekly
                </button>
                <button 
                  @click="trendPeriod = 'monthly'" 
                  class="px-2 py-1 text-xs rounded-md" 
                  :class="trendPeriod === 'monthly' ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200'"
                >
                  Monthly
                </button>
                <button 
                  @click="trendPeriod = 'yearly'" 
                  class="px-2 py-1 text-xs rounded-md" 
                  :class="trendPeriod === 'yearly' ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200'"
                >
                  Yearly
                </button>
              </div>
            </div>
            
            <div class="h-64 bg-neutral-50 dark:bg-neutral-900 rounded-md p-4">
              <!-- Placeholder for chart - would use Chart.js in real implementation -->
              <div class="h-full flex items-center justify-center">
                <p class="text-neutral-500 dark:text-neutral-400">Attendance trend chart would be displayed here</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Member Attendance Tab -->
        <div v-if="activeTab === 'members'" class="space-y-4">
          <div class="mb-4">
            <label for="member-attendance-search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Search Member</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input 
                type="text" 
                id="member-attendance-search" 
                v-model="memberAttendanceSearch" 
                class="block w-full pr-10 border-neutral-300 dark:border-neutral-600 rounded-md focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                placeholder="Search by name or ID"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-md p-4">
            <p class="text-neutral-500 dark:text-neutral-400 text-center">Select a member to view their attendance history</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAttendanceStore } from '../../stores/attendance';
import { useEventsStore } from '../../stores/events';

// Stores
const attendanceStore = useAttendanceStore();
const eventsStore = useEventsStore();

// State
const memberSearch = ref('');
const guestCount = ref(1);
const notes = ref('');
const categories = ref({});
const showReports = ref(false);
const activeTab = ref('recent');
const trendPeriod = ref('weekly');
const memberAttendanceSearch = ref('');

// Computed properties
const currentSession = computed(() => attendanceStore.currentSession);

const currentEventName = computed(() => {
  if (!currentSession.value.eventId) return '';
  const event = eventsStore.getEventById(currentSession.value.eventId);
  return event ? event.title : '';
});

const settings = computed(() => attendanceStore.settings);

const members = computed(() => {
  // This would typically come from a members store
  // For demo purposes, we're using a hardcoded list
  return [
    { id: 'member-1', name: 'John Smith' },
    { id: 'member-2', name: 'Mary Johnson' },
    { id: 'member-3', name: 'Robert Williams' },
    { id: 'member-4', name: 'Sarah Brown' },
    { id: 'member-5', name: 'Michael Davis' }
  ];
});

const filteredMembers = computed(() => {
  if (!memberSearch.value) return members.value;
  
  const search = memberSearch.value.toLowerCase();
  return members.value.filter(member => 
    member.name.toLowerCase().includes(search) || 
    member.id.toLowerCase().includes(search)
  );
});

const latestRecords = computed(() => attendanceStore.getLatestRecords(10));

const trends = computed(() => attendanceStore.getAttendanceTrends(trendPeriod.value));

// Methods
function startSession() {
  // In a real implementation, we would show a modal to select an event
  // For demo purposes, we're using a hardcoded event ID
  const eventId = 'event-1'; // Sunday Service
  attendanceStore.startCheckInSession(eventId);
  
  // Initialize categories
  settings.value.defaultCategories.forEach(category => {
    categories.value[category] = 0;
  });
}

function endSession() {
  // Save any remaining data before ending the session
  if (notes.value) {
    // Update the record with notes
    const record = attendanceStore.getRecordsByEventId(currentSession.value.eventId)[0];
    if (record) {
      record.notes = notes.value;
      attendanceStore.updateRecord(record);
    }
  }
  
  // End the session
  attendanceStore.endCheckInSession();
  
  // Reset form
  notes.value = '';
  categories.value = {};
}

function checkInMember(member) {
  if (!currentSession.value.isActive) return;
  
  const success = attendanceStore.checkInMember(currentSession.value.eventId, member);
  
  if (success) {
    // Show success message
    // This would typically use a toast notification system
    alert(`${member.name} checked in successfully!`);
  } else {
    // Show error message
    alert(`${member.name} is already checked in.`);
  }
}

function checkInGuests() {
  if (!currentSession.value.isActive || guestCount.value <= 0) return;
  
  const success = attendanceStore.checkInGuest(currentSession.value.eventId, guestCount.value);
  
  if (success) {
    // Show success message
    alert(`${guestCount.value} guests checked in successfully!`);
    
    // Update categories if provided
    const record = attendanceStore.getRecordsByEventId(currentSession.value.eventId)[0];
    if (record) {
      record.categories = { ...record.categories };
      
      Object.keys(categories.value).forEach(category => {
        if (categories.value[category] > 0) {
          record.categories[category] = (record.categories[category] || 0) + categories.value[category];
        }
      });
      
      attendanceStore.updateRecord(record);
    }
    
    // Reset guest count
    guestCount.value = 1;
  }
}

function formatTime(isoString) {
  if (!isoString) return '';
  
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatDate(isoString) {
  if (!isoString) return '';
  
  const date = new Date(isoString);
  return date.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function formatCategoryName(category) {
  return category
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

function getEventName(eventId) {
  const event = eventsStore.getEventById(eventId);
  return event ? event.title : 'Unknown Event';
}

// Lifecycle hooks
onMounted(() => {
  // Initialize categories
  settings.value.defaultCategories.forEach(category => {
    categories.value[category] = 0;
  });
});
</script>
