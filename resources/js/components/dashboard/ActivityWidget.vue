<template>
  <DashboardWidget 
    :title="title" 
    :loading="loading" 
    :refreshable="refreshable"
    @refresh="$emit('refresh')"
    type="activity"
  >
    <template #icon>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </template>
    
    <div class="activity-content">
      <div v-if="!activities || activities.length === 0" class="flex flex-col items-center justify-center py-8 text-neutral-500 dark:text-neutral-400 transition-colors duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p>No recent activity</p>
      </div>
      
      <div v-else>
        <div class="relative">
          <!-- Activity timeline -->
          <div class="absolute left-5 top-0 bottom-0 w-px bg-neutral-200 dark:bg-neutral-700 transition-colors duration-300"></div>
          
          <div class="space-y-4">
            <div 
              v-for="(activity, index) in displayedActivities" 
              :key="index"
              class="activity-item relative pl-10"
            >
              <!-- Activity dot -->
              <div 
                class="absolute left-0 top-1.5 w-10 flex justify-center"
              >
                <span 
                  class="w-3 h-3 rounded-full border-2 border-white dark:border-neutral-800 transition-colors duration-300"
                  :class="getActivityTypeClass(activity.type)"
                ></span>
              </div>
              
              <!-- Activity content -->
              <div class="pb-4">
                <div class="flex items-start justify-between">
                  <div>
                    <div class="text-sm font-medium text-neutral-900 dark:text-white transition-colors duration-300">
                      {{ activity.title }}
                    </div>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5 transition-colors duration-300" v-html="activity.description"></div>
                  </div>
                  <div class="text-xs text-neutral-500 dark:text-neutral-400 ml-2 whitespace-nowrap transition-colors duration-300">
                    {{ formatTimeAgo(activity.timestamp) }}
                  </div>
                </div>
                
                <!-- Activity details (optional) -->
                <div 
                  v-if="activity.details"
                  class="mt-2 p-2 text-sm bg-neutral-50 dark:bg-neutral-800 rounded border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 transition-colors duration-300"
                >
                  {{ activity.details }}
                </div>
                
                <!-- Activity actions (optional) -->
                <div 
                  v-if="activity.actions && activity.actions.length > 0"
                  class="mt-2 flex space-x-2"
                >
                  <button 
                    v-for="(action, actionIndex) in activity.actions" 
                    :key="actionIndex"
                    @click="$emit('activity-action', { activity, action: action.id })"
                    class="text-xs px-2 py-1 rounded border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 transition-colors duration-300"
                  >
                    {{ action.label }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="activities.length > limit" class="text-center pt-2">
          <button 
            @click="showAllActivities = !showAllActivities" 
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium transition-colors duration-300"
          >
            {{ showAllActivities ? 'Show Less' : `Show All (${activities.length})` }}
          </button>
        </div>
      </div>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { ref, computed } from 'vue';
import DashboardWidget from './DashboardWidget.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Recent Activity'
  },
  activities: {
    type: Array,
    default: () => []
  },
  limit: {
    type: Number,
    default: 5
  },
  loading: {
    type: Boolean,
    default: false
  },
  refreshable: {
    type: Boolean,
    default: true
  }
});

defineEmits(['refresh', 'activity-action']);

const showAllActivities = ref(false);

const displayedActivities = computed(() => {
  if (showAllActivities.value) {
    return props.activities;
  }
  return props.activities.slice(0, props.limit);
});

// Format time ago
const formatTimeAgo = (timestamp) => {
  const now = new Date();
  const date = new Date(timestamp);
  const seconds = Math.floor((now - date) / 1000);
  
  let interval = Math.floor(seconds / 31536000);
  if (interval >= 1) {
    return interval === 1 ? '1 year ago' : `${interval} years ago`;
  }
  
  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) {
    return interval === 1 ? '1 month ago' : `${interval} months ago`;
  }
  
  interval = Math.floor(seconds / 86400);
  if (interval >= 1) {
    return interval === 1 ? '1 day ago' : `${interval} days ago`;
  }
  
  interval = Math.floor(seconds / 3600);
  if (interval >= 1) {
    return interval === 1 ? '1 hour ago' : `${interval} hours ago`;
  }
  
  interval = Math.floor(seconds / 60);
  if (interval >= 1) {
    return interval === 1 ? '1 minute ago' : `${interval} minutes ago`;
  }
  
  return 'Just now';
};

// Get activity type class
const getActivityTypeClass = (type) => {
  const typeClasses = {
    'member': 'bg-blue-500',
    'attendance': 'bg-green-500',
    'donation': 'bg-yellow-500',
    'event': 'bg-purple-500',
    'group': 'bg-indigo-500',
    'communication': 'bg-pink-500',
    'system': 'bg-neutral-500'
  };
  
  return typeClasses[type] || 'bg-primary-500';
};
</script>
