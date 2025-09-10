<template>
  <div 
    :class="[
      'form-item', 
      { 'form-item-error': hasError },
      { 'form-item-required': required },
      { 'form-item-disabled': isDisabled }
    ]"
    ref="formItemRef"
  >
    <!-- Label -->
    <label 
      v-if="label" 
      :for="inputId" 
      :class="[
        'form-label',
        { 'sr-only': hideLabel },
        labelAlignClass
      ]"
      :style="labelStyle"
    >
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    
    <!-- Input wrapper -->
    <div class="form-input-wrapper">
      <!-- Input slot -->
      <slot :id="inputId" :disabled="isDisabled" :aria-describedby="helpTextId"></slot>
      
      <!-- Help text -->
      <div v-if="helpText" :id="helpTextId" class="form-help-text">
        {{ helpText }}
      </div>
      
      <!-- Error message -->
      <div v-if="errorMessage" class="form-error-message">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  hideLabel: {
    type: Boolean,
    default: false
  },
  helpText: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  rules: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'validate', 'focus', 'blur']);

// Element refs
const formItemRef = ref(null);
const inputId = ref(`form-item-${uuidv4()}`);
const helpTextId = ref(`help-text-${uuidv4()}`);

// Form context
const form = inject('form', null);

// Local state
const errorMessage = ref('');
const focused = ref(false);

// Computed properties
const isDisabled = computed(() => {
  return props.disabled || (form?.disabled.value ?? false);
});

const hasError = computed(() => {
  return !!errorMessage.value;
});

const labelAlignClass = computed(() => {
  if (!form) return '';
  
  const align = form.labelAlign.value;
  return {
    'text-left': align === 'left',
    'text-right': align === 'right',
    'text-center': align === 'center'
  };
});

const labelStyle = computed(() => {
  if (!form || form.labelPosition.value === 'top') return {};
  
  return {
    width: form.labelWidth.value,
    flexShrink: 0
  };
});

// Methods
const validate = async () => {
  errorMessage.value = '';
  
  if (!props.rules || props.rules.length === 0) {
    return { valid: true, errors: [] };
  }
  
  const errors = [];
  
  // Run validation rules
  for (const rule of props.rules) {
    if (typeof rule === 'function') {
      try {
        const result = await rule(props.modelValue, props.name);
        
        if (result !== true) {
          errors.push(result);
          errorMessage.value = result;
          break;
        }
      } catch (error) {
        console.error('Validation error:', error);
        errors.push(error.message || 'Validation error');
        errorMessage.value = error.message || 'Validation error';
        break;
      }
    }
  }
  
  const isValid = errors.length === 0;
  emit('validate', { valid: isValid, errors });
  
  return { valid: isValid, errors };
};

const reset = () => {
  errorMessage.value = '';
  emit('update:modelValue', null);
};

const handleFocus = () => {
  focused.value = true;
  emit('focus');
};

const handleBlur = () => {
  focused.value = false;
  emit('blur');
  
  // Validate on blur if enabled
  if (form?.validateOnBlur.value) {
    validate();
  }
};

// Watch for form errors
watch(() => form?.formErrors.value?.[props.name], (errors) => {
  if (errors && errors.length > 0) {
    errorMessage.value = errors[0];
  } else {
    errorMessage.value = '';
  }
});

// Watch for value changes
watch(() => props.modelValue, () => {
  // Validate on change if enabled
  if (form?.validateOnChange.value) {
    nextTick(() => validate());
  }
});

// Register with parent form
let unregister = null;

onMounted(() => {
  if (form?.registerFormItem) {
    unregister = form.registerFormItem({
      name: props.name,
      value: props.modelValue,
      validate,
      reset,
      element: formItemRef.value
    });
  }
});

onBeforeUnmount(() => {
  if (unregister) {
    unregister();
  }
});

// Expose methods and state
defineExpose({
  validate,
  reset,
  errorMessage,
  focused,
  formItemRef
});
</script>

<style scoped>
.form-item {
  @apply mb-4;
}

.form-label {
  @apply block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1;
}

.form-label-required {
  @apply text-red-500 ml-1;
}

.form-input-wrapper {
  @apply w-full;
}

.form-help-text {
  @apply mt-1 text-sm text-neutral-500 dark:text-neutral-400;
}

.form-error-message {
  @apply mt-1 text-sm text-red-600 dark:text-red-400;
}

.form-item-error :deep(input),
.form-item-error :deep(select),
.form-item-error :deep(textarea) {
  @apply border-red-500 dark:border-red-700 focus:ring-red-500 focus:border-red-500 dark:focus:ring-red-700 dark:focus:border-red-700;
}

.form-item-required .form-label::after {
  content: "*";
  @apply text-red-500 ml-1;
}

.form-item-disabled {
  @apply opacity-70;
}
</style>
