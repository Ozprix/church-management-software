import { createApp, h } from 'vue';
import Toast from '../components/ui/Toast.vue';

// Toast container ID
const TOAST_CONTAINER_ID = 'toast-container';

// Toast positions
const POSITIONS = {
  'top-left': 'top-left',
  'top-center': 'top-center',
  'top-right': 'top-right',
  'bottom-left': 'bottom-left',
  'bottom-center': 'bottom-center',
  'bottom-right': 'bottom-right'
};

// Toast variants
const VARIANTS = {
  default: 'default',
  primary: 'primary',
  success: 'success',
  error: 'error',
  warning: 'warning',
  info: 'info'
};

// Create toast container if it doesn't exist
const getOrCreateContainer = (position) => {
  const containerId = `${TOAST_CONTAINER_ID}-${position}`;
  let container = document.getElementById(containerId);
  
  if (!container) {
    container = document.createElement('div');
    container.id = containerId;
    container.className = 'fixed z-50 flex flex-col gap-4';
    
    // Position the container
    switch (position) {
      case POSITIONS['top-left']:
        container.style.top = '1rem';
        container.style.left = '1rem';
        break;
      case POSITIONS['top-center']:
        container.style.top = '1rem';
        container.style.left = '50%';
        container.style.transform = 'translateX(-50%)';
        break;
      case POSITIONS['top-right']:
        container.style.top = '1rem';
        container.style.right = '1rem';
        break;
      case POSITIONS['bottom-left']:
        container.style.bottom = '1rem';
        container.style.left = '1rem';
        break;
      case POSITIONS['bottom-center']:
        container.style.bottom = '1rem';
        container.style.left = '50%';
        container.style.transform = 'translateX(-50%)';
        break;
      case POSITIONS['bottom-right']:
      default:
        container.style.bottom = '1rem';
        container.style.right = '1rem';
        break;
    }
    
    document.body.appendChild(container);
  }
  
  return container;
};

// Create a toast
const createToast = (options = {}) => {
  const {
    title = '',
    message = '',
    variant = VARIANTS.default,
    position = POSITIONS['bottom-right'],
    duration = 5000,
    dismissible = true,
    icon = true,
    autoClose = true,
    onClose = () => {}
  } = options;
  
  // Create a div to mount the toast
  const mountPoint = document.createElement('div');
  const container = getOrCreateContainer(position);
  container.appendChild(mountPoint);
  
  // Create the toast instance
  const toastApp = createApp({
    render() {
      return h(Toast, {
        title,
        message,
        variant,
        position,
        duration,
        dismissible,
        icon,
        autoClose,
        onClose: () => {
          // Clean up when toast is closed
          toastApp.unmount();
          container.removeChild(mountPoint);
          onClose();
          
          // Remove container if it's empty
          if (container.children.length === 0) {
            document.body.removeChild(container);
          }
        }
      });
    }
  });
  
  // Mount the toast
  toastApp.mount(mountPoint);
  
  // Return a function to dismiss the toast
  return {
    dismiss: () => {
      const toastInstance = toastApp._instance.exposed;
      if (toastInstance && toastInstance.dismiss) {
        toastInstance.dismiss();
      }
    }
  };
};

// Toast service
const toastService = {
  // Show a default toast
  show(message, options = {}) {
    return createToast({ message, ...options });
  },
  
  // Show a success toast
  success(message, options = {}) {
    return createToast({ message, variant: VARIANTS.success, ...options });
  },
  
  // Show an error toast
  error(message, options = {}) {
    return createToast({ message, variant: VARIANTS.error, ...options });
  },
  
  // Show a warning toast
  warning(message, options = {}) {
    return createToast({ message, variant: VARIANTS.warning, ...options });
  },
  
  // Show an info toast
  info(message, options = {}) {
    return createToast({ message, variant: VARIANTS.info, ...options });
  },
  
  // Show a primary toast
  primary(message, options = {}) {
    return createToast({ message, variant: VARIANTS.primary, ...options });
  },
  
  // Constants
  POSITIONS,
  VARIANTS
};

export default toastService;
