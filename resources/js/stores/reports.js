import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useReportsStore = defineStore('reports', {
  state: () => ({
    savedReports: useStorage('saved-reports', []),
    reportTemplates: useStorage('report-templates', [
      {
        id: 'membership-summary',
        name: 'Membership Summary',
        category: 'members',
        description: 'Overview of church membership statistics',
        icon: 'users',
        fields: [
          { name: 'totalMembers', label: 'Total Members', type: 'number' },
          { name: 'newMembers', label: 'New Members (Last 30 Days)', type: 'number' },
          { name: 'activeMembers', label: 'Active Members', type: 'number' },
          { name: 'inactiveMembers', label: 'Inactive Members', type: 'number' },
          { name: 'membersByGender', label: 'Members by Gender', type: 'chart', chartType: 'pie' },
          { name: 'membersByAge', label: 'Members by Age Group', type: 'chart', chartType: 'bar' },
          { name: 'membershipTrend', label: 'Membership Trend', type: 'chart', chartType: 'line' }
        ]
      },
      {
        id: 'financial-summary',
        name: 'Financial Summary',
        category: 'finance',
        description: 'Overview of church financial statistics',
        icon: 'chart-pie',
        fields: [
          { name: 'totalIncome', label: 'Total Income', type: 'currency' },
          { name: 'totalExpenses', label: 'Total Expenses', type: 'currency' },
          { name: 'netIncome', label: 'Net Income', type: 'currency' },
          { name: 'incomeByCategory', label: 'Income by Category', type: 'chart', chartType: 'pie' },
          { name: 'expensesByCategory', label: 'Expenses by Category', type: 'chart', chartType: 'pie' },
          { name: 'financialTrend', label: 'Financial Trend', type: 'chart', chartType: 'line' }
        ]
      },
      {
        id: 'attendance-report',
        name: 'Attendance Report',
        category: 'events',
        description: 'Overview of service and event attendance',
        icon: 'calendar',
        fields: [
          { name: 'averageAttendance', label: 'Average Attendance', type: 'number' },
          { name: 'attendanceByService', label: 'Attendance by Service', type: 'chart', chartType: 'bar' },
          { name: 'attendanceTrend', label: 'Attendance Trend', type: 'chart', chartType: 'line' }
        ]
      },
      {
        id: 'groups-report',
        name: 'Groups Report',
        category: 'groups',
        description: 'Overview of church groups and activities',
        icon: 'users-group',
        fields: [
          { name: 'totalGroups', label: 'Total Groups', type: 'number' },
          { name: 'activeGroups', label: 'Active Groups', type: 'number' },
          { name: 'membersByGroup', label: 'Members by Group', type: 'chart', chartType: 'bar' },
          { name: 'groupActivities', label: 'Group Activities', type: 'table' }
        ]
      },
      {
        id: 'donations-report',
        name: 'Donations Report',
        category: 'finance',
        description: 'Overview of donations and pledges',
        icon: 'gift',
        fields: [
          { name: 'totalDonations', label: 'Total Donations', type: 'currency' },
          { name: 'donationsByPurpose', label: 'Donations by Purpose', type: 'chart', chartType: 'pie' },
          { name: 'topDonors', label: 'Top Donors', type: 'table' },
          { name: 'donationTrend', label: 'Donation Trend', type: 'chart', chartType: 'line' }
        ]
      }
    ]),
    currentReport: null,
    reportData: null,
    isGenerating: false,
    dateRange: {
      start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0], // First day of current month
      end: new Date().toISOString().split('T')[0] // Today
    },
    exportFormats: [
      { id: 'pdf', name: 'PDF', icon: 'file-pdf' },
      { id: 'excel', name: 'Excel', icon: 'file-excel' },
      { id: 'csv', name: 'CSV', icon: 'file-csv' }
    ]
  }),
  
  getters: {
    getReportById: (state) => (id) => {
      return state.reportTemplates.find(report => report.id === id);
    },
    
    getSavedReportById: (state) => (id) => {
      return state.savedReports.find(report => report.id === id);
    },
    
    getReportsByCategory: (state) => (category) => {
      return state.reportTemplates.filter(report => report.category === category);
    },
    
    getAllCategories: (state) => {
      const categories = state.reportTemplates.map(report => report.category);
      return [...new Set(categories)];
    }
  },
  
  actions: {
    setCurrentReport(reportId) {
      this.currentReport = this.getReportById(reportId);
      this.reportData = null;
    },
    
    async generateReport(reportId, options = {}) {
      this.isGenerating = true;
      
      try {
        // In a real application, this would make an API call to the backend
        // For now, we'll simulate the report generation with mock data
        const report = this.getReportById(reportId);
        
        if (!report) {
          throw new Error(`Report template with ID ${reportId} not found`);
        }
        
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Generate mock data based on report fields
        const mockData = {};
        
        for (const field of report.fields) {
          mockData[field.name] = this.generateMockDataForField(field, options);
        }
        
        this.reportData = {
          id: `${reportId}-${Date.now()}`,
          templateId: reportId,
          name: report.name,
          category: report.category,
          generatedAt: new Date().toISOString(),
          dateRange: {
            start: options.dateRange?.start || this.dateRange.start,
            end: options.dateRange?.end || this.dateRange.end
          },
          data: mockData
        };
        
        return this.reportData;
      } catch (error) {
        console.error('Error generating report:', error);
        throw error;
      } finally {
        this.isGenerating = false;
      }
    },
    
    generateMockDataForField(field, options) {
      switch (field.type) {
        case 'number':
          return Math.floor(Math.random() * 1000);
          
        case 'currency':
          return Math.floor(Math.random() * 10000) / 100;
          
        case 'chart':
          return this.generateMockChartData(field, options);
          
        case 'table':
          return this.generateMockTableData(field, options);
          
        default:
          return 'Mock data';
      }
    },
    
    generateMockChartData(field, options) {
      switch (field.chartType) {
        case 'pie':
          return {
            labels: ['Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5'],
            datasets: [{
              data: [
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100)
              ],
              backgroundColor: [
                '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
              ]
            }]
          };
          
        case 'bar':
          return {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
              label: field.label,
              data: [
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100)
              ],
              backgroundColor: '#4F46E5'
            }]
          };
          
        case 'line':
          return {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
              label: field.label,
              data: [
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100)
              ],
              borderColor: '#4F46E5',
              tension: 0.1
            }]
          };
          
        default:
          return {
            labels: ['Category 1', 'Category 2', 'Category 3'],
            datasets: [{
              data: [
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100),
                Math.floor(Math.random() * 100)
              ]
            }]
          };
      }
    },
    
    generateMockTableData(field, options) {
      // Generate different mock table data based on field name
      switch (field.name) {
        case 'topDonors':
          return [
            { name: 'John Smith', amount: '$1,200.00', date: '2025-05-15' },
            { name: 'Jane Doe', amount: '$950.00', date: '2025-05-12' },
            { name: 'Robert Johnson', amount: '$750.00', date: '2025-05-10' },
            { name: 'Emily Wilson', amount: '$500.00', date: '2025-05-08' },
            { name: 'Michael Brown', amount: '$350.00', date: '2025-05-05' }
          ];
          
        case 'groupActivities':
          return [
            { group: 'Youth Group', activity: 'Weekly Meeting', participants: 25, date: '2025-05-20' },
            { group: 'Choir', activity: 'Practice Session', participants: 15, date: '2025-05-18' },
            { group: 'Women\'s Ministry', activity: 'Bible Study', participants: 12, date: '2025-05-16' },
            { group: 'Men\'s Fellowship', activity: 'Breakfast Meeting', participants: 18, date: '2025-05-15' },
            { group: 'Children\'s Church', activity: 'Sunday School', participants: 30, date: '2025-05-12' }
          ];
          
        default:
          return [
            { id: 1, name: 'Item 1', value: Math.floor(Math.random() * 100) },
            { id: 2, name: 'Item 2', value: Math.floor(Math.random() * 100) },
            { id: 3, name: 'Item 3', value: Math.floor(Math.random() * 100) },
            { id: 4, name: 'Item 4', value: Math.floor(Math.random() * 100) },
            { id: 5, name: 'Item 5', value: Math.floor(Math.random() * 100) }
          ];
      }
    },
    
    saveReport(report) {
      const savedReport = {
        id: `saved-${Date.now()}`,
        originalId: report.id,
        templateId: report.templateId,
        name: report.name,
        category: report.category,
        generatedAt: report.generatedAt,
        savedAt: new Date().toISOString(),
        dateRange: report.dateRange,
        data: report.data
      };
      
      this.savedReports.unshift(savedReport);
      return savedReport;
    },
    
    deleteSavedReport(reportId) {
      const index = this.savedReports.findIndex(report => report.id === reportId);
      if (index !== -1) {
        this.savedReports.splice(index, 1);
        return true;
      }
      return false;
    },
    
    exportReport(report, format) {
      // In a real application, this would generate and download the report in the specified format
      // For now, we'll just log a message
      console.log(`Exporting report "${report.name}" in ${format} format`);
      
      // Simulate download delay
      return new Promise(resolve => {
        setTimeout(() => {
          resolve({
            success: true,
            message: `Report "${report.name}" has been exported as ${format.toUpperCase()}`
          });
        }, 1500);
      });
    },
    
    setDateRange(start, end) {
      this.dateRange.start = start;
      this.dateRange.end = end;
    }
  }
});
