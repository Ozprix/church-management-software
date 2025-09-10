<template>
  <div class="member-management-tabs">
    <ul class="nav nav-tabs mb-3" id="memberManagementTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'skills' }"
          id="skills-tab" 
          data-bs-toggle="tab" 
          data-bs-target="#skills-tab-pane" 
          type="button" 
          role="tab" 
          aria-controls="skills-tab-pane" 
          aria-selected="true"
          @click="activeTab = 'skills'"
        >
          <i class="fas fa-tools me-1"></i> Skills
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'interests' }"
          id="interests-tab" 
          data-bs-toggle="tab" 
          data-bs-target="#interests-tab-pane" 
          type="button" 
          role="tab" 
          aria-controls="interests-tab-pane" 
          aria-selected="false"
          @click="activeTab = 'interests'"
        >
          <i class="fas fa-heart me-1"></i> Interests
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'spiritual-gifts' }"
          id="spiritual-gifts-tab" 
          data-bs-toggle="tab" 
          data-bs-target="#spiritual-gifts-tab-pane" 
          type="button" 
          role="tab" 
          aria-controls="spiritual-gifts-tab-pane" 
          aria-selected="false"
          @click="activeTab = 'spiritual-gifts'"
        >
          <i class="fas fa-dove me-1"></i> Spiritual Gifts
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'availability' }"
          id="availability-tab" 
          data-bs-toggle="tab" 
          data-bs-target="#availability-tab-pane" 
          type="button" 
          role="tab" 
          aria-controls="availability-tab-pane" 
          aria-selected="false"
          @click="activeTab = 'availability'"
        >
          <i class="fas fa-calendar-alt me-1"></i> Availability
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button 
          class="nav-link" 
          :class="{ active: activeTab === 'matching' }"
          id="matching-tab" 
          data-bs-toggle="tab" 
          data-bs-target="#matching-tab-pane" 
          type="button" 
          role="tab" 
          aria-controls="matching-tab-pane" 
          aria-selected="false"
          @click="activeTab = 'matching'"
        >
          <i class="fas fa-handshake me-1"></i> Ministry Matching
        </button>
      </li>
    </ul>
    <div class="tab-content" id="memberManagementTabsContent">
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': activeTab === 'skills' }"
        id="skills-tab-pane" 
        role="tabpanel" 
        aria-labelledby="skills-tab" 
        tabindex="0"
      >
        <member-skills 
          :member-id="memberId" 
          :can-manage-skills="canManageSkills"
        />
      </div>
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': activeTab === 'interests' }"
        id="interests-tab-pane" 
        role="tabpanel" 
        aria-labelledby="interests-tab" 
        tabindex="0"
      >
        <member-interests 
          :member-id="memberId" 
          :can-manage-interests="canManageInterests"
        />
      </div>
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': activeTab === 'spiritual-gifts' }"
        id="spiritual-gifts-tab-pane" 
        role="tabpanel" 
        aria-labelledby="spiritual-gifts-tab" 
        tabindex="0"
      >
        <member-spiritual-gifts 
          :member-id="memberId" 
          :can-manage-spiritual-gifts="canManageSpiritualGifts"
        />
      </div>
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': activeTab === 'availability' }"
        id="availability-tab-pane" 
        role="tabpanel" 
        aria-labelledby="availability-tab" 
        tabindex="0"
      >
        <member-availability 
          :member-id="memberId" 
          :can-manage-availability="canManageAvailability"
        />
      </div>
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': activeTab === 'matching' }"
        id="matching-tab-pane" 
        role="tabpanel" 
        aria-labelledby="matching-tab" 
        tabindex="0"
      >
        <ministry-matching :member-id="memberId" />
      </div>
    </div>
  </div>
</template>

<script>
import MemberSkills from './MemberSkills.vue';
import MemberInterests from './MemberInterests.vue';
import MemberSpiritualGifts from './MemberSpiritualGifts.vue';
import MemberAvailability from './MemberAvailability.vue';
import MinistryMatching from './MinistryMatching.vue';

export default {
  name: 'MemberManagementTabs',
  components: {
    MemberSkills,
    MemberInterests,
    MemberSpiritualGifts,
    MemberAvailability,
    MinistryMatching
  },
  props: {
    memberId: {
      type: [Number, String],
      required: true
    },
    initialTab: {
      type: String,
      default: 'skills',
      validator: (value) => ['skills', 'interests', 'spiritual-gifts', 'availability', 'matching'].includes(value)
    }
  },
  data() {
    return {
      activeTab: this.initialTab
    };
  },
  computed: {
    canManageSkills() {
      return this.hasPermission('manage_member_skills');
    },
    canManageInterests() {
      return this.hasPermission('manage_member_interests');
    },
    canManageSpiritualGifts() {
      return this.hasPermission('manage_spiritual_gifts');
    },
    canManageAvailability() {
      return this.hasPermission('manage_member_availability') || this.isSelf();
    }
  },
  methods: {
    hasPermission(permission) {
      // Check if the current user has the specified permission
      // This assumes that you have a global user object with permissions
      return this.$store.getters.hasPermission(permission);
    },
    isSelf() {
      // Check if the current user is viewing their own profile
      return this.$store.getters.currentUser && 
             this.$store.getters.currentUser.member_id === parseInt(this.memberId);
    }
  }
};
</script>

<style scoped>
.member-management-tabs .nav-tabs .nav-link {
  color: #495057;
}

.member-management-tabs .nav-tabs .nav-link.active {
  color: #007bff;
  font-weight: 500;
}

.tab-content {
  padding-top: 1rem;
}
</style>
