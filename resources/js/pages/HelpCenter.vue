<template>
  <div class="help-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Help Center</h1>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Find answers to common questions and learn how to use the system
          </p>
        </div>
        
        <!-- Main Content -->
        <div class="p-6">
          <!-- Search Bar -->
          <div class="mb-8">
            <div class="relative">
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search for help topics..." 
                class="block w-full pl-10 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-sm placeholder-neutral-500 dark:placeholder-neutral-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-neutral-400 dark:text-neutral-500"></i>
              </div>
            </div>
          </div>
          
          <!-- Tabs -->
          <div class="mb-6">
            <div class="border-b border-neutral-200 dark:border-neutral-700">
              <nav class="flex -mb-px">
                <button 
                  v-for="tab in tabs" 
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  class="py-3 px-4 text-sm font-medium border-b-2 whitespace-nowrap"
                  :class="activeTab === tab.id ? 
                    'border-primary-500 text-primary-600 dark:text-primary-400' : 
                    'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300 dark:hover:border-neutral-600'"
                >
                  <i :class="[tab.icon, 'mr-2']"></i>
                  {{ tab.name }}
                </button>
              </nav>
            </div>
          </div>
          
          <!-- Tab Content -->
          <div>
            <!-- Getting Started Tab -->
            <div v-if="activeTab === 'getting-started'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div 
                  v-for="guide in filteredGuides" 
                  :key="guide.id"
                  class="bg-neutral-50 dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-300"
                >
                  <div class="p-4">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">{{ guide.title }}</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ guide.description }}</p>
                    <button 
                      @click="openGuide(guide)"
                      class="inline-flex items-center text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                    >
                      Learn more
                      <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- FAQ Tab -->
            <div v-else-if="activeTab === 'faq'" class="space-y-4">
              <div 
                v-for="(faq, index) in filteredFaqs" 
                :key="index"
                class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden"
              >
                <button 
                  @click="toggleFaq(index)"
                  class="w-full flex justify-between items-center p-4 text-left bg-neutral-50 dark:bg-neutral-900 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-200"
                >
                  <span class="text-neutral-900 dark:text-white font-medium">{{ faq.question }}</span>
                  <i :class="expandedFaqs[index] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-neutral-500 dark:text-neutral-400"></i>
                </button>
                <div 
                  v-show="expandedFaqs[index]"
                  class="p-4 bg-white dark:bg-neutral-800 border-t border-neutral-200 dark:border-neutral-700"
                >
                  <p class="text-neutral-600 dark:text-neutral-400">{{ faq.answer }}</p>
                </div>
              </div>
            </div>
            
            <!-- Video Tutorials Tab -->
            <div v-else-if="activeTab === 'videos'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div 
                  v-for="video in filteredVideos" 
                  :key="video.id"
                  class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-md transition-shadow duration-300"
                >
                  <div class="aspect-w-16 aspect-h-9 bg-neutral-200 dark:bg-neutral-700 relative">
                    <img :src="video.thumbnail" :alt="video.title" class="object-cover w-full h-full" />
                    <div class="absolute inset-0 flex items-center justify-center">
                      <button 
                        @click="playVideo(video)"
                        class="w-16 h-16 rounded-full bg-primary-600/80 hover:bg-primary-600 flex items-center justify-center text-white transition-colors duration-300"
                      >
                        <i class="fas fa-play text-xl"></i>
                      </button>
                    </div>
                  </div>
                  <div class="p-4">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-1">{{ video.title }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">{{ video.duration }}</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ video.description }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Support Tab -->
            <div v-else-if="activeTab === 'support'" class="space-y-6">
              <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 border border-neutral-200 dark:border-neutral-700">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-4">Contact Support</h3>
                <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                  Need help with something specific? Our support team is here to help.
                </p>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                    <input 
                      type="text" 
                      v-model="supportForm.name" 
                      class="block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-900 dark:text-white dark:bg-neutral-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                    <input 
                      type="email" 
                      v-model="supportForm.email" 
                      class="block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-900 dark:text-white dark:bg-neutral-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject</label>
                    <input 
                      type="text" 
                      v-model="supportForm.subject" 
                      class="block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-900 dark:text-white dark:bg-neutral-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Message</label>
                    <textarea 
                      v-model="supportForm.message" 
                      rows="4"
                      class="block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-900 dark:text-white dark:bg-neutral-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    ></textarea>
                  </div>
                  <div>
                    <button 
                      @click="submitSupportRequest"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                      Submit Request
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Guide Modal -->
    <Modal 
      v-if="selectedGuide"
      :title="selectedGuide.title"
      @close="selectedGuide = null"
    >
      <div class="prose dark:prose-invert max-w-none">
        <div v-html="selectedGuide.content"></div>
      </div>
    </Modal>
    
    <!-- Video Modal -->
    <Modal 
      v-if="selectedVideo"
      :title="selectedVideo.title"
      @close="selectedVideo = null"
      size="lg"
    >
      <div class="aspect-w-16 aspect-h-9">
        <iframe 
          :src="selectedVideo.embedUrl" 
          frameborder="0" 
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
          allowfullscreen
          class="w-full h-full"
        ></iframe>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';
