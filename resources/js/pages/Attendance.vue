<template>
  <div class="attendance-page">
    <div class="container mx-auto px-4 py-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Attendance Management</h1>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
          Track and analyze attendance for church services and events
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Attendance Stats</h2>
            
            <div class="space-y-4">
              <!-- Weekly Average -->
              <div class="p-3 rounded-md bg-neutral-50 dark:bg-neutral-700">
                <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Weekly Average</div>
                <div class="mt-1 flex items-end">
                  <div class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ weeklyAverage }}</div>
                  <div class="ml-2 text-xs font-medium" :class="weeklyTrend > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    <span v-if="weeklyTrend > 0">+{{ weeklyTrend }}%</span>
                    <span v-else>{{ weeklyTrend }}%</span>
                  </div>
                </div>
              </div>
              
              <!-- Monthly Average -->
              <div class="p-3 rounded-md bg-neutral-50 dark:bg-neutral-700">
                <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Monthly Average</div>
                <div class="mt-1 flex items-end">
                  <div class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ monthlyAverage }}</div>
                  <div class="ml-2 text-xs font-medium" :class="monthlyTrend > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    <span v-if="monthlyTrend > 0">+{{ monthlyTrend }}%</span>
                    <span v-else>{{ monthlyTrend }}%</span>
                  </div>
                </div>
              </div>
              
              <!-- Year to Date -->
              <div class="p-3 rounded-md bg-neutral-50 dark:bg-neutral-700">
                <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Year to Date</div>
                <div class="mt-1 flex items-end">
                  <div class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ yearToDate }}</div>
                  <div class="ml-2 text-xs font-medium" :class="yearlyTrend > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    <span v-if="yearlyTrend > 0">+{{ yearlyTrend }}%</span>
                    <span v-else>{{ yearlyTrend }}%</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-neutral-200 dark:border-neutral-700">
              <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-3">Quick Actions</h3>
              <div class="space-y-2">
                <button 
                  @click="exportAttendanceData" 
                  class="w-full flex items-center px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-colors duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Export Data
                </button>
                <button 
                  @click="showSettings = true" 
                  class="w-full flex items-center px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-colors duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Attendance Settings
                </button>
                <button 
                  @click="generateReport" 
                  class="w-full flex items-center px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-colors duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Generate Report
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="lg:col-span-3">
          <AttendanceTracker />
        </div>
      </div>
    </div>
    
    <!-- Settings Modal -->
    <div v-if="showSettings" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showSettings = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white" id="modal-title">
                  Attendance Settings
                </h3>
                
                <div class="mt-4 space-y-4">
                  <!-- Auto Check-in -->
                  <div class="flex items-center justify-between">
                    <label for="auto-checkin" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      Auto Check-in
                    </label>
                    <div class="relative inline-block w-10 mr-2 align-middle select-none">
                      <input 
                        type="checkbox" 
                        id="auto-checkin" 
                        v-model="settings.autoCheckIn"
                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                      />
                      <label 
                        for="auto-checkin" 
                        class="toggle-label block overflow-hidden h-6 rounded-full bg-neutral-300 dark:bg-neutral-600 cursor-pointer"
                      ></label>
                    </div>
                  </div>
                  
                  <!-- Check-in Methods -->
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                      Check-in Methods
                    </label>
                    <div class="space-y-2">
                      <div class="flex items-center">
                        <input 
                          id="method-manual" 
                          type="checkbox" 
                          value="manual" 
                          v-model="settings.checkInMethods"
                          class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                        />
                        <label for="method-manual" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                          Manual Entry
                        </label>
                      </div>
                      <div class="flex items-center">
                        <input 
                          id="method-qrcode" 
                          type="checkbox" 
                          value="qrcode" 
                          v-model="settings.checkInMethods"
                          class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                        />
                        <label for="method-qrcode" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                          QR Code Scan
                        </label>
                      </div>
                      <div class="flex items-center">
                        <input 
                          id="method-kiosk" 
                          type="checkbox" 
                          value="kiosk" 
                          v-model="settings.checkInMethods"
                          class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                        />
                        <label for="method-kiosk" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                          Kiosk Mode
                        </label>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Default Categories -->
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                      Default Categories
                    </label>
                    <div class="flex flex-wrap gap-2">
                      <div 
                        v-for="category in settings.defaultCategories" 
                        :key="category" 
                        class="flex items-center bg-neutral-100 dark:bg-neutral-700 px-2 py-1 rounded-md"
                      >
                        <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ formatCategoryName(category) }}</span>
                        <button 
                          @click="removeCategory(category)" 
                          class="ml-1 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                      <div class="flex items-center">
                        <input 
                          type="text" 
                          v-model="newCategory" 
                          placeholder="Add category" 
                          class="px-2 py-1 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                          @keyup.enter="addCategory"
                        />
                        <button 
                          @click="addCategory" 
                          class="ml-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Reminder Settings -->
                  <div>
                    <div class="flex items-center justify-between">
                      <label for="reminder-enabled" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Enable Reminders
                      </label>
                      <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input 
                          type="checkbox" 
                          id="reminder-enabled" 
                          v-model="settings.reminderEnabled"
                          class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                        />
                        <label 
                          for="reminder-enabled" 
                          class="toggle-label block overflow-hidden h-6 rounded-full bg-neutral-300 dark:bg-neutral-600 cursor-pointer"
                        ></label>
                      </div>
                    </div>
                    
                    <div v-if="settings.reminderEnabled" class="mt-2">
                      <label for="reminder-time" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Reminder Time (minutes before event)
                      </label>
                      <input 
                        type="number" 
                        id="reminder-time" 
                        v-model="settings.reminderTime" 
                        min="5" 
                        class="mt-1 block w-full border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white sm:text-sm"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="saveSettings" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Save
            </button>
            <button 
              type="button" 
              @click="showSettings = false" 
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
import { useAttendanceStore } from '../stores/attendance';
import AttendanceTracker from '../components/attendance/AttendanceTracker.vue';

