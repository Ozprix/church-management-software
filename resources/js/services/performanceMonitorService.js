/**
 * Performance Monitor Service
 * 
 * A service for monitoring and reporting application performance metrics.
 * Tracks page load times, component render times, API response times,
 * and other performance-related metrics.
 */

class PerformanceMonitorService {
  constructor() {
    this.metrics = {
      pageLoads: {},
      apiCalls: {},
      componentRenders: {},
      resourceLoads: {},
      interactions: {}
    };
    
    this.isEnabled = process.env.NODE_ENV !== 'test';
    this.debugMode = process.env.NODE_ENV === 'development';
    this.sampleRate = 0.1; // Only track 10% of events in production to reduce overhead
    
    // Initialize performance observers if supported
    this.initPerformanceObservers();
  }
  
  /**
   * Initialize performance observers for automatic metric collection
   */
  initPerformanceObservers() {
    if (!this.isEnabled || typeof window === 'undefined' || !window.PerformanceObserver) {
      return;
    }
    
    try {
      // Observe page load metrics
      const pageObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (entry.entryType === 'navigation') {
            this.recordPageLoad(window.location.pathname, {
              dnsTime: entry.domainLookupEnd - entry.domainLookupStart,
              connectTime: entry.connectEnd - entry.connectStart,
              ttfb: entry.responseStart - entry.requestStart,
              domLoad: entry.domComplete - entry.domLoading,
              fullLoad: entry.loadEventEnd - entry.startTime
            });
          }
        }
      });
      
      pageObserver.observe({ entryTypes: ['navigation'] });
      
      // Observe resource load metrics
      const resourceObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (entry.initiatorType === 'fetch' || entry.initiatorType === 'xmlhttprequest') {
            this.recordApiCall(this.extractApiEndpoint(entry.name), entry.duration);
          } else {
            this.recordResourceLoad(entry.initiatorType, entry.name, entry.duration);
          }
        }
      });
      
      resourceObserver.observe({ entryTypes: ['resource'] });
      
      // Observe long tasks (potential UI blocking)
      if (window.PerformanceObserver.supportedEntryTypes.includes('longtask')) {
        const longTaskObserver = new PerformanceObserver((list) => {
          for (const entry of list.getEntries()) {
            this.recordLongTask(entry.duration, entry.attribution[0]?.name || 'unknown');
          }
        });
        
        longTaskObserver.observe({ entryTypes: ['longtask'] });
      }
      
      // Store observers for cleanup
      this.observers = {
        page: pageObserver,
        resource: resourceObserver,
        longTask: longTaskObserver
      };
    } catch (error) {
      console.error('Error initializing performance observers:', error);
    }
  }
  
  /**
   * Extract API endpoint from a URL
   * @param {string} url - Full URL
   * @returns {string} - Simplified endpoint path
   */
  extractApiEndpoint(url) {
    try {
      const urlObj = new URL(url);
      let path = urlObj.pathname;
      
      // Extract API endpoint from path
      if (path.includes('/api/')) {
        path = path.substring(path.indexOf('/api/') + 4);
      }
      
      // Remove IDs for better grouping
      return path.replace(/\/\d+(\/?$|\/)/g, '/{id}/');
    } catch (error) {
      return url;
    }
  }
  
  /**
   * Determine if we should sample this event based on sample rate
   * @returns {boolean} - Whether to record this event
   */
  shouldSample() {
    if (this.debugMode) return true;
    return Math.random() < this.sampleRate;
  }
  
  /**
   * Record page load performance
   * @param {string} page - Page path
   * @param {Object} metrics - Performance metrics
   */
  recordPageLoad(page, metrics) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    if (!this.metrics.pageLoads[page]) {
      this.metrics.pageLoads[page] = {
        samples: [],
        count: 0
      };
    }
    
    this.metrics.pageLoads[page].samples.push({
      timestamp: Date.now(),
      ...metrics
    });
    
    this.metrics.pageLoads[page].count++;
    
    // Keep only the last 10 samples to avoid memory issues
    if (this.metrics.pageLoads[page].samples.length > 10) {
      this.metrics.pageLoads[page].samples.shift();
    }
    
    if (this.debugMode) {
      console.log(`Page Load (${page}):`, metrics);
    }
  }
  
  /**
   * Record API call performance
   * @param {string} endpoint - API endpoint
   * @param {number} duration - Call duration in ms
   */
  recordApiCall(endpoint, duration) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    if (!this.metrics.apiCalls[endpoint]) {
      this.metrics.apiCalls[endpoint] = {
        samples: [],
        count: 0,
        total: 0,
        min: Infinity,
        max: 0,
        avg: 0
      };
    }
    
    const entry = this.metrics.apiCalls[endpoint];
    
    entry.samples.push({
      timestamp: Date.now(),
      duration
    });
    
    entry.count++;
    entry.total += duration;
    entry.min = Math.min(entry.min, duration);
    entry.max = Math.max(entry.max, duration);
    entry.avg = entry.total / entry.count;
    
    // Keep only the last 10 samples
    if (entry.samples.length > 10) {
      const removed = entry.samples.shift();
      entry.total -= removed.duration;
      entry.avg = entry.total / entry.samples.length;
    }
    
    // Log slow API calls
    if (this.debugMode && duration > 1000) {
      console.warn(`Slow API call to ${endpoint}: ${duration.toFixed(2)}ms`);
    }
  }
  
  /**
   * Record component render time
   * @param {string} componentName - Name of the component
   * @param {number} duration - Render duration in ms
   */
  recordComponentRender(componentName, duration) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    if (!this.metrics.componentRenders[componentName]) {
      this.metrics.componentRenders[componentName] = {
        samples: [],
        count: 0,
        total: 0,
        avg: 0
      };
    }
    
    const entry = this.metrics.componentRenders[componentName];
    
    entry.samples.push({
      timestamp: Date.now(),
      duration
    });
    
    entry.count++;
    entry.total += duration;
    entry.avg = entry.total / entry.count;
    
    // Keep only the last 5 samples
    if (entry.samples.length > 5) {
      const removed = entry.samples.shift();
      entry.total -= removed.duration;
      entry.avg = entry.total / entry.samples.length;
    }
    
    // Log slow component renders
    if (this.debugMode && duration > 50) {
      console.warn(`Slow component render for ${componentName}: ${duration.toFixed(2)}ms`);
    }
  }
  
  /**
   * Record resource load performance
   * @param {string} type - Resource type (script, css, img, etc.)
   * @param {string} url - Resource URL
   * @param {number} duration - Load duration in ms
   */
  recordResourceLoad(type, url, duration) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    // Simplify URL to avoid storing too many entries
    const simplifiedUrl = url.split('?')[0];
    const key = `${type}:${simplifiedUrl}`;
    
    if (!this.metrics.resourceLoads[key]) {
      this.metrics.resourceLoads[key] = {
        type,
        url: simplifiedUrl,
        count: 0,
        total: 0,
        avg: 0
      };
    }
    
    const entry = this.metrics.resourceLoads[key];
    
    entry.count++;
    entry.total += duration;
    entry.avg = entry.total / entry.count;
    
    // Log slow resource loads
    if (this.debugMode && duration > 1000) {
      console.warn(`Slow resource load (${type}): ${simplifiedUrl} - ${duration.toFixed(2)}ms`);
    }
  }
  
  /**
   * Record user interaction metrics
   * @param {string} action - User action (click, submit, etc.)
   * @param {string} element - Element interacted with
   * @param {number} responseTime - Time to respond in ms
   */
  recordInteraction(action, element, responseTime) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    const key = `${action}:${element}`;
    
    if (!this.metrics.interactions[key]) {
      this.metrics.interactions[key] = {
        samples: [],
        count: 0,
        total: 0,
        avg: 0
      };
    }
    
    const entry = this.metrics.interactions[key];
    
    entry.samples.push({
      timestamp: Date.now(),
      duration: responseTime
    });
    
    entry.count++;
    entry.total += responseTime;
    entry.avg = entry.total / entry.count;
    
    // Keep only the last 5 samples
    if (entry.samples.length > 5) {
      const removed = entry.samples.shift();
      entry.total -= removed.duration;
      entry.avg = entry.total / entry.samples.length;
    }
    
    // Log slow interactions
    if (this.debugMode && responseTime > 100) {
      console.warn(`Slow interaction ${action} on ${element}: ${responseTime.toFixed(2)}ms`);
    }
  }
  
  /**
   * Record long task that might block the UI
   * @param {number} duration - Task duration in ms
   * @param {string} source - Source of the long task
   */
  recordLongTask(duration, source) {
    if (!this.isEnabled || !this.shouldSample()) return;
    
    if (!this.metrics.longTasks) {
      this.metrics.longTasks = [];
    }
    
    this.metrics.longTasks.push({
      timestamp: Date.now(),
      duration,
      source
    });
    
    // Keep only the last 10 long tasks
    if (this.metrics.longTasks.length > 10) {
      this.metrics.longTasks.shift();
    }
    
    if (this.debugMode) {
      console.warn(`Long task detected (${duration.toFixed(2)}ms) from ${source}`);
    }
  }
  
  /**
   * Get performance report with aggregated metrics
   * @returns {Object} - Performance report
   */
  getPerformanceReport() {
    if (!this.isEnabled) return null;
    
    // Calculate summary metrics
    const apiCallSummary = Object.keys(this.metrics.apiCalls).map(endpoint => ({
      endpoint,
      avgDuration: this.metrics.apiCalls[endpoint].avg.toFixed(2),
      callCount: this.metrics.apiCalls[endpoint].count,
      maxDuration: this.metrics.apiCalls[endpoint].max.toFixed(2)
    })).sort((a, b) => b.avgDuration - a.avgDuration);
    
    const slowComponents = Object.keys(this.metrics.componentRenders)
      .filter(component => this.metrics.componentRenders[component].avg > 20)
      .map(component => ({
        component,
        avgRenderTime: this.metrics.componentRenders[component].avg.toFixed(2),
        renderCount: this.metrics.componentRenders[component].count
      }))
      .sort((a, b) => b.avgRenderTime - a.avgRenderTime);
    
    const pageLoadSummary = Object.keys(this.metrics.pageLoads).map(page => {
      const samples = this.metrics.pageLoads[page].samples;
      if (samples.length === 0) return null;
      
      // Calculate averages from samples
      const avgTtfb = samples.reduce((sum, s) => sum + s.ttfb, 0) / samples.length;
      const avgDomLoad = samples.reduce((sum, s) => sum + s.domLoad, 0) / samples.length;
      const avgFullLoad = samples.reduce((sum, s) => sum + s.fullLoad, 0) / samples.length;
      
      return {
        page,
        avgTtfb: avgTtfb.toFixed(2),
        avgDomLoad: avgDomLoad.toFixed(2),
        avgFullLoad: avgFullLoad.toFixed(2),
        loadCount: this.metrics.pageLoads[page].count
      };
    }).filter(Boolean).sort((a, b) => b.avgFullLoad - a.avgFullLoad);
    
    return {
      timestamp: Date.now(),
      summary: {
        apiCalls: apiCallSummary.slice(0, 5), // Top 5 slowest API calls
        slowComponents: slowComponents.slice(0, 5), // Top 5 slowest components
        pageLoads: pageLoadSummary,
        longTasks: this.metrics.longTasks || []
      },
      details: this.metrics
    };
  }
  
  /**
   * Reset all collected metrics
   */
  resetMetrics() {
    this.metrics = {
      pageLoads: {},
      apiCalls: {},
      componentRenders: {},
      resourceLoads: {},
      interactions: {}
    };
  }
  
  /**
   * Create a performance tracking wrapper for Vue components
   * @param {Object} options - Options for the wrapper
   * @returns {Function} - HOC wrapper function
   */
  createComponentTracker(options = {}) {
    const self = this;
    
    return function(component) {
      const originalMounted = component.mounted;
      const originalUpdated = component.updated;
      const componentName = options.name || component.name || 'UnnamedComponent';
      
      // Track mount time
      component.mounted = function() {
        const startTime = performance.now();
        
        if (originalMounted) {
          originalMounted.call(this);
        }
        
        const duration = performance.now() - startTime;
        self.recordComponentRender(`${componentName}:mount`, duration);
      };
      
      // Track update time
      component.updated = function() {
        const startTime = performance.now();
        
        if (originalUpdated) {
          originalUpdated.call(this);
        }
        
        const duration = performance.now() - startTime;
        self.recordComponentRender(`${componentName}:update`, duration);
      };
      
      return component;
    };
  }
  
  /**
   * Clean up observers and resources
   */
  cleanup() {
    if (this.observers) {
      Object.values(this.observers).forEach(observer => {
        if (observer && typeof observer.disconnect === 'function') {
          observer.disconnect();
        }
      });
    }
  }
}

