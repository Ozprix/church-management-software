import { mount } from '@vue/test-utils';
import OnboardingSettings from '../../../../resources/js/components/onboarding/OnboardingSettings.vue';
import { nextTick } from 'vue';

// Mock the onboarding store
jest.mock('../../../../resources/js/stores/onboarding', () => ({
  useOnboardingStore: jest.fn(() => ({
    userPreferences: {
      showOnboardingOnLogin: true,
      enableFeatureTips: true,
      enableContextualHelp: true
    },
    featureTours: {
      dashboard: true,
      members: false,
      donations: true
    },
    updatePreferences: jest.fn(),
    resetOnboarding: jest.fn()
  }))
}));

// Mock the toast composable
jest.mock('../../../../resources/js/composables/useToast', () => ({
  useToast: jest.fn(() => ({
    success: jest.fn(),
    error: jest.fn(),
    info: jest.fn()
  }))
}));

describe('OnboardingSettings.vue', () => {
  let wrapper;

  beforeEach(() => {
    // Mount component
    wrapper = mount(OnboardingSettings, {
      global: {
        stubs: {
          'Toggle': true,
          'ConfirmationModal': true
        }
      }
    });
  });

  afterEach(() => {
    wrapper.unmount();
    jest.clearAllMocks();
  });

  test('renders correctly with preferences from store', () => {
    expect(wrapper.find('.onboarding-settings').exists()).toBe(true);
    
    // Check that preferences are loaded from store
    expect(wrapper.vm.preferences.showOnboardingOnLogin).toBe(true);
    expect(wrapper.vm.preferences.enableFeatureTips).toBe(true);
    expect(wrapper.vm.preferences.enableContextualHelp).toBe(true);
    
    // Check that feature tours are loaded from store
    expect(Object.keys(wrapper.vm.featureTours)).toContain('dashboard');
    expect(Object.keys(wrapper.vm.featureTours)).toContain('members');
    expect(Object.keys(wrapper.vm.featureTours)).toContain('donations');
  });

  test('updates preferences when changed', async () => {
    const onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    const toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Change a preference
    wrapper.vm.preferences.showOnboardingOnLogin = false;
    
    // Call updatePreferences method
    wrapper.vm.updatePreferences();
    await nextTick();
    
    // Check that store method was called with updated preferences
    expect(onboardingStore.updatePreferences).toHaveBeenCalledWith({
      showOnboardingOnLogin: false,
      enableFeatureTips: true,
      enableContextualHelp: true
    });
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Preferences updated');
  });

  test('formats feature names correctly', () => {
    expect(wrapper.vm.formatFeatureName('dashboardMain')).toBe('Dashboard Main');
    expect(wrapper.vm.formatFeatureName('user-profile')).toBe('User Profile');
    expect(wrapper.vm.formatFeatureName('donationSettings')).toBe('Donation Settings');
  });

  test('restarts a tour', async () => {
    const toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Call restartTour method
    wrapper.vm.restartTour('dashboard');
    await nextTick();
    
    // Check that feature tour was reset in local state
    expect(wrapper.vm.featureTours.dashboard).toBe(false);
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Dashboard tour will start when you next visit that section');
  });

  test('shows confirmation modal when reset button is clicked', async () => {
    // Initially, confirmation modal should not be shown
    expect(wrapper.vm.showResetConfirmation).toBe(false);
    
    // Call confirmResetOnboarding method
    wrapper.vm.confirmResetOnboarding();
    await nextTick();
    
    // Check that confirmation modal is now shown
    expect(wrapper.vm.showResetConfirmation).toBe(true);
  });

  test('resets onboarding when confirmed', async () => {
    const onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    const toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Show confirmation modal
    wrapper.vm.showResetConfirmation = true;
    await nextTick();
    
    // Call resetOnboarding method
    wrapper.vm.resetOnboarding();
    await nextTick();
    
    // Check that store method was called
    expect(onboardingStore.resetOnboarding).toHaveBeenCalled();
    
    // Check that confirmation modal is hidden
    expect(wrapper.vm.showResetConfirmation).toBe(false);
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith("Onboarding has been reset. You'll see the guide on your next login.");
  });
});
