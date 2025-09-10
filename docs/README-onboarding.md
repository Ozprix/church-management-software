# Church Management System - Onboarding & Help System

## Overview

This module provides a comprehensive onboarding and help system for the Church Management System, designed to improve user experience and reduce the learning curve for new users.

## Features

- **Guided Onboarding**: Step-by-step introduction for new users
- **Interactive Feature Tours**: Contextual guidance for specific features
- **Help Center**: Centralized repository of help resources
- **Contextual Help**: Context-aware assistance throughout the application
- **Personalized Settings**: User preferences for help and onboarding

## Components

### Core Components

- **FeatureTour.vue**: Reusable component for step-by-step feature tours
- **ContextualHelp.vue**: Floating help button with context-aware content
- **OnboardingGuide.vue**: Main onboarding experience for new users
- **OnboardingSettings.vue**: User preferences for onboarding and help
- **HelpCenter.vue**: Comprehensive help resources and support

### Feature-Specific Components

- **PledgeCampaignTour.vue**: Tour for pledge campaign management
- **PledgeFulfillmentReport.vue**: Enhanced with help integration
- **DonationInsights.vue**: Data visualization with guided tour

## State Management

The onboarding system uses Pinia for state management:

- **onboarding.js**: Central store for all onboarding and help features
  - Role-based onboarding steps
  - Feature tour completion tracking
  - User preferences
  - Tip visibility

## Getting Started

### Prerequisites

- Vue 3
- Pinia
- TailwindCSS

### Usage

1. **Add to a new feature**:

```vue
<template>
  <div>
    <!-- Your feature UI -->
    <FeatureTour
      ref="featureTour"
      tour-id="yourFeatureId"
      :steps="tourSteps"
      :auto-start="false"
      @complete="onTourComplete"
    />
    
    <button @click="startTour">Show Tour</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useOnboardingStore } from '../../stores/onboarding';
import FeatureTour from '../onboarding/FeatureTour.vue';

const onboardingStore = useOnboardingStore();
const featureTour = ref(null);

const tourSteps = [
  {
    title: 'Step Title',
    description: 'Step description',
    target: '.target-element',
    position: 'bottom'
  }
];

const startTour = () => {
  featureTour.value.startTour();
};

const onTourComplete = () => {
  onboardingStore.markFeatureTourCompleted('yourFeatureId');
};
</script>
```

2. **Add contextual help**:

```javascript
// In ContextualHelp.vue
const helpContentMap = {
  'your-route': [
    {
      title: 'Help Title',
      icon: 'fas fa-info-circle',
      content: 'Help content...'
    }
  ]
};
```

## Documentation

For detailed documentation, see:

- [Onboarding System Documentation](./onboarding-system.md)
- [Testing Documentation](../TESTING.md)

## Testing

The onboarding system includes comprehensive tests:

- Component tests for all onboarding components
- Store tests for the onboarding store
- Integration tests for feature-specific components

Run tests with:

```bash
npm test
```

## Contributing

When contributing to the onboarding system:

1. Follow the established patterns for components and state management
2. Write tests for all new components and features
3. Update documentation to reflect changes
4. Ensure all components are accessible and responsive
5. Test with different user roles to ensure appropriate content

## License

This project is licensed under the same license as the main Church Management System.
