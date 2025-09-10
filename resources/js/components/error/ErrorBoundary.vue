<template>
  <div>
    <!-- Render default slot when there's no error -->
    <slot v-if="!error"></slot>
    
    <!-- Error UI when an error occurs -->
    <div v-else class="error-boundary bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-10 w-10 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-neutral-900 dark:text-white">{{ errorTitle }}</h3>
          <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            <p>{{ errorMessage }}</p>
          </div>
          
          <!-- Technical details (collapsible) -->
          <div v-if="showDetails" class="mt-4">
            <button 
              @click="toggleDetails" 
              class="flex items-center text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white focus:outline-none"
            >
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="h-4 w-4 mr-1 transition-transform duration-200" 
                :class="{ 'rotate-90': detailsExpanded }"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Technical Details
            </button>
            
            <div v-if="detailsExpanded" class="mt-2 p-3 bg-neutral-50 dark:bg-neutral-900 rounded-md overflow-auto max-h-64">
              <pre class="text-xs text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ errorDetails }}</pre>
            </div>
          </div>
          
          <!-- Action buttons -->
          <div class="mt-6 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0">
            <button 
              @click="retry" 
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Try Again
            </button>
            <button 
              @click="goBack" 
              class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Go Back
            </button>
            <button 
              v-if="showReportButton"
              @click="reportError" 
              class="inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Report Issue
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onErrorCaptured, provide } from 'vue';
import { useRouter } from 'vue-router';
import { useToastService } from '../../services/toastService';

const props = defineProps({
  fallbackComponent: {
    type: Object,
    default: null
  },
  showDetails: {
    type: Boolean,
    default: true
  },
  showReportButton: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['error', 'retry', 'report']);

// State
const error = ref(null);
const errorInfo = ref(null);
const detailsExpanded = ref(false);
const router = useRouter();
const toast = useToastService();

// Computed properties
const errorTitle = computed(() => {
  if (!error.value) return 'An error occurred';
  
  // Extract meaningful error title
  if (error.value.name === 'NetworkError') {
    return 'Network Error';
  } else if (error.value.name === 'AuthenticationError') {
    return 'Authentication Error';
  } else if (error.value.name === 'ValidationError') {
    return 'Validation Error';
  } else if (error.value.name === 'NotFoundError') {
    return 'Not Found Error';
  } else if (error.value.name === 'PermissionError') {
    return 'Permission Denied';
  } else {
    return error.value.name || 'Application Error';
  }
});

const errorMessage = computed(() => {
  if (!error.value) return 'Something went wrong. Please try again.';
  
  // Extract meaningful error message
  if (error.value.name === 'NetworkError') {
    return 'Unable to connect to the server. Please check your internet connection and try again.';
  } else if (error.value.name === 'AuthenticationError') {
    return 'Your session has expired or you do not have permission to access this resource.';
  } else if (error.value.name === 'ValidationError') {
    return 'There was an issue with the data you provided. Please check your inputs and try again.';
  } else if (error.value.name === 'NotFoundError') {
    return 'The resource you are looking for could not be found.';
  } else if (error.value.name === 'PermissionError') {
    return 'You do not have permission to perform this action.';
  } else {
    return error.value.message || 'An unexpected error occurred. Please try again or contact support.';
  }
});

const errorDetails = computed(() => {
  if (!error.value) return '';
  
  let details = '';
  
  // Add error name and message
  details += `Error: ${error.value.name || 'Unknown Error'}\n`;
  details += `Message: ${error.value.message || 'No message provided'}\n\n`;
  
  // Add stack trace if available
  if (error.value.stack) {
    details += `Stack Trace:\n${error.value.stack}\n\n`;
  }
  
  // Add component info if available
  if (errorInfo.value && errorInfo.value.component) {
    details += `Component: ${errorInfo.value.component.name || 'Unknown'}\n`;
  }
  
  // Add additional info
  if (errorInfo.value && errorInfo.value.props) {
    details += `Props: ${JSON.stringify(errorInfo.value.props, null, 2)}\n`;
  }
  
  return details;
});

// Methods
const toggleDetails = () => {
  detailsExpanded.value = !detailsExpanded.value;
};

const retry = () => {
  error.value = null;
  errorInfo.value = null;
  emit('retry');
  
  // Refresh the current route
  const currentRoute = router.currentRoute.value;
  router.replace({ path: currentRoute.path, query: { ...currentRoute.query, _t: Date.now() } });
};

const goBack = () => {
  router.back();
};

const reportError = () => {
  emit('report', { error: error.value, info: errorInfo.value });
  
  // In a real implementation, you would send the error to your error tracking service
  // For now, we'll just show a toast
  toast.info('Error report submitted. Thank you for helping us improve!');
  
  // Reset error state
  error.value = null;
  errorInfo.value = null;
};

// Error handling
onErrorCaptured((err, instance, info) => {
  error.value = err;
  errorInfo.value = {
    component: instance,
    info: info,
    props: instance ? instance.$props : null
  };
  
  emit('error', { error: err, info: errorInfo.value });
  
  // Log error to console in development
  if (process.env.NODE_ENV !== 'production') {
    console.error('Error captured by ErrorBoundary:', err);
    console.info('Component:', instance);
    console.info('Error Info:', info);
  }
  
  // Prevent error from propagating further
  return false;
});

// Provide error handling context to child components
provide('errorBoundary', {
  captureError: (err, info) => {
    error.value = err;
    errorInfo.value = info;
    emit('error', { error: err, info });
  }
});
</script>

<style scoped>
/* Add any additional styling here */
</style>
