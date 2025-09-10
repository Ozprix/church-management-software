import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useAuthStore } from './auth';
import { useToast } from '../composables/useToast';

export const useOnboardingStore = defineStore('onboarding', () => {
  // State
  const onboardingCompleted = ref(false);
  const currentRole = ref('');
  const showTips = ref(true);
  const tipsSeen = ref([]);
  const featureTours = ref({
    dashboard: false,
    members: false,
    donations: false,
    events: false,
    reports: false,
    search: false
  });
  const userPreferences = ref({
    showOnboardingOnLogin: true,
    enableFeatureTips: true,
    enableContextualHelp: true
  });
  
  // Get services
  const authStore = useAuthStore();
  const toast = useToast();
  
  // Computed
  const isFirstTimeUser = computed(() => {
    return !onboardingCompleted.value;
  });
  
  const pendingFeatureTours = computed(() => {
    return Object.entries(featureTours.value)
      .filter(([_, completed]) => !completed)
      .map(([feature]) => feature);
  });
  
  const hasCompletedAllTours = computed(() => {
    return Object.values(featureTours.value).every(completed => completed);
  });
  
  // Role-specific onboarding steps
  const getOnboardingSteps = (role = '') => {
    // Common steps for all roles
    const commonSteps = [
      {
        id: 'welcome',
        title: 'Welcome to the Church Management System',
        description: 'Let\'s take a quick tour to help you get started with the system. This will only take a few minutes.',
        icon: 'star',
        position: 1
      },
      {
        id: 'dashboard',
        title: 'Dashboard Overview',
        description: 'Your dashboard provides a quick overview of your church\'s key metrics and recent activities.',
        icon: 'home',
        position: 2,
        featureId: 'dashboard'
      },
      {
        id: 'navigation',
        title: 'Navigation',
        description: 'The main navigation menu gives you access to all features of the system.',
        icon: 'compass',
        position: 3
      },
      {
        id: 'help',
        title: 'Getting Help',
        description: 'Help is always available when you need it.',
        icon: 'question-circle',
        position: 7
      },
      {
        id: 'complete',
        title: 'You\'re All Set!',
        description: 'You\'ve completed the onboarding guide. You can revisit this guide anytime from your profile menu.',
        icon: 'check-circle',
        position: 8
      }
    ];
    
    // Role-specific steps
    const roleSteps = {
      admin: [
        {
          id: 'members',
          title: 'Member Management',
          description: 'The members module helps you manage your church membership database.',
          icon: 'users',
          position: 4,
          featureId: 'members'
        },
        {
          id: 'donations',
          title: 'Donations & Financial Management',
          description: 'The donations module helps you track and manage all financial contributions.',
          icon: 'hand-holding-heart',
          position: 5,
          featureId: 'donations'
        },
        {
          id: 'reports',
          title: 'Reports & Analytics',
          description: 'Generate insightful reports to make data-driven decisions.',
          icon: 'chart-bar',
          position: 6,
          featureId: 'reports'
        }
      ],
      finance: [
        {
          id: 'donations',
          title: 'Donations & Financial Management',
          description: 'The donations module helps you track and manage all financial contributions.',
          icon: 'hand-holding-heart',
          position: 4,
          featureId: 'donations'
        },
        {
          id: 'pledge-campaigns',
          title: 'Pledge Campaign Management',
          description: 'Create and manage pledge campaigns to reach your financial goals.',
          icon: 'bullseye',
          position: 5,
          featureId: 'donations'
        },
        {
          id: 'reports',
          title: 'Financial Reports',
          description: 'Generate detailed financial reports and export data for accounting purposes.',
          icon: 'chart-bar',
          position: 6,
          featureId: 'reports'
        }
      ],
      membership: [
        {
          id: 'members',
          title: 'Member Management',
          description: 'The members module helps you manage your church membership database.',
          icon: 'users',
          position: 4,
          featureId: 'members'
        },
        {
          id: 'groups',
          title: 'Groups & Ministries',
          description: 'Organize your members into groups and ministries.',
          icon: 'user-friends',
          position: 5,
          featureId: 'members'
        },
        {
          id: 'attendance',
          title: 'Attendance Tracking',
          description: 'Track attendance for services and events.',
          icon: 'clipboard-check',
          position: 6,
          featureId: 'events'
        }
      ],
      events: [
        {
          id: 'events',
          title: 'Event Management',
          description: 'Create and manage church events, services, and activities.',
          icon: 'calendar',
          position: 4,
          featureId: 'events'
        },
        {
          id: 'attendance',
          title: 'Attendance Tracking',
          description: 'Track attendance for services and events.',
          icon: 'clipboard-check',
          position: 5,
          featureId: 'events'
        },
        {
          id: 'volunteers',
          title: 'Volunteer Management',
          description: 'Organize volunteers for your events and services.',
          icon: 'hands-helping',
          position: 6,
          featureId: 'members'
        }
      ]
    };
    
    // If no role specified or role not found, return admin steps
    const specificRoleSteps = roleSteps[role] || roleSteps.admin;
    
    // Combine and sort steps
    return [...commonSteps, ...specificRoleSteps]
      .sort((a, b) => a.position - b.position);
  };
  
  // Feature tips by section
  const getFeatureTips = (section) => {
    const tips = {
      dashboard: [
        {
          id: 'dashboard-widgets',
          title: 'Customizable Widgets',
          description: 'You can customize your dashboard by adding, removing, or rearranging widgets.',
          position: 'bottom',
          element: '.dashboard-widget-container'
        },
        {
          id: 'dashboard-stats',
          title: 'Key Metrics',
          description: 'These stats give you a quick overview of your church\'s current status.',
          position: 'bottom',
          element: '.stats-widget'
        }
      ],
      members: [
        {
          id: 'member-search',
          title: 'Advanced Search',
          description: 'Use the search bar to quickly find members by name, email, phone, or other criteria.',
          position: 'bottom',
          element: '.member-search'
        },
        {
          id: 'member-filters',
          title: 'Member Filters',
          description: 'Filter members by status, group, or custom fields.',
          position: 'right',
          element: '.member-filters'
        }
      ],
      donations: [
        {
          id: 'donation-entry',
          title: 'Quick Entry',
          description: 'Use this form to quickly record new donations.',
          position: 'right',
          element: '.donation-entry-form'
        },
        {
          id: 'donation-reports',
          title: 'Financial Reports',
          description: 'Generate detailed reports on donations and giving trends.',
          position: 'top',
          element: '.donation-reports'
        },
        {
          id: 'pledge-campaigns',
          title: 'Pledge Campaigns',
          description: 'Create and manage pledge campaigns to track progress toward financial goals.',
          position: 'left',
          element: '.pledge-campaigns'
        }
      ],
      search: [
        {
          id: 'search-filters',
          title: 'Search Filters',
          description: 'Refine your search results using these filters.',
          position: 'right',
          element: '.search-filters'
        },
        {
          id: 'voice-search',
          title: 'Voice Search',
          description: 'Click the microphone icon to search using your voice.',
          position: 'bottom',
          element: '.voice-search'
        },
        {
          id: 'search-history',
          title: 'Search History',
          description: 'Quickly access your recent searches.',
          position: 'bottom',
          element: '.search-history'
        }
      ]
    };
    
    return tips[section] || [];
  };
  
  // Methods
  const initializeOnboarding = () => {
    // Check local storage for onboarding status
    const storedStatus = localStorage.getItem('onboardingCompleted');
    const storedRole = localStorage.getItem('userRole');
    const storedShowTips = localStorage.getItem('showTips');
    const storedTipsSeen = localStorage.getItem('tipsSeen');
    const storedFeatureTours = localStorage.getItem('featureTours');
    const storedPreferences = localStorage.getItem('onboardingPreferences');
    
    // Set state from storage or defaults
    onboardingCompleted.value = storedStatus === 'true';
    currentRole.value = storedRole || authStore.userRole || 'admin';
    showTips.value = storedShowTips !== 'false';
    
    if (storedTipsSeen) {
      try {
        tipsSeen.value = JSON.parse(storedTipsSeen);
      } catch (e) {
        console.error('Error parsing stored tips seen:', e);
      }
    }
    
    if (storedFeatureTours) {
      try {
        featureTours.value = { ...featureTours.value, ...JSON.parse(storedFeatureTours) };
      } catch (e) {
        console.error('Error parsing stored feature tours:', e);
      }
    }
    
    if (storedPreferences) {
      try {
        userPreferences.value = { ...userPreferences.value, ...JSON.parse(storedPreferences) };
      } catch (e) {
        console.error('Error parsing stored preferences:', e);
      }
    }
  };
  
  const completeOnboarding = (showTipsAfter = true) => {
    onboardingCompleted.value = true;
    showTips.value = showTipsAfter;
    
    // Save to local storage
    localStorage.setItem('onboardingCompleted', 'true');
    localStorage.setItem('showTips', showTipsAfter.toString());
    
    // Show confirmation toast
    toast.success('Onboarding completed! You can restart it anytime from your profile settings.');
  };
  
  const markFeatureTourCompleted = (featureId) => {
    if (featureTours.value.hasOwnProperty(featureId)) {
      featureTours.value[featureId] = true;
      
      // Save to local storage
      localStorage.setItem('featureTours', JSON.stringify(featureTours.value));
      
      // Show toast if all tours completed
      if (hasCompletedAllTours.value) {
        toast.success('You\'ve completed all feature tours!');
      }
    }
  };
  
  const markTipSeen = (tipId) => {
    if (!tipsSeen.value.includes(tipId)) {
      tipsSeen.value.push(tipId);
      
      // Save to local storage
      localStorage.setItem('tipsSeen', JSON.stringify(tipsSeen.value));
    }
  };
  
  const resetOnboarding = () => {
    onboardingCompleted.value = false;
    showTips.value = true;
    tipsSeen.value = [];
    
    // Reset feature tours
    Object.keys(featureTours.value).forEach(key => {
      featureTours.value[key] = false;
    });
    
    // Save to local storage
    localStorage.setItem('onboardingCompleted', 'false');
    localStorage.setItem('showTips', 'true');
    localStorage.setItem('tipsSeen', JSON.stringify([]));
    localStorage.setItem('featureTours', JSON.stringify(featureTours.value));
    
    // Show confirmation toast
    toast.success('Onboarding has been reset. You\'ll see the guide on your next login.');
  };
  
  const updatePreferences = (preferences) => {
    userPreferences.value = { ...userPreferences.value, ...preferences };
    
    // Save to local storage
    localStorage.setItem('onboardingPreferences', JSON.stringify(userPreferences.value));
    
    // Show confirmation toast
    toast.success('Your onboarding preferences have been updated.');
  };
  
  const setUserRole = (role) => {
    currentRole.value = role;
    
    // Save to local storage
    localStorage.setItem('userRole', role);
  };
  
  // Initialize on store creation
  initializeOnboarding();
  
  return {
    // State
    onboardingCompleted,
    currentRole,
    showTips,
    tipsSeen,
    featureTours,
    userPreferences,
    
    // Computed
    isFirstTimeUser,
    pendingFeatureTours,
    hasCompletedAllTours,
    
    // Methods
    getOnboardingSteps,
    getFeatureTips,
    completeOnboarding,
    markFeatureTourCompleted,
    markTipSeen,
    resetOnboarding,
    updatePreferences,
    setUserRole,
    initializeOnboarding
  };
});
