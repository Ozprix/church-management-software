import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { format, formatDistance } from 'date-fns';

// Import optimization, responsive, offline, and i18n plugins
import PerformancePlugin from './plugins/performancePlugin';
import ResponsiveComponentsPlugin from './components/responsive';
import OfflinePlugin from './plugins/offlinePlugin';
import I18nComponentsPlugin from './components/i18n';

// Create Pinia store
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(router);
app.use(pinia);
app.use(Toast, {
  transition: "Vue-Toastification__bounce",
  maxToasts: 3,
  newestOnTop: true
});

// Use performance optimization plugin
app.use(PerformancePlugin, {
  enablePerformanceMonitoring: process.env.NODE_ENV !== 'production', // Only enable in development
  preconnectDomains: [
    // Add common third-party domains used in your app
    'https://fonts.googleapis.com',
    'https://fonts.gstatic.com'
  ]
});

// Use responsive components plugin
app.use(ResponsiveComponentsPlugin);

// Use offline plugin with router and pinia integration
app.use(OfflinePlugin, {
  router,
  pinia,
  themeColor: '#2563eb',
  offlineFallbackRoute: '/offline'
});

// Use i18n components plugin
app.use(I18nComponentsPlugin);

// Performance optimization: Preload critical assets
if (typeof window !== 'undefined') {
  // Preload critical fonts
  const fontPreload = document.createElement('link');
  fontPreload.rel = 'preload';
  fontPreload.href = '/fonts/figtree-v1-latin-regular.woff2';
  fontPreload.as = 'font';
  fontPreload.type = 'font/woff2';
  fontPreload.crossOrigin = 'anonymous';
  document.head.appendChild(fontPreload);
  
  // Add date-fns to window for offline use
  window.dateFns = { format, formatDistance };
}

// Mount app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Check if the mounting element exists
  const mountEl = document.getElementById('app');
  if (mountEl) {
    // Performance mark for measuring initial render
    if (window.performance && window.performance.mark) {
      window.performance.mark('vue-app-mount-start');
    }
    
    app.mount('#app');
    
    // Performance mark for measuring initial render
    if (window.performance && window.performance.mark) {
      window.performance.mark('vue-app-mount-end');
      window.performance.measure('vue-app-mount', 'vue-app-mount-start', 'vue-app-mount-end');
      console.log('App mounted in:', performance.getEntriesByName('vue-app-mount')[0].duration.toFixed(2), 'ms');
    }
  } else {
    console.error('Mounting element #app not found');
  }
});

export { app, router, pinia };
