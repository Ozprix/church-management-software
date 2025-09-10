import { createRouter, createWebHistory } from 'vue-router';

// Layouts - Keep these eagerly loaded as they're essential for the app structure
import DashboardLayout from '../layouts/DashboardLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';

// Error Pages - Keep eagerly loaded as it's a critical fallback
import NotFound from '../pages/NotFound.vue';

// Search components
import { SearchResults } from '../components/search';

// Performance optimization: Using dynamic imports for code splitting and lazy loading
// This ensures that components are only loaded when they're needed

// Auth components will be bundled together in a separate chunk
const Login = () => import(/* webpackChunkName: "auth" */ '../views/auth/Login.vue');
const Register = () => import(/* webpackChunkName: "auth" */ '../views/auth/Register.vue');
const ForgotPassword = () => import(/* webpackChunkName: "auth" */ '../views/auth/ForgotPassword.vue');
const ResetPassword = () => import(/* webpackChunkName: "auth" */ '../views/auth/ResetPassword.vue');

// Dashboard is frequently accessed, but still benefits from lazy loading
const Dashboard = () => import(/* webpackChunkName: "dashboard" */ '../pages/Dashboard.vue');

// Feature-specific components grouped by functionality
const ComponentDemo = () => import(/* webpackChunkName: "ui-demo" */ '../pages/ComponentDemo.vue');
const ThemeCustomizer = () => import(/* webpackChunkName: "theme" */ '../pages/ThemeCustomizer.vue');

// Core features grouped by functionality
const Settings = () => import(/* webpackChunkName: "settings" */ '../pages/Settings.vue');
const Notifications = () => import(/* webpackChunkName: "notifications" */ '../pages/Notifications.vue');
const Reports = () => import(/* webpackChunkName: "reports" */ '../pages/Reports.vue');
const Events = () => import(/* webpackChunkName: "events" */ '../pages/Events.vue');
const Attendance = () => import(/* webpackChunkName: "attendance" */ '../pages/Attendance.vue');
const Media = () => import(/* webpackChunkName: "media" */ '../pages/Media.vue');

// Report components bundled together
const SavedReportsPage = () => import(/* webpackChunkName: "reports-detail" */ '../components/reports/SavedReportsPage.vue');
const PrintableReport = () => import(/* webpackChunkName: "reports-detail" */ '../components/reports/PrintableReport.vue');
const PledgeFulfillmentReport = () => import(/* webpackChunkName: "reports-detail" */ '../components/reports/PledgeFulfillmentReport.vue');

// Onboarding and help components
const OnboardingGuide = () => import(/* webpackChunkName: "onboarding" */ '../components/onboarding/OnboardingGuide.vue');
const OnboardingSettings = () => import(/* webpackChunkName: "onboarding" */ '../components/onboarding/OnboardingSettings.vue');
const HelpCenter = () => import(/* webpackChunkName: "help" */ '../pages/HelpCenter.vue');

// Define a loading component for route-level code-splitting
const LoadingComponent = {
  template: `
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <svg class="animate-spin h-10 w-10 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-3 text-neutral-600 dark:text-neutral-300">Loading...</p>
      </div>
    </div>
  `
};

// Error component for when chunks fail to load
const ErrorComponent = {
  template: `
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <svg class="h-16 w-16 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h2 class="text-xl font-semibold mt-4 text-neutral-800 dark:text-white">Failed to load resource</h2>
        <p class="mt-2 text-neutral-600 dark:text-neutral-300">Please refresh the page or try again later.</p>
        <button 
          @click="window.location.reload()" 
          class="mt-4 px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors"
        >
          Refresh Page
        </button>
      </div>
    </div>
  `
};

