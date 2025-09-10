<template>
  <div 
    :class="[
      'loading-spinner flex items-center justify-center',
      { 'inline-flex': inline, 'flex': !inline },
      sizeClasses
    ]"
  >
    <div v-if="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 max-w-sm mx-auto z-50">
        <div class="flex flex-col items-center">
          <div class="spinner-container">
            <svg 
              :class="[
                'animate-spin text-primary-600 dark:text-primary-400',
                spinnerSizeClasses
              ]" 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <p v-if="message" class="mt-3 text-neutral-700 dark:text-neutral-300 text-center">{{ message }}</p>
        </div>
      </div>
    </div>
    
    <template v-else>
      <svg 
        :class="[
          'animate-spin text-primary-600 dark:text-primary-400',
          spinnerSizeClasses
        ]" 
        xmlns="http://www.w3.org/2000/svg" 
        fill="none" 
        viewBox="0 0 24 24"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span v-if="message" :class="['ml-2 text-neutral-700 dark:text-neutral-300', textSizeClasses]">{{ message }}</span>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  message: {
    type: String,
    default: ''
  },
  inline: {
    type: Boolean,
    default: false
  },
  overlay: {
    type: Boolean,
    default: false
  }
});

// Computed properties
const spinnerSizeClasses = computed(() => {
  const sizes = {
    'xs': 'h-3 w-3',
    'sm': 'h-4 w-4',
    'md': 'h-6 w-6',
    'lg': 'h-8 w-8',
    'xl': 'h-10 w-10'
  };
  
  return sizes[props.size];
});

const textSizeClasses = computed(() => {
  const sizes = {
    'xs': 'text-xs',
    'sm': 'text-sm',
    'md': 'text-base',
    'lg': 'text-lg',
    'xl': 'text-xl'
  };
  
  return sizes[props.size];
});

const sizeClasses = computed(() => {
  if (props.inline) {
    return '';
  }
  
  const sizes = {
    'xs': 'h-4',
    'sm': 'h-6',
    'md': 'h-8',
    'lg': 'h-10',
    'xl': 'h-12'
  };
  
  return sizes[props.size];
});
</script>

<style scoped>
.loading-spinner {
  position: relative;
}

.loading-spinner.inline {
  display: inline-flex;
}
</style>
