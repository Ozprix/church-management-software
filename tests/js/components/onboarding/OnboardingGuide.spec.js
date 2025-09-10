import { mount } from '@vue/test-utils';
import { setActivePinia, createPinia } from 'pinia';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import OnboardingGuide from '../../../../resources/js/components/onboarding/OnboardingGuide.vue';

// Mock the onboarding store
jest.mock('../../../../resources/js/stores/onboarding', () => ({
  useOnboardingStore: jest.fn(() => ({
    onboardingCompleted: false,
    currentRole: 'admin',
    getOnboardingSteps: jest.fn(() => [
      {
        id: 'welcome',
        title: 'Welcome to Church Management',
        description: 'This guide will help you get started with the system.',
        icon: 'fas fa-church'
      },
      {
        id: 'dashboard',
        title: 'Dashboard Overview',
        description: 'Your dashboard provides a quick overview of important information.',
        icon: 'fas fa-tachometer-alt'
      },
      {
        id: 'members',
        title: 'Managing Members',
        description: 'Learn how to add, edit, and manage church members.',
        icon: 'fas fa-users'
      },
      {
        id: 'donations',
        title: 'Tracking Donations',
        description: 'Record and track donations and generate reports.',
        icon: 'fas fa-hand-holding-usd'
      }
    ]),
    completeOnboarding: jest.fn(),
    setUserRole: jest.fn()
  }))
}));

// Mock the auth store
jest.mock('../../../../resources/js/stores/auth', () => ({
  useAuthStore: jest.fn(() => ({
    user: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'admin'
    }
  }))
}));

// Mock the router
jest.mock('vue-router', () => ({
  useRouter: jest.fn(() => ({
    push: jest.fn()
  }))
}));

// Mock the toast service
jest.mock('../../../../resources/js/composables/useToast', () => ({
  useToast: jest.fn(() => ({
    success: jest.fn(),
    error: jest.fn(),
    info: jest.fn()
  }))
}));

describe('OnboardingGuide.vue', () => {
  let wrapper;
  let onboardingStore;
  let router;
  let toast;

  beforeEach(() => {
    // Create a fresh pinia instance and make it active
    setActivePinia(createPinia());
    
    // Get the mocked store and router
    onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    router = require('vue-router').useRouter();
    toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Mount the component
    wrapper = mount(OnboardingGuide);
  });

  it('renders correctly', () => {
    expect(wrapper.find('.onboarding-guide').exists()).toBe(true);
    expect(wrapper.find('.onboarding-header').exists()).toBe(true);
    expect(wrapper.find('.onboarding-steps').exists()).toBe(true);
  });

  it('loads onboarding steps from the store', () => {
    // Check that getOnboardingSteps was called with the current role
    expect(onboardingStore.getOnboardingSteps).toHaveBeenCalledWith('admin');
    
    // Check that steps are loaded into the component
    expect(wrapper.vm.steps.length).toBe(4);
    expect(wrapper.vm.steps[0].id).toBe('welcome');
    expect(wrapper.vm.steps[1].id).toBe('dashboard');
    expect(wrapper.vm.steps[2].id).toBe('members');
    expect(wrapper.vm.steps[3].id).toBe('donations');
  });

  it('starts with the first step active', () => {
    expect(wrapper.vm.currentStepIndex).toBe(0);
    expect(wrapper.vm.currentStep.id).toBe('welcome');
  });

  it('navigates to the next step when next button is clicked', async () => {
    // Initially at first step
    expect(wrapper.vm.currentStepIndex).toBe(0);
    
    // Find and click the next button
    const nextButton = wrapper.find('button.btn-primary');
    await nextButton.trigger('click');
    
    // Should now be at second step
    expect(wrapper.vm.currentStepIndex).toBe(1);
    expect(wrapper.vm.currentStep.id).toBe('dashboard');
  });

  it('navigates to the previous step when back button is clicked', async () => {
    // Move to second step first
    wrapper.vm.currentStepIndex = 1;
    await wrapper.vm.$nextTick();
    
    // Find and click the back button
    const backButton = wrapper.find('button.btn-secondary');
    await backButton.trigger('click');
    
    // Should now be back at first step
    expect(wrapper.vm.currentStepIndex).toBe(0);
    expect(wrapper.vm.currentStep.id).toBe('welcome');
  });

  it('completes onboarding when finish button is clicked on last step', async () => {
    // Move to last step
    wrapper.vm.currentStepIndex = 3;
    await wrapper.vm.$nextTick();
    
    // Find and click the finish button
    const finishButton = wrapper.find('button.btn-primary');
    await finishButton.trigger('click');
    
    // Check that completeOnboarding was called
    expect(onboardingStore.completeOnboarding).toHaveBeenCalledWith(true);
    
    // Check that router.push was called to navigate to dashboard
    expect(router.push).toHaveBeenCalledWith('/dashboard');
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Onboarding completed! You can access the help center anytime from the navigation menu.');
  });

  it('skips onboarding when skip button is clicked', async () => {
    // Find and click the skip button
    const skipButton = wrapper.find('button.btn-link');
    await skipButton.trigger('click');
    
    // Check that completeOnboarding was called
    expect(onboardingStore.completeOnboarding).toHaveBeenCalledWith(false);
    
    // Check that router.push was called to navigate to dashboard
    expect(router.push).toHaveBeenCalledWith('/dashboard');
    
    // Check that info toast was shown
    expect(toast.info).toHaveBeenCalledWith('Onboarding skipped. You can access it later from your profile settings.');
  });

  it('shows progress indicator correctly', () => {
    // Initially at first step (1 of 4)
    const progressText = wrapper.find('.progress-text').text();
    expect(progressText).toContain('Step 1 of 4');
    
    // Progress percentage should be 25% (1/4)
    const progressBar = wrapper.find('.progress-bar');
    expect(progressBar.attributes('style')).toContain('width: 25%');
  });

  it('updates user role when role selector changes', async () => {
    // Find the role selector
    const roleSelector = wrapper.find('select');
    
    // Change the selected role
    await roleSelector.setValue('finance');
    
    // Check that setUserRole was called with the new role
    expect(onboardingStore.setUserRole).toHaveBeenCalledWith('finance');
    
    // Check that getOnboardingSteps was called with the new role
    expect(onboardingStore.getOnboardingSteps).toHaveBeenCalledWith('finance');
  });

  it('disables back button on first step', () => {
    // Initially at first step
    const backButton = wrapper.find('button.btn-secondary');
    expect(backButton.attributes('disabled')).toBeDefined();
  });

  it('shows finish button instead of next on last step', async () => {
    // Move to last step
    wrapper.vm.currentStepIndex = 3;
    await wrapper.vm.$nextTick();
    
    // Check that button text is "Finish" not "Next"
    const primaryButton = wrapper.find('button.btn-primary');
    expect(primaryButton.text()).toContain('Finish');
    expect(primaryButton.text()).not.toContain('Next');
  });

  it('handles keyboard navigation', async () => {
    // Initially at first step
    expect(wrapper.vm.currentStepIndex).toBe(0);
    
    // Simulate right arrow key press
    await wrapper.trigger('keydown', { key: 'ArrowRight' });
    
    // Should move to next step
    expect(wrapper.vm.currentStepIndex).toBe(1);
    
    // Simulate left arrow key press
    await wrapper.trigger('keydown', { key: 'ArrowLeft' });
    
    // Should move back to first step
    expect(wrapper.vm.currentStepIndex).toBe(0);
  });
});
