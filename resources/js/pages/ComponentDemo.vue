<template>
  <div class="component-demo">
    <h1 class="text-3xl font-bold mb-6 text-neutral-900 dark:text-neutral-100">UI Component Demo</h1>
    
    <!-- Component Navigation -->
    <div class="mb-8 sticky top-0 z-10 bg-white dark:bg-neutral-900 py-4 border-b border-neutral-200 dark:border-neutral-800">
      <div class="flex flex-wrap gap-2">
        <Button @click="scrollToSection('buttons')" variant="outline" size="sm">Buttons</Button>
        <Button @click="scrollToSection('toast')" variant="outline" size="sm">Toast</Button>
        <Button @click="scrollToSection('modal')" variant="outline" size="sm">Modal</Button>
        <Button @click="scrollToSection('dropdown')" variant="outline" size="sm">Dropdown</Button>
        <Button @click="scrollToSection('tabs')" variant="outline" size="sm">Tabs</Button>
        <Button @click="scrollToSection('table')" variant="outline" size="sm">Table</Button>
        <Button @click="scrollToSection('form')" variant="outline" size="sm">Form</Button>
      </div>
    </div>
    
    <div class="grid grid-cols-1 gap-8">
      <!-- Button Demo -->
      <section id="buttons" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Buttons</h2>
        <Card>
          <div class="space-y-4">
            <div class="flex flex-wrap gap-2">
              <Button>Default</Button>
              <Button variant="primary">Primary</Button>
              <Button variant="secondary">Secondary</Button>
              <Button variant="success">Success</Button>
              <Button variant="warning">Warning</Button>
              <Button variant="error">Error</Button>
              <Button variant="info">Info</Button>
              <Button variant="outline">Outline</Button>
              <Button variant="ghost">Ghost</Button>
            </div>
            
            <div class="flex flex-wrap gap-2">
              <Button size="sm">Small</Button>
              <Button>Medium</Button>
              <Button size="lg">Large</Button>
            </div>
            
            <div class="flex flex-wrap gap-2">
              <Button loading>Loading</Button>
              <Button disabled>Disabled</Button>
              <Button icon>
                <template #icon>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                  </svg>
                </template>
                Icon Only
              </Button>
              <Button>
                <template #icon>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                  </svg>
                </template>
                With Icon
              </Button>
            </div>
          </div>
        </Card>
      </section>
      
      <!-- Toast Demo -->
      <section id="toast" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Toast Notifications</h2>
        <Card>
          <div class="flex flex-wrap gap-2">
            <Button @click="showDefaultToast">Default Toast</Button>
            <Button @click="showSuccessToast" variant="success">Success Toast</Button>
            <Button @click="showErrorToast" variant="error">Error Toast</Button>
            <Button @click="showWarningToast" variant="warning">Warning Toast</Button>
            <Button @click="showInfoToast" variant="info">Info Toast</Button>
          </div>
        </Card>
      </section>
      
      <!-- Modal Demo -->
      <section id="modal" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Modal</h2>
        <Card>
          <div class="flex flex-wrap gap-2">
            <Button @click="isModalOpen = true">Open Modal</Button>
            <Button @click="isFormModalOpen = true" variant="primary">Form Modal</Button>
            <Button @click="isConfirmModalOpen = true" variant="warning">Confirm Modal</Button>
          </div>
          
          <!-- Basic Modal -->
          <Modal v-model="isModalOpen" title="Example Modal">
            <p class="text-neutral-600 dark:text-neutral-300">
              This is a basic modal example. You can put any content here.
            </p>
            <p class="mt-4 text-neutral-600 dark:text-neutral-300">
              Modals are great for focusing user attention on a specific task or information.
            </p>
            
            <template #footer>
              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                <Button variant="primary" @click="isModalOpen = false">Confirm</Button>
              </div>
            </template>
          </Modal>
          
          <!-- Form Modal -->
          <Modal v-model="isFormModalOpen" title="Form Modal" size="lg">
            <Form @submit="handleFormSubmit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormItem name="firstName" label="First Name" required>
                  <Input v-model="formData.firstName" placeholder="Enter first name" />
                </FormItem>
                
                <FormItem name="lastName" label="Last Name" required>
                  <Input v-model="formData.lastName" placeholder="Enter last name" />
                </FormItem>
                
                <FormItem name="email" label="Email Address" required class="md:col-span-2">
                  <Input v-model="formData.email" type="email" placeholder="Enter email address" />
                </FormItem>
              </div>
              
              <template #footer>
                <div class="flex justify-end space-x-2">
                  <Button variant="outline" @click="isFormModalOpen = false">Cancel</Button>
                  <Button variant="primary" type="submit">Save</Button>
                </div>
              </template>
            </Form>
          </Modal>
          
          <!-- Confirm Modal -->
          <Modal v-model="isConfirmModalOpen" title="Confirm Action" size="sm">
            <p class="text-neutral-600 dark:text-neutral-300">
              Are you sure you want to perform this action? This cannot be undone.
            </p>
            
            <template #footer>
              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="isConfirmModalOpen = false">Cancel</Button>
                <Button variant="warning" @click="confirmAction">Confirm</Button>
              </div>
            </template>
          </Modal>
        </Card>
      </section>
      
      <!-- Dropdown Demo -->
      <section id="dropdown" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Dropdown</h2>
        <Card>
          <div class="flex flex-wrap gap-4">
            <Dropdown label="Default Dropdown">
              <a href="#" class="dropdown-item">Profile</a>
              <a href="#" class="dropdown-item">Settings</a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item text-red-600 dark:text-red-400">Logout</a>
            </Dropdown>
            
            <Dropdown variant="primary" label="Primary Dropdown">
              <a href="#" class="dropdown-item">Edit</a>
              <a href="#" class="dropdown-item">Duplicate</a>
              <a href="#" class="dropdown-item">Archive</a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item text-red-600 dark:text-red-400">Delete</a>
            </Dropdown>
            
            <Dropdown position="bottom-left" width="md">
              <template #trigger="{ open }">
                <Button variant="outline">
                  <span>Custom Trigger</span>
                  <svg
                    :class="['ml-2 h-5 w-5 transition-transform', open ? 'rotate-180' : '']"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </Button>
              </template>
              <div class="p-2">
                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">Signed in as</div>
                <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">user@example.com</div>
                <div class="dropdown-divider my-2"></div>
                <a href="#" class="dropdown-item">Account settings</a>
                <a href="#" class="dropdown-item">Support</a>
                <div class="dropdown-divider my-2"></div>
                <a href="#" class="dropdown-item text-red-600 dark:text-red-400">Sign out</a>
              </div>
            </Dropdown>
          </div>
        </Card>
      </section>
      
      <!-- Tabs Demo -->
      <section id="tabs" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Tabs</h2>
        <Card>
          <h3 class="text-lg font-medium mb-2 text-neutral-800 dark:text-neutral-200">Default Tabs</h3>
          <Tabs :tabs="defaultTabs" v-model="activeDefaultTab">
            <template #tab-0>
              <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                <p class="text-neutral-600 dark:text-neutral-300">This is the first tab content. You can put any content here.</p>
              </div>
            </template>
            <template #tab-1>
              <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                <p class="text-neutral-600 dark:text-neutral-300">This is the second tab with different content.</p>
              </div>
            </template>
            <template #tab-2>
              <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                <p class="text-neutral-600 dark:text-neutral-300">This is the third tab content.</p>
              </div>
            </template>
          </Tabs>
          
          <div class="mt-8">
            <h3 class="text-lg font-medium mb-2 text-neutral-800 dark:text-neutral-200">Pills Style</h3>
            <Tabs :tabs="pillTabs" variant="pills">
              <template #tab-0>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                  <p class="text-neutral-600 dark:text-neutral-300">Members tab content.</p>
                </div>
              </template>
              <template #tab-1>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                  <p class="text-neutral-600 dark:text-neutral-300">Groups tab content.</p>
                </div>
              </template>
              <template #tab-2>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                  <p class="text-neutral-600 dark:text-neutral-300">Events tab content.</p>
                </div>
              </template>
            </Tabs>
          </div>
        </Card>
      </section>
      
      <!-- Table Demo -->
      <section id="table" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Table</h2>
        <Card>
          <Table
            :columns="tableColumns"
            :data="tableData"
            selectable
            striped
            pagination
            :page-size="5"
          >
            <template #toolbar>
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">Members</h3>
                <Button variant="primary" size="sm">
                  <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                  </template>
                  Add Member
                </Button>
              </div>
            </template>
            
            <!-- Custom cell renderers -->
            <template #cell-status="{ value }">
              <Badge
                :variant="value === 'Active' ? 'success' : value === 'Inactive' ? 'error' : 'warning'"
                size="sm"
              >
                {{ value }}
              </Badge>
            </template>
            
            <template #actions="{ item }">
              <div class="flex space-x-2">
                <button class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
                <button class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </template>
          </Table>
        </Card>
      </section>
      
      <!-- Form Demo -->
      <section id="form" class="demo-section">
        <h2 class="text-2xl font-semibold mb-4 text-neutral-800 dark:text-neutral-200">Form</h2>
        <Card>
          <Form @submit="handleCompleteFormSubmit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormItem
                name="name"
                label="Full Name"
                required
                :rules="[v => !!v || 'Name is required']"
              >
                <Input v-model="completeForm.name" placeholder="Enter your full name" />
              </FormItem>
              
              <FormItem
                name="email"
                label="Email Address"
                required
                :rules="[
                  v => !!v || 'Email is required',
                  v => /^\S+@\S+\.\S+$/.test(v) || 'Email must be valid'
                ]"
              >
                <Input v-model="completeForm.email" type="email" placeholder="Enter your email address" />
              </FormItem>
            </div>
            
            <FormItem
              name="message"
              label="Message"
              help-text="Please provide any additional information"
            >
              <textarea
                v-model="completeForm.message"
                rows="4"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                placeholder="Enter your message"
              ></textarea>
            </FormItem>
            
            <div class="flex justify-end space-x-2">
              <Button variant="outline" type="button" @click="resetCompleteForm">Reset</Button>
              <Button variant="primary" type="submit">Submit</Button>
            </div>
          </Form>
        </Card>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Card from '../components/ui/Card.vue';
