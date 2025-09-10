<template>
  <div class="group-member-roles">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Group Member Roles</h5>
        <div>
          <button class="btn btn-sm btn-primary" @click="showAddRoleModal" v-if="canManageRoles">
            <i class="fas fa-plus"></i> Add Role
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
          <div v-if="groupMembers.length === 0" class="text-center py-3">
            <p class="text-muted">No members in this group yet.</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Role</th>
                  <th>Permissions</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in groupMembers" :key="member.id">
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        <img v-if="member.profile_photo" :src="member.profile_photo" alt="Profile" class="rounded-circle">
                        <div v-else class="avatar-placeholder">
                          {{ getInitials(member.first_name, member.last_name) }}
                        </div>
                      </div>
                      <div>
                        <div class="fw-bold">{{ member.first_name }} {{ member.last_name }}</div>
                        <div class="small text-muted">{{ member.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="getRoleBadgeClass(member.pivot.role)">
                      {{ formatRole(member.pivot.role) }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <span v-for="(permission, index) in getRolePermissions(member.pivot.role)" :key="index" 
                            class="badge bg-light text-dark">
                        {{ permission }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary" @click="editMemberRole(member)" v-if="canManageRoles">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-outline-danger" @click="confirmRemoveRole(member)" v-if="canManageRoles && member.pivot.role !== 'leader'">
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
    </div>

    <!-- Add/Edit Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true" ref="roleModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="roleModalLabel">{{ isEditing ? 'Edit' : 'Add' }} Group Role</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRole">
              <div class="mb-3">
                <label for="memberSelect" class="form-label">Member</label>
                <select id="memberSelect" class="form-select" v-model="selectedMember" :disabled="isEditing">
                  <option value="">Select a member</option>
                  <option v-for="member in availableMembers" :key="member.id" :value="member.id">
                    {{ member.first_name }} {{ member.last_name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label for="roleSelect" class="form-label">Role</label>
                <select id="roleSelect" class="form-select" v-model="selectedRole">
                  <option value="leader">Leader</option>
                  <option value="assistant_leader">Assistant Leader</option>
                  <option value="secretary">Secretary</option>
                  <option value="treasurer">Treasurer</option>
                  <option value="member">Member</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-3" v-if="selectedRole === 'other'">
                <label for="customRole" class="form-label">Custom Role Title</label>
                <input type="text" class="form-control" id="customRole" v-model="customRoleTitle" placeholder="Enter custom role title">
              </div>
              <div class="mb-3">
                <label class="form-label">Role Permissions</label>
                <div class="form-check mb-2" v-for="(permission, index) in availablePermissions" :key="index">
                  <input class="form-check-input" type="checkbox" :id="`permission-${index}`" 
                         v-model="selectedPermissions" :value="permission.value">
                  <label class="form-check-label" :for="`permission-${index}`">
                    {{ permission.label }}
                  </label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveRole">Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" ref="confirmationModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Action</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to remove this member's role?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="removeRole">Remove</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'GroupMemberRoles',
  props: {
    groupId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      loading: false,
      groupMembers: [],
      availableMembers: [],
      isEditing: false,
      selectedMember: '',
      selectedRole: 'member',
      customRoleTitle: '',
      selectedPermissions: [],
      memberToRemove: null,
      roleModal: null,
      confirmationModal: null,
      availablePermissions: [
        { label: 'Manage attendance', value: 'manage_attendance' },
        { label: 'Manage events', value: 'manage_events' },
        { label: 'Send communications', value: 'send_communications' },
        { label: 'Manage documents', value: 'manage_documents' },
        { label: 'View analytics', value: 'view_analytics' },
        { label: 'Manage members', value: 'manage_members' }
      ]
    };
  },
  computed: {
    canManageRoles() {
      // Check if current user has permission to manage roles
      return this.$store.getters['auth/hasPermission']('manage_group_roles') || 
             this.isGroupLeader();
    }
  },
  mounted() {
    this.loadGroupMembers();
    this.loadAvailableMembers();
    
    // Initialize Bootstrap modals
    this.$nextTick(() => {
      this.roleModal = new Modal(this.$refs.roleModal);
      this.confirmationModal = new Modal(this.$refs.confirmationModal);
    });
  },
  methods: {
    async loadGroupMembers() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/groups/${this.groupId}/members`);
        this.groupMembers = response.data.data;
      } catch (error) {
        console.error('Error loading group members:', error);
        this.$toast.error('Failed to load group members');
      } finally {
        this.loading = false;
      }
    },
    async loadAvailableMembers() {
      try {
        const response = await axios.get('/api/members');
        // Filter out members who are already in the group
        this.availableMembers = response.data.data.filter(member => {
          return !this.groupMembers.some(groupMember => groupMember.id === member.id);
        });
      } catch (error) {
        console.error('Error loading available members:', error);
        this.$toast.error('Failed to load available members');
      }
    },
    showAddRoleModal() {
      this.isEditing = false;
      this.selectedMember = '';
      this.selectedRole = 'member';
      this.customRoleTitle = '';
      this.selectedPermissions = ['view_analytics']; // Default permission
      this.roleModal.show();
    },
    editMemberRole(member) {
      this.isEditing = true;
      this.selectedMember = member.id;
      this.selectedRole = member.pivot.role;
      this.customRoleTitle = member.pivot.custom_role_title || '';
      this.selectedPermissions = member.pivot.permissions || ['view_analytics'];
      this.roleModal.show();
    },
    async saveRole() {
      try {
        const payload = {
          role: this.selectedRole,
          custom_role_title: this.selectedRole === 'other' ? this.customRoleTitle : '',
          permissions: this.selectedPermissions
        };
        
        if (this.isEditing) {
          await axios.put(`/api/groups/${this.groupId}/members/${this.selectedMember}`, payload);
          this.$toast.success('Member role updated successfully');
        } else {
          await axios.post(`/api/groups/${this.groupId}/members`, {
            member_id: this.selectedMember,
            ...payload
          });
          this.$toast.success('Member added to group successfully');
        }
        
        this.roleModal.hide();
        this.loadGroupMembers();
        this.loadAvailableMembers();
      } catch (error) {
        console.error('Error saving role:', error);
        this.$toast.error('Failed to save role');
      }
    },
    confirmRemoveRole(member) {
      this.memberToRemove = member;
      this.confirmationModal.show();
    },
    async removeRole() {
      if (!this.memberToRemove) return;
      
      try {
        await axios.delete(`/api/groups/${this.groupId}/members/${this.memberToRemove.id}`);
        this.$toast.success('Member removed from group successfully');
        this.confirmationModal.hide();
        this.loadGroupMembers();
        this.loadAvailableMembers();
      } catch (error) {
        console.error('Error removing member:', error);
        this.$toast.error('Failed to remove member');
      }
    },
    formatRole(role) {
      if (!role) return 'Member';
      
      return role
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    },
    getRoleBadgeClass(role) {
      switch (role) {
        case 'leader': return 'bg-primary';
        case 'assistant_leader': return 'bg-info';
        case 'secretary': return 'bg-success';
        case 'treasurer': return 'bg-warning text-dark';
        case 'other': return 'bg-secondary';
        default: return 'bg-light text-dark';
      }
    },
    getRolePermissions(role) {
      // In a real implementation, this would come from the backend
      // This is a simplified version for demonstration
      const permissionMap = {
        'leader': ['manage_attendance', 'manage_events', 'send_communications', 'manage_documents', 'view_analytics', 'manage_members'],
        'assistant_leader': ['manage_attendance', 'manage_events', 'send_communications', 'view_analytics'],
        'secretary': ['manage_attendance', 'send_communications', 'manage_documents', 'view_analytics'],
        'treasurer': ['manage_events', 'view_analytics'],
        'member': ['view_analytics']
      };
      
      return (permissionMap[role] || []).map(permission => {
        return permission
          .split('_')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ');
      });
    },
    getInitials(firstName, lastName) {
      return `${firstName ? firstName.charAt(0) : ''}${lastName ? lastName.charAt(0) : ''}`;
    },
    isGroupLeader() {
      // Check if current user is the group leader
      const currentUserId = this.$store.getters['auth/currentUser']?.id;
      if (!currentUserId) return false;
      
      const userMembership = this.groupMembers.find(member => member.id === currentUserId);
      return userMembership && userMembership.pivot.role === 'leader';
    }
  }
};
</script>

<style scoped>
.avatar-sm {
  width: 40px;
  height: 40px;
  overflow: hidden;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background-color: #e9ecef;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  border-radius: 50%;
}

.badge {
  font-size: 0.8rem;
  padding: 0.35em 0.65em;
}
</style>
