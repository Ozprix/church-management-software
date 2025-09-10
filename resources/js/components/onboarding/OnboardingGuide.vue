<template>
  <div class="onboarding-guide">
    <!-- Onboarding Modal -->
    <div v-if="showOnboarding" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6">
            <!-- Progress bar -->
            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 mb-6">
              <div class="bg-primary-600 h-2.5 rounded-full" :style="{ width: `${(currentStep / totalSteps) * 100}%` }"></div>
            </div>
            
            <!-- Step content -->
            <div class="step-content">
              <!-- Welcome Step -->
              <div v-if="currentStep === 1" class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900">
                  <svg class="h-10 w-10 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                  </svg>
                </div>
                <h3 class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white">Welcome to the Church Management System</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                  Let's take a quick tour to help you get started with the system. This will only take a few minutes.
                </p>
              </div>
              
              <!-- Dashboard Overview Step -->
              <div v-else-if="currentStep === 2">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Dashboard Overview</h3>
                <div class="mt-4 flex items-start">
                  <img src="/images/onboarding/dashboard.png" alt="Dashboard" class="w-1/2 rounded-lg shadow-md mr-4" />
                  <div>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-3">
                      Your dashboard provides a quick overview of your church's key metrics and recent activities.
                    </p>
                    <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-400 space-y-2">
                      <li>View membership statistics and trends</li>
                      <li>Monitor attendance and giving</li>
                      <li>Track upcoming events</li>
                      <li>See recent member activities</li>
                    </ul>
                  </div>
                </div>
              </div>
              
              <!-- Navigation Step -->
              <div v-else-if="currentStep === 3">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Navigation</h3>
                <div class="mt-4 flex items-start">
                  <div>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-3">
                      The main navigation menu gives you access to all areas of the system:
                    </p>
                    <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-400 space-y-2">
                      <li><strong>Members:</strong> Manage your church membership database</li>
                      <li><strong>Groups:</strong> Organize and track small groups and ministries</li>
                      <li><strong>Events:</strong> Schedule and manage church events</li>
                      <li><strong>Donations:</strong> Track giving and generate financial reports</li>
                      <li><strong>Reports:</strong> Access comprehensive church analytics</li>
                      <li><strong>Settings:</strong> Customize the system to your needs</li>
                    </ul>
                  </div>
                  <img src="/images/onboarding/navigation.png" alt="Navigation" class="w-1/2 rounded-lg shadow-md ml-4" />
                </div>
              </div>
              
              <!-- Donations Module Step -->
              <div v-else-if="currentStep === 4">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Donations & Financial Management</h3>
                <div class="mt-4">
                  <p class="text-neutral-600 dark:text-neutral-400 mb-3">
                    The donations module helps you track and manage all financial contributions:
                  </p>
                  <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-3 rounded-lg">
                      <h4 class="font-medium text-neutral-900 dark:text-white">Record Donations</h4>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Easily record individual and batch donations</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-3 rounded-lg">
                      <h4 class="font-medium text-neutral-900 dark:text-white">Generate Reports</h4>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Create detailed financial reports and statements</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-3 rounded-lg">
                      <h4 class="font-medium text-neutral-900 dark:text-white">Manage Campaigns</h4>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Track special fundraising campaigns and pledges</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-3 rounded-lg">
                      <h4 class="font-medium text-neutral-900 dark:text-white">Donor Management</h4>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Maintain donor records and giving history</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Customization Step -->
              <div v-else-if="currentStep === 5">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Customizing Your Experience</h3>
                <div class="mt-4">
                  <p class="text-neutral-600 dark:text-neutral-400 mb-3">
                    Make the system work for you by customizing these key areas:
                  </p>
                  <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-400 space-y-2">
                    <li><strong>Dashboard:</strong> Arrange widgets to show the information most important to you</li>
                    <li><strong>Dark Mode:</strong> Toggle between light and dark themes for your preferred viewing experience</li>
                    <li><strong>Notifications:</strong> Set your notification preferences to stay informed</li>
                    <li><strong>Reports:</strong> Save frequently used reports for quick access</li>
                  </ul>
                  <div class="mt-4 flex justify-center">
                    <img src="/images/onboarding/customization.png" alt="Customization" class="w-3/4 rounded-lg shadow-md" />
                  </div>
                </div>
              </div>
              
              <!-- Getting Help Step -->
              <div v-else-if="currentStep === 6">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white">Getting Help</h3>
                <div class="mt-4">
                  <p class="text-neutral-600 dark:text-neutral-400 mb-3">
                    Help is always available when you need it:
                  </p>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg">
                      <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Contextual Help</h4>
                      </div>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Look for the help icons throughout the system for guidance on specific features.</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg">
                      <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Documentation</h4>
                      </div>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Access comprehensive documentation by clicking on the Help menu in the navigation bar.</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg">
                      <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Support Chat</h4>
                      </div>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Chat with our support team for immediate assistance with any questions.</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg">
                      <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Video Tutorials</h4>
                      </div>
                      <p class="text-sm text-neutral-600 dark:text-neutral-400">Watch step-by-step video tutorials on how to use different features.</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Final Step -->
              <div v-else-if="currentStep === 7" class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900">
                  <svg class="h-10 w-10 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h3 class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white">You're All Set!</h3>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                  You're now ready to start using the Church Management System. You can access this guide anytime from the Help menu.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                  <button 
                    @click="completeOnboarding" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                    Get Started
                  </button>
                  <button 
                    @click="showTips = true; completeOnboarding()" 
                    class="inline-flex justify-center items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                    Show Me Tips
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Modal footer -->
          <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              v-if="currentStep < totalSteps" 
              @click="nextStep" 
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto"
            >
              Next
              <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <button 
              v-if="currentStep > 1" 
              @click="prevStep" 
              class="mt-3 w-full inline-flex justify-center items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto"
            >
              <svg class="mr-2 -ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back
            </button>
            <button 
              @click="skipOnboarding" 
              class="mt-3 w-full inline-flex justify-center items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto"
            >
              Skip Tour
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Feature Tips (shown after onboarding if user selected "Show Me Tips") -->
    <div v-if="showTips" class="fixed bottom-4 right-4 z-40">
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-700 p-4 max-w-xs">
        <div class="flex justify-between items-start">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-primary-100 dark:bg-primary-900 rounded-full p-1">
              <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <h3 class="ml-2 text-sm font-medium text-neutral-900 dark:text-white">{{ currentTip.title }}</h3>
          </div>
          <button @click="nextTip" class="text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">{{ currentTip.content }}</p>
        <div class="mt-3 flex justify-between items-center">
          <div class="flex space-x-1">
            <span v-for="(_, index) in tips" :key="index" class="h-1.5 w-1.5 rounded-full" :class="tipIndex === index ? 'bg-primary-600 dark:bg-primary-400' : 'bg-neutral-300 dark:bg-neutral-600'"></span>
          </div>
          <button @click="nextTip" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
            Next Tip
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useOnboardingStore } from '../../stores/onboarding';
import { useSettingsStore } from '../../stores/settings';

