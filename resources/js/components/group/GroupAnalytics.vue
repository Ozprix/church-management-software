<template>
  <div class="group-analytics">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Group Analytics</h5>
        <div>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" :class="{ active: timeRange === 'month' }" @click="setTimeRange('month')">
              Month
            </button>
            <button class="btn btn-sm btn-outline-primary" :class="{ active: timeRange === 'quarter' }" @click="setTimeRange('quarter')">
              Quarter
            </button>
            <button class="btn btn-sm btn-outline-primary" :class="{ active: timeRange === 'year' }" @click="setTimeRange('year')">
              Year
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else>
          <!-- Summary Cards -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card h-100 border-0 bg-light">
                <div class="card-body text-center">
                  <div class="display-4 fw-bold text-primary mb-2">{{ stats.totalMembers }}</div>
                  <div class="text-muted">Total Members</div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card h-100 border-0 bg-light">
                <div class="card-body text-center">
                  <div class="display-4 fw-bold text-success mb-2">{{ stats.avgAttendance }}%</div>
                  <div class="text-muted">Average Attendance</div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card h-100 border-0 bg-light">
                <div class="card-body text-center">
                  <div class="display-4 fw-bold text-info mb-2">{{ stats.newMembers }}</div>
                  <div class="text-muted">New Members</div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card h-100 border-0 bg-light">
                <div class="card-body text-center">
                  <div class="display-4 fw-bold text-warning mb-2">{{ stats.totalEvents }}</div>
                  <div class="text-muted">Total Events</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Attendance Trends -->
          <div class="row mb-4">
            <div class="col-md-8">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Attendance Trends</h6>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="position: relative; height:250px;">
                    <!-- Placeholder for chart - in a real implementation, use a chart library like Chart.js -->
                    <div class="chart-placeholder d-flex align-items-center justify-content-center h-100 border rounded">
                      <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>Attendance trend visualization would appear here</p>
                        <p class="small">Using actual attendance data from the database</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Member Engagement</h6>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="position: relative; height:250px;">
                    <!-- Placeholder for chart - in a real implementation, use a chart library like Chart.js -->
                    <div class="chart-placeholder d-flex align-items-center justify-content-center h-100 border rounded">
                      <div class="text-center text-muted">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <p>Engagement metrics would appear here</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Growth & Demographics -->
          <div class="row">
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Growth Over Time</h6>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="position: relative; height:200px;">
                    <!-- Placeholder for chart - in a real implementation, use a chart library like Chart.js -->
                    <div class="chart-placeholder d-flex align-items-center justify-content-center h-100 border rounded">
                      <div class="text-center text-muted">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <p>Growth visualization would appear here</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <h6 class="mb-0">Member Demographics</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h6 class="text-muted mb-3">Age Distribution</h6>
                      <div class="mb-2" v-for="(value, label) in demographics.ageGroups" :key="'age-'+label">
                        <div class="d-flex justify-content-between mb-1">
                          <small>{{ label }}</small>
                          <small class="text-muted">{{ value }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar bg-info" :style="{ width: value + '%' }"></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <h6 class="text-muted mb-3">Gender Distribution</h6>
                      <div class="mb-2" v-for="(value, label) in demographics.gender" :key="'gender-'+label">
                        <div class="d-flex justify-content-between mb-1">
                          <small>{{ label }}</small>
                          <small class="text-muted">{{ value }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar" :class="label === 'Male' ? 'bg-primary' : 'bg-danger'" :style="{ width: value + '%' }"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
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
  name: 'GroupAnalytics',
  props: {
    groupId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      loading: false,
      timeRange: 'month', // month, quarter, year
      stats: {
        totalMembers: 0,
        avgAttendance: 0,
        newMembers: 0,
        totalEvents: 0
      },
      demographics: {
        ageGroups: {
          '18-24': 0,
          '25-34': 0,
          '35-44': 0,
          '45-54': 0,
          '55+': 0
        },
        gender: {
          'Male': 0,
          'Female': 0
        }
      },
      attendanceData: [],
      growthData: []
    };
  },
  mounted() {
    this.loadAnalyticsData();
  },
  methods: {
    async loadAnalyticsData() {
      this.loading = true;
      try {
        // In a real implementation, this would be an API call to fetch analytics data
        // For demonstration, we'll use mock data
        
        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Set mock data based on the selected time range
        this.setMockData();
      } catch (error) {
        console.error('Error loading analytics data:', error);
        this.$toast.error('Failed to load analytics data');
      } finally {
        this.loading = false;
      }
    },
    setTimeRange(range) {
      this.timeRange = range;
      this.loadAnalyticsData();
    },
    setMockData() {
      // Set different mock data based on the selected time range
      switch (this.timeRange) {
        case 'month':
          this.stats = {
            totalMembers: 24,
            avgAttendance: 78,
            newMembers: 3,
            totalEvents: 8
          };
          this.demographics = {
            ageGroups: {
              '18-24': 15,
              '25-34': 30,
              '35-44': 25,
              '45-54': 20,
              '55+': 10
            },
            gender: {
              'Male': 45,
              'Female': 55
            }
          };
          break;
        case 'quarter':
          this.stats = {
            totalMembers: 28,
            avgAttendance: 72,
            newMembers: 8,
            totalEvents: 24
          };
          this.demographics = {
            ageGroups: {
              '18-24': 18,
              '25-34': 28,
              '35-44': 22,
              '45-54': 18,
              '55+': 14
            },
            gender: {
              'Male': 48,
              'Female': 52
            }
          };
          break;
        case 'year':
          this.stats = {
            totalMembers: 35,
            avgAttendance: 68,
            newMembers: 15,
            totalEvents: 96
          };
          this.demographics = {
            ageGroups: {
              '18-24': 20,
              '25-34': 25,
              '35-44': 20,
              '45-54': 15,
              '55+': 20
            },
            gender: {
              'Male': 50,
              'Female': 50
            }
          };
          break;
      }
    },
    // In a real implementation, these methods would process actual data
    // and prepare it for visualization using a chart library
    prepareAttendanceData() {
      // Process attendance data for chart
    },
    prepareGrowthData() {
      // Process growth data for chart
    }
  }
};
</script>

<style scoped>
.chart-placeholder {
  background-color: #f8f9fa;
}

.progress {
  background-color: #e9ecef;
  border-radius: 10px;
}

.progress-bar {
  border-radius: 10px;
}

.card-body {
  padding: 1.25rem;
}

.display-4 {
  font-size: 2.5rem;
}
</style>
