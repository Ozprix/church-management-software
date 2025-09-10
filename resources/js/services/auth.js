// resources/js/services/auth.js

import axios from 'axios';

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found');
}

// Configure axios
axios.defaults.withCredentials = true;
axios.defaults.baseURL = '';

// Add auth token from localStorage if it exists
const authToken = localStorage.getItem('token');
if (authToken) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

export default {
  /**
   * Login the user
   * @param {Object} credentials
   * @returns {Promise}
   */
  login(credentials) {
    return axios.post('/api/auth/login', credentials);
  },

  /**
   * Register a new user
   * @param {Object} user
   * @returns {Promise}
   */
  register(user) {
    return axios.post('/api/auth/register', user);
  },

  /**
   * Logout the user
   * @returns {Promise}
   */
  logout() {
    return axios.post('/api/auth/logout');
  },

  /**
   * Get the authenticated user
   * @returns {Promise}
   */
  getUser() {
    return axios.get('/api/auth/user');
  },

  /**
   * Check if the user is authenticated
   * @returns {boolean}
   */
  isAuthenticated() {
    return !!localStorage.getItem('token');
  },

  /**
   * Set the authentication token
   * @param {string} token
   */
  setToken(token) {
    localStorage.setItem('token', token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  },

  /**
   * Remove the authentication token
   */
  removeToken() {
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
  }
};