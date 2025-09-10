<template>
  <aside class="bg-primary-800 dark:bg-primary-900 text-white w-64 min-h-screen fixed left-0 top-0 z-30 transition-all duration-300 transform" :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
    <div class="p-4 border-b border-primary-700 dark:border-primary-800 transition-colors duration-300">
      <div class="flex items-center justify-between">
        <router-link to="/" class="text-xl font-bold text-white">{{ churchName }}</router-link>
        <button @click="toggleSidebar" class="text-white md:hidden hover:text-primary-200 transition-colors duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <nav class="py-4 overflow-y-auto h-[calc(100vh-64px)]">
      <ul class="space-y-1">
        <!-- Dashboard -->
        <li>
          <router-link to="/" class="flex items-center px-4 py-2 text-neutral-200 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
          </router-link>
        </li>
        
        <!-- Members -->
        <li>
          <button @click="toggleSection('members')" class="w-full flex items-center justify-between px-4 py-2 text-neutral-200 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActiveSection('members') }">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span>Members</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="sectionOpen.members ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <ul v-show="sectionOpen.members" class="pl-10 py-1 bg-primary-900 dark:bg-black/30 transition-colors duration-300">
            <li>
              <router-link to="/members" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/members') }">
                All Members
              </router-link>
            </li>
            <li>
              <router-link to="/members/new" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/members/new') }">
                Add Member
              </router-link>
            </li>
            <li>
              <router-link to="/members/import" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/members/import') }">
                Import Members
              </router-link>
            </li>
          </ul>
        </li>
        
        <!-- Families -->
        <li>
          <router-link to="/families" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/families') }">
            <i class="fas fa-home w-5 h-5 mr-3"></i>
            <span>Families</span>
          </router-link>
        </li>
        
        <!-- Attendance -->
        <li>
          <router-link to="/attendance" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/attendance') }">
            <i class="fas fa-clipboard-check w-5 h-5 mr-3"></i>
            <span>Attendance</span>
          </router-link>
        </li>
        
        <!-- Groups -->
        <li>
          <button @click="toggleSection('groups')" class="w-full flex items-center justify-between px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActiveSection('groups') }">
            <div class="flex items-center">
              <i class="fas fa-users w-5 h-5 mr-3"></i>
              <span>Groups</span>
            </div>
            <i :class="sectionOpen.groups ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="text-xs"></i>
          </button>
          <ul v-show="sectionOpen.groups" class="pl-10 py-1 bg-gray-900">
            <li>
              <router-link to="/groups" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/groups') }">
                All Groups
              </router-link>
            </li>
            <li>
              <router-link to="/groups/create" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/groups/create') }">
                Create Group
              </router-link>
            </li>
          </ul>
        </li>
        
        <!-- Finance -->
        <li>
          <button @click="toggleSection('finance')" class="w-full flex items-center justify-between px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActiveSection('finance') }">
            <div class="flex items-center">
              <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i>
              <span>Finance</span>
            </div>
            <i :class="sectionOpen.finance ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="text-xs"></i>
          </button>
          <ul v-show="sectionOpen.finance" class="pl-10 py-1 bg-gray-900">
            <li>
              <router-link to="/finance" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance') }">
                Dashboard
              </router-link>
            </li>
            <li>
              <router-link to="/finance/donations" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/donations') }">
                Donations
              </router-link>
            </li>
            <li>
              <router-link to="/finance/donation-categories" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/donation-categories') }">
                Categories
              </router-link>
            </li>
            <li>
              <router-link to="/finance/projects" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/projects') }">
                Projects
              </router-link>
            </li>
            <li>
              <router-link to="/finance/expenses" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/expenses') }">
                Expenses
              </router-link>
            </li>
            <li>
              <router-link to="/finance/campaigns" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/campaigns') }">
                Campaigns
              </router-link>
            </li>
            <li>
              <router-link to="/finance/budgets" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/budgets') }">
                Budgets
              </router-link>
            </li>
            <li>
              <router-link to="/finance/pledges" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/pledges') }">
                Pledges
              </router-link>
            </li>
            <li>
              <router-link to="/finance/reports" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/finance/reports') }">
                Financial Reports
              </router-link>
            </li>
            <li>
              <router-link to="/tax-receipts" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/tax-receipts') }">
                Tax Receipts
              </router-link>
            </li>
          </ul>
        </li>
        
        <!-- Events -->
        <li>
          <router-link to="/events" class="flex items-center px-4 py-2 text-neutral-200 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/events') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Events</span>
          </router-link>
        </li>
        
        <!-- Attendance -->
        <li>
          <router-link to="/attendance" class="flex items-center px-4 py-2 text-neutral-200 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/attendance') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
            </svg>
            <span>Attendance</span>
          </router-link>
        </li>
        
        <!-- Communications -->
        <li>
          <button @click="toggleSection('communications')" class="w-full flex items-center justify-between px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActiveSection('communications') }">
            <div class="flex items-center">
              <i class="fas fa-envelope w-5 h-5 mr-3"></i>
              <span>Communications</span>
            </div>
            <i :class="sectionOpen.communications ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="text-xs"></i>
          </button>
          <ul v-show="sectionOpen.communications" class="pl-10 py-1 bg-gray-900">
            <li>
              <router-link to="/communications" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/communications') }">
                Email & SMS
              </router-link>
            </li>
            <li>
              <router-link to="/prayer-requests" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/prayer-requests') }">
                Prayer Requests
              </router-link>
            </li>
            <li>
              <router-link to="/whatsapp" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/whatsapp') }">
                WhatsApp
              </router-link>
            </li>
          </ul>
        </li>
        
        <!-- Reports -->
        <li>
          <router-link to="/reports" class="flex items-center px-4 py-2 text-neutral-200 hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white transition-colors duration-300" :class="{ 'bg-primary-700 dark:bg-primary-800 text-white': isActive('/reports') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Reports</span>
          </router-link>
        </li>
        
        <!-- Settings -->
        <li>
          <button @click="toggleSection('settings')" class="w-full flex items-center justify-between px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActiveSection('settings') }">
            <div class="flex items-center">
              <i class="fas fa-cog w-5 h-5 mr-3"></i>
              <span>Settings</span>
            </div>
            <i :class="sectionOpen.settings ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="text-xs"></i>
          </button>
          <ul v-show="sectionOpen.settings" class="pl-10 py-1 bg-gray-900">
            <li>
              <router-link to="/settings" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/settings') }">
                General
              </router-link>
            </li>
            <li>
              <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/profile') }">
                Profile
              </router-link>
            </li>
            <li>
              <router-link to="/custom-fields" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white" :class="{ 'bg-gray-700 text-white': isActive('/custom-fields') }">
                Custom Fields
              </router-link>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSettingsStore } from '@/stores/settings';

