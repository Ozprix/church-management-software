import { ref, reactive } from 'vue';

// Create a reactive state for toasts
const toasts = ref([]);
let toastId = 0;

// Toast service with composition API
export const useToastService = () => {
  // Show a toast notification
  const show = ({ type = 'info', title = '', message = '', duration = 5000 }) => {
    const id = ++toastId;
    
    // Add toast to the array
    toasts.value.push({
      id,
      type,
      title,
      message,
      visible: true,
      timestamp: new Date()
    });
    
    // Auto-remove toast after duration
    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }
    
    return id;
  };
  
  // Remove a toast by id
  const remove = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index !== -1) {
      // Mark as not visible first (for animation)
      toasts.value[index].visible = false;
      
      // Remove after animation completes
      setTimeout(() => {
        toasts.value = toasts.value.filter(toast => toast.id !== id);
      }, 300);
    }
  };
  
  // Clear all toasts
  const clear = () => {
    toasts.value.forEach(toast => {
      toast.visible = false;
    });
    
    setTimeout(() => {
      toasts.value = [];
    }, 300);
  };
  
  // Helper methods for common toast types
  const success = (message, title = 'Success') => show({ type: 'success', title, message });
  const error = (message, title = 'Error') => show({ type: 'error', title, message });
  const warning = (message, title = 'Warning') => show({ type: 'warning', title, message });
  const info = (message, title = 'Information') => show({ type: 'info', title, message });
  
  return {
    toasts,
    show,
    remove,
    clear,
    success,
    error,
    warning,
    info
  };
};

// Export a singleton instance for global use
export const toastService = useToastService();
