<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Donations</h2>
      <button 
        @click="showAddModal = true" 
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Record Donation
      </button>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
      <div class="relative flex-grow">
        <input 
          type="text" 
          v-model="search" 
          placeholder="Search donations..." 
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <span class="absolute right-3 top-2.5 text-gray-400">
          <i class="fas fa-search"></i>
        </span>
      </div>
      <div class="flex gap-2">
        <select 
          v-model="categoryFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Categories</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <select 
          v-model="projectFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Projects</option>
          <option v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.name }}
          </option>
        </select>
        <select 
          v-model="typeFilter" 
          class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="all">All Types</option>
          <option value="regular">Regular</option>
          <option value="gift">Gift</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline"> {{ error }}</span>
    </div>

    <!-- Donations Table -->
    <div v-else-if="filteredDonations.length > 0" class="overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead class="bg-gray-100">
          <tr>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Date</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Member</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Type</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Category</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Project</th>
            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="donation in filteredDonations" :key="donation.id" class="hover:bg-gray-50">
            <td class="py-3 px-4 text-sm text-gray-900">{{ formatDate(donation.donation_date) }}</td>
            <td class="py-3 px-4 text-sm">
              <div v-if="donation.is_anonymous" class="text-gray-500 italic">Anonymous</div>
              <div v-else class="text-gray-900">
                {{ donation.member ? `${donation.member.first_name} ${donation.member.last_name}` : 'Unknown' }}
              </div>
            </td>
            <td class="py-3 px-4 text-sm">
              <span 
                v-if="donation.recipient_id" 
                class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium"
              >
                Gift
              </span>
              <span v-else class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                Regular
              </span>
            </td>
            <td class="py-3 px-4 text-sm font-medium text-gray-900">${{ formatAmount(donation.amount) }}</td>
            <td class="py-3 px-4 text-sm text-gray-500">
              {{ donation.category ? donation.category.name : 'Uncategorized' }}
            </td>
            <td class="py-3 px-4 text-sm text-gray-500">
              {{ donation.project ? donation.project.name : 'None' }}
            </td>
            <td class="py-3 px-4 text-sm">
              <div class="flex space-x-2">
                <button 
                  @click="viewDonation(donation)" 
                  class="text-indigo-600 hover:text-indigo-900"
                  title="View Details"
                >
                  <i class="fas fa-eye"></i>
                </button>
                <button 
                  @click="editDonation(donation)" 
                  class="text-blue-600 hover:text-blue-900"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  @click="confirmDelete(donation)" 
                  class="text-red-600 hover:text-red-900"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
                <button 
                  @click="sendReceipt(donation)" 
                  class="text-green-600 hover:text-green-900"
                  title="Send Receipt"
                >
                  <i class="fas fa-envelope"></i>
                </button>
                <generate-tax-receipt 
                  :donation="donation" 
                  @receipt-updated="fetchDonations"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div class="flex justify-between items-center mt-6">
        <div class="text-sm text-gray-500">
          Showing {{ filteredDonations.length }} of {{ totalDonations }} donations
        </div>
        <div class="flex space-x-2">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1" 
            :class="currentPage === 1 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'"
            class="px-3 py-1 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Previous
          </button>
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages" 
            :class="currentPage === totalPages ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'"
            class="px-3 py-1 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <div class="text-gray-500 mb-4">
        <i class="fas fa-hand-holding-usd text-4xl"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900">No donations found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by recording a new donation.
      </p>
      <button 
        @click="showAddModal = true" 
        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Record Donation
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showEditModal ? 'Edit Donation' : 'Record New Donation' }}
        </h3>
        <form @submit.prevent="showEditModal ? updateDonation() : addDonation()">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Donation Type Selection -->
            <div class="md:col-span-2 mb-2">
              <div class="flex items-center space-x-6">
                <div class="flex items-center">
                  <input 
                    type="radio" 
                    id="regular-donation" 
                    v-model="form.donationType" 
                    value="regular"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                  />
                  <label for="regular-donation" class="ml-2 block text-sm text-gray-900">Regular Donation</label>
                </div>
                <div class="flex items-center">
                  <input 
                    type="radio" 
                    id="gift-donation" 
                    v-model="form.donationType" 
                    value="gift"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                  />
                  <label for="gift-donation" class="ml-2 block text-sm text-gray-900">Gift to Member</label>
                </div>
              </div>
            </div>

            <!-- Member (Donor) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Donor</label>
              <select 
                v-model="form.member_id" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="" disabled>Select a member</option>
                <option v-for="member in members" :key="member.id" :value="member.id">
                  {{ member.first_name }} {{ member.last_name }}
                </option>
              </select>
            </div>

            <!-- Recipient (for gifts) -->
            <div v-if="form.donationType === 'gift'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Recipient</label>
              <select 
                v-model="form.recipient_id" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="" disabled>Select a recipient</option>
                <option 
                  v-for="member in members.filter(m => m.id !== form.member_id)" 
                  :key="member.id" 
                  :value="member.id"
                >
                  {{ member.first_name }} {{ member.last_name }}
                </option>
              </select>
            </div>

            <!-- Amount -->
            <div :class="{ 'md:col-span-2': form.donationType !== 'gift' }">
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount ($)</label>
              <input 
                type="number" 
                v-model="form.amount" 
                required
                min="0.01"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select 
                v-model="form.category_id" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">None</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Project -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
              <select 
                v-model="form.project_id" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">None</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select 
                v-model="form.payment_method" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="" disabled>Select payment method</option>
                <option value="cash">Cash</option>
                <option value="check">Check</option>
                <option value="credit_card">Credit Card</option>
                <option value="debit_card">Debit Card</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_payment">Mobile Payment</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Transaction ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
              <input 
                type="text" 
                v-model="form.transaction_id" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Optional"
              />
            </div>

            <!-- Donation Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Donation Date</label>
              <input 
                type="date" 
                v-model="form.donation_date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <!-- Anonymous Donation -->
            <div class="md:col-span-2 flex items-center">
              <input 
                type="checkbox" 
                id="is_anonymous" 
                v-model="form.is_anonymous" 
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_anonymous" class="ml-2 block text-sm text-gray-900">Anonymous Donation</label>
            </div>

            <!-- Recurring Donation -->
            <div class="md:col-span-2 flex items-center">
              <input 
                type="checkbox" 
                id="is_recurring" 
                v-model="form.is_recurring" 
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_recurring" class="ml-2 block text-sm text-gray-900">Recurring Donation</label>
            </div>

            <!-- Recurring Details (if recurring) -->
            <div v-if="form.is_recurring" class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                <select 
                  v-model="form.recurring_frequency" 
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="" disabled>Select frequency</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input 
                  type="date" 
                  v-model="form.recurring_start_date" 
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date (Optional)</label>
                <input 
                  type="date" 
                  v-model="form.recurring_end_date" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
            </div>

            <!-- Gift Message (if gift) -->
            <div v-if="form.donationType === 'gift'" class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Gift Message</label>
              <textarea 
                v-model="form.gift_message" 
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Optional message to include with the gift"
              ></textarea>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea 
                v-model="form.notes" 
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Optional notes about this donation"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :disabled="loading"
            >
              {{ showEditModal ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Delete</h3>
        <p class="text-sm text-gray-500 mb-4">
          Are you sure you want to delete this donation? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="showDeleteModal = false" 
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Cancel
          </button>
          <button 
            @click="deleteDonation" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            :disabled="loading"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Donation Details Modal -->
    <div v-if="showDetailsModal && selectedDonation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-medium text-gray-900">Donation Details</h3>
          <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="space-y-6">
          <!-- Receipt Number and Status -->
          <div class="flex justify-between items-center">
            <div>
              <span class="text-sm text-gray-500">Receipt #:</span>
              <span class="ml-2 font-medium">{{ selectedDonation.receipt_number || 'Not issued' }}</span>
            </div>
            <div>
              <span 
                :class="{
                  'bg-green-100 text-green-800': selectedDonation.receipt_sent,
                  'bg-yellow-100 text-yellow-800': !selectedDonation.receipt_sent
                }" 
                class="px-2 py-1 rounded-full text-xs font-medium"
              >
                {{ selectedDonation.receipt_sent ? 'Receipt Sent' : 'Receipt Pending' }}
              </span>
            </div>
          </div>
          
          <!-- Donation Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-b border-gray-200 py-4">
            <!-- Amount -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Amount</h4>
              <p class="mt-1 text-xl font-semibold text-gray-900">${{ formatAmount(selectedDonation.amount) }}</p>
            </div>
            
            <!-- Date -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Date</h4>
              <p class="mt-1 text-gray-900">{{ formatDate(selectedDonation.donation_date) }}</p>
            </div>
            
            <!-- Donor -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Donor</h4>
              <p v-if="selectedDonation.is_anonymous" class="mt-1 text-gray-500 italic">Anonymous</p>
              <p v-else-if="selectedDonation.member" class="mt-1 text-gray-900">
                {{ selectedDonation.member.first_name }} {{ selectedDonation.member.last_name }}
              </p>
              <p v-else class="mt-1 text-gray-500">Unknown</p>
            </div>
            
            <!-- Recipient (if gift) -->
            <div v-if="selectedDonation.recipient_id">
              <h4 class="text-sm font-medium text-gray-500">Recipient</h4>
              <p v-if="selectedDonation.recipient" class="mt-1 text-gray-900">
                {{ selectedDonation.recipient.first_name }} {{ selectedDonation.recipient.last_name }}
              </p>
              <p v-else class="mt-1 text-gray-500">Unknown</p>
            </div>
            
            <!-- Category -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Category</h4>
              <p class="mt-1 text-gray-900">
                {{ selectedDonation.category ? selectedDonation.category.name : 'Uncategorized' }}
              </p>
            </div>
            
            <!-- Project -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Project</h4>
              <p class="mt-1 text-gray-900">
                {{ selectedDonation.project ? selectedDonation.project.name : 'None' }}
              </p>
            </div>
            
            <!-- Payment Method -->
            <div>
              <h4 class="text-sm font-medium text-gray-500">Payment Method</h4>
              <p class="mt-1 text-gray-900 capitalize">
                {{ selectedDonation.payment_method.replace('_', ' ') }}
              </p>
            </div>
            
            <!-- Transaction ID -->
            <div v-if="selectedDonation.transaction_id">
              <h4 class="text-sm font-medium text-gray-500">Transaction ID</h4>
              <p class="mt-1 text-gray-900">{{ selectedDonation.transaction_id }}</p>
            </div>
            
            <!-- Recurring Info -->
            <div v-if="selectedDonation.is_recurring" class="md:col-span-2 bg-blue-50 p-3 rounded-md">
              <h4 class="text-sm font-medium text-blue-700">Recurring Donation</h4>
              <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                <div>
                  <span class="text-xs text-blue-600">Frequency:</span>
                  <span class="ml-1 text-sm capitalize">{{ selectedDonation.recurring_frequency }}</span>
                </div>
                <div>
                  <span class="text-xs text-blue-600">Start Date:</span>
                  <span class="ml-1 text-sm">{{ formatDate(selectedDonation.recurring_start_date) }}</span>
                </div>
                <div v-if="selectedDonation.recurring_end_date">
                  <span class="text-xs text-blue-600">End Date:</span>
                  <span class="ml-1 text-sm">{{ formatDate(selectedDonation.recurring_end_date) }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Gift Message (if gift) -->
          <div v-if="selectedDonation.gift_message" class="bg-purple-50 p-4 rounded-md">
            <h4 class="text-sm font-medium text-purple-700 mb-2">Gift Message</h4>
            <p class="text-gray-900 italic">"{{ selectedDonation.gift_message }}"</p>
          </div>
          
          <!-- Notes -->
          <div v-if="selectedDonation.notes">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Notes</h4>
            <p class="text-gray-900">{{ selectedDonation.notes }}</p>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button 
              v-if="!selectedDonation.receipt_sent" 
              @click="sendReceipt(selectedDonation.id)" 
              class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              Send Receipt
            </button>
            <button 
              @click="editDonation(selectedDonation); showDetailsModal = false" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Edit
            </button>
            <button 
              @click="confirmDelete(selectedDonation); showDetailsModal = false" 
              class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import { useToast } from 'vue-toastification';
import GenerateTaxReceipt from './tax-receipts/GenerateTaxReceipt.vue';

export default {
  name: 'EnhancedDonations',
  components: {
    GenerateTaxReceipt
  },
  setup() {
    const toast = useToast();
    const donations = ref([]);
    const categories = ref([]);
    const projects = ref([]);
    const members = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const search = ref('');
    const categoryFilter = ref('all');
    const projectFilter = ref('all');
    const typeFilter = ref('all');
    const currentPage = ref(1);
    const perPage = ref(15);
    const totalDonations = ref(0);
    const totalPages = ref(1);
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);
    const showDetailsModal = ref(false);
    const donationToDelete = ref(null);
    const selectedDonation = ref(null);
    const form = ref({
      donationType: 'regular',
      member_id: '',
      recipient_id: '',
      category_id: '',
      project_id: '',
      amount: '',
      payment_method: '',
      transaction_id: '',
      donation_date: new Date().toISOString().split('T')[0],
      is_anonymous: false,
      is_recurring: false,
      recurring_frequency: '',
      recurring_start_date: '',
      recurring_end_date: '',
      notes: '',
      gift_message: ''
    });

    // Fetch donations with pagination
    const fetchDonations = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get('/api/donations', {
          params: {
            page: currentPage.value,
            per_page: perPage.value,
            category_id: categoryFilter.value !== 'all' ? categoryFilter.value : null,
            project_id: projectFilter.value !== 'all' ? projectFilter.value : null,
            is_gift: typeFilter.value === 'gift' ? true : (typeFilter.value === 'regular' ? false : null)
          }
        });
        donations.value = response.data.data.data;
        totalDonations.value = response.data.data.total;
        totalPages.value = Math.ceil(response.data.data.total / perPage.value);
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load donations';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };

    // Fetch categories
    const fetchCategories = async () => {
      try {
        const response = await axios.get('/api/donation-categories', {
          params: { is_active: true }
        });
        categories.value = response.data.data;
      } catch (err) {
        toast.error('Failed to load donation categories');
      }
    };

    // Fetch projects
    const fetchProjects = async () => {
      try {
        const response = await axios.get('/api/projects', {
          params: { status: 'active' }
        });
        projects.value = response.data.data;
      } catch (err) {
        toast.error('Failed to load projects');
      }
    };

    // Fetch members
    const fetchMembers = async () => {
      try {
        const response = await axios.get('/api/members', {
          params: { membership_status: 'active' }
        });
        members.value = response.data.data;
      } catch (err) {
        toast.error('Failed to load members');
      }
    };

    // Filtered donations based on search
    const filteredDonations = computed(() => {
      if (!search.value) return donations.value;
      
      const searchLower = search.value.toLowerCase();
      return donations.value.filter(donation => {
        // Search by member name
        const memberName = donation.member ? 
          `${donation.member.first_name} ${donation.member.last_name}`.toLowerCase() : '';
        
        // Search by recipient name if it's a gift
        const recipientName = donation.recipient ? 
          `${donation.recipient.first_name} ${donation.recipient.last_name}`.toLowerCase() : '';
        
        // Search by category name
        const categoryName = donation.category ? donation.category.name.toLowerCase() : '';
        
        // Search by project name
        const projectName = donation.project ? donation.project.name.toLowerCase() : '';
        
        return memberName.includes(searchLower) || 
               recipientName.includes(searchLower) || 
               categoryName.includes(searchLower) || 
               projectName.includes(searchLower) ||
               donation.amount.toString().includes(searchLower);
      });
    });

    // Format amount with commas
    const formatAmount = (amount) => {
      return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    // Pagination methods
    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchDonations();
      }
    };

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
        fetchDonations();
      }
    };

    // View donation details
    const viewDonation = (donation) => {
      selectedDonation.value = donation;
      showDetailsModal.value = true;
    };

    // Edit donation
    const editDonation = (donation) => {
      form.value = {
        donationType: donation.recipient_id ? 'gift' : 'regular',
        member_id: donation.member_id,
        recipient_id: donation.recipient_id || '',
        category_id: donation.category_id || '',
        project_id: donation.project_id || '',
        amount: donation.amount,
        payment_method: donation.payment_method,
        transaction_id: donation.transaction_id || '',
        donation_date: donation.donation_date,
        is_anonymous: donation.is_anonymous,
        is_recurring: donation.is_recurring,
        recurring_frequency: donation.recurring_frequency || '',
        recurring_start_date: donation.recurring_start_date || '',
        recurring_end_date: donation.recurring_end_date || '',
        notes: donation.notes || '',
        gift_message: donation.gift_message || '',
        id: donation.id
      };
      showEditModal.value = true;
    };

    // Confirm delete
    const confirmDelete = (donation) => {
      donationToDelete.value = donation;
      showDeleteModal.value = true;
    };

    // Delete donation
    const deleteDonation = async () => {
      if (!donationToDelete.value) return;
      
      loading.value = true;
      try {
        await axios.delete(`/api/donations/${donationToDelete.value.id}`);
        toast.success('Donation deleted successfully');
        showDeleteModal.value = false;
        fetchDonations(); // Refresh the list
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete donation';
        toast.error(error.value);
      } finally {
        loading.value = false;
        donationToDelete.value = null;
      }
    };

    // Send receipt
    const sendReceipt = async (id) => {
      loading.value = true;
      try {
        await axios.post(`/api/donations/${id}/send-receipt`);
        toast.success('Receipt sent successfully');
        
        // Update the donation in the list
        const index = donations.value.findIndex(d => d.id === id);
        if (index !== -1) {
          donations.value[index].receipt_sent = true;
          donations.value[index].receipt_sent_at = new Date().toISOString();
        }
      } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to send receipt');
      } finally {
        loading.value = false;
      }
    };

    // Load data on component mount
    onMounted(() => {
      fetchDonations();
      fetchCategories();
      fetchProjects();
      fetchMembers();
    });
    
    // Add a new donation
    const addDonation = async () => {
      loading.value = true;
      try {
        // Prepare the data
        const donationData = {
          member_id: form.value.member_id,
          amount: form.value.amount,
          payment_method: form.value.payment_method,
          donation_date: form.value.donation_date,
          is_anonymous: form.value.is_anonymous,
          is_recurring: form.value.is_recurring,
          notes: form.value.notes
        };
        
        // Add category if selected
        if (form.value.category_id) {
          donationData.category_id = form.value.category_id;
        }
        
        // Add project if selected
        if (form.value.project_id) {
          donationData.project_id = form.value.project_id;
        }
        
        // Add transaction ID if provided
        if (form.value.transaction_id) {
          donationData.transaction_id = form.value.transaction_id;
        }
        
        // Add recurring details if it's a recurring donation
        if (form.value.is_recurring) {
          donationData.recurring_frequency = form.value.recurring_frequency;
          donationData.recurring_start_date = form.value.recurring_start_date;
          if (form.value.recurring_end_date) {
            donationData.recurring_end_date = form.value.recurring_end_date;
          }
        }
        
        // Add recipient and gift message if it's a gift
        if (form.value.donationType === 'gift') {
          donationData.recipient_id = form.value.recipient_id;
          if (form.value.gift_message) {
            donationData.gift_message = form.value.gift_message;
          }
        }
        
        const response = await axios.post('/api/donations', donationData);
        toast.success('Donation recorded successfully');
        closeModal();
        fetchDonations(); // Refresh the list
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to record donation';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };
    
    // Update an existing donation
    const updateDonation = async () => {
      if (!form.value.id) return;
      
      loading.value = true;
      try {
        // Prepare the data
        const donationData = {
          member_id: form.value.member_id,
          amount: form.value.amount,
          payment_method: form.value.payment_method,
          donation_date: form.value.donation_date,
          is_anonymous: form.value.is_anonymous,
          is_recurring: form.value.is_recurring,
          notes: form.value.notes
        };
        
        // Add category if selected
        if (form.value.category_id) {
          donationData.category_id = form.value.category_id;
        }
        
        // Add project if selected
        if (form.value.project_id) {
          donationData.project_id = form.value.project_id;
        }
        
        // Add transaction ID if provided
        if (form.value.transaction_id) {
          donationData.transaction_id = form.value.transaction_id;
        }
        
        // Add recurring details if it's a recurring donation
        if (form.value.is_recurring) {
          donationData.recurring_frequency = form.value.recurring_frequency;
          donationData.recurring_start_date = form.value.recurring_start_date;
          if (form.value.recurring_end_date) {
            donationData.recurring_end_date = form.value.recurring_end_date;
          }
        }
        
        // Add recipient and gift message if it's a gift
        if (form.value.donationType === 'gift') {
          donationData.recipient_id = form.value.recipient_id;
          if (form.value.gift_message) {
            donationData.gift_message = form.value.gift_message;
          }
        } else {
          // Ensure recipient_id is null if not a gift
          donationData.recipient_id = null;
        }
        
        const response = await axios.put(`/api/donations/${form.value.id}`, donationData);
        toast.success('Donation updated successfully');
        closeModal();
        fetchDonations(); // Refresh the list
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update donation';
        toast.error(error.value);
      } finally {
        loading.value = false;
      }
    };
    
    // Close modal and reset form
    const closeModal = () => {
      showAddModal.value = false;
      showEditModal.value = false;
      form.value = {
        donationType: 'regular',
        member_id: '',
        recipient_id: '',
        category_id: '',
        project_id: '',
        amount: '',
        payment_method: '',
        transaction_id: '',
        donation_date: new Date().toISOString().split('T')[0],
        is_anonymous: false,
        is_recurring: false,
        recurring_frequency: '',
        recurring_start_date: '',
        recurring_end_date: '',
        notes: '',
        gift_message: ''
      };
    };

    // Watch for filter changes
    watch([categoryFilter, projectFilter, typeFilter], () => {
      currentPage.value = 1; // Reset to first page when filters change
      fetchDonations();
    });

    return {
      donations,
      categories,
      projects,
      members,
      loading,
      error,
      search,
      categoryFilter,
      projectFilter,
      typeFilter,
      currentPage,
      perPage,
      totalDonations,
      totalPages,
      showAddModal,
      showEditModal,
      showDeleteModal,
      showDetailsModal,
      donationToDelete,
      selectedDonation,
      filteredDonations,
      formatAmount,
      formatDate,
      nextPage,
      prevPage,
      viewDonation,
      editDonation,
      confirmDelete,
      deleteDonation,
      sendReceipt,
      addDonation,
      updateDonation,
      closeModal,
      form
    };
  }
};
</script>
