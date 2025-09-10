/**
 * Translations Index
 * 
 * This file exports all translations for easy importing.
 * It provides a fallback mechanism for when API translations are not available.
 */

import en from './en';
import es from './es';
import fr from './fr';
import de from './de';
import pt from './pt';
import zh from './zh';

// Available translations with their metadata
const translations = {
  en,
  es,
  fr,
  de,
  pt,
  zh
};

/**
 * Get a translation by locale
 * @param {string} locale - Locale code
 * @returns {Object} - Translation object or empty object if not found
 */
export const getTranslation = (locale) => {
  return translations[locale] || {};
};

/**
 * Get all available translations
 * @returns {Object} - All translations
 */
export const getAllTranslations = () => {
  return translations;
};

// Export default English translations
export default en;
