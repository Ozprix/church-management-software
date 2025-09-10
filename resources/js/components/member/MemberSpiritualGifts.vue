<template>
  <div class="member-spiritual-gifts">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Spiritual Gifts</h5>
        <div>
          <button v-if="canManageSpiritualGifts" class="btn btn-sm btn-primary" @click="showAddGiftModal">
            <i class="fas fa-plus"></i> Add Spiritual Gift
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="memberGifts.length === 0" class="text-center py-3">
          <p class="text-muted">No spiritual gifts added yet.</p>
          <button v-if="canManageSpiritualGifts" class="btn btn-outline-primary" @click="showAddGiftModal">
            <i class="fas fa-plus"></i> Add Spiritual Gift
          </button>
        </div>
        <div v-else>
          <div class="row g-3">
            <div v-for="gift in memberGifts" :key="gift.id" class="col-md-6 col-lg-4">
              <div class="gift-card p-3 border rounded h-100">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="d-flex align-items-center">
                    <div class="gift-icon me-2">
                      <i class="fas fa-dove"></i>
                    </div>
                    <div>
                      <h6 class="mb-0">{{ gift.name }}</h6>
                      <div v-if="gift.scripture_reference" class="text-muted small">
                        <i class="fas fa-book me-1"></i> {{ gift.scripture_reference }}
                      </div>
                    </div>
                  </div>
                  <div v-if="canManageSpiritualGifts" class="dropdown">
                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><button class="dropdown-item" @click="editGift(gift)"><i class="fas fa-edit me-2"></i> Edit</button></li>
                      <li><button class="dropdown-item text-danger" @click="confirmRemoveGift(gift)"><i class="fas fa-trash me-2"></i> Remove</button></li>
                    </ul>
                  </div>
                </div>
                <div class="mt-2">
                  <span :class="getStrengthLevelBadgeClass(gift.pivot.strength_level)">
                    {{ formatStrengthLevel(gift.pivot.strength_level) }} Strength
                  </span>
                  <span v-if="gift.pivot.is_assessed" class="badge bg-success ms-1">
                    <i class="fas fa-check-circle me-1"></i> Assessed
                  </span>
                </div>
                <div v-if="gift.pivot.assessment_date" class="mt-1 small text-muted">
                  <i class="fas fa-calendar me-1"></i> Assessed on {{ formatDate(gift.pivot.assessment_date) }}
                </div>
                <div v-if="gift.pivot.notes" class="mt-2 small text-muted">
                  {{ gift.pivot.notes }}
                </div>
                <div v-if="gift.description" class="mt-2 small">
                  <div class="text-muted fst-italic">{{ truncateText(gift.description, 100) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Spiritual Gift Modal -->
    <div class="modal fade" id="giftModal" tabindex="-1" aria-labelledby="giftModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="giftModalLabel">{{ isEditing ? 'Edit Spiritual Gift' : 'Add Spiritual Gift' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveGift">
              <div class="mb-3">
                <label for="giftSelect" class="form-label">Spiritual Gift</label>
                <div v-if="loadingGifts" class="text-center">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <select v-else id="giftSelect" v-model="selectedGift" class="form-select" required>
                  <option value="" disabled>Select a spiritual gift</option>
                  <option v-for="gift in availableGifts" :key="gift.id" :value="gift.id">
                    {{ gift.name }}
                  </option>
                </select>
                <div v-if="selectedGiftDescription" class="mt-2 small text-muted fst-italic">
                  {{ selectedGiftDescription }}
                </div>
              </div>
              <div class="mb-3">
                <label for="strengthLevel" class="form-label">Strength Level</label>
                <select id="strengthLevel" v-model="giftForm.strength_level" class="form-select" required>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="isAssessed" v-model="giftForm.is_assessed">
                  <label class="form-check-label" for="isAssessed">
                    Formally Assessed
                  </label>
                </div>
              </div>
              <div v-if="giftForm.is_assessed" class="mb-3">
                <label for="assessmentDate" class="form-label">Assessment Date</label>
                <input type="date" id="assessmentDate" v-model="giftForm.assessment_date" class="form-control">
              </div>
              <div class="mb-3">
                <label for="giftNotes" class="form-label">Notes</label>
                <textarea id="giftNotes" v-model="giftForm.notes" class="form-control" rows="3"></textarea>
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
            <h5 class="modal-title">Remove Spiritual Gift</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to remove <strong>{{ giftToRemove?.name }}</strong> from this member's spiritual gifts?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="removeGift" :disabled="removing">
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
  name: 'MemberSpiritualGifts',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    },
    canManageSpiritualGifts: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      loadingGifts: false,
      saving: false,
      removing: false,
      memberGifts: [],
      availableGifts: [],
      selectedGift: '',
      isEditing: false,
      giftForm: {
        spiritual_gift_id: null,
        strength_level: 'medium',
        notes: '',
        is_assessed: false,
        assessment_date: null
      },
      giftToRemove: null,
      giftModal: null,
      confirmRemoveModal: null
    };
  },
  computed: {
    selectedGiftDescription() {
      if (!this.selectedGift) return '';
      const gift = this.availableGifts.find(g => g.id === this.selectedGift);
      return gift ? gift.description : '';
    },
    filteredAvailableGifts() {
      return this.availableGifts.filter(gift => 
        !this.memberGifts.some(memberGift => memberGift.id === gift.id)
      );
    }
  },
  mounted() {
    this.loadMemberGifts();
    this.loadAvailableGifts();
    this.giftModal = new Modal(document.getElementById('giftModal'));
    this.confirmRemoveModal = new Modal(document.getElementById('confirmRemoveModal'));
  },
  methods: {
    async loadMemberGifts() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/members/${this.memberId}/spiritual-gifts`);
        this.memberGifts = response.data.data;
      } catch (error) {
        console.error('Error loading member spiritual gifts:', error);
        this.$toast.error('Failed to load member spiritual gifts');
      } finally {
        this.loading = false;
      }
    },
    async loadAvailableGifts() {
      this.loadingGifts = true;
      try {
        const response = await axios.get('/api/spiritual-gifts');
        this.availableGifts = response.data.data.data; // Paginated response
      } catch (error) {
        console.error('Error loading available spiritual gifts:', error);
        this.$toast.error('Failed to load available spiritual gifts');
      } finally {
        this.loadingGifts = false;
      }
    },
    showAddGiftModal() {
      this.isEditing = false;
      this.selectedGift = '';
      this.giftForm = {
        spiritual_gift_id: null,
        strength_level: 'medium',
        notes: '',
        is_assessed: false,
        assessment_date: null
      };
      this.giftModal.show();
    },
    editGift(gift) {
      this.isEditing = true;
      this.selectedGift = gift.id;
      this.giftForm = {
        spiritual_gift_id: gift.id,
        strength_level: gift.pivot.strength_level,
        notes: gift.pivot.notes,
        is_assessed: gift.pivot.is_assessed,
        assessment_date: gift.pivot.assessment_date
      };
      this.giftModal.show();
    },
    async saveGift() {
      this.saving = true;
      
      try {
        const formData = { ...this.giftForm };
        formData.spiritual_gift_id = this.selectedGift;
        
        await axios.post(`/api/members/${this.memberId}/spiritual-gifts`, formData);
        
        this.giftModal.hide();
        await this.loadMemberGifts();
        this.$toast.success(this.isEditing ? 'Spiritual gift updated successfully' : 'Spiritual gift added successfully');
      } catch (error) {
        console.error('Error saving spiritual gift:', error);
        this.$toast.error('Failed to save spiritual gift');
      } finally {
        this.saving = false;
      }
    },
    confirmRemoveGift(gift) {
      this.giftToRemove = gift;
      this.confirmRemoveModal.show();
    },
    async removeGift() {
      this.removing = true;
      
      try {
        await axios.delete(`/api/members/${this.memberId}/spiritual-gifts/${this.giftToRemove.id}`);
        
        this.confirmRemoveModal.hide();
        await this.loadMemberGifts();
        this.$toast.success('Spiritual gift removed successfully');
      } catch (error) {
        console.error('Error removing spiritual gift:', error);
        this.$toast.error('Failed to remove spiritual gift');
      } finally {
        this.removing = false;
        this.giftToRemove = null;
      }
    },
    getStrengthLevelBadgeClass(level) {
      const classes = {
        low: 'badge bg-light text-dark',
        medium: 'badge bg-info',
        high: 'badge bg-primary'
      };
      
      return classes[level] || 'badge bg-secondary';
    },
    formatStrengthLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    truncateText(text, maxLength) {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    }
  }
};
</script>

<style scoped>
.member-spiritual-gifts .gift-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f8ff;
  color: #007bff;
  border-radius: 50%;
}

.gift-card {
  transition: all 0.2s ease;
  background-color: #fff;
}

.gift-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}
</style>
