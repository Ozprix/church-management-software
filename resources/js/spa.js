import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

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

// Mount app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Check if the mounting element exists
  const mountEl = document.getElementById('app');
  if (mountEl) {
    app.mount('#app');
    console.log('Vue application mounted successfully');
  } else {
    console.error('Mounting element #app not found');
  }
});

export { app, router, store };
