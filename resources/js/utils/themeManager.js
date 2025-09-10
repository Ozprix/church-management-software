// Theme Manager for Church Management System
// Handles theme customization, persistence, and application

import { useStorage } from '@vueuse/core';
import { watch, computed } from 'vue';
import { useSettingsStore } from '../stores/settings';

// Default theme presets
export const themePresets = {
  system: {
    name: 'System Default',
    colors: {
      primary: {
        50: '#f0f9ff',
        100: '#e0f2fe',
        200: '#bae6fd',
        300: '#7dd3fc',
        400: '#38bdf8',
        500: '#0ea5e9',
        600: '#0284c7',
        700: '#0369a1',
        800: '#075985',
        900: '#0c4a6e',
      },
      secondary: {
        50: '#f8fafc',
        100: '#f1f5f9',
        200: '#e2e8f0',
        300: '#cbd5e1',
        400: '#94a3b8',
        500: '#64748b',
        600: '#475569',
        700: '#334155',
        800: '#1e293b',
        900: '#0f172a',
      }
    }
  },
  light: {
    name: 'Light Theme',
    colors: {
      primary: {
        50: '#f0f9ff',
        100: '#e0f2fe',
        200: '#bae6fd',
        300: '#7dd3fc',
        400: '#38bdf8',
        500: '#0ea5e9',
        600: '#0284c7',
        700: '#0369a1',
        800: '#075985',
        900: '#0c4a6e',
      },
      secondary: {
        50: '#f8fafc',
        100: '#f1f5f9',
        200: '#e2e8f0',
        300: '#cbd5e1',
        400: '#94a3b8',
        500: '#64748b',
        600: '#475569',
        700: '#334155',
        800: '#1e293b',
        900: '#0f172a',
      }
    }
  },
  dark: {
    name: 'Dark Theme',
    colors: {
      primary: {
        50: '#0c4a6e',
        100: '#075985',
        200: '#0369a1',
        300: '#0284c7',
        400: '#0ea5e9',
        500: '#38bdf8',
        600: '#7dd3fc',
        700: '#bae6fd',
        800: '#e0f2fe',
        900: '#f0f9ff',
      },
      secondary: {
        50: '#0f172a',
        100: '#1e293b',
        200: '#334155',
        300: '#475569',
        400: '#64748b',
        500: '#94a3b8',
        600: '#cbd5e1',
        700: '#e2e8f0',
        800: '#f1f5f9',
        900: '#f8fafc',
      }
    }
  }
};

// Initialize theme system
export function initializeThemeSystem() {
  const settingsStore = useSettingsStore();
  
  // Theme state
  const currentTheme = useStorage('theme', 'system');
  const customColors = useStorage('custom-colors', {});
  const isDarkMode = useStorage('dark-mode', false);
  
  // Apply theme to document
  const applyTheme = (theme) => {
    const root = document.documentElement;
    const themeData = themePresets[theme] || themePresets.system;
    
    // Apply color variables
    Object.entries(themeData.colors).forEach(([colorName, shades]) => {
      Object.entries(shades).forEach(([shade, value]) => {
        root.style.setProperty(`--color-${colorName}-${shade}`, value);
      });
    });
    
    // Apply custom colors if any
    Object.entries(customColors.value).forEach(([key, value]) => {
      root.style.setProperty(`--${key}`, value);
    });
    
    // Apply dark mode class
    if (isDarkMode.value) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  };
  
  // Watch for theme changes
  watch(currentTheme, (newTheme) => {
    applyTheme(newTheme);
  }, { immediate: true });
  
  watch(isDarkMode, () => {
    applyTheme(currentTheme.value);
  });
  
  // Toggle dark mode
  const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
  };
  
  // Set custom color
  const setCustomColor = (key, value) => {
    customColors.value[key] = value;
    applyTheme(currentTheme.value);
  };
  
  // Reset to default theme
  const resetTheme = () => {
    currentTheme.value = 'system';
    customColors.value = {};
    isDarkMode.value = false;
  };
  
  return {
    currentTheme,
    customColors,
    isDarkMode,
    toggleDarkMode,
    setCustomColor,
    resetTheme,
    applyTheme
  };
}

// Export a singleton instance
export const themeManager = initializeThemeSystem();