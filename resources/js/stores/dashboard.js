import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    widgets: useStorage('dashboard-widgets', [
      {
        id: 'members-stats',
        type: 'stats',
        title: 'Members',
        size: 'small',
        position: 0,
        visible: true,
        settings: {
          showTrend: true,
          showProgress: false
        }
      },
      {
        id: 'attendance-stats',
        type: 'stats',
        title: 'Attendance',
        size: 'small',
        position: 1,
        visible: true,
        settings: {
          showTrend: true,
          showProgress: false
        }
      },
      {
        id: 'donations-stats',
        type: 'stats',
        title: 'Donations',
        size: 'small',
        position: 2,
        visible: true,
        settings: {
          showTrend: true,
          showProgress: true
        }
      },
      {
        id: 'growth-chart',
        type: 'chart',
        title: 'Growth Trends',
        size: 'medium',
        position: 3,
        visible: true,
        settings: {
          chartType: 'line',
          period: 'year'
        }
      },
      {
        id: 'upcoming-events',
        type: 'events',
        title: 'Upcoming Events',
        size: 'medium',
        position: 4,
        visible: true,
        settings: {
          limit: 3
        }
      },
      {
        id: 'recent-activity',
        type: 'activity',
        title: 'Recent Activity',
        size: 'medium',
        position: 5,
        visible: true,
        settings: {
          limit: 5
        }
      },
      {
        id: 'donation-distribution',
        type: 'chart',
        title: 'Donation Distribution',
        size: 'medium',
        position: 6,
        visible: true,
        settings: {
          chartType: 'doughnut',
          period: 'month'
        }
      }
    ]),
    
    // User's layout preference
    layout: useStorage('dashboard-layout', 'grid'), // 'grid' or 'list'
    
    // Whether widgets can be dragged and reordered
    editMode: false
  }),
  
  getters: {
    visibleWidgets: (state) => {
      return state.widgets
        .filter(widget => widget.visible)
        .sort((a, b) => a.position - b.position);
    },
    
    getWidgetById: (state) => (id) => {
      return state.widgets.find(widget => widget.id === id);
    },
    
    smallWidgets: (state) => {
      return state.visibleWidgets.filter(widget => widget.size === 'small');
    },
    
    mediumWidgets: (state) => {
      return state.visibleWidgets.filter(widget => widget.size === 'medium');
    },
    
    largeWidgets: (state) => {
      return state.visibleWidgets.filter(widget => widget.size === 'large');
    }
  },
  
  actions: {
    toggleWidgetVisibility(id) {
      const widget = this.widgets.find(w => w.id === id);
      if (widget) {
        widget.visible = !widget.visible;
      }
    },
    
    updateWidgetPosition(id, newPosition) {
      const widget = this.widgets.find(w => w.id === id);
      if (widget) {
        widget.position = newPosition;
      }
    },
    
    updateWidgetSize(id, newSize) {
      const widget = this.widgets.find(w => w.id === id);
      if (widget) {
        widget.size = newSize;
      }
    },
    
    updateWidgetSettings(id, settings) {
      const widget = this.widgets.find(w => w.id === id);
      if (widget) {
        widget.settings = { ...widget.settings, ...settings };
      }
    },
    
    reorderWidgets(newOrder) {
      // newOrder is an array of widget IDs in their new order
      newOrder.forEach((id, index) => {
        const widget = this.widgets.find(w => w.id === id);
        if (widget) {
          widget.position = index;
        }
      });
    },
    
    toggleEditMode() {
      this.editMode = !this.editMode;
    },
    
    toggleLayout() {
      this.layout = this.layout === 'grid' ? 'list' : 'grid';
    },
    
    resetDashboard() {
      // Reset to default widget configuration
      this.$reset();
    },
    
    addWidget(widget) {
      // Generate a unique ID if not provided
      if (!widget.id) {
        widget.id = `widget-${Date.now()}`;
      }
      
      // Set position to the end of the list
      widget.position = this.widgets.length;
      
      // Add the new widget
      this.widgets.push(widget);
      
      return widget.id;
    },
    
    removeWidget(id) {
      const index = this.widgets.findIndex(w => w.id === id);
      if (index !== -1) {
        this.widgets.splice(index, 1);
        
        // Update positions of remaining widgets
        this.widgets.forEach((widget, i) => {
          if (widget.position > index) {
            widget.position--;
          }
        });
      }
    }
  }
});
