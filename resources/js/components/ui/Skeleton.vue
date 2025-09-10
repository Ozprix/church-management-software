<template>
  <div 
    :class="[
      'animate-pulse bg-neutral-200 dark:bg-neutral-700',
      radiusClass,
      className
    ]"
    :style="{ 
      width: width, 
      height: height 
    }"
  ></div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Skeleton',
  
  props: {
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '1rem'
    },
    radius: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    className: {
      type: String,
      default: ''
    }
  },
  
  setup(props) {
    const radiusClass = computed(() => {
      switch (props.radius) {
        case 'none':
          return 'rounded-none';
        case 'sm':
          return 'rounded-sm';
        case 'md':
          return 'rounded-md';
        case 'lg':
          return 'rounded-lg';
        case 'xl':
          return 'rounded-xl';
        case 'full':
          return 'rounded-full';
        default:
          return 'rounded-md';
      }
    });

    return {
      radiusClass
    };
  }
};
</script>
