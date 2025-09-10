import { mount } from '@vue/test-utils';
import PledgeFulfillmentReport from '../../../../resources/js/components/reports/PledgeFulfillmentReport.vue';
import { nextTick } from 'vue';

// Mock the donations store
jest.mock('../../../../resources/js/stores/donations', () => ({
  useDonationsStore: jest.fn(() => ({
    getCampaigns: jest.fn().mockResolvedValue([
      { id: 1, name: 'Building Fund' },
      { id: 2, name: 'Mission Trip' }
    ]),
    getPledges: jest.fn().mockResolvedValue([
      { 
        id: 1, 
        donor_name: 'John Doe', 
        campaign_name: 'Building Fund',
        campaign_id: 1,
        amount: 1000, 
        amount_received: 750,
        status: 'in_progress'
      },
      { 
        id: 2, 
        donor_name: 'Jane Smith', 
        campaign_name: 'Mission Trip',
        campaign_id: 2,
        amount: 500, 
        amount_received: 500,
        status: 'fulfilled'
      }
    ]),
    exportPledgeFulfillmentReport: jest.fn().mockResolvedValue(true)
  }))
}));

// Mock the onboarding store
jest.mock('../../../../resources/js/stores/onboarding', () => ({
  useOnboardingStore: jest.fn(() => ({
    featureTours: {
      pledgeFulfillmentReport: false
    },
    markFeatureTourCompleted: jest.fn()
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

// Mock Chart.js
jest.mock('chart.js/auto', () => {
  return jest.fn().mockImplementation(() => ({
    destroy: jest.fn()
  }));
});

describe('PledgeFulfillmentReport.vue', () => {
  let wrapper;

  beforeEach(() => {
    // Mount component
    wrapper = mount(PledgeFulfillmentReport, {
      global: {
        stubs: {
          'Modal': true,
          'FeatureTour': true,
          'ContextualHelp': true
        }
      }
    });
  });

  afterEach(() => {
    wrapper.unmount();
    jest.clearAllMocks();
  });

  test('renders correctly with initial state', () => {
    expect(wrapper.find('.pledge-fulfillment-report').exists()).toBe(true);
    expect(wrapper.find('h3').text()).toBe('Pledge Fulfillment Report');
    
    // Check that filters are rendered
    expect(wrapper.find('select[v-model="selectedCampaignId"]').exists()).toBe(true);
    expect(wrapper.find('select[v-model="selectedStatus"]').exists()).toBe(true);
    
    // Check that export button is rendered
    expect(wrapper.find('button[title="Export"]').exists()).toBe(true);
    expect(wrapper.find('button[title="Help"]').exists()).toBe(true);
  });

  test('loads campaigns and pledges on mount', async () => {
    const donationsStore = require('../../../../resources/js/stores/donations').useDonationsStore();
    
    // Wait for async operations to complete
    await nextTick();
    
    // Check that store methods were called
    expect(donationsStore.getCampaigns).toHaveBeenCalled();
    expect(donationsStore.getPledges).toHaveBeenCalled();
    
    // Check that data was loaded into component state
    expect(wrapper.vm.campaigns.length).toBe(2);
    expect(wrapper.vm.pledges.length).toBe(2);
  });

  test('filters pledges by search query', async () => {
    // Wait for data to load
    await nextTick();
    
    // Set search query
    wrapper.vm.searchQuery = 'John';
    await nextTick();
    
    // Check that pledges are filtered
    expect(wrapper.vm.filteredAndSortedPledges.length).toBe(1);
    expect(wrapper.vm.filteredAndSortedPledges[0].donor_name).toBe('John Doe');
  });

  test('sorts pledges by different criteria', async () => {
    // Wait for data to load
    await nextTick();
    
    // Sort by donor name
    wrapper.vm.sortBy = 'donor_name';
    await nextTick();
    
    // Check sorting order (alphabetical)
    expect(wrapper.vm.filteredAndSortedPledges[0].donor_name).toBe('Jane Smith');
    expect(wrapper.vm.filteredAndSortedPledges[1].donor_name).toBe('John Doe');
    
    // Sort by amount
    wrapper.vm.sortBy = 'amount';
    await nextTick();
    
    // Check sorting order (highest first)
    expect(wrapper.vm.filteredAndSortedPledges[0].amount).toBe(1000);
    expect(wrapper.vm.filteredAndSortedPledges[1].amount).toBe(500);
    
    // Sort by status
    wrapper.vm.sortBy = 'status';
    await nextTick();
    
    // Check sorting order (fulfilled first)
    expect(wrapper.vm.filteredAndSortedPledges[0].status).toBe('fulfilled');
    expect(wrapper.vm.filteredAndSortedPledges[1].status).toBe('in_progress');
  });

  test('calculates summary correctly', async () => {
    // Wait for data to load
    await nextTick();
    
    // Check summary calculations
    expect(wrapper.vm.summary.totalPledged).toBe(1500); // 1000 + 500
    expect(wrapper.vm.summary.amountReceived).toBe(1250); // 750 + 500
    expect(wrapper.vm.summary.amountRemaining).toBe(250); // 1500 - 1250
    expect(wrapper.vm.summary.fulfillmentRate).toBeCloseTo(83.33, 2); // (1250 / 1500) * 100
    expect(wrapper.vm.summary.pledgeCount).toBe(2);
  });

  test('shows export options when export button is clicked', async () => {
    // Initially, export options modal should not be shown
    expect(wrapper.vm.showExportOptions).toBe(false);
    
    // Click export button
    await wrapper.find('button').trigger('click');
    
    // Check that export options modal is now shown
    expect(wrapper.vm.showExportOptions).toBe(true);
  });

  test('exports report with selected options', async () => {
    const donationsStore = require('../../../../resources/js/stores/donations').useDonationsStore();
    const toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Set export options
    wrapper.vm.showExportOptions = true;
    wrapper.vm.selectedExportFormat = 'pdf';
    wrapper.vm.exportOptions = {
      includeSummary: true,
      includeCharts: true,
      includeDetails: true
    };
    await nextTick();
    
    // Call exportReport method
    await wrapper.vm.exportReport();
    
    // Check that store method was called with correct options
    expect(donationsStore.exportPledgeFulfillmentReport).toHaveBeenCalledWith({
      format: 'pdf',
      campaignId: '',
      status: '',
      options: {
        includeSummary: true,
        includeCharts: true,
        includeDetails: true
      }
    });
    
    // Check that modal is closed
    expect(wrapper.vm.showExportOptions).toBe(false);
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Report exported successfully as PDF');
  });

  test('starts help tour when help button is clicked', async () => {
    // Mock the featureTour ref
    wrapper.vm.featureTour = {
      startTour: jest.fn()
    };
    
    // Call showHelpTour method
    wrapper.vm.showHelpTour();
    
    // Check that startTour method was called
    expect(wrapper.vm.featureTour.startTour).toHaveBeenCalled();
  });

  test('marks tour as completed when tour is finished', async () => {
    const onboardingStore = require('../../../../resources/js/stores/onboarding').useOnboardingStore();
    const toast = require('../../../../resources/js/composables/useToast').useToast();
    
    // Call onTourComplete method
    wrapper.vm.onTourComplete();
    
    // Check that store method was called
    expect(onboardingStore.markFeatureTourCompleted).toHaveBeenCalledWith('pledgeFulfillmentReport');
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Tour completed! You now know how to use the Pledge Fulfillment Report.');
  });
});