// Props
defineProps({
  sidebarOpen: {
    type: Boolean,
    required: true
  }
});

// Emits
const emit = defineEmits(['toggle-sidebar']);

// Store access
const settingsStore = useSettingsStore();
const route = useRoute();

// Computed values
const churchName = computed(() => {
  return settingsStore.churchName || 'Church Manager';
});

// Track which sections are open
const sectionOpen = ref({
  members: false,
  groups: false,
  finance: false,
  communications: false,
  reports: false,
  settings: false
});

// Check if a route is active
function isActive(path) {
  return route.path === path;
}

// Check if a section is active
function isActiveSection(section) {
  const sectionPaths = {
    members: ['/members', '/members/new', '/members/import'],
    groups: ['/groups', '/groups/create', '/groups/edit', '/groups/view'],
    finance: ['/finance', '/finance/donations', '/finance/donation-categories', '/finance/projects', '/finance/expenses', '/finance/campaigns', '/finance/budgets', '/finance/pledges', '/finance/reports', '/tax-receipts'],
    communications: ['/communications', '/prayer-requests', '/whatsapp'],
    reports: ['/reports', '/reports/members', '/reports/attendance', '/reports/financial'],
    settings: ['/settings', '/profile', '/custom-fields', '/theme-customizer']
  };
  
  return sectionPaths[section]?.some(path => route.path.startsWith(path)) || false;
}

// Toggle sidebar
function toggleSidebar() {
  emit('toggle-sidebar');
}

// Toggle section
function toggleSection(section) {
  sectionOpen.value[section] = !sectionOpen.value[section];
}

// Auto-open section based on current route
function autoOpenSections() {
  Object.keys(sectionOpen.value).forEach(section => {
    if (isActiveSection(section)) {
      sectionOpen.value[section] = true;
    }
  });
}

// Call autoOpenSections on component mount
onMounted(() => {
  autoOpenSections();
});
</script>
