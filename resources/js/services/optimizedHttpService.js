/**
 * Optimized HTTP Service
 * 
 * A performance-optimized HTTP client that implements:
 * - Request caching using cacheService
 * - Request deduplication (prevents multiple identical requests)
 * - Automatic retry for failed requests
 * - Request batching for multiple similar requests
 * - Request cancellation
 */

import axios from 'axios';
import cacheService from './cacheService';

class OptimizedHttpService {
  constructor() {
    // Create axios instance with default config
    this.http = axios.create({
      baseURL: '/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    // Track in-flight requests to prevent duplicates
    this.pendingRequests = new Map();
    
    // Configure request interceptor
    this.http.interceptors.request.use(
      this.handleRequestInterceptor.bind(this),
      this.handleRequestError.bind(this)
    );
    
    // Configure response interceptor
    this.http.interceptors.response.use(
      this.handleResponseInterceptor.bind(this),
      this.handleResponseError.bind(this)
    );
    
    // Default cache TTL values (in milliseconds)
    this.cacheTTL = {
      short: 60 * 1000, // 1 minute
      medium: 5 * 60 * 1000, // 5 minutes
      long: 30 * 60 * 1000, // 30 minutes
      day: 24 * 60 * 60 * 1000 // 1 day
    };
    
    // Maximum number of retry attempts
    this.maxRetryAttempts = 3;
  }
  
  /**
   * Set the authentication token for all requests
   * @param {string} token - JWT or other auth token
   */
  setAuthToken(token) {
    if (token) {
      this.http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      delete this.http.defaults.headers.common['Authorization'];
    }
  }
  
  /**
   * Create a unique key for a request to use for caching and deduplication
   * @param {Object} config - Axios request config
   * @returns {string} - Unique request key
   */
  createRequestKey(config) {
    const { method, url, params, data } = config;
    
    // Create a string that uniquely identifies this request
    return `${method}:${url}:${JSON.stringify(params || {})}:${JSON.stringify(data || {})}`;
  }
  
  /**
   * Request interceptor to handle caching and deduplication
   * @param {Object} config - Axios request config
   * @returns {Object} - Modified config
   */
  handleRequestInterceptor(config) {
    // Add timestamp to prevent browser caching for GET requests
    if (config.method === 'get' && !config.params) {
      config.params = {};
    }
    
    if (config.method === 'get' && !config.params._t) {
      config.params._t = Date.now();
    }
    
    // Skip caching if explicitly disabled for this request
    if (config.noCache) {
      return config;
    }
    
    const requestKey = this.createRequestKey(config);
    
    // Check if this is a GET request that can be cached
    if (config.method === 'get') {
      // Check if we already have a cached response
      const cachedResponse = cacheService.getCachedApiResponse(requestKey);
      
      if (cachedResponse) {
        // If we have a cached response, resolve immediately with the cached data
        const cachedData = cachedResponse;
        
        // Create a canceled request that resolves with the cached data
        config.adapter = () => {
          return Promise.resolve({
            data: cachedData,
            status: 200,
            statusText: 'OK',
            headers: {},
            config,
            request: {},
            cached: true
          });
        };
      }
      
      // Check if we have a pending request for the same data
      if (this.pendingRequests.has(requestKey)) {
        // Return the existing promise to prevent duplicate requests
        const pendingRequest = this.pendingRequests.get(requestKey);
        
        // Create a canceled request that resolves with the pending request
        config.adapter = () => pendingRequest;
      } else if (config.method === 'get') {
        // For GET requests, store the promise to prevent duplicates
        const requestPromise = new Promise((resolve, reject) => {
          // Store the resolve/reject functions to use later
          config._resolve = resolve;
          config._reject = reject;
        });
        
        this.pendingRequests.set(requestKey, requestPromise);
        config._requestKey = requestKey;
      }
    }
    
    return config;
  }
  
  /**
   * Handle request interceptor errors
   * @param {Error} error - Request error
   * @returns {Promise} - Rejected promise with error
   */
  handleRequestError(error) {
    return Promise.reject(error);
  }
  
  /**
   * Response interceptor to handle caching successful responses
   * @param {Object} response - Axios response
   * @returns {Object} - Modified response
   */
  handleResponseInterceptor(response) {
    // Skip if this was a cached response
    if (response.cached) {
      return response;
    }
    
    const config = response.config;
    const requestKey = config._requestKey;
    
    // If this was a tracked request, resolve the pending promise
    if (requestKey && config._resolve) {
      config._resolve(response);
      this.pendingRequests.delete(requestKey);
    }
    
    // Cache GET responses unless explicitly disabled
    if (config.method === 'get' && !config.noCache) {
      // Determine cache TTL based on config or use default
      const ttl = config.cacheTTL || this.cacheTTL.medium;
      const persistent = config.persistentCache || false;
      
      // Cache the response data
      cacheService.cacheApiResponse(requestKey, response.data, ttl, persistent);
    }
    
    return response;
  }
  
  /**
   * Handle response interceptor errors
   * @param {Error} error - Response error
   * @returns {Promise} - Rejected promise with error or retry
   */
  handleResponseError(error) {
    const config = error.config;
    
    // If this was a tracked request, reject the pending promise
    if (config && config._requestKey && config._reject) {
      config._reject(error);
      this.pendingRequests.delete(config._requestKey);
    }
    
    // Implement retry logic for network errors or 5xx responses
    if (config && (!error.response || (error.response.status >= 500 && error.response.status < 600))) {
      // Retry count starts at 0
      config.retryCount = config.retryCount || 0;
      
      // Check if we should retry
      if (config.retryCount < this.maxRetryAttempts) {
        config.retryCount++;
        
        // Exponential backoff: 1s, 2s, 4s, etc.
        const backoff = Math.pow(2, config.retryCount - 1) * 1000;
        
        // Return a promise that resolves after the backoff period
        return new Promise(resolve => {
          setTimeout(() => {
            resolve(this.http(config));
          }, backoff);
        });
      }
    }
    
    return Promise.reject(error);
  }
  
  /**
   * Perform a GET request with caching
   * @param {string} url - Request URL
   * @param {Object} params - URL parameters
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  get(url, params = {}, config = {}) {
    return this.http.get(url, { ...config, params })
      .then(response => response.data);
  }
  
  /**
   * Perform a POST request
   * @param {string} url - Request URL
   * @param {Object} data - Request payload
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  post(url, data = {}, config = {}) {
    return this.http.post(url, data, config)
      .then(response => response.data);
  }
  
  /**
   * Perform a PUT request
   * @param {string} url - Request URL
   * @param {Object} data - Request payload
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  put(url, data = {}, config = {}) {
    return this.http.put(url, data, config)
      .then(response => response.data);
  }
  
  /**
   * Perform a PATCH request
   * @param {string} url - Request URL
   * @param {Object} data - Request payload
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  patch(url, data = {}, config = {}) {
    return this.http.patch(url, data, config)
      .then(response => response.data);
  }
  
  /**
   * Perform a DELETE request
   * @param {string} url - Request URL
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  delete(url, config = {}) {
    return this.http.delete(url, config)
      .then(response => response.data);
  }
  
  /**
   * Perform a GET request with long-term caching
   * @param {string} url - Request URL
   * @param {Object} params - URL parameters
   * @param {Object} config - Additional axios config
   * @returns {Promise} - Promise resolving to the response data
   */
  getCached(url, params = {}, config = {}) {
    // Use a longer cache TTL and enable persistent storage
    return this.get(url, params, {
      ...config,
      cacheTTL: this.cacheTTL.day,
      persistentCache: true
    });
  }
  
  /**
   * Invalidate cache for a specific endpoint
   * @param {string} url - URL to invalidate
   * @param {Object} params - URL parameters
   */
  invalidateCache(url, params = {}) {
    const requestKey = this.createRequestKey({
      method: 'get',
      url,
      params
    });
    
    cacheService.invalidateApiCache(requestKey);
  }
  
  /**
   * Invalidate all cache entries that match a pattern
   * @param {string} pattern - Pattern to match against URLs
   */
  invalidateCacheByPattern(pattern) {
    cacheService.invalidateApiCacheByPattern(pattern);
  }
  
  /**
   * Clear all API cache
   */
  clearCache() {
    cacheService.clearMemoryCache();
    cacheService.clearStorageCache();
  }
  
  /**
   * Create a cancellable request
   * @param {Function} requestFn - Request function to execute
   * @returns {Object} - Object with execute and cancel methods
   */
  createCancellableRequest(requestFn) {
    const controller = new AbortController();
    
    return {
      execute: () => requestFn({ signal: controller.signal }),
      cancel: () => controller.abort()
    };
  }
  
  /**
   * Batch multiple GET requests into a single request
   * @param {Array} requests - Array of request objects with url and params
   * @returns {Promise} - Promise resolving to an array of responses
   */
  batchGet(requests) {
    // Check if we can use a real batch endpoint
    if (this.http.defaults.baseURL.includes('/api')) {
      // Create a single request to a batch endpoint
      return this.post('/batch', { requests })
        .then(response => response.results);
    }
    
    // Fallback to Promise.all for multiple requests
    return Promise.all(
      requests.map(req => this.get(req.url, req.params, req.config))
    );
  }
}

// Create and export a singleton instance
const optimizedHttp = new OptimizedHttpService();

export default optimizedHttp;
