import { registerSW } from 'virtual:pwa-register';
import { useToast } from 'vue-toastification';

const toast = useToast();

export const registerServiceWorker = () => {
  if ('serviceWorker' in navigator) {
    const updateSW = registerSW({
      onNeedRefresh() {
        // Show a toast notification when a new version is available
        toast.info(
          {
            component: {
              template: `
                <div>
                  <p class="mb-2">New content is available!</p>
                  <button 
                    @click="updateServiceWorker" 
                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 mr-2"
                  >
                    Update
                  </button>
                  <button 
                    @click="close" 
                    class="px-4 py-2 bg-neutral-200 text-neutral-800 rounded-md hover:bg-neutral-300"
                  >
                    Dismiss
                  </button>
                </div>
              `,
              methods: {
                updateServiceWorker() {
                  updateSW();
                  this.$closeToast();
                },
                close() {
                  this.$closeToast();
                }
              }
            }
          },
          {
            timeout: false,
            closeOnClick: false,
            draggable: false,
            closeButton: false,
          }
        );
      },
      onOfflineReady() {
        // Show a toast notification when offline mode is ready
        toast.success(
          "App is ready for offline use",
          {
            timeout: 3000,
          }
        );
      },
      onRegistered(registration) {
        // Check for updates every hour
        setInterval(() => {
          registration?.update();
        }, 60 * 60 * 1000);
      },
      onRegisterError(error) {
        console.error('Service worker registration error:', error);
      }
    });

    return updateSW;
  }
  
  return null;
};