const authStore = useAuthStore();
const onboardingStore = useOnboardingStore();
const settingsStore = useSettingsStore();

// State
const showOnboarding = ref(false);
const currentStep = ref(1);
const showTips = ref(false);
const currentTip = ref(0);

// Get steps based on user role
const steps = computed(() => {
  return onboardingStore.getOnboardingSteps(authStore.userRole || 'admin');
});

// Total steps
const totalSteps = computed(() => steps.value.length);

// Current step data
const currentStepData = computed(() => steps.value[currentStep.value - 1] || {});

// Get tips for current section
const tips = computed(() => {
  // Default to dashboard tips
  const section = currentStepData.value?.featureId || 'dashboard';
  return onboardingStore.getFeatureTips(section);
});

// Computed
const stepPercentage = computed(() => {
  return Math.round((currentStep.value / totalSteps.value) * 100);
});

const buttonText = computed(() => {
  if (currentStep.value === totalSteps.value) {
    return 'Get Started';
  }
  return 'Next';
});

const stepIcon = computed(() => {
  return currentStepData.value?.icon || 'info-circle';
});

// Methods
const nextStep = () => {
  if (currentStep.value < totalSteps.value) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const skipOnboarding = () => {
  onboardingStore.completeOnboarding(false);
  showOnboarding.value = false;
};

const completeOnboarding = () => {
  onboardingStore.completeOnboarding(true);
  showOnboarding.value = false;
  showTips.value = true;
};

const nextTip = () => {
  // Mark current tip as seen
  const tipId = tips.value[currentTip.value]?.id;
  if (tipId) {
    onboardingStore.markTipSeen(tipId);
  }
  
  if (currentTip.value < tips.value.length - 1) {
    currentTip.value++;
  } else {
    // Hide tips after showing all
    showTips.value = false;
    onboardingStore.showTips = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  // Check if user has completed onboarding
  const onboardingCompleted = settingsStore.onboardingCompleted;
  
  // If user is authenticated and hasn't completed onboarding, show the guide
  if (authStore.isAuthenticated && !onboardingCompleted) {
    showOnboarding.value = true;
  }
});

// Watch for auth changes
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    // Update role in onboarding store
    onboardingStore.setUserRole(newUser.role || 'admin');
  }
});

// Watch for authentication state changes
watch(
  () => authStore.isAuthenticated,
  (newValue) => {
    if (newValue) {
      // Check if user has completed onboarding
      const onboardingCompleted = settingsStore.onboardingCompleted;
      
      // If user is authenticated and hasn't completed onboarding, show the guide
      if (authStore.isAuthenticated && !onboardingCompleted) {
        showOnboarding.value = true;
      }
    }
  }
);

// Expose component to parent
defineExpose({
  openOnboarding: () => {
    showOnboarding.value = true;
    currentStep.value = 1;
  },
  resetOnboarding: () => {
    onboardingStore.resetOnboarding();
    showOnboarding.value = true;
    currentStep.value = 1;
  }
});
</script>

<style scoped>
.step-content {
  min-height: 300px;
}

@keyframes bounce-in {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-bounce-in {
  animation: bounce-in 0.5s ease-out;
}
</style>
