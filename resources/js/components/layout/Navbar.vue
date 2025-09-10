<template>
  <nav class="bg-primary-600 dark:bg-primary-900 shadow-lg transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/dashboard" class="text-white font-bold text-xl animate-pulse-soft">ChCMS</router-link>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <!-- Dashboard link -->
            <router-link 
              to="/dashboard" 
              class="text-white hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300"
              active-class="bg-primary-700 dark:bg-primary-800"
            >
              Dashboard
            </router-link>

            <!-- UI Components Demo link -->
            <router-link 
              to="/ui-components" 
              class="text-white hover:bg-primary-700 dark:hover:bg-primary-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-300"
            >
              UI Components
            </router-link>

            <!-- Members dropdown -->
            <div class="relative" @mouseover="openDropdown = 'members'" @mouseleave="openDropdown = null">
              <button 
                class="text-white hover:bg-indigo-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center"
              >
                Members
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div 
                v-show="openDropdown === 'members'" 
                class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
              >
                <div class="py-1">
                  <router-link to="/members" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Member List</router-link>
                  <router-link to="/members/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add Member</router-link>
                  <router-link to="/families" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Families</router-link>
                </div>
              </div>
            </div>

            <!-- Finance dropdown -->
            <div class="relative" @mouseover="openDropdown = 'finance'" @mouseleave="openDropdown = null">
              <button 
                class="text-white hover:bg-indigo-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center"
              >
                Finance
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div 
                v-show="openDropdown === 'finance'" 
                class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
              >
                <div class="py-1">
                  <router-link to="/finance/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Finance Dashboard</router-link>
                  <router-link to="/donations" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Donations</router-link>
                  <router-link to="/expenses" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Expenses</router-link>
                  <router-link to="/pledges" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pledges</router-link>
                  <router-link to="/budgets" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Budgets</router-link>
                  <router-link to="/campaigns" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Campaigns</router-link>
                </div>
              </div>
            </div>

            <!-- Events & Attendance -->
            <div class="relative" @mouseover="openDropdown = 'events'" @mouseleave="openDropdown = null">
              <button 
                class="text-white hover:bg-indigo-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center"
              >
                Events
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div 
                v-show="openDropdown === 'events'" 
                class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
              >
                <div class="py-1">
                  <router-link to="/events" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Events</router-link>
                  <router-link to="/attendance" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Attendance</router-link>
                </div>
              </div>
            </div>

            <!-- Media Library -->
            <div class="relative" @mouseover="openDropdown = 'media'" @mouseleave="openDropdown = null">
              <button 
                class="text-white hover:bg-indigo-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center"
              >
                Media
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div 
                v-show="openDropdown === 'media'" 
                class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
              >
                <div class="py-1">
                  <router-link to="/media?tab=sermons" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Sermons</router-link>
                  <router-link to="/media?tab=podcasts" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Podcasts</router-link>
                  <router-link to="/media?tab=devotionals" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Devotionals</router-link>
                  <router-link to="/media?tab=resources" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Resources</router-link>
                </div>
              </div>
            </div>
            
            <!-- Reports & Analytics -->
            <div class="relative" @mouseover="openDropdown = 'reports'" @mouseleave="openDropdown = null">
              <button 
                class="text-white hover:bg-indigo-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center"
              >
                Reports
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div 
                v-show="openDropdown === 'reports'" 
                class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
              >
                <div class="py-1">
                  <router-link to="/reports/dashboard" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Analytics Dashboard</router-link>
                  <router-link to="/reports" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">All Reports</router-link>
                  <router-link to="/reports/create" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Create Report</router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="-mr-2 flex items-center sm:hidden">
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-primary-700 dark:hover:bg-primary-800 focus:outline-none transition-colors duration-300">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <!-- User menu -->
        <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-2">          
          <!-- Search Icon -->
          <router-link to="/search" class="text-white p-2 rounded-full hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300" title="Search">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </router-link>
          
          <!-- Help Icon -->
          <div class="relative" @mouseover="openDropdown = 'help'" @mouseleave="openDropdown = null">
            <button 
              class="text-white p-2 rounded-full hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300" 
              title="Help & Support"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <div 
              v-show="openDropdown === 'help'" 
              class="absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
            >
              <div class="py-1">
                <router-link to="/onboarding/guide" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">
                  <i class="fas fa-play-circle mr-2"></i> Onboarding Guide
                </router-link>
                <router-link to="/onboarding/settings" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">
                  <i class="fas fa-cog mr-2"></i> Help Settings
                </router-link>
                <a @click.prevent="showKeyboardShortcuts" href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">
                  <i class="fas fa-keyboard mr-2"></i> Keyboard Shortcuts
                </a>
                <a @click.prevent="openHelpCenter" href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">
                  <i class="fas fa-book mr-2"></i> Help Center
                </a>
              </div>
            </div>
          </div>

          <!-- Language Selector -->
          <LanguageSelector />
          
          <!-- Dark Mode Toggle -->
          <DarkModeToggle />
          <div class="ml-3 relative" @mouseover="openDropdown = 'user'" @mouseleave="openDropdown = null">
            <div>
              <button class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary-600 dark:focus:ring-offset-primary-900 focus:ring-white transition-all duration-300">
                <span class="sr-only">Open user menu</span>
                <div class="h-8 w-8 rounded-full bg-white flex items-center justify-center text-primary-600 dark:text-primary-500 font-bold transition-colors duration-300">{{ userInitials }}</div>
              </button>
            </div>
            <div 
              v-show="openDropdown === 'user'" 
              class="absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 dark:ring-neutral-700 focus:outline-none transition-colors duration-300"
            >
              <div class="py-1">
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Your Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Settings</a>
                <router-link to="/onboarding/settings" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Help & Onboarding</router-link>
                <button @click="logout" class="w-full text-left block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors duration-300">Sign out</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div v-show="mobileMenuOpen" class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link to="/dashboard" class="block pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Dashboard</router-link>
        
        <!-- Mobile Members Section -->
        <div>
          <button @click="toggleMobileSection('members')" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">
            <span>Members</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path v-if="openMobileSection === 'members'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openMobileSection === 'members'" class="pl-6 pr-4 py-2 space-y-1">
            <router-link to="/members" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Member List</router-link>
            <router-link to="/members/create" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Add Member</router-link>
            <router-link to="/families" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Families</router-link>
          </div>
        </div>

        <!-- Mobile Finance Section -->
        <div>
          <button @click="toggleMobileSection('finance')" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-indigo-700">
            <span>Finance</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path v-if="openMobileSection === 'finance'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openMobileSection === 'finance'" class="pl-6 pr-4 py-2 space-y-1">
            <router-link to="/finance/dashboard" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Finance Dashboard</router-link>
            <router-link to="/donations" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Donations</router-link>
            <router-link to="/expenses" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Expenses</router-link>
            <router-link to="/pledges" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Pledges</router-link>
            <router-link to="/budgets" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Budgets</router-link>
            <router-link to="/campaigns" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Campaigns</router-link>
          </div>
        </div>

        <!-- Mobile Events Section -->
        <div>
          <button @click="toggleMobileSection('events')" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">
            <span>Events</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path v-if="openMobileSection === 'events'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openMobileSection === 'events'" class="pl-6 pr-4 py-2 space-y-1">
            <router-link to="/events" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Events</router-link>
            <router-link to="/attendance" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Attendance</router-link>
          </div>
        </div>
        
        <!-- Mobile Media Section -->
        <div>
          <button @click="toggleMobileSection('media')" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">
            <span>Media</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path v-if="openMobileSection === 'media'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openMobileSection === 'media'" class="pl-6 pr-4 py-2 space-y-1">
            <router-link to="/media?tab=sermons" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Sermons</router-link>
            <router-link to="/media?tab=podcasts" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Podcasts</router-link>
            <router-link to="/media?tab=devotionals" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Devotionals</router-link>
            <router-link to="/media?tab=resources" class="block py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">Resources</router-link>
          </div>
        </div>

        <!-- Mobile Reports Section -->
        <div>
          <button @click="toggleMobileSection('reports')" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-indigo-700">
            <span>Reports</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path v-if="openMobileSection === 'reports'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openMobileSection === 'reports'" class="pl-6 pr-4 py-2 space-y-1">
            <router-link to="/reports/dashboard" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Analytics Dashboard</router-link>
            <router-link to="/reports" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">All Reports</router-link>
            <router-link to="/reports/create" class="block py-2 text-base font-medium text-white hover:bg-indigo-700">Create Report</router-link>
          </div>
        </div>
      </div>

      <!-- Mobile search and help links -->
      <div class="pt-2 pb-2 border-t border-indigo-700">
        <router-link to="/search" class="flex items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span>Search</span>
        </router-link>
        <router-link to="/onboarding/guide" class="flex items-center pl-3 pr-4 py-2 text-base font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Help & Onboarding</span>
        </router-link>
      </div>

      <!-- Mobile profile section -->
      <div class="pt-4 pb-3 border-t border-indigo-700">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center text-indigo-600 font-bold">{{ userInitials }}</div>
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-white">{{ userName }}</div>
            <div class="text-sm font-medium text-indigo-300">{{ userEmail }}</div>
          </div>
        </div>
        <div class="mt-3 space-y-1">
          <a href="#" class="block px-4 py-2 text-base font-medium text-white hover:bg-indigo-700">Your Profile</a>
          <a href="#" class="block px-4 py-2 text-base font-medium text-white hover:bg-indigo-700">Settings</a>
          <button @click="logout" class="w-full text-left block px-4 py-2 text-base font-medium text-white hover:bg-indigo-700">Sign out</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useOnboardingStore } from '../../stores/onboarding';
