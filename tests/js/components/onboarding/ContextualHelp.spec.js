import { mount } from '@vue/test-utils';
import { setActivePinia, createPinia } from 'pinia';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import ContextualHelp from '../../../../resources/js/components/onboarding/ContextualHelp.vue';

// Mock the onboarding store
jest.mock('../../../../resources/js/stores/onboarding', () => ({
  useOnboardingStore: jest.fn(() => ({
    userPreferences: {
      enableContextualHelp: true
    }
  }))
}));

// Mock the router
jest.mock('vue-router', () => ({
  useRoute: jest.fn(() => ({
    name: 'donations.index'
  }))
}));

describe('ContextualHelp.vue', () => {
  let wrapper;
  let onboardingStore;

  beforeEach(() => {
    // Create a fresh pinia instance and make it active
    setActivePinia(createPinia());
    
    // Get the mocked store
    onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    
    // Mount the component
    wrapper = mount(ContextualHelp);
  });

  it('renders correctly when contextual help is enabled', () => {
    expect(wrapper.find('.contextual-help').exists()).toBe(true);
    expect(wrapper.find('.help-button').exists()).toBe(true);
  });

  it('does not render when contextual help is disabled', async () => {
    // Update the store to disable contextual help
    onboardingStore.userPreferences.enableContextualHelp = false;
    await wrapper.vm.$nextTick();
    
    // Component should not render
    expect(wrapper.find('.contextual-help').exists()).toBe(false);
  });

  it('shows help panel when help button is clicked', async () => {
    // Initially, help panel should be hidden
    expect(wrapper.vm.isHelpOpen).toBe(false);
    expect(wrapper.find('.help-panel').exists()).toBe(false);
    
    // Click the help button
    await wrapper.find('.help-button').trigger('click');
    
    // Help panel should now be visible
    expect(wrapper.vm.isHelpOpen).toBe(true);
    expect(wrapper.find('.help-panel').exists()).toBe(true);
  });

  it('closes help panel when close button is clicked', async () => {
    // Open the help panel first
    wrapper.vm.isHelpOpen = true;
    await wrapper.vm.$nextTick();
    
    // Click the close button
    await wrapper.find('.close-button').trigger('click');
    
    // Help panel should now be hidden
    expect(wrapper.vm.isHelpOpen).toBe(false);
  });

  it('loads contextual help content based on current route', async () => {
    // Open the help panel
    wrapper.vm.isHelpOpen = true;
    await wrapper.vm.$nextTick();
    
    // Check that help content for the current route is displayed
    const helpContent = wrapper.find('.help-content');
    expect(helpContent.exists()).toBe(true);
    
    // The route is 'donations.index', so donation-related help should be shown
    expect(helpContent.text()).toContain('Donations');
  });

  it('shows general help when no route-specific help is available', async () => {
    // Change the mocked route to one without specific help content
    require('vue-router').useRoute.mockReturnValue({
      name: 'non-existent-route'
    });
    
    // Create a new wrapper with the updated route
    const newWrapper = mount(ContextualHelp);
    
    // Open the help panel
    newWrapper.vm.isHelpOpen = true;
    await newWrapper.vm.$nextTick();
    
    // Check that general help content is displayed
    const helpContent = newWrapper.find('.help-content');
    expect(helpContent.exists()).toBe(true);
    expect(helpContent.text()).toContain('General Help');
  });

  it('navigates to help center when "View Help Center" is clicked', async () => {
    // Mock the router push method
    const mockRouterPush = jest.fn();
    require('vue-router').useRouter = jest.fn(() => ({
      push: mockRouterPush
    }));
    
    // Create a new wrapper with the mocked router
    const newWrapper = mount(ContextualHelp);
    
    // Open the help panel
    newWrapper.vm.isHelpOpen = true;
    await newWrapper.vm.$nextTick();
    
    // Click the "View Help Center" link
    await newWrapper.find('.view-help-center').trigger('click');
    
    // Check that router.push was called with the correct route
    expect(mockRouterPush).toHaveBeenCalledWith('/help-center');
  });

  it('closes help panel when clicked outside', async () => {
    // Open the help panel first
    wrapper.vm.isHelpOpen = true;
    await wrapper.vm.$nextTick();
    
    // Simulate a click outside the help panel
    const event = new MouseEvent('click');
    Object.defineProperty(event, 'target', {
      value: document.body,
      enumerable: true
    });
    
    // Call the clickOutside method
    wrapper.vm.clickOutside(event);
    
    // Help panel should now be hidden
    expect(wrapper.vm.isHelpOpen).toBe(false);
  });

  it('does not close help panel when clicked inside', async () => {
    // Open the help panel first
    wrapper.vm.isHelpOpen = true;
    await wrapper.vm.$nextTick();
    
    // Create a mock element that would be inside the help panel
    const mockHelpPanelElement = document.createElement('div');
    mockHelpPanelElement.className = 'help-panel';
    
    // Mock contains to return true (indicating click was inside)
    wrapper.vm.$refs.helpPanel = {
      contains: jest.fn().mockReturnValue(true)
    };
    
    // Simulate a click event with the mock element as target
    const event = new MouseEvent('click');
    Object.defineProperty(event, 'target', {
      value: mockHelpPanelElement,
      enumerable: true
    });
    
    // Call the clickOutside method
    wrapper.vm.clickOutside(event);
    
    // Help panel should still be open
    expect(wrapper.vm.isHelpOpen).toBe(true);
  });

  it('handles keyboard shortcuts', async () => {
    // Initially, help panel should be closed
    expect(wrapper.vm.isHelpOpen).toBe(false);
    
    // Simulate pressing '?' key
    const event = new KeyboardEvent('keydown', { key: '?' });
    document.dispatchEvent(event);
    
    // Help panel should now be open
    expect(wrapper.vm.isHelpOpen).toBe(true);
    
    // Simulate pressing Escape key
    const escapeEvent = new KeyboardEvent('keydown', { key: 'Escape' });
    document.dispatchEvent(escapeEvent);
    
    // Help panel should now be closed
    expect(wrapper.vm.isHelpOpen).toBe(false);
  });

  it('removes event listeners on unmount', () => {
    // Spy on document removeEventListener
    const removeEventListenerSpy = jest.spyOn(document, 'removeEventListener');
    
    // Unmount the component
    wrapper.unmount();
    
    // Check that event listeners were removed
    expect(removeEventListenerSpy).toHaveBeenCalledWith('keydown', expect.any(Function));
    expect(removeEventListenerSpy).toHaveBeenCalledWith('click', expect.any(Function));
    
    // Restore the original method
    removeEventListenerSpy.mockRestore();
  });
});
