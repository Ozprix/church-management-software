import { useNotificationStore } from '../stores/notifications';
import { useSettingsStore } from '../stores/settings';

class NotificationService {
  constructor() {
    this.notificationStore = useNotificationStore();
    this.settingsStore = useSettingsStore();
    this.hasRequestedPermission = false;
    this.browserPermission = null;
  }

  /**
   * Initialize the notification service
   */
  async init() {
    // Check if browser notifications are supported
    if ('Notification' in window) {
      this.browserPermission = Notification.permission;
      
      // Request permission if not already requested and browser notifications are enabled
      if (this.notificationStore.settings.browser && 
          this.browserPermission !== 'granted' && 
          this.browserPermission !== 'denied' &&
          !this.hasRequestedPermission) {
        
        this.hasRequestedPermission = true;
        try {
          const permission = await Notification.requestPermission();
          this.browserPermission = permission;
        } catch (error) {
          console.error('Error requesting notification permission:', error);
        }
      }
    }
  }

  /**
   * Send a notification
   * @param {Object} notification - The notification object
   * @param {string} notification.title - The notification title
   * @param {string} notification.message - The notification message
   * @param {string} notification.category - The notification category
   * @param {string} notification.link - Optional link to navigate to when clicked
   * @param {Object} notification.data - Optional additional data
   * @returns {string|null} - The notification ID if successful, null otherwise
   */
  send(notification) {
    // Check if notifications are enabled for this category
    if (!this.notificationStore.settings.categories[notification.category]) {
      return null;
    }
    
    // Check if Do Not Disturb is active
    if (this.notificationStore.isDoNotDisturbActive) {
      // Still add to notification center but don't show browser notification
      return this.notificationStore.addNotification(notification);
    }
    
    // Add to notification store
    const notificationId = this.notificationStore.addNotification(notification);
    
    // Send browser notification if enabled
    if (this.notificationStore.settings.browser && this.browserPermission === 'granted') {
      this.sendBrowserNotification(notification);
    }
    
    // Send desktop notification if enabled (for Electron or similar)
    if (this.notificationStore.settings.desktop && window.electron) {
      this.sendDesktopNotification(notification);
    }
    
    return notificationId;
  }

  /**
   * Send a browser notification
   * @param {Object} notification - The notification object
   */
  sendBrowserNotification(notification) {
    if (!('Notification' in window)) return;
    
    try {
      const browserNotification = new Notification(notification.title, {
        body: notification.message,
        icon: '/images/logo.png',
        tag: notification.id
      });
      
      // Handle click event
      browserNotification.onclick = () => {
        window.focus();
        if (notification.link) {
          window.location.href = notification.link;
        }
        this.notificationStore.markAsRead(notification.id);
      };
    } catch (error) {
      console.error('Error sending browser notification:', error);
    }
  }

  /**
   * Send a desktop notification (for Electron or similar)
   * @param {Object} notification - The notification object
   */
  sendDesktopNotification(notification) {
    if (!window.electron) return;
    
    try {
      window.electron.sendNotification({
        title: notification.title,
        body: notification.message,
        icon: '/images/logo.png',
        id: notification.id,
        link: notification.link
      });
    } catch (error) {
      console.error('Error sending desktop notification:', error);
    }
  }

  /**
   * Mark a notification as read
   * @param {string} id - The notification ID
   */
  markAsRead(id) {
    this.notificationStore.markAsRead(id);
  }

  /**
   * Mark all notifications as read
   */
  markAllAsRead() {
    this.notificationStore.markAllAsRead();
  }

  /**
   * Remove a notification
   * @param {string} id - The notification ID
   */
  remove(id) {
    this.notificationStore.removeNotification(id);
  }

  /**
   * Clear all notifications
   */
  clearAll() {
    this.notificationStore.clearAllNotifications();
  }
}

// Create a singleton instance
const notificationService = new NotificationService();

export default notificationService;
