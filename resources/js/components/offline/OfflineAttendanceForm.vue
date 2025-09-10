<template>
  <div class="offline-attendance-form">
    <div class="form-header">
      <h2>{{ isEditing ? 'Edit Attendance Record' : 'Add Attendance Record' }}</h2>
      <div class="offline-indicator" v-if="isOfflineMode">
        <i class="fas fa-wifi-slash"></i>
        <span>Offline Mode</span>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="attendance-form">
      <div class="form-group">
        <label for="event_name">Event Name *</label>
        <input 
          type="text" 
          id="event_name" 
          v-model="form.event_name" 
          required
          :class="{ 'error': errors.event_name }"
        >
        <div class="error-message" v-if="errors.event_name">{{ errors.event_name }}</div>
      </div>

      <div class="form-group">
        <label for="event_date">Event Date *</label>
        <input 
          type="date" 
          id="event_date" 
          v-model="form.event_date" 
          required
          :class="{ 'error': errors.event_date }"
        >
        <div class="error-message" v-if="errors.event_date">{{ errors.event_date }}</div>
      </div>

      <div class="form-group">
        <label for="event_time">Event Time</label>
        <input 
          type="time" 
          id="event_time" 
          v-model="form.event_time"
          :class="{ 'error': errors.event_time }"
        >
        <div class="error-message" v-if="errors.event_time">{{ errors.event_time }}</div>
      </div>

      <div class="form-group">
        <label for="count">Attendance Count *</label>
        <input 
          type="number" 
          id="count" 
          v-model.number="form.count" 
          min="0"
          required
          :class="{ 'error': errors.count }"
        >
        <div class="error-message" v-if="errors.count">{{ errors.count }}</div>
      </div>

      <div class="form-group">
        <label for="notes">Notes</label>
        <textarea 
          id="notes" 
          v-model="form.notes"
          rows="3"
          :class="{ 'error': errors.notes }"
        ></textarea>
        <div class="error-message" v-if="errors.notes">{{ errors.notes }}</div>
      </div>

      <div class="form-actions">
        <button 
          type="button" 
          class="btn-secondary" 
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting">
            <i class="fas fa-spinner fa-spin"></i> Saving...
          </span>
          <span v-else>
            {{ isEditing ? 'Update Record' : 'Save Record' }}
          </span>
        </button>
      </div>
    </form>

    <div class="offline-notice" v-if="isOfflineMode">
      <p>
        <i class="fas fa-info-circle"></i>
        You are currently offline. This attendance record will be saved locally and synchronized when you're back online.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useOffline } from '../../services/offlineService';
import { useToast } from 'vue-toastification';

const props = defineProps({
  attendanceRecord: {
    type: Object,
    default: () => ({})
  },
  isEditing: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['saved', 'cancel']);

// Get services
const offline = useOffline();
const toast = useToast();

// Form state
const form = reactive({
  event_name: '',
  event_date: '',
  event_time: '',
  count: 0,
  notes: ''
});

// Validation errors
const errors = reactive({
  event_name: '',
  event_date: '',
  event_time: '',
  count: '',
  notes: ''
});

// Form submission state
const isSubmitting = ref(false);

// Get offline status
const isOfflineMode = computed(() => offline.isOfflineMode);

// Initialize form with existing data if editing
onMounted(() => {
  if (props.isEditing && props.attendanceRecord) {
    form.event_name = props.attendanceRecord.event_name || '';
    form.event_date = props.attendanceRecord.event_date || '';
    form.event_time = props.attendanceRecord.event_time || '';
    form.count = props.attendanceRecord.count || 0;
    form.notes = props.attendanceRecord.notes || '';
  } else {
    // Set default date to today for new records
    const today = new Date();
    form.event_date = today.toISOString().split('T')[0];
  }
});

// Validate form
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  // Validate event name
  if (!form.event_name.trim()) {
    errors.event_name = 'Event name is required';
    isValid = false;
  }
  
  // Validate event date
  if (!form.event_date) {
    errors.event_date = 'Event date is required';
    isValid = false;
  }
  
  // Validate count
  if (form.count === undefined || form.count < 0) {
    errors.count = 'Attendance count must be a positive number';
    isValid = false;
  }
  
  return isValid;
};

