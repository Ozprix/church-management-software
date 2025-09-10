import { mount } from '@vue/test-utils';
import { setActivePinia, createPinia } from 'pinia';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import ExportSettings from '../../../../../resources/js/components/donations/settings/ExportSettings.vue';

// Mock the donation store
jest.mock('../../../../../resources/js/stores/donations', () => ({
  useDonationStore: jest.fn(() => ({
    saveExportSettings: jest.fn().mockResolvedValue({}),
    getExportSettings: jest.fn().mockResolvedValue({
      csvDelimiter: ',',
      csvEncoding: 'UTF-8',
      includeHeader: true,
      excelAutoFilter: true,
      excelFreezeHeader: true,
      excelAutoWidth: true,
      pdfPageSize: 'A4',
      pdfOrientation: 'portrait',
      pdfIncludeLogo: true,
      pdfIncludeFooter: true,
      pdfFooterText: 'Church Management System',
      defaultFields: ['id', 'donor_name', 'amount', 'date', 'payment_method']
    })
  }))
}));

// Mock the toast service
jest.mock('../../../../../resources/js/composables/useToast', () => ({
  useToast: jest.fn(() => ({
    success: jest.fn(),
    error: jest.fn()
  }))
}));

// Mock the form components
jest.mock('../../../../../resources/js/components/form/FormValidator.vue', () => ({
  name: 'FormValidator',
  template: '<div><slot :validate="validate" :errors="errors" :hasFieldError="hasFieldError" :getFieldError="getFieldError"></slot></div>',
  props: ['rules', 'modelValue'],
  setup() {
    return {
      validate: jest.fn().mockReturnValue(true),
      errors: {},
      hasFieldError: jest.fn().mockReturnValue(false),
      getFieldError: jest.fn().mockReturnValue('')
    };
  }
}));

jest.mock('../../../../../resources/js/components/form/FormField.vue', () => ({
  name: 'FormField',
  template: '<div><slot></slot></div>',
  props: ['id', 'label', 'error', 'helpText']
}));

jest.mock('../../../../../resources/js/components/ErrorBoundary.vue', () => ({
  name: 'ErrorBoundary',
  template: '<div><slot></slot></div>'
}));

describe('ExportSettings.vue', () => {
  let wrapper;
  let donationStore;
  let toast;

  beforeEach(() => {
    // Create a fresh pinia instance and make it active
    setActivePinia(createPinia());
    
    // Get the mocked store and toast
    donationStore = require('../../../../../resources/js/stores/donations').useDonationStore();
    toast = require('../../../../../resources/js/composables/useToast').useToast();
    
    // Mount the component
    wrapper = mount(ExportSettings);
  });

  it('renders correctly', () => {
    expect(wrapper.find('.export-settings').exists()).toBe(true);
    expect(wrapper.find('h3').text()).toContain('Export Settings');
  });

  it('loads export settings on mount', async () => {
    // Wait for component to load settings
    await wrapper.vm.$nextTick();
    
    // Check that getExportSettings was called
    expect(donationStore.getExportSettings).toHaveBeenCalled();
    
    // Check that settings are loaded into the component
    expect(wrapper.vm.exportSettings.csvDelimiter).toBe(',');
    expect(wrapper.vm.exportSettings.pdfPageSize).toBe('A4');
    expect(wrapper.vm.exportSettings.defaultFields).toContain('donor_name');
  });

  it('saves settings when form is submitted', async () => {
    // Set up the form data
    wrapper.vm.exportSettings = {
      csvDelimiter: ';',
      csvEncoding: 'UTF-8',
      includeHeader: true,
      excelAutoFilter: true,
      excelFreezeHeader: false,
      excelAutoWidth: true,
      pdfPageSize: 'Letter',
      pdfOrientation: 'landscape',
      pdfIncludeLogo: true,
      pdfIncludeFooter: true,
      pdfFooterText: 'Custom Footer',
      defaultFields: ['id', 'donor_name', 'amount', 'date']
    };
    
    // Trigger form submission
    await wrapper.find('form').trigger('submit.prevent');
    
    // Check that saveExportSettings was called with the correct data
    expect(donationStore.saveExportSettings).toHaveBeenCalledWith(wrapper.vm.exportSettings);
    
    // Check that success toast was shown
    expect(toast.success).toHaveBeenCalledWith('Export settings saved successfully');
  });

  it('handles validation errors', async () => {
    // Mock validation failure
    wrapper.vm.validate = jest.fn().mockReturnValue(false);
    
    // Trigger form submission
    await wrapper.find('form').trigger('submit.prevent');
    
    // Check that saveExportSettings was not called
    expect(donationStore.saveExportSettings).not.toHaveBeenCalled();
  });

  it('resets form to original values', async () => {
    // Change some settings
    wrapper.vm.exportSettings.csvDelimiter = ';';
    wrapper.vm.exportSettings.pdfPageSize = 'Letter';
    
    // Find and click the reset button
    const resetButton = wrapper.findAll('button').find(btn => btn.text().includes('Reset'));
    await resetButton.trigger('click');
    
    // Check that settings were reset to original values
    expect(wrapper.vm.exportSettings.csvDelimiter).toBe(',');
    expect(wrapper.vm.exportSettings.pdfPageSize).toBe('A4');
    
    // Check that toast was shown
    expect(toast.success).toHaveBeenCalledWith('Form reset to original values');
  });

  it('handles errors from ErrorBoundary', async () => {
    // Simulate an error
    const error = new Error('Test error');
    wrapper.vm.handleError({ error });
    
    // Check that error toast was shown
    expect(toast.error).toHaveBeenCalledWith('An error occurred: Test error');
  });

  it('toggles field selection correctly', async () => {
    // Initial state should have 'donor_name' selected
    expect(wrapper.vm.exportSettings.defaultFields).toContain('donor_name');
    
    // Find the checkbox for donor_name and uncheck it
    const checkbox = wrapper.findAll('input[type="checkbox"]')
      .find(input => input.attributes('id') === 'field-donor_name');
    
    if (checkbox) {
      await checkbox.setValue(false);
      
      // Check that donor_name was removed from defaultFields
      expect(wrapper.vm.exportSettings.defaultFields).not.toContain('donor_name');
    }
  });

  it('validates that at least one field is selected', () => {
    // Set up with no fields selected
    wrapper.vm.exportSettings.defaultFields = [];
    
    // Get the validator function
    const validator = wrapper.vm.validationRules.defaultFields[0].validator;
    
    // Check that validation fails with empty array
    expect(validator([])).toBe(false);
    
    // Check that validation passes with at least one field
    expect(validator(['donor_name'])).toBe(true);
  });
});