import Modal from '../components/ui/Modal.vue';
import Form from '../components/ui/Form.vue';
import FormItem from '../components/ui/FormItem.vue';
import Input from '../components/ui/Input.vue';
import Dropdown from '../components/ui/Dropdown.vue';
import Tabs from '../components/ui/Tabs.vue';
import Table from '../components/ui/Table.vue';
import Badge from '../components/ui/Badge.vue';
import toastService from '../utils/toastService';

// Toast demo
const showDefaultToast = () => {
  toastService.show('This is a default toast notification');
};

const showSuccessToast = () => {
  toastService.success('Operation completed successfully!', {
    title: 'Success',
    duration: 3000
  });
};

const showErrorToast = () => {
  toastService.error('An error occurred while processing your request.', {
    title: 'Error',
    duration: 5000
  });
};

const showWarningToast = () => {
  toastService.warning('Please be careful with this action.', {
    title: 'Warning',
    position: toastService.POSITIONS['top-right']
  });
};

const showInfoToast = () => {
  toastService.info('Here is some useful information.', {
    title: 'Information',
    position: toastService.POSITIONS['bottom-center']
  });
};

// Modal demo
const isModalOpen = ref(false);
const isFormModalOpen = ref(false);
const isConfirmModalOpen = ref(false);

// Form demo
const formData = ref({
  firstName: '',
  lastName: '',
  email: ''
});