// Submit form
const submitForm = async () => {
  // Validate form
  if (!validateForm()) {
    toast.error('Please fix the errors in the form');
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    // Prepare data
    const attendanceData = {
      id: props.isEditing ? props.attendanceRecord.id : null,
      event_name: form.event_name,
      event_date: form.event_date,
      event_time: form.event_time,
      count: form.count,
      notes: form.notes,
      timestamp: new Date().toISOString()
    };
    
    if (offline.isOfflineMode.value) {
      // Save attendance record offline
      await offline.saveAttendanceOffline(attendanceData);
      
      toast.success('Attendance record saved offline. It will be synchronized when you\'re back online.');
    } else {
      // Save attendance record online
      const response = await fetch('/api/attendance' + (props.isEditing ? `/${attendanceData.id}` : ''), {
        method: props.isEditing ? 'PUT' : 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(attendanceData)
      });
      
      if (!response.ok) {
        throw new Error('Failed to save attendance record');
      }
      
      toast.success(`Attendance record ${props.isEditing ? 'updated' : 'saved'} successfully`);
    }
    
    // Emit saved event
    emit('saved', attendanceData);
  } catch (error) {
    console.error('Error saving attendance record:', error);
    
    // If online request failed, try to save offline
    if (!offline.isOfflineMode.value) {
      try {
        const attendanceData = {
          id: props.isEditing ? props.attendanceRecord.id : null,
          event_name: form.event_name,
          event_date: form.event_date,
          event_time: form.event_time,
          count: form.count,
          notes: form.notes,
          timestamp: new Date().toISOString()
        };
        
        await offline.saveAttendanceOffline(attendanceData);
        
        toast.info('Connection issue detected. Attendance record saved offline and will be synchronized when connection is restored.');
        
        // Emit saved event
        emit('saved', attendanceData);
      } catch (offlineError) {
        console.error('Error saving attendance record offline:', offlineError);
        toast.error('Failed to save attendance record. Please try again later.');
      }
    } else {
      toast.error('Failed to save attendance record. Please try again later.');
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.offline-attendance-form {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.form-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.offline-indicator {
  display: flex;
  align-items: center;
  background-color: #fee2e2;
  color: #ef4444;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.offline-indicator i {
  margin-right: 0.25rem;
}

.attendance-form {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .attendance-form {
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
  }
}

.form-group {
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .form-group:nth-child(5) {
    grid-column: span 2;
  }
  
  .form-actions {
    grid-column: span 2;
  }
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

input, textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  font-size: 1rem;
  transition: border-color 0.2s;
}

input:focus, textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

input.error, textarea.error {
  border-color: #ef4444;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary, .btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-primary:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: white;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background-color: #f3f4f6;
}

.offline-notice {
  margin-top: 1.5rem;
  padding: 0.75rem;
  background-color: #fffbeb;
  border-left: 4px solid #f59e0b;
  border-radius: 0.25rem;
}

.offline-notice p {
  margin: 0;
  font-size: 0.875rem;
  color: #92400e;
}

.offline-notice i {
  margin-right: 0.25rem;
}

/* Dark mode support */
:global(.dark) .offline-attendance-form {
  background-color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

:global(.dark) .form-header h2 {
  color: #f3f4f6;
}

:global(.dark) .offline-indicator {
  background-color: #7f1d1d;
  color: #fca5a5;
}

:global(.dark) label {
  color: #d1d5db;
}

:global(.dark) input, :global(.dark) textarea {
  background-color: #374151;
  border-color: #4b5563;
  color: #e5e7eb;
}

:global(.dark) input:focus, :global(.dark) textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

:global(.dark) .btn-secondary {
  background-color: #374151;
  color: #e5e7eb;
  border-color: #4b5563;
}

:global(.dark) .btn-secondary:hover {
  background-color: #4b5563;
}

:global(.dark) .offline-notice {
  background-color: #78350f;
  border-left-color: #f59e0b;
}

:global(.dark) .offline-notice p {
  color: #fcd34d;
}
</style>
