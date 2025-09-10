<template>
  <span 
    :class="[
      'inline-flex items-center justify-center font-medium',
      sizeClasses,
      variantClasses,
      radiusClasses,
      { 'animate-pulse-soft': pulse },
      className
    ]"
  >
    <span v-if="icon && iconPosition === 'left'" class="mr-1">
      <slot name="icon"></slot>
    </span>
    <slot></slot>
    <span v-if="icon && iconPosition === 'right'" class="ml-1">
      <slot name="icon"></slot>
    </span>
  </span>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Badge',
  
  props: {
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'outline'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    radius: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    icon: {
      type: Boolean,
      default: false
    },
    iconPosition: {
      type: String,
      default: 'left',
      validator: (value) => ['left', 'right'].includes(value)
    },
    pulse: {
      type: Boolean,
      default: false
    },
    className: {
      type: String,
      default: ''
    }
  },
  
  setup(props) {
    const sizeClasses = computed(() => {
      switch (props.size) {
        case 'xs':
          return 'px-1.5 py-0.5 text-xs';
        case 'sm':
          return 'px-2 py-0.5 text-xs';
        case 'md':
          return 'px-2.5 py-0.5 text-sm';
        case 'lg':
          return 'px-3 py-1 text-sm';
        default:
          return 'px-2.5 py-0.5 text-sm';
      }
    });

    const variantClasses = computed(() => {
      switch (props.variant) {
        case 'primary':
          return 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300';
        case 'secondary':
          return 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-300';
        case 'success':
          return 'bg-accent-teal-light text-accent-teal-dark dark:bg-accent-teal-dark/30 dark:text-accent-teal-light';
        case 'danger':
          return 'bg-accent-coral-light text-accent-coral-dark dark:bg-accent-coral-dark/30 dark:text-accent-coral-light';
        case 'warning':
          return 'bg-accent-gold-light text-accent-gold-dark dark:bg-accent-gold-dark/30 dark:text-accent-gold-light';
        case 'info':
          return 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300';
        case 'light':
          return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300';
        case 'dark':
          return 'bg-neutral-800 text-neutral-100 dark:bg-neutral-900 dark:text-neutral-100';
        case 'outline':
          return 'bg-transparent border border-current';
        default:
          return 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300';
      }
    });

    const radiusClasses = computed(() => {
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
      sizeClasses,
      variantClasses,
      radiusClasses
    };
  }
};
</script>
