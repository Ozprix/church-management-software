/**
 * Responsive Components
 * 
 * This file exports all responsive components for easy importing.
 * It also provides a plugin to register all components globally.
 */

import ResponsiveContainer from './ResponsiveContainer.vue';
import ResponsiveGrid from './ResponsiveGrid.vue';
import ResponsiveShow from './ResponsiveShow.vue';
import ResponsiveHide from './ResponsiveHide.vue';
import { ResponsivePlugin } from '../../services/responsiveService';

// Create a plugin to register all responsive components
const ResponsiveComponentsPlugin = {
  install(app) {
    // Register the responsive service plugin first
    app.use(ResponsivePlugin);
    
    // Register all responsive components globally
    app.component('ResponsiveContainer', ResponsiveContainer);
    app.component('ResponsiveGrid', ResponsiveGrid);
    app.component('ResponsiveShow', ResponsiveShow);
    app.component('ResponsiveHide', ResponsiveHide);
  }
};

// Export individual components
export {
  ResponsiveContainer,
  ResponsiveGrid,
  ResponsiveShow,
  ResponsiveHide,
  ResponsivePlugin,
  ResponsiveComponentsPlugin
};

// Export default plugin
export default ResponsiveComponentsPlugin;
