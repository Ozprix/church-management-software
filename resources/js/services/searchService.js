/**
 * Advanced Search Service
 * 
 * A service for performing advanced searches across all church data.
 * Provides utilities for:
 * - Full-text search across multiple data types
 * - Filtering and sorting search results
 * - Caching search results for better performance
 * - Offline search capabilities
 */

import { ref, computed, reactive } from 'vue';
import { useOffline } from './offlineService';
import { useCacheService } from './cacheService';
import { useOptimizedHttp } from './optimizedHttpService';
import { useFuzzyMatch } from './fuzzyMatchService';

// Search types
export const SEARCH_TYPES = {
  ALL: 'all',
  MEMBERS: 'members',
  DONATIONS: 'donations',
  EVENTS: 'events',
  ATTENDANCE: 'attendance',
  GROUPS: 'groups',
  SERMONS: 'sermons',
  RESOURCES: 'resources'
};

/**
 * Create a composable function for advanced search
 * @returns {Object} - Search utilities
 */
export function useSearch() {
  // Get required services
  const offline = useOffline();
  const cache = useCacheService();
  const http = useOptimizedHttp();
  const fuzzyMatch = useFuzzyMatch();
  
  // Search state
  const searchQuery = ref('');
  const searchType = ref(SEARCH_TYPES.ALL);
  const searchResults = ref([]);
  const isSearching = ref(false);
  const searchError = ref(null);
  const searchFilters = reactive({});
  const searchSort = ref({ field: 'relevance', direction: 'desc' });
  const totalResults = ref(0);
  const currentPage = ref(1);
  const resultsPerPage = ref(20);
  
  // Recent searches (stored in localStorage)
  const recentSearches = ref(
    JSON.parse(localStorage.getItem('recentSearches') || '[]')
  );
  
  // Computed properties
  const hasResults = computed(() => searchResults.value.length > 0);
  const totalPages = computed(() => Math.ceil(totalResults.value / resultsPerPage.value));
  const hasMoreResults = computed(() => currentPage.value < totalPages.value);
  
  /**
   * Perform a search
   * @param {Object} options - Search options
   * @returns {Promise<Array>} - Search results
   */
  const search = async (options = {}) => {
    // Merge options with current state
    const query = options.query || searchQuery.value;
    const type = options.type || searchType.value;
    const filters = options.filters || searchFilters;
    const sort = options.sort || searchSort.value;
    const page = options.page || currentPage.value;
    const perPage = options.perPage || resultsPerPage.value;
    
    // Update state
    isSearching.value = true;
    searchError.value = null;
    
    try {
      // Check cache first
      const cacheKey = generateCacheKey(searchQuery.value, searchType.value, filters, searchSort.value, currentPage.value);
      const cachedResults = cache.get(cacheKey);
      
      if (cachedResults) {
        searchResults.value = cachedResults.results;
        totalResults.value = cachedResults.total;
        isSearching.value = false;
        
        // Add to recent searches
        addToRecentSearches(searchQuery.value, searchType.value);
        
        return searchResults.value;
      }
      
      // If offline, perform client-side search
      if (offline.isOffline.value) {
        return performOfflineSearch(filters);
      }
      
      // Prepare search parameters
      const searchParams = {
        query: searchQuery.value,
        type: searchType.value,
        filters,
        sort: searchSort.value,
        page: currentPage.value,
        perPage: resultsPerPage.value
      };
      
      // Add fuzzy matching parameters if enabled
      if (filters.fuzzyMatch) {
        searchParams.fuzzyMatch = true;
        searchParams.fuzzyThreshold = filters.fuzzyThreshold || 0.6;
      }
      
      // Perform API search
      const response = await http.post('/api/search', searchParams);
      
      // Process results with highlighting if fuzzy matching is enabled
      let processedResults = response.data.results;
      
      if (filters.fuzzyMatch && searchQuery.value.trim()) {
        processedResults = processedResults.map(result => {
          // Add highlighted content for display fields
          const highlightFields = ['title', 'name', 'description', 'content'];
          
          highlightFields.forEach(field => {
            if (result[field]) {
              result[`${field}_highlighted`] = fuzzyMatch.highlightMatches(
                result[field],
                searchQuery.value,
                'search-highlight'
              );
            }
          });
          
          return result;
        });
      }
      
      // Update state with results
      searchResults.value = processedResults;
      totalResults.value = response.data.total;
      
      // Cache results
      cache.set(cacheKey, {
        results: processedResults,
        total: response.data.total
      }, 60 * 5); // Cache for 5 minutes
      
      // Add to recent searches
      addToRecentSearches(searchQuery.value, searchType.value);
      
      return searchResults.value;
    } catch (error) {
      console.error('Search error:', error);
      searchError.value = error.message || 'An error occurred during search';
      return [];
    } finally {
      isSearching.value = false;
    }
  };
  
  /**
   * Perform an offline search
   * @param {Object} filters - Search filters
   * @returns {Promise<Array>} - Search results
   */
  const performOfflineSearch = async (filters) => {
    try {
      // Normalize query for searching
      const normalizedQuery = searchQuery.value.toLowerCase().trim();
      
      // Get offline data based on type
      let offlineData = [];
      
      if (searchType.value === SEARCH_TYPES.ALL || searchType.value === SEARCH_TYPES.MEMBERS) {
        const members = await offline.getOfflineData('members-cache') || [];
        offlineData = [...offlineData, ...members.map(item => ({
          ...item,
          type: 'member'
        }))];
      }
      
      if (searchType.value === SEARCH_TYPES.ALL || searchType.value === SEARCH_TYPES.DONATIONS) {
        const donations = await offline.getOfflineData('donations-cache') || [];
        offlineData = [...offlineData, ...donations.map(item => ({
          ...item,
          type: 'donation'
        }))];
      }
      
      if (searchType.value === SEARCH_TYPES.ALL || searchType.value === SEARCH_TYPES.EVENTS) {
        const events = await offline.getOfflineData('events-cache') || [];
        offlineData = [...offlineData, ...events.map(item => ({
          ...item,
          type: 'event'
        }))];
      }
      
      if (searchType.value === SEARCH_TYPES.ALL || searchType.value === SEARCH_TYPES.ATTENDANCE) {
        const attendance = await offline.getOfflineData('attendance-cache') || [];
        offlineData = [...offlineData, ...attendance.map(item => ({
          ...item,
          type: 'attendance'
        }))];
      }
      
      // Filter data by query
      let results = offlineData.filter(item => {
        // Search in all string fields
        return Object.values(item).some(value => {
          if (typeof value === 'string') {
            return value.toLowerCase().includes(normalizedQuery);
          }
          return false;
        });
      });
      
      // Apply filters
      if (filters && Object.keys(filters).length > 0) {
        results = results.filter(item => {
          return Object.entries(filters).every(([key, value]) => {
            if (Array.isArray(value)) {
              return value.includes(item[key]);
            }
            return item[key] === value;
          });
        });
      }
      
      // Apply sorting
      if (searchSort.value && searchSort.value.field) {
        results.sort((a, b) => {
          if (searchSort.value.field === 'relevance') {
            // For relevance, we count the number of matches
            const countMatches = (item) => {
              let count = 0;
              Object.values(item).forEach(value => {
                if (typeof value === 'string') {
                  const matches = value.toLowerCase().match(new RegExp(normalizedQuery, 'g'));
                  if (matches) {
                    count += matches.length;
                  }
                }
              });
              return count;
            };
            
            const matchesA = countMatches(a);
            const matchesB = countMatches(b);
            
            return searchSort.value.direction === 'desc' ? matchesB - matchesA : matchesA - matchesB;
          }
          
          const valueA = a[searchSort.value.field];
          const valueB = b[searchSort.value.field];
          
          if (typeof valueA === 'string' && typeof valueB === 'string') {
            return searchSort.value.direction === 'desc' ? 
              valueB.localeCompare(valueA) : 
              valueA.localeCompare(valueB);
          }
          
          return searchSort.value.direction === 'desc' ? 
            valueB - valueA : 
            valueA - valueB;
        });
      }
      
      // Apply pagination
      const startIndex = (currentPage.value - 1) * resultsPerPage.value;
      const paginatedResults = results.slice(startIndex, startIndex + resultsPerPage.value);
      
      // Update state
      searchResults.value = paginatedResults;
      totalResults.value = results.length;
      
      // Add to recent searches
      addToRecentSearches(searchQuery.value, searchType.value);
      
      return paginatedResults;
    } catch (error) {
      console.error('Offline search error:', error);
      searchError.value = 'An error occurred while performing offline search';
      return [];
    }
  };
  
  /**
   * Generate a cache key for search results
   * @param {string} query - Search query
   * @param {string} type - Search type
   * @param {Object} filters - Search filters
   * @param {Object} sort - Sort options
   * @param {number} page - Page number
   * @param {number} perPage - Results per page
   * @returns {string} - Cache key
   */
  const generateCacheKey = (query, type, filters, sort, page, perPage) => {
    return `search:${query}:${type}:${JSON.stringify(filters)}:${sort.field}:${sort.direction}:${page}:${perPage}`;
  };
  
  /**
   * Add search to recent searches
   * @param {string} query - Search query
   * @param {string} type - Search type
   */
  const addToRecentSearches = (query, type) => {
    if (!query.trim()) return;
    
    // Create search item
    const searchItem = {
      query,
      type,
      timestamp: new Date().toISOString(),
      filters: { ...searchFilters } // Save filters for reuse
    };
    
    // Add to recent searches (avoid duplicates)
    const existingIndex = recentSearches.value.findIndex(item => 
      item.query === query && item.type === type
    );
    
    if (existingIndex !== -1) {
      // Update timestamp and filters of existing item
      recentSearches.value[existingIndex].timestamp = searchItem.timestamp;
      recentSearches.value[existingIndex].filters = searchItem.filters;
    } else {
      // Add new item
      recentSearches.value.unshift(searchItem);
      
      // Limit to 10 items
      if (recentSearches.value.length > 10) {
        recentSearches.value.pop();
      }
    }
    
    // Save to localStorage
    try {
      localStorage.setItem('recentSearches', JSON.stringify(recentSearches.value));
    } catch (error) {
      console.error('Error saving recent searches:', error);
    }
  };
  
  /**
   * Clear recent searches
   */
  const clearRecentSearches = () => {
    recentSearches.value = [];
    localStorage.removeItem('recentSearches');
  };
  
  /**
   * Load more results (next page)
   * @returns {Promise<Array>} - Additional search results
   */
  const loadMoreResults = async () => {
    if (!hasMoreResults.value) return [];
    
    return await search({ page: currentPage.value + 1 });
  };
  
  /**
   * Reset search state
   */
  const resetSearch = () => {
    searchQuery.value = '';
    searchType.value = SEARCH_TYPES.ALL;
    searchResults.value = [];
    totalResults.value = 0;
    currentPage.value = 1;
    Object.keys(searchFilters).forEach(key => delete searchFilters[key]);
    searchSort.value = { field: 'relevance', direction: 'desc' };
    searchError.value = null;
  };
  
  return {
    // State
    searchQuery,
    searchType,
    searchResults,
    isSearching,
    searchError,
    searchFilters,
    searchSort,
    totalResults,
    currentPage,
    resultsPerPage,
    recentSearches,
    
    // Computed
    hasResults,
    totalPages,
    hasMoreResults,
    
    // Methods
    search,
    loadMoreResults,
    resetSearch,
    clearRecentSearches,
    
    // Constants
    SEARCH_TYPES
  };
}

// Create a Vue plugin
export const SearchPlugin = {
  install(app) {
    const search = useSearch();
    
    // Add to global properties
    app.config.globalProperties.$search = search;
    
    // Provide to components
    app.provide('search', search);
  }
};

// Export singleton instance for direct import
export default useSearch;
