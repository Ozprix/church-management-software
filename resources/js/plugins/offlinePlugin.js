/**
 * Offline Plugin
 * 
 * This plugin integrates offline functionality into the Vue application.
 * It registers the service worker, provides offline detection,
 * and handles offline data storage and synchronization.
 */

import { OfflinePlugin } from '../services/offlineService';
import OfflineManager from '../components/offline/OfflineManager.vue';

// Create a plugin to register offline functionality
const OfflineAppPlugin = {
  install(app, options = {}) {
    // Register the offline service plugin
    app.use(OfflinePlugin);
    
    // Register the OfflineManager component globally
    app.component('OfflineManager', OfflineManager);
    
    // Add meta tags for PWA
    if (typeof document !== 'undefined') {
      // Add theme-color meta tag
      const themeColorMeta = document.createElement('meta');
      themeColorMeta.name = 'theme-color';
      themeColorMeta.content = options.themeColor || '#2563eb';
      document.head.appendChild(themeColorMeta);
      
      // Add apple-mobile-web-app-capable meta tag
      const appleMobileWebAppCapableMeta = document.createElement('meta');
      appleMobileWebAppCapableMeta.name = 'apple-mobile-web-app-capable';
      appleMobileWebAppCapableMeta.content = 'yes';
      document.head.appendChild(appleMobileWebAppCapableMeta);
      
      // Add apple-mobile-web-app-status-bar-style meta tag
      const appleMobileWebAppStatusBarStyleMeta = document.createElement('meta');
      appleMobileWebAppStatusBarStyleMeta.name = 'apple-mobile-web-app-status-bar-style';
      appleMobileWebAppStatusBarStyleMeta.content = 'black-translucent';
      document.head.appendChild(appleMobileWebAppStatusBarStyleMeta);
      
      // Add manifest link
      const manifestLink = document.createElement('link');
      manifestLink.rel = 'manifest';
      manifestLink.href = '/manifest.json';
      document.head.appendChild(manifestLink);
      
      // Add apple touch icon
      const appleTouchIcon = document.createElement('link');
      appleTouchIcon.rel = 'apple-touch-icon';
      appleTouchIcon.href = '/images/icons/icon-192x192.png';
      document.head.appendChild(appleTouchIcon);
    }
    
    // Add global mixin for offline detection
    app.mixin({
      mounted() {
        // Check if we need to show offline warning
        if (this.$offline && this.$offline.isOfflineMode) {
          this.$toast.info('You are currently offline. Some features may be limited.', {
            timeout: 3000
          });
        }
      }
    });
    
    // Add router navigation guard for offline mode
    if (options.router) {
      options.router.beforeEach((to, from, next) => {
        // Get offline service from app config
        const offline = app.config.globalProperties.$offline;
        
        if (offline && offline.isOfflineMode.value) {
          // Check if the route requires online access
          const requiresOnline = to.meta.requiresOnline;
          
          if (requiresOnline) {
            // Show toast notification
            const toast = app.config.globalProperties.$toast;
            if (toast) {
              toast.warning('This feature requires an internet connection. Please try again when online.', {
                timeout: 5000
              });
            }
            
            // Redirect to offline page or stay on current page
            if (options.offlineFallbackRoute) {
              next({ path: options.offlineFallbackRoute });
            } else {
              next(false);
            }
            return;
          }
        }
        
        // Continue navigation
        next();
      });
    }
    
    // Add Pinia store integration if available
    if (options.pinia) {
      // Subscribe to store actions
      options.pinia.use(({ store }) => {
        // Get offline service from app config
        const offline = app.config.globalProperties.$offline;
        
        if (!offline) return;
        
        // Add $saveOffline method to stores
        store.$saveOffline = async (storeName, data) => {
          if (offline.isOfflineMode.value) {
            switch (storeName) {
              case 'donations':
                return await offline.saveDonationOffline(data);
              case 'attendance':
                return await offline.saveAttendanceOffline(data);
              case 'members':
                return await offline.saveMemberOffline(data);
              default:
                return await offline.storeOfflineData(storeName, data);
            }
          }
          return null;
        };
      });
    }
  }
};

export default OfflineAppPlugin;