import Modal from '../components/ui/Modal.vue';

// Services
const toast = useToast();

// Component state
const searchQuery = ref('');
const activeTab = ref('getting-started');
const expandedFaqs = ref([]);
const selectedGuide = ref(null);
const selectedVideo = ref(null);
const supportForm = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

// Tabs
const tabs = [
  { id: 'getting-started', name: 'Getting Started', icon: 'fas fa-play-circle' },
  { id: 'faq', name: 'FAQ', icon: 'fas fa-question-circle' },
  { id: 'videos', name: 'Video Tutorials', icon: 'fas fa-video' },
  { id: 'support', name: 'Support', icon: 'fas fa-headset' }
];

// Sample data - in a real app, this would come from an API
const guides = [
  {
    id: 1,
    title: 'Getting Started with the Dashboard',
    description: 'Learn how to navigate and customize your dashboard.',
    content: `
      <h2>Getting Started with the Dashboard</h2>
      <p>The dashboard is your central hub for managing your church. Here's how to make the most of it:</p>
      <h3>Navigating the Dashboard</h3>
      <p>The dashboard is divided into several widgets, each showing different aspects of your church's activities:</p>
      <ul>
        <li><strong>Stats Widget:</strong> Shows key metrics like total members, recent donations, and upcoming events.</li>
        <li><strong>Activity Feed:</strong> Displays recent actions and updates in the system.</li>
        <li><strong>Calendar Widget:</strong> Shows upcoming events and services.</li>
        <li><strong>Donation Trends:</strong> Visualizes donation patterns over time.</li>
      </ul>
      <h3>Customizing Your Dashboard</h3>
      <p>You can customize your dashboard to show the information that matters most to you:</p>
      <ol>
        <li>Click the "Edit Dashboard" button in the top right corner.</li>
        <li>Drag widgets to rearrange them.</li>
        <li>Click the gear icon on any widget to configure it or remove it.</li>
        <li>Use the "+" button to add new widgets.</li>
        <li>Click "Save Layout" when you're done.</li>
      </ol>
    `
  },
  {
    id: 2,
    title: 'Managing Members',
    description: 'Learn how to add, edit, and organize church members.',
    content: `
      <h2>Managing Members</h2>
      <p>The members module helps you maintain your church membership database.</p>
      <h3>Adding New Members</h3>
      <p>To add a new member:</p>
      <ol>
        <li>Navigate to the Members section from the main menu.</li>
        <li>Click the "Add Member" button.</li>
        <li>Fill out the required information (marked with *).</li>
        <li>Add optional details as needed.</li>
        <li>Click "Save" to create the member record.</li>
      </ol>
      <h3>Organizing Members into Groups</h3>
      <p>Groups help you organize members by ministry, activity, or any other category:</p>
      <ol>
        <li>Go to the "Groups" tab in the Members section.</li>
        <li>Click "Create Group" to add a new group.</li>
        <li>Add members to the group by selecting them from the list.</li>
        <li>Assign group leaders and set meeting schedules.</li>
        <li>Use the group dashboard to track attendance and activities.</li>
      </ol>
    `
  },
  {
    id: 3,
    title: 'Donation Management',
    description: 'Learn how to record and manage donations and pledges.',
    content: `
      <h2>Donation Management</h2>
      <p>The donations module helps you track financial contributions to your church.</p>
      <h3>Recording Donations</h3>
      <p>To record a new donation:</p>
      <ol>
        <li>Navigate to the Donations section from the main menu.</li>
        <li>Click the "Add Donation" button.</li>
        <li>Select the donor from the dropdown or add a new donor.</li>
        <li>Enter the donation amount and date.</li>
        <li>Select the donation category and payment method.</li>
        <li>Add any notes or reference numbers.</li>
        <li>Click "Save" to record the donation.</li>
      </ol>
      <h3>Managing Pledge Campaigns</h3>
      <p>Pledge campaigns help you track progress toward financial goals:</p>
      <ol>
        <li>Go to the "Campaigns" tab in the Donations section.</li>
        <li>Click "Create Campaign" to set up a new campaign.</li>
        <li>Define the campaign goal, start date, and end date.</li>
        <li>Add pledges from members.</li>
        <li>Track progress on the campaign dashboard.</li>
        <li>Generate reports on pledge fulfillment.</li>
      </ol>
    `
  },
  {
    id: 4,
    title: 'Event Management',
    description: 'Learn how to create and manage church events.',
    content: `
      <h2>Event Management</h2>
      <p>The events module helps you organize and manage church services, meetings, and special events.</p>
      <h3>Creating Events</h3>
      <p>To create a new event:</p>
      <ol>
        <li>Navigate to the Events section from the main menu.</li>
        <li>Click the "Add Event" button.</li>
        <li>Enter the event details, including title, date, time, and location.</li>
        <li>Add a description and any special instructions.</li>
        <li>Assign staff or volunteers to the event.</li>
        <li>Set up registration options if needed.</li>
        <li>Click "Save" to create the event.</li>
      </ol>
      <h3>Tracking Attendance</h3>
      <p>To track attendance for an event:</p>
      <ol>
        <li>Navigate to the event in the Events section.</li>
        <li>Click the "Attendance" tab.</li>
        <li>Use the check-in feature to mark attendees as they arrive.</li>
        <li>Alternatively, update attendance after the event by checking names on the list.</li>
        <li>View attendance reports to analyze trends over time.</li>
      </ol>
    `
  }
];

