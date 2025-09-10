import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import { usePreferredDark } from '@vueuse/core';

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    darkMode: useStorage('darkMode', null),
    sidebarCollapsed: useStorage('sidebarCollapsed', false),
    locale: useStorage('locale', 'en'),
    dateFormat: useStorage('dateFormat', 'MM/DD/YYYY'),
    currencyFormat: useStorage('currencyFormat', 'USD'),
    notificationsEnabled: useStorage('notificationsEnabled', true),
    autoRefreshInterval: useStorage('autoRefreshInterval', 0), // 0 = disabled
    compactMode: useStorage('compactMode', false),
    animations: useStorage('animations', true),
    lastSyncTime: useStorage('lastSyncTime', null),
    themePreset: useStorage('themePreset', 'system'),
    customColors: useStorage('customColors', null),
    enableSeasonalThemes: useStorage('enableSeasonalThemes', true),
    highContrast: useStorage('highContrast', false),
    fontSize: useStorage('fontSize', 'medium'),
    animationsReduced: useStorage('animationsReduced', false),
    churchName: useStorage('churchName', 'Church Management System'),
    churchLogo: useStorage('churchLogo', null),
  }),
  
  getters: {
    isDarkMode: (state) => {
      // If user has explicitly set a preference, use that
      if (state.darkMode !== null) {
        return state.darkMode === 'dark';
      }
      
      // Otherwise, use system preference
      return usePreferredDark().value;
    },
    
    currentTheme: (state) => {
      return state.themePreset;
    },
    
    getLocale: (state) => state.locale,
    getDateFormat: (state) => state.dateFormat,
    getCurrencyFormat: (state) => state.currencyFormat,
    areNotificationsEnabled: (state) => state.notificationsEnabled,
    getAutoRefreshInterval: (state) => state.autoRefreshInterval,
    isCompactMode: (state) => state.compactMode,
    areAnimationsEnabled: (state) => state.animations,
    getLastSyncTime: (state) => state.lastSyncTime,
    hasCustomBranding: (state) => {
      return state.churchName !== 'Church Management System' || state.churchLogo !== null;
    },
    
    accessibilitySettings: (state) => {
      return {
        highContrast: state.highContrast,
        fontSize: state.fontSize,
        animationsReduced: state.animationsReduced
      };
    }
  },
  
  actions: {
    toggleDarkMode() {
      this.darkMode = this.darkMode === 'dark' ? 'light' : 'dark';
      this.updateTheme();
    },
    
    setDarkMode(value) {
      this.darkMode = value ? 'dark' : 'light';
      this.updateTheme();
    },
    
    updateTheme() {
      // Apply dark mode
      document.documentElement.classList.toggle('dark', this.isDarkMode);
      
      // Apply accessibility settings
      document.documentElement.classList.toggle('high-contrast', this.highContrast);
      document.documentElement.classList.toggle('text-sm', this.fontSize === 'small');
      document.documentElement.classList.toggle('text-lg', this.fontSize === 'large');
      document.documentElement.classList.toggle('reduce-motion', this.animationsReduced);
      
      // Apply theme preset
      const themeClasses = ['theme-default', 'theme-modern', 'theme-classic', 'theme-vibrant', 'theme-minimal'];
      themeClasses.forEach(cls => document.documentElement.classList.remove(cls));
      
      if (this.themePreset !== 'system' && this.themePreset !== 'custom') {
        document.documentElement.classList.add(`theme-${this.themePreset}`);
      }
      
      // Apply custom colors if needed
      if (this.themePreset === 'custom' && this.customColors) {
        this.applyCustomColors(this.customColors);
      }
    },
    
    applyCustomColors(colors) {
      const root = document.documentElement;
      if (colors.primary) root.style.setProperty('--color-primary', colors.primary);
      if (colors.secondary) root.style.setProperty('--color-secondary', colors.secondary);
      if (colors.accent) root.style.setProperty('--color-accent', colors.accent);
    },
    
    setThemePreset(preset) {
      this.themePreset = preset;
      this.updateTheme();
    },
    
    setCustomColors(colors) {
      this.customColors = colors;
      this.updateTheme();
    },
    
    toggleSeasonalThemes() {
      this.enableSeasonalThemes = !this.enableSeasonalThemes;
      this.updateTheme();
    },
    
    toggleHighContrast() {
      this.highContrast = !this.highContrast;
      this.updateTheme();
    },
    
    setFontSize(size) {
      this.fontSize = size;
      this.updateTheme();
    },
    
    toggleReducedMotion() {
      this.animationsReduced = !this.animationsReduced;
      this.updateTheme();
    },
    
    setChurchBranding(name, logo) {
      if (name) this.churchName = name;
      if (logo) this.churchLogo = logo;
    },
    
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
    },
    
    setSidebarCollapsed(value) {
      this.sidebarCollapsed = value;
    },
    
    setLocale(locale) {
      this.locale = locale;
    },
    
    setDateFormat(format) {
      this.dateFormat = format;
    },
    
    setCurrencyFormat(format) {
      this.currencyFormat = format;
    },
    
    toggleNotifications() {
      this.notificationsEnabled = !this.notificationsEnabled;
    },
    
    setAutoRefreshInterval(interval) {
      this.autoRefreshInterval = interval;
    },
    
    toggleCompactMode() {
      this.compactMode = !this.compactMode;
    },
    
    toggleAnimations() {
      this.animations = !this.animations;
    },
    
    updateLastSyncTime() {
      this.lastSyncTime = new Date().toISOString();
    },
    
    resetSettings() {
      this.darkMode = null;
      this.sidebarCollapsed = false;
      this.locale = 'en';
      this.dateFormat = 'MM/DD/YYYY';
      this.currencyFormat = 'USD';
      this.notificationsEnabled = true;
      this.autoRefreshInterval = 0;
      this.compactMode = false;
      this.animations = true;
      this.lastSyncTime = null;
      
      // Reset theme customization settings
      this.themePreset = 'system';
      this.customColors = null;
      this.enableSeasonalThemes = true;
      this.highContrast = false;
      this.fontSize = 'medium';
      this.animationsReduced = false;
      
      // Update the theme
      this.updateTheme();
    },
  },
  
  persist: {
    key: 'church-mgmt-settings',
    storage: localStorage,
  },
});
