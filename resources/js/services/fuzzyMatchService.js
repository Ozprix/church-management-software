/**
 * Fuzzy Match Service
 * 
 * A service for performing fuzzy string matching to improve search results.
 * Provides utilities for:
 * - Calculating string similarity
 * - Fuzzy searching within arrays of objects
 * - Highlighting matched text
 */

/**
 * Calculate Levenshtein distance between two strings
 * @param {string} str1 - First string
 * @param {string} str2 - Second string
 * @returns {number} - Distance (lower means more similar)
 */
const levenshteinDistance = (str1, str2) => {
  const track = Array(str2.length + 1).fill(null).map(() => 
    Array(str1.length + 1).fill(null));
  
  for (let i = 0; i <= str1.length; i += 1) {
    track[0][i] = i;
  }
  
  for (let j = 0; j <= str2.length; j += 1) {
    track[j][0] = j;
  }
  
  for (let j = 1; j <= str2.length; j += 1) {
    for (let i = 1; i <= str1.length; i += 1) {
      const indicator = str1[i - 1] === str2[j - 1] ? 0 : 1;
      track[j][i] = Math.min(
        track[j][i - 1] + 1, // deletion
        track[j - 1][i] + 1, // insertion
        track[j - 1][i - 1] + indicator, // substitution
      );
    }
  }
  
  return track[str2.length][str1.length];
};

/**
 * Calculate similarity between two strings (0-1 scale)
 * @param {string} str1 - First string
 * @param {string} str2 - Second string
 * @returns {number} - Similarity score (1 means identical)
 */
const calculateSimilarity = (str1, str2) => {
  if (!str1 || !str2) return 0;
  
  // Normalize strings for comparison
  const s1 = str1.toLowerCase().trim();
  const s2 = str2.toLowerCase().trim();
  
  // Handle exact matches and empty strings
  if (s1 === s2) return 1;
  if (s1.length === 0 || s2.length === 0) return 0;
  
  // Calculate Levenshtein distance
  const distance = levenshteinDistance(s1, s2);
  
  // Convert to similarity score (0-1)
  return 1 - (distance / Math.max(s1.length, s2.length));
};

/**
 * Perform fuzzy search on an array of objects
 * @param {Array} items - Array of objects to search
 * @param {string} query - Search query
 * @param {Array} keys - Object keys to search within
 * @param {number} threshold - Minimum similarity threshold (0-1)
 * @returns {Array} - Matched items with scores
 */
const fuzzySearch = (items, query, keys, threshold = 0.6) => {
  if (!query || query.trim() === '') return [];
  if (!items || !Array.isArray(items) || items.length === 0) return [];
  
  const normalizedQuery = query.toLowerCase().trim();
  const results = [];
  
  // Search through each item
  for (const item of items) {
    let bestScore = 0;
    let bestMatch = '';
    
    // Check each specified key
    for (const key of keys) {
      // Handle nested keys with dot notation
      const value = key.split('.').reduce((obj, k) => obj && obj[k], item);
      
      if (value) {
        const stringValue = String(value);
        const score = calculateSimilarity(normalizedQuery, stringValue);
        
        // Keep track of best match for this item
        if (score > bestScore) {
          bestScore = score;
          bestMatch = stringValue;
        }
      }
    }
    
    // Add to results if score meets threshold
    if (bestScore >= threshold) {
      results.push({
        item,
        score: bestScore,
        match: bestMatch
      });
    }
  }
  
  // Sort by score (highest first)
  return results.sort((a, b) => b.score - a.score);
};

/**
 * Highlight matched text in a string
 * @param {string} text - Text to highlight within
 * @param {string} query - Query to highlight
 * @param {string} highlightClass - CSS class for highlighting
 * @returns {string} - HTML with highlighted text
 */
const highlightMatches = (text, query, highlightClass = 'highlight') => {
  if (!text || !query || query.trim() === '') return text;
  
  const normalizedText = String(text);
  const normalizedQuery = query.toLowerCase().trim();
  
  // Simple case: direct substring match
  const index = normalizedText.toLowerCase().indexOf(normalizedQuery);
  
  if (index >= 0) {
    const before = normalizedText.substring(0, index);
    const match = normalizedText.substring(index, index + normalizedQuery.length);
    const after = normalizedText.substring(index + normalizedQuery.length);
    
    return `${before}<span class="${highlightClass}">${match}</span>${after}`;
  }
  
  // No direct match, return original
  return normalizedText;
};

/**
 * Create a composable function for fuzzy matching
 * @returns {Object} - Fuzzy matching utilities
 */
export function useFuzzyMatch() {
  return {
    calculateSimilarity,
    fuzzySearch,
    highlightMatches
  };
}

// Export singleton instance for direct import
export default useFuzzyMatch();
