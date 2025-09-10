/**
 * Cache Service
 * 
 * A service for managing application-level caching to improve performance.
 * Implements various caching strategies including:
 * - Memory caching for frequently accessed data
 * - LocalStorage caching for persistent data
 * - Cache invalidation strategies
 */

class CacheService {
  constructor() {
    this.memoryCache = new Map();
    this.defaultTTL = 5 * 60 * 1000; // 5 minutes in milliseconds
    this.cachePrefix = 'church_mgmt_cache_';
    
    // Initialize cache cleanup interval
    this.initCacheCleanup();
  }
  
  /**
   * Set an item in the memory cache
   * @param {string} key - Cache key
   * @param {any} value - Value to cache
   * @param {number} ttl - Time to live in milliseconds (optional)
   */
  setMemoryItem(key, value, ttl = this.defaultTTL) {
    const expiresAt = Date.now() + ttl;
    
    this.memoryCache.set(key, {
      value,
      expiresAt
    });
    
    return value;
  }
  
  /**
   * Get an item from the memory cache
   * @param {string} key - Cache key
   * @returns {any|null} - Cached value or null if not found/expired
   */
  getMemoryItem(key) {
    if (!this.memoryCache.has(key)) {
      return null;
    }
    
    const cachedItem = this.memoryCache.get(key);
    
    // Check if the item has expired
    if (cachedItem.expiresAt < Date.now()) {
      this.memoryCache.delete(key);
      return null;
    }
    
    return cachedItem.value;
  }
  
  /**
   * Remove an item from the memory cache
   * @param {string} key - Cache key
   */
  removeMemoryItem(key) {
    this.memoryCache.delete(key);
  }
  
  /**
   * Clear all items from the memory cache
   */
  clearMemoryCache() {
    this.memoryCache.clear();
  }
  
  /**
   * Set an item in localStorage with expiration
   * @param {string} key - Cache key
   * @param {any} value - Value to cache
   * @param {number} ttl - Time to live in milliseconds (optional)
   */
  setStorageItem(key, value, ttl = this.defaultTTL) {
    const item = {
      value,
      expiresAt: Date.now() + ttl
    };
    
    try {
      localStorage.setItem(this.cachePrefix + key, JSON.stringify(item));
      return value;
    } catch (error) {
      console.error('Error saving to localStorage:', error);
      return value;
    }
  }
  
  /**
   * Get an item from localStorage
   * @param {string} key - Cache key
   * @returns {any|null} - Cached value or null if not found/expired
   */
  getStorageItem(key) {
    try {
      const item = localStorage.getItem(this.cachePrefix + key);
      
      if (!item) {
        return null;
      }
      
      const parsedItem = JSON.parse(item);
      
      // Check if the item has expired
      if (parsedItem.expiresAt < Date.now()) {
        localStorage.removeItem(this.cachePrefix + key);
        return null;
      }
      
      return parsedItem.value;
    } catch (error) {
      console.error('Error reading from localStorage:', error);
      return null;
    }
  }
  
  /**
   * Remove an item from localStorage
   * @param {string} key - Cache key
   */
  removeStorageItem(key) {
    try {
      localStorage.removeItem(this.cachePrefix + key);
    } catch (error) {
      console.error('Error removing from localStorage:', error);
    }
  }
  
  /**
   * Clear all cached items from localStorage that match our prefix
   */
  clearStorageCache() {
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        
        if (key.startsWith(this.cachePrefix)) {
          localStorage.removeItem(key);
        }
      }
    } catch (error) {
      console.error('Error clearing localStorage cache:', error);
    }
  }
  
  /**
   * Cache API response data
   * @param {string} endpoint - API endpoint
   * @param {any} data - Response data to cache
   * @param {number} ttl - Time to live in milliseconds (optional)
   * @param {boolean} persistent - Whether to store in localStorage (optional)
   */
  cacheApiResponse(endpoint, data, ttl = this.defaultTTL, persistent = false) {
    const cacheKey = `api_${endpoint}`;
    
    // Always cache in memory
    this.setMemoryItem(cacheKey, data, ttl);
    
    // Optionally cache in localStorage for persistence
    if (persistent) {
      this.setStorageItem(cacheKey, data, ttl);
    }
    
    return data;
  }
  
  /**
   * Get cached API response data
   * @param {string} endpoint - API endpoint
   * @param {boolean} checkStorage - Whether to check localStorage if not in memory
   * @returns {any|null} - Cached response data or null
   */
  getCachedApiResponse(endpoint, checkStorage = true) {
    const cacheKey = `api_${endpoint}`;
    
    // Check memory cache first
    let cachedData = this.getMemoryItem(cacheKey);
    
    // If not in memory and checkStorage is true, try localStorage
    if (!cachedData && checkStorage) {
      cachedData = this.getStorageItem(cacheKey);
      
      // If found in storage but not memory, add to memory for faster access next time
      if (cachedData) {
        this.setMemoryItem(cacheKey, cachedData);
      }
    }
    
    return cachedData;
  }
  
  /**
   * Invalidate cached API response
   * @param {string} endpoint - API endpoint
   * @param {boolean} removeFromStorage - Whether to also remove from localStorage
   */
  invalidateApiCache(endpoint, removeFromStorage = true) {
    const cacheKey = `api_${endpoint}`;
    
    this.removeMemoryItem(cacheKey);
    
    if (removeFromStorage) {
      this.removeStorageItem(cacheKey);
    }
  }
  
  /**
   * Invalidate all API cache entries that match a pattern
   * @param {string} pattern - Pattern to match against endpoints
   */
  invalidateApiCacheByPattern(pattern) {
    // Create a RegExp from the pattern
    const regex = new RegExp(pattern);
    
    // Check memory cache
    for (const [key, value] of this.memoryCache.entries()) {
      if (key.startsWith('api_') && regex.test(key.substring(4))) {
        this.memoryCache.delete(key);
      }
    }
    
    // Check localStorage
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        
        if (key.startsWith(this.cachePrefix + 'api_') && regex.test(key.substring(this.cachePrefix.length + 4))) {
          localStorage.removeItem(key);
        }
      }
    } catch (error) {
      console.error('Error invalidating pattern from localStorage:', error);
    }
  }
  
  /**
   * Initialize periodic cache cleanup to prevent memory leaks
   */
  initCacheCleanup() {
    // Run cleanup every 10 minutes
    setInterval(() => {
      this.cleanupExpiredCache();
    }, 10 * 60 * 1000);
  }
  
  /**
   * Clean up expired cache entries
   */
  cleanupExpiredCache() {
    const now = Date.now();
    
    // Clean memory cache
    for (const [key, item] of this.memoryCache.entries()) {
      if (item.expiresAt < now) {
        this.memoryCache.delete(key);
      }
    }
    
    // Clean localStorage cache
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        
        if (key.startsWith(this.cachePrefix)) {
          const item = localStorage.getItem(key);
          
          if (item) {
            try {
              const parsedItem = JSON.parse(item);
              
              if (parsedItem.expiresAt < now) {
                localStorage.removeItem(key);
              }
            } catch (e) {
              // If we can't parse it, remove it
              localStorage.removeItem(key);
            }
          }
        }
      }
    } catch (error) {
      console.error('Error cleaning up localStorage cache:', error);
    }
  }
}

// Create a singleton instance
const cacheService = new CacheService();

export default cacheService;
