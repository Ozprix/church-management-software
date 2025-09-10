<template>
  <div class="help-tooltip-container">
    <div 
      @mouseenter="showTooltip" 
      @mouseleave="hideTooltip"
      @focus="showTooltip"
      @blur="hideTooltip"
      class="help-icon inline-flex"
      tabindex="0"
      role="button"
      aria-label="Help information"
    >
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        class="h-4 w-4 text-neutral-400 hover:text-primary-500 dark:text-neutral-500 dark:hover:text-primary-400 transition-colors duration-200" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div 
        v-if="isVisible" 
        class="tooltip-content absolute z-50 mt-2 w-64 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 p-4"
        :class="[positionClass]"
        role="tooltip"
      >
        <div class="tooltip-arrow absolute w-3 h-3 bg-white dark:bg-neutral-800 transform rotate-45" :class="arrowPositionClass"></div>
        
        <div class="relative">
          <h3 v-if="title" class="text-sm font-medium text-neutral-900 dark:text-white mb-1">{{ title }}</h3>
          <p class="text-xs text-neutral-600 dark:text-neutral-400">{{ content }}</p>
          
          <div v-if="link" class="mt-2">
            <a 
              :href="link" 
              @click.stop="openLink"
              class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 inline-flex items-center"
            >
              Learn more
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  content: {
    type: String,
    required: true
  },
  link: {
    type: String,
    default: ''
  },
  position: {
    type: String,
    default: 'bottom',
    validator: (value) => ['top', 'right', 'bottom', 'left'].includes(value)
  },
  openInNewTab: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['learn-more']);

// State
const isVisible = ref(false);
const tooltipElement = ref(null);

// Computed properties
const positionClass = computed(() => {
  switch (props.position) {
    case 'top':
      return 'bottom-full mb-2';
    case 'right':
      return 'left-full ml-2';
    case 'left':
      return 'right-full mr-2';
    case 'bottom':
    default:
      return 'top-full mt-2';
  }
});

const arrowPositionClass = computed(() => {
  switch (props.position) {
    case 'top':
      return 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2';
    case 'right':
      return 'left-0 top-1/2 -translate-y-1/2 -translate-x-1/2';
    case 'left':
      return 'right-0 top-1/2 -translate-y-1/2 translate-x-1/2';
    case 'bottom':
    default:
      return 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2';
  }
});

// Methods
const showTooltip = () => {
  isVisible.value = true;
};

const hideTooltip = () => {
  isVisible.value = false;
};

const openLink = (event) => {
  event.preventDefault();
  
  if (props.link) {
    if (props.openInNewTab) {
      window.open(props.link, '_blank');
    } else {
      emit('learn-more', props.link);
    }
  }
};

// Handle clicks outside to close tooltip
const handleClickOutside = (event) => {
  if (tooltipElement.value && !tooltipElement.value.contains(event.target)) {
    hideTooltip();
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.help-tooltip-container {
  position: relative;
  display: inline-block;
}

.help-icon {
  cursor: help;
}
</style>
