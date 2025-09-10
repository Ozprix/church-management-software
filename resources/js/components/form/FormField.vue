<template>
  <div class="form-field" :class="{ 'has-error': error, 'is-required': required }">
    <!-- Field Label -->
    <label 
      v-if="label" 
      :for="id" 
      class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
      
      <!-- Help Tooltip -->
      <span v-if="helpText" class="ml-1 inline-block">
        <HelpTooltip :content="helpText" />
      </span>
    </label>
    
    <!-- Field Input -->
    <div class="relative">
      <slot></slot>
      
      <!-- Validation Icon -->
      <div v-if="showValidationIcon" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg v-if="error" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <svg v-else-if="valid" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    
    <!-- Error Message -->
    <div v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    
    <!-- Helper Text -->
    <div v-else-if="helperText" class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
      {{ helperText }}
    </div>
    
    <!-- Character Counter -->
    <div v-if="showCharCount && maxLength" class="mt-1 text-xs text-right text-neutral-500 dark:text-neutral-400">
      {{ currentLength }} / {{ maxLength }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import HelpTooltip from '../help/HelpTooltip.vue';

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  valid: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  helperText: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  maxLength: {
    type: Number,
    default: null
  },
  showCharCount: {
    type: Boolean,
    default: false
  },
  showValidationIcon: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'change']);

// Computed properties
const currentLength = computed(() => {
  if (typeof props.modelValue === 'string') {
    return props.modelValue.length;
  } else if (Array.isArray(props.modelValue)) {
    return props.modelValue.length;
  }
  return 0;
});

// Watch for changes to maxLength
watch(() => props.modelValue, (newValue) => {
  if (props.maxLength && typeof newValue === 'string' && newValue.length > props.maxLength) {
    emit('update:modelValue', newValue.slice(0, props.maxLength));
  }
});
</script>

<style scoped>
.form-field {
  margin-bottom: 1rem;
}

.has-error :deep(input),
.has-error :deep(select),
.has-error :deep(textarea) {
  border-color: #ef4444 !important;
  padding-right: 2.5rem;
}

.has-error :deep(input:focus),
.has-error :deep(select:focus),
.has-error :deep(textarea:focus) {
  box-shadow: 0 0 0 1px #ef4444 !important;
}

.is-required :deep(input),
.is-required :deep(select),
.is-required :deep(textarea) {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Ccircle cx='4' cy='4' r='3' fill='%23ef4444'/%3E%3C/svg%3E");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 0.5rem;
}

.has-error.is-required :deep(input),
.has-error.is-required :deep(select),
.has-error.is-required :deep(textarea) {
  background-image: none;
}
</style>
