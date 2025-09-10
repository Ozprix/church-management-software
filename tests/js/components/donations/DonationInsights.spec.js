import { mount } from '@vue/test-utils';
import DonationInsights from '../../../../resources/js/components/donations/DonationInsights.vue';
import { nextTick } from 'vue';

// Mock the donations store
jest.mock('../../../../resources/js/stores/donations', () => ({
  useDonationStore: jest.fn(() => ({
    getDonationInsights: jest.fn().mockResolvedValue({
      summary: {
        totalAmount: 25000,
        totalDonors: 45,
        averageDonation: 555.56,
        percentChange: 12.5,
        donorChange: 8.3,
        averageChange: 4.2
      },
      trends: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        values: [3000, 4500, 3800, 5200, 4100, 4400]
      },
      categories: {
        labels: ['Tithes', 'Offerings', 'Building Fund', 'Missions', 'Youth Ministry'],
        values: [12000, 5000, 4000, 2500, 1500]
      },
      campaigns: [
        {
          id: 1,
          name: 'Building Fund',
          goal: 50000,
          amountRaised: 25000,
          percentComplete: 50,
          endDate: '2025-12-31'
        },
        {
          id: 2,
          name: 'Mission Trip',
          goal: 10000,
          amountRaised: 8000,
          percentComplete: 80,
          endDate: '2025-08-15'
        }
      ]
    })
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

describe('DonationInsights.vue', () => {
  let wrapper;

  beforeEach(() => {
    // Mock document.documentElement.classList
    Object.defineProperty(document.documentElement, 'classList', {
      value: {
        contains: jest.fn().mockReturnValue(false)
      }
    });

    // Mock canvas context
    HTMLCanvasElement.prototype.getContext = jest.fn(() => ({
      clearRect: jest.fn(),
      beginPath: jest.fn(),
      arc: jest.fn(),
      fill: jest.fn(),
      stroke: jest.fn()
    }));

    // Mount component
    wrapper = mount(DonationInsights, {
      global: {
        stubs: {
          canvas: true
        }
      }
    });
  });

  afterEach(() => {
    wrapper.unmount();
    jest.clearAllMocks();
  });

  test('renders correctly with initial state', () => {
    expect(wrapper.find('.donation-insights').exists()).toBe(true);
    expect(wrapper.find('h3').text()).toBe('Donation Insights');
    
    // Check that time range selector is rendered
    expect(wrapper.find('select[v-model="timeRange"]').exists()).toBe(true);
    
    // Check that loading spinner is shown initially
    expect(wrapper.find('.spinner').exists()).toBe(true);
  });

  test('loads donation insights on mount', async () => {
    const donationStore = require('../../../../resources/js/stores/donations').useDonationStore();
    
    // Wait for async operations to complete
    await nextTick();
    
    // Check that store method was called
    expect(donationStore.getDonationInsights).toHaveBeenCalled();
    
    // Check that loading state is updated
    expect(wrapper.vm.loading).toBe(false);
    
    // Check that data was loaded into component state
    expect(wrapper.vm.summary.totalAmount).toBe(25000);
    expect(wrapper.vm.summary.totalDonors).toBe(45);
    expect(wrapper.vm.campaigns.length).toBe(2);
  });

  test('formats numbers correctly', () => {
    expect(wrapper.vm.formatNumber(1234.56)).toBe('1,234.56');
    expect(wrapper.vm.formatNumber(1000)).toBe('1,000.00');
    expect(wrapper.vm.formatNumber(0)).toBe('0.00');
  });

  test('formats dates correctly', () => {
    expect(wrapper.vm.formatDate('2025-12-31')).toMatch(/Dec 31, 2025/);
    expect(wrapper.vm.formatDate('2025-08-15')).toMatch(/Aug 15, 2025/);
  });

  test('calculates date range based on selected time range', () => {
    // Test week range
    wrapper.vm.timeRange = 'week';
    let { start, end } = wrapper.vm.calculateDateRange();
    expect(start).toBeInstanceOf(Date);
    expect(end).toBeInstanceOf(Date);
    expect(end - start).toBeLessThanOrEqual(7 * 24 * 60 * 60 * 1000); // 7 days in milliseconds
    
    // Test month range
    wrapper.vm.timeRange = 'month';
    ({ start, end } = wrapper.vm.calculateDateRange());
    expect(start.getDate()).toBe(1); // First day of month
    expect(end.getDate()).toBeGreaterThan(1); // Not first day of month
    
    // Test year range
    wrapper.vm.timeRange = 'year';
    ({ start, end } = wrapper.vm.calculateDateRange());
    expect(start.getMonth()).toBe(0); // January
    expect(start.getDate()).toBe(1); // First day of year
  });

  test('refreshes data when time range changes', async () => {
    const donationStore = require('../../../../resources/js/stores/donations').useDonationStore();
    
    // Wait for initial data load
    await nextTick();
    
    // Clear mock calls
    donationStore.getDonationInsights.mockClear();
    
    // Change time range
    wrapper.vm.timeRange = 'year';
    await nextTick();
    
    // Check that store method was called again
    expect(donationStore.getDonationInsights).toHaveBeenCalled();
  });

  test('refreshes data when custom date range changes', async () => {
    const donationStore = require('../../../../resources/js/stores/donations').useDonationStore();
    
    // Wait for initial data load
    await nextTick();
    
    // Set custom time range
    wrapper.vm.timeRange = 'custom';
    await nextTick();
    
    // Clear mock calls
    donationStore.getDonationInsights.mockClear();
    
    // Change custom date range
    wrapper.vm.startDate = '2025-01-01';
    wrapper.vm.endDate = '2025-06-30';
    await nextTick();
    
    // Check that store method was called again
    expect(donationStore.getDonationInsights).toHaveBeenCalled();
  });

  test('handles errors when fetching data', async () => {
    const donationStore = require('../../../../resources/js/stores/donations').useDonationStore();
    
    // Force an error
    donationStore.getDonationInsights.mockRejectedValueOnce(new Error('API error'));
    
    // Call refreshData method
    wrapper.vm.refreshData();
    
    // Wait for async operations to complete
    await nextTick();
    
    // Check that error state is updated
    expect(wrapper.vm.loading).toBe(false);
    expect(wrapper.vm.error).toBe('Failed to load donation insights. Please try again.');
  });

  test('updates charts when data changes', async () => {
    // Mock updateCharts method
    const updateChartsSpy = jest.spyOn(wrapper.vm, 'updateCharts');
    
    // Wait for initial data load
    await nextTick();
    
    // Check that updateCharts was called
    expect(updateChartsSpy).toHaveBeenCalled();
  });
});
