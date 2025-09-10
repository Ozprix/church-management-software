import { setActivePinia, createPinia } from 'pinia';
import { useOnboardingStore } from '../../../resources/js/stores/onboarding';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';

// Mock localStorage
const localStorageMock = (() => {
  let store = {};
  return {
    getItem: jest.fn(key => store[key] || null),
    setItem: jest.fn((key, value) => {
      store[key] = value.toString();
    }),
    removeItem: jest.fn(key => {
      delete store[key];
    }),
    clear: jest.fn(() => {
      store = {};
    })
  };
})();

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock
});

// Mock auth store
jest.mock('../../../resources/js/stores/auth', () => ({
  useAuthStore: jest.fn(() => ({
    user: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'admin'
    }
  }))
}));

// Mock toast service
jest.mock('../../../resources/js/composables/useToast', () => ({
  useToast: jest.fn(() => ({
    success: jest.fn(),
    error: jest.fn(),
    info: jest.fn()
  }))
}));

describe('Onboarding Store', () => {
  beforeEach(() => {
    // Create a fresh pinia instance and make it active
    setActivePinia(createPinia());
    
    // Clear localStorage mock
    localStorageMock.clear();
    
    // Clear all mock function calls
    jest.clearAllMocks();
  });

  it('initializes with default values', () => {
    const onboardingStore = useOnboardingStore();
    
    expect(onboardingStore.onboardingCompleted).toBe(false);
    expect(onboardingStore.currentRole).toBe('');
    expect(onboardingStore.showTips).toBe(true);
    expect(onboardingStore.tipsSeen).toEqual([]);
    expect(onboardingStore.featureTours).toEqual({
      dashboard: false,
      members: false,
      donations: false,
      events: false,
      reports: false,
      search: false
    });
    expect(onboardingStore.userPreferences).toEqual({
      showOnboardingOnLogin: true,
      enableFeatureTips: true,
      enableContextualHelp: true
    });
  });

  it('loads state from localStorage if available', () => {
    // Set up localStorage with saved state
    localStorageMock.getItem.mockImplementation(key => {
      if (key === 'onboardingCompleted') return 'true';
      if (key === 'currentRole') return '"finance"';
      if (key === 'showTips') return 'false';
      if (key === 'tipsSeen') return '["dashboard.welcome","members.add"]';
      if (key === 'featureTours') return '{"dashboard":true,"members":true,"donations":false}';
      if (key === 'userPreferences') return '{"showOnboardingOnLogin":false,"enableFeatureTips":true}';
      return null;
    });
    
    const onboardingStore = useOnboardingStore();
    
    // Initialize the store
    onboardingStore.initializeOnboarding();
    
    // Check that state was loaded from localStorage
    expect(onboardingStore.onboardingCompleted).toBe(true);
    expect(onboardingStore.currentRole).toBe('finance');
    expect(onboardingStore.showTips).toBe(false);
    expect(onboardingStore.tipsSeen).toEqual(['dashboard.welcome', 'members.add']);
    expect(onboardingStore.featureTours.dashboard).toBe(true);
    expect(onboardingStore.featureTours.members).toBe(true);
    expect(onboardingStore.featureTours.donations).toBe(false);
    expect(onboardingStore.userPreferences.showOnboardingOnLogin).toBe(false);
    expect(onboardingStore.userPreferences.enableFeatureTips).toBe(true);
  });

  it('returns correct onboarding steps for different roles', () => {
    const onboardingStore = useOnboardingStore();
    
    // Admin role
    const adminSteps = onboardingStore.getOnboardingSteps('admin');
    expect(adminSteps.length).toBeGreaterThan(0);
    expect(adminSteps.some(step => step.id === 'members')).toBe(true);
    expect(adminSteps.some(step => step.id === 'donations')).toBe(true);
    expect(adminSteps.some(step => step.id === 'reports')).toBe(true);
    
    // Finance role
    const financeSteps = onboardingStore.getOnboardingSteps('finance');
    expect(financeSteps.length).toBeGreaterThan(0);
    expect(financeSteps.some(step => step.id === 'donations')).toBe(true);
    expect(financeSteps.some(step => step.id === 'pledge-campaigns')).toBe(true);
    expect(financeSteps.some(step => step.id === 'reports')).toBe(true);
    
    // Membership role
    const membershipSteps = onboardingStore.getOnboardingSteps('membership');
    expect(membershipSteps.length).toBeGreaterThan(0);
    expect(membershipSteps.some(step => step.id === 'members')).toBe(true);
    expect(membershipSteps.some(step => step.id === 'groups')).toBe(true);
    expect(membershipSteps.some(step => step.id === 'attendance')).toBe(true);
    
    // Events role
    const eventsSteps = onboardingStore.getOnboardingSteps('events');
    expect(eventsSteps.length).toBeGreaterThan(0);
    expect(eventsSteps.some(step => step.id === 'events')).toBe(true);
    expect(eventsSteps.some(step => step.id === 'attendance')).toBe(true);
    expect(eventsSteps.some(step => step.id === 'volunteers')).toBe(true);
  });

  it('returns feature tips for different sections', () => {
    const onboardingStore = useOnboardingStore();
    
    // Dashboard tips
    const dashboardTips = onboardingStore.getFeatureTips('dashboard');
    expect(dashboardTips.length).toBeGreaterThan(0);
    
    // Members tips
    const membersTips = onboardingStore.getFeatureTips('members');
    expect(membersTips.length).toBeGreaterThan(0);
    
    // Donations tips
    const donationsTips = onboardingStore.getFeatureTips('donations');
    expect(donationsTips.length).toBeGreaterThan(0);
    
    // Non-existent section
    const nonExistentTips = onboardingStore.getFeatureTips('non-existent');
    expect(nonExistentTips.length).toBe(0);
  });

  it('marks feature tour as completed', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, all feature tours are marked as not completed
    expect(onboardingStore.featureTours.dashboard).toBe(false);
    
    // Mark dashboard tour as completed
    onboardingStore.markFeatureTourCompleted('dashboard');
    
    // Check that tour is now marked as completed
    expect(onboardingStore.featureTours.dashboard).toBe(true);
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('featureTours', expect.any(String));
  });

  it('marks tip as seen', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, tipsSeen is empty
    expect(onboardingStore.tipsSeen.length).toBe(0);
    
    // Mark a tip as seen
    onboardingStore.markTipSeen('dashboard.welcome');
    
    // Check that tip is now marked as seen
    expect(onboardingStore.tipsSeen).toContain('dashboard.welcome');
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('tipsSeen', expect.any(String));
  });

  it('completes onboarding', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, onboardingCompleted is false
    expect(onboardingStore.onboardingCompleted).toBe(false);
    
    // Complete onboarding
    onboardingStore.completeOnboarding(true);
    
    // Check that onboardingCompleted is now true
    expect(onboardingStore.onboardingCompleted).toBe(true);
    
    // Check that showTips is now true (as specified in the parameter)
    expect(onboardingStore.showTips).toBe(true);
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('onboardingCompleted', 'true');
  });

  it('resets onboarding', () => {
    const onboardingStore = useOnboardingStore();
    const toast = require('../../../resources/js/composables/useToast').useToast();
    
    // Set up initial state
    onboardingStore.onboardingCompleted = true;
    onboardingStore.tipsSeen = ['dashboard.welcome', 'members.add'];
    onboardingStore.featureTours.dashboard = true;
    onboardingStore.featureTours.members = true;
    
    // Reset onboarding
    onboardingStore.resetOnboarding();
    
    // Check that state was reset
    expect(onboardingStore.onboardingCompleted).toBe(false);
    expect(onboardingStore.tipsSeen).toEqual([]);
    expect(onboardingStore.featureTours.dashboard).toBe(false);
    expect(onboardingStore.featureTours.members).toBe(false);
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('onboardingCompleted', 'false');
    expect(localStorageMock.setItem).toHaveBeenCalledWith('tipsSeen', '[]');
    expect(localStorageMock.setItem).toHaveBeenCalledWith('featureTours', expect.any(String));
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Onboarding has been reset. You will see the guide on your next login.');
  });

  it('updates user preferences', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, userPreferences has default values
    expect(onboardingStore.userPreferences.showOnboardingOnLogin).toBe(true);
    expect(onboardingStore.userPreferences.enableFeatureTips).toBe(true);
    expect(onboardingStore.userPreferences.enableContextualHelp).toBe(true);
    
    // Update preferences
    onboardingStore.updatePreferences({
      showOnboardingOnLogin: false,
      enableFeatureTips: false,
      enableContextualHelp: true
    });
    
    // Check that preferences were updated
    expect(onboardingStore.userPreferences.showOnboardingOnLogin).toBe(false);
    expect(onboardingStore.userPreferences.enableFeatureTips).toBe(false);
    expect(onboardingStore.userPreferences.enableContextualHelp).toBe(true);
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('userPreferences', expect.any(String));
  });

  it('sets user role', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, currentRole is empty
    expect(onboardingStore.currentRole).toBe('');
    
    // Set role
    onboardingStore.setUserRole('finance');
    
    // Check that role was updated
    expect(onboardingStore.currentRole).toBe('finance');
    
    // Check that localStorage was updated
    expect(localStorageMock.setItem).toHaveBeenCalledWith('currentRole', '"finance"');
  });

  it('computes isFirstTimeUser correctly', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, onboardingCompleted is false
    expect(onboardingStore.isFirstTimeUser).toBe(true);
    
    // Complete onboarding
    onboardingStore.onboardingCompleted = true;
    
    // Check that isFirstTimeUser is now false
    expect(onboardingStore.isFirstTimeUser).toBe(false);
  });

  it('computes pendingFeatureTours correctly', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, all feature tours are pending
    expect(onboardingStore.pendingFeatureTours.length).toBe(6); // dashboard, members, donations, events, reports, search
    
    // Mark some tours as completed
    onboardingStore.featureTours.dashboard = true;
    onboardingStore.featureTours.members = true;
    
    // Check that only incomplete tours are returned
    expect(onboardingStore.pendingFeatureTours.length).toBe(4);
    expect(onboardingStore.pendingFeatureTours).toContain('donations');
    expect(onboardingStore.pendingFeatureTours).toContain('events');
    expect(onboardingStore.pendingFeatureTours).toContain('reports');
    expect(onboardingStore.pendingFeatureTours).toContain('search');
    expect(onboardingStore.pendingFeatureTours).not.toContain('dashboard');
    expect(onboardingStore.pendingFeatureTours).not.toContain('members');
  });

  it('computes hasCompletedAllTours correctly', () => {
    const onboardingStore = useOnboardingStore();
    
    // Initially, no tours are completed
    expect(onboardingStore.hasCompletedAllTours).toBe(false);
    
    // Mark all tours as completed
    Object.keys(onboardingStore.featureTours).forEach(tour => {
      onboardingStore.featureTours[tour] = true;
    });
    
    // Check that hasCompletedAllTours is now true
    expect(onboardingStore.hasCompletedAllTours).toBe(true);
    
    // Mark one tour as incomplete
    onboardingStore.featureTours.dashboard = false;
    
    // Check that hasCompletedAllTours is now false
    expect(onboardingStore.hasCompletedAllTours).toBe(false);
  });
});
