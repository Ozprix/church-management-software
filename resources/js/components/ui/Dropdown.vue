<template>
  <div class="relative inline-block text-left" ref="dropdownRef">
    <!-- Trigger button -->
    <div @click="toggle" v-if="$slots.trigger">
      <slot name="trigger" :open="isOpen"></slot>
    </div>
    <button 
      v-else
      @click="toggle"
      type="button"
      :class="[
        'inline-flex justify-between items-center w-full rounded-md shadow-sm px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500',
        buttonVariantClasses
      ]"
      :id="buttonId"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      {{ label }}
      <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>
    
    <!-- Dropdown menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div 
        v-show="isOpen"
        :class="[
          'absolute z-10 mt-2 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 focus:outline-none',
          widthClass,
          positionClasses
        ]"
        :aria-labelledby="buttonId"
        role="menu"
        aria-orientation="vertical"
        tabindex="-1"
        ref="menuRef"
      >
        <div class="py-1" role="none">
          <slot></slot>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
  label: {
    type: String,
    default: 'Options'
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'secondary', 'outline'].includes(value)
  },
  position: {
    type: String,
    default: 'bottom-right',
    validator: (value) => [
      'top-left', 'top-right', 'bottom-left', 'bottom-right'
    ].includes(value)
  },
  width: {
    type: String,
    default: 'auto',
    validator: (value) => ['auto', 'full', 'sm', 'md', 'lg'].includes(value)
  },
  closeOnClick: {
    type: Boolean,
    default: true
  },
  closeOnClickOutside: {
    type: Boolean,
    default: true
  },
  modelValue: {
    type: Boolean,
    default: undefined
  }
});

const emit = defineEmits(['update:modelValue', 'open', 'close']);

// State
const isOpen = ref(props.modelValue !== undefined ? props.modelValue : false);
const dropdownRef = ref(null);
const menuRef = ref(null);
const buttonId = ref(`dropdown-button-${uuidv4()}`);

// Computed properties
const buttonVariantClasses = computed(() => {
  const variants = {
    default: 'bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700',
    primary: 'bg-primary-600 text-white hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-800',
    secondary: 'bg-secondary-600 text-white hover:bg-secondary-700 dark:bg-secondary-700 dark:hover:bg-secondary-800',
    outline: 'bg-transparent text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20'
  };
  
  return variants[props.variant] || variants.default;
});

const positionClasses = computed(() => {
  const positions = {
    'top-left': 'origin-bottom-left bottom-full left-0 mb-2',
    'top-right': 'origin-bottom-right bottom-full right-0 mb-2',
    'bottom-left': 'origin-top-left top-full left-0',
    'bottom-right': 'origin-top-right top-full right-0'
  };
  
  return positions[props.position] || positions['bottom-right'];
});

const widthClass = computed(() => {
  const widths = {
    auto: 'w-auto min-w-[10rem]',
    full: 'w-full',
    sm: 'w-48',
    md: 'w-56',
    lg: 'w-64'
  };
  
  return widths[props.width] || widths.auto;
});

// Methods
const toggle = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    emit('open');
  } else {
    emit('close');
  }
  
  if (props.modelValue !== undefined) {
    emit('update:modelValue', isOpen.value);
  }
};

const close = () => {
  if (isOpen.value) {
    isOpen.value = false;
    emit('close');
    
    if (props.modelValue !== undefined) {
      emit('update:modelValue', false);
    }
  }
};

const handleClickOutside = (event) => {
  if (props.closeOnClickOutside && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    close();
  }
};

const handleMenuItemClick = (event) => {
  if (props.closeOnClick && event.target.closest('[role="menuitem"]')) {
    close();
  }
};

// Watch for modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined && newValue !== isOpen.value) {
    isOpen.value = newValue;
  }
});

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  if (menuRef.value) {
    menuRef.value.addEventListener('click', handleMenuItemClick);
  }
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  if (menuRef.value) {
    menuRef.value.removeEventListener('click', handleMenuItemClick);
  }
});

// Expose methods
defineExpose({
  isOpen,
  toggle,
  close
});
</script>
