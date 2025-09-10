<template>
  <div class="dashboard">
    <div class="container mx-auto px-4 py-8">
      <div class="flex flex-wrap justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-0">Dashboard</h1>
        
        <div class="flex items-center space-x-3">
          <!-- Dashboard Layout Toggle -->
          <button 
            @click="dashboardStore.toggleLayout"
            class="p-2 rounded-full text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-300"
            :title="dashboardStore.layout === 'grid' ? 'Switch to List View' : 'Switch to Grid View'"
          >
            <svg v-if="dashboardStore.layout === 'grid'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
          
          <!-- Edit Mode Toggle -->
          <button 
            @click="dashboardStore.toggleEditMode"
            class="p-2 rounded-full text-neutral-500 hover:text-primary-500 dark:text-neutral-400 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-300"
            :class="{ 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400': dashboardStore.editMode }"
            :title="dashboardStore.editMode ? 'Exit Edit Mode' : 'Customize Dashboard'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>
          
          <!-- Welcome Badge -->
          <Badge variant="primary" size="lg" radius="full" class="animate-bounce-in">
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </template>
            Welcome Back
          </Badge>
        </div>
      </div>
      
      <!-- Dashboard Edit Mode Banner -->
      <Card v-if="dashboardStore.editMode" variant="glass" elevation="lg" radius="lg" animate class="mb-8 bg-primary-500 bg-opacity-10 border border-primary-500 border-opacity-30">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <div>
              <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-1">Dashboard Edit Mode</h2>
              <p class="text-neutral-600 dark:text-neutral-300">Customize your dashboard by dragging widgets, toggling visibility, or changing settings.</p>
            </div>
          </div>
          <Button @click="dashboardStore.toggleEditMode" variant="primary" size="sm">Done Editing</Button>
        </div>
      </Card>
      
      <!-- Welcome Card (only shown if not in edit mode) -->
      <Card v-else variant="glass" elevation="lg" radius="lg" animate class="mb-8 bg-sacred-pattern bg-opacity-5 bg-blend-overlay">
        <template #header>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Welcome to the Church Management System</h2>
        </template>
        <p class="text-neutral-600 dark:text-neutral-300">Manage your church's members, groups, events, and more from this central dashboard.</p>
      </Card>
      
      <!-- Stats Widgets Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Members Stats Widget -->
        <StatsWidget
          v-if="getWidgetVisibility('members-stats')"
          title="Members"
          value="1,254"
          description="Total church members"
          :loading="widgetLoading.members"
          :trend="5.2"
          :show-trend="true"
          comparison-period="last month"
          @refresh="refreshWidget('members')"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </template>
        </StatsWidget>
        
        <!-- Attendance Stats Widget -->
        <StatsWidget
          v-if="getWidgetVisibility('attendance-stats')"
          title="Attendance"
          value="742"
          description="Last Sunday attendance"
          :loading="widgetLoading.attendance"
          :trend="-2.1"
          :show-trend="true"
          comparison-period="previous Sunday"
          @refresh="refreshWidget('attendance')"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </template>
        </StatsWidget>
        
        <!-- Donations Stats Widget -->
        <StatsWidget
          v-if="getWidgetVisibility('donations-stats')"
          title="Donations"
          value="$24,850"
          description="This month's total"
          :loading="widgetLoading.donations"
          :trend="8.7"
          :show-trend="true"
          comparison-period="last month"
          :show-progress="true"
          :progress-value="83"
          progress-label="Monthly Goal"
          progress-color="success"
          @refresh="refreshWidget('donations')"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>
        </StatsWidget>
      </div>
      
      <!-- Main Widgets Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Growth Chart Widget -->
        <ChartWidget
          v-if="getWidgetVisibility('growth-chart')"
          title="Growth Trends"
          :chart-data="chartData.growth"
          chart-type="line"
          :loading="widgetLoading.growthChart"
          :configurable="true"
          @refresh="refreshWidget('growthChart')"
          @configure="configureWidget('growth-chart')"
        />
        
        <!-- Upcoming Events Widget -->
        <EventsWidget
          v-if="getWidgetVisibility('upcoming-events')"
          title="Upcoming Events"
          :events="eventsData"
          :limit="3"
          :loading="widgetLoading.events"
          @refresh="refreshWidget('events')"
        />
        
        <!-- Recent Activity Widget -->
        <ActivityWidget
          v-if="getWidgetVisibility('recent-activity')"
          title="Recent Activity"
          :activities="activityData"
          :limit="5"
          :loading="widgetLoading.activity"
          @refresh="refreshWidget('activity')"
          @activity-action="handleActivityAction"
        />
        
        <!-- Donation Distribution Chart Widget -->
        <ChartWidget
          v-if="getWidgetVisibility('donation-distribution')"
          title="Donation Distribution"
          :chart-data="chartData.donations"
          chart-type="doughnut"
          :loading="widgetLoading.donationsChart"
          :configurable="true"
          @refresh="refreshWidget('donationsChart')"
          @configure="configureWidget('donation-distribution')"
        />
      </div>
      
      <!-- Dashboard Configuration (only shown in edit mode) -->
      <div v-if="dashboardStore.editMode" class="mb-8">
        <Card variant="default" elevation="md" radius="lg">
          <template #header>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Dashboard Configuration</h2>
          </template>
          
          <div class="space-y-4">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">Widget Visibility</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div 
                v-for="widget in dashboardStore.widgets" 
                :key="widget.id"
                class="flex items-center space-x-2 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 transition-colors duration-300"
              >
                <input 
                  type="checkbox" 
                  :id="`widget-${widget.id}`" 
                  :checked="widget.visible"
                  @change="toggleWidgetVisibility(widget.id)"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded transition-colors duration-300"
                >
                <label :for="`widget-${widget.id}`" class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">{{ widget.title }}</label>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
              <Button @click="dashboardStore.resetDashboard()" variant="outline" size="sm">Reset to Default</Button>
              <Button @click="dashboardStore.toggleEditMode()" variant="primary" size="sm">Done</Button>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Member Roles</h3>
          </div>
          <p class="text-neutral-600 dark:text-neutral-400 mb-4">Assign and manage roles and permissions for group members.</p>
          <Button variant="outline" size="sm" tag="router-link" to="/groups" icon iconPosition="right">
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </template>
            Manage Roles
          </Button>
        </Card>
        
        <!-- Analytics Card -->
        <Card variant="glass" elevation="md" radius="lg" className="transition-all duration-300 hover:shadow-lg">
          <div class="flex items-center mb-4">
            <div class="p-2 rounded-full bg-accent-teal-light/50 dark:bg-accent-teal-dark/30 mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-accent-teal-DEFAULT dark:text-accent-teal-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Group Analytics</h3>
          </div>
          <p class="text-neutral-600 dark:text-neutral-400 mb-4">View attendance trends, engagement metrics, and group growth.</p>
          <Button variant="success" size="sm" tag="router-link" to="/groups" icon iconPosition="right">
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </template>
            View Analytics
          </Button>
        </Card>
        
        <!-- Communication Card -->
        <Card variant="glass" elevation="md" radius="lg" className="transition-all duration-300 hover:shadow-lg">
          <div class="flex items-center mb-4">
            <div class="p-2 rounded-full bg-secondary-100 dark:bg-secondary-900/50 mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary-600 dark:text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Group Communication</h3>
          </div>
          <p class="text-neutral-600 dark:text-neutral-400 mb-4">Send messages, announcements, and prayer requests to group members.</p>
          <Button variant="secondary" size="sm" tag="router-link" to="/groups" icon iconPosition="right">
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </template>
            Communicate
          </Button>
        </Card>
      </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useSettingsStore } from '../stores/settings';
