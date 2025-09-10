<template>
  <div class="contextual-help">
    <!-- Help Button -->
    <button 
      v-if="showHelpButton"
      @click="toggleHelpPanel" 
      class="help-button fixed bottom-6 right-6 z-40 flex items-center justify-center w-12 h-12 rounded-full bg-primary-600 hover:bg-primary-700 text-white shadow-lg transition-all duration-300 ease-in-out"
      :class="{ 'rotate-45': isOpen }"
      aria-label="Help"
    >
      <i :class="isOpen ? 'fas fa-times' : 'fas fa-question'"></i>
    </button>
    
    <!-- Help Panel -->
    <transition name="slide-up">
      <div 
        v-if="isOpen" 
        class="help-panel fixed bottom-20 right-6 z-40 w-80 bg-white dark:bg-neutral-800 rounded-lg shadow-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden"
      >
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
          <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Help & Resources</h3>
          <button @click="isOpen = false" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="p-4">
          <!-- Contextual Help -->
          <div v-if="contextualHelp.length > 0" class="mb-4">
            <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">{{ currentPageTitle }}</h4>
            <ul class="space-y-2">
              <li v-for="(item, index) in contextualHelp" :key="index">
                <button 
                  @click="showHelpItem(item)" 
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i :class="item.icon || 'fas fa-info-circle'" class="mr-2 text-primary-500"></i>
                  <span>{{ item.title }}</span>
                </button>
              </li>
            </ul>
          </div>
          
          <!-- Quick Actions -->
          <div class="mb-4">
            <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">Quick Actions</h4>
            <ul class="space-y-2">
              <li>
                <button 
                  @click="startOnboarding" 
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-play-circle mr-2 text-primary-500"></i>
                  <span>Restart Onboarding Guide</span>
                </button>
              </li>
              <li>
                <button 
                  @click="openHelpCenter" 
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-book mr-2 text-primary-500"></i>
                  <span>Open Help Center</span>
                </button>
              </li>
              <li>
                <button 
                  @click="openKeyboardShortcuts" 
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-keyboard mr-2 text-primary-500"></i>
                  <span>Keyboard Shortcuts</span>
                </button>
              </li>
            </ul>
          </div>
          
          <!-- Help Resources -->
          <div>
            <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">Resources</h4>
            <ul class="space-y-2">
              <li>
                <a 
                  href="#" 
                  @click.prevent="openDocumentation"
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-file-alt mr-2 text-primary-500"></i>
                  <span>Documentation</span>
                </a>
              </li>
              <li>
                <a 
                  href="#" 
                  @click.prevent="openVideoTutorials"
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-video mr-2 text-primary-500"></i>
                  <span>Video Tutorials</span>
                </a>
              </li>
              <li>
                <a 
                  href="#" 
                  @click.prevent="contactSupport"
                  class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center"
                >
                  <i class="fas fa-headset mr-2 text-primary-500"></i>
                  <span>Contact Support</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </transition>
    
    <!-- Help Item Modal -->
    <Modal 
      v-if="selectedHelpItem" 
      :title="selectedHelpItem.title"
      @close="selectedHelpItem = null"
    >
      <div class="help-content">
        <div v-if="selectedHelpItem.image" class="mb-4">
          <img :src="selectedHelpItem.image" :alt="selectedHelpItem.title" class="w-full rounded-lg">
        </div>
        
        <div v-html="selectedHelpItem.content" class="prose dark:prose-invert max-w-none"></div>
        
        <div v-if="selectedHelpItem.steps && selectedHelpItem.steps.length > 0" class="mt-4">
          <h4 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Steps</h4>
          <ol class="list-decimal list-inside space-y-2">
            <li v-for="(step, index) in selectedHelpItem.steps" :key="index" class="text-neutral-700 dark:text-neutral-300">
              {{ step }}
            </li>
          </ol>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end">
          <button 
            v-if="selectedHelpItem.demoAvailable"
            @click="startDemo" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mr-3"
          >
            <i class="fas fa-play-circle mr-2"></i> Show Me How
          </button>
          <button 
            @click="selectedHelpItem = null" 
            class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Close
          </button>
        </div>
      </template>
    </Modal>
    
    <!-- Keyboard Shortcuts Modal -->
    <Modal 
      v-if="showKeyboardShortcuts" 
      title="Keyboard Shortcuts"
      @close="showKeyboardShortcuts = false"
    >
      <div class="keyboard-shortcuts">
        <div class="grid grid-cols-2 gap-4">
          <div v-for="(group, groupName) in keyboardShortcuts" :key="groupName" class="col-span-1">
            <h4 class="text-lg font-medium text-neutral-900 dark:text-white mb-2 capitalize">{{ groupName }}</h4>
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4">
              <div v-for="(shortcut, index) in group" :key="index" class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800 last:border-b-0">
                <span class="text-neutral-700 dark:text-neutral-300">{{ shortcut.description }}</span>
                <div class="flex space-x-1">
                  <span 
                    v-for="(key, keyIndex) in shortcut.keys" 
                    :key="keyIndex" 
                    class="px-2 py-1 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded text-xs font-mono"
                  >
                    {{ key }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end">
          <button 
            @click="showKeyboardShortcuts = false" 
            class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Close
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { useRoute } from 'vue-router';
import { useOnboardingStore } from '../../stores/onboarding';
import Modal from '../ui/Modal.vue';

// Get onboarding store
const onboardingStore = useOnboardingStore();
const route = useRoute();

// Get onboarding guide reference (optional)
const onboardingGuide = inject('onboardingGuide', null);

// Component state
const isOpen = ref(false);
const showHelpButton = ref(true);
const selectedHelpItem = ref(null);
const showKeyboardShortcuts = ref(false);

// Computed
const currentPageTitle = computed(() => {
  return route.meta.title || 'Current Page';
});

// Help content by route
const helpContentMap = {
  'dashboard': [
    {
      title: 'Customizing Your Dashboard',
      icon: 'fas fa-columns',
      content: `
        <p>Your dashboard is fully customizable to show the information that matters most to you.</p>
        <p>You can add, remove, resize, and rearrange widgets to create a personalized view of your church's key metrics and activities.</p>
      `,
      image: '/images/help/dashboard-customization.png',
      steps: [
        'Click the "Edit Dashboard" button in the top right corner',
        'Drag widgets to rearrange them',
        'Use the "+" button to add new widgets',
        'Click the gear icon on any widget to configure it or remove it',
        'Click "Save Layout" when you're done'
      ],
      demoAvailable: true
    },
    {
      title: 'Understanding Dashboard Metrics',
      icon: 'fas fa-chart-line',
      content: `
        <p>The dashboard displays key metrics about your church's membership, attendance, donations, and activities.</p>
        <p>Each metric is calculated based on data from the corresponding module and can be customized to show different time periods.</p>
      `,
      steps: [
        'Hover over any metric to see a tooltip with more information',
        'Click on a metric to view detailed reports',
        'Use the time period selector to change the date range',
        'Click the refresh icon to update the data'
      ],
      demoAvailable: false
    }
  ],
  'members': [
    {
      title: 'Adding New Members',
      icon: 'fas fa-user-plus',
      content: `
        <p>You can add new members to your church database individually or by importing a CSV file.</p>
        <p>Each member record can include contact information, family relationships, ministry involvement, and custom fields.</p>
      `,
      steps: [
        'Click the "Add Member" button in the top right corner',
        'Fill out the required fields (marked with *)',
        'Add optional information as needed',
        'Upload a photo if available',
        'Click "Save" to create the member record'
      ],
      demoAvailable: true
    },
    {
      title: 'Managing Member Groups',
      icon: 'fas fa-users',
      content: `
        <p>Groups help you organize members by ministry, activity, or any other category.</p>
        <p>Members can belong to multiple groups, and you can use groups for communication, attendance tracking, and reporting.</p>
      `,
      steps: [
        'Go to the "Groups" tab in the Members section',
        'Click "Create Group" to add a new group',
        'Add members to the group by selecting them from the list',
        'Assign group leaders and set meeting schedules',
        'Use the group dashboard to track attendance and activities'
      ],
      demoAvailable: true
    }
  ],
  'donations': [
    {
      title: 'Recording Donations',
      icon: 'fas fa-hand-holding-heart',
      content: `
        <p>You can record donations from members or visitors, including one-time gifts and recurring donations.</p>
        <p>Each donation can be categorized and linked to specific funds or campaigns.</p>
      `,
      steps: [
        'Click the "Add Donation" button',
        'Select the donor from the dropdown or add a new donor',
        'Enter the donation amount and date',
        'Select the donation category and payment method',
        'Add any notes or reference numbers',
        'Click "Save" to record the donation'
      ],
      demoAvailable: true
    },
    {
      title: 'Managing Pledge Campaigns',
      icon: 'fas fa-bullseye',
      content: `
        <p>Pledge campaigns help you track progress toward financial goals for building projects, missions, or other initiatives.</p>
        <p>You can create campaigns with specific goals, track pledges, and monitor fulfillment.</p>
      `,
      steps: [
        'Go to the "Campaigns" tab in the Donations section',
        'Click "Create Campaign" to set up a new campaign',
        'Define the campaign goal, start date, and end date',
        'Add pledges from members',
        'Track progress on the campaign dashboard',
        'Generate reports on pledge fulfillment'
      ],
      demoAvailable: true
    }
  ],
  'search': [
    {
      title: 'Advanced Search Features',
      icon: 'fas fa-search',
      content: `
        <p>The search functionality allows you to find information across all modules of the church management system.</p>
        <p>You can use filters, voice search, and save your searches for future use.</p>
      `,
      steps: [
        'Enter your search term in the search bar',
        'Use the filters panel to narrow down results by type, date, etc.',
        'Click the microphone icon to use voice search',
        'Save your search for future reference',
        'Click on a result to view the full details'
      ],
      demoAvailable: true
    }
  ]
};

// Keyboard shortcuts
const keyboardShortcuts = {
  navigation: [
    { description: 'Dashboard', keys: ['G', 'D'] },
    { description: 'Members', keys: ['G', 'M'] },
    { description: 'Donations', keys: ['G', 'O'] },
    { description: 'Events', keys: ['G', 'E'] },
    { description: 'Reports', keys: ['G', 'R'] }
  ],
  actions: [
    { description: 'Search', keys: ['/'] },
    { description: 'New Item', keys: ['N'] },
    { description: 'Save', keys: ['Ctrl', 'S'] },
    { description: 'Help', keys: ['?'] },
    { description: 'Close Modal', keys: ['Esc'] }
  ],
  display: [
    { description: 'Toggle Dark Mode', keys: ['Ctrl', 'D'] },
    { description: 'Fullscreen', keys: ['F11'] },
    { description: 'Zoom In', keys: ['Ctrl', '+'] },
    { description: 'Zoom Out', keys: ['Ctrl', '-'] },
    { description: 'Reset Zoom', keys: ['Ctrl', '0'] }
  ]
};

// Get contextual help for current route
const contextualHelp = computed(() => {
  const routeName = route.name;
  return helpContentMap[routeName] || [];
});

// Methods
const toggleHelpPanel = () => {
  isOpen.value = !isOpen.value;
};

const showHelpItem = (item) => {
  selectedHelpItem.value = item;
  isOpen.value = false;
};

const startDemo = () => {
  // Close the help item modal
  selectedHelpItem.value = null;
  
  // TODO: Implement demo functionality
  console.log('Starting demo for:', selectedHelpItem.value?.title);
};

const startOnboarding = () => {
  if (onboardingGuide) {
    onboardingGuide.resetOnboarding();
  } else {
    onboardingStore.resetOnboarding();
    window.location.reload();
  }
  
  isOpen.value = false;
};

const openHelpCenter = () => {
  // TODO: Implement help center navigation
  console.log('Opening help center');
  isOpen.value = false;
};

const openKeyboardShortcuts = () => {
  showKeyboardShortcuts.value = true;
  isOpen.value = false;
};

const openDocumentation = () => {
  // TODO: Implement documentation navigation
  console.log('Opening documentation');
  isOpen.value = false;
};

const openVideoTutorials = () => {
  // TODO: Implement video tutorials navigation
  console.log('Opening video tutorials');
  isOpen.value = false;
};

const contactSupport = () => {
  // TODO: Implement contact support functionality
  console.log('Contacting support');
  isOpen.value = false;
};

// Lifecycle hooks
onMounted(() => {
  // Check if contextual help is enabled in user preferences
  showHelpButton.value = onboardingStore.userPreferences.enableContextualHelp;
});
</script>

<style scoped>
.help-button {
  transition: transform 0.3s ease;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(20px);
  opacity: 0;
}

.prose {
  max-width: 100%;
}

.prose p {
  margin-bottom: 1rem;
}

.prose img {
  border-radius: 0.375rem;
  margin: 1rem 0;
}
</style>
