/**
 * Offline Components
 * 
 * This file exports all offline components for easy importing.
 * It also provides a plugin to register all components globally.
 */

import OfflineManager from './OfflineManager.vue';
import OfflineAttendanceForm from './OfflineAttendanceForm.vue';
import OfflineModeToggle from './OfflineModeToggle.vue';
import OfflineSettings from './OfflineSettings.vue';
import { OfflinePlugin } from '../../services/offlineService';

// Create a plugin to register all offline components
const OfflineComponentsPlugin = {
  install(app) {
    // Register the offline service plugin first
    app.use(OfflinePlugin);
    
    // Register all offline components globally
    app.component('OfflineManager', OfflineManager);
    app.component('OfflineAttendanceForm', OfflineAttendanceForm);
    app.component('OfflineModeToggle', OfflineModeToggle);
    app.component('OfflineSettings', OfflineSettings);
  }
};

// Export individual components
export {
  OfflineManager,
  OfflineAttendanceForm,
  OfflineModeToggle,
  OfflineSettings,
  OfflinePlugin,
  OfflineComponentsPlugin
};

// Export default plugin
export default OfflineComponentsPlugin;
