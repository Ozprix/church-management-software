<template>
  <div class="member-interests">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Interests</h5>
        <div>
          <button v-if="canManageInterests" class="btn btn-sm btn-primary" @click="showAddInterestModal">
            <i class="fas fa-plus"></i> Add Interest
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="memberInterests.length === 0" class="text-center py-3">
          <p class="text-muted">No interests added yet.</p>
          <button v-if="canManageInterests" class="btn btn-outline-primary" @click="showAddInterestModal">
            <i class="fas fa-plus"></i> Add Interest
          </button>
        </div>
        <div v-else>
          <div class="row g-3">
            <div v-for="interest in memberInterests" :key="interest.id" class="col-md-6 col-lg-4">
              <div class="interest-card p-3 border rounded h-100">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="d-flex align-items-center">
                    <div class="interest-icon me-2">
                      <i class="fas fa-heart"></i>
                    </div>
                    <div>
                      <h6 class="mb-0">{{ interest.name }}</h6>
                      <div class="text-muted small">{{ interest.category || 'Uncategorized' }}</div>
                    </div>
                  </div>
                  <div v-if="canManageInterests" class="dropdown">
                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><button class="dropdown-item" @click="editInterest(interest)"><i class="fas fa-edit me-2"></i> Edit</button></li>
                      <li><button class="dropdown-item text-danger" @click="confirmRemoveInterest(interest)"><i class="fas fa-trash me-2"></i> Remove</button></li>
                    </ul>
                  </div>
                </div>
                <div class="mt-2">
                  <span :class="getInterestLevelBadgeClass(interest.pivot.interest_level)">
                    {{ formatInterestLevel(interest.pivot.interest_level) }} Interest
                  </span>
                </div>
                <div v-if="interest.pivot.notes" class="mt-2 small text-muted">
                  {{ interest.pivot.notes }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Interest Modal -->
    <div class="modal fade" id="interestModal" tabindex="-1" aria-labelledby="interestModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="interestModalLabel">{{ isEditing ? 'Edit Interest' : 'Add Interest' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveInterest">
              <div class="mb-3">
                <label for="interestSelect" class="form-label">Interest</label>
                <div v-if="loadingInterests" class="text-center">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <select v-else id="interestSelect" v-model="selectedInterest" class="form-select" required>
                  <option value="" disabled>Select an interest</option>
                  <optgroup v-for="(interests, category) in groupedInterests" :key="category" :label="category || 'Uncategorized'">
                    <option v-for="interest in interests" :key="interest.id" :value="interest.id">{{ interest.name }}</option>
                  </optgroup>
                </select>
              </div>
              <div class="mb-3">
                <label for="interestLevel" class="form-label">Interest Level</label>
                <select id="interestLevel" v-model="interestForm.interest_level" class="form-select" required>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="interestNotes" class="form-label">Notes</label>
                <textarea id="interestNotes" v-model="interestForm.notes" class="form-control" rows="3"></textarea>
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

    <!-- Confirm Remove Modal -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Remove Interest</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to remove <strong>{{ interestToRemove?.name }}</strong> from this member's interests?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="removeInterest" :disabled="removing">
              <span v-if="removing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Remove
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
  name: 'MemberInterests',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    },
    canManageInterests: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      loadingInterests: false,
      saving: false,
      removing: false,
      memberInterests: [],
      availableInterests: [],
      selectedInterest: '',
      isEditing: false,
      interestForm: {
        interest_id: null,
        interest_level: 'medium',
        notes: ''
      },
      interestToRemove: null,
      interestModal: null,
      confirmRemoveModal: null
    };
  },
  computed: {
    groupedInterests() {
      const grouped = {};
      
      this.availableInterests.forEach(interest => {
        const category = interest.category || 'Uncategorized';
        if (!grouped[category]) {
          grouped[category] = [];
        }
        
        // Only add interests that the member doesn't already have
        if (!this.memberInterests.some(memberInterest => memberInterest.id === interest.id)) {
          grouped[category].push(interest);
        }
      });
      
      return grouped;
    }
  },
  mounted() {
    this.loadMemberInterests();
    this.loadAvailableInterests();
    this.interestModal = new Modal(document.getElementById('interestModal'));
    this.confirmRemoveModal = new Modal(document.getElementById('confirmRemoveModal'));
  },
  methods: {
    async loadMemberInterests() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/members/${this.memberId}/interests`);
        this.memberInterests = response.data.data;
      } catch (error) {
        console.error('Error loading member interests:', error);
        this.$toast.error('Failed to load member interests');
      } finally {
        this.loading = false;
      }
    },
    async loadAvailableInterests() {
      this.loadingInterests = true;
      try {
        const response = await axios.get('/api/interests');
        this.availableInterests = response.data.data.data; // Paginated response
      } catch (error) {
        console.error('Error loading available interests:', error);
        this.$toast.error('Failed to load available interests');
      } finally {
        this.loadingInterests = false;
      }
    },
    showAddInterestModal() {
      this.isEditing = false;
      this.selectedInterest = '';
      this.interestForm = {
        interest_id: null,
        interest_level: 'medium',
        notes: ''
      };
      this.interestModal.show();
    },
    editInterest(interest) {
      this.isEditing = true;
      this.selectedInterest = interest.id;
      this.interestForm = {
        interest_id: interest.id,
        interest_level: interest.pivot.interest_level,
        notes: interest.pivot.notes
      };
      this.interestModal.show();
    },
    async saveInterest() {
      this.saving = true;
      
      try {
        const formData = { ...this.interestForm };
        formData.interest_id = this.selectedInterest;
        
        await axios.post(`/api/members/${this.memberId}/interests`, formData);
        
        this.interestModal.hide();
        await this.loadMemberInterests();
        this.$toast.success(this.isEditing ? 'Interest updated successfully' : 'Interest added successfully');
      } catch (error) {
        console.error('Error saving interest:', error);
        this.$toast.error('Failed to save interest');
      } finally {
        this.saving = false;
      }
    },
    confirmRemoveInterest(interest) {
      this.interestToRemove = interest;
      this.confirmRemoveModal.show();
    },
    async removeInterest() {
      this.removing = true;
      
      try {
        await axios.delete(`/api/members/${this.memberId}/interests/${this.interestToRemove.id}`);
        
        this.confirmRemoveModal.hide();
        await this.loadMemberInterests();
        this.$toast.success('Interest removed successfully');
      } catch (error) {
        console.error('Error removing interest:', error);
        this.$toast.error('Failed to remove interest');
      } finally {
        this.removing = false;
        this.interestToRemove = null;
      }
    },
    getInterestLevelBadgeClass(level) {
      const classes = {
        low: 'badge bg-light text-dark',
        medium: 'badge bg-info',
        high: 'badge bg-primary'
      };
      
      return classes[level] || 'badge bg-secondary';
    },
    formatInterestLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    }
  }
};
</script>

<style scoped>
.member-interests .interest-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f0f0;
  color: #dc3545;
  border-radius: 50%;
}

.interest-card {
  transition: all 0.2s ease;
  background-color: #fff;
}

.interest-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}
</style>
