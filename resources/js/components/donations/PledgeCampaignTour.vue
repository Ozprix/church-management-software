<template>
  <div class="pledge-campaign-tour">
    <FeatureTour
      ref="featureTour"
      tour-id="pledgeCampaign"
      :steps="tourSteps"
      :auto-start="autoStart"
      @complete="onTourComplete"
      @skip="onTourSkip"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useOnboardingStore } from '../../stores/onboarding';
import { useToast } from '../../composables/useToast';
import FeatureTour from '../onboarding/FeatureTour.vue';

// Props
const props = defineProps({
  autoStart: {
    type: Boolean,
    default: false
  }
});

// Services and stores
const router = useRouter();
const onboardingStore = useOnboardingStore();
const toast = useToast();

// Component refs
const featureTour = ref(null);

// Tour steps
const tourSteps = [
  {
    title: 'Welcome to Pledge Campaigns',
    description: 'Pledge campaigns help you track progress toward financial goals for building projects, missions, or other initiatives.',
    target: '.pledge-campaign-manager',
    position: 'bottom'
  },
  {
    title: 'Create New Campaigns',
    description: 'Click this button to create a new pledge campaign with a specific goal, start date, and end date.',
    target: '.pledge-campaign-manager .new-campaign-button',
    position: 'bottom'
  },
  {
    title: 'Campaign List',
    description: 'View all your active and completed campaigns here. Click on any campaign to see its details.',
    target: '.pledge-campaign-manager .campaign-list',
    position: 'right'
  },
  {
    title: 'Campaign Progress',
    description: 'Each campaign shows its progress toward the goal, including the total amount pledged and received.',
    target: '.pledge-campaign-manager .campaign-progress',
    position: 'left'
  },
  {
    title: 'Campaign Actions',
    description: 'Use these options to edit, delete, or manage pledges for each campaign.',
    target: '.pledge-campaign-manager .campaign-actions',
    position: 'left'
  },
  {
    title: 'Campaign Details',
    description: 'Click on a campaign to view detailed information, including all pledges and donations.',
    target: '.pledge-campaign-manager .campaign-item',
    position: 'bottom'
  },
  {
    title: 'Campaign Reports',
    description: 'Generate reports on pledge fulfillment and campaign progress to share with leadership.',
    target: '.pledge-campaign-manager .campaign-reports',
    position: 'top'
  }
];

// Methods
const startTour = () => {
  if (featureTour.value) {
    featureTour.value.startTour();
  }
};

const onTourComplete = () => {
  toast.success('Tour completed! You now know how to manage pledge campaigns.');
  onboardingStore.markFeatureTourCompleted('pledgeCampaign');
};

const onTourSkip = () => {
  toast.info('Tour skipped. You can restart it anytime from the Help menu.');
};

// Lifecycle hooks
onMounted(() => {
  // Check if elements exist before starting tour
  const checkElements = () => {
    const managerElement = document.querySelector('.pledge-campaign-manager');
    if (!managerElement) {
      // If we're not on the right page, navigate there first
      if (router.currentRoute.value.name !== 'donations.campaigns') {
        router.push({ name: 'donations.campaigns' });
        // Wait for navigation and then try again
        setTimeout(checkElements, 500);
        return;
      }
    }
    
    // Start tour if autoStart is true and tour hasn't been completed
    if (props.autoStart && !onboardingStore.featureTours.pledgeCampaign) {
      // Give the page a moment to fully render
      setTimeout(() => {
        startTour();
      }, 1000);
    }
  };
  
  checkElements();
});

// Expose methods to parent
defineExpose({
  startTour
});
</script>
