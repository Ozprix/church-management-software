<template>
  <div class="responsive-grid" :class="gridClasses" :style="gridStyles">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  // Number of columns at different breakpoints
  cols: {
    type: [Number, Object],
    default: 1
  },
  // Gap between grid items
  gap: {
    type: [Number, String, Object],
    default: 4
  },
  // Row gap (if different from column gap)
  rowGap: {
    type: [Number, String, Object],
    default: null
  },
  // Alignment of grid items
  align: {
    type: String,
    default: 'stretch',
    validator: (value) => ['start', 'center', 'end', 'stretch'].includes(value)
  },
  // Justification of grid items
  justify: {
    type: String,
    default: 'start',
    validator: (value) => ['start', 'center', 'end', 'between', 'around', 'evenly'].includes(value)
  },
  // Auto-fit grid items to available space
  autoFit: {
    type: Boolean,
    default: false
  },
  // Minimum width of auto-fit grid items
  minWidth: {
    type: [Number, String],
    default: '250px'
  }
});

// Convert numeric gap to Tailwind class
const getGapClass = (gap) => {
  if (typeof gap === 'number') {
    return `gap-${gap}`;
  } else if (typeof gap === 'string' && !isNaN(parseInt(gap))) {
    return `gap-${parseInt(gap)}`;
  }
  return gap;
};

// Get responsive column classes
const getColsClass = (cols) => {
  if (typeof cols === 'number') {
    return `grid-cols-${cols}`;
  }
  
  // Handle responsive object
  const classes = [];
  
  if (cols.xs) classes.push(`grid-cols-${cols.xs}`);
  if (cols.sm) classes.push(`sm:grid-cols-${cols.sm}`);
  if (cols.md) classes.push(`md:grid-cols-${cols.md}`);
  if (cols.lg) classes.push(`lg:grid-cols-${cols.lg}`);
  if (cols.xl) classes.push(`xl:grid-cols-${cols.xl}`);
  if (cols['2xl']) classes.push(`2xl:grid-cols-${cols['2xl']}`);
  
  return classes.join(' ');
};

// Get responsive gap classes
const getResponsiveGapClass = (gap) => {
  if (typeof gap !== 'object' || gap === null) {
    return getGapClass(gap);
  }
  
  // Handle responsive object
  const classes = [];
  
  if (gap.xs) classes.push(getGapClass(gap.xs));
  if (gap.sm) classes.push(`sm:${getGapClass(gap.sm)}`);
  if (gap.md) classes.push(`md:${getGapClass(gap.md)}`);
  if (gap.lg) classes.push(`lg:${getGapClass(gap.lg)}`);
  if (gap.xl) classes.push(`xl:${getGapClass(gap.xl)}`);
  if (gap['2xl']) classes.push(`2xl:${getGapClass(gap['2xl'])}`);
  
  return classes.join(' ');
};

// Computed classes for grid
const gridClasses = computed(() => {
  const classes = ['grid'];
  
  // Auto-fit or fixed columns
  if (props.autoFit) {
    classes.push('grid-cols-auto-fit');
  } else {
    classes.push(getColsClass(props.cols));
  }
  
  // Gap
  if (props.gap) {
    classes.push(getResponsiveGapClass(props.gap));
  }
  
  // Row gap (if different from column gap)
  if (props.rowGap) {
    classes.push(getResponsiveGapClass(props.rowGap).replace('gap-', 'gap-y-'));
  }
  
  // Alignment
  if (props.align) {
    classes.push(`items-${props.align}`);
  }
  
  // Justification
  if (props.justify) {
    classes.push(`justify-${props.justify}`);
  }
  
  return classes;
});

// Computed styles for auto-fit grid
const gridStyles = computed(() => {
  if (!props.autoFit) return {};
  
  const minWidth = typeof props.minWidth === 'number' ? `${props.minWidth}px` : props.minWidth;
  
  return {
    gridTemplateColumns: `repeat(auto-fit, minmax(${minWidth}, 1fr))`
  };
});
</script>

<style scoped>
/* Any additional styling */
</style>
