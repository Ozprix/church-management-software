<template>
  <div class="contextual-help">
    <!-- Help Icon Button -->
    <button 
      @click="toggleHelpPanel"
      class="help-icon p-1.5 rounded-full text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
      :class="{ 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400': isHelpOpen }"
      :aria-expanded="isHelpOpen.toString()"
      aria-haspopup="true"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>
    
    <!-- Help Panel Slide-in -->
    <transition
      enter-active-class="transform transition ease-in-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transform transition ease-in-out duration-300"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div 
        v-if="isHelpOpen" 
        class="fixed inset-y-0 right-0 z-40 w-full sm:w-96 bg-white dark:bg-neutral-800 shadow-xl overflow-y-auto"
      >
        <!-- Help Panel Header -->
        <div class="px-4 py-5 sm:px-6 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
          <h2 class="text-lg font-medium text-neutral-900 dark:text-white">Help & Support</h2>
          <button 
            @click="toggleHelpPanel" 
            class="p-1.5 rounded-full text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300 focus:outline-none"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Help Panel Content -->
        <div class="px-4 py-5 sm:px-6">
          <!-- Current Page Help -->
          <div v-if="currentPageHelp" class="mb-6">
            <h3 class="text-md font-medium text-neutral-900 dark:text-white mb-2">{{ currentPageHelp.title }}</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">{{ currentPageHelp.description }}</p>
            
            <div v-if="currentPageHelp.tips && currentPageHelp.tips.length > 0" class="mt-4 space-y-3">
              <h4 class="text-sm font-medium text-neutral-900 dark:text-white">Tips & Shortcuts</h4>
              <ul class="list-disc list-inside text-sm text-neutral-600 dark:text-neutral-400 space-y-2">
                <li v-for="(tip, index) in currentPageHelp.tips" :key="index">{{ tip }}</li>
              </ul>
            </div>
          </div>
          
          <!-- Feature Help Sections -->
          <div class="space-y-6">
            <div v-for="(section, index) in helpSections" :key="index" class="border-t border-neutral-200 dark:border-neutral-700 pt-4" :data-section-index="index">
              <h3 
                @click="toggleSection(index)" 
                class="flex justify-between items-center text-md font-medium text-neutral-900 dark:text-white cursor-pointer hover:text-primary-600 dark:hover:text-primary-400"
              >
                <span>{{ section.title }}</span>
                <svg 
                  xmlns="http://www.w3.org/2000/svg" 
                  class="h-5 w-5 transition-transform duration-200" 
                  :class="{ 'rotate-180': expandedSections.includes(index) }"
                  fill="none" 
                  viewBox="0 0 24 24" 
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </h3>
              
              <div v-if="expandedSections.includes(index)" class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 space-y-3">
                <p>{{ section.description }}</p>
                
                <div v-if="section.steps && section.steps.length > 0" class="mt-3">
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">How to use:</h4>
                  <ol class="list-decimal list-inside space-y-1">
                    <li v-for="(step, stepIndex) in section.steps" :key="stepIndex">{{ step }}</li>
                  </ol>
                </div>
                
                <div v-if="section.image" class="mt-3">
                  <img :src="section.image" :alt="section.title" class="rounded-md border border-neutral-200 dark:border-neutral-700 max-w-full" />
                </div>
                
                <div v-if="section.videoUrl" class="mt-3">
                  <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">Watch tutorial:</h4>
                  <a 
                    :href="section.videoUrl" 
                    target="_blank" 
                    class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Watch Video Tutorial
                  </a>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Search for Help -->
          <div class="mt-8 border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <h3 class="text-md font-medium text-neutral-900 dark:text-white mb-3">Search for Help</h3>
            <div class="relative">
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search help topics..." 
                class="block w-full pl-10 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md leading-5 bg-white dark:bg-neutral-900 placeholder-neutral-500 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:text-white sm:text-sm"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            
            <!-- Search Results -->
            <div v-if="searchQuery.length > 2 && filteredResults.length > 0" class="mt-4 space-y-3">
              <h4 class="text-sm font-medium text-neutral-900 dark:text-white">Search Results</h4>
              <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
                <li v-for="(result, index) in filteredResults" :key="index" class="py-3">
                  <h5 class="text-sm font-medium text-neutral-900 dark:text-white">{{ result.title }}</h5>
                  <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">{{ result.description }}</p>
                  <button 
                    @click="showSearchResult(result)" 
                    class="mt-2 text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 inline-flex items-center"
                  >
                    Learn more
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </li>
              </ul>
            </div>
            
            <div v-else-if="searchQuery.length > 2 && filteredResults.length === 0" class="mt-4">
              <p class="text-sm text-neutral-600 dark:text-neutral-400">No results found. Try different keywords or contact support.</p>
            </div>
          </div>
          
          <!-- Contact Support -->
          <div class="mt-8 border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <h3 class="text-md font-medium text-neutral-900 dark:text-white mb-3">Need More Help?</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <button 
                @click="startChat" 
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Chat with Support
              </button>
              <button 
                @click="openDocumentation" 
                class="inline-flex items-center justify-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Documentation
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
    
    <!-- Backdrop -->
    <div 
      v-if="isHelpOpen" 
      class="fixed inset-0 z-30 bg-neutral-900 bg-opacity-50 transition-opacity"
      @click="toggleHelpPanel"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToastService } from '../../services/toastService';

