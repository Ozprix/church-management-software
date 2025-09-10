<template>
  <form @submit.prevent="handleSubmit" :class="formClasses">
    <slot></slot>
  </form>
</template>

<script setup>
import { provide, ref, computed } from 'vue';

const props = defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  inline: {
    type: Boolean,
    default: false
  },
  labelPosition: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'left', 'right'].includes(value)
  },
  labelWidth: {
    type: [String, Number],
    default: '120px'
  },
  labelAlign: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right', 'center'].includes(value)
  },
  validateOnSubmit: {
    type: Boolean,
    default: true
  },
  validateOnChange: {
    type: Boolean,
    default: false
  },
  validateOnBlur: {
    type: Boolean,
    default: true
  },
  scrollToError: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['submit', 'reset', 'validation-error']);

// Form state
const formItems = ref([]);
const formErrors = ref({});
const isSubmitting = ref(false);

// Computed properties
const formClasses = computed(() => {
  return {
    'form-inline': props.inline,
    'form-disabled': props.disabled || props.loading,
    [`form-label-${props.labelPosition}`]: true
  };
});

// Methods
const registerFormItem = (item) => {
  formItems.value.push(item);
  return () => {
    const index = formItems.value.indexOf(item);
    if (index !== -1) {
      formItems.value.splice(index, 1);
    }
  };
};

const validateForm = async () => {
  let isValid = true;
  formErrors.value = {};
  
  // Validate all form items
  for (const item of formItems.value) {
    if (item.validate) {
      const result = await item.validate();
      if (!result.valid) {
        isValid = false;
        formErrors.value[item.name] = result.errors;
        
        // Scroll to first error if enabled
        if (props.scrollToError && isValid) {
          setTimeout(() => {
            item.element?.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }, 100);
        }
      }
    }
  }
  
  return isValid;
};

const resetForm = () => {
  formErrors.value = {};
  
  // Reset all form items
  for (const item of formItems.value) {
    if (item.reset) {
      item.reset();
    }
  }
  
  emit('reset');
};

const handleSubmit = async (event) => {
  isSubmitting.value = true;
  
  try {
    // Validate form if enabled
    if (props.validateOnSubmit) {
      const isValid = await validateForm();
      if (!isValid) {
        emit('validation-error', formErrors.value);
        return;
      }
    }
    
    // Collect form data
    const formData = {};
    for (const item of formItems.value) {
      if (item.name && item.value !== undefined) {
        formData[item.name] = item.value;
      }
    }
    
    emit('submit', formData, event);
  } finally {
    isSubmitting.value = false;
  }
};

// Provide form context to child components
provide('form', {
  disabled: computed(() => props.disabled || props.loading),
  loading: computed(() => props.loading),
  labelPosition: computed(() => props.labelPosition),
  labelWidth: computed(() => props.labelWidth),
  labelAlign: computed(() => props.labelAlign),
  validateOnChange: computed(() => props.validateOnChange),
  validateOnBlur: computed(() => props.validateOnBlur),
  registerFormItem,
  validateForm,
  resetForm,
  formErrors
});

// Expose methods and state
defineExpose({
  validate: validateForm,
  reset: resetForm,
  formItems,
  formErrors,
  isSubmitting
});
</script>

<style scoped>
.form-inline {
  @apply flex flex-wrap items-center;
}

.form-inline > :deep(*) {
  @apply mr-4 mb-0;
}

.form-disabled {
  @apply opacity-70 pointer-events-none;
}

.form-label-left :deep(.form-item) {
  @apply flex flex-row items-start;
}

.form-label-right :deep(.form-item) {
  @apply flex flex-row-reverse items-start;
}

.form-label-top :deep(.form-item) {
  @apply flex flex-col;
}
</style>
