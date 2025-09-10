<template>
  <DashboardWidget 
    :title="title" 
    :loading="loading" 
    :refreshable="refreshable"
    @refresh="$emit('refresh')"
    type="stats"
  >
    <template #icon>
      <slot name="icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </slot>
    </template>
    
    <div class="stats-content">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-3xl font-bold text-neutral-900 dark:text-white transition-colors duration-300">
            {{ value }}
          </div>
          <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 transition-colors duration-300">
            {{ description }}
          </div>
        </div>
        
        <div v-if="showTrend && trend !== null" class="trend-indicator">
          <div class="flex items-center" :class="trendClass">
            <svg v-if="trend > 0" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <svg v-else-if="trend < 0" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
            </svg>
            <span>{{ Math.abs(trend) }}%</span>
          </div>
          <div class="text-xs mt-1">vs {{ comparisonPeriod }}</div>
        </div>
      </div>
      
      <div v-if="showProgress" class="mt-4">
        <div class="flex justify-between text-xs text-neutral-600 dark:text-neutral-400 mb-1 transition-colors duration-300">
          <span>{{ progressLabel }}</span>
          <span>{{ progressValue }}%</span>
        </div>
        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 transition-colors duration-300">
          <div 
            class="h-2 rounded-full transition-all duration-500 ease-out" 
            :class="progressColorClass"
            :style="{ width: `${progressValue}%` }"
          ></div>
        </div>
      </div>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { computed } from 'vue';
import DashboardWidget from './DashboardWidget.vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  },
  refreshable: {
    type: Boolean,
    default: true
  },
  trend: {
    type: Number,
    default: null
  },
  showTrend: {
    type: Boolean,
    default: false
  },
  comparisonPeriod: {
    type: String,
    default: 'last period'
  },
  showProgress: {
    type: Boolean,
    default: false
  },
  progressValue: {
    type: Number,
    default: 0
  },
  progressLabel: {
    type: String,
    default: 'Progress'
  },
  progressColor: {
    type: String,
    default: 'primary' // primary, success, warning, error
  }
});

defineEmits(['refresh']);

const trendClass = computed(() => {
  if (props.trend > 0) return 'text-green-500';
  if (props.trend < 0) return 'text-red-500';
  return 'text-neutral-500 dark:text-neutral-400';
});

const progressColorClass = computed(() => {
  const colors = {
    primary: 'bg-primary-500',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500'
  };
  
  return colors[props.progressColor] || colors.primary;
});
</script>
