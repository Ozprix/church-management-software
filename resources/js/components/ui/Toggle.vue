<template>
  <div class="toggle-component">
    <button
      type="button"
      :id="id"
      :class="[
        'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-neutral-800',
        modelValue ? 'bg-primary-600 dark:bg-primary-500' : 'bg-neutral-200 dark:bg-neutral-700'
      ]"
      role="switch"
      :aria-checked="modelValue.toString()"
      @click="toggle"
    >
      <span class="sr-only">{{ label }}</span>
      <span
        :class="[
          'pointer-events-none inline-block h-5 w-5 rounded-full bg-white dark:bg-neutral-200 shadow transform ring-0 transition ease-in-out duration-200',
          modelValue ? 'translate-x-5' : 'translate-x-0'
        ]"
      ></span>
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  id: {
    type: String,
    default: () => `toggle-${Math.random().toString(36).substring(2, 9)}`
  },
  label: {
    type: String,
    default: 'Toggle'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

function toggle() {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue);
  }
}
</script>

<style scoped>
.toggle-component {
  display: inline-flex;
  align-items: center;
}
</style>
