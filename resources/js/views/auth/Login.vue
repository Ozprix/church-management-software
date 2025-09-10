<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <div class="mb-4 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Church Management System</h1>
        <p class="text-gray-600">Sign in to your account</p>
      </div>
      
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ error }}</span>
      </div>
      
      <form @submit.prevent="login">
        <!-- Email Address -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            required
            autofocus
          />
        </div>

        <!-- Password -->
        <div class="mt-4">
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            required
          />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
          <label class="inline-flex items-center">
            <input
              type="checkbox"
              v-model="form.remember"
              class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            />
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
          </label>
        </div>

        <div class="flex items-center justify-end mt-4">
          <a class="underline text-sm text-gray-600 hover:text-gray-900" href="#" @click.prevent="forgotPassword">
            Forgot your password?
          </a>

          <button
            type="submit"
            class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
            :disabled="loading"
          >
            <span v-if="loading" class="mr-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Log in
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

export default {
  name: 'Login',
  
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const toast = useToast();
    
    const form = ref({
      email: '',
      password: '',
      remember: false
    });
    
    const loading = computed(() => store.getters['auth/loading']);
    const error = computed(() => store.getters['auth/error']);
    
    const login = async () => {
      try {
        await store.dispatch('auth/login', form.value);
        toast.success('Login successful!');
        router.push('/');
      } catch (error) {
        console.error('Login error:', error);
        // Error is already set in the store
      }
    };
    
    const forgotPassword = () => {
      router.push('/auth/forgot-password');
    };
    
    return {
      form,
      loading,
      error,
      login,
      forgotPassword
    };
  }
};
</script>
