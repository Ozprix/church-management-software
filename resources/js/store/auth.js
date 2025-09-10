// resources/js/store/auth.js

import authService from '../services/auth';

export default {
  namespaced: true,
  
  state: {
    user: null,
    loading: false,
    error: null
  },
  
  getters: {
    isAuthenticated: state => !!state.user,
    user: state => state.user,
    loading: state => state.loading,
    error: state => state.error,
    hasPermission: state => permission => {
      if (!state.user || !state.user.permissions) return false;
      return state.user.permissions.includes(permission);
    },
    currentUser: state => state.user
  },
  
  mutations: {
    SET_USER(state, user) {
      state.user = user;
    },
    SET_LOADING(state, loading) {
      state.loading = loading;
    },
    SET_ERROR(state, error) {
      state.error = error;
    }
  },
  
  actions: {
    async login({ commit }, credentials) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      
      try {
        const response = await authService.login(credentials);
        authService.setToken(response.data.token);
        commit('SET_USER', response.data.user);
        return response;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'An error occurred during login');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    async register({ commit }, user) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      
      try {
        const response = await authService.register(user);
        authService.setToken(response.data.token);
        commit('SET_USER', response.data.user);
        return response;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'An error occurred during registration');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    async logout({ commit }) {
      commit('SET_LOADING', true);
      
      try {
        await authService.logout();
        authService.removeToken();
        commit('SET_USER', null);
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    async fetchUser({ commit }) {
      if (!authService.isAuthenticated()) {
        commit('SET_USER', null);
        return;
      }
      
      commit('SET_LOADING', true);
      
      try {
        const response = await authService.getUser();
        commit('SET_USER', response.data.user);
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch user');
        authService.removeToken();
        commit('SET_USER', null);
      } finally {
        commit('SET_LOADING', false);
      }
    }
  }
};