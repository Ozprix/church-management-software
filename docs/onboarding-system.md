# Church Management System Onboarding & Help Documentation

This document provides comprehensive documentation for the onboarding and help system implemented in the Church Management System.

## Overview

The onboarding and help system is designed to provide a seamless user experience for new and existing users of the Church Management System. It includes:

1. **Guided Onboarding**: Step-by-step introduction to the system for new users
2. **Feature Tours**: Interactive guides for specific features
3. **Contextual Help**: Context-aware help throughout the application
4. **Help Center**: Centralized repository of help resources
5. **Personalized Settings**: User preferences for help and onboarding

## Architecture

The system follows a modular architecture with the following components:

### 1. Core Components

#### Onboarding Store (`resources/js/stores/onboarding.js`)

The central state management for all onboarding and help features. Uses Pinia for state management and persists state to localStorage.

Key features:
- Role-based onboarding steps
- Feature tour completion tracking
- User preferences management
- Tip visibility control

#### Feature Tour (`resources/js/components/onboarding/FeatureTour.vue`)

Reusable component for creating interactive step-by-step tours of specific features.

Key features:
- Highlighted target elements
- Customizable tooltips
- Progress indicators
- Responsive design
- Dark mode support

#### Contextual Help (`resources/js/components/onboarding/ContextualHelp.vue`)

Provides context-aware help throughout the application.

Key features:
- Floating help button
- Context-specific help content
- Quick actions
- Keyboard shortcuts
- Help resources

#### Onboarding Guide (`resources/js/components/onboarding/OnboardingGuide.vue`)

Main onboarding experience for new users.

Key features:
- Role-based steps
- Progress tracking
- Skip and resume functionality
- Personalized content

#### Onboarding Settings (`resources/js/components/onboarding/OnboardingSettings.vue`)

User preferences for onboarding and help.

Key features:
- Toggle onboarding visibility
- Feature tip controls
- Tour reset functionality
- Help visibility settings

### 2. Feature-Specific Components

#### Pledge Campaign Tour (`resources/js/components/donations/PledgeCampaignTour.vue`)

Specialized tour for the pledge campaign management feature.

Key features:
- Campaign creation guidance
- Management interface tour
- Progress tracking explanation
- Report generation guidance

#### Help Center (`resources/js/pages/HelpCenter.vue`)

Centralized repository of help resources.

Key features:
- Searchable content
- Getting started guides
- FAQs
- Video tutorials
- Support request form

## Data Flow

1. **User Authentication**:
   - On login, the `onboarding.js` store initializes
   - Checks if user is a first-time user
   - Loads appropriate onboarding state from localStorage

2. **Onboarding Flow**:
   - First-time users see the `OnboardingGuide.vue`
   - Guide steps are filtered based on user role
   - Completion status is tracked in the store

3. **Feature Discovery**:
   - Feature tours are triggered based on user navigation
   - Tour completion is tracked in the store
   - Completed tours can be reset via settings

4. **Help Access**:
   - Contextual help is available via the help button
   - Help content is context-aware based on current route
   - Help Center provides comprehensive resources

## Integration Points

### 1. Navigation Integration

The help and onboarding system is integrated into the main navigation via:

- Help icon in the navbar
- Onboarding settings in the user menu
- Mobile navigation help links

### 2. Router Integration

Routes are defined in `resources/js/router/index.js` for:

- Onboarding guide
- Onboarding settings
- Help center

### 3. Component Integration

Feature-specific components (like `PledgeFulfillmentReport.vue`) integrate with the help system via:

- Help buttons
- Feature tour triggers
- Contextual help integration

## Extending the System

### Adding a New Feature Tour

1. Create a new tour component:

