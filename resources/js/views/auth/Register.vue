<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <div class="mb-4 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Create an Account</h1>
        <p class="text-gray-600 mt-1">Join the Church Management System</p>
      </div>
      
      <form @submit.prevent="register">
        <!-- Name -->
        <div class="mb-4">
          <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            required
            autocomplete="name"
          />
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            required
            autocomplete="email"
          />
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            required
            autocomplete="new-password"
          />
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
          <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            required
            autocomplete="new-password"
          />
        </div>

        <div class="flex items-center justify-between mb-4">
          <button
            type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
            :disabled="loading"
          >
            <span v-if="loading">Processing...</span>
            <span v-else>Register</span>
          </button>
        </div>
        
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Already have an account?
            <router-link :to="{ name: 'login' }" class="text-blue-500 hover:text-blue-700">Log in</router-link>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from 'vue-toastification';

export default {
  name: 'Register',
  
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const toast = useToast();
    const loading = ref(false);
    
    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    });
    
    const register = async () => {
      loading.value = true;
      
      try {
        // This is a placeholder - actual implementation would use the auth store
        await authStore.register(form.value);
        toast.success('Registration successful! Please log in.');
        router.push({ name: 'login' });
      } catch (error) {
        toast.error('Registration failed: ' + (error.response?.data?.message || 'Unknown error'));
      } finally {
        loading.value = false;
      }
    };
    
    return {
      form,
      loading,
      register
    };
  }
};
</script>
