<template>
  <div class="member-availability">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Availability</h5>
        <div class="btn-group">
          <button v-if="canManageAvailability" class="btn btn-sm btn-outline-secondary" @click="showCopyDayModal">
            <i class="fas fa-copy"></i> Copy Day
          </button>
          <button v-if="canManageAvailability" class="btn btn-sm btn-outline-danger" @click="confirmClearAvailability">
            <i class="fas fa-trash-alt"></i> Clear All
          </button>
          <button v-if="canManageAvailability" class="btn btn-sm btn-primary" @click="showAddTimeSlotModal">
            <i class="fas fa-plus"></i> Add Time Slot
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else>
          <div class="mb-3">
            <div class="btn-group w-100">
              <button 
                v-for="(day, index) in daysOfWeek" 
                :key="index" 
                class="btn" 
                :class="[selectedDay === index ? 'btn-primary' : 'btn-outline-secondary']"
                @click="selectedDay = index"
              >
                {{ day.short }}
              </button>
            </div>
          </div>
          
          <div v-if="!hasAvailabilityForDay" class="text-center py-5">
            <p class="text-muted">No availability set for {{ daysOfWeek[selectedDay].name }}.</p>
            <button v-if="canManageAvailability" class="btn btn-outline-primary" @click="showAddTimeSlotModal">
              <i class="fas fa-plus"></i> Add Time Slot
            </button>
          </div>
          
          <div v-else class="time-slots">
            <div v-for="timeSlot in dayTimeSlots" :key="timeSlot.id" class="time-slot-card mb-3 p-3 border rounded">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div :class="getAvailabilityTypeIconClass(timeSlot.availability_type)" class="me-3">
                    <i :class="getAvailabilityTypeIcon(timeSlot.availability_type)"></i>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ formatTime(timeSlot.start_time) }} - {{ formatTime(timeSlot.end_time) }}</h6>
                    <span :class="getAvailabilityTypeBadgeClass(timeSlot.availability_type)">
                      {{ formatAvailabilityType(timeSlot.availability_type) }}
                    </span>
                  </div>
                </div>
                <div v-if="canManageAvailability" class="btn-group">
                  <button class="btn btn-sm btn-outline-primary" @click="editTimeSlot(timeSlot)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="confirmRemoveTimeSlot(timeSlot)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <div v-if="timeSlot.notes" class="mt-2 small text-muted">
                {{ timeSlot.notes }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Time Slot Modal -->
    <div class="modal fade" id="timeSlotModal" tabindex="-1" aria-labelledby="timeSlotModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="timeSlotModalLabel">{{ isEditing ? 'Edit Time Slot' : 'Add Time Slot' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveTimeSlot">
              <div class="mb-3">
                <label for="dayOfWeek" class="form-label">Day of Week</label>
                <select id="dayOfWeek" v-model="timeSlotForm.day_of_week" class="form-select" required>
                  <option v-for="(day, index) in daysOfWeek" :key="index" :value="index">
                    {{ day.name }}
                  </option>
                </select>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="startTime" class="form-label">Start Time</label>
                  <input type="time" id="startTime" v-model="timeSlotForm.start_time" class="form-control" required>
                </div>
                <div class="col">
                  <label for="endTime" class="form-label">End Time</label>
                  <input type="time" id="endTime" v-model="timeSlotForm.end_time" class="form-control" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="availabilityType" class="form-label">Availability Type</label>
                <select id="availabilityType" v-model="timeSlotForm.availability_type" class="form-select" required>
                  <option value="available">Available</option>
                  <option value="unavailable">Unavailable</option>
                  <option value="preferred">Preferred</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" v-model="timeSlotForm.notes" class="form-control" rows="3"></textarea>
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  {{ isEditing ? 'Update' : 'Add' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Copy Day Modal -->
    <div class="modal fade" id="copyDayModal" tabindex="-1" aria-labelledby="copyDayModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="copyDayModalLabel">Copy Day Availability</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="copyDay">
              <div class="mb-3">
                <label for="sourceDay" class="form-label">Copy From</label>
                <select id="sourceDay" v-model="copyDayForm.source_day" class="form-select" required>
                  <option v-for="(day, index) in daysOfWeek" :key="index" :value="index">
                    {{ day.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label for="targetDay" class="form-label">Copy To</label>
                <select id="targetDay" v-model="copyDayForm.target_day" class="form-select" required>
                  <option v-for="(day, index) in daysOfWeek" :key="index" :value="index" :disabled="index === copyDayForm.source_day">
                    {{ day.name }}
                  </option>
                </select>
              </div>
              <div class="alert alert-warning" v-if="copyDayForm.target_day !== null && hasDayAvailability(copyDayForm.target_day)">
                <i class="fas fa-exclamation-triangle me-2"></i>
                This will overwrite any existing availability for {{ daysOfWeek[copyDayForm.target_day]?.name }}.
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" :disabled="copying || copyDayForm.source_day === copyDayForm.target_day">
                  <span v-if="copying" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  Copy
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Remove Time Slot Modal -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Remove Time Slot</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to remove this time slot?</p>
            <div v-if="timeSlotToRemove" class="d-flex align-items-center p-2 border rounded">
              <div :class="getAvailabilityTypeIconClass(timeSlotToRemove.availability_type)" class="me-3">
                <i :class="getAvailabilityTypeIcon(timeSlotToRemove.availability_type)"></i>
              </div>
              <div>
                <div class="fw-bold">{{ daysOfWeek[timeSlotToRemove.day_of_week]?.name }}</div>
                <div>{{ formatTime(timeSlotToRemove.start_time) }} - {{ formatTime(timeSlotToRemove.end_time) }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="removeTimeSlot" :disabled="removing">
              <span v-if="removing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Clear All Modal -->
    <div class="modal fade" id="confirmClearModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Clear All Availability</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle me-2"></i>
              This will remove all availability time slots for this member. This action cannot be undone.
            </div>
            <p>Are you sure you want to clear all availability?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="clearAllAvailability" :disabled="clearing">
              <span v-if="clearing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Clear All
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'MemberAvailability',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    },
    canManageAvailability: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      saving: false,
      removing: false,
      copying: false,
      clearing: false,
      availability: [],
      selectedDay: new Date().getDay(), // Default to current day
      isEditing: false,
      timeSlotForm: {
        day_of_week: 0,
        start_time: '09:00',
        end_time: '17:00',
        availability_type: 'available',
        notes: ''
      },
      copyDayForm: {
        source_day: null,
        target_day: null
      },
      timeSlotToRemove: null,
      timeSlotModal: null,
      copyDayModal: null,
      confirmRemoveModal: null,
      confirmClearModal: null,
      daysOfWeek: [
        { name: 'Sunday', short: 'Sun' },
        { name: 'Monday', short: 'Mon' },
        { name: 'Tuesday', short: 'Tue' },
        { name: 'Wednesday', short: 'Wed' },
        { name: 'Thursday', short: 'Thu' },
        { name: 'Friday', short: 'Fri' },
        { name: 'Saturday', short: 'Sat' }
      ]
    };
  },
  computed: {
    dayTimeSlots() {
      return this.availability
        .filter(slot => slot.day_of_week === this.selectedDay)
        .sort((a, b) => {
          // Sort by start time
          return a.start_time.localeCompare(b.start_time);
        });
    },
    hasAvailabilityForDay() {
      return this.dayTimeSlots.length > 0;
    }
  },
  mounted() {
    this.loadMemberAvailability();
    this.timeSlotModal = new Modal(document.getElementById('timeSlotModal'));
    this.copyDayModal = new Modal(document.getElementById('copyDayModal'));
    this.confirmRemoveModal = new Modal(document.getElementById('confirmRemoveModal'));
    this.confirmClearModal = new Modal(document.getElementById('confirmClearModal'));
  },
  methods: {
    async loadMemberAvailability() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/members/${this.memberId}/availability`);
        this.availability = response.data.data;
      } catch (error) {
        console.error('Error loading member availability:', error);
        this.$toast.error('Failed to load member availability');
      } finally {
        this.loading = false;
      }
    },
    showAddTimeSlotModal() {
      this.isEditing = false;
      this.timeSlotForm = {
        day_of_week: this.selectedDay,
        start_time: '09:00',
        end_time: '17:00',
        availability_type: 'available',
        notes: ''
      };
      this.timeSlotModal.show();
    },
    editTimeSlot(timeSlot) {
      this.isEditing = true;
      this.timeSlotForm = {
        id: timeSlot.id,
        day_of_week: timeSlot.day_of_week,
        start_time: timeSlot.start_time,
        end_time: timeSlot.end_time,
        availability_type: timeSlot.availability_type,
        notes: timeSlot.notes
      };
      this.timeSlotModal.show();
    },
    async saveTimeSlot() {
      this.saving = true;
      
      try {
        // Validate that end time is after start time
        if (this.timeSlotForm.start_time >= this.timeSlotForm.end_time) {
          this.$toast.error('End time must be after start time');
          this.saving = false;
          return;
        }
        
        // Check for overlapping time slots
        const overlappingSlots = this.availability.filter(slot => 
          slot.day_of_week === this.timeSlotForm.day_of_week &&
          slot.id !== this.timeSlotForm.id &&
          ((this.timeSlotForm.start_time >= slot.start_time && this.timeSlotForm.start_time < slot.end_time) ||
           (this.timeSlotForm.end_time > slot.start_time && this.timeSlotForm.end_time <= slot.end_time) ||
           (this.timeSlotForm.start_time <= slot.start_time && this.timeSlotForm.end_time >= slot.end_time))
        );
        
        if (overlappingSlots.length > 0) {
          this.$toast.error('This time slot overlaps with an existing time slot');
          this.saving = false;
          return;
        }
        
        // Prepare the time slots array
        const timeSlots = [{
          start_time: this.timeSlotForm.start_time,
          end_time: this.timeSlotForm.end_time,
          availability_type: this.timeSlotForm.availability_type,
          notes: this.timeSlotForm.notes
        }];
        
        // Send the request
        await axios.post(`/api/members/${this.memberId}/availability`, {
          day_of_week: this.timeSlotForm.day_of_week,
          time_slots: timeSlots
        });
        
        this.timeSlotModal.hide();
        await this.loadMemberAvailability();
        this.selectedDay = this.timeSlotForm.day_of_week;
        this.$toast.success(this.isEditing ? 'Time slot updated successfully' : 'Time slot added successfully');
      } catch (error) {
        console.error('Error saving time slot:', error);
        this.$toast.error('Failed to save time slot');
      } finally {
        this.saving = false;
      }
    },
    confirmRemoveTimeSlot(timeSlot) {
      this.timeSlotToRemove = timeSlot;
      this.confirmRemoveModal.show();
    },
    async removeTimeSlot() {
      this.removing = true;
      
      try {
        await axios.delete(`/api/members/${this.memberId}/availability/${this.timeSlotToRemove.id}`);
        
        this.confirmRemoveModal.hide();
        await this.loadMemberAvailability();
        this.$toast.success('Time slot removed successfully');
      } catch (error) {
        console.error('Error removing time slot:', error);
        this.$toast.error('Failed to remove time slot');
      } finally {
        this.removing = false;
        this.timeSlotToRemove = null;
      }
    },
    showCopyDayModal() {
      this.copyDayForm = {
        source_day: this.selectedDay,
        target_day: null
      };
      this.copyDayModal.show();
    },
    async copyDay() {
      this.copying = true;
      
      try {
        await axios.post(`/api/members/${this.memberId}/availability/copy-day`, this.copyDayForm);
        
        this.copyDayModal.hide();
        await this.loadMemberAvailability();
        this.selectedDay = this.copyDayForm.target_day;
        this.$toast.success('Availability copied successfully');
      } catch (error) {
        console.error('Error copying availability:', error);
        this.$toast.error('Failed to copy availability');
      } finally {
        this.copying = false;
      }
    },
    confirmClearAvailability() {
      this.confirmClearModal.show();
    },
    async clearAllAvailability() {
      this.clearing = true;
      
      try {
        await axios.delete(`/api/members/${this.memberId}/availability`);
        
        this.confirmClearModal.hide();
        await this.loadMemberAvailability();
        this.$toast.success('All availability cleared successfully');
      } catch (error) {
        console.error('Error clearing availability:', error);
        this.$toast.error('Failed to clear availability');
      } finally {
        this.clearing = false;
      }
    },
    formatTime(timeString) {
      if (!timeString) return '';
      
      try {
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(parseInt(hours, 10));
        date.setMinutes(parseInt(minutes, 10));
        
        return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      } catch (e) {
        return timeString;
      }
    },
    getAvailabilityTypeIconClass(type) {
      const classes = {
        available: 'availability-icon available',
        unavailable: 'availability-icon unavailable',
        preferred: 'availability-icon preferred'
      };
      
      return classes[type] || 'availability-icon';
    },
    getAvailabilityTypeIcon(type) {
      const icons = {
        available: 'fas fa-check-circle',
        unavailable: 'fas fa-times-circle',
        preferred: 'fas fa-star'
      };
      
      return icons[type] || 'fas fa-circle';
    },
    getAvailabilityTypeBadgeClass(type) {
      const classes = {
        available: 'badge bg-success',
        unavailable: 'badge bg-danger',
        preferred: 'badge bg-primary'
      };
      
      return classes[type] || 'badge bg-secondary';
    },
    formatAvailabilityType(type) {
      return type.charAt(0).toUpperCase() + type.slice(1);
    },
    hasDayAvailability(day) {
      return this.availability.some(slot => slot.day_of_week === day);
    }
  }
};
</script>

<style scoped>
.member-availability .availability-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.member-availability .availability-icon.available {
  background-color: #d4edda;
  color: #28a745;
}

.member-availability .availability-icon.unavailable {
  background-color: #f8d7da;
  color: #dc3545;
}

.member-availability .availability-icon.preferred {
  background-color: #cce5ff;
  color: #007bff;
}

.time-slot-card {
  transition: all 0.2s ease;
}

.time-slot-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
