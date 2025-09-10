<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :class="[
      'inline-flex items-center justify-center rounded-md font-medium focus:outline-none transition-all duration-300',
      sizeClasses,
      variantClasses,
      { 
        'opacity-50 cursor-not-allowed': disabled,
        'animate-pulse-soft': loading
      },
      className
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <span v-if="loading" class="mr-2">
      <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <span v-if="icon && iconPosition === 'left'" class="mr-2">
      <slot name="icon"></slot>
    </span>
    <slot></slot>
    <span v-if="icon && iconPosition === 'right'" class="ml-2">
      <slot name="icon"></slot>
    </span>
  </component>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Button',
  
  props: {
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link', 'outline', 'ghost'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    tag: {
      type: String,
      default: 'button'
    },
    type: {
      type: String,
      default: 'button'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
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
    className: {
      type: String,
      default: ''
    }
  },
  
  setup(props) {
    const sizeClasses = computed(() => {
      switch (props.size) {
        case 'xs':
          return 'px-2 py-1 text-xs';
        case 'sm':
          return 'px-3 py-1.5 text-sm';
        case 'md':
          return 'px-4 py-2 text-sm';
        case 'lg':
          return 'px-5 py-2.5 text-base';
        case 'xl':
          return 'px-6 py-3 text-lg';
        default:
          return 'px-4 py-2 text-sm';
      }
    });

    const variantClasses = computed(() => {
      switch (props.variant) {
        case 'primary':
          return 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-800';
        case 'secondary':
          return 'bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 dark:bg-secondary-700 dark:hover:bg-secondary-800';
        case 'success':
          return 'bg-accent-teal-DEFAULT text-white hover:bg-accent-teal-dark focus:ring-2 focus:ring-accent-teal-DEFAULT focus:ring-offset-2';
        case 'danger':
          return 'bg-accent-coral-DEFAULT text-white hover:bg-accent-coral-dark focus:ring-2 focus:ring-accent-coral-DEFAULT focus:ring-offset-2';
        case 'warning':
          return 'bg-accent-gold-DEFAULT text-white hover:bg-accent-gold-dark focus:ring-2 focus:ring-accent-gold-DEFAULT focus:ring-offset-2';
        case 'info':
          return 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2';
        case 'light':
          return 'bg-neutral-200 text-neutral-800 hover:bg-neutral-300 focus:ring-2 focus:ring-neutral-200 focus:ring-offset-2 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600';
        case 'dark':
          return 'bg-neutral-800 text-white hover:bg-neutral-900 focus:ring-2 focus:ring-neutral-800 focus:ring-offset-2 dark:bg-neutral-900 dark:hover:bg-neutral-800';
        case 'link':
          return 'bg-transparent text-primary-600 hover:text-primary-700 hover:underline focus:ring-0 dark:text-primary-400 dark:hover:text-primary-300';
        case 'outline':
          return 'bg-transparent border border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:text-primary-400 dark:border-primary-400 dark:hover:bg-primary-900/30';
        case 'ghost':
          return 'bg-transparent text-neutral-700 hover:bg-neutral-100 focus:ring-2 focus:ring-neutral-200 focus:ring-offset-2 dark:text-neutral-300 dark:hover:bg-neutral-800';
        default:
          return 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2';
      }
    });

    return {
      sizeClasses,
      variantClasses
    };
  }
};
</script>
