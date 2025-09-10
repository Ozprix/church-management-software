<template>
  <div class="tabs-container">
    <!-- Tab navigation -->
    <div :class="['tabs-header', `tabs-${variant}`]">
      <div class="tabs-nav-container" :class="{ 'overflow-x-auto': scrollable }">
        <div class="tabs-nav" :class="{ 'flex-nowrap': scrollable }">
          <button
            v-for="(tab, index) in tabs"
            :key="index"
            class="tab-button"
            :class="[
              getTabClasses(index),
              { 'cursor-not-allowed opacity-50': tab.disabled }
            ]"
            @click="!tab.disabled && selectTab(index)"
            :disabled="tab.disabled"
            role="tab"
            :aria-selected="activeTabIndex === index"
            :aria-controls="`tab-panel-${_uid}-${index}`"
            :id="`tab-${_uid}-${index}`"
          >
            <div class="flex items-center">
              <div v-if="tab.icon" class="tab-icon mr-2">
                <component :is="tab.icon" v-if="typeof tab.icon === 'object'" />
                <span v-else v-html="tab.icon"></span>
              </div>
              <span>{{ tab.title }}</span>
            </div>
            <div v-if="tab.badge" class="tab-badge ml-2">
              <slot :name="`badge-${index}`" v-if="$slots[`badge-${index}`]"></slot>
              <span v-else>{{ tab.badge }}</span>
            </div>
          </button>
        </div>
      </div>
      
      <!-- Extra content slot -->
      <div v-if="$slots.extra" class="tabs-extra">
        <slot name="extra"></slot>
      </div>
    </div>
    
    <!-- Tab content -->
    <div class="tabs-content">
      <div
        v-for="(tab, index) in tabs"
        :key="index"
        class="tab-panel"
        :class="{ 'hidden': activeTabIndex !== index, 'animate-fade-in': activeTabIndex === index && animate }"
        role="tabpanel"
        :aria-labelledby="`tab-${_uid}-${index}`"
        :id="`tab-panel-${_uid}-${index}`"
      >
        <slot :name="`tab-${index}`"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, provide } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    validator: (tabs) => {
      return tabs.every(tab => typeof tab === 'object' && 'title' in tab);
    }
  },
  modelValue: {
    type: Number,
    default: 0
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'pills', 'underline', 'bordered'].includes(value)
  },
  align: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'center', 'right', 'justified'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  animate: {
    type: Boolean,
    default: true
  },
  syncWithRoute: {
    type: Boolean,
    default: false
  },
  queryParam: {
    type: String,
    default: 'tab'
  }
});

const emit = defineEmits(['update:modelValue', 'tab-change']);

// Unique ID for accessibility
const _uid = ref(`tabs-${Math.floor(Math.random() * 10000)}`);

// Active tab state
const activeTabIndex = ref(props.modelValue || 0);

// Router integration
const route = useRoute();
const router = useRouter();

// Methods
const selectTab = (index) => {
  if (props.tabs[index].disabled) return;
  
  activeTabIndex.value = index;
  emit('update:modelValue', index);
  emit('tab-change', index, props.tabs[index]);
  
  // Update URL if syncWithRoute is enabled
  if (props.syncWithRoute) {
    const query = { ...route.query };
    query[props.queryParam] = index.toString();
    router.replace({ query });
  }
};

// Computed classes for tabs
const getTabClasses = (index) => {
  const isActive = activeTabIndex.value === index;
  const baseClasses = [
    `tab-${props.size}`,
    `tab-align-${props.align}`,
  ];
  
  // Add variant-specific classes
  switch (props.variant) {
    case 'pills':
      baseClasses.push(
        isActive 
          ? 'bg-primary-600 text-white dark:bg-primary-700' 
          : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800'
      );
      break;
    case 'underline':
      baseClasses.push(
        isActive 
          ? 'text-primary-600 border-b-2 border-primary-600 dark:text-primary-400 dark:border-primary-400' 
          : 'text-neutral-600 border-b-2 border-transparent hover:border-neutral-300 dark:text-neutral-300'
      );
      break;
    case 'bordered':
      baseClasses.push(
        isActive 
          ? 'text-primary-600 border-t-2 border-l border-r border-primary-600 dark:text-primary-400 dark:border-primary-400 bg-white dark:bg-neutral-800' 
          : 'text-neutral-600 border border-transparent hover:border-neutral-300 dark:text-neutral-300 bg-neutral-50 dark:bg-neutral-900'
      );
      break;
    default: // default variant
      baseClasses.push(
        isActive 
          ? 'text-primary-600 font-medium dark:text-primary-400' 
          : 'text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100'
      );
      break;
  }
  
  return baseClasses;
};

// Watch for prop changes
watch(() => props.modelValue, (newValue) => {
  if (newValue !== activeTabIndex.value && newValue >= 0 && newValue < props.tabs.length) {
    activeTabIndex.value = newValue;
  }
});

// Initialize from route if syncWithRoute is enabled
onMounted(() => {
  if (props.syncWithRoute && route.query[props.queryParam]) {
    const tabIndex = parseInt(route.query[props.queryParam], 10);
    if (!isNaN(tabIndex) && tabIndex >= 0 && tabIndex < props.tabs.length && !props.tabs[tabIndex].disabled) {
      activeTabIndex.value = tabIndex;
      emit('update:modelValue', tabIndex);
    }
  }
});

// Provide tab context to child components
provide('tabsContext', {
  activeTabIndex,
  selectTab
});

// Expose methods and state
defineExpose({
  activeTabIndex,
  selectTab
});
</script>

<style scoped>
.tabs-container {
  @apply w-full;
}

.tabs-header {
  @apply flex items-center border-b border-neutral-200 dark:border-neutral-700;
}

.tabs-header.tabs-pills {
  @apply border-b-0 mb-4;
}

.tabs-header.tabs-bordered {
  @apply border-b-0;
}

.tabs-nav-container {
  @apply flex-grow;
}

.tabs-nav {
  @apply flex;
}

.tabs-nav.flex-nowrap {
  min-width: max-content;
  flex-wrap: nowrap;
}

.tabs-extra {
  @apply ml-auto;
}

.tab-button {
  @apply flex items-center px-4 py-2 focus:outline-none transition-colors duration-200;
}

.tab-align-left {
  @apply text-left;
}

.tab-align-center {
  @apply text-center justify-center;
}

.tab-align-right {
  @apply text-right justify-end;
}

.tab-align-justified {
  @apply text-center justify-center flex-grow;
}

.tab-sm {
  @apply text-xs py-1 px-3;
}

.tab-md {
  @apply text-sm py-2 px-4;
}

.tab-lg {
  @apply text-base py-3 px-6;
}

.tab-badge {
  @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200;
}

.tab-icon {
  @apply w-4 h-4;
}

.tabs-content {
  @apply py-4;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