```vue
<template>
  <FeatureTour
    ref="featureTour"
    tour-id="yourFeatureId"
    :steps="tourSteps"
    :auto-start="autoStart"
    @complete="onTourComplete"
    @skip="onTourSkip"
  />
</template>

<script setup>
import { ref } from 'vue';
import { useOnboardingStore } from '../../stores/onboarding';
import FeatureTour from '../onboarding/FeatureTour.vue';

const onboardingStore = useOnboardingStore();
const featureTour = ref(null);

// Define tour steps
const tourSteps = [
  {
    title: 'Step 1 Title',
    description: 'Step 1 description',
    target: '.target-element-selector',
    position: 'bottom'
  },
  // Add more steps as needed
];

// Methods
const onTourComplete = () => {
  onboardingStore.markFeatureTourCompleted('yourFeatureId');
};

// Expose methods
defineExpose({
  startTour: () => featureTour.value?.startTour()
});
</script>
```

2. Update the onboarding store:

```javascript
// Add your feature to the featureTours state
featureTours: ref({
  dashboard: false,
  members: false,
  // Add your feature
  yourFeatureId: false
})
```

3. Integrate the tour in your feature component:

```vue
<template>
  <div>
    <!-- Your feature UI -->
    <YourFeatureTour ref="featureTour" :auto-start="isFirstTime" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useOnboardingStore } from '../../stores/onboarding';
import YourFeatureTour from './YourFeatureTour.vue';

const onboardingStore = useOnboardingStore();
const featureTour = ref(null);
const isFirstTime = !onboardingStore.featureTours.yourFeatureId;

// Start tour button handler
const showTour = () => {
  featureTour.value?.startTour();
};
</script>
```

### Adding Help Center Content

1. Update the Help Center data:

```javascript
// Add to the appropriate section (guides, faqs, videos)
const guides = [
  // Existing guides
  {
    id: 5, // Use the next available ID
    title: 'Your Feature Guide',
    description: 'Learn how to use your feature',
    content: `
      <h2>Your Feature Guide</h2>
      <p>Detailed instructions for your feature...</p>
    `
  }
];
```

2. Add contextual help for your feature:

```javascript
// In ContextualHelp.vue
const helpContentMap = {
  // Existing routes
  'your-feature': [
    {
      title: 'Using Your Feature',
      icon: 'fas fa-star',
      content: 'Help content for your feature...',
      steps: [
        'Step 1 instruction',
        'Step 2 instruction'
      ]
    }
  ]
};
```

## Best Practices

1. **Consistency**: Maintain consistent UI and UX across all help and onboarding components
2. **Personalization**: Use role-based content to show relevant information
3. **Progressive Disclosure**: Show information progressively to avoid overwhelming users
4. **Persistence**: Save user preferences and progress to localStorage
5. **Accessibility**: Ensure all components are accessible to all users
6. **Performance**: Lazy-load help content to minimize impact on application performance
7. **Testing**: Thoroughly test all onboarding and help components

## Troubleshooting

### Common Issues

1. **Tour Not Appearing**
   - Check that the target elements exist in the DOM
   - Verify that the tour hasn't been marked as completed
   - Check browser console for errors

2. **Help Content Not Loading**
   - Verify route name matches the helpContentMap key
   - Check network requests for any API failures
   - Ensure content is properly formatted

3. **Preferences Not Saving**
   - Check localStorage permissions
   - Verify that the store is properly initialized
   - Check for errors in the store methods

## Future Enhancements

Potential improvements for the onboarding and help system:

1. **Analytics Integration**: Track onboarding completion and help usage
2. **AI-Powered Help**: Implement a chatbot for interactive help
3. **Video Tutorials**: Expand video content for visual learners
4. **Feedback System**: Allow users to rate help content
5. **Localization**: Translate help content to multiple languages
6. **Interactive Tutorials**: Add interactive exercises for learning
7. **Help Content API**: Move help content to a backend API for easier updates

## Conclusion

The onboarding and help system provides a comprehensive solution for guiding users through the Church Management System. By following this documentation, developers can maintain and extend the system to support new features and improve the user experience.
