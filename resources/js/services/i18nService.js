/**
 * Internationalization Service
 * 
 * A service for managing multi-language support throughout the application.
 * Provides utilities for:
 * - Loading and managing translations
 * - Switching between languages
 * - Formatting dates, numbers, and currencies based on locale
 */

import { ref, computed, watch } from 'vue';
import { format as formatDate, formatDistance, formatRelative } from 'date-fns';
import { enUS, es, fr, de, pt, zhCN } from 'date-fns/locale';

// Available locales with their metadata
const availableLocales = {
  'en': {
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    dateLocale: enUS,
    rtl: false
  },
  'es': {
    name: 'Spanish',
    nativeName: 'Español',
    flag: '🇪🇸',
    dateLocale: es,
    rtl: false
  },
  'fr': {
    name: 'French',
    nativeName: 'Français',
    flag: '🇫🇷',
    dateLocale: fr,
    rtl: false
  },
  'de': {
    name: 'German',
    nativeName: 'Deutsch',
    flag: '🇩🇪',
    dateLocale: de,
    rtl: false
  },
  'pt': {
    name: 'Portuguese',
    nativeName: 'Português',
    flag: '🇵🇹',
    dateLocale: pt,
    rtl: false
  },
  'zh': {
    name: 'Chinese (Simplified)',
    nativeName: '中文',
    flag: '🇨🇳',
    dateLocale: zhCN,
    rtl: false
  }
};

// Translation cache
const translationCache = {};

/**
 * Create a composable function for internationalization
 * @returns {Object} - i18n utilities
 */
