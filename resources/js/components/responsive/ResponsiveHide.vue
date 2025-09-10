<template>
  <div v-if="!isHidden">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useResponsive } from '../../services/responsiveService';

const props = defineProps({
  // Hide only on specific breakpoints
  only: {
    type: [String, Array],
    default: null
  },
  // Hide on breakpoints greater than or equal to this
  from: {
    type: String,
    default: null
  },
  // Hide on breakpoints less than or equal to this
  to: {
    type: String,
    default: null
  },
  // Show on these breakpoints (opposite of only)
  not: {
    type: [String, Array],
    default: null
  },
  // Hide on mobile devices
  mobile: {
    type: Boolean,
    default: null
  },
  // Hide on desktop devices
  desktop: {
    type: Boolean,
    default: null
  },
  // Hide on tablet devices
  tablet: {
    type: Boolean,
    default: null
  },
  // Hide in landscape orientation
  landscape: {
    type: Boolean,
    default: null
  },
  // Hide in portrait orientation
  portrait: {
    type: Boolean,
    default: null
  }
});

// Get responsive utilities
const responsive = useResponsive();

// Breakpoint order for comparison
const breakpointOrder = ['xs', 'sm', 'md', 'lg', 'xl', '2xl'];

// Determine if content should be hidden
const isHidden = computed(() => {
  // Check specific device types
  if (props.mobile !== null) {
    if (props.mobile && responsive.isMobile.value) return true;
    if (!props.mobile && !responsive.isMobile.value) return true;
  }
  
  if (props.desktop !== null) {
    if (props.desktop && responsive.isDesktop.value) return true;
    if (!props.desktop && !responsive.isDesktop.value) return true;
  }
  
  if (props.tablet !== null) {
    if (props.tablet && responsive.isTablet.value) return true;
    if (!props.tablet && !responsive.isTablet.value) return true;
  }
  
  // Check orientation
  if (props.landscape !== null) {
    if (props.landscape && responsive.isLandscape.value) return true;
    if (!props.landscape && !responsive.isLandscape.value) return true;
  }
  
  if (props.portrait !== null) {
    if (props.portrait && responsive.isPortrait.value) return true;
    if (!props.portrait && !responsive.isPortrait.value) return true;
  }
  
  // Check breakpoint conditions
  if (props.only) {
    if (Array.isArray(props.only)) {
      if (props.only.includes(responsive.currentBreakpoint.value)) {
        return true;
      }
    } else if (props.only === responsive.currentBreakpoint.value) {
      return true;
    }
  }
  
  if (props.not) {
    if (Array.isArray(props.not)) {
      if (!props.not.includes(responsive.currentBreakpoint.value)) {
        return true;
      }
    } else if (props.not !== responsive.currentBreakpoint.value) {
      return true;
    }
  }
  
  if (props.from && props.to) {
    const fromIndex = breakpointOrder.indexOf(props.from);
    const toIndex = breakpointOrder.indexOf(props.to);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex >= fromIndex && currentIndex <= toIndex) {
      return true;
    }
  } else if (props.from) {
    const fromIndex = breakpointOrder.indexOf(props.from);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex >= fromIndex) {
      return true;
    }
  } else if (props.to) {
    const toIndex = breakpointOrder.indexOf(props.to);
    const currentIndex = breakpointOrder.indexOf(responsive.currentBreakpoint.value);
    
    if (currentIndex <= toIndex) {
      return true;
    }
  }
  
  // Default to not hidden if no conditions specified
  return false;
});
</script>
