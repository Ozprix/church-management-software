<template>
  <div class="ministry-matching">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ministry Matching</h5>
        <div>
          <button class="btn btn-sm btn-primary" @click="findMatches">
            <i class="fas fa-search"></i> Find Matches
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
          <div class="row mb-4">
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Skills</h6>
                </div>
                <div class="card-body">
                  <div v-if="memberSkills.length === 0" class="text-center py-3">
                    <p class="text-muted">No skills added yet.</p>
                  </div>
                  <div v-else>
                    <div v-for="skill in memberSkills" :key="skill.id" class="mb-2">
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <i class="fas fa-tools text-primary"></i>
                        </div>
                        <div>
                          <div class="fw-bold">{{ skill.name }}</div>
                          <div class="small text-muted">{{ formatProficiencyLevel(skill.pivot.proficiency_level) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Interests</h6>
                </div>
                <div class="card-body">
                  <div v-if="memberInterests.length === 0" class="text-center py-3">
                    <p class="text-muted">No interests added yet.</p>
                  </div>
                  <div v-else>
                    <div v-for="interest in memberInterests" :key="interest.id" class="mb-2">
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <i class="fas fa-heart text-danger"></i>
                        </div>
                        <div>
                          <div class="fw-bold">{{ interest.name }}</div>
                          <div class="small text-muted">{{ formatInterestLevel(interest.pivot.interest_level) }} Interest</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Spiritual Gifts</h6>
                </div>
                <div class="card-body">
                  <div v-if="memberGifts.length === 0" class="text-center py-3">
                    <p class="text-muted">No spiritual gifts added yet.</p>
                  </div>
                  <div v-else>
                    <div v-for="gift in memberGifts" :key="gift.id" class="mb-2">
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <i class="fas fa-dove text-info"></i>
                        </div>
                        <div>
                          <div class="fw-bold">{{ gift.name }}</div>
                          <div class="small text-muted">{{ formatStrengthLevel(gift.pivot.strength_level) }} Strength</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <h6>Ministry Opportunities</h6>
            <div v-if="loading" class="text-center py-3">
              <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2">Finding matching ministries...</span>
            </div>
            <div v-else-if="ministryMatches.length === 0" class="text-center py-3">
              <p class="text-muted">No matching ministries found. Try adjusting the filters or adding more skills, interests, and spiritual gifts.</p>
            </div>
            <div v-else>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Ministry</th>
                      <th>Match Score</th>
                      <th>Skills Match</th>
                      <th>Interests Match</th>
                      <th>Gifts Match</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="match in ministryMatches" :key="match.id">
                      <td>
                        <div class="fw-bold">{{ match.name }}</div>
                        <div class="small text-muted">{{ match.description }}</div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="progress flex-grow-1" style="height: 8px;">
                            <div 
                              class="progress-bar" 
                              :class="getMatchScoreClass(match.match_score)"
                              :style="{ width: `${match.match_score}%` }"
                            ></div>
                          </div>
                          <span class="ms-2">{{ match.match_score }}%</span>
                        </div>
                      </td>
                      <td>{{ match.skills_match }}%</td>
                      <td>{{ match.interests_match }}%</td>
                      <td>{{ match.gifts_match }}%</td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" @click="viewMinistryDetails(match)">
                          <i class="fas fa-info-circle"></i> Details
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MinistryMatching',
  props: {
    memberId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      loading: false,
      memberSkills: [],
      memberInterests: [],
      memberGifts: [],
      ministryMatches: []
    };
  },
  mounted() {
    this.loadMemberData();
  },
  methods: {
    async loadMemberData() {
      this.loading = true;
      try {
        // Load member skills
        const skillsResponse = await axios.get(`/api/members/${this.memberId}/skills`);
        this.memberSkills = skillsResponse.data.data;
        
        // Load member interests
        const interestsResponse = await axios.get(`/api/members/${this.memberId}/interests`);
        this.memberInterests = interestsResponse.data.data;
        
        // Load member spiritual gifts
        const giftsResponse = await axios.get(`/api/members/${this.memberId}/spiritual-gifts`);
        this.memberGifts = giftsResponse.data.data;
        
        // Find initial matches
        this.findMatches();
      } catch (error) {
        console.error('Error loading member data:', error);
        this.$toast.error('Failed to load member data');
      } finally {
        this.loading = false;
      }
    },
    async findMatches() {
      this.loading = true;
      try {
        // This is a placeholder for the actual API call
        // In a real implementation, this would call an endpoint that matches
        // the member's skills, interests, and gifts with ministry opportunities
        
        // Simulating API response with mock data
        setTimeout(() => {
          this.ministryMatches = [
            {
              id: 1,
              name: 'Worship Team',
              description: 'Music ministry for Sunday services',
              match_score: 85,
              skills_match: 90,
              interests_match: 80,
              gifts_match: 85
            },
            {
              id: 2,
              name: 'Children\'s Ministry',
              description: 'Teaching and caring for children',
              match_score: 75,
              skills_match: 70,
              interests_match: 85,
              gifts_match: 70
            },
            {
              id: 3,
              name: 'Welcome Team',
              description: 'Greeting and assisting visitors',
              match_score: 65,
              skills_match: 60,
              interests_match: 75,
              gifts_match: 60
            }
          ];
          this.loading = false;
        }, 1000);
      } catch (error) {
        console.error('Error finding ministry matches:', error);
        this.$toast.error('Failed to find ministry matches');
        this.loading = false;
      }
    },
    viewMinistryDetails(ministry) {
      // This would navigate to or show a modal with ministry details
      console.log('View ministry details:', ministry);
      this.$toast.info(`Viewing details for ${ministry.name}`);
    },
    formatProficiencyLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    },
    formatInterestLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    },
    formatStrengthLevel(level) {
      return level.charAt(0).toUpperCase() + level.slice(1);
    },
    getMatchScoreClass(score) {
      if (score >= 80) return 'bg-success';
      if (score >= 60) return 'bg-info';
      if (score >= 40) return 'bg-warning';
      return 'bg-danger';
    }
  }
};
</script>

<style scoped>
.ministry-matching .progress {
  border-radius: 10px;
  background-color: #f0f0f0;
}

.ministry-matching .progress-bar {
  border-radius: 10px;
}
</style>
