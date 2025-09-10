<template>
  <div 
    :class="[
      'input-wrapper',
      { 'input-disabled': disabled },
      { 'input-readonly': readonly },
      { 'input-with-prefix': $slots.prefix || prefixIcon },
      { 'input-with-suffix': $slots.suffix || suffixIcon },
      { 'input-error': error }
    ]"
  >
    <!-- Prefix -->
    <div v-if="$slots.prefix || prefixIcon" class="input-prefix">
      <slot name="prefix">
        <i v-if="prefixIcon" class="input-icon">
          <component :is="prefixIcon" />
        </i>
      </slot>
    </div>
    
    <!-- Input element -->
    <input
      :id="id"
      ref="inputRef"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      :maxlength="maxlength"
      :min="min"
      :max="max"
      :step="step"
      :pattern="pattern"
      :autocomplete="autocomplete"
      :name="name"
      :class="[
        'input-element',
        `input-size-${size}`,
        { 'input-rounded': rounded }
      ]"
      @input="handleInput"
      @change="handleChange"
      @focus="handleFocus"
      @blur="handleBlur"
      @keydown="handleKeydown"
      @keyup="handleKeyup"
      @keypress="handleKeypress"
    />
    
    <!-- Clear button -->
    <button
      v-if="clearable && modelValue && !disabled && !readonly"
      type="button"
      class="input-clear-button"
      @click="clear"
      aria-label="Clear input"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Suffix -->
    <div v-if="$slots.suffix || suffixIcon" class="input-suffix">
      <slot name="suffix">
        <i v-if="suffixIcon" class="input-icon">
          <component :is="suffixIcon" />
        </i>
      </slot>
    </div>
    
    <!-- Character counter -->
    <div v-if="showCharacterCount && maxlength" class="input-character-count">
      {{ modelValue ? modelValue.length : 0 }}/{{ maxlength }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'password', 'email', 'number', 'tel', 'url', 
      'search', 'date', 'time', 'datetime-local', 'month', 'week'
    ].includes(value)
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  rounded: {
    type: Boolean,
    default: false
  },
  prefixIcon: {
    type: [String, Object],
    default: null
  },
  suffixIcon: {
    type: [String, Object],
    default: null
  },
  maxlength: {
    type: [String, Number],
    default: null
  },
  showCharacterCount: {
    type: Boolean,
    default: false
  },
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  pattern: {
    type: String,
    default: null
  },
  autocomplete: {
    type: String,
    default: 'off'
  },
  name: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  },
  error: {
    type: Boolean,
    default: false
  },
  autofocus: {
    type: Boolean,
    default: false
  },
  trim: {
    type: Boolean,
    default: false
  },
  lazy: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'update:modelValue',
  'input',
  'change',
  'focus',
  'blur',
  'keydown',
  'keyup',
  'keypress',
  'clear'
]);

// Element ref
const inputRef = ref(null);

// Methods
const focus = () => {
  inputRef.value?.focus();
};

const blur = () => {
  inputRef.value?.blur();
};

const select = () => {
  inputRef.value?.select();
};

const clear = () => {
  emit('update:modelValue', '');
  emit('clear');
  
  // Focus input after clearing
  nextTick(() => {
    focus();
  });
};

const handleInput = (event) => {
  if (!props.lazy) {
    const value = event.target.value;
    emit('update:modelValue', props.trim ? value.trim() : value);
  }
  
  emit('input', event);
};

const handleChange = (event) => {
  if (props.lazy) {
    const value = event.target.value;
    emit('update:modelValue', props.trim ? value.trim() : value);
  }
  
  emit('change', event);
};

const handleFocus = (event) => {
  emit('focus', event);
};

const handleBlur = (event) => {
  if (props.trim && !props.lazy) {
    const value = event.target.value.trim();
    if (value !== props.modelValue) {
      emit('update:modelValue', value);
    }
  }
  
  emit('blur', event);
};

const handleKeydown = (event) => {
  emit('keydown', event);
};

const handleKeyup = (event) => {
  emit('keyup', event);
};

const handleKeypress = (event) => {
  emit('keypress', event);
};

// Auto focus
watch(() => props.autofocus, (value) => {
  if (value) {
    nextTick(() => {
      focus();
    });
  }
}, { immediate: true });

// Expose methods
defineExpose({
  focus,
  blur,
  select,
  clear,
  inputRef
});
</script>

<style scoped>
.input-wrapper {
  @apply relative flex items-center w-full;
}

.input-element {
  @apply w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-600 dark:focus:border-primary-600 transition-colors duration-200;
}

.input-size-sm {
  @apply px-2 py-1 text-sm rounded-md;
}

.input-size-md {
  @apply px-3 py-2 text-base rounded-md;
}

.input-size-lg {
  @apply px-4 py-3 text-lg rounded-md;
}

.input-rounded {
  @apply rounded-full;
}

.input-with-prefix .input-element {
  @apply pl-10;
}

.input-with-suffix .input-element {
  @apply pr-10;
}

.input-prefix, .input-suffix {
  @apply absolute inset-y-0 flex items-center text-neutral-500 dark:text-neutral-400;
}

.input-prefix {
  @apply left-0 pl-3;
}

.input-suffix {
  @apply right-0 pr-3;
}

.input-icon {
  @apply w-5 h-5;
}

.input-clear-button {
  @apply absolute right-3 p-1 rounded-full text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300 transition-colors duration-200;
}

.input-with-suffix .input-clear-button {
  @apply right-10;
}

.input-character-count {
  @apply absolute right-3 -bottom-5 text-xs text-neutral-500 dark:text-neutral-400;
}

.input-disabled {
  @apply opacity-60 cursor-not-allowed;
}

.input-disabled .input-element {
  @apply bg-neutral-100 dark:bg-neutral-900 cursor-not-allowed;
}

.input-readonly .input-element {
  @apply bg-neutral-50 dark:bg-neutral-900;
}

.input-error .input-element {
  @apply border-red-500 dark:border-red-700 focus:ring-red-500 focus:border-red-500 dark:focus:ring-red-700 dark:focus:border-red-700;
}
</style>