// Store
const attendanceStore = useAttendanceStore();

// State
const showSettings = ref(false);
const newCategory = ref('');
const settings = ref({ ...attendanceStore.settings });

// Computed properties
const weeklyAverage = computed(() => {
  // Calculate from the weekly trends data
  const weeklyTrends = attendanceStore.getAttendanceTrends('weekly');
  if (weeklyTrends.length === 0) return 0;
  
  const sum = weeklyTrends.reduce((total, item) => total + item.count, 0);
  return Math.round(sum / weeklyTrends.length);
});

const weeklyTrend = computed(() => {
  // Calculate the percentage change from the previous week
  const weeklyTrends = attendanceStore.getAttendanceTrends('weekly');
  if (weeklyTrends.length < 2) return 0;
  
  const currentWeek = weeklyTrends[weeklyTrends.length - 1].count;
  const previousWeek = weeklyTrends[weeklyTrends.length - 2].count;
  
  if (previousWeek === 0) return 100; // Avoid division by zero
  
  return Math.round(((currentWeek - previousWeek) / previousWeek) * 100);
});

const monthlyAverage = computed(() => {
  // Calculate from the monthly trends data
  const monthlyTrends = attendanceStore.getAttendanceTrends('monthly');
  if (monthlyTrends.length === 0) return 0;
  
  const sum = monthlyTrends.reduce((total, item) => total + item.count, 0);
  return Math.round(sum / monthlyTrends.length);
});

const monthlyTrend = computed(() => {
  // Calculate the percentage change from the previous month
  const monthlyTrends = attendanceStore.getAttendanceTrends('monthly');
  if (monthlyTrends.length < 2) return 0;
  
  const currentMonth = monthlyTrends[monthlyTrends.length - 1].count;
  const previousMonth = monthlyTrends[monthlyTrends.length - 2].count;
  
  if (previousMonth === 0) return 100; // Avoid division by zero
  
  return Math.round(((currentMonth - previousMonth) / previousMonth) * 100);
});

const yearToDate = computed(() => {
  // Get the total attendance for the current year
  const yearlyTrends = attendanceStore.getAttendanceTrends('yearly');
  if (yearlyTrends.length === 0) return 0;
  
  return yearlyTrends[yearlyTrends.length - 1].count;
});

const yearlyTrend = computed(() => {
  // Calculate the percentage change from the previous year
  const yearlyTrends = attendanceStore.getAttendanceTrends('yearly');
  if (yearlyTrends.length < 2) return 0;
  
  const currentYear = yearlyTrends[yearlyTrends.length - 1].count;
  const previousYear = yearlyTrends[yearlyTrends.length - 2].count;
  
  if (previousYear === 0) return 100; // Avoid division by zero
  
  return Math.round(((currentYear - previousYear) / previousYear) * 100);
});

// Methods
function formatCategoryName(category) {
  return category
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

function addCategory() {
  if (!newCategory.value.trim()) return;
  
  const formattedCategory = newCategory.value
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '-');
  
  if (!settings.value.defaultCategories.includes(formattedCategory)) {
    settings.value.defaultCategories.push(formattedCategory);
  }
  
  newCategory.value = '';
}

function removeCategory(category) {
  const index = settings.value.defaultCategories.indexOf(category);
  if (index !== -1) {
    settings.value.defaultCategories.splice(index, 1);
  }
}

function saveSettings() {
  attendanceStore.updateSettings(settings.value);
  showSettings.value = false;
}

function exportAttendanceData() {
  // In a real implementation, this would generate a CSV or Excel file
  alert('Attendance data would be exported here');
}

function generateReport() {
  // In a real implementation, this would generate a PDF report
  alert('Attendance report would be generated here');
}
</script>

<style scoped>
.toggle-checkbox:checked {
  @apply right-0 border-primary-600;
}
.toggle-checkbox:checked + .toggle-label {
  @apply bg-primary-600;
}
</style>
