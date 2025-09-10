/**
 * Responsive Service
 * 
 * A service for managing responsive behavior throughout the application.
 * Provides utilities for detecting screen sizes, orientation changes,
 * and responsive adaptations.
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';

// Breakpoint definitions (matching Tailwind CSS defaults)
const breakpoints = {
  xs: 0,
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  '2xl': 1536
};

/**
 * Create a composable function for responsive behavior
 * @returns {Object} - Responsive utilities
 */
export function useResponsive() {
  // Current window dimensions
  const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 0);
  const windowHeight = ref(typeof window !== 'undefined' ? window.innerHeight : 0);
  
  // Device orientation
  const orientation = ref('portrait');
  
  // Is mobile device detection
  const isMobileDevice = ref(false);
  
  // Is touch device detection
  const isTouchDevice = ref(false);
  
  // Current breakpoint
  const currentBreakpoint = computed(() => {
    if (windowWidth.value >= breakpoints['2xl']) return '2xl';
    if (windowWidth.value >= breakpoints.xl) return 'xl';
    if (windowWidth.value >= breakpoints.lg) return 'lg';
    if (windowWidth.value >= breakpoints.md) return 'md';
    if (windowWidth.value >= breakpoints.sm) return 'sm';
    return 'xs';
  });
  
  // Computed properties for each breakpoint
  const isXs = computed(() => currentBreakpoint.value === 'xs');
  const isSm = computed(() => currentBreakpoint.value === 'sm');
  const isMd = computed(() => currentBreakpoint.value === 'md');
  const isLg = computed(() => currentBreakpoint.value === 'lg');
  const isXl = computed(() => currentBreakpoint.value === 'xl');
  const is2Xl = computed(() => currentBreakpoint.value === '2xl');
  
  // Computed properties for breakpoint ranges
  const isSmAndUp = computed(() => windowWidth.value >= breakpoints.sm);
  const isMdAndUp = computed(() => windowWidth.value >= breakpoints.md);
  const isLgAndUp = computed(() => windowWidth.value >= breakpoints.lg);
  const isXlAndUp = computed(() => windowWidth.value >= breakpoints.xl);
  
  const isSmAndDown = computed(() => windowWidth.value < breakpoints.md);
  const isMdAndDown = computed(() => windowWidth.value < breakpoints.lg);
  const isLgAndDown = computed(() => windowWidth.value < breakpoints.xl);
  const isXlAndDown = computed(() => windowWidth.value < breakpoints['2xl']);
  
  // Computed property for mobile screens
  const isMobile = computed(() => windowWidth.value < breakpoints.md || isMobileDevice.value);
  
  // Computed property for desktop screens
  const isDesktop = computed(() => windowWidth.value >= breakpoints.lg && !isMobileDevice.value);
  
  // Computed property for tablet screens
  const isTablet = computed(() => 
    (windowWidth.value >= breakpoints.md && windowWidth.value < breakpoints.lg) ||
    (isMobileDevice.value && windowWidth.value >= breakpoints.md)
  );
  
  // Computed property for landscape orientation
  const isLandscape = computed(() => orientation.value === 'landscape');
  
  // Computed property for portrait orientation
  const isPortrait = computed(() => orientation.value === 'portrait');
  
  /**
   * Update dimensions and device detection
   */
  const updateDimensions = () => {
    if (typeof window === 'undefined') return;
    
    windowWidth.value = window.innerWidth;
    windowHeight.value = window.innerHeight;
    
    // Update orientation
    orientation.value = windowWidth.value > windowHeight.value ? 'landscape' : 'portrait';
    
    // Check if device is mobile
    if (typeof navigator !== 'undefined') {
      const userAgent = navigator.userAgent.toLowerCase();
      isMobileDevice.value = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);
      
      // Check if device supports touch
      isTouchDevice.value = 'ontouchstart' in window || 
        navigator.maxTouchPoints > 0 || 
        navigator.msMaxTouchPoints > 0;
    }
  };
  
  /**
   * Get the value for the current breakpoint from a responsive object
   * @param {Object} values - Object with breakpoint values
   * @param {*} defaultValue - Default value if no matching breakpoint
   * @returns {*} - Value for current breakpoint
   */
  const getResponsiveValue = (values, defaultValue = null) => {
    if (!values) return defaultValue;
    
    // If values is not an object, return it directly
    if (typeof values !== 'object') return values;
    
    // Check for exact breakpoint match
    if (values[currentBreakpoint.value] !== undefined) {
      return values[currentBreakpoint.value];
    }
    
    // Check for breakpoints in descending order
    const breakpointOrder = ['2xl', 'xl', 'lg', 'md', 'sm', 'xs'];
    const currentIndex = breakpointOrder.indexOf(currentBreakpoint.value);
    
    // Look for the closest larger breakpoint with a defined value
    for (let i = currentIndex - 1; i >= 0; i--) {
      const breakpoint = breakpointOrder[i];
      if (values[breakpoint] !== undefined) {
        return values[breakpoint];
      }
    }
    
    // Look for the closest smaller breakpoint with a defined value
    for (let i = currentIndex + 1; i < breakpointOrder.length; i++) {
      const breakpoint = breakpointOrder[i];
      if (values[breakpoint] !== undefined) {
        return values[breakpoint];
      }
    }
    
    // Return default value if no matching breakpoint found
    return defaultValue;
  };
  
  // Setup event listeners
  const setupListeners = () => {
    if (typeof window === 'undefined') return;
    
    // Initial update
    updateDimensions();
    
    // Add resize event listener
    window.addEventListener('resize', updateDimensions);
    
    // Add orientation change event listener
    window.addEventListener('orientationchange', updateDimensions);
  };
  
  // Cleanup event listeners
  const cleanupListeners = () => {
    if (typeof window === 'undefined') return;
    
    window.removeEventListener('resize', updateDimensions);
    window.removeEventListener('orientationchange', updateDimensions);
  };
  
  // Setup and cleanup in component lifecycle
  if (typeof onMounted === 'function') {
    onMounted(setupListeners);
  }
  
  if (typeof onUnmounted === 'function') {
    onUnmounted(cleanupListeners);
  }
  
  return {
    // Dimensions
    windowWidth,
    windowHeight,
    
    // Breakpoints
    breakpoints,
    currentBreakpoint,
    
    // Breakpoint checks
    isXs,
    isSm,
    isMd,
    isLg,
    isXl,
    is2Xl,
    
    // Breakpoint range checks
    isSmAndUp,
    isMdAndUp,
    isLgAndUp,
    isXlAndUp,
    isSmAndDown,
    isMdAndDown,
    isLgAndDown,
    isXlAndDown,
    
    // Device type checks
    isMobile,
    isTablet,
    isDesktop,
    isMobileDevice,
    isTouchDevice,
    
    // Orientation checks
    orientation,
    isLandscape,
    isPortrait,
    
    // Utilities
    getResponsiveValue,
    updateDimensions
  };
}