const handleFormSubmit = (data) => {
  console.log('Form submitted:', data);
  toastService.success('Form submitted successfully!');
  isFormModalOpen.value = false;
};

const confirmAction = () => {
  toastService.success('Action confirmed!');
  isConfirmModalOpen.value = false;
};

// Navigation
const scrollToSection = (sectionId) => {
  const section = document.getElementById(sectionId);
  if (section) {
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

// Tabs demo
const activeDefaultTab = ref(0);
const defaultTabs = [
  { title: 'Tab 1', icon: null },
  { title: 'Tab 2', icon: null },
  { title: 'Tab 3', icon: null, disabled: true }
];

const pillTabs = [
  { 
    title: 'Members', 
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
          </svg>`,
    badge: '42'
  },
  { 
    title: 'Groups', 
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
          </svg>`,
    badge: '12'
  },
  { 
    title: 'Events', 
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
          </svg>`,
    badge: '8'
  }
];

// Table demo
const tableColumns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'phone', label: 'Phone', sortable: true },
  { key: 'status', label: 'Status', sortable: true, align: 'center' },
  { key: 'joinDate', label: 'Join Date', sortable: true, formatter: (value) => new Date(value).toLocaleDateString() }
];

const tableData = [
  { id: 1, name: 'John Doe', email: 'john@example.com', phone: '(555) 123-4567', status: 'Active', joinDate: '2023-01-15' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', phone: '(555) 987-6543', status: 'Active', joinDate: '2023-02-20' },
  { id: 3, name: 'Robert Johnson', email: 'robert@example.com', phone: '(555) 456-7890', status: 'Inactive', joinDate: '2022-11-05' },
  { id: 4, name: 'Emily Davis', email: 'emily@example.com', phone: '(555) 234-5678', status: 'Active', joinDate: '2023-03-10' },
  { id: 5, name: 'Michael Wilson', email: 'michael@example.com', phone: '(555) 876-5432', status: 'Pending', joinDate: '2023-04-25' },
  { id: 6, name: 'Sarah Brown', email: 'sarah@example.com', phone: '(555) 345-6789', status: 'Active', joinDate: '2022-12-18' },
  { id: 7, name: 'David Miller', email: 'david@example.com', phone: '(555) 765-4321', status: 'Inactive', joinDate: '2023-01-30' }
];

// Complete form demo
const completeForm = ref({
  name: '',
  email: '',
  message: ''
});

const handleCompleteFormSubmit = (data) => {
  console.log('Form submitted:', data);
  toastService.success('Form submitted successfully!');
  resetCompleteForm();
};

const resetCompleteForm = () => {
  completeForm.value = {
    name: '',
    email: '',
    message: ''
  };
};
</script>

<style scoped>
.component-demo {
  @apply max-w-6xl mx-auto px-4 py-8;
}

.demo-section {
  @apply p-4 rounded-lg;
}
</style>
