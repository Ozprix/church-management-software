import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useEventsStore = defineStore('events', {
  state: () => ({
    events: useStorage('church-events', [
      {
        id: 'event-1',
        title: 'Sunday Service',
        description: 'Regular Sunday worship service',
        start: '2025-05-26T10:00:00',
        end: '2025-05-26T12:00:00',
        allDay: false,
        location: 'Main Sanctuary',
        category: 'worship',
        color: '#4F46E5', // Indigo
        recurring: {
          frequency: 'weekly',
          day: 0, // Sunday
          interval: 1
        },
        attendees: [],
        organizer: 'Pastor Johnson',
        resources: ['Projector', 'Sound System'],
        reminders: [
          { type: 'email', time: 60 }, // 60 minutes before
          { type: 'notification', time: 30 } // 30 minutes before
        ],
        isPublic: true
      },
      {
        id: 'event-2',
        title: 'Youth Group Meeting',
        description: 'Weekly youth group fellowship and Bible study',
        start: '2025-05-28T18:30:00',
        end: '2025-05-28T20:00:00',
        allDay: false,
        location: 'Youth Room',
        category: 'youth',
        color: '#10B981', // Green
        recurring: {
          frequency: 'weekly',
          day: 3, // Wednesday
          interval: 1
        },
        attendees: [],
        organizer: 'Youth Pastor Smith',
        resources: ['Projector', 'Snacks'],
        reminders: [
          { type: 'notification', time: 60 } // 60 minutes before
        ],
        isPublic: true
      },
      {
        id: 'event-3',
        title: 'Prayer Meeting',
        description: 'Weekly prayer gathering',
        start: '2025-05-30T19:00:00',
        end: '2025-05-30T20:00:00',
        allDay: false,
        location: 'Chapel',
        category: 'prayer',
        color: '#8B5CF6', // Purple
        recurring: {
          frequency: 'weekly',
          day: 5, // Friday
          interval: 1
        },
        attendees: [],
        organizer: 'Elder Williams',
        resources: [],
        reminders: [
          { type: 'notification', time: 30 } // 30 minutes before
        ],
        isPublic: true
      },
      {
        id: 'event-4',
        title: 'Church Picnic',
        description: 'Annual church picnic and fellowship',
        start: '2025-06-15T12:00:00',
        end: '2025-06-15T16:00:00',
        allDay: true,
        location: 'City Park',
        category: 'fellowship',
        color: '#F59E0B', // Amber
        recurring: null,
        attendees: [],
        organizer: 'Fellowship Committee',
        resources: ['Tables', 'Chairs', 'Grill'],
        reminders: [
          { type: 'email', time: 1440 }, // 1 day before
          { type: 'notification', time: 120 } // 2 hours before
        ],
        isPublic: true
      },
      {
        id: 'event-5',
        title: 'Choir Practice',
        description: 'Weekly choir rehearsal',
        start: '2025-05-27T19:00:00',
        end: '2025-05-27T20:30:00',
        allDay: false,
        location: 'Choir Room',
        category: 'music',
        color: '#EF4444', // Red
        recurring: {
          frequency: 'weekly',
          day: 2, // Tuesday
          interval: 1
        },
        attendees: [],
        organizer: 'Music Director',
        resources: ['Piano', 'Sheet Music'],
        reminders: [
          { type: 'notification', time: 60 } // 60 minutes before
        ],
        isPublic: true
      }
    ]),
    categories: useStorage('event-categories', [
      { id: 'worship', name: 'Worship Service', color: '#4F46E5' },
      { id: 'youth', name: 'Youth', color: '#10B981' },
      { id: 'prayer', name: 'Prayer', color: '#8B5CF6' },
      { id: 'fellowship', name: 'Fellowship', color: '#F59E0B' },
      { id: 'music', name: 'Music', color: '#EF4444' },
      { id: 'meeting', name: 'Meeting', color: '#0EA5E9' },
      { id: 'outreach', name: 'Outreach', color: '#EC4899' },
      { id: 'education', name: 'Education', color: '#14B8A6' }
    ]),
    resources: useStorage('event-resources', [
      { id: 'projector', name: 'Projector' },
      { id: 'sound-system', name: 'Sound System' },
      { id: 'chairs', name: 'Chairs' },
      { id: 'tables', name: 'Tables' },
      { id: 'piano', name: 'Piano' },
      { id: 'microphones', name: 'Microphones' },
      { id: 'snacks', name: 'Snacks' },
      { id: 'sheet-music', name: 'Sheet Music' },
      { id: 'grill', name: 'Grill' }
    ]),
    calendarView: useStorage('calendar-view', 'month'),
    selectedDate: useStorage('selected-date', new Date().toISOString()),
    eventModalOpen: false,
    selectedEvent: null,
    draggedEvent: null,
    filter: useStorage('event-filter', {
      categories: [],
      search: '',
      dateRange: {
        start: null,
        end: null
      }
    })
  }),
  
  getters: {
    getEventById: (state) => (id) => {
      return state.events.find(event => event.id === id);
    },
    
    getEventsByCategory: (state) => (category) => {
      return state.events.filter(event => event.category === category);
    },
    
    getEventsByDateRange: (state) => (start, end) => {
      const startDate = new Date(start);
      const endDate = new Date(end);
      
      return state.events.filter(event => {
        const eventStart = new Date(event.start);
        const eventEnd = new Date(event.end);
        
        // Check if the event falls within the date range
        return (eventStart >= startDate && eventStart <= endDate) || 
               (eventEnd >= startDate && eventEnd <= endDate) ||
               (eventStart <= startDate && eventEnd >= endDate);
      });
    },
    
    getUpcomingEvents: (state) => (count = 5) => {
      const now = new Date();
      
      // Filter events that haven't ended yet
      const upcomingEvents = state.events.filter(event => {
        const eventEnd = new Date(event.end);
        return eventEnd >= now;
      });
      
      // Sort by start date (ascending)
      upcomingEvents.sort((a, b) => {
        return new Date(a.start) - new Date(b.start);
      });
      
      // Return the specified number of events
      return upcomingEvents.slice(0, count);
    },
    
    getCategoryById: (state) => (id) => {
      return state.categories.find(category => category.id === id);
    },
    
    getResourceById: (state) => (id) => {
      return state.resources.find(resource => resource.id === id);
    },
    
    filteredEvents: (state) => {
      let filtered = [...state.events];
      
      // Filter by categories
      if (state.filter.categories.length > 0) {
        filtered = filtered.filter(event => state.filter.categories.includes(event.category));
      }
      
      // Filter by search term
      if (state.filter.search) {
        const searchTerm = state.filter.search.toLowerCase();
        filtered = filtered.filter(event => 
          event.title.toLowerCase().includes(searchTerm) || 
          event.description.toLowerCase().includes(searchTerm) ||
          event.location.toLowerCase().includes(searchTerm) ||
          event.organizer.toLowerCase().includes(searchTerm)
        );
      }
      
      // Filter by date range
      if (state.filter.dateRange.start && state.filter.dateRange.end) {
        const startDate = new Date(state.filter.dateRange.start);
        const endDate = new Date(state.filter.dateRange.end);
        
        filtered = filtered.filter(event => {
          const eventStart = new Date(event.start);
          const eventEnd = new Date(event.end);
          
          return (eventStart >= startDate && eventStart <= endDate) || 
                 (eventEnd >= startDate && eventEnd <= endDate) ||
                 (eventStart <= startDate && eventEnd >= endDate);
        });
      }
      
      return filtered;
    },
    
    // Get events formatted for FullCalendar
    calendarEvents: (state) => {
      return state.events.map(event => ({
        id: event.id,
        title: event.title,
        start: event.start,
        end: event.end,
        allDay: event.allDay,
        backgroundColor: event.color,
        borderColor: event.color,
        extendedProps: {
          description: event.description,
          location: event.location,
          category: event.category,
          organizer: event.organizer,
          isPublic: event.isPublic
        }
      }));
    }
  },
  
  actions: {
    addEvent(event) {
      // Generate a unique ID if not provided
      if (!event.id) {
        event.id = 'event-' + Date.now();
      }
      
      this.events.push(event);
      return event.id;
    },
    
    updateEvent(updatedEvent) {
      const index = this.events.findIndex(event => event.id === updatedEvent.id);
      
      if (index !== -1) {
        this.events[index] = { ...updatedEvent };
        return true;
      }
      
      return false;
    },
    
    deleteEvent(id) {
      const index = this.events.findIndex(event => event.id === id);
      
      if (index !== -1) {
        this.events.splice(index, 1);
        return true;
      }
      
      return false;
    },
    
    addCategory(category) {
      // Generate a unique ID if not provided
      if (!category.id) {
        category.id = category.name.toLowerCase().replace(/\\s+/g, '-');
      }
      
      this.categories.push(category);
      return category.id;
    },
    
    updateCategory(updatedCategory) {
      const index = this.categories.findIndex(category => category.id === updatedCategory.id);
      
      if (index !== -1) {
        this.categories[index] = { ...updatedCategory };
        return true;
      }
      
      return false;
    },
    
    deleteCategory(id) {
      const index = this.categories.findIndex(category => category.id === id);
      
      if (index !== -1) {
        // Check if any events are using this category
        const eventsWithCategory = this.events.filter(event => event.category === id);
        
        if (eventsWithCategory.length > 0) {
          return false; // Cannot delete category in use
        }
        
        this.categories.splice(index, 1);
        return true;
      }
      
      return false;
    },
    
    addResource(resource) {
      // Generate a unique ID if not provided
      if (!resource.id) {
        resource.id = resource.name.toLowerCase().replace(/\\s+/g, '-');
      }
      
      this.resources.push(resource);
      return resource.id;
    },
    
    updateResource(updatedResource) {
      const index = this.resources.findIndex(resource => resource.id === updatedResource.id);
      
      if (index !== -1) {
        this.resources[index] = { ...updatedResource };
        return true;
      }
      
      return false;
    },
    
    deleteResource(id) {
      const index = this.resources.findIndex(resource => resource.id === id);
      
      if (index !== -1) {
        // Check if any events are using this resource
        const eventsWithResource = this.events.filter(event => 
          event.resources && event.resources.includes(id)
        );
        
        if (eventsWithResource.length > 0) {
          return false; // Cannot delete resource in use
        }
        
        this.resources.splice(index, 1);
        return true;
      }
      
      return false;
    },
    
    setCalendarView(view) {
      this.calendarView = view;
    },
    
    setSelectedDate(date) {
      this.selectedDate = date;
    },
    
    openEventModal(event = null) {
      this.selectedEvent = event;
      this.eventModalOpen = true;
    },
    
    closeEventModal() {
      this.selectedEvent = null;
      this.eventModalOpen = false;
    },
    
    setDraggedEvent(event) {
      this.draggedEvent = event;
    },
    
    clearDraggedEvent() {
      this.draggedEvent = null;
    },
    
    updateFilter(filter) {
      this.filter = { ...this.filter, ...filter };
    },
    
    resetFilter() {
      this.filter = {
        categories: [],
        search: '',
        dateRange: {
          start: null,
          end: null
        }
      };
    },
    
    // Generate recurring events based on a pattern
    generateRecurringEvents(baseEvent, startDate, endDate) {
      if (!baseEvent.recurring) return [baseEvent];
      
      const events = [];
      const start = new Date(startDate);
      const end = new Date(endDate);
      const eventStart = new Date(baseEvent.start);
      const eventEnd = new Date(baseEvent.end);
      const duration = eventEnd - eventStart;
      
      let current = new Date(eventStart);
      
      while (current <= end) {
        if (current >= start) {
          const newEvent = { ...baseEvent };
          newEvent.id = `${baseEvent.id}-${current.getTime()}`;
          newEvent.start = new Date(current).toISOString();
          newEvent.end = new Date(current.getTime() + duration).toISOString();
          
          events.push(newEvent);
        }
        
        // Move to the next occurrence based on frequency
        switch (baseEvent.recurring.frequency) {
          case 'daily':
            current.setDate(current.getDate() + baseEvent.recurring.interval);
            break;
            
          case 'weekly':
            current.setDate(current.getDate() + (7 * baseEvent.recurring.interval));
            break;
            
          case 'monthly':
            current.setMonth(current.getMonth() + baseEvent.recurring.interval);
            break;
            
          case 'yearly':
            current.setFullYear(current.getFullYear() + baseEvent.recurring.interval);
            break;
        }
      }
      
      return events;
    }
  }
});
