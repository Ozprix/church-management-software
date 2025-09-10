<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg overflow-hidden transition-colors duration-300">
      <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
        <h1 class="text-2xl font-bold text-neutral-800 dark:text-white transition-colors duration-300">Settings</h1>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Church Information -->
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Church Information
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4 transition-colors duration-300">Manage your church's basic information and contact details.</p>
            <router-link to="/church-info" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-300">
              Manage Church Info
            </router-link>
          </div>
          
          <!-- User Profile -->
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              User Profile
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4 transition-colors duration-300">Update your personal information and account settings.</p>
            <router-link to="/profile" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-300">
              Edit Profile
            </router-link>
          </div>
          
          <!-- Theme Customization -->
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
              Theme Customization
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4 transition-colors duration-300">Customize the appearance of your church management system with themes, colors, and seasonal options.</p>
            <router-link to="/theme-customizer" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-300">
              Customize Theme
            </router-link>
          </div>
          
          <!-- Custom Fields -->
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Custom Fields
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4 transition-colors duration-300">Create and manage custom fields for members, groups, and other entities.</p>
            <router-link to="/custom-fields" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-300">
              Manage Custom Fields
            </router-link>
          </div>
          
          <!-- Component Demo -->
          <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700 transition-colors duration-300">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-white mb-4 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              UI Component Demo
            </h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4 transition-colors duration-300">Explore all available UI components for your church management system.</p>
            <router-link to="/component-demo" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-300">
              View Components
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useSettingsStore } from '@/stores/settings';

const settingsStore = useSettingsStore();
</script>
