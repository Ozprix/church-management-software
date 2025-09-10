/**
 * Internationalization Components
 * 
 * This file exports all i18n components for easy importing.
 * It also provides a plugin to register all components globally.
 */

import LanguageSelector from './LanguageSelector.vue';
import TranslatedText from './TranslatedText.vue';
import { I18nPlugin } from '../../services/i18nService';

// Create a plugin to register all i18n components
const I18nComponentsPlugin = {
  install(app) {
    // Register the i18n service plugin first
    app.use(I18nPlugin);
    
    // Register all i18n components globally
    app.component('LanguageSelector', LanguageSelector);
    app.component('TranslatedText', TranslatedText);
    
    // Add a shorter alias for TranslatedText
    app.component('T', TranslatedText);
  }
};

// Export individual components
export {
  LanguageSelector,
  TranslatedText,
  I18nPlugin,
  I18nComponentsPlugin
};

// Export default plugin
export default I18nComponentsPlugin;