import { useDashboardStore } from '../stores/dashboard';
import { useToast } from '../composables/useToast';

// UI Components
import Card from '../components/ui/Card.vue';
import Button from '../components/ui/Button.vue';
import Badge from '../components/ui/Badge.vue';

// Dashboard Widget Components
import StatsWidget from '../components/dashboard/StatsWidget.vue';
import ChartWidget from '../components/dashboard/ChartWidget.vue';
import EventsWidget from '../components/dashboard/EventsWidget.vue';
import ActivityWidget from '../components/dashboard/ActivityWidget.vue';

// Store instances
const authStore = useAuthStore();
const settingsStore = useSettingsStore();
const dashboardStore = useDashboardStore();
const toast = useToast();

// Widget loading states
const widgetLoading = ref({
  members: false,
  attendance: false,
  donations: false,
  growthChart: false,
  events: false,
  activity: false,
  donationsChart: false
});

// Sample data for widgets
const chartData = ref({
  growth: [
    {
      name: 'Members',
      data: [
        { x: 'Jan', y: 1100 },
        { x: 'Feb', y: 1150 },
        { x: 'Mar', y: 1180 },
        { x: 'Apr', y: 1200 },
        { x: 'May', y: 1220 },
        { x: 'Jun', y: 1254 }
      ]
    },
    {
      name: 'Attendance',
      data: [
        { x: 'Jan', y: 650 },
        { x: 'Feb', y: 680 },
        { x: 'Mar', y: 720 },
        { x: 'Apr', y: 750 },
        { x: 'May', y: 760 },
        { x: 'Jun', y: 742 }
      ]
    }
  ],
  donations: [
    { name: 'General Fund', value: 15000 },
    { name: 'Building Fund', value: 5000 },
    { name: 'Missions', value: 3000 },
    { name: 'Youth Ministry', value: 1850 }
  ]
});