const routes = [
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard,
        meta: { 
          title: 'Dashboard',
          description: 'Church Management System Dashboard' 
        },
        // Add loading and error components
        loading: LoadingComponent,
        error: ErrorComponent
      },
      {
        path: 'ui-components',
        name: 'ui-components',
        component: ComponentDemo,
        meta: { 
          title: 'UI Components',
          description: 'UI Component Demo Page' 
        }
      },
      {
        path: 'theme-customizer',
        name: 'theme-customizer',
        component: ThemeCustomizer,
        meta: { 
          title: 'Theme Customizer',
          description: 'Customize the application theme' 
        }
      }
    ]
  },
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: Login
      },
      {
        path: 'register',
        name: 'register',
        component: Register
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: ForgotPassword
      },
      {
        path: 'reset-password/:token',
        name: 'reset-password',
        component: ResetPassword
      }
    ]
  },
  {
    path: '/theme-customizer',
    name: 'ThemeCustomizer',
    component: () => import('../pages/ThemeCustomizer.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: Notifications,
    meta: { requiresAuth: true }
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/saved',
    name: 'saved-reports',
    component: SavedReportsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/print',
    name: 'print-report',
    component: PrintableReport,
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/pledge-fulfillment',
    name: 'pledge-fulfillment-report',
    component: PledgeFulfillmentReport,
    meta: { 
      requiresAuth: true,
      title: 'Pledge Fulfillment Report',
      description: 'Track pledge campaign progress and fulfillment'
    }
  },
  {
    path: '/events',
    name: 'Events',
    component: Events,
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: Attendance,
    meta: { requiresAuth: true }
  },
  {
    path: '/media',
    name: 'Media',
    component: Media,
    meta: { requiresAuth: true }
  },
  {
    path: '/component-demo',
    name: 'ComponentDemo',
    component: () => import('../pages/ComponentDemo.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/onboarding',
    meta: { requiresAuth: true },
    children: [
      {
        path: 'guide',
        name: 'onboarding-guide',
        component: OnboardingGuide,
        meta: { 
          title: 'Onboarding Guide',
          description: 'Interactive guide to help you get started with the Church Management System'
        }
      },
      {
        path: 'settings',
        name: 'onboarding-settings',
        component: OnboardingSettings,
        meta: { 
          title: 'Help & Onboarding Settings',
          description: 'Customize your onboarding experience and help preferences'
        }
      }
    ]
  },
  {
    path: '/help-center',
    name: 'help-center',
    component: HelpCenter,
    meta: { 
      requiresAuth: true,
      title: 'Help Center',
      description: 'Find answers to common questions and learn how to use the system'
    }
  },
  {
    path: '/search',
    name: 'search-results',
    component: SearchResults,
    meta: { 
      requiresAuth: true,
      title: 'Search Results',
      description: 'Search across church data'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // If the user used browser back/forward buttons, restore position
    if (savedPosition) {
      return savedPosition;
    }
    
    // If the route has a hash, scroll to the element with that id
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      };
    }
    
    // Otherwise scroll to top with smooth behavior
    return { 
      top: 0,
      behavior: 'smooth'
    };
  }
});

// Performance optimization: Prefetch components for likely navigation paths
const prefetchComponents = (componentsToLoad) => {
  for (const component of componentsToLoad) {
    try {
      // This triggers the dynamic import but doesn't wait for it
      component();
    } catch (error) {
      console.error('Error prefetching component:', error);
    }
  }
};

// Navigation guards
router.beforeEach((to, from, next) => {
  // Set document title based on route meta
  if (to.meta.title) {
    document.title = `${to.meta.title} - Church Management System`;
  } else {
    document.title = 'Church Management System';
  }
  
  // Authentication check
  const isAuthenticated = localStorage.getItem('token');
  
  if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
    next({ name: 'login' });
  } else {
    next();
  }
});

// After navigation is confirmed but before the next route is resolved
router.beforeResolve((to, from, next) => {
  // Prefetch components based on current route
  if (to.name === 'dashboard') {
    // If on dashboard, prefetch commonly accessed routes
    prefetchComponents([Reports, Notifications, Settings]);
  } else if (to.name === 'reports') {
    // If on reports, prefetch report-related components
    prefetchComponents([SavedReportsPage, PrintableReport]);
  }
  
  next();
});

export default router;
