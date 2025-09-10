<template>
  <div 
    v-if="loading" 
    class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-neutral-900 transition-opacity duration-500"
    :class="{ 'opacity-0 pointer-events-none': !loading }"
  >
    <div class="text-center">
      <div class="relative mb-6">
        <!-- Sacred geometry animation -->
        <svg class="w-24 h-24 mx-auto" viewBox="0 0 100 100">
          <!-- Outer circle -->
          <circle 
            cx="50" 
            cy="50" 
            r="45" 
            fill="none" 
            stroke="currentColor" 
            class="text-primary-200 dark:text-primary-900"
            stroke-width="2"
          />
          
          <!-- Inner hexagon that rotates -->
          <polygon 
            points="50,5 87.5,25 87.5,75 50,95 12.5,75 12.5,25" 
            fill="none" 
            stroke="currentColor" 
            class="text-primary-500 dark:text-primary-400 animate-spin-slow origin-center"
            stroke-width="2"
          />
          
          <!-- Inner triangle that pulses -->
          <polygon 
            points="50,15 80,70 20,70" 
            fill="none" 
            stroke="currentColor" 
            class="text-secondary-500 dark:text-secondary-400 animate-pulse-soft"
            stroke-width="2"
          />
          
          <!-- Center circle -->
          <circle 
            cx="50" 
            cy="50" 
            r="8" 
            fill="currentColor" 
            class="text-primary-600 dark:text-primary-500"
          />
        </svg>
        
        <!-- Circular progress indicator -->
        <svg class="w-32 h-32 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 -rotate-90" viewBox="0 0 100 100">
          <circle 
            cx="50" 
            cy="50" 
            r="45" 
            fill="none" 
            stroke="currentColor" 
            stroke-width="3" 
            class="text-neutral-200 dark:text-neutral-800"
          />
          <circle 
            cx="50" 
            cy="50" 
            r="45" 
            fill="none" 
            stroke="currentColor" 
            stroke-width="3" 
            :stroke-dasharray="283" 
            :stroke-dashoffset="283 - (progress / 100) * 283" 
            class="text-primary-600 dark:text-primary-400 transition-all duration-300 ease-in-out"
          />
        </svg>
      </div>
      
      <h2 class="text-xl font-semibold text-primary-700 dark:text-primary-300 mb-2">{{ message || 'Loading...' }}</h2>
      <p class="text-neutral-500 dark:text-neutral-400">{{ subMessage || 'Please wait while we prepare your experience' }}</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';

export default {
  name: 'LoadingScreen',
  
  props: {
    loading: {
      type: Boolean,
      default: true
    },
    message: {
      type: String,
      default: ''
    },
    subMessage: {
      type: String,
      default: ''
    },
    duration: {
      type: Number,
      default: 2000 // Default loading time in ms
    },
    autoProgress: {
      type: Boolean,
      default: true
    }
  },
  
  emits: ['progress-complete'],
  
  setup(props, { emit }) {
    const progress = ref(0);
    let progressInterval = null;
    
    const startProgressAnimation = () => {
      if (props.autoProgress) {
        const increment = 100 / (props.duration / 50); // Update every 50ms
        progressInterval = setInterval(() => {
          progress.value += increment;
          
          if (progress.value >= 100) {
            progress.value = 100;
            clearInterval(progressInterval);
            emit('progress-complete');
          }
        }, 50);
      }
    };
    
    onMounted(() => {
      startProgressAnimation();
    });
    
    onBeforeUnmount(() => {
      if (progressInterval) {
        clearInterval(progressInterval);
      }
    });
    
    return {
      progress
    };
  }
};
</script>
