<template>
  <div class="donations-page">
    <div class="container mx-auto px-4 py-6">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Donations</h1>
        <p class="text-neutral-500 dark:text-neutral-400">Manage tithes, offerings, and other financial contributions</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <DonationStatCard 
          title="Total Donations" 
          :value="formatCurrency(donationStore.totalDonations)" 
          icon="cash" 
          color="primary"
        />
        <DonationStatCard 
          title="Total Pledged" 
          :value="formatCurrency(donationStore.totalPledged)" 
          icon="chart-bar" 
          color="success"
        />
        <DonationStatCard 
          title="Campaign Goals" 
          :value="formatCurrency(donationStore.totalCampaignGoals)" 
          icon="flag" 
          color="warning"
        />
        <DonationStatCard 
          title="Campaign Raised" 
          :value="formatCurrency(donationStore.totalCampaignRaised)" 
          icon="trending-up" 
          color="info"
        />
      </div>
      
      <!-- Tabs -->
      <div class="mb-6 border-b border-neutral-200 dark:border-neutral-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="activeTab = tab.id" 
            :class="[
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
      
      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Donations Tab -->
        <div v-if="activeTab === 'donations'">
          <DonationsList />
        </div>
        
        <!-- Pledges Tab -->
        <div v-else-if="activeTab === 'pledges'">
          <PledgesList />
        </div>
        
        <!-- Campaigns Tab -->
        <div v-else-if="activeTab === 'campaigns'">
          <CampaignsList />
        </div>
        
        <!-- Settings Tab -->
        <div v-else-if="activeTab === 'settings'">
          <DonationSettings />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useDonationStore } from '../stores/donations';
import { useRoute, useRouter } from 'vue-router';
import DonationStatCard from '../components/donations/DonationStatCard.vue';
import DonationsList from '../components/donations/DonationsList.vue';
import PledgesList from '../components/donations/PledgesList.vue';
import CampaignsList from '../components/donations/CampaignsList.vue';
import DonationSettings from '../components/donations/DonationSettings.vue';

// Store
const donationStore = useDonationStore();
const route = useRoute();
const router = useRouter();

// State
const activeTab = ref('donations');

// Tabs configuration
const tabs = [
  { id: 'donations', name: 'Donations' },
  { id: 'pledges', name: 'Pledges' },
  { id: 'campaigns', name: 'Campaigns' },
  { id: 'settings', name: 'Settings' }
];

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
};

// Watch for route query changes
onMounted(() => {
  // Set active tab from route query if present
  if (route.query.tab && tabs.some(tab => tab.id === route.query.tab)) {
    activeTab.value = route.query.tab;
  }
});

// Watch for active tab changes to update route
watch(activeTab, (newTab) => {
  router.replace({ query: { ...route.query, tab: newTab } });
});
</script>
