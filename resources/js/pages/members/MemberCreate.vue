<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link 
        to="/members" 
        class="text-blue-600 hover:text-blue-800 mr-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Members
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">Add New Member</h1>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="saveMember">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Personal Information -->
          <div class="md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h2>
          </div>

          <!-- First Name -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">
              First Name *
            </label>
            <input 
              id="first_name" 
              v-model="member.first_name" 
              type="text" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.first_name" class="text-red-500 text-xs mt-1">
              {{ validationErrors.first_name[0] }}
            </p>
          </div>

          <!-- Last Name -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">
              Last Name *
            </label>
            <input 
              id="last_name" 
              v-model="member.last_name" 
              type="text" 
              required
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.last_name" class="text-red-500 text-xs mt-1">
              {{ validationErrors.last_name[0] }}
            </p>
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="gender">
              Gender
            </label>
            <select 
              id="gender" 
              v-model="member.gender" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <p v-if="validationErrors.gender" class="text-red-500 text-xs mt-1">
              {{ validationErrors.gender[0] }}
            </p>
          </div>

          <!-- Date of Birth -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="date_of_birth">
              Date of Birth
            </label>
            <input 
              id="date_of_birth" 
              v-model="member.date_of_birth" 
              type="date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.date_of_birth" class="text-red-500 text-xs mt-1">
              {{ validationErrors.date_of_birth[0] }}
            </p>
          </div>

          <!-- Contact Information -->
          <div class="md:col-span-2 mt-4">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Contact Information</h2>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
              Email
            </label>
            <input 
              id="email" 
              v-model="member.email" 
              type="email" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.email" class="text-red-500 text-xs mt-1">
              {{ validationErrors.email[0] }}
            </p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
              Phone
            </label>
            <input 
              id="phone" 
              v-model="member.phone" 
              type="tel" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.phone" class="text-red-500 text-xs mt-1">
              {{ validationErrors.phone[0] }}
            </p>
          </div>

          <!-- Address -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
              Address
            </label>
            <input 
              id="address" 
              v-model="member.address" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.address" class="text-red-500 text-xs mt-1">
              {{ validationErrors.address[0] }}
            </p>
          </div>

          <!-- City -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
              City
            </label>
            <input 
              id="city" 
              v-model="member.city" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.city" class="text-red-500 text-xs mt-1">
              {{ validationErrors.city[0] }}
            </p>
          </div>

          <!-- State -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="state">
              State
            </label>
            <input 
              id="state" 
              v-model="member.state" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.state" class="text-red-500 text-xs mt-1">
              {{ validationErrors.state[0] }}
            </p>
          </div>

          <!-- Zip -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="zip">
              Zip/Postal Code
            </label>
            <input 
              id="zip" 
              v-model="member.zip" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.zip" class="text-red-500 text-xs mt-1">
              {{ validationErrors.zip[0] }}
            </p>
          </div>

          <!-- Country -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="country">
              Country
            </label>
            <input 
              id="country" 
              v-model="member.country" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.country" class="text-red-500 text-xs mt-1">
              {{ validationErrors.country[0] }}
            </p>
          </div>

          <!-- Membership Information -->
          <div class="md:col-span-2 mt-4">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Membership Information</h2>
          </div>

          <!-- Membership Status -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="membership_status">
              Membership Status
            </label>
            <select 
              id="membership_status" 
              v-model="member.membership_status" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="transferred">Transferred</option>
            </select>
            <p v-if="validationErrors.membership_status" class="text-red-500 text-xs mt-1">
              {{ validationErrors.membership_status[0] }}
            </p>
          </div>

          <!-- Membership Date -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="membership_date">
              Membership Date
            </label>
            <input 
              id="membership_date" 
              v-model="member.membership_date" 
              type="date" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.membership_date" class="text-red-500 text-xs mt-1">
              {{ validationErrors.membership_date[0] }}
            </p>
          </div>

          <!-- Family -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="family_id">
              Family
            </label>
            <select 
              id="family_id" 
              v-model="member.family_id" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
              <option value="">No Family</option>
              <option v-for="family in families" :key="family.id" :value="family.id">
                {{ family.name }}
              </option>
            </select>
            <p v-if="validationErrors.family_id" class="text-red-500 text-xs mt-1">
              {{ validationErrors.family_id[0] }}
            </p>
          </div>

          <!-- Profile Photo -->
          <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_photo">
              Profile Photo
            </label>
            <input 
              id="profile_photo" 
              type="file" 
              accept="image/*"
              @change="handleFileUpload"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <p v-if="validationErrors.profile_photo" class="text-red-500 text-xs mt-1">
              {{ validationErrors.profile_photo[0] }}
            </p>
          </div>

          <!-- Preview Image -->
          <div v-if="imagePreview" class="md:col-span-2">
            <img :src="imagePreview" alt="Profile Preview" class="h-40 w-40 object-cover rounded-full mx-auto">
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end">
          <button 
            type="button" 
            @click="$router.push('/members')" 
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            :disabled="saving"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Save Member</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'MemberCreate',
  data() {
    return {
      member: {
        first_name: '',
        last_name: '',
        gender: '',
        date_of_birth: '',
        phone: '',
        email: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        profile_photo: null,
        membership_status: 'pending',
        membership_date: '',
        family_id: '',
        custom_fields: {}
      },
      families: [],
      saving: false,
      error: null,
      validationErrors: {},
      imagePreview: null
    };
  },
  created() {
    this.fetchFamilies();
  },
  methods: {
    async fetchFamilies() {
      try {
        const response = await axios.get('/families', {
          params: { per_page: 100 } // Get a large number of families for the dropdown
        });
        
        if (response.data.status === 'success') {
          this.families = response.data.data.data;
        }
      } catch (error) {
        this.error = 'Failed to load families. Please refresh the page and try again.';
      }
    },
    
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      // Store the file for form submission
      this.member.profile_photo = file;
      
      // Create a preview
      const reader = new FileReader();
      reader.onload = e => {
        this.imagePreview = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    
    async saveMember() {
      this.saving = true;
      this.error = null;
      this.validationErrors = {};
      
      try {
        // Create form data for file upload
        const formData = new FormData();
        
        // Add all member fields to the form data
        Object.keys(this.member).forEach(key => {
          if (this.member[key] !== null && this.member[key] !== '') {
            if (key === 'custom_fields' && Object.keys(this.member.custom_fields).length > 0) {
              formData.append(key, JSON.stringify(this.member.custom_fields));
            } else {
              formData.append(key, this.member[key]);
            }
          }
        });
        
        const response = await axios.post('/members', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data.status === 'success') {
          // Redirect to the member list with a success message
          this.$router.push({
            path: '/members',
            query: { 
              message: 'Member created successfully',
              type: 'success'
            }
          });
        }
      } catch (error) {
        if (error.response?.status === 422) {
          // Validation errors
          this.validationErrors = error.response.data.errors || {};
          this.error = 'Please correct the errors in the form.';
        } else {
          this.error = error.response?.data?.message || 'An error occurred while saving the member.';
        }
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
