<template>
  <div 
    :class="[
      'overflow-hidden transition-all duration-300',
      radiusClass,
      elevationClass,
      variantClass,
      { 'animate-bounce-in': animate },
      className
    ]"
  >
    <div v-if="$slots.header" :class="['px-6 py-4', headerClass]">
      <slot name="header"></slot>
    </div>
    <div :class="['px-6 py-4', bodyClass]">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" :class="['px-6 py-4', footerClass]">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Card',
  
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary', 'glass', 'glass-dark', 'outline'].includes(value)
    },
    elevation: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    radius: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    animate: {
      type: Boolean,
      default: false
    },
    className: {
      type: String,
      default: ''
    },
    headerClass: {
      type: String,
      default: ''
    },
    bodyClass: {
      type: String,
      default: ''
    },
    footerClass: {
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

    const elevationClass = computed(() => {
      switch (props.elevation) {
        case 'none':
          return 'shadow-none';
        case 'sm':
          return 'shadow-sm';
        case 'md':
          return 'shadow-md';
        case 'lg':
          return 'shadow-lg';
        case 'xl':
          return 'shadow-xl';
        default:
          return 'shadow-md';
      }
    });

    const variantClass = computed(() => {
      switch (props.variant) {
        case 'default':
          return 'bg-white dark:bg-neutral-800 dark:text-neutral-200';
        case 'primary':
          return 'bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800';
        case 'secondary':
          return 'bg-secondary-50 dark:bg-secondary-900/30 border border-secondary-200 dark:border-secondary-800';
        case 'glass':
          return 'glassmorphism';
        case 'glass-dark':
          return 'glassmorphism-dark';
        case 'outline':
          return 'bg-transparent border border-neutral-200 dark:border-neutral-700';
        default:
          return 'bg-white dark:bg-neutral-800';
      }
    });

    return {
      radiusClass,
      elevationClass,
      variantClass
    };
  }
};
</script>
