import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { v4 as uuidv4 } from 'uuid';
import donationApiService from '../services/donationApiService';
import { useToastService } from '../services/toastService';

export const useDonationStore = defineStore('donations', () => {
  // State
  const donations = ref([]);
  const pledges = ref([]);
  const campaigns = ref([]);
  const pledgeCampaigns = ref([]);
  const donationCategories = ref([
    { id: 'cat-1', name: 'Tithe', description: 'Regular tithe (10% of income)', color: '#4F46E5' },
    { id: 'cat-2', name: 'Offering', description: 'General offering', color: '#10B981' },
    { id: 'cat-3', name: 'Building Fund', description: 'Donations for church building projects', color: '#F59E0B' },
    { id: 'cat-4', name: 'Missions', description: 'Support for mission work', color: '#EF4444' },
    { id: 'cat-5', name: 'Benevolence', description: 'Helping those in need', color: '#8B5CF6' },
    { id: 'cat-6', name: 'Youth Ministry', description: 'Support for youth programs', color: '#EC4899' },
    { id: 'cat-7', name: 'Children\'s Ministry', description: 'Support for children\'s programs', color: '#06B6D4' },
    { id: 'cat-8', name: 'Special Projects', description: 'One-time special projects', color: '#6366F1' }
  ]);
  const paymentMethods = ref([
    { id: 'method-1', name: 'Cash', icon: 'cash' },
    { id: 'method-2', name: 'Check', icon: 'check' },
    { id: 'method-3', name: 'Credit Card', icon: 'credit-card' },
    { id: 'method-4', name: 'Bank Transfer', icon: 'bank' },
    { id: 'method-5', name: 'Mobile Money', icon: 'mobile' },
    { id: 'method-6', name: 'Online Payment', icon: 'globe' }
  ]);
  const isLoading = ref(false);
  const error = ref(null);
  
  // Receipt and Export Settings
  const receiptSettings = ref({
    orgName: 'Your Church',
    taxId: '12-3456789',
    orgAddress: '123 Church Street, Cityville, ST 12345',
    receiptTitle: 'Donation Receipt',
    thankYouMessage: 'Thank you for your generous donation to our church. Your contribution helps us continue our mission and ministry.',
    footerMessage: 'Thank you for your continued support!',
    taxDeductibleMessage: 'No goods or services were provided in exchange for this contribution. This organization is a tax-exempt 501(c)(3) organization. Your donation may be tax-deductible to the extent allowed by law.',
    autoGenerate: true,
    emailReceipt: false,
    includeLogo: true,
    includeSignature: true
  });
  
  const exportSettings = ref({
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
    selectedFields: [
      'id', 'date', 'amount', 'memberName', 'categoryName', 'paymentMethodName', 'notes'
    ]
  });
  
  // Toast service for notifications
  const toast = useToastService();
  
  // Fetch data from API
  const fetchDonations = async (params = {}) => {
    isLoading.value = true;
    error.value = null;
    
    try {
      const response = await donationApiService.getDonations(params);
      donations.value = response.data;
      return response;
    } catch (err) {
      console.error('Error fetching donations:', err);
      error.value = err.message || 'Failed to fetch donations';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Fetch pledge campaigns from API
  const fetchPledgeCampaigns = async (params = {}) => {
    isLoading.value = true;
    error.value = null;
    
    try {
      const response = await donationApiService.getPledgeCampaigns(params);
      pledgeCampaigns.value = response.data;
      return response;
    } catch (err) {
      console.error('Error fetching pledge campaigns:', err);
      error.value = err.message || 'Failed to fetch pledge campaigns';
      
      // Fallback to sample data in development
      if (process.env.NODE_ENV === 'development') {
        const sampleData = {
          data: initializeSamplePledgeCampaigns()
        };
        pledgeCampaigns.value = sampleData.data;
        return sampleData;
      }
      
      throw err;
    } finally {
      isLoading.value = false;
    }
  };
  
  const fetchPledges = async (params = {}) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.pledges.getAll(params);
      pledges.value = response.data;
      
      return response;
    } catch (err) {
      console.error('Error fetching pledges:', err);
      error.value = err.message || 'Failed to fetch pledges';
      toast.error('Failed to fetch pledges');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  const fetchCampaigns = async (params = {}) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.campaigns.getAll(params);
      campaigns.value = response.data;
      
      return response;
    } catch (err) {
      console.error('Error fetching campaigns:', err);
      error.value = err.message || 'Failed to fetch campaigns';
      toast.error('Failed to fetch campaigns');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  const fetchCategories = async () => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.categories.getAll();
      donationCategories.value = response.data;
      
      return response;
    } catch (err) {
      console.error('Error fetching donation categories:', err);
      error.value = err.message || 'Failed to fetch donation categories';
      toast.error('Failed to fetch donation categories');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  const fetchPaymentMethods = async () => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.paymentMethods.getAll();
      paymentMethods.value = response.data;
      
      return response;
    } catch (err) {
      console.error('Error fetching payment methods:', err);
      error.value = err.message || 'Failed to fetch payment methods';
      toast.error('Failed to fetch payment methods');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  const fetchSettings = async () => {
    try {
      isLoading.value = true;
      error.value = null;
      
      // Fetch receipt settings
      const receiptResponse = await donationApiService.settings.getReceiptSettings();
      receiptSettings.value = receiptResponse.data;
      
      // Fetch export settings
      const exportResponse = await donationApiService.settings.getExportSettings();
      exportSettings.value = exportResponse.data;
      
      return { receiptSettings: receiptResponse.data, exportSettings: exportResponse.data };
    } catch (err) {
      console.error('Error fetching donation settings:', err);
      error.value = err.message || 'Failed to fetch donation settings';
      toast.error('Failed to fetch donation settings');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Initialize data
  const initializeData = () => {
    // In development, use sample data
    if (process.env.NODE_ENV === 'development') {
      initializeSampleData();
    } else {
      // In production, fetch from API
      fetchDonations();
      fetchPledges();
      fetchCampaigns();
      fetchPledgeCampaigns();
      fetchCategories();
      fetchPaymentMethods();
      fetchSettings();
    }
  };
  
  // Sample data for development
  const initializeSampleData = () => {
    // Initialize sample pledge campaigns
    pledgeCampaigns.value = initializeSamplePledgeCampaigns();
    // Only initialize if empty
    if (donations.value.length === 0) {
      const sampleDonations = [
        {
          id: 'don-1',
          memberId: 'member-1',
          memberName: 'John Smith',
          amount: 500.00,
          date: '2025-05-01T10:30:00',
          categoryId: 'cat-1',
          paymentMethodId: 'method-1',
          notes: 'Monthly tithe',
          isRecurring: true,
          recurringFrequency: 'monthly',
          campaignId: null,
          receiptSent: true,
          receiptNumber: 'R-2025-001',
          taxDeductible: true
        },
        {
          id: 'don-2',
          memberId: 'member-2',
          memberName: 'Sarah Johnson',
          amount: 100.00,
          date: '2025-05-05T11:15:00',
          categoryId: 'cat-2',
          paymentMethodId: 'method-3',
          notes: 'Sunday offering',
          isRecurring: false,
          recurringFrequency: null,
          campaignId: null,
          receiptSent: true,
          receiptNumber: 'R-2025-002',
          taxDeductible: true
        },
        {
          id: 'don-3',
          memberId: 'member-3',
          memberName: 'Michael Williams',
          amount: 1000.00,
          date: '2025-05-10T09:45:00',
          categoryId: 'cat-3',
          paymentMethodId: 'method-2',
          notes: 'Building fund donation',
          isRecurring: false,
          recurringFrequency: null,
          campaignId: 'campaign-1',
          receiptSent: true,
          receiptNumber: 'R-2025-003',
          taxDeductible: true
        },
        {
          id: 'don-4',
          memberId: 'member-4',
          memberName: 'Emily Davis',
          amount: 250.00,
          date: '2025-05-15T14:20:00',
          categoryId: 'cat-4',
          paymentMethodId: 'method-4',
          notes: 'Mission trip support',
          isRecurring: false,
          recurringFrequency: null,
          campaignId: 'campaign-2',
          receiptSent: false,
          receiptNumber: null,
          taxDeductible: true
        },
        {
          id: 'don-5',
          memberId: 'member-5',
          memberName: 'David Miller',
          amount: 50.00,
          date: '2025-05-19T16:30:00',
          categoryId: 'cat-5',
          paymentMethodId: 'method-1',
          notes: 'Benevolence fund',
          isRecurring: true,
          recurringFrequency: 'monthly',
          campaignId: null,
          receiptSent: false,
          receiptNumber: null,
          taxDeductible: true
        }
      ];
      
      const samplePledges = [
        {
          id: 'pledge-1',
          memberId: 'member-1',
          memberName: 'John Smith',
          amount: 6000.00,
          startDate: '2025-01-01T00:00:00',
          endDate: '2025-12-31T23:59:59',
          categoryId: 'cat-1',
          frequency: 'monthly',
          amountPerFrequency: 500.00,
          campaignId: null,
          notes: 'Annual tithe pledge',
          fulfilled: 2000.00,
          status: 'in-progress'
        },
        {
          id: 'pledge-2',
          memberId: 'member-3',
          memberName: 'Michael Williams',
          amount: 5000.00,
          startDate: '2025-03-01T00:00:00',
          endDate: '2025-08-31T23:59:59',
          categoryId: 'cat-3',
          frequency: 'monthly',
          amountPerFrequency: 1000.00,
          campaignId: 'campaign-1',
          notes: 'Building fund pledge',
          fulfilled: 1000.00,
          status: 'in-progress'
        },
        {
          id: 'pledge-3',
          memberId: 'member-6',
          memberName: 'Jennifer Brown',
          amount: 1200.00,
          startDate: '2025-04-01T00:00:00',
          endDate: '2025-12-31T23:59:59',
          categoryId: 'cat-4',
          frequency: 'monthly',
          amountPerFrequency: 100.00,
          campaignId: 'campaign-2',
          notes: 'Missions pledge',
          fulfilled: 200.00,
          status: 'in-progress'
        }
      ];
      
      const sampleCampaigns = [
        {
          id: 'campaign-1',
          name: 'New Building Fund',
          description: 'Campaign to raise funds for the new church building',
          goal: 500000.00,
          startDate: '2025-01-01T00:00:00',
          endDate: '2025-12-31T23:59:59',
          raised: 125000.00,
          status: 'active',
          categoryId: 'cat-3',
          image: null
        },
        {
          id: 'campaign-2',
          name: 'Summer Mission Trip',
          description: 'Fundraising for the youth summer mission trip to Mexico',
          goal: 15000.00,
          startDate: '2025-03-01T00:00:00',
          endDate: '2025-06-30T23:59:59',
          raised: 8500.00,
          status: 'active',
          categoryId: 'cat-4',
          image: null
        },
        {
          id: 'campaign-3',
          name: 'Christmas Charity Drive',
          description: 'Annual Christmas charity drive for local families in need',
          goal: 10000.00,
          startDate: '2025-11-01T00:00:00',
          endDate: '2025-12-25T23:59:59',
          raised: 0.00,
          status: 'planned',
          categoryId: 'cat-5',
          image: null
        }
      ];
      
      donations.value = sampleDonations;
      pledges.value = samplePledges;
      campaigns.value = sampleCampaigns;
    }
  };
  
  // Initialize sample pledge campaigns
  const initializeSamplePledgeCampaigns = () => {
    const currentDate = new Date();
    const oneYearLater = new Date(currentDate);
    oneYearLater.setFullYear(currentDate.getFullYear() + 1);
    
    const sixMonthsAgo = new Date(currentDate);
    sixMonthsAgo.setMonth(currentDate.getMonth() - 6);
    
    const threeMonthsLater = new Date(currentDate);
    threeMonthsLater.setMonth(currentDate.getMonth() + 3);
    
    return [
      {
        id: 'pledge-campaign-1',
        name: 'Building Fund 2025',
        description: 'Campaign to raise funds for our new church building project. This will help us expand our ministry and serve more people in our community.',
        goal: 500000,
        current_amount: 325000,
        start_date: sixMonthsAgo.toISOString().split('T')[0],
        end_date: oneYearLater.toISOString().split('T')[0],
        status: 'active',
        icon: 'fas fa-church',
        pledges_count: 78,
        donors_count: 65,
        category_id: 'cat-3'
      },
      {
        id: 'pledge-campaign-2',
        name: 'Mission Trip to Kenya',
        description: 'Support our mission team as they travel to Kenya to build a school and provide medical assistance.',
        goal: 75000,
        current_amount: 68500,
        start_date: sixMonthsAgo.toISOString().split('T')[0],
        end_date: threeMonthsLater.toISOString().split('T')[0],
        status: 'active',
        icon: 'fas fa-globe-americas',
        pledges_count: 42,
        donors_count: 38,
        category_id: 'cat-4'
      },
      {
        id: 'pledge-campaign-3',
        name: 'Youth Ministry Expansion',
        description: 'Help us expand our youth ministry program with new equipment, resources, and events.',
        goal: 25000,
        current_amount: 25000,
        start_date: sixMonthsAgo.toISOString().split('T')[0],
        end_date: sixMonthsAgo.toISOString().split('T')[0],
        status: 'completed',
        icon: 'fas fa-graduation-cap',
        pledges_count: 35,
        donors_count: 30,
        category_id: 'cat-6'
      },
      {
        id: 'pledge-campaign-4',
        name: 'Christmas Outreach 2025',
        description: 'Annual campaign to provide gifts, food, and necessities to families in need during the holiday season.',
        goal: 15000,
        current_amount: 0,
        start_date: threeMonthsLater.toISOString().split('T')[0],
        end_date: oneYearLater.toISOString().split('T')[0],
        status: 'upcoming',
        icon: 'fas fa-hands-helping',
        pledges_count: 0,
        donors_count: 0,
        category_id: 'cat-5'
      }
    ];
  };
  
  // Get donor list
  const getDonors = async () => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.donors.getAll();
      return response;
    } catch (err) {
      console.error('Error fetching donors:', err);
      error.value = err.message || 'Failed to fetch donors';
      toast.error('Failed to fetch donors');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Get donor contributions
  const getDonorContributions = async (donorId) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await donationApiService.donors.getContributions(donorId);
      return response;
    } catch (err) {
      console.error(`Error fetching contributions for donor ${donorId}:`, err);
      error.value = err.message || 'Failed to fetch donor contributions';
      toast.error('Failed to fetch donor contributions');
      return null;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Get campaign details
  const getCampaignDetails = (campaignId) => {
    const campaign = campaigns.value.find(c => c.id === campaignId);
    
    if (!campaign) {
      return null;
    }
    
    const campaignDonations = donations.value.filter(d => d.campaign_id === campaignId);
    const campaignPledges = pledges.value.filter(p => p.campaign_id === campaignId);
    
    return {
      ...campaign,
      donations: campaignDonations,
      pledges: campaignPledges
    };
  };
  
  // Get pledge campaign details
  const getPledgeCampaignDetails = (campaignId) => {
    const campaign = pledgeCampaigns.value.find(c => c.id === campaignId);
    
    if (!campaign) {
      return null;
    }
    
    // Get related pledges and donations
    const campaignPledges = pledges.value.filter(p => p.campaign_id === campaignId);
    const campaignDonations = donations.value.filter(d => d.campaign_id === campaignId);
    
    // Calculate additional metrics
    const totalPledged = campaignPledges.reduce((sum, pledge) => sum + pledge.amount, 0);
    const averagePledge = campaignPledges.length > 0 ? totalPledged / campaignPledges.length : 0;
    
    // Group pledges by status
    const pledgesByStatus = campaignPledges.reduce((acc, pledge) => {
      const status = pledge.status || 'pending';
      if (!acc[status]) acc[status] = [];
      acc[status].push(pledge);
      return acc;
    }, {});
    
    // Calculate fulfillment rate
    const fulfillmentRate = campaign.goal > 0 ? campaign.current_amount / campaign.goal : 0;
    
    return {
      ...campaign,
      pledges: campaignPledges,
      donations: campaignDonations,
      metrics: {
        total_pledged: totalPledged,
        average_pledge: averagePledge,
        fulfillment_rate: fulfillmentRate,
        pledges_by_status: pledgesByStatus
      }
    };
  };
  
  // Getters
  const totalDonations = computed(() => {
    return donations.value.reduce((total, donation) => total + donation.amount, 0);
  });
  
  const totalByCategory = computed(() => {
    const totals = {};
    donationCategories.value.forEach(category => {
      totals[category.id] = donations.value
        .filter(donation => donation.categoryId === category.id)
        .reduce((total, donation) => total + donation.amount, 0);
    });
    return totals;
  });
  
  const totalPledged = computed(() => {
    return pledges.value.reduce((total, pledge) => total + pledge.amount, 0);
  });
  
  const totalFulfilled = computed(() => {
    return pledges.value.reduce((total, pledge) => total + pledge.fulfilled, 0);
  });
  
  const totalCampaignGoals = computed(() => {
    return campaigns.value.reduce((total, campaign) => total + campaign.goal, 0);
  });
  
  const totalCampaignRaised = computed(() => {
    return campaigns.value.reduce((total, campaign) => total + campaign.raised, 0);
  });
  
  const donationsByDate = computed(() => {
    const sorted = [...donations.value].sort((a, b) => {
      return new Date(b.date) - new Date(a.date);
    });
    return sorted;
  });
  
  const activeCampaigns = computed(() => {
    return campaigns.value.filter(campaign => campaign.status === 'active');
  });
  
  const getCategoryById = (id) => {
    return donationCategories.value.find(category => category.id === id);
  };
  
  const getPaymentMethodById = (id) => {
    return paymentMethods.value.find(method => method.id === id);
  };
  
  const getCampaignById = (id) => {
    return campaigns.value.find(campaign => campaign.id === id);
  };
  
  // Actions
  const addDonation = (donation) => {
    const newDonation = {
      id: donation.id || `don-${uuidv4()}`,
      ...donation,
      date: donation.date || new Date().toISOString(),
      receiptSent: donation.receiptSent || false,
      receiptNumber: donation.receiptNumber || null,
      taxDeductible: donation.taxDeductible || true
    };
    
    donations.value.push(newDonation);
    return newDonation;
  };
  
  const updateDonation = (id, updates) => {
    const index = donations.value.findIndex(donation => donation.id === id);
    if (index !== -1) {
      const donation = donations.value[index];
      
      // Update campaign raised amount if applicable
      if (donation.campaignId) {
        const campaign = campaigns.value.find(c => c.id === donation.campaignId);
        if (campaign) {
          campaign.raised = campaign.raised - donations.value[index].amount + updates.amount;
        }
      }
      
      // Update the donation
      donations.value[index] = {
        ...donations.value[index],
        ...updates
      };
      
      return donations.value[index];
    }
    return null;
  };
  
  const deleteDonation = (id) => {
    const index = donations.value.findIndex(donation => donation.id === id);
    if (index !== -1) {
      const donation = donations.value[index];
      
      // Update campaign raised amount if applicable
      if (donation.campaignId) {
        const campaign = campaigns.value.find(c => c.id === donation.campaignId);
        if (campaign) {
          campaign.raised -= donation.amount;
        }
      }
      
      // Update pledge fulfilled amount if applicable
      if (donation.pledgeId) {
        const pledge = pledges.value.find(p => p.id === donation.pledgeId);
        if (pledge) {
          pledge.fulfilled -= donation.amount;
          
          // Update pledge status
          if (pledge.fulfilled < pledge.amount) {
            pledge.status = 'in-progress';
          }
        }
      }
      
      donations.value.splice(index, 1);
      return true;
    }
    return false;
  };
  
  const addPledge = (pledge) => {
    const newPledge = {
      id: pledge.id || `pledge-${uuidv4()}`,
      ...pledge,
      startDate: pledge.startDate || new Date().toISOString(),
      fulfilled: pledge.fulfilled || 0,
      status: pledge.status || 'in-progress'
    };
    
    pledges.value.push(newPledge);
    return newPledge;
  };
  
  const updatePledge = (id, updates) => {
    const index = pledges.value.findIndex(pledge => pledge.id === id);
    if (index !== -1) {
      pledges.value[index] = {
        ...pledges.value[index],
        ...updates
      };
      
      return pledges.value[index];
    }
    return null;
  };
  
  const deletePledge = (id) => {
    const index = pledges.value.findIndex(pledge => pledge.id === id);
    if (index !== -1) {
      pledges.value.splice(index, 1);
      return true;
    }
    return false;
  };
  
  const addCampaign = (campaign) => {
    const newCampaign = {
      id: campaign.id || `campaign-${uuidv4()}`,
      ...campaign,
      startDate: campaign.startDate || new Date().toISOString(),
      raised: campaign.raised || 0,
      status: campaign.status || 'active'
    };
    
    campaigns.value.push(newCampaign);
    return newCampaign;
  };
  
  const updateCampaign = (id, updates) => {
    const index = campaigns.value.findIndex(campaign => campaign.id === id);
    if (index !== -1) {
      campaigns.value[index] = {
        ...campaigns.value[index],
        ...updates
      };
      
      return campaigns.value[index];
    }
    return null;
  };
  
  const deleteCampaign = (id) => {
    const index = campaigns.value.findIndex(campaign => campaign.id === id);
    if (index !== -1) {
      // Check if there are donations associated with this campaign
      const hasDonations = donations.value.some(donation => donation.campaignId === id);
      if (hasDonations) {
        return false; // Cannot delete campaign with associated donations
      }
      
      campaigns.value.splice(index, 1);
      return true;
    }
    return false;
  };
  
  const addDonationCategory = (category) => {
    const newCategory = {
      id: category.id || `cat-${uuidv4()}`,
      ...category
    };
    
    donationCategories.value.push(newCategory);
    return newCategory;
  };
  
  const updateDonationCategory = (id, updates) => {
    const index = donationCategories.value.findIndex(category => category.id === id);
    if (index !== -1) {
      donationCategories.value[index] = {
        ...donationCategories.value[index],
        ...updates
      };
      
      return donationCategories.value[index];
    }
    return null;
  };
  
  const deleteDonationCategory = (id) => {
    const index = donationCategories.value.findIndex(category => category.id === id);
    if (index !== -1) {
      // Check if there are donations or campaigns using this category
      const isInUse = donations.value.some(donation => donation.categoryId === id) ||
                      campaigns.value.some(campaign => campaign.categoryId === id);
      
      if (isInUse) {
        return false; // Cannot delete category in use
      }
      
      donationCategories.value.splice(index, 1);
      return true;
    }
    return false;
  };
  
  const addPaymentMethod = (method) => {
    const newMethod = {
      id: method.id || `method-${uuidv4()}`,
      ...method
    };
    
    paymentMethods.value.push(newMethod);
    return newMethod;
  };
  
  const updatePaymentMethod = (id, updates) => {
    const index = paymentMethods.value.findIndex(method => method.id === id);
    if (index !== -1) {
      paymentMethods.value[index] = {
        ...paymentMethods.value[index],
        ...updates
      };
      
      return paymentMethods.value[index];
    }
    return null;
  };
  
  const deletePaymentMethod = (id) => {
    const index = paymentMethods.value.findIndex(method => method.id === id);
    if (index !== -1) {
      // Check if there are donations using this payment method
      const isInUse = donations.value.some(donation => donation.paymentMethodId === id);
      
      if (isInUse) {
        return false; // Cannot delete method in use
      }
      
      paymentMethods.value.splice(index, 1);
      return true;
    }
    return false;
  };
  
  // Generate receipt number
  const generateReceiptNumber = () => {
    const year = new Date().getFullYear();
    const lastReceipt = donations.value
      .filter(d => d.receiptNumber && d.receiptNumber.startsWith(`R-${year}`))
      .sort((a, b) => {
        const numA = parseInt(a.receiptNumber.split('-')[2]);
        const numB = parseInt(b.receiptNumber.split('-')[2]);
        return numB - numA;
      })[0];
    
    let nextNumber = 1;
    if (lastReceipt && lastReceipt.receiptNumber) {
      const parts = lastReceipt.receiptNumber.split('-');
      if (parts.length === 3) {
        nextNumber = parseInt(parts[2]) + 1;
      }
    }
    
    return `R-${year}-${nextNumber.toString().padStart(3, '0')}`;
  };
  
  // Update receipt settings
  const updateReceiptSettings = async (settings) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      // For offline mode or development without API
      if (process.env.NODE_ENV === 'development' && process.env.VUE_APP_USE_MOCK_DATA === 'true') {
        receiptSettings.value = {
          ...receiptSettings.value,
          ...settings
        };
        return true;
      }
      
      // Use API service
      await donationApiService.settings.updateReceiptSettings(settings);
      
      // Update local state
      receiptSettings.value = {
        ...receiptSettings.value,
        ...settings
      };
      
      // Show success notification
      toast.success('Receipt settings updated successfully');
      
      return true;
    } catch (err) {
      console.error('Error updating receipt settings:', err);
      error.value = err.message || 'Failed to update receipt settings';
      toast.error('Failed to update receipt settings');
      return false;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Update export settings
  const updateExportSettings = async (settings) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      // For offline mode or development without API
      if (process.env.NODE_ENV === 'development' && process.env.VUE_APP_USE_MOCK_DATA === 'true') {
        exportSettings.value = {
          ...exportSettings.value,
          ...settings
        };
        return true;
      }
      
      // Use API service
      await donationApiService.settings.updateExportSettings(settings);
      
      // Update local state
      exportSettings.value = {
        ...exportSettings.value,
        ...settings
      };
      
      // Show success notification
      toast.success('Export settings updated successfully');
      
      return true;
    } catch (err) {
      console.error('Error updating export settings:', err);
      error.value = err.message || 'Failed to update export settings';
      toast.error('Failed to update export settings');
      return false;
    } finally {
      isLoading.value = false;
    }
  };
  
  // Reports functionality
  const reports = {
    // Get donation summary report
    getSummary: async (params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getSummary(params);
        return response;
      } catch (err) {
        console.error('Error fetching donation summary report:', err);
        error.value = err.message || 'Failed to fetch donation summary report';
        toast.error('Failed to fetch donation summary report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get pledge fulfillment report
    getPledgeFulfillment: async (params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getPledgeFulfillment(params);
        return response;
      } catch (err) {
        console.error('Error fetching pledge fulfillment report:', err);
        error.value = err.message || 'Failed to fetch pledge fulfillment report';
        toast.error('Failed to fetch pledge fulfillment report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get campaign progress report
    getCampaignProgress: async (campaignId, params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getCampaignProgress(campaignId, params);
        return response;
      } catch (err) {
        console.error(`Error fetching campaign progress report for campaign ${campaignId}:`, err);
        error.value = err.message || 'Failed to fetch campaign progress report';
        toast.error('Failed to fetch campaign progress report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get donor contribution report
    getDonorContribution: async (donorId, params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getDonorContribution(donorId, params);
        return response;
      } catch (err) {
        console.error(`Error fetching donor contribution report for donor ${donorId}:`, err);
        error.value = err.message || 'Failed to fetch donor contribution report';
        toast.error('Failed to fetch donor contribution report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get category analysis report
    getCategoryAnalysis: async (params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getCategoryAnalysis(params);
        return response;
      } catch (err) {
        console.error('Error fetching category analysis report:', err);
        error.value = err.message || 'Failed to fetch category analysis report';
        toast.error('Failed to fetch category analysis report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Save a report
    saveReport: async (reportData) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.saveReport(reportData);
        toast.success('Report saved successfully');
        return response;
      } catch (err) {
        console.error('Error saving report:', err);
        error.value = err.message || 'Failed to save report';
        toast.error('Failed to save report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get saved reports
    getSavedReports: async () => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getSavedReports();
        return response;
      } catch (err) {
        console.error('Error fetching saved reports:', err);
        error.value = err.message || 'Failed to fetch saved reports';
        toast.error('Failed to fetch saved reports');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Get a saved report by ID
    getSavedReportById: async (id) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.getSavedReportById(id);
        return response;
      } catch (err) {
        console.error(`Error fetching saved report ${id}:`, err);
        error.value = err.message || 'Failed to fetch saved report';
        toast.error('Failed to fetch saved report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Delete a saved report
    deleteSavedReport: async (id) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.deleteSavedReport(id);
        toast.success('Report deleted successfully');
        return response;
      } catch (err) {
        console.error(`Error deleting saved report ${id}:`, err);
        error.value = err.message || 'Failed to delete saved report';
        toast.error('Failed to delete saved report');
        return null;
      } finally {
        isLoading.value = false;
      }
    },
    
    // Export a report
    exportReport: async (format, params = {}) => {
      try {
        isLoading.value = true;
        error.value = null;
        
        const response = await donationApiService.reports.exportReport(format, params);
        return response;
      } catch (err) {
        console.error(`Error exporting report as ${format}:`, err);
        error.value = err.message || `Failed to export report as ${format}`;
        toast.error(`Failed to export report as ${format.toUpperCase()}`);
        return null;
      } finally {
        isLoading.value = false;
      }
    }
  };
  
  return {
    // State
    donations,
    pledges,
    campaigns,
    pledgeCampaigns,
    donationCategories,
    paymentMethods,
    isLoading,
    error,
    receiptSettings,
    exportSettings,
    
    // API Methods for Reports
    getDonors,
    getDonorContributions,
    getCampaignDetails,
    getPledges,
    campaigns,
    donationCategories,
    paymentMethods,
    receiptSettings,
    exportSettings,
    isLoading,
    error,
    
    // API methods
    fetchDonations,
    fetchPledges,
    fetchCampaigns,
    fetchPledgeCampaigns,
    fetchCategories,
    fetchPaymentMethods,
    fetchSettings,
    initializeData,
    
    // Getters
    totalDonations,
    totalByCategory,
    totalPledged,
    totalFulfilled,
    totalCampaignGoals,
    totalCampaignRaised,
    donationsByDate,
    activeCampaigns,
    getCategoryById,
    getPaymentMethodById,
    getCampaignById,
    
    // Actions
    addDonation,
    updateDonation,
    deleteDonation,
    addPledge,
    updatePledge,
    deletePledge,
    addCampaign,
    updateCampaign,
    deleteCampaign,
    addDonationCategory,
    updateDonationCategory,
    deleteDonationCategory,
    addPaymentMethod,
    updatePaymentMethod,
    deletePaymentMethod,
    generateReceiptNumber,
    getDonationStats,
    updateReceiptSettings,
    updateExportSettings,
    initializeSampleData
  };
});
