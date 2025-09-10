import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.user && !!state.token,
    userInitials: (state) => {
      if (!state.user) return '';
      
      const names = state.user.name.split(' ');
      if (names.length === 1) return names[0].charAt(0).toUpperCase();
      
      return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
    },
    userRole: (state) => state.user?.role || 'user',
    isAdmin: (state) => state.user?.role === 'admin',
  },
  
  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/auth/login', credentials);
        
        if (response.data.token) {
          this.token = response.data.token;
          localStorage.setItem('token', response.data.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          await this.fetchUser();
          return true;
        }
        
        return false;
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed. Please try again.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUser() {
      if (!this.token) return false;
      
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get('/api/auth/user');
        this.user = response.data;
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user data.';
        
        // If unauthorized, clear auth state
        if (error.response?.status === 401) {
          this.logout();
        }
        
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async logout() {
      this.loading = true;
      
      try {
        if (this.token) {
          await axios.post('/api/auth/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.error = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        this.loading = false;
      }
    },
    
    async register(userData) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/auth/register', userData);
        
        if (response.data.token) {
          this.token = response.data.token;
          localStorage.setItem('token', response.data.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          await this.fetchUser();
          return true;
        }
        
        return false;
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed. Please try again.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async updateProfile(profileData) {
      if (!this.user) return false;
      
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.put('/api/auth/profile', profileData);
        this.user = response.data;
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update profile.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async changePassword(passwordData) {
      if (!this.user) return false;
      
      this.loading = true;
      this.error = null;
      
      try {
        await axios.put('/api/auth/password', passwordData);
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to change password.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async requestPasswordReset(email) {
      this.loading = true;
      this.error = null;
      
      try {
        await axios.post('/api/auth/password/email', { email });
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send password reset email.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async resetPassword(resetData) {
      this.loading = true;
      this.error = null;
      
      try {
        await axios.post('/api/auth/password/reset', resetData);
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reset password.';
        return false;
      } finally {
        this.loading = false;
      }
    },
  },
  
  persist: {
    key: 'church-mgmt-auth',
    storage: localStorage,
    paths: ['token'],
  },
});