const props = defineProps({
  context: {
    type: String,
    default: ''
  }
});

// State
const isHelpOpen = ref(false);
const expandedSections = ref([]);
const searchQuery = ref('');
const route = useRoute();
const toast = useToastService();

// Help content data
const helpContent = {
  dashboard: {
    title: 'Dashboard',
    description: 'The dashboard provides an overview of your church\'s key metrics and recent activities.',
    tips: [
      'Click the gear icon to customize your dashboard widgets',
      'Toggle between grid and list view using the layout button',
      'Click on any widget\'s refresh button to update its data'
    ]
  },
  members: {
    title: 'Members Management',
    description: 'Manage your church membership database, including member profiles, family relationships, and contact information.',
    tips: [
      'Use the search bar to quickly find members',
      'Click on a member\'s name to view their full profile',
      'Use the bulk actions menu to perform actions on multiple members'
    ]
  },
  donations: {
    title: 'Donations & Financial Management',
    description: 'Track donations, generate reports, and manage financial campaigns.',
    tips: [
      'Use batch entry for recording multiple donations quickly',
      'Set up recurring donations for regular givers',
      'Export donation data for accounting purposes'
    ]
  },
  reports: {
    title: 'Reports',
    description: 'Generate and analyze reports about your church\'s activities, membership, and finances.',
    tips: [
      'Save frequently used reports for quick access',
      'Schedule reports to be generated automatically',
      'Export reports in various formats (PDF, Excel, CSV)'
    ]
  },
  events: {
    title: 'Events',
    description: 'Schedule and manage church events, track attendance, and handle registrations.',
    tips: [
      'Set up recurring events for regular meetings',
      'Use the calendar view to see all upcoming events',
      'Enable online registration for events'
    ]
  },
  settings: {
    title: 'Settings',
    description: 'Configure your church management system to match your church\'s needs.',
    tips: [
      'Customize notification preferences in the Notifications tab',
      'Set up user roles and permissions in the Users tab',
      'Configure church details in the Church Profile tab'
    ]
  }
};

// Help sections for common features
const helpSections = [
  {
    title: 'Getting Started',
    description: 'Learn the basics of using the Church Management System.',
    steps: [
      'Navigate through the system using the main menu',
      'Customize your dashboard to show relevant information',
      'Set up your church profile in the Settings page',
      'Add your first members to the database'
    ],
    image: '/images/help/getting-started.png',
    videoUrl: 'https://example.com/tutorials/getting-started'
  },
  {
    title: 'Managing Members',
    description: 'Learn how to add, edit, and organize your church members.',
    steps: [
      'Add new members using the "Add Member" button',
      'Organize members into families and groups',
      'Track member attendance and participation',
      'Generate member directories and reports'
    ],
    image: '/images/help/managing-members.png',
    videoUrl: 'https://example.com/tutorials/managing-members'
  },
  {
    title: 'Recording Donations',
    description: 'Track financial contributions and generate donation reports.',
    steps: [
      'Record individual donations from the Donations page',
      'Use batch entry for multiple donations',
      'Generate donation statements for tax purposes',
      'Set up and track fundraising campaigns'
    ],
    image: '/images/help/recording-donations.png',
    videoUrl: 'https://example.com/tutorials/recording-donations'
  },
  {
    title: 'Creating Reports',
    description: 'Generate and analyze various reports about your church.',
    steps: [
      'Select a report type from the Reports page',
      'Apply filters to customize your report',
      'Save reports for future reference',
      'Export reports in different formats'
    ],
    image: '/images/help/creating-reports.png',
    videoUrl: 'https://example.com/tutorials/creating-reports'
  },
  {
    title: 'Managing Events',
    description: 'Schedule, organize, and track church events and attendance.',
    steps: [
      'Create new events from the Events page',
      'Set up registration for events',
      'Track attendance for events',
      'Send notifications to event participants'
    ],
    image: '/images/help/managing-events.png',
    videoUrl: 'https://example.com/tutorials/managing-events'
  }
];

