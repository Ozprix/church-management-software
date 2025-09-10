<template>
  <div class="receipt-settings">
    <!-- Receipt Settings Form -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-white">Receipt Settings</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Configure how donation receipts are generated and displayed
        </p>
      </div>
      
      <div class="p-4 sm:p-6">
        <form @submit.prevent="saveSettings">
          <div class="space-y-6">
            <!-- Organization Information -->
            <div>
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Organization Information</h4>
              <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                  <label for="org-name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Organization Name
                  </label>
                  <div class="mt-1">
                    <input 
                      type="text" 
                      id="org-name" 
                      v-model="receiptSettings.orgName" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    />
                  </div>
                </div>
                
                <div class="sm:col-span-3">
                  <label for="tax-id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Tax ID / EIN
                  </label>
                  <div class="mt-1">
                    <input 
                      type="text" 
                      id="tax-id" 
                      v-model="receiptSettings.taxId" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    />
                  </div>
                </div>
                
                <div class="sm:col-span-6">
                  <label for="org-address" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Organization Address
                  </label>
                  <div class="mt-1">
                    <textarea 
                      id="org-address" 
                      v-model="receiptSettings.orgAddress" 
                      rows="3" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Receipt Content -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Receipt Content</h4>
              <div class="space-y-6">
                <div>
                  <label for="receipt-title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Receipt Title
                  </label>
                  <div class="mt-1">
                    <input 
                      type="text" 
                      id="receipt-title" 
                      v-model="receiptSettings.receiptTitle" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    />
                  </div>
                </div>
                
                <div>
                  <label for="thank-you-message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Thank You Message
                  </label>
                  <div class="mt-1">
                    <textarea 
                      id="thank-you-message" 
                      v-model="receiptSettings.thankYouMessage" 
                      rows="3" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    ></textarea>
                  </div>
                  <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    This message will appear at the top of donation receipts.
                  </p>
                </div>
                
                <div>
                  <label for="footer-message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Footer Message
                  </label>
                  <div class="mt-1">
                    <textarea 
                      id="footer-message" 
                      v-model="receiptSettings.footerMessage" 
                      rows="3" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    ></textarea>
                  </div>
                  <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    This message will appear at the bottom of donation receipts.
                  </p>
                </div>
                
                <div>
                  <label for="tax-deductible-message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Tax Deductible Message
                  </label>
                  <div class="mt-1">
                    <textarea 
                      id="tax-deductible-message" 
                      v-model="receiptSettings.taxDeductibleMessage" 
                      rows="3" 
                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-neutral-300 dark:border-neutral-600 rounded-md dark:bg-neutral-700 dark:text-white"
                    ></textarea>
                  </div>
                  <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    Legal statement regarding tax deductibility of donations.
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Receipt Options -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Receipt Options</h4>
              <div class="space-y-4">
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="auto-generate" 
                      v-model="receiptSettings.autoGenerate" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="auto-generate" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Automatically generate receipts
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Generate a receipt automatically when a donation is recorded.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="email-receipt" 
                      v-model="receiptSettings.emailReceipt" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="email-receipt" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Email receipts to donors
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Send an email with the receipt when a donation is recorded.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="include-logo" 
                      v-model="receiptSettings.includeLogo" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="include-logo" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Include organization logo
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Display the organization logo on receipts.
                    </p>
                  </div>
                </div>
                
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input 
                      id="include-signature" 
                      v-model="receiptSettings.includeSignature" 
                      type="checkbox" 
                      class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-300 dark:border-neutral-600 rounded dark:bg-neutral-700"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="include-signature" class="font-medium text-neutral-700 dark:text-neutral-300">
                      Include digital signature
                    </label>
                    <p class="text-neutral-500 dark:text-neutral-400">
                      Display a digital signature on receipts.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Receipt Preview -->
            <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
              <h4 class="text-md font-medium text-neutral-900 dark:text-white mb-4">Receipt Preview</h4>
              <div class="bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-md p-4">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="text-lg font-bold">{{ receiptSettings.receiptTitle || 'Donation Receipt' }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Receipt #: SAMPLE-12345</p>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Date: {{ new Date().toLocaleDateString() }}</p>
                  </div>
                  <div v-if="receiptSettings.includeLogo" class="w-20 h-20 bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center rounded">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Logo</span>
                  </div>
                </div>
                
                <div class="mt-4">
                  <p class="text-sm">{{ receiptSettings.thankYouMessage || 'Thank you for your generous donation.' }}</p>
                </div>
                
                <div class="mt-6">
                  <div class="flex justify-between text-sm font-medium">
                    <div>
                      <p>Donor:</p>
                      <p class="text-neutral-500 dark:text-neutral-400">John Doe</p>
                      <p class="text-neutral-500 dark:text-neutral-400">123 Main St</p>
                      <p class="text-neutral-500 dark:text-neutral-400">Anytown, ST 12345</p>
                    </div>
                    <div>
                      <p>From:</p>
                      <p class="text-neutral-500 dark:text-neutral-400">{{ receiptSettings.orgName || 'Your Church' }}</p>
                      <p class="text-neutral-500 dark:text-neutral-400">{{ receiptSettings.orgAddress || '456 Church Ave, Cityville, ST 67890' }}</p>
                      <p class="text-neutral-500 dark:text-neutral-400">Tax ID: {{ receiptSettings.taxId || '12-3456789' }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="mt-6">
                  <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead>
                      <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                      <tr>
                        <td class="px-3 py-2 text-sm text-neutral-900 dark:text-white">General Tithe</td>
                        <td class="px-3 py-2 text-sm text-neutral-500 dark:text-neutral-400">{{ new Date().toLocaleDateString() }}</td>
                        <td class="px-3 py-2 text-sm text-neutral-900 dark:text-white text-right">$100.00</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2" class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white text-right">Total:</td>
                        <td class="px-3 py-2 text-sm font-medium text-neutral-900 dark:text-white text-right">$100.00</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                
                <div class="mt-6 text-xs text-neutral-500 dark:text-neutral-400">
                  <p>{{ receiptSettings.taxDeductibleMessage || 'No goods or services were provided in exchange for this contribution. This organization is a tax-exempt 501(c)(3) organization. Your donation may be tax-deductible to the extent allowed by law.' }}</p>
                </div>
                
                <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
                  <div class="text-xs text-neutral-500 dark:text-neutral-400">
                    <p>{{ receiptSettings.footerMessage || 'Thank you for your support!' }}</p>
                  </div>
                  <div v-if="receiptSettings.includeSignature" class="w-32">
                    <div class="border-b border-neutral-400 dark:border-neutral-600 mb-1"></div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 text-center">Authorized Signature</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Save Button -->
            <div class="pt-6 flex justify-end">
              <button 
                type="submit" 
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Save Settings
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useDonationStore } from '../../../stores/donations';
import { useToastService } from '../../../services/toastService';

// Store
const donationStore = useDonationStore();
const toast = useToastService();

// Get receipt settings from store or use defaults
const receiptSettings = reactive({
  orgName: donationStore.receiptSettings?.orgName || 'Your Church',
  taxId: donationStore.receiptSettings?.taxId || '',
  orgAddress: donationStore.receiptSettings?.orgAddress || '',
  receiptTitle: donationStore.receiptSettings?.receiptTitle || 'Donation Receipt',
  thankYouMessage: donationStore.receiptSettings?.thankYouMessage || 'Thank you for your generous donation to our church. Your contribution helps us continue our mission and ministry.',
  footerMessage: donationStore.receiptSettings?.footerMessage || 'Thank you for your continued support!',
  taxDeductibleMessage: donationStore.receiptSettings?.taxDeductibleMessage || 'No goods or services were provided in exchange for this contribution. This organization is a tax-exempt 501(c)(3) organization. Your donation may be tax-deductible to the extent allowed by law.',
  autoGenerate: donationStore.receiptSettings?.autoGenerate || true,
  emailReceipt: donationStore.receiptSettings?.emailReceipt || false,
  includeLogo: donationStore.receiptSettings?.includeLogo || true,
  includeSignature: donationStore.receiptSettings?.includeSignature || true
});

// Save settings
const saveSettings = () => {
  donationStore.updateReceiptSettings(receiptSettings);
  
  toast.show({
    type: 'success',
    title: 'Settings Saved',
    message: 'Receipt settings have been updated successfully.'
  });
};
</script>
