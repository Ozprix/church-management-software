// resources/js/store/reports.js

import axios from 'axios';

const state = {
  reports: [],
  report: null,
  loading: false,
  error: null,
  reportTypes: [],
  metrics: null,
  donationsChart: [],
  attendanceChart: [],
  favoriteReports: [],
  recentReports: [],
  exportStatus: null,
  templates: [],
  currentTemplate: null
};

const getters = {
  getAllReports: state => state.reports,
  getCurrentReport: state => state.report,
  isLoading: state => state.loading,
  getError: state => state.error,
  getReportTypes: state => state.reportTypes,
  getMetrics: state => state.metrics,
  getDonationsChart: state => state.donationsChart,
  getAttendanceChart: state => state.attendanceChart,
  getFavoriteReports: state => state.favoriteReports,
  getRecentReports: state => state.recentReports,
  getExportStatus: state => state.exportStatus,
  getAllTemplates: state => state.templates,
  getCurrentTemplate: state => state.currentTemplate
};

const actions = {
  async fetchReports({ commit }, params = {}) {
    commit('SET_LOADING', true);
    try {
      const response = await axios.get('/reports', { params });
      commit('SET_REPORTS', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch reports');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchReport({ commit }, id) {
    commit('SET_LOADING', true);
    try {
      const response = await axios.get(`/reports/${id}`);
      commit('SET_REPORT', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async createReport({ commit }, reportData) {
    commit('SET_LOADING', true);
    try {
      const response = await axios.post('/reports', reportData);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to create report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async updateReport({ commit }, { id, data }) {
    commit('SET_LOADING', true);
    try {
      const response = await axios.put(`/reports/${id}`, data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to update report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async deleteReport({ commit }, id) {
    commit('SET_LOADING', true);
    try {
      const response = await axios.delete(`/reports/${id}`);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to delete report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async toggleFavorite({ commit }, id) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.post(`/reports/${id}/toggle-favorite`);
      // Update the report in state if it exists
      if (state.report && state.report.id === id) {
        commit('UPDATE_REPORT_FAVORITE_STATUS', {
          id,
          is_favorite: !state.report.is_favorite
        });
      }
      // Refresh the list of favorite reports
      this.dispatch('reports/fetchFavoriteReports');
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to toggle favorite');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async generateReport({ commit }, { id, format }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    commit('SET_EXPORT_STATUS', 'processing');
    try {
      const response = await axios.get(`/reports/${id}/generate`, {
        params: { format },
        responseType: format === 'pdf' || format === 'excel' ? 'blob' : 'json'
      });
      
      if (format === 'pdf' || format === 'excel') {
        // Create a download link for the file
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `report-${id}.${format === 'pdf' ? 'pdf' : 'xlsx'}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        commit('SET_EXPORT_STATUS', 'completed');
        return { status: 'success', message: `Report downloaded as ${format}` };
      }
      
      commit('SET_EXPORT_STATUS', 'completed');
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to generate report');
      commit('SET_EXPORT_STATUS', 'failed');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchReportTypes({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports/types');
      commit('SET_REPORT_TYPES', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch report types');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchFavoriteReports({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports', { params: { favorite: true } });
      commit('SET_FAVORITE_REPORTS', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch favorite reports');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchRecentReports({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports', { params: { limit: 5, sort: 'created_at', order: 'desc' } });
      commit('SET_RECENT_REPORTS', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch recent reports');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchMetrics({ commit }, params = {}) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports/metrics', { params });
      commit('SET_METRICS', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch metrics');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchDonationsChart({ commit }, params = {}) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports/charts/donations', { params });
      commit('SET_DONATIONS_CHART', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch donations chart');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchAttendanceChart({ commit }, params = {}) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/reports/charts/attendance', { params });
      commit('SET_ATTENDANCE_CHART', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch attendance chart');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  // Schedule a report to be generated and sent via email
  async scheduleReport({ commit }, { id, schedule }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.post(`/reports/${id}/schedule`, schedule);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to schedule report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  // Template management
  async fetchTemplates({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get('/report-templates');
      commit('SET_TEMPLATES', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch report templates');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async fetchTemplate({ commit }, id) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.get(`/report-templates/${id}`);
      commit('SET_CURRENT_TEMPLATE', response.data.data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch report template');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async createTemplate({ commit }, templateData) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.post('/report-templates', templateData);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to create report template');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async updateTemplate({ commit }, { id, data }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.put(`/report-templates/${id}`, data);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to update report template');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async deleteTemplate({ commit }, id) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.delete(`/report-templates/${id}`);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to delete report template');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
  
  async applyTemplate({ commit }, { reportId, templateId }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const response = await axios.post(`/reports/${reportId}/apply-template/${templateId}`);
      return response.data;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to apply template to report');
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  }
};

const mutations = {
  SET_LOADING(state, loading) {
    state.loading = loading;
  },
  SET_ERROR(state, error) {
    state.error = error;
  },
  SET_REPORTS(state, reports) {
    state.reports = reports;
  },
  SET_REPORT(state, report) {
    state.report = report;
  },
  SET_REPORT_TYPES(state, types) {
    state.reportTypes = types;
  },
  SET_METRICS(state, metrics) {
    state.metrics = metrics;
  },
  SET_DONATIONS_CHART(state, data) {
    state.donationsChart = data;
  },
  SET_ATTENDANCE_CHART(state, data) {
    state.attendanceChart = data;
  },
  SET_FAVORITE_REPORTS(state, reports) {
    state.favoriteReports = reports;
  },
  SET_RECENT_REPORTS(state, reports) {
    state.recentReports = reports;
  },
  SET_EXPORT_STATUS(state, status) {
    state.exportStatus = status;
  },
  SET_TEMPLATES(state, templates) {
    state.templates = templates;
  },
  SET_CURRENT_TEMPLATE(state, template) {
    state.currentTemplate = template;
  },
  UPDATE_REPORT_FAVORITE_STATUS(state, { id, is_favorite }) {
    if (state.report && state.report.id === id) {
      state.report.is_favorite = is_favorite;
    }
    
    // Update in reports list if exists
    const reportIndex = state.reports.findIndex(r => r.id === id);
    if (reportIndex !== -1) {
      state.reports[reportIndex].is_favorite = is_favorite;
    }
    
    // Update in favorite reports if exists
    const favoriteIndex = state.favoriteReports.findIndex(r => r.id === id);
    if (favoriteIndex !== -1 && !is_favorite) {
      state.favoriteReports.splice(favoriteIndex, 1);
    }
  }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
