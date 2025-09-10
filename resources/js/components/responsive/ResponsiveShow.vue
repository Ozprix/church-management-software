<template>
  <div v-if="isVisible">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useResponsive } from '../../services/responsiveService';

const props = defineProps({
  // Show only on specific breakpoints
  only: {
    type: [String, Array],
    default: null
  },
  // Show on breakpoints greater than or equal to this
  from: {
    type: String,
    default: null
  },
  // Show on breakpoints less than or equal to this
  to: {
    type: String,
    default: null
  },
  // Hide on these breakpoints (opposite of only)
  not: {
    type: [String, Array],
    default: null
  },
  // Show on mobile devices
  mobile: {
    type: Boolean,
    default: null
  },
  // Show on desktop devices
  desktop: {
    type: Boolean,
    default: null
  },
  // Show on tablet devices
  tablet: {
    type: Boolean,
    default: null
  },
  // Show in landscape orientation
  landscape: {
    type: Boolean,
    default: null
  },
  // Show in portrait orientation
  portrait: {
    type: Boolean,
    default: null
  }
});

// Get responsive utilities
const responsive = useResponsive();

// Breakpoint order for comparison
const breakpointOrder = ['xs', 'sm', 'md', 'lg', 'xl', '2xl'];

// Determine if content should be visible
const isVisible = computed(() => {
  // Check specific device types
  if (props.mobile !== null) {
    if (props.mobile && !responsive.isMobile.value) return false;
    if (!props.mobile && responsive.isMobile.value) return false;
  }
  
  if (props.desktop !== null) {
    if (props.desktop && !responsive.isDesktop.value) return false;
    if (!props.desktop && responsive.isDesktop.value) return false;
  }
  
  if (props.tablet !== null) {
    if (props.tablet && !responsive.isTablet.value) return false;
    if (!props.tablet && responsive.isTablet.value) return false;
  }
  
  // Check orientation
  if (props.landscape !== null) {
    if (props.landscape && !responsive.isLandscape.value) return false;
    if (!props.landscape && responsive.isLandscape.value) return false;
  }
  
  if (props.portrait !== null) {
    if (props.portrait && !responsive.isPortrait.value) return false;
    if (!props.portrait && responsive.isPortrait.value) return false;
  }
  
  // Check breakpoint conditions
  if (props.only) {
    if (Array.isArray(props.only)) {
      if (!props.only.includes(responsive.currentBreakpoint.value)) {
        return false;
      }
    } else if (props.only !== responsive.currentBreakpoint.value) {
      return false;
    }
  }
  
  if (props.not) {
    if (Array.isArray(props.not)) {
      if (props.not.includes(responsive.currentBreakpoint.value)) {
        return false;
      }
    } else if (props.not === responsive.currentBreakpoint.value) {
      return false;
    }
  }
  
  if (props.from && props.to) {
    const fromIndex = breakpointOrder.indexOf(props.from);
    const toIndex = breakpointOrder.indexOf(props.to);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex < fromIndex || currentIndex > toIndex) {
      return false;
    }
  } else if (props.from) {
    const fromIndex = breakpointOrder.indexOf(props.from);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex < fromIndex) {
      return false;
    }
  } else if (props.to) {
    const toIndex = breakpointOrder.indexOf(props.to);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex > toIndex) {
      return false;
    }
  }
  
  // Default to visible if no conditions specified
  return true;
});
</script>
