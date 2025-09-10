<template>
  <div class="form-validator">
    <slot
      :validate="validate"
      :reset="reset"
      :errors="errors"
      :isValid="isValid"
      :isDirty="isDirty"
      :validateField="validateField"
      :resetField="resetField"
      :hasFieldError="hasFieldError"
      :getFieldError="getFieldError"
    ></slot>
    
    <!-- Validation Summary (optional) -->
    <div v-if="showSummary && errors.length > 0" class="validation-summary mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
      <h3 class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">Please fix the following errors:</h3>
      <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-1">
        <li v-for="(error, index) in errors" :key="index">{{ error.message }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  rules: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Object,
    required: true
  },
  validateOnMount: {
    type: Boolean,
    default: false
  },
  validateOnChange: {
    type: Boolean,
    default: true
  },
  showSummary: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'validation', 'submit', 'reset']);

// State
const errors = ref([]);
const touched = ref({});
const dirty = ref({});
const validated = ref(false);

// Computed properties
const isValid = computed(() => errors.value.length === 0);
const isDirty = computed(() => Object.keys(dirty.value).length > 0);

// Watch for model changes
watch(() => props.modelValue, (newValue) => {
  if (props.validateOnChange) {
    validate();
  }
}, { deep: true });

// Methods
const validate = () => {
  errors.value = [];
  validated.value = true;
  
  // Loop through each field in the rules
  Object.entries(props.rules).forEach(([field, fieldRules]) => {
    // Get the field value
    const value = props.modelValue[field];
    
    // Apply each rule for the field
    fieldRules.forEach(rule => {
      // Skip validation if the field is not required and the value is empty
      if (!rule.required && (value === undefined || value === null || value === '')) {
        return;
      }
      
      // Validate using the rule's validator function
      if (rule.validator && typeof rule.validator === 'function') {
        const isValid = rule.validator(value, props.modelValue);
        
        if (!isValid) {
          errors.value.push({
            field,
            message: rule.message || `${field} is invalid`
          });
        }
      }
      
      // Validate required fields
      if (rule.required && (value === undefined || value === null || value === '')) {
        errors.value.push({
          field,
          message: rule.message || `${field} is required`
        });
      }
      
      // Validate min length
      if (rule.minLength && value && value.length < rule.minLength) {
        errors.value.push({
          field,
          message: rule.message || `${field} must be at least ${rule.minLength} characters`
        });
      }
      
      // Validate max length
      if (rule.maxLength && value && value.length > rule.maxLength) {
        errors.value.push({
          field,
          message: rule.message || `${field} must be no more than ${rule.maxLength} characters`
        });
      }
      
      // Validate pattern
      if (rule.pattern && value && !rule.pattern.test(value)) {
        errors.value.push({
          field,
          message: rule.message || `${field} format is invalid`
        });
      }
      
      // Validate min value
      if (rule.min !== undefined && value !== undefined && value !== null && value < rule.min) {
        errors.value.push({
          field,
          message: rule.message || `${field} must be at least ${rule.min}`
        });
      }
      
      // Validate max value
      if (rule.max !== undefined && value !== undefined && value !== null && value > rule.max) {
        errors.value.push({
          field,
          message: rule.message || `${field} must be no more than ${rule.max}`
        });
      }
    });
  });
  
  // Emit validation event
  emit('validation', {
    isValid: isValid.value,
    errors: errors.value
  });
  
  return isValid.value;
};

const reset = () => {
  errors.value = [];
  touched.value = {};
  dirty.value = {};
  validated.value = false;
  
  emit('reset');
};

const validateField = (field) => {
  // Mark the field as touched
  touched.value[field] = true;
  
  // Get the field rules
  const fieldRules = props.rules[field];
  
  if (!fieldRules) {
    return true;
  }
  
  // Remove existing errors for this field
  errors.value = errors.value.filter(error => error.field !== field);
  
  // Get the field value
  const value = props.modelValue[field];
  
  // Apply each rule for the field
  fieldRules.forEach(rule => {
    // Skip validation if the field is not required and the value is empty
    if (!rule.required && (value === undefined || value === null || value === '')) {
      return;
    }
    
    // Validate using the rule's validator function
    if (rule.validator && typeof rule.validator === 'function') {
      const isValid = rule.validator(value, props.modelValue);
      
      if (!isValid) {
        errors.value.push({
          field,
          message: rule.message || `${field} is invalid`
        });
      }
    }
    
    // Validate required fields
    if (rule.required && (value === undefined || value === null || value === '')) {
      errors.value.push({
        field,
        message: rule.message || `${field} is required`
      });
    }
    
    // Validate min length
    if (rule.minLength && value && value.length < rule.minLength) {
      errors.value.push({
        field,
        message: rule.message || `${field} must be at least ${rule.minLength} characters`
      });
    }
    
    // Validate max length
    if (rule.maxLength && value && value.length > rule.maxLength) {
      errors.value.push({
        field,
        message: rule.message || `${field} must be no more than ${rule.maxLength} characters`
      });
    }
    
    // Validate pattern
    if (rule.pattern && value && !rule.pattern.test(value)) {
      errors.value.push({
        field,
        message: rule.message || `${field} format is invalid`
      });
    }
    
    // Validate min value
    if (rule.min !== undefined && value !== undefined && value !== null && value < rule.min) {
      errors.value.push({
        field,
        message: rule.message || `${field} must be at least ${rule.min}`
      });
    }
    
    // Validate max value
    if (rule.max !== undefined && value !== undefined && value !== null && value > rule.max) {
      errors.value.push({
        field,
        message: rule.message || `${field} must be no more than ${rule.max}`
      });
    }
  });
  
  // Emit validation event
  emit('validation', {
    isValid: isValid.value,
    errors: errors.value
  });
  
  return !hasFieldError(field);
};

const resetField = (field) => {
  // Remove errors for this field
  errors.value = errors.value.filter(error => error.field !== field);
  
  // Reset touched and dirty state for this field
  delete touched.value[field];
  delete dirty.value[field];
};

const hasFieldError = (field) => {
  return errors.value.some(error => error.field === field);
};

const getFieldError = (field) => {
  const fieldError = errors.value.find(error => error.field === field);
  return fieldError ? fieldError.message : '';
};

const markFieldAsDirty = (field) => {
  dirty.value[field] = true;
};

// Lifecycle hooks
onMounted(() => {
  if (props.validateOnMount) {
    validate();
  }
});

// Expose component methods
defineExpose({
  validate,
  reset,
  validateField,
  resetField,
  hasFieldError,
  getFieldError,
  markFieldAsDirty,
  isValid,
  isDirty
});
</script>

<style scoped>
/* Add any additional styling here */
</style>
