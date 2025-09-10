<template>
  <div 
    :class="[
      'fixed z-50 flex items-center p-4 rounded-lg shadow-lg transition-all duration-300 transform',
      variantClasses,
      positionClasses,
      { 'opacity-0 translate-y-2': !isVisible, 'opacity-100 translate-y-0': isVisible }
    ]"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <!-- Icon -->
    <div v-if="icon || $slots.icon" class="flex-shrink-0 mr-3">
      <slot name="icon">
        <svg v-if="variant === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <svg v-else-if="variant === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <svg v-else-if="variant === 'warning'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <svg v-else-if="variant === 'info'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
      </slot>
    </div>
    
    <!-- Content -->
    <div class="flex-grow">
      <div v-if="title" class="font-medium">{{ title }}</div>
      <div v-if="message || $slots.default" class="text-sm">
        <slot>{{ message }}</slot>
      </div>
    </div>
    
    <!-- Close button -->
    <button 
      v-if="dismissible" 
      @click="dismiss"
      class="flex-shrink-0 ml-3 p-1 rounded-full hover:bg-black/10 dark:hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      aria-label="Close"
    >
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'error', 'warning', 'info'].includes(value)
  },
  position: {
    type: String,
    default: 'bottom-right',
    validator: (value) => [
      'top-left', 'top-center', 'top-right',
      'bottom-left', 'bottom-center', 'bottom-right'
    ].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  duration: {
    type: Number,
    default: 5000 // 5 seconds
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  icon: {
    type: Boolean,
    default: true
  },
  autoClose: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close']);

// State
const isVisible = ref(false);
let autoCloseTimeout = null;

// Computed properties
const variantClasses = computed(() => {
  const classes = {
    default: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100',
    primary: 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-100',
    success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
    error: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
    warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'
  };
  
  return classes[props.variant];
});

const positionClasses = computed(() => {
  const positions = {
    'top-left': 'top-4 left-4',
    'top-center': 'top-4 left-1/2 -translate-x-1/2',
    'top-right': 'top-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2',
    'bottom-right': 'bottom-4 right-4'
  };
  
  return positions[props.position];
});

// Methods
const dismiss = () => {
  isVisible.value = false;
  setTimeout(() => {
    emit('close');
  }, 300); // Wait for transition to complete
};

const startAutoCloseTimer = () => {
  if (props.autoClose && props.duration > 0) {
    autoCloseTimeout = setTimeout(() => {
      dismiss();
    }, props.duration);
  }
};

// Lifecycle hooks
onMounted(() => {
  // Show toast with a slight delay for animation
  setTimeout(() => {
    isVisible.value = true;
  }, 100);
  
  startAutoCloseTimer();
});

onBeforeUnmount(() => {
  if (autoCloseTimeout) {
    clearTimeout(autoCloseTimeout);
  }
});

// Watch for changes to duration or autoClose
watch(() => props.duration, () => {
  if (autoCloseTimeout) {
    clearTimeout(autoCloseTimeout);
  }
  startAutoCloseTimer();
});

watch(() => props.autoClose, (newValue) => {
  if (newValue) {
    startAutoCloseTimer();
  } else if (autoCloseTimeout) {
    clearTimeout(autoCloseTimeout);
  }
});

// Expose methods
defineExpose({
  dismiss
});
</script>
