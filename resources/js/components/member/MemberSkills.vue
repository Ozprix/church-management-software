<template>
  <div class="member-skills">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Skills</h5>
        <div>
          <button v-if="canManageSkills" class="btn btn-sm btn-primary" @click="showAddSkillModal">
            <i class="fas fa-plus"></i> Add Skill
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="memberSkills.length === 0" class="text-center py-3">
          <p class="text-muted">No skills added yet.</p>
          <button v-if="canManageSkills" class="btn btn-outline-primary" @click="showAddSkillModal">
            <i class="fas fa-plus"></i> Add Skill
          </button>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Skill</th>
                <th>Category</th>
                <th>Proficiency</th>
                <th>Verified</th>
                <th v-if="canManageSkills">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="skill in memberSkills" :key="skill.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="skill-icon me-2">
                      <i class="fas fa-tools"></i>
                    </div>
                    <div>
                      <div class="fw-bold">{{ skill.name }}</div>
                      <div class="text-muted small" v-if="skill.pivot.notes">{{ skill.pivot.notes }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ skill.category || 'Uncategorized' }}</td>
                <td>
                  <span :class="getProficiencyBadgeClass(skill.pivot.proficiency_level)">
                    {{ formatProficiencyLevel(skill.pivot.proficiency_level) }}
                  </span>
                </td>
                <td>
                  <span v-if="skill.pivot.is_verified" class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> Verified
                  </span>
                  <span v-else class="badge bg-secondary">
                    <i class="fas fa-clock me-1"></i> Unverified
                  </span>
                </td>
                <td v-if="canManageSkills">
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" @click="editSkill(skill)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmRemoveSkill(skill)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Skill Modal -->
    <div class="modal fade" id="skillModal" tabindex="-1" aria-labelledby="skillModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="skillModalLabel">{{ isEditing ? 'Edit Skill' : 'Add Skill' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveSkill">
              <div class="mb-3">
                <label for="skillSelect" class="form-label">Skill</label>
                <div v-if="loadingSkills" class="text-center">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <select v-else id="skillSelect" v-model="selectedSkill" class="form-select" required>
                  <option value="" disabled>Select a skill</option>
                  <optgroup v-for="(skills, category) in groupedSkills" :key="category" :label="category || 'Uncategorized'">
                    <option v-for="skill in skills" :key="skill.id" :value="skill.id">{{ skill.name }}</option>
                  </optgroup>
                </select>
              </div>
              <div class="mb-3">
                <label for="proficiencyLevel" class="form-label">Proficiency Level</label>
                <select id="proficiencyLevel" v-model="skillForm.proficiency_level" class="form-select" required>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="skillNotes" class="form-label">Notes</label>
                <textarea id="skillNotes" v-model="skillForm.notes" class="form-control" rows="3"></textarea>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="isVerified" v-model="skillForm.is_verified">
                <label class="form-check-label" for="isVerified">
                  Verified Skill
                </label>
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
            <h5 class="modal-title">Remove Skill</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to remove <strong>{{ skillToRemove?.name }}</strong> from this member's skills?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="removeSkill" :disabled="removing">
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
  name: 'MemberSkills',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    },
    canManageSkills: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      loadingSkills: false,
      saving: false,
      removing: false,
      memberSkills: [],
      availableSkills: [],
      selectedSkill: '',
      isEditing: false,
      skillForm: {
        skill_id: null,
        proficiency_level: 'intermediate',
        notes: '',
        is_verified: false
      },
      skillToRemove: null,
      skillModal: null,
      confirmRemoveModal: null
    };
  },
  computed: {
    groupedSkills() {
      const grouped = {};
      
      this.availableSkills.forEach(skill => {
        const category = skill.category || 'Uncategorized';
        if (!grouped[category]) {
          grouped[category] = [];
        }
        
        // Only add skills that the member doesn't already have
        if (!this.memberSkills.some(memberSkill => memberSkill.id === skill.id)) {
          grouped[category].push(skill);
        }
      });
      
      return grouped;
    }
  },
  mounted() {
    this.loadMemberSkills();
    this.loadAvailableSkills();
    this.skillModal = new Modal(document.getElementById('skillModal'));
    this.confirmRemoveModal = new Modal(document.getElementById('confirmRemoveModal'));
  },
  methods: {
    async loadMemberSkills() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/members/${this.memberId}/skills`);
        this.memberSkills = response.data.data;
      } catch (error) {
        console.error('Error loading member skills:', error);
        this.$toast.error('Failed to load member skills');
      } finally {
        this.loading = false;
      }
    },
    async loadAvailableSkills() {
      this.loadingSkills = true;
      try {
        const response = await axios.get('/api/skills');
        this.availableSkills = response.data.data.data; // Paginated response
      } catch (error) {
        console.error('Error loading available skills:', error);
        this.$toast.error('Failed to load available skills');
      } finally {
        this.loadingSkills = false;
      }
    },
    showAddSkillModal() {
      this.isEditing = false;
      this.selectedSkill = '';
      this.skillForm = {
        skill_id: null,
        proficiency_level: 'intermediate',
        notes: '',
        is_verified: false
      };
      this.skillModal.show();
    },
    editSkill(skill) {
      this.isEditing = true;
      this.selectedSkill = skill.id;
      this.skillForm = {
        skill_id: skill.id,
        proficiency_level: skill.pivot.proficiency_level,
        notes: skill.pivot.notes,
        is_verified: skill.pivot.is_verified
      };
      this.skillModal.show();
    },
    async saveSkill() {
      this.saving = true;
      
      try {
        const formData = { ...this.skillForm };
        formData.skill_id = this.selectedSkill;
        
        await axios.post(`/api/members/${this.memberId}/skills`, formData);
        
        this.skillModal.hide();
        await this.loadMemberSkills();
        this.$toast.success(this.isEditing ? 'Skill updated successfully' : 'Skill added successfully');
      } catch (error) {
        console.error('Error saving skill:', error);
        this.$toast.error('Failed to save skill');
      } finally {
        this.saving = false;
      }
    },
    confirmRemoveSkill(skill) {
      this.skillToRemove = skill;
      this.confirmRemoveModal.show();
    },
    async removeSkill() {
      this.removing = true;
      
      try {
        await axios.delete(`/api/members/${this.memberId}/skills/${this.skillToRemove.id}`);
        
        this.confirmRemoveModal.hide();
        await this.loadMemberSkills();
        this.$toast.success('Skill removed successfully');
      } catch (error) {
        console.error('Error removing skill:', error);
        this.$toast.error('Failed to remove skill');
      } finally {
        this.removing = false;
        this.skillToRemove = null;
      }
    },
    getProficiencyBadgeClass(level) {
      const classes = {
        beginner: 'badge bg-info',
        intermediate: 'badge bg-primary',
        advanced: 'badge bg-success',
        expert: 'badge bg-warning text-dark'
      };
      
      return classes[level] || 'badge bg-secondary';
    },
    formatProficiencyLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    }
  }
};
</script>

<style scoped>
.member-skills .skill-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f0f0;
  border-radius: 50%;
}
</style>