// Create a Vue plugin
export const ResponsivePlugin = {
  install(app) {
    const responsive = useResponsive();
    
    // Add to global properties
    app.config.globalProperties.$responsive = responsive;
    
    // Provide to components
    app.provide('responsive', responsive);
    
    // Add a directive for responsive visibility
    app.directive('responsive', {
      mounted(el, binding) {
        const updateVisibility = () => {
          const { value } = binding;
          let shouldShow = true;
          
          if (typeof value === 'string') {
            // Simple breakpoint check (e.g. v-responsive="'md'")
            shouldShow = responsive.currentBreakpoint.value === value;
          } else if (typeof value === 'object') {
            // Complex conditions (e.g. v-responsive="{ from: 'md', to: 'xl' }")
            if (value.from && value.to) {
              const fromIndex = Object.keys(breakpoints).indexOf(value.from);
              const toIndex = Object.keys(breakpoints).indexOf(value.to);
              const currentIndex = Object.keys(breakpoints).indexOf(responsive.currentBreakpoint.value);
              shouldShow = currentIndex >= fromIndex && currentIndex <= toIndex;
            } else if (value.from) {
              const fromIndex = Object.keys(breakpoints).indexOf(value.from);
              const currentIndex = Object.keys(breakpoints).indexOf(responsive.currentBreakpoint.value);
              shouldShow = currentIndex >= fromIndex;
            } else if (value.to) {
              const toIndex = Object.keys(breakpoints).indexOf(value.to);
              const currentIndex = Object.keys(breakpoints).indexOf(responsive.currentBreakpoint.value);
              shouldShow = currentIndex <= toIndex;
            } else if (value.only) {
              if (Array.isArray(value.only)) {
                shouldShow = value.only.includes(responsive.currentBreakpoint.value);
              } else {
                shouldShow = responsive.currentBreakpoint.value === value.only;
              }
            } else if (value.not) {
              if (Array.isArray(value.not)) {
                shouldShow = !value.not.includes(responsive.currentBreakpoint.value);
              } else {
                shouldShow = responsive.currentBreakpoint.value !== value.not;
              }
            }
          }
          
          // Update element visibility
          el.style.display = shouldShow ? '' : 'none';
        };
        
        // Initial update
        updateVisibility();
        
        // Store the update function for later
        el._updateResponsiveVisibility = updateVisibility;
        
        // Add resize event listener
        window.addEventListener('resize', el._updateResponsiveVisibility);
      },
      
      updated(el, binding) {
        // Update when directive value changes
        if (el._updateResponsiveVisibility) {
          el._updateResponsiveVisibility();
        }
      },
      
      unmounted(el) {
        // Clean up event listener
        if (el._updateResponsiveVisibility) {
          window.removeEventListener('resize', el._updateResponsiveVisibility);
          delete el._updateResponsiveVisibility;
        }
      }
    });
  }
};

// Export singleton instance for direct import
export default useResponsive;
