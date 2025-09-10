<template>
  <div class="dashboard-widget h-full" :class="[`widget-${type}`, { 'is-loading': loading }]">
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm h-full transition-colors duration-300">
      <div class="p-4 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center transition-colors duration-300">
        <h3 class="font-medium text-neutral-800 dark:text-white flex items-center transition-colors duration-300">
          <span v-if="icon" class="mr-2 text-primary-500 dark:text-primary-400 transition-colors duration-300">
            <slot name="icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </slot>
          </span>
          {{ title }}
        </h3>
        <div class="widget-actions flex items-center">
          <button 
            v-if="refreshable" 
            @click="$emit('refresh')" 
            class="text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 p-1 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300"
            :class="{ 'animate-spin': loading }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
          <button 
            v-if="configurable" 
            @click="$emit('configure')" 
            class="text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 p-1 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700 ml-1 transition-colors duration-300"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>
          <slot name="actions"></slot>
        </div>
      </div>
      
      <div class="p-4 widget-content h-[calc(100%-60px)] overflow-auto">
        <div v-if="loading" class="flex justify-center items-center h-full">
          <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else>
          <slot></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'default'
  },
  icon: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  refreshable: {
    type: Boolean,
    default: true
  },
  configurable: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh', 'configure']);
</script>

<style scoped>
.dashboard-widget {
  transition: all 0.3s ease;
}

.is-loading .widget-content {
  opacity: 0.7;
}
</style>
