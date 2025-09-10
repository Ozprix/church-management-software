import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import pinia from './stores';
import { registerServiceWorker } from './utils/registerSW';

// Import UI components
import Button from './components/ui/Button.vue';
import Card from './components/ui/Card.vue';
import Badge from './components/ui/Badge.vue';
import Skeleton from './components/ui/Skeleton.vue';
import DarkModeToggle from './components/ui/DarkModeToggle.vue';

// Configure axios
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Add auth token from localStorage if it exists
const authToken = localStorage.getItem('token');
if (authToken) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

// Handle 401 responses globally
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            router.push('/auth/login');
        }
        return Promise.reject(error);
    }
);

// Create Vue app
const app = createApp(App);

// Register global components
app.component('AppButton', Button);
app.component('AppCard', Card);
app.component('AppBadge', Badge);
app.component('AppSkeleton', Skeleton);
app.component('DarkModeToggle', DarkModeToggle);

// Use plugins
app.use(router);
app.use(pinia);
app.use(Toast, {
    transition: "Vue-Toastification__bounce",
    maxToasts: 3,
    newestOnTop: true,
    toastClassName: 'dark:!bg-neutral-800 dark:!text-neutral-100',
    bodyClassName: 'dark:!text-neutral-100',
    closeButtonClassName: 'dark:!text-neutral-300'
});

// Provide axios globally
app.provide('axios', axios);
app.config.globalProperties.$axios = axios;

// Add global properties
app.config.globalProperties.$formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

app.config.globalProperties.$formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Mount app
app.mount('#app');

// Temporarily disabled service worker registration for PWA support
// if (import.meta.env.PROD) {
//   registerServiceWorker();
// }
