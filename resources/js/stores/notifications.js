import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: useStorage('notifications', []),
    unreadCount: useStorage('unreadCount', 0),
    settings: useStorage('notification-settings', {
      email: true,
      browser: true,
      mobile: false,
      desktop: true,
      categories: {
        members: true,
        events: true,
        groups: true,
        donations: true,
        system: true
      },
      doNotDisturb: {
        enabled: false,
        startTime: '22:00',
        endTime: '08:00'
      }
    })
  }),
  
  getters: {
    unreadNotifications: (state) => {
      return state.notifications.filter(notification => !notification.read);
    },
    
    readNotifications: (state) => {
      return state.notifications.filter(notification => notification.read);
    },
    
    getNotificationsByCategory: (state) => (category) => {
      return state.notifications.filter(notification => notification.category === category);
    },
    
    getNotificationsById: (state) => (id) => {
      return state.notifications.find(notification => notification.id === id);
    },
    
    isDoNotDisturbActive: (state) => {
      if (!state.settings.doNotDisturb.enabled) return false;
      
      const now = new Date();
      const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
      const startTime = state.settings.doNotDisturb.startTime;
      const endTime = state.settings.doNotDisturb.endTime;
      
      // Handle cases where the DND period spans midnight
      if (startTime > endTime) {
        return currentTime >= startTime || currentTime < endTime;
      } else {
        return currentTime >= startTime && currentTime < endTime;
      }
    }
  },
  
  actions: {
    addNotification(notification) {
      // Generate a unique ID if not provided
      if (!notification.id) {
        notification.id = `notification-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
      }
      
      // Set default values if not provided
      const newNotification = {
        read: false,
        timestamp: new Date().toISOString(),
        ...notification
      };
      
      // Add to the beginning of the array
      this.notifications.unshift(newNotification);
      
      // Update unread count
      this.unreadCount++;
      
      return newNotification.id;
    },
    
    markAsRead(id) {
      const notification = this.notifications.find(n => n.id === id);
      if (notification && !notification.read) {
        notification.read = true;
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      }
    },
    
    markAllAsRead() {
      this.notifications.forEach(notification => {
        notification.read = true;
      });
      this.unreadCount = 0;
    },
    
    removeNotification(id) {
      const index = this.notifications.findIndex(n => n.id === id);
      if (index !== -1) {
        const notification = this.notifications[index];
        if (!notification.read) {
          this.unreadCount = Math.max(0, this.unreadCount - 1);
        }
        this.notifications.splice(index, 1);
      }
    },
    
    clearAllNotifications() {
      this.notifications = [];
      this.unreadCount = 0;
    },
    
    updateSettings(settings) {
      this.settings = { ...this.settings, ...settings };
    },
    
    toggleCategoryEnabled(category) {
      if (this.settings.categories[category] !== undefined) {
        this.settings.categories[category] = !this.settings.categories[category];
      }
    },
    
    toggleDoNotDisturb() {
      this.settings.doNotDisturb.enabled = !this.settings.doNotDisturb.enabled;
    },
    
    setDoNotDisturbTime(startTime, endTime) {
      this.settings.doNotDisturb.startTime = startTime;
      this.settings.doNotDisturb.endTime = endTime;
    }
  }
});
