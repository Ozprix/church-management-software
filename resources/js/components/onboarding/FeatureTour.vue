<template>
  <div class="feature-tour">
    <!-- Tour Overlay -->
    <div v-if="isActive" class="fixed inset-0 z-50 pointer-events-none">
      <!-- Highlight Target Element -->
      <div 
        v-if="currentStep && targetElement"
        class="absolute pointer-events-none"
        :style="highlightStyles"
      >
        <!-- Pulse Animation -->
        <div class="absolute inset-0 border-2 border-primary-500 rounded-md animate-pulse"></div>
      </div>
      
      <!-- Tour Tooltip -->
      <div 
        v-if="currentStep"
        class="absolute bg-white dark:bg-neutral-800 rounded-lg shadow-xl border border-neutral-200 dark:border-neutral-700 p-4 max-w-xs pointer-events-auto"
        :style="tooltipStyles"
      >
        <!-- Step Content -->
        <div class="mb-3">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1">
            {{ currentStep.title }}
          </h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">
            {{ currentStep.description }}
          </p>
        </div>
        
        <!-- Progress Indicator -->
        <div class="flex items-center justify-between mb-3">
          <div class="flex space-x-1">
            <span 
              v-for="(_, index) in steps" 
              :key="index"
              class="w-2 h-2 rounded-full"
              :class="index === currentStepIndex ? 'bg-primary-500' : 'bg-neutral-300 dark:bg-neutral-600'"
            ></span>
          </div>
          <div class="text-xs text-neutral-500 dark:text-neutral-400">
            {{ currentStepIndex + 1 }} / {{ steps.length }}
          </div>
        </div>
        
        <!-- Actions -->
        <div class="flex justify-between">
          <button 
            v-if="currentStepIndex > 0"
            @click="prevStep" 
            class="text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white"
          >
            Back
          </button>
          <div class="flex-grow"></div>
          <button 
            @click="skipTour" 
            class="text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white mr-3"
          >
            Skip
          </button>
          <button 
            @click="nextStep" 
            class="px-3 py-1 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-md"
          >
            {{ isLastStep ? 'Finish' : 'Next' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useOnboardingStore } from '../../stores/onboarding';

// Props
const props = defineProps({
  tourId: {
    type: String,
    required: true
  },
  steps: {
    type: Array,
    required: true,
    validator: (steps) => {
      return steps.every(step => 
        step.hasOwnProperty('title') && 
        step.hasOwnProperty('description') && 
        step.hasOwnProperty('target')
      );
    }
  },
  autoStart: {
    type: Boolean,
    default: false
  },
  startDelay: {
    type: Number,
    default: 500
  }
});

// Emits
const emit = defineEmits(['complete', 'skip', 'start']);

// Get onboarding store
const onboardingStore = useOnboardingStore();

// State
const isActive = ref(false);
const currentStepIndex = ref(0);
const targetElement = ref(null);
const tooltipPosition = ref({ top: 0, left: 0 });
const resizeObserver = ref(null);

// Computed
const currentStep = computed(() => {
  if (!props.steps || props.steps.length === 0) return null;
  return props.steps[currentStepIndex.value] || null;
});

const isLastStep = computed(() => {
  return currentStepIndex.value === props.steps.length - 1;
});

const highlightStyles = computed(() => {
  if (!targetElement.value) return {};
  
  const rect = targetElement.value.getBoundingClientRect();
  
  return {
    top: `${rect.top - 4}px`,
    left: `${rect.left - 4}px`,
    width: `${rect.width + 8}px`,
    height: `${rect.height + 8}px`,
    borderRadius: '0.375rem'
  };
});

const tooltipStyles = computed(() => {
  if (!targetElement.value) return {};
  
  const targetRect = targetElement.value.getBoundingClientRect();
  const position = currentStep.value.position || 'bottom';
  
  // Calculate tooltip dimensions (approximate)
  const tooltipWidth = 320;
  const tooltipHeight = 150;
  
  // Calculate viewport dimensions
  const viewportWidth = window.innerWidth;
  const viewportHeight = window.innerHeight;
  
  // Default positions for each placement
  const positions = {
    top: {
      top: targetRect.top - tooltipHeight - 12,
      left: targetRect.left + (targetRect.width / 2) - (tooltipWidth / 2)
    },
    right: {
      top: targetRect.top + (targetRect.height / 2) - (tooltipHeight / 2),
      left: targetRect.right + 12
    },
    bottom: {
      top: targetRect.bottom + 12,
      left: targetRect.left + (targetRect.width / 2) - (tooltipWidth / 2)
    },
    left: {
      top: targetRect.top + (targetRect.height / 2) - (tooltipHeight / 2),
      left: targetRect.left - tooltipWidth - 12
    }
  };
  
  // Get position for the current placement
  let { top, left } = positions[position];
  
  // Adjust if tooltip would go off screen
  if (left < 16) left = 16;
  if (left + tooltipWidth > viewportWidth - 16) left = viewportWidth - tooltipWidth - 16;
  if (top < 16) top = 16;
  if (top + tooltipHeight > viewportHeight - 16) top = viewportHeight - tooltipHeight - 16;
  
  return {
    top: `${top}px`,
    left: `${left}px`,
    width: `${tooltipWidth}px`,
    zIndex: 9999
  };
});

// Methods
const startTour = () => {
  if (onboardingStore.featureTours[props.tourId]) {
    // Skip if tour already completed
    return;
  }
  
  currentStepIndex.value = 0;
  isActive.value = true;
  updateTargetElement();
  emit('start');
};

const nextStep = () => {
  if (isLastStep.value) {
    completeTour();
  } else {
    currentStepIndex.value++;
    updateTargetElement();
  }
};

const prevStep = () => {
  if (currentStepIndex.value > 0) {
    currentStepIndex.value--;
    updateTargetElement();
  }
};

const skipTour = () => {
  isActive.value = false;
  emit('skip');
};

const completeTour = () => {
  isActive.value = false;
  onboardingStore.markFeatureTourCompleted(props.tourId);
  emit('complete');
};

const updateTargetElement = async () => {
  await nextTick();
  
  if (!currentStep.value) return;
  
  const selector = currentStep.value.target;
  if (!selector) return;
  
  // Try to find the target element
  targetElement.value = document.querySelector(selector);
  
  // Scroll element into view if needed
  if (targetElement.value) {
    const rect = targetElement.value.getBoundingClientRect();
    
    // Check if element is in viewport
    const isInViewport = (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= window.innerHeight &&
      rect.right <= window.innerWidth
    );
    
    if (!isInViewport) {
      targetElement.value.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }
  }
};

const handleResize = () => {
  updateTargetElement();
};

// Lifecycle hooks
onMounted(() => {
  // Start tour after delay if autoStart is true
  if (props.autoStart) {
    setTimeout(() => {
      startTour();
    }, props.startDelay);
  }
  
  // Set up resize observer to update positions when window resizes
  window.addEventListener('resize', handleResize);
  
  // Set up mutation observer to detect DOM changes
  resizeObserver.value = new ResizeObserver(handleResize);
  if (document.body) {
    resizeObserver.value.observe(document.body);
  }
});

onBeforeUnmount(() => {
  // Clean up event listeners
  window.removeEventListener('resize', handleResize);
  
  // Clean up resize observer
  if (resizeObserver.value) {
    resizeObserver.value.disconnect();
  }
});

// Watch for changes in steps
watch(() => props.steps, () => {
  if (isActive.value) {
    updateTargetElement();
  }
});

// Expose methods to parent
defineExpose({
  startTour,
  skipTour,
  completeTour
});
</script>

<style scoped>
.feature-tour {
  position: relative;
}

@keyframes pulse {
  0% {
    opacity: 0.7;
    transform: scale(1);
  }
  50% {
    opacity: 0.9;
    transform: scale(1.05);
  }
  100% {
    opacity: 0.7;
    transform: scale(1);
  }
}

.animate-pulse {
  animation: pulse 2s infinite;
}
</style>
