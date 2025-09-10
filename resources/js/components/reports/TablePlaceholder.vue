<template>
  <div class="table-placeholder">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
        <thead class="bg-neutral-50 dark:bg-neutral-900">
          <tr>
            <th 
              v-for="(_, key) in data[0]" 
              :key="key"
              scope="col" 
              class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider"
            >
              {{ formatColumnName(key) }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
          <tr v-for="(row, index) in data" :key="index">
            <td 
              v-for="(value, key) in row" 
              :key="key"
              class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400"
            >
              {{ value }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
});

function formatColumnName(key) {
  // Convert camelCase to Title Case with spaces
  return key
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, (str) => str.toUpperCase());
}
</script>