const faqs = [
  {
    question: 'How do I reset my password?',
    answer: 'To reset your password, click on the "Forgot Password" link on the login page. Enter your email address, and you will receive an email with instructions to reset your password.'
  },
  {
    question: 'Can I export donation data for tax purposes?',
    answer: 'Yes, you can export donation data for tax purposes. Go to the Donations section, click on "Export" in the top right corner, select the date range and export format (PDF, Excel, or CSV), and click "Export".'
  },
  {
    question: 'How do I add a family member to an existing family?',
    answer: 'To add a family member, go to the Members section, find the family, and click on "View Family". Then click "Add Family Member" and fill out the required information.'
  },
  {
    question: 'How do I set up recurring donations?',
    answer: 'To set up recurring donations, go to the Donations section, click "Add Donation", fill out the donation details, and check the "Make this a recurring donation" box. Then select the frequency (weekly, monthly, etc.) and set an end date if applicable.'
  },
  {
    question: 'Can I customize email templates for communications?',
    answer: 'Yes, you can customize email templates. Go to Settings > Communications > Email Templates. Select the template you want to edit, make your changes, and click "Save Template".'
  },
  {
    question: 'How do I generate attendance reports?',
    answer: 'To generate attendance reports, go to the Reports section, select "Attendance Report" from the list, choose the date range and event types you want to include, and click "Generate Report".'
  },
  {
    question: 'How do I add a new user to the system?',
    answer: 'To add a new user, go to Settings > Users > Add User. Fill out the user details, select their role and permissions, and click "Create User". The new user will receive an email with instructions to set up their password.'
  },
  {
    question: 'Can I import member data from another system?',
    answer: 'Yes, you can import member data. Go to Members > Import, download the CSV template, fill it with your data, and upload it back to the system. The import wizard will guide you through mapping fields and validating the data.'
  }
];

const videos = [
  {
    id: 1,
    title: 'Dashboard Overview',
    description: 'A quick tour of the dashboard and its features.',
    duration: '3:45',
    thumbnail: '/images/tutorials/dashboard-overview.jpg',
    embedUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ'
  },
  {
    id: 2,
    title: 'Adding and Managing Members',
    description: 'Learn how to add, edit, and organize church members.',
    duration: '5:12',
    thumbnail: '/images/tutorials/managing-members.jpg',
    embedUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ'
  },
  {
    id: 3,
    title: 'Donation Management',
    description: 'How to record donations and manage financial data.',
    duration: '4:30',
    thumbnail: '/images/tutorials/donation-management.jpg',
    embedUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ'
  },
  {
    id: 4,
    title: 'Creating and Managing Events',
    description: 'Learn how to set up events and track attendance.',
    duration: '6:18',
    thumbnail: '/images/tutorials/event-management.jpg',
    embedUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ'
  }
];

// Computed
const filteredGuides = computed(() => {
  if (!searchQuery.value) return guides;
  
  const query = searchQuery.value.toLowerCase();
  return guides.filter(guide => 
    guide.title.toLowerCase().includes(query) || 
    guide.description.toLowerCase().includes(query)
  );
});

const filteredFaqs = computed(() => {
  if (!searchQuery.value) return faqs;
  
  const query = searchQuery.value.toLowerCase();
  return faqs.filter(faq => 
    faq.question.toLowerCase().includes(query) || 
    faq.answer.toLowerCase().includes(query)
  );
});

const filteredVideos = computed(() => {
  if (!searchQuery.value) return videos;
  
  const query = searchQuery.value.toLowerCase();
  return videos.filter(video => 
    video.title.toLowerCase().includes(query) || 
    video.description.toLowerCase().includes(query)
  );
});

// Methods
const toggleFaq = (index) => {
  expandedFaqs.value[index] = !expandedFaqs.value[index];
};

const openGuide = (guide) => {
  selectedGuide.value = guide;
};

const playVideo = (video) => {
  selectedVideo.value = video;
};

const submitSupportRequest = () => {
  // Validate form
  if (!supportForm.value.name || !supportForm.value.email || !supportForm.value.subject || !supportForm.value.message) {
    toast.error('Please fill out all fields');
    return;
  }
  
  // In a real app, this would send the form data to an API
  toast.success('Support request submitted successfully');
  
  // Reset form
  supportForm.value = {
    name: '',
    email: '',
    subject: '',
    message: ''
  };
};

// Lifecycle hooks
onMounted(() => {
  // Initialize expandedFaqs array
  expandedFaqs.value = Array(faqs.length).fill(false);
});
</script>

<style scoped>
.aspect-w-16 {
  position: relative;
  padding-bottom: 56.25%;
}

.aspect-w-16 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.prose {
  max-width: 100%;
}
</style>
