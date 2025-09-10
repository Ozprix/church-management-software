import api from './apiService';

/**
 * Donation API Service
 * Handles all API calls related to donations, pledges, and campaigns
 */
export const donationApiService = {
  /**
   * Donation Endpoints
   */
  donations: {
    // Get all donations with optional filtering
    getAll: async (params = {}) => {
      try {
        const response = await api.get('/donations', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching donations:', error);
        throw error;
      }
    },
    
    // Get a single donation by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/donations/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching donation ${id}:`, error);
        throw error;
      }
    },
    
    // Create a new donation
    create: async (donationData) => {
      try {
        const response = await api.post('/donations', donationData);
        return response.data;
      } catch (error) {
        console.error('Error creating donation:', error);
        throw error;
      }
    },
    
    // Update an existing donation
    update: async (id, donationData) => {
      try {
        const response = await api.put(`/donations/${id}`, donationData);
        return response.data;
      } catch (error) {
        console.error(`Error updating donation ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a donation
    delete: async (id) => {
      try {
        const response = await api.delete(`/donations/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting donation ${id}:`, error);
        throw error;
      }
    },
    
    // Generate receipt for a donation
    generateReceipt: async (id) => {
      try {
        const response = await api.post(`/donations/${id}/receipt`);
        return response.data;
      } catch (error) {
        console.error(`Error generating receipt for donation ${id}:`, error);
        throw error;
      }
    },
    
    // Get donation statistics
    getStats: async (params = {}) => {
      try {
        const response = await api.get('/donations/stats', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching donation statistics:', error);
        throw error;
      }
    },
    
    // Export donations
    export: async (format = 'csv', params = {}) => {
      try {
        const response = await api.get(`/donations/export/${format}`, { 
          params,
          responseType: 'blob'
        });
        return response.data;
      } catch (error) {
        console.error(`Error exporting donations to ${format}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Pledge Endpoints
   */
  pledges: {
    // Get all pledges with optional filtering
    getAll: async (params = {}) => {
      try {
        const response = await api.get('/pledges', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching pledges:', error);
        throw error;
      }
    },
    
    // Get a single pledge by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/pledges/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching pledge ${id}:`, error);
        throw error;
      }
    },
    
    // Create a new pledge
    create: async (pledgeData) => {
      try {
        const response = await api.post('/pledges', pledgeData);
        return response.data;
      } catch (error) {
        console.error('Error creating pledge:', error);
        throw error;
      }
    },
    
    // Update an existing pledge
    update: async (id, pledgeData) => {
      try {
        const response = await api.put(`/pledges/${id}`, pledgeData);
        return response.data;
      } catch (error) {
        console.error(`Error updating pledge ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a pledge
    delete: async (id) => {
      try {
        const response = await api.delete(`/pledges/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting pledge ${id}:`, error);
        throw error;
      }
    },
    
    // Get pledge fulfillment status
    getFulfillment: async (id) => {
      try {
        const response = await api.get(`/pledges/${id}/fulfillment`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching fulfillment for pledge ${id}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Campaign Endpoints
   */
  campaigns: {
    // Get all campaigns with optional filtering
    getAll: async (params = {}) => {
      try {
        const response = await api.get('/campaigns', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching campaigns:', error);
        throw error;
      }
    },
    
    // Get detailed campaign information including progress, donors, and statistics
    getDetails: async (id) => {
      try {
        const response = await api.get(`/campaigns/${id}/details`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching detailed information for campaign ${id}:`, error);
        throw error;
      }
    },
    
    // Get a single campaign by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/campaigns/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching campaign ${id}:`, error);
        throw error;
      }
    },
    
    // Create a new campaign
    create: async (campaignData) => {
      try {
        const response = await api.post('/campaigns', campaignData);
        return response.data;
      } catch (error) {
        console.error('Error creating campaign:', error);
        throw error;
      }
    },
    
    // Update an existing campaign
    update: async (id, campaignData) => {
      try {
        const response = await api.put(`/campaigns/${id}`, campaignData);
        return response.data;
      } catch (error) {
        console.error(`Error updating campaign ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a campaign
    delete: async (id) => {
      try {
        const response = await api.delete(`/campaigns/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting campaign ${id}:`, error);
        throw error;
      }
    },
    
    // Get campaign progress
    getProgress: async (id) => {
      try {
        const response = await api.get(`/campaigns/${id}/progress`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching progress for campaign ${id}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Donation Categories Endpoints
   */
  categories: {
    // Get all donation categories
    getAll: async () => {
      try {
        const response = await api.get('/donation-categories');
        return response.data;
      } catch (error) {
        console.error('Error fetching donation categories:', error);
        throw error;
      }
    },
    
    // Get a single category by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/donation-categories/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching donation category ${id}:`, error);
        throw error;
      }
    },
    
    // Create a new category
    create: async (categoryData) => {
      try {
        const response = await api.post('/donation-categories', categoryData);
        return response.data;
      } catch (error) {
        console.error('Error creating donation category:', error);
        throw error;
      }
    },
    
    // Update an existing category
    update: async (id, categoryData) => {
      try {
        const response = await api.put(`/donation-categories/${id}`, categoryData);
        return response.data;
      } catch (error) {
        console.error(`Error updating donation category ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a category
    delete: async (id) => {
      try {
        const response = await api.delete(`/donation-categories/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting donation category ${id}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Payment Methods Endpoints
   */
  paymentMethods: {
    // Get all payment methods
    getAll: async () => {
      try {
        const response = await api.get('/payment-methods');
        return response.data;
      } catch (error) {
        console.error('Error fetching payment methods:', error);
        throw error;
      }
    },
    
    // Get a single payment method by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/payment-methods/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching payment method ${id}:`, error);
        throw error;
      }
    },
    
    // Create a new payment method
    create: async (methodData) => {
      try {
        const response = await api.post('/payment-methods', methodData);
        return response.data;
      } catch (error) {
        console.error('Error creating payment method:', error);
        throw error;
      }
    },
    
    // Update an existing payment method
    update: async (id, methodData) => {
      try {
        const response = await api.put(`/payment-methods/${id}`, methodData);
        return response.data;
      } catch (error) {
        console.error(`Error updating payment method ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a payment method
    delete: async (id) => {
      try {
        const response = await api.delete(`/payment-methods/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting payment method ${id}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Donors Endpoints
   */
  donors: {
    // Get all donors
    getAll: async (params = {}) => {
      try {
        const response = await api.get('/donors', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching donors:', error);
        throw error;
      }
    },
    
    // Get a single donor by ID
    getById: async (id) => {
      try {
        const response = await api.get(`/donors/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching donor ${id}:`, error);
        throw error;
      }
    },
    
    // Get contributions for a specific donor
    getContributions: async (id, params = {}) => {
      try {
        const response = await api.get(`/donors/${id}/contributions`, { params });
        return response.data;
      } catch (error) {
        console.error(`Error fetching contributions for donor ${id}:`, error);
        throw error;
      }
    },
    
    // Get donor statistics
    getStats: async (params = {}) => {
      try {
        const response = await api.get('/donors/stats', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching donor statistics:', error);
        throw error;
      }
    }
  },
  
  /**
   * Reports Endpoints
   */
  reports: {
    // Get donation summary report
    getSummary: async (params = {}) => {
      try {
        const response = await api.get('/reports/donations/summary', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching donation summary report:', error);
        throw error;
      }
    },
    
    // Get pledge fulfillment report
    getPledgeFulfillment: async (params = {}) => {
      try {
        const response = await api.get('/reports/pledges/fulfillment', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching pledge fulfillment report:', error);
        throw error;
      }
    },
    
    // Get campaign progress report
    getCampaignProgress: async (campaignId, params = {}) => {
      try {
        const response = await api.get(`/reports/campaigns/${campaignId}/progress`, { params });
        return response.data;
      } catch (error) {
        console.error(`Error fetching campaign progress report for campaign ${campaignId}:`, error);
        throw error;
      }
    },
    
    // Get donor contribution report
    getDonorContribution: async (donorId, params = {}) => {
      try {
        const response = await api.get(`/reports/donors/${donorId}/contributions`, { params });
        return response.data;
      } catch (error) {
        console.error(`Error fetching donor contribution report for donor ${donorId}:`, error);
        throw error;
      }
    },
    
    // Get category analysis report
    getCategoryAnalysis: async (params = {}) => {
      try {
        const response = await api.get('/reports/categories/analysis', { params });
        return response.data;
      } catch (error) {
        console.error('Error fetching category analysis report:', error);
        throw error;
      }
    },
    
    // Save a report
    saveReport: async (reportData) => {
      try {
        const response = await api.post('/reports/save', reportData);
        return response.data;
      } catch (error) {
        console.error('Error saving report:', error);
        throw error;
      }
    },
    
    // Get saved reports
    getSavedReports: async () => {
      try {
        const response = await api.get('/reports/saved');
        return response.data;
      } catch (error) {
        console.error('Error fetching saved reports:', error);
        throw error;
      }
    },
    
    // Get a saved report by ID
    getSavedReportById: async (id) => {
      try {
        const response = await api.get(`/reports/saved/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching saved report ${id}:`, error);
        throw error;
      }
    },
    
    // Delete a saved report
    deleteSavedReport: async (id) => {
      try {
        const response = await api.delete(`/reports/saved/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error deleting saved report ${id}:`, error);
        throw error;
      }
    },
    
    // Export a report
    exportReport: async (format, params = {}) => {
      try {
        const response = await api.get(`/reports/export/${format}`, {
          params,
          responseType: 'blob'
        });
        return response.data;
      } catch (error) {
        console.error(`Error exporting report as ${format}:`, error);
        throw error;
      }
    }
  },
  
  /**
   * Settings Endpoints
   */
  settings: {
    // Get donation settings
    getSettings: async () => {
      try {
        const response = await api.get('/donation-settings');
        return response.data;
      } catch (error) {
        console.error('Error fetching donation settings:', error);
        throw error;
      }
    },
    
    // Update donation settings
    updateSettings: async (settingsData) => {
      try {
        const response = await api.put('/donation-settings', settingsData);
        return response.data;
      } catch (error) {
        console.error('Error updating donation settings:', error);
        throw error;
      }
    },
    
    // Get receipt settings
    getReceiptSettings: async () => {
      try {
        const response = await api.get('/donation-settings/receipt');
        return response.data;
      } catch (error) {
        console.error('Error fetching receipt settings:', error);
        throw error;
      }
    },
    
    // Update receipt settings
    updateReceiptSettings: async (settingsData) => {
      try {
        const response = await api.put('/donation-settings/receipt', settingsData);
        return response.data;
      } catch (error) {
        console.error('Error updating receipt settings:', error);
        throw error;
      }
    },
    
    // Get export settings
    getExportSettings: async () => {
      try {
        const response = await api.get('/donation-settings/export');
        return response.data;
      } catch (error) {
        console.error('Error fetching export settings:', error);
        throw error;
      }
    },
    
    // Update export settings
    updateExportSettings: async (settingsData) => {
      try {
        const response = await api.put('/donation-settings/export', settingsData);
        return response.data;
      } catch (error) {
        console.error('Error updating export settings:', error);
        throw error;
      }
    }
  }
};

export default donationApiService;