// Create a singleton instance
const performanceMonitor = new PerformanceMonitorService();

// Add a Vue plugin interface
export const PerformanceMonitorPlugin = {
  install(app) {
    app.config.globalProperties.$performance = performanceMonitor;
    
    // Add a directive for tracking interaction performance
    app.directive('track-interaction', {
      mounted(el, binding) {
        const action = binding.arg || 'click';
        const element = binding.value || el.id || el.className || 'unknown';
        
        el.addEventListener(action, () => {
          const startTime = performance.now();
          
          // Use requestAnimationFrame to measure response time
          requestAnimationFrame(() => {
            const duration = performance.now() - startTime;
            performanceMonitor.recordInteraction(action, element, duration);
          });
        });
      }
    });
    
    // Add a component performance tracking mixin
    app.mixin({
      beforeCreate() {
        // Only track components that opt-in with the trackPerformance option
        if (this.$options.trackPerformance) {
          const componentName = this.$options.name || 'UnnamedComponent';
          
          this.$once('hook:mounted', () => {
            performanceMonitor.recordComponentRender(`${componentName}:initial-render`, this.$options.renderTime || 0);
          });
          
          // Wrap the render function to measure performance
          if (this.$options.render) {
            const originalRender = this.$options.render;
            this.$options.render = function() {
              const start = performance.now();
              const vnode = originalRender.apply(this, arguments);
              this.$options.renderTime = performance.now() - start;
              return vnode;
            };
          }
        }
      }
    });
  }
};

export default performanceMonitor;
