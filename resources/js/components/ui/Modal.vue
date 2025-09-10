<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div 
        v-if="modelValue" 
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeOnBackdrop && close()"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal container -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <!-- Modal content -->
          <div 
            :class="[
              'relative max-h-[90vh] overflow-y-auto rounded-lg bg-white dark:bg-neutral-800 shadow-xl transition-all transform',
              sizeClasses,
              { 'animate-bounce-in': animateIn }
            ]"
            @click.stop
            ref="modalRef"
          >
            <!-- Close button -->
            <button 
              v-if="showCloseButton" 
              @click="close"
              class="absolute top-3 right-3 p-1 rounded-full text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
              aria-label="Close modal"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
            
            <!-- Header -->
            <div v-if="$slots.header || title" class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
              <slot name="header">
                <h3 :id="titleId" class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                  {{ title }}
                </h3>
              </slot>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-4">
              <slot></slot>
            </div>
            
            <!-- Footer -->
            <div v-if="$slots.footer" class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 rounded-b-lg">
              <slot name="footer"></slot>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useSettingsStore } from '../../stores/settings';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeOnEsc: {
    type: Boolean,
    default: true
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  animateIn: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'close']);

// Unique ID for accessibility
const titleId = ref(`modal-title-${uuidv4()}`);
const modalRef = ref(null);
const settingsStore = useSettingsStore();

// Computed properties
const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-4xl'
  };
  
  return sizes[props.size] || sizes.md;
});

// Methods
const close = () => {
  if (props.persistent) return;
  
  emit('update:modelValue', false);
  emit('close');
};

const handleEscKey = (event) => {
  if (event.key === 'Escape' && props.closeOnEsc && props.modelValue) {
    close();
  }
};

// Focus trap
const handleTabKey = (event) => {
  if (!modalRef.value || !props.modelValue) return;
  
  const focusableElements = modalRef.value.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];
  
  // If no focusable elements, do nothing
  if (focusableElements.length === 0) return;
  
  // Handle tab navigation
  if (event.key === 'Tab') {
    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  }
};

// Save previous active element to restore focus
let previousActiveElement = null;

// Watch for modal open/close
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    // Save current active element
    previousActiveElement = document.activeElement;
    
    // Add event listeners
    document.addEventListener('keydown', handleEscKey);
    document.addEventListener('keydown', handleTabKey);
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    
    // Focus first focusable element in modal
    nextTick(() => {
      if (modalRef.value) {
        const firstFocusable = modalRef.value.querySelector(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (firstFocusable) {
          firstFocusable.focus();
        } else {
          modalRef.value.focus();
        }
      }
    });
  } else {
    // Remove event listeners
    document.removeEventListener('keydown', handleEscKey);
    document.removeEventListener('keydown', handleTabKey);
    
    // Restore body scrolling
    document.body.style.overflow = '';
    
    // Restore focus
    if (previousActiveElement) {
      previousActiveElement.focus();
    }
  }
});

// Clean up on component unmount
onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleEscKey);
  document.removeEventListener('keydown', handleTabKey);
  
  // Restore body scrolling if modal was open
  if (props.modelValue) {
    document.body.style.overflow = '';
  }
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .transform,
.modal-fade-leave-to .transform {
  transform: scale(0.95);
  transition: transform 0.3s ease;
}

.animate-bounce-in {
  animation: bounce-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes bounce-in {
  0% {
    transform: scale(0.9);
    opacity: 0;
  }
  70% {
    transform: scale(1.05);
    opacity: 1;
  }
  100% {
    transform: scale(1);
  }
}
</style>
