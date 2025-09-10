import { mount } from '@vue/test-utils';
import FeatureTour from '../../../../resources/js/components/onboarding/FeatureTour.vue';
import { nextTick } from 'vue';

// Mock the onboarding store
jest.mock('../../../../resources/js/stores/onboarding', () => ({
  useOnboardingStore: jest.fn(() => ({
    featureTours: {
      testTour: false
    },
    markFeatureTourCompleted: jest.fn()
  }))
}));

describe('FeatureTour.vue', () => {
  let wrapper;
  const mockSteps = [
    {
      title: 'Step 1',
      description: 'This is the first step',
      target: '.test-element-1',
      position: 'bottom'
    },
    {
      title: 'Step 2',
      description: 'This is the second step',
      target: '.test-element-2',
      position: 'right'
    }
  ];

  beforeEach(() => {
    // Create test elements in the DOM
    document.body.innerHTML = `
      <div class="test-element-1">Test Element 1</div>
      <div class="test-element-2">Test Element 2</div>
    `;

    // Mock Element.getBoundingClientRect
    Element.prototype.getBoundingClientRect = jest.fn(() => ({
      width: 100,
      height: 50,
      top: 100,
      left: 100,
      bottom: 150,
      right: 200
    }));

    // Mock window.innerWidth and window.innerHeight
    Object.defineProperty(window, 'innerWidth', { value: 1024 });
    Object.defineProperty(window, 'innerHeight', { value: 768 });

    // Mount component
    wrapper = mount(FeatureTour, {
      props: {
        tourId: 'testTour',
        steps: mockSteps,
        autoStart: false
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });
  });

  afterEach(() => {
    wrapper.unmount();
    document.body.innerHTML = '';
    jest.clearAllMocks();
  });

  test('renders correctly when inactive', () => {
    expect(wrapper.find('.feature-tour').exists()).toBe(true);
    expect(wrapper.find('.fixed').exists()).toBe(false);
  });

  test('starts tour when startTour method is called', async () => {
    wrapper.vm.startTour();
    await nextTick();
    
    expect(wrapper.vm.isActive).toBe(true);
    expect(wrapper.vm.currentStepIndex).toBe(0);
    expect(wrapper.find('.fixed').exists()).toBe(true);
    expect(wrapper.find('h3').text()).toBe('Step 1');
  });

  test('navigates to next step', async () => {
    wrapper.vm.startTour();
    await nextTick();
    
    wrapper.vm.nextStep();
    await nextTick();
    
    expect(wrapper.vm.currentStepIndex).toBe(1);
    expect(wrapper.find('h3').text()).toBe('Step 2');
  });

  test('navigates to previous step', async () => {
    wrapper.vm.startTour();
    await nextTick();
    
    // Go to step 2
    wrapper.vm.nextStep();
    await nextTick();
    
    // Go back to step 1
    wrapper.vm.prevStep();
    await nextTick();
    
    expect(wrapper.vm.currentStepIndex).toBe(0);
    expect(wrapper.find('h3').text()).toBe('Step 1');
  });

  test('completes tour when on last step and next is clicked', async () => {
    const onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    const completeSpy = jest.spyOn(wrapper.vm, 'completeTour');
    const emitSpy = jest.spyOn(wrapper.vm.$emit);
    
    wrapper.vm.startTour();
    await nextTick();
    
    // Go to last step
    wrapper.vm.nextStep();
    await nextTick();
    
    // Complete tour
    wrapper.vm.nextStep();
    await nextTick();
    
    expect(completeSpy).toHaveBeenCalled();
    expect(wrapper.vm.isActive).toBe(false);
    expect(onboardingStore.markFeatureTourCompleted).toHaveBeenCalledWith('testTour');
    expect(emitSpy).toHaveBeenCalledWith('complete');
  });

  test('skips tour when skip button is clicked', async () => {
    const emitSpy = jest.spyOn(wrapper.vm.$emit);
    
    wrapper.vm.startTour();
    await nextTick();
    
    wrapper.vm.skipTour();
    await nextTick();
    
    expect(wrapper.vm.isActive).toBe(false);
    expect(emitSpy).toHaveBeenCalledWith('skip');
  });
});
