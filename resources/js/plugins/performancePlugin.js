/**
 * Performance Plugin
 * 
 * A Vue plugin that integrates all performance optimization services:
 * - Caching service
 * - Optimized HTTP service
 * - Performance monitoring
 * - Image optimization
 * - Lazy loading utilities
 */

import cacheService from '../services/cacheService';
import optimizedHttp from '../services/optimizedHttpService';
import performanceMonitor, { PerformanceMonitorPlugin } from '../services/performanceMonitorService';

// Image optimization utilities
const optimizeImage = (url, options = {}) => {
  if (!url) return '';
  
  // Default options
  const defaults = {
    width: null,
    height: null,
    quality: 80,
    format: 'auto'
  };
  
  const settings = { ...defaults, ...options };
  
  // If it's already an optimized URL or SVG, return as is
  if (url.includes('data:image/') || url.endsWith('.svg')) {
    return url;
  }
  
  // If using a CDN that supports image optimization
  if (url.includes('cloudinary.com')) {
    // Construct Cloudinary transformation URL
    const transformations = [];
    
    if (settings.width) transformations.push(`w_${settings.width}`);
    if (settings.height) transformations.push(`h_${settings.height}`);
    if (settings.quality) transformations.push(`q_${settings.quality}`);
    if (settings.format !== 'auto') transformations.push(`f_${settings.format}`);
    
    // Insert transformations into URL
    return url.replace('/upload/', `/upload/${transformations.join(',')}/`);
  }
  
  // For local images, add query parameters
  const params = [];
  
  if (settings.width) params.push(`w=${settings.width}`);
  if (settings.height) params.push(`h=${settings.height}`);
  if (settings.quality) params.push(`q=${settings.quality}`);
  if (settings.format !== 'auto') params.push(`fm=${settings.format}`);
  
  const separator = url.includes('?') ? '&' : '?';
  return params.length ? `${url}${separator}${params.join('&')}` : url;
};

// Lazy loading utilities
const lazyLoadDirective = {
  mounted(el, binding) {
    function loadResource() {
      // Handle different element types
      if (el.nodeName.toLowerCase() === 'img') {
        // For images, swap data-src to src
        const src = el.getAttribute('data-src');
        if (src) {
          el.setAttribute('src', src);
          el.removeAttribute('data-src');
        }
      } else if (el.nodeName.toLowerCase() === 'video') {
        // For videos, load the source
        const src = el.getAttribute('data-src');
        if (src) {
          el.setAttribute('src', src);
          el.load();
          el.removeAttribute('data-src');
        }
      } else if (el.getAttribute('data-background')) {
        // For background images
        const src = el.getAttribute('data-background');
        if (src) {
          el.style.backgroundImage = `url(${src})`;
          el.removeAttribute('data-background');
        }
      }
      
      // Add loaded class for transitions
      el.classList.add('loaded');
      
      // Remove placeholder if exists
      const placeholder = el.querySelector('.placeholder');
      if (placeholder) {
        placeholder.style.opacity = '0';
        setTimeout(() => {
          placeholder.remove();
        }, 500);
      }
    }
    
    function handleIntersect(entries, observer) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          loadResource();
          observer.unobserve(el);
        }
      });
    }
    
    // Use Intersection Observer for lazy loading
    if ('IntersectionObserver' in window) {
      const options = {
        root: null,
        rootMargin: '50px',
        threshold: 0.1
      };
      
      const observer = new IntersectionObserver(handleIntersect, options);
      observer.observe(el);
    } else {
      // Fallback for browsers that don't support Intersection Observer
      loadResource();
    }
  }
};

// Resource hints for preloading/prefetching
const resourceHints = {
  /**
   * Preload a resource
   * @param {string} url - URL to preload
   * @param {string} as - Resource type (script, style, image, etc.)
   * @param {boolean} crossorigin - Whether to use crossorigin
   */
  preload(url, as = 'script', crossorigin = false) {
    if (!url || typeof document === 'undefined') return;
    
    const link = document.createElement('link');
    link.rel = 'preload';
    link.href = url;
    link.as = as;
    
    if (crossorigin) {
      link.crossOrigin = 'anonymous';
    }
    
    document.head.appendChild(link);
  },
  
  /**
   * Prefetch a resource
   * @param {string} url - URL to prefetch
   */
  prefetch(url) {
    if (!url || typeof document === 'undefined') return;
    
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    
    document.head.appendChild(link);
  },
  
  /**
   * Preconnect to a domain
   * @param {string} url - Domain to preconnect to
   * @param {boolean} crossorigin - Whether to use crossorigin
   */
  preconnect(url, crossorigin = true) {
    if (!url || typeof document === 'undefined') return;
    
    const link = document.createElement('link');
    link.rel = 'preconnect';
    link.href = url;
    
    if (crossorigin) {
      link.crossOrigin = 'anonymous';
    }
    
    document.head.appendChild(link);
  }
};

// Debounce utility for performance optimization
const debounce = (fn, delay) => {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
};

// Throttle utility for performance optimization
const throttle = (fn, limit) => {
  let inThrottle;
  return function(...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

// Main plugin
const PerformancePlugin = {
  install(app, options = {}) {
    // Register performance monitor
    app.use(PerformanceMonitorPlugin);
    
    // Register global properties
    app.config.globalProperties.$cache = cacheService;
    app.config.globalProperties.$http = optimizedHttp;
    app.config.globalProperties.$performance = performanceMonitor;
    
    // Register directives
    app.directive('lazy', lazyLoadDirective);
    
    // Register global methods
    app.config.globalProperties.$optimizeImage = optimizeImage;
    app.config.globalProperties.$preload = resourceHints.preload;
    app.config.globalProperties.$prefetch = resourceHints.prefetch;
    app.config.globalProperties.$preconnect = resourceHints.preconnect;
    app.config.globalProperties.$debounce = debounce;
    app.config.globalProperties.$throttle = throttle;
    
    // Setup preconnect to API domain
    if (typeof document !== 'undefined') {
      resourceHints.preconnect(window.location.origin);
      
      // Preconnect to common third-party domains if used
      if (options.preconnectDomains) {
        options.preconnectDomains.forEach(domain => {
          resourceHints.preconnect(domain);
        });
      }
    }
    
    // Initialize performance monitoring
    if (options.enablePerformanceMonitoring !== false) {
      // Start monitoring on app mount
      app.mixin({
        mounted() {
          if (this.$root === this) {
            console.log('Performance monitoring enabled');
          }
        }
      });
    }
    
    // Provide services to the app
    app.provide('cache', cacheService);
    app.provide('http', optimizedHttp);
    app.provide('performance', performanceMonitor);
  }
};

export {
  cacheService,
  optimizedHttp,
  performanceMonitor,
  optimizeImage,
  lazyLoadDirective,
  resourceHints,
  debounce,
  throttle
};

export default PerformancePlugin;
