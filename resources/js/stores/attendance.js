import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    records: useStorage('church-attendance-records', [
      {
        id: 'att-1',
        eventId: 'event-1', // Sunday Service
        date: '2025-05-19T10:00:00',
        totalCount: 145,
        membersPresent: [
          { id: 'member-1', name: 'John Smith', checkInTime: '2025-05-19T09:45:00' },
          { id: 'member-2', name: 'Mary Johnson', checkInTime: '2025-05-19T09:50:00' },
          { id: 'member-3', name: 'Robert Williams', checkInTime: '2025-05-19T09:55:00' }
        ],
        guestCount: 12,
        notes: 'Mother\'s Day service',
        categories: {
          adults: 78,
          youth: 35,
          children: 32
        },
        recordedBy: 'Admin User'
      },
      {
        id: 'att-2',
        eventId: 'event-1', // Sunday Service
        date: '2025-05-12T10:00:00',
        totalCount: 132,
        membersPresent: [
          { id: 'member-1', name: 'John Smith', checkInTime: '2025-05-12T09:48:00' },
          { id: 'member-2', name: 'Mary Johnson', checkInTime: '2025-05-12T09:52:00' }
        ],
        guestCount: 8,
        notes: 'Regular Sunday service',
        categories: {
          adults: 72,
          youth: 30,
          children: 30
        },
        recordedBy: 'Admin User'
      },
      {
        id: 'att-3',
        eventId: 'event-2', // Youth Group Meeting
        date: '2025-05-14T18:30:00',
        totalCount: 28,
        membersPresent: [
          { id: 'member-3', name: 'Robert Williams', checkInTime: '2025-05-14T18:25:00' }
        ],
        guestCount: 3,
        notes: 'Youth Bible study',
        categories: {
          youth: 28
        },
        recordedBy: 'Youth Pastor'
      }
    ]),
    trends: useStorage('church-attendance-trends', {
      weekly: [
        { week: '2025-05-05', count: 128 },
        { week: '2025-05-12', count: 132 },
        { week: '2025-05-19', count: 145 }
      ],
      monthly: [
        { month: '2025-02', count: 520 },
        { month: '2025-03', count: 540 },
        { month: '2025-04', count: 535 },
        { month: '2025-05', count: 550 }
      ],
      yearly: [
        { year: '2023', count: 5840 },
        { year: '2024', count: 6120 },
        { year: '2025', count: 2245 }
      ]
    }),
    settings: useStorage('church-attendance-settings', {
      autoCheckIn: true,
      checkInMethods: ['manual', 'qrcode', 'kiosk'],
      defaultCategories: ['adults', 'youth', 'children', 'first-time'],
      reminderEnabled: true,
      reminderTime: 15, // minutes before event
      trackFirstTimeVisitors: true,
      trackRegularAttendance: true
    }),
    currentSession: {
      eventId: null,
      isActive: false,
      startTime: null,
      checkedInCount: 0
    }
  }),
  
  getters: {
    getRecordById: (state) => (id) => {
      return state.records.find(record => record.id === id);
    },
    
    getRecordsByEventId: (state) => (eventId) => {
      return state.records.filter(record => record.eventId === eventId);
    },
    
    getRecordsByDateRange: (state) => (startDate, endDate) => {
      const start = new Date(startDate);
      const end = new Date(endDate);
      
      return state.records.filter(record => {
        const recordDate = new Date(record.date);
        return recordDate >= start && recordDate <= end;
      });
    },
    
    getLatestRecords: (state) => (count = 5) => {
      // Create a copy of the records array to avoid mutating the state
      const sortedRecords = [...state.records];
      
      // Sort by date (most recent first)
      sortedRecords.sort((a, b) => {
        return new Date(b.date) - new Date(a.date);
      });
      
      // Return the specified number of records
      return sortedRecords.slice(0, count);
    },
    
    getMemberAttendance: (state) => (memberId, startDate, endDate) => {
      const start = startDate ? new Date(startDate) : new Date(0); // Beginning of time if not specified
      const end = endDate ? new Date(endDate) : new Date(); // Current time if not specified
      
      return state.records.filter(record => {
        const recordDate = new Date(record.date);
        if (recordDate < start || recordDate > end) return false;
        
        return record.membersPresent.some(member => member.id === memberId);
      });
    },
    
    getAttendanceTrends: (state) => (period = 'weekly') => {
      return state.trends[period] || [];
    },
    
    getAttendanceRate: (state) => (memberId, totalEvents) => {
      if (!totalEvents || totalEvents <= 0) return 0;
      
      const attendedEvents = state.records.filter(record => {
        return record.membersPresent.some(member => member.id === memberId);
      }).length;
      
      return (attendedEvents / totalEvents) * 100;
    }
  },
  
  actions: {
    addRecord(record) {
      // Generate a unique ID if not provided
      if (!record.id) {
        record.id = 'att-' + Date.now();
      }
      
      // Set default values for optional fields
      record.guestCount = record.guestCount || 0;
      record.notes = record.notes || '';
      record.categories = record.categories || {};
      
      // Calculate total count if not provided
      if (!record.totalCount) {
        record.totalCount = record.membersPresent.length + record.guestCount;
      }
      
      this.records.push(record);
      return record.id;
    },
    
    updateRecord(updatedRecord) {
      const index = this.records.findIndex(record => record.id === updatedRecord.id);
      
      if (index !== -1) {
        // Recalculate total count
        if (!updatedRecord.totalCount) {
          updatedRecord.totalCount = updatedRecord.membersPresent.length + updatedRecord.guestCount;
        }
        
        this.records[index] = { ...updatedRecord };
        return true;
      }
      
      return false;
    },
    
    deleteRecord(id) {
      const index = this.records.findIndex(record => record.id === id);
      
      if (index !== -1) {
        this.records.splice(index, 1);
        return true;
      }
      
      return false;
    },
    
    startCheckInSession(eventId) {
      if (this.currentSession.isActive) {
        return false; // Another session is already active
      }
      
      this.currentSession = {
        eventId,
        isActive: true,
        startTime: new Date().toISOString(),
        checkedInCount: 0
      };
      
      return true;
    },
    
    endCheckInSession() {
      if (!this.currentSession.isActive) {
        return false; // No active session
      }
      
      this.currentSession = {
        eventId: null,
        isActive: false,
        startTime: null,
        checkedInCount: 0
      };
      
      return true;
    },
    
    checkInMember(eventId, member) {
      // Find the record for this event
      const recordIndex = this.records.findIndex(record => 
        record.eventId === eventId && 
        new Date(record.date).toDateString() === new Date().toDateString()
      );
      
      // If no record exists for today's event, create one
      if (recordIndex === -1) {
        const newRecord = {
          id: 'att-' + Date.now(),
          eventId,
          date: new Date().toISOString(),
          totalCount: 1,
          membersPresent: [{
            id: member.id,
            name: member.name,
            checkInTime: new Date().toISOString()
          }],
          guestCount: 0,
          notes: '',
          categories: {},
          recordedBy: 'System'
        };
        
        this.records.push(newRecord);
        
        if (this.currentSession.isActive && this.currentSession.eventId === eventId) {
          this.currentSession.checkedInCount++;
        }
        
        return true;
      }
      
      // Check if member is already checked in
      const memberIndex = this.records[recordIndex].membersPresent.findIndex(m => m.id === member.id);
      
      if (memberIndex === -1) {
        // Add member to the record
        this.records[recordIndex].membersPresent.push({
          id: member.id,
          name: member.name,
          checkInTime: new Date().toISOString()
        });
        
        // Update total count
        this.records[recordIndex].totalCount++;
        
        if (this.currentSession.isActive && this.currentSession.eventId === eventId) {
          this.currentSession.checkedInCount++;
        }
        
        return true;
      }
      
      return false; // Member already checked in
    },
    
    checkInGuest(eventId, count = 1) {
      // Find the record for this event
      const recordIndex = this.records.findIndex(record => 
        record.eventId === eventId && 
        new Date(record.date).toDateString() === new Date().toDateString()
      );
      
      // If no record exists for today's event, create one
      if (recordIndex === -1) {
        const newRecord = {
          id: 'att-' + Date.now(),
          eventId,
          date: new Date().toISOString(),
          totalCount: count,
          membersPresent: [],
          guestCount: count,
          notes: '',
          categories: {},
          recordedBy: 'System'
        };
        
        this.records.push(newRecord);
        
        if (this.currentSession.isActive && this.currentSession.eventId === eventId) {
          this.currentSession.checkedInCount += count;
        }
        
        return true;
      }
      
      // Update guest count and total count
      this.records[recordIndex].guestCount += count;
      this.records[recordIndex].totalCount += count;
      
      if (this.currentSession.isActive && this.currentSession.eventId === eventId) {
        this.currentSession.checkedInCount += count;
      }
      
      return true;
    },
    
    updateAttendanceTrends() {
      // This would typically be called on a schedule or when viewing reports
      // For demo purposes, we're just using the static data defined in state
      
      // In a real implementation, this would calculate trends based on actual attendance records
      // Example implementation:
      
      // Weekly trends (last 10 weeks)
      const weeklyTrends = [];
      const now = new Date();
      
      for (let i = 9; i >= 0; i--) {
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - (i * 7));
        weekStart.setHours(0, 0, 0, 0);
        
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        weekEnd.setHours(23, 59, 59, 999);
        
        const weekRecords = this.getRecordsByDateRange(weekStart, weekEnd);
        const weekTotal = weekRecords.reduce((sum, record) => sum + record.totalCount, 0);
        
        weeklyTrends.push({
          week: weekStart.toISOString().substring(0, 10),
          count: weekTotal
        });
      }
      
      this.trends.weekly = weeklyTrends;
      
      // Similar implementations would be done for monthly and yearly trends
    },
    
    updateSettings(newSettings) {
      this.settings = { ...this.settings, ...newSettings };
      return true;
    }
  }
});