export function useI18n() {
  // Current locale
  const currentLocale = ref(localStorage.getItem('app_locale') || 'en');
  
  // Current translations
  const translations = ref({});
  
  // Loading state
  const isLoading = ref(false);
  
  // Error state
  const error = ref(null);
  
  // Computed properties
  const localeMetadata = computed(() => availableLocales[currentLocale.value] || availableLocales.en);
  const isRtl = computed(() => localeMetadata.value.rtl);
  const dateLocale = computed(() => localeMetadata.value.dateLocale);
  
  // Watch for locale changes
  watch(currentLocale, (newLocale) => {
    // Save to localStorage
    localStorage.setItem('app_locale', newLocale);
    
    // Load translations for the new locale
    loadTranslations(newLocale);
    
    // Update document language and direction
    if (typeof document !== 'undefined') {
      document.documentElement.lang = newLocale;
      document.documentElement.dir = isRtl.value ? 'rtl' : 'ltr';
      
      // Add/remove RTL class
      if (isRtl.value) {
        document.documentElement.classList.add('rtl');
      } else {
        document.documentElement.classList.remove('rtl');
      }
    }
  });
  
  /**
   * Load translations for a specific locale
   * @param {string} locale - Locale code
   * @returns {Promise<Object>} - Loaded translations
   */
  const loadTranslations = async (locale) => {
    // Use cached translations if available
    if (translationCache[locale]) {
      translations.value = translationCache[locale];
      return translationCache[locale];
    }
    
    isLoading.value = true;
    error.value = null;
    
    try {
      // Load translations from the server
      const response = await fetch(`/api/translations/${locale}`);
      
      if (!response.ok) {
        throw new Error(`Failed to load translations for ${locale}`);
      }
      
      const data = await response.json();
      
      // Cache translations
      translationCache[locale] = data;
      
      // Update current translations
      translations.value = data;
      
      return data;
    } catch (err) {
      console.error('Error loading translations:', err);
      error.value = err.message;
      
      // Fallback to empty translations
      translations.value = {};
      
      return {};
    } finally {
      isLoading.value = false;
    }
  };
  
  /**
   * Translate a key
   * @param {string} key - Translation key
   * @param {Object} params - Parameters for interpolation
   * @returns {string} - Translated text
   */
  const t = (key, params = {}) => {
    // Get nested keys using dot notation
    const keys = key.split('.');
    let result = translations.value;
    
    // Navigate through nested keys
    for (const k of keys) {
      if (result && result[k] !== undefined) {
        result = result[k];
      } else {
        // Key not found
        console.warn(`Translation key not found: ${key}`);
        return key;
      }
    }
    
    // If result is not a string, return the key
    if (typeof result !== 'string') {
      return key;
    }
    
    // Interpolate parameters
    if (params && Object.keys(params).length > 0) {
      return result.replace(/{{\s*([^}]+)\s*}}/g, (match, paramKey) => {
        return params[paramKey] !== undefined ? params[paramKey] : match;
      });
    }
    
    return result;
  };
  
  /**
   * Format a date according to the current locale
   * @param {Date|string|number} date - Date to format
   * @param {string} formatStr - Format string
   * @returns {string} - Formatted date
   */
  const formatDateTime = (date, formatStr = 'PPP') => {
    try {
      return formatDate(new Date(date), formatStr, {
        locale: dateLocale.value
      });
    } catch (err) {
      console.error('Error formatting date:', err);
      return String(date);
    }
  };
  
  /**
   * Format relative time
   * @param {Date|string|number} date - Date to format
   * @param {Date} baseDate - Base date for comparison
   * @returns {string} - Formatted relative time
   */
  const formatRelativeTime = (date, baseDate = new Date()) => {
    try {
      return formatRelative(new Date(date), baseDate, {
        locale: dateLocale.value
      });
    } catch (err) {
      console.error('Error formatting relative time:', err);
      return String(date);
    }
  };
  
  /**
   * Format time distance
   * @param {Date|string|number} date - Date to format
   * @param {Date} baseDate - Base date for comparison
   * @param {Object} options - Options for formatting
   * @returns {string} - Formatted time distance
   */
  const formatTimeDistance = (date, baseDate = new Date(), options = {}) => {
    try {
      return formatDistance(new Date(date), baseDate, {
        locale: dateLocale.value,
        addSuffix: true,
        ...options
      });
    } catch (err) {
      console.error('Error formatting time distance:', err);
      return String(date);
    }
  };
  
  /**
   * Format a number according to the current locale
   * @param {number} number - Number to format
   * @param {Object} options - Number format options
   * @returns {string} - Formatted number
   */
  const formatNumber = (number, options = {}) => {
    try {
      return new Intl.NumberFormat(currentLocale.value, options).format(number);
    } catch (err) {
      console.error('Error formatting number:', err);
      return String(number);
    }
  };
  
  /**
   * Format a currency amount according to the current locale
   * @param {number} amount - Amount to format
   * @param {string} currency - Currency code
   * @returns {string} - Formatted currency amount
   */
  const formatCurrency = (amount, currency = 'USD') => {
    return formatNumber(amount, {
      style: 'currency',
      currency
    });
  };
  
  /**
   * Change the current locale
   * @param {string} locale - New locale code
   */
  const setLocale = (locale) => {
    if (availableLocales[locale]) {
      currentLocale.value = locale;
    } else {
      console.warn(`Locale not supported: ${locale}`);
    }
  };
  
  /**
   * Get all available locales
   * @returns {Object} - Available locales
   */
  const getAvailableLocales = () => {
    return availableLocales;
  };
  
  // Initialize
  if (typeof window !== 'undefined') {
    // Load translations for the current locale
    loadTranslations(currentLocale.value);
    
    // Set document language and direction
    document.documentElement.lang = currentLocale.value;
    document.documentElement.dir = isRtl.value ? 'rtl' : 'ltr';
    
    // Add RTL class if needed
    if (isRtl.value) {
      document.documentElement.classList.add('rtl');
    }
  }
  
  return {
    // State
    currentLocale,
    translations,
    isLoading,
    error,
    
    // Computed properties
    localeMetadata,
    isRtl,
    
    // Methods
    t,
    loadTranslations,
    setLocale,
    getAvailableLocales,
    formatDateTime,
    formatRelativeTime,
    formatTimeDistance,
    formatNumber,
    formatCurrency
  };
}

// Create a Vue plugin
export const I18nPlugin = {
  install(app) {
    const i18n = useI18n();
    
    // Add to global properties
    app.config.globalProperties.$t = i18n.t;
    app.config.globalProperties.$i18n = i18n;
    
    // Provide to components
    app.provide('i18n', i18n);
    
    // Add global directive for translations
    app.directive('t', {
      mounted(el, binding) {
        const key = binding.value;
        const params = binding.arg || {};
        
        el.textContent = i18n.t(key, params);
      },
      
      updated(el, binding) {
        const key = binding.value;
        const params = binding.arg || {};
        
        el.textContent = i18n.t(key, params);
      }
    });
  }
};

// Create a singleton instance
const i18nInstance = useI18n();

// Initialize translations immediately to prevent "t is not defined" errors
// This ensures translations are loaded before components try to use them
i18nInstance.loadTranslations(i18nInstance.currentLocale.value);

// Export singleton instance for direct import
export default i18nInstance;
