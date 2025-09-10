<template>
  <div class="progress-tracker">
    <!-- Horizontal Progress Tracker -->
    <div v-if="orientation === 'horizontal'" class="flex items-center w-full">
      <template v-for="(step, index) in steps" :key="index">
        <!-- Step item -->
        <div 
          class="relative flex flex-col items-center flex-1"
          :class="{ 'cursor-pointer': clickable && isStepClickable(index) }"
          @click="clickable && isStepClickable(index) ? goToStep(index) : null"
        >
          <!-- Step circle -->
          <div 
            class="flex items-center justify-center w-8 h-8 rounded-full transition-colors duration-200"
            :class="[
              getStepCircleClasses(index),
              { 'hover:bg-opacity-80': clickable && isStepClickable(index) }
            ]"
          >
            <!-- Completed step icon -->
            <svg 
              v-if="isStepCompleted(index)" 
              class="w-5 h-5 text-white" 
              xmlns="http://www.w3.org/2000/svg" 
              viewBox="0 0 20 20" 
              fill="currentColor"
            >
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            
            <!-- Current step icon -->
            <span 
              v-else-if="currentStep === index" 
              class="w-2 h-2 bg-white rounded-full"
            ></span>
            
            <!-- Step number -->
            <span 
              v-else 
              class="text-sm font-medium"
              :class="{ 'text-white': isStepActive(index), 'text-neutral-500 dark:text-neutral-400': !isStepActive(index) }"
            >
              {{ index + 1 }}
            </span>
          </div>
          
          <!-- Step label -->
          <div 
            v-if="showLabels" 
            class="mt-2 text-xs text-center"
            :class="[
              { 'font-medium': currentStep === index },
              getStepLabelClasses(index)
            ]"
          >
            {{ step.label }}
          </div>
          
          <!-- Step description -->
          <div 
            v-if="showDescriptions && step.description" 
            class="mt-1 text-xs text-center max-w-xs"
            :class="getStepDescriptionClasses(index)"
          >
            {{ step.description }}
          </div>
        </div>
        
        <!-- Connector line between steps -->
        <div 
          v-if="index < steps.length - 1" 
          class="flex-auto border-t-2 transition-colors duration-200 my-4"
          :class="[
            { 
              'border-primary-500 dark:border-primary-400': isStepCompleted(index),
              'border-neutral-300 dark:border-neutral-600': !isStepCompleted(index)
            }
          ]"
        ></div>
      </template>
    </div>
    
    <!-- Vertical Progress Tracker -->
    <div v-else class="flex flex-col w-full">
      <template v-for="(step, index) in steps" :key="index">
        <!-- Step item -->
        <div 
          class="relative flex items-start mb-4"
          :class="{ 'cursor-pointer': clickable && isStepClickable(index) }"
          @click="clickable && isStepClickable(index) ? goToStep(index) : null"
        >
          <!-- Step circle and connector line -->
          <div class="flex flex-col items-center mr-4">
            <div 
              class="flex items-center justify-center w-8 h-8 rounded-full transition-colors duration-200"
              :class="[
                getStepCircleClasses(index),
                { 'hover:bg-opacity-80': clickable && isStepClickable(index) }
              ]"
            >
              <!-- Completed step icon -->
              <svg 
                v-if="isStepCompleted(index)" 
                class="w-5 h-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor"
              >
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              
              <!-- Current step icon -->
              <span 
                v-else-if="currentStep === index" 
                class="w-2 h-2 bg-white rounded-full"
              ></span>
              
              <!-- Step number -->
              <span 
                v-else 
                class="text-sm font-medium"
                :class="{ 'text-white': isStepActive(index), 'text-neutral-500 dark:text-neutral-400': !isStepActive(index) }"
              >
                {{ index + 1 }}
              </span>
            </div>
            
            <!-- Connector line to next step -->
            <div 
              v-if="index < steps.length - 1" 
              class="w-0.5 h-full transition-colors duration-200 my-1"
              :class="[
                { 
                  'bg-primary-500 dark:bg-primary-400': isStepCompleted(index),
                  'bg-neutral-300 dark:bg-neutral-600': !isStepCompleted(index)
                }
              ]"
            ></div>
          </div>
          
          <!-- Step content -->
          <div class="flex flex-col flex-1">
            <!-- Step label -->
            <div 
              class="text-sm"
              :class="[
                { 'font-medium': currentStep === index },
                getStepLabelClasses(index)
              ]"
            >
              {{ step.label }}
            </div>
            
            <!-- Step description -->
            <div 
              v-if="showDescriptions && step.description" 
              class="mt-1 text-xs max-w-md"
              :class="getStepDescriptionClasses(index)"
            >
              {{ step.description }}
            </div>
          </div>
        </div>
      </template>
    </div>
    
    <!-- Navigation buttons -->
    <div v-if="showNavigation" class="flex justify-between mt-6">
      <button 
        v-if="currentStep > 0 || allowBackOnFirst"
        @click="goToPreviousStep"
        class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        {{ backLabel }}
      </button>
      <div v-else></div>
      
      <button 
        v-if="currentStep < steps.length - 1 || allowNextOnLast"
        @click="goToNextStep"
        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        {{ nextLabel }}
      </button>
      <button 
        v-else-if="showFinishButton"
        @click="finish"
        class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
      >
        {{ finishLabel }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  steps: {
    type: Array,
    required: true,
    validator: (value) => {
      return value.every(step => typeof step === 'object' && 'label' in step);
    }
  },
  modelValue: {
    type: Number,
    default: 0
  },
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  showDescriptions: {
    type: Boolean,
    default: true
  },
  showNavigation: {
    type: Boolean,
    default: true
  },
  showFinishButton: {
    type: Boolean,
    default: true
  },
  backLabel: {
    type: String,
    default: 'Back'
  },
  nextLabel: {
    type: String,
    default: 'Next'
  },
  finishLabel: {
    type: String,
    default: 'Finish'
  },
  clickable: {
    type: Boolean,
    default: true
  },
  allowBackOnFirst: {
    type: Boolean,
    default: false
  },
  allowNextOnLast: {
    type: Boolean,
    default: false
  },
  allowSkip: {
    type: Boolean,
    default: false
  },
  linear: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:modelValue', 'step-change', 'finish']);

// Internal state
const currentStep = ref(props.modelValue);

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  currentStep.value = newValue;
});