// Computed properties
const currentPageHelp = computed(() => {
  const currentPath = route.path.split('/')[1] || 'dashboard';
  return helpContent[currentPath] || null;
});

const filteredResults = computed(() => {
  if (searchQuery.value.length < 3) return [];
  
  const query = searchQuery.value.toLowerCase();
  const results = [];
  
  // Search in page help content
  Object.entries(helpContent).forEach(([key, content]) => {
    if (
      content.title.toLowerCase().includes(query) ||
      content.description.toLowerCase().includes(query) ||
      (content.tips && content.tips.some(tip => tip.toLowerCase().includes(query)))
    ) {
      results.push({
        id: key,
        title: content.title,
        description: content.description,
        type: 'page'
      });
    }
  });
  
  // Search in help sections
  helpSections.forEach((section, index) => {
    if (
      section.title.toLowerCase().includes(query) ||
      section.description.toLowerCase().includes(query) ||
      (section.steps && section.steps.some(step => step.toLowerCase().includes(query)))
    ) {
      results.push({
        id: `section-${index}`,
        title: section.title,
        description: section.description,
        type: 'section',
        sectionIndex: index
      });
    }
  });
  
  return results;
});

// Methods
const toggleHelpPanel = () => {
  isHelpOpen.value = !isHelpOpen.value;
};

const toggleSection = (index) => {
  const position = expandedSections.value.indexOf(index);
  if (position === -1) {
    expandedSections.value.push(index);
  } else {
    expandedSections.value.splice(position, 1);
  }
};

const showSearchResult = (result) => {
  if (result.type === 'section' && typeof result.sectionIndex === 'number') {
    // Expand the section
    if (!expandedSections.value.includes(result.sectionIndex)) {
      expandedSections.value.push(result.sectionIndex);
    }
    
    // Scroll to the section
    setTimeout(() => {
      const sectionElement = document.querySelector(`[data-section-index="${result.sectionIndex}"]`);
      if (sectionElement) {
        sectionElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  } else if (result.type === 'page') {
    // Close help panel and navigate to the page
    isHelpOpen.value = false;
    // You could add navigation here if needed
  }
};

const startChat = () => {
  // This would integrate with your chat support system
  toast.info('Connecting to support chat...');
  // In a real implementation, this would open your chat widget
  window.open('https://support.yourchurch.com/chat', '_blank');
};

const openDocumentation = () => {
  // Open documentation in a new tab
  window.open('https://docs.yourchurch.com', '_blank');
};

// Watch for context prop changes
watch(() => props.context, (newContext) => {
  if (newContext) {
    isHelpOpen.value = true;
    // If context matches a section, expand it
    const sectionIndex = helpSections.findIndex(section => 
      section.title.toLowerCase().includes(newContext.toLowerCase())
    );
    if (sectionIndex !== -1 && !expandedSections.value.includes(sectionIndex)) {
      expandedSections.value.push(sectionIndex);
    }
  }
});

// Expose methods to parent component
defineExpose({
  openHelp: (context = '') => {
    if (context) {
      const sectionIndex = helpSections.findIndex(section => 
        section.title.toLowerCase().includes(context.toLowerCase())
      );
      if (sectionIndex !== -1 && !expandedSections.value.includes(sectionIndex)) {
        expandedSections.value.push(sectionIndex);
      }
    }
    isHelpOpen.value = true;
  },
  closeHelp: () => {
    isHelpOpen.value = false;
  }
});
</script>

<style scoped>
/* Add any additional styling here */
</style>
