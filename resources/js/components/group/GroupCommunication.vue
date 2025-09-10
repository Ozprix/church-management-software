<template>
  <div class="group-communication">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Group Communication</h5>
        <div>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" :class="{ active: activeTab === 'messages' }" @click="setActiveTab('messages')">
              Messages
            </button>
            <button class="btn btn-sm btn-outline-primary" :class="{ active: activeTab === 'announcements' }" @click="setActiveTab('announcements')">
              Announcements
            </button>
            <button class="btn btn-sm btn-outline-primary" :class="{ active: activeTab === 'prayer' }" @click="setActiveTab('prayer')">
              Prayer Requests
            </button>
            <button class="btn btn-sm btn-outline-primary" :class="{ active: activeTab === 'documents' }" @click="setActiveTab('documents')">
              Documents
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Messages Tab -->
        <div v-if="activeTab === 'messages'">
          <div class="d-flex justify-content-between mb-3">
            <h6>Group Messages</h6>
            <button class="btn btn-sm btn-primary" @click="showNewMessageModal">
              <i class="fas fa-plus"></i> New Message
            </button>
          </div>
          
          <div class="message-list">
            <div v-if="messages.length === 0" class="text-center py-4">
              <p class="text-muted">No messages yet. Start a conversation!</p>
            </div>
            <div v-else class="card mb-3" v-for="message in messages" :key="message.id">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      <img v-if="message.sender.profile_photo" :src="message.sender.profile_photo" alt="Profile" class="rounded-circle">
                      <div v-else class="avatar-placeholder">
                        {{ getInitials(message.sender.first_name, message.sender.last_name) }}
                      </div>
                    </div>
                    <div>
                      <div class="fw-bold">{{ message.sender.first_name }} {{ message.sender.last_name }}</div>
                      <div class="small text-muted">{{ formatDate(message.created_at) }}</div>
                    </div>
                  </div>
                  <div class="dropdown" v-if="message.sender.id === currentUserId">
                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" @click.prevent="editMessage(message)">Edit</a></li>
                      <li><a class="dropdown-item text-danger" href="#" @click.prevent="deleteMessage(message)">Delete</a></li>
                    </ul>
                  </div>
                </div>
                <div class="message-content">
                  {{ message.content }}
                </div>
                <div v-if="message.attachments && message.attachments.length > 0" class="mt-2">
                  <div v-for="attachment in message.attachments" :key="attachment.id" class="attachment">
                    <a :href="attachment.url" target="_blank" class="d-flex align-items-center">
                      <i class="fas fa-paperclip me-2"></i>
                      <span>{{ attachment.name }}</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Announcements Tab -->
        <div v-if="activeTab === 'announcements'">
          <div class="d-flex justify-content-between mb-3">
            <h6>Group Announcements</h6>
            <button class="btn btn-sm btn-primary" @click="showNewAnnouncementModal" v-if="canManageCommunications">
              <i class="fas fa-plus"></i> New Announcement
            </button>
          </div>
          
          <div class="announcement-list">
            <div v-if="announcements.length === 0" class="text-center py-4">
              <p class="text-muted">No announcements yet.</p>
            </div>
            <div v-else class="card mb-3" v-for="announcement in announcements" :key="announcement.id">
              <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">{{ announcement.title }}</h6>
                  <span class="badge bg-primary">{{ formatDate(announcement.created_at) }}</span>
                </div>
              </div>
              <div class="card-body">
                <p>{{ announcement.content }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Prayer Requests Tab -->
        <div v-if="activeTab === 'prayer'">
          <div class="d-flex justify-content-between mb-3">
            <h6>Prayer Requests</h6>
            <button class="btn btn-sm btn-primary" @click="showNewPrayerRequestModal">
              <i class="fas fa-plus"></i> New Prayer Request
            </button>
          </div>
          
          <div class="prayer-list">
            <div v-if="prayerRequests.length === 0" class="text-center py-4">
              <p class="text-muted">No prayer requests yet.</p>
            </div>
            <div v-else class="card mb-3" v-for="prayer in prayerRequests" :key="prayer.id">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2">
                      <img v-if="prayer.requester.profile_photo" :src="prayer.requester.profile_photo" alt="Profile" class="rounded-circle">
                      <div v-else class="avatar-placeholder">
                        {{ getInitials(prayer.requester.first_name, prayer.requester.last_name) }}
                      </div>
                    </div>
                    <div>
                      <div class="fw-bold">{{ prayer.requester.first_name }} {{ prayer.requester.last_name }}</div>
                      <div class="small text-muted">{{ formatDate(prayer.created_at) }}</div>
                    </div>
                  </div>
                  <span class="badge" :class="prayer.is_answered ? 'bg-success' : 'bg-warning'">
                    {{ prayer.is_answered ? 'Answered' : 'Ongoing' }}
                  </span>
                </div>
                <div class="prayer-content">
                  {{ prayer.request }}
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                  <button class="btn btn-sm btn-outline-primary" @click="togglePrayerStatus(prayer)">
                    {{ prayer.is_answered ? 'Mark as Ongoing' : 'Mark as Answered' }}
                  </button>
                  <div>
                    <span class="me-2">
                      <i class="fas fa-praying-hands"></i> {{ prayer.prayer_count }}
                    </span>
                    <button class="btn btn-sm btn-link text-primary" @click="incrementPrayerCount(prayer)">
                      I Prayed
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Documents Tab -->
        <div v-if="activeTab === 'documents'">
          <div class="d-flex justify-content-between mb-3">
            <h6>Group Documents</h6>
            <button class="btn btn-sm btn-primary" @click="showUploadDocumentModal" v-if="canManageDocuments">
              <i class="fas fa-upload"></i> Upload Document
            </button>
          </div>
          
          <div class="document-list">
            <div v-if="documents.length === 0" class="text-center py-4">
              <p class="text-muted">No documents yet.</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="document in documents" :key="document.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <i :class="getDocumentIcon(document.type)" class="me-2"></i>
                        <span>{{ document.name }}</span>
                      </div>
                    </td>
                    <td>{{ document.type.toUpperCase() }}</td>
                    <td>{{ document.uploader.first_name }} {{ document.uploader.last_name }}</td>
                    <td>{{ formatDate(document.created_at) }}</td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <a :href="document.url" target="_blank" class="btn btn-outline-primary">
                          <i class="fas fa-download"></i>
                        </a>
                        <button class="btn btn-outline-danger" @click="deleteDocument(document)" v-if="canManageDocuments">
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
    </div>
  </div>
</template>

<script>
export default {
  name: 'GroupCommunication',
  props: {
    groupId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      activeTab: 'messages',
      messages: [],
      announcements: [],
      prayerRequests: [],
      documents: [],
      currentUserId: this.$store.getters['auth/currentUser']?.id
    };
  },
  computed: {
    canManageCommunications() {
      return this.$store.getters['auth/hasPermission']('send_communications') || 
             this.isGroupLeader();
    },
    canManageDocuments() {
      return this.$store.getters['auth/hasPermission']('manage_documents') || 
             this.isGroupLeader();
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    setActiveTab(tab) {
      this.activeTab = tab;
    },
    async loadData() {
      // In a real implementation, these would be API calls
      // For demonstration, we'll use mock data
      this.loadMockData();
    },
    loadMockData() {
      // Mock messages
      this.messages = [
        {
          id: 1,
          content: 'Hello everyone! Looking forward to our meeting this weekend.',
          created_at: '2025-05-20T14:30:00',
          sender: {
            id: 1,
            first_name: 'John',
            last_name: 'Doe',
            profile_photo: null
          },
          attachments: []
        },
        {
          id: 2,
          content: 'Don\'t forget to bring your study materials!',
          created_at: '2025-05-21T09:15:00',
          sender: {
            id: 2,
            first_name: 'Jane',
            last_name: 'Smith',
            profile_photo: null
          },
          attachments: [
            {
              id: 1,
              name: 'study_guide.pdf',
              url: '#'
            }
          ]
        }
      ];
      
      // Mock announcements
      this.announcements = [
        {
          id: 1,
          title: 'Schedule Change',
          content: 'Our meeting time has changed to 7:00 PM starting next week.',
          created_at: '2025-05-19T10:00:00'
        },
        {
          id: 2,
          title: 'Summer Retreat',
          content: 'Registration for the summer retreat is now open. Please sign up by June 1st.',
          created_at: '2025-05-15T16:45:00'
        }
      ];
      
      // Mock prayer requests
      this.prayerRequests = [
        {
          id: 1,
          request: 'Please pray for my mother who is in the hospital.',
          is_answered: false,
          prayer_count: 5,
          created_at: '2025-05-22T08:20:00',
          requester: {
            id: 3,
            first_name: 'Michael',
            last_name: 'Johnson',
            profile_photo: null
          }
        },
        {
          id: 2,
          request: 'Thankful for a successful job interview!',
          is_answered: true,
          prayer_count: 8,
          created_at: '2025-05-18T12:10:00',
          requester: {
            id: 4,
            first_name: 'Sarah',
            last_name: 'Williams',
            profile_photo: null
          }
        }
      ];
      
      // Mock documents
      this.documents = [
        {
          id: 1,
          name: 'Meeting Minutes - May 15',
          type: 'pdf',
          url: '#',
          created_at: '2025-05-16T09:30:00',
          uploader: {
            id: 2,
            first_name: 'Jane',
            last_name: 'Smith'
          }
        },
        {
          id: 2,
          name: 'Bible Study Guide',
          type: 'docx',
          url: '#',
          created_at: '2025-05-10T14:20:00',
          uploader: {
            id: 1,
            first_name: 'John',
            last_name: 'Doe'
          }
        },
        {
          id: 3,
          name: 'Group Photo',
          type: 'jpg',
          url: '#',
          created_at: '2025-05-05T16:45:00',
          uploader: {
            id: 3,
            first_name: 'Michael',
            last_name: 'Johnson'
          }
        }
      ];
    },
    showNewMessageModal() {
      // In a real implementation, this would show a modal for creating a new message
      console.log('Show new message modal');
    },
    showNewAnnouncementModal() {
      // In a real implementation, this would show a modal for creating a new announcement
      console.log('Show new announcement modal');
    },
    showNewPrayerRequestModal() {
      // In a real implementation, this would show a modal for creating a new prayer request
      console.log('Show new prayer request modal');
    },
    showUploadDocumentModal() {
      // In a real implementation, this would show a modal for uploading a document
      console.log('Show upload document modal');
    },
    editMessage(message) {
      // In a real implementation, this would allow editing a message
      console.log('Edit message:', message);
    },
    deleteMessage(message) {
      // In a real implementation, this would delete a message
      console.log('Delete message:', message);
    },
    togglePrayerStatus(prayer) {
      // In a real implementation, this would toggle the prayer request status
      prayer.is_answered = !prayer.is_answered;
      console.log('Toggle prayer status:', prayer);
    },
    incrementPrayerCount(prayer) {
      // In a real implementation, this would increment the prayer count
      prayer.prayer_count++;
      console.log('Increment prayer count:', prayer);
    },
    deleteDocument(document) {
      // In a real implementation, this would delete a document
      console.log('Delete document:', document);
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    getInitials(firstName, lastName) {
      return `${firstName ? firstName.charAt(0) : ''}${lastName ? lastName.charAt(0) : ''}`;
    },
    getDocumentIcon(type) {
      const iconMap = {
        'pdf': 'fas fa-file-pdf text-danger',
        'docx': 'fas fa-file-word text-primary',
        'xlsx': 'fas fa-file-excel text-success',
        'pptx': 'fas fa-file-powerpoint text-warning',
        'jpg': 'fas fa-file-image text-info',
        'png': 'fas fa-file-image text-info',
        'txt': 'fas fa-file-alt text-secondary'
      };
      
      return iconMap[type] || 'fas fa-file text-secondary';
    },
    isGroupLeader() {
      // In a real implementation, this would check if the current user is a group leader
      return true; // Placeholder
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

.message-content, .prayer-content {
  white-space: pre-line;
}

.attachment {
  background-color: #f8f9fa;
  padding: 0.5rem;
  border-radius: 0.25rem;
  margin-bottom: 0.5rem;
}

.attachment a {
  color: inherit;
  text-decoration: none;
}

.attachment a:hover {
  text-decoration: underline;
}
</style>