// Methods
const isStepCompleted = (index) => {
  return index < currentStep.value;
};

const isStepActive = (index) => {
  return index <= currentStep.value;
};

const isStepClickable = (index) => {
  if (!props.clickable) return false;
  if (props.allowSkip) return true;
  if (props.linear) return index <= currentStep.value + 1 && index >= currentStep.value - 1;
  return index <= currentStep.value;
};

const getStepCircleClasses = (index) => {
  if (currentStep.value === index) {
    return 'bg-primary-600 dark:bg-primary-500';
  } else if (isStepCompleted(index)) {
    return 'bg-primary-600 dark:bg-primary-500';
  } else if (isStepActive(index)) {
    return 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200';
  } else {
    return 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400';
  }
};

const getStepLabelClasses = (index) => {
  if (currentStep.value === index) {
    return 'text-primary-600 dark:text-primary-400';
  } else if (isStepCompleted(index)) {
    return 'text-neutral-700 dark:text-neutral-300';
  } else {
    return 'text-neutral-500 dark:text-neutral-400';
  }
};

const getStepDescriptionClasses = (index) => {
  if (currentStep.value === index) {
    return 'text-neutral-600 dark:text-neutral-400';
  } else {
    return 'text-neutral-500 dark:text-neutral-500';
  }
};

const goToStep = (index) => {
  if (isStepClickable(index)) {
    const oldStep = currentStep.value;
    currentStep.value = index;
    emit('update:modelValue', index);
    emit('step-change', { from: oldStep, to: index });
  }
};

const goToNextStep = () => {
  if (currentStep.value < props.steps.length - 1 || props.allowNextOnLast) {
    const oldStep = currentStep.value;
    currentStep.value++;
    emit('update:modelValue', currentStep.value);
    emit('step-change', { from: oldStep, to: currentStep.value });
  }
};

const goToPreviousStep = () => {
  if (currentStep.value > 0 || props.allowBackOnFirst) {
    const oldStep = currentStep.value;
    currentStep.value--;
    emit('update:modelValue', currentStep.value);
    emit('step-change', { from: oldStep, to: currentStep.value });
  }
};

const finish = () => {
  emit('finish');
};

// Expose methods
defineExpose({
  goToStep,
  goToNextStep,
  goToPreviousStep,
  finish
});
</script>

<style scoped>
/* Add any additional styling here */
</style>