// Sample events data
const eventsData = ref([
  {
    title: 'Sunday Service',
    date: '2025-05-26T10:00:00',
    location: 'Main Sanctuary',
    type: 'Service'
  },
  {
    title: 'Prayer Meeting',
    date: '2025-05-28T19:00:00',
    location: 'Prayer Room',
    type: 'Meeting'
  },
  {
    title: 'Youth Group',
    date: '2025-05-30T18:30:00',
    location: 'Youth Center',
    type: 'Meeting'
  },
  {
    title: 'Community Outreach',
    date: '2025-06-01T09:00:00',
    location: 'Downtown Community Center',
    type: 'Outreach'
  }
]);

// Sample activity data
const activityData = ref([
  {
    title: 'New Member Added',
    description: '<strong>John Smith</strong> was added to the database',
    timestamp: '2025-05-25T18:30:00',
    type: 'member',
    actions: [
      { id: 'view', label: 'View Profile' }
    ]
  },
  {
    title: 'Donation Received',
    description: '<strong>$500</strong> donation received for <strong>Building Fund</strong>',
    timestamp: '2025-05-25T15:45:00',
    type: 'donation'
  },
  {
    title: 'Event Created',
    description: '<strong>Community Outreach</strong> event scheduled for June 1st',
    timestamp: '2025-05-24T14:20:00',
    type: 'event',
    actions: [
      { id: 'view', label: 'View Event' }
    ]
  },
  {
    title: 'Group Updated',
    description: '<strong>Youth Ministry</strong> group details were updated',
    timestamp: '2025-05-24T11:15:00',
    type: 'group'
  },
  {
    title: 'Attendance Recorded',
    description: '<strong>742</strong> attendees recorded for Sunday Service',
    timestamp: '2025-05-23T13:10:00',
    type: 'attendance'
  }
]);

// Widget visibility helper
function getWidgetVisibility(id) {
  const widget = dashboardStore.getWidgetById(id);
  return widget ? widget.visible : false;
}

// Toggle widget visibility
function toggleWidgetVisibility(id) {
  dashboardStore.toggleWidgetVisibility(id);
}

// Refresh widget data
async function refreshWidget(widgetId) {
  // Set loading state
  widgetLoading.value[widgetId] = true;
  
  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500));
  
  // Update data if needed
  // In a real application, this would fetch fresh data from the API
  
  // Reset loading state
  widgetLoading.value[widgetId] = false;
  
  // Show success toast
  toast.success(`${widgetId.charAt(0).toUpperCase() + widgetId.slice(1)} data refreshed`, {
    position: 'top-right',
    duration: 2000
  });
}

// Configure widget
function configureWidget(widgetId) {
  // Show configuration modal for the widget
  // This would be implemented with a modal component
  toast.info(`Configure widget: ${widgetId}`, {
    position: 'top-right',
    duration: 2000
  });
}

// Handle activity action
function handleActivityAction({ activity, action }) {
  // Handle activity action based on type and action ID
  toast.info(`Action ${action} on activity: ${activity.title}`, {
    position: 'top-right',
    duration: 2000
  });
}

onMounted(() => {
  // Show welcome toast
  toast.success('Welcome back to the Church Management System!', {
    position: 'top-right',
    duration: 3000
  });
  
  // Initialize dashboard if needed
  // This could load user preferences or fetch initial data
});
</script>

<style scoped>
.dashboard {
  min-height: calc(100vh - 64px);
  background-color: #f9fafb;
  transition: background-color 0.3s ease;
}

:deep(.dark) .dashboard {
  background-color: #111827;
}
</style>
