<template>
  <div class="responsive-container" :class="containerClasses">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  fluid: {
    type: Boolean,
    default: false
  },
  maxWidth: {
    type: String,
    default: null,
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', null].includes(value)
  },
  padding: {
    type: [Boolean, String],
    default: true
  }
});

// Computed classes for container
const containerClasses = computed(() => {
  const classes = [];
  
  // Container type
  if (props.fluid) {
    classes.push('w-full');
  } else {
    classes.push('mx-auto');
    
    // Max width
    if (props.maxWidth) {
      switch (props.maxWidth) {
        case 'sm':
          classes.push('max-w-screen-sm'); // 640px
          break;
        case 'md':
          classes.push('max-w-screen-md'); // 768px
          break;
        case 'lg':
          classes.push('max-w-screen-lg'); // 1024px
          break;
        case 'xl':
          classes.push('max-w-screen-xl'); // 1280px
          break;
        case '2xl':
          classes.push('max-w-screen-2xl'); // 1536px
          break;
      }
    } else {
      classes.push('max-w-7xl'); // Default max width (1280px)
    }
  }
  
  // Padding
  if (props.padding === true) {
    classes.push('px-4 sm:px-6 lg:px-8');
  } else if (typeof props.padding === 'string') {
    classes.push(props.padding);
  }
  
  return classes;
});
</script>

<style scoped>
/* Any additional styling */
</style>
