<template>
  <div class="onboarding-settings">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Onboarding & Help Settings</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Customize your onboarding experience and help preferences
        </p>
      </div>
      
      <div class="p-4 sm:p-6 space-y-6">
        <!-- Onboarding Preferences -->
        <div>
          <h4 class="text-base font-medium text-neutral-900 dark:text-white mb-4">Onboarding Preferences</h4>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h5 class="text-sm font-medium text-neutral-900 dark:text-white">Show Onboarding on Login</h5>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                  Show the onboarding guide when you log in if you haven't completed it
                </p>
              </div>
              <Toggle 
                v-model="preferences.showOnboardingOnLogin" 
                @update:modelValue="updatePreferences"
              />
            </div>
            
            <div class="flex items-center justify-between">
              <div>
                <h5 class="text-sm font-medium text-neutral-900 dark:text-white">Enable Feature Tips</h5>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                  Show helpful tips when using features for the first time
                </p>
              </div>
              <Toggle 
                v-model="preferences.enableFeatureTips" 
                @update:modelValue="updatePreferences"
              />
            </div>
            
            <div class="flex items-center justify-between">
              <div>
                <h5 class="text-sm font-medium text-neutral-900 dark:text-white">Enable Contextual Help</h5>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                  Show contextual help buttons throughout the application
                </p>
              </div>
              <Toggle 
                v-model="preferences.enableContextualHelp" 
                @update:modelValue="updatePreferences"
              />
            </div>
          </div>
        </div>
        
        <!-- Feature Tours -->
        <div>
          <h4 class="text-base font-medium text-neutral-900 dark:text-white mb-4">Feature Tours</h4>
          
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 mb-4">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
              Feature tours guide you through specific features of the application. You can restart any tour you've completed.
            </p>
          </div>
          
          <div class="space-y-4">
            <div v-for="(completed, feature) in featureTours" :key="feature" class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
              <div>
                <h5 class="text-sm font-medium text-neutral-900 dark:text-white capitalize">{{ formatFeatureName(feature) }}</h5>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                  {{ completed ? 'Completed' : 'Not completed' }}
                </p>
              </div>
              <button 
                @click="restartTour(feature)" 
                class="px-3 py-1 text-xs bg-primary-600 hover:bg-primary-700 text-white rounded-md"
                :disabled="!completed"
                :class="{ 'opacity-50 cursor-not-allowed': !completed }"
              >
                {{ completed ? 'Restart Tour' : 'Start Tour' }}
              </button>
            </div>
          </div>
        </div>
        
        <!-- Reset Onboarding -->
        <div class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
          <div class="flex items-center justify-between">
            <div>
              <h5 class="text-sm font-medium text-neutral-900 dark:text-white">Reset Onboarding</h5>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Reset all onboarding guides and feature tours
              </p>
            </div>
            <button 
              @click="confirmResetOnboarding" 
              class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded-md"
            >
              Reset All
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Confirmation Modal -->
    <ConfirmationModal
      v-if="showResetConfirmation"
      title="Reset Onboarding"
      message="Are you sure you want to reset all onboarding guides and feature tours? This will show the onboarding guide again on your next login."
      confirm-text="Reset"
      confirm-variant="danger"
      @confirm="resetOnboarding"
      @cancel="showResetConfirmation = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useOnboardingStore } from '../../stores/onboarding';
import { useToast } from '../../composables/useToast';
import Toggle from '../ui/Toggle.vue';
import ConfirmationModal from '../ui/ConfirmationModal.vue';

// Get stores and services
const onboardingStore = useOnboardingStore();
const toast = useToast();

// Component state
const preferences = reactive({
  showOnboardingOnLogin: true,
  enableFeatureTips: true,
  enableContextualHelp: true
});

const featureTours = reactive({});
const showResetConfirmation = ref(false);

// Methods
const initializeSettings = () => {
  // Load preferences from store
  Object.assign(preferences, onboardingStore.userPreferences);
  
  // Load feature tour status
  Object.assign(featureTours, onboardingStore.featureTours);
};

const updatePreferences = () => {
  onboardingStore.updatePreferences(preferences);
  toast.success('Preferences updated');
};

const formatFeatureName = (feature) => {
  return feature.replace(/([A-Z])/g, ' $1')
    .replace(/^./, (str) => str.toUpperCase())
    .replace(/-/g, ' ');
};

const restartTour = (feature) => {
  // Reset the specific feature tour
  onboardingStore.featureTours[feature] = false;
  
  // Save to local storage
  localStorage.setItem('featureTours', JSON.stringify(onboardingStore.featureTours));
  
  // Show confirmation toast
  toast.success(`${formatFeatureName(feature)} tour will start when you next visit that section`);
  
  // Update local state
  featureTours[feature] = false;
};

const confirmResetOnboarding = () => {
  showResetConfirmation.value = true;
};

const resetOnboarding = () => {
  onboardingStore.resetOnboarding();
  
  // Update local state
  initializeSettings();
  
  // Hide confirmation modal
  showResetConfirmation.value = false;
  
  // Show confirmation toast
  toast.success('Onboarding has been reset. You\'ll see the guide on your next login.');
};

// Lifecycle hooks
onMounted(() => {
  initializeSettings();
});
</script>