import { useAuthStore } from '../../stores/auth';
import { useSettingsStore } from '../../stores/settings';
import { useI18n } from '../../services/i18nService';
import DarkModeToggle from '../ui/DarkModeToggle.vue';
import LanguageSelector from '../i18n/LanguageSelector.vue';

// Setup
const router = useRouter();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();
const onboardingStore = useOnboardingStore();
const i18n = useI18n();
const openDropdown = ref(null);
const openMobileSection = ref(null);
const mobileMenuOpen = ref(false);

// User information
const user = computed(() => authStore.user || {});
const userName = computed(() => user.value?.name || 'User');
const userEmail = computed(() => user.value?.email || '');
const userInitials = computed(() => {
  if (!authStore.isAuthenticated || !authStore.user) {
    return '';
  }
  
  return authStore.userInitials;
});

// Methods
const logout = async () => {
  try {
    await authStore.logout();
    router.push('/auth/login');
  } catch (error) {
    console.error('Logout error:', error);
  }
};

const toggleMobileSection = (section) => {
  if (openMobileSection.value === section) {
    openMobileSection.value = null;
  } else {
    openMobileSection.value = section;
  }
};

const showKeyboardShortcuts = () => {
  // Close dropdown
  openDropdown.value = null;
  
  // Emit event that will be caught by ContextualHelp component
  window.dispatchEvent(new CustomEvent('show-keyboard-shortcuts'));
};

const openHelpCenter = () => {
  // Close dropdown
  openDropdown.value = null;
  
  // Navigate to help center or show help modal
  router.push('/help-center');
};
</script>
