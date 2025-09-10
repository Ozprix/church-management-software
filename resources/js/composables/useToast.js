import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
  const addToast = (toast) => {
    const id = Date.now() + Math.random();
    const newToast = {
      id,
      type: 'info',
      duration: 5000,
      ...toast
    };
    
    toasts.value.push(newToast);
    
    if (newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, newToast.duration);
    }
    
    return id;
  };
  
  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };
  
  const success = (message, options = {}) => {
    return addToast({ ...options, type: 'success', message });
  };
  
  const error = (message, options = {}) => {
    return addToast({ ...options, type: 'error', message });
  };
  
  const warning = (message, options = {}) => {
    return addToast({ ...options, type: 'warning', message });
  };
  
  const info = (message, options = {}) => {
    return addToast({ ...options, type: 'info', message });
  };
  
  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info
  };
}
