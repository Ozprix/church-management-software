<template>
  <div class="table-container">
    <!-- Table toolbar -->
    <div v-if="$slots.toolbar" class="table-toolbar">
      <slot name="toolbar"></slot>
    </div>
    
    <!-- Table wrapper -->
    <div class="table-wrapper" :class="{ 'table-responsive': responsive }">
      <table class="table" :class="tableClasses">
        <!-- Table header -->
        <thead class="table-header">
          <tr>
            <!-- Selection column -->
            <th v-if="selectable" class="table-cell selection-cell">
              <div class="flex items-center justify-center">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected && !allSelected"
                  @change="toggleSelectAll"
                  class="form-checkbox h-4 w-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500 dark:border-neutral-600 dark:bg-neutral-800 dark:focus:ring-primary-600"
                />
              </div>
            </th>
            
            <!-- Column headers -->
            <th 
              v-for="(column, index) in columns" 
              :key="index"
              class="table-cell"
              :class="[
                column.headerClass,
                { 'cursor-pointer': column.sortable }
              ]"
              :style="column.width ? { width: column.width } : {}"
              @click="column.sortable && sortBy(column.key)"
            >
              <div class="flex items-center" :class="{ 'justify-center': column.align === 'center', 'justify-end': column.align === 'right' }">
                <span>{{ column.label }}</span>
                
                <!-- Sort indicator -->
                <span v-if="column.sortable" class="ml-1">
                  <svg v-if="sortKey === column.key && sortOrder === 'asc'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else-if="sortKey === column.key && sortOrder === 'desc'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-4 h-4 text-neutral-300 dark:text-neutral-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                  </svg>
                </span>
              </div>
            </th>
            
            <!-- Actions column -->
            <th v-if="$slots.actions" class="table-cell actions-cell">
              <span>Actions</span>
            </th>
          </tr>
        </thead>
        
        <!-- Table body -->
        <tbody class="table-body">
          <!-- Empty state -->
          <tr v-if="displayData.length === 0" class="table-row empty-row">
            <td :colspan="totalColumns" class="table-cell empty-cell">
              <slot name="empty">
                <div class="flex flex-col items-center justify-center py-6">
                  <svg class="w-12 h-12 text-neutral-300 dark:text-neutral-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <p class="text-neutral-500 dark:text-neutral-400">No data available</p>
                </div>
              </slot>
            </td>
          </tr>
          
          <!-- Loading state -->
          <tr v-else-if="loading" class="table-row loading-row">
            <td :colspan="totalColumns" class="table-cell loading-cell">
              <slot name="loading">
                <div class="flex items-center justify-center py-4">
                  <svg class="animate-spin h-6 w-6 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="ml-2 text-neutral-600 dark:text-neutral-300">Loading...</span>
                </div>
              </slot>
            </td>
          </tr>
          
          <!-- Data rows -->
          <template v-else>
            <tr 
              v-for="(item, rowIndex) in displayData" 
              :key="rowIndex"
              class="table-row"
              :class="[
                { 'selected-row': isSelected(item) },
                { 'hover:bg-neutral-50 dark:hover:bg-neutral-800': hoverable },
                rowClasses
              ]"
              @click="handleRowClick(item, rowIndex)"
            >
              <!-- Selection column -->
              <td v-if="selectable" class="table-cell selection-cell" @click.stop>
                <div class="flex items-center justify-center">
                  <input
                    type="checkbox"
                    :checked="isSelected(item)"
                    @change="toggleSelect(item)"
                    class="form-checkbox h-4 w-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500 dark:border-neutral-600 dark:bg-neutral-800 dark:focus:ring-primary-600"
                  />
                </div>
              </td>
              
              <!-- Data cells -->
              <td 
                v-for="(column, colIndex) in columns" 
                :key="colIndex"
                class="table-cell"
                :class="[
                  column.cellClass,
                  { 'text-center': column.align === 'center', 'text-right': column.align === 'right' }
                ]"
              >
                <slot 
                  :name="`cell-${column.key}`" 
                  :item="item" 
                  :value="getValue(item, column.key)" 
                  :index="rowIndex"
                >
                  <template v-if="column.formatter">
                    {{ column.formatter(getValue(item, column.key), item, rowIndex) }}
                  </template>
                  <template v-else>
                    {{ getValue(item, column.key) }}
                  </template>
                </slot>
              </td>
              
              <!-- Actions column -->
              <td v-if="$slots.actions" class="table-cell actions-cell" @click.stop>
                <slot name="actions" :item="item" :index="rowIndex"></slot>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div v-if="pagination && !loading" class="table-pagination">
      <slot name="pagination" :total="totalItems" :current-page="currentPage" :page-size="pageSize" :change-page="changePage">
        <div class="flex items-center justify-between">
          <div class="text-sm text-neutral-600 dark:text-neutral-400">
            Showing {{ paginationInfo.from }} to {{ paginationInfo.to }} of {{ totalItems }} entries
          </div>
          
          <div class="flex items-center space-x-2">
            <button 
              @click="changePage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="pagination-button"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
            >
              Previous
            </button>
            
            <div class="flex items-center">
              <template v-for="page in paginationPages" :key="page">
                <button 
                  v-if="page !== '...'"
                  @click="changePage(page)"
                  class="pagination-page-button"
                  :class="{ 'bg-primary-600 text-white dark:bg-primary-700': currentPage === page }"
                >
                  {{ page }}
                </button>
                <span v-else class="px-2 py-1 text-neutral-600 dark:text-neutral-400">...</span>
              </template>
            </div>
            
            <button 
              @click="changePage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="pagination-button"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
            >
              Next
            </button>
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  selectable: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: true
  },
  striped: {
    type: Boolean,
    default: false
  },
  bordered: {
    type: Boolean,
    default: false
  },
  compact: {
    type: Boolean,
    default: false
  },
  responsive: {
    type: Boolean,
    default: true
  },
  sortable: {
    type: Boolean,
    default: true
  },
  defaultSort: {
    type: String,
    default: ''
  },
  defaultSortOrder: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value)
  },
  pagination: {
    type: Boolean,
    default: false
  },
  pageSize: {
    type: Number,
    default: 10
  },
  currentPageProp: {
    type: Number,
    default: 1
  },
  totalItems: {
    type: Number,
    default: 0
  },
  rowClasses: {
    type: [String, Object, Array],
    default: ''
  }
});

const emit = defineEmits([
  'row-click',
  'sort-change',
  'selection-change',
  'page-change',
  'update:currentPage'
]);

// State
const selectedItems = ref([]);
const sortKey = ref(props.defaultSort);
const sortOrder = ref(props.defaultSortOrder);
const currentPage = ref(props.currentPageProp);

// Computed properties
const tableClasses = computed(() => {
  return {
    'table-striped': props.striped,
    'table-bordered': props.bordered,
    'table-compact': props.compact
  };
});

const totalColumns = computed(() => {
  let count = props.columns.length;
  if (props.selectable) count++;
  if (props.$slots.actions) count++;
  return count;
});

const sortedData = computed(() => {
  if (!props.sortable || !sortKey.value) {
    return [...props.data];
  }
  
  const column = props.columns.find(col => col.key === sortKey.value);
  if (!column || !column.sortable) {
    return [...props.data];
  }
  
  return [...props.data].sort((a, b) => {
    const aValue = getValue(a, sortKey.value);
    const bValue = getValue(b, sortKey.value);
    
    // Custom sorter if provided
    if (column.sorter) {
      return sortOrder.value === 'asc' 
        ? column.sorter(aValue, bValue, a, b)
        : column.sorter(bValue, aValue, b, a);
    }
    
    // Default sorting
    if (aValue === bValue) return 0;
    
    // Handle null/undefined values
    if (aValue === null || aValue === undefined) return sortOrder.value === 'asc' ? -1 : 1;
    if (bValue === null || bValue === undefined) return sortOrder.value === 'asc' ? 1 : -1;
    
    // Compare based on value type
    if (typeof aValue === 'string') {
      return sortOrder.value === 'asc' 
        ? aValue.localeCompare(bValue) 
        : bValue.localeCompare(aValue);
    }
    
    return sortOrder.value === 'asc' 
      ? aValue - bValue 
      : bValue - aValue;
  });
});

const displayData = computed(() => {
  if (!props.pagination || props.totalItems > 0) {
    // Server-side pagination, just return the data
    return sortedData.value;
  }
  
  // Client-side pagination
  const start = (currentPage.value - 1) * props.pageSize;
  const end = start + props.pageSize;
  return sortedData.value.slice(start, end);
});

const totalPages = computed(() => {
  const total = props.totalItems > 0 ? props.totalItems : props.data.length;
  return Math.ceil(total / props.pageSize);
});

const paginationInfo = computed(() => {
  const total = props.totalItems > 0 ? props.totalItems : props.data.length;
  const from = total === 0 ? 0 : (currentPage.value - 1) * props.pageSize + 1;
  const to = Math.min(currentPage.value * props.pageSize, total);
  
  return { from, to };
});

const paginationPages = computed(() => {
  if (totalPages.value <= 7) {
    return Array.from({ length: totalPages.value }, (_, i) => i + 1);
  }
  
  // Complex pagination with ellipsis
  const pages = [];
  
  // Always show first page
  pages.push(1);
  
  // Show ellipsis or page 2
  if (currentPage.value > 3) {
    pages.push('...');
  } else {
    pages.push(2);
  }
  
  // Middle pages
  let startPage = Math.max(3, currentPage.value - 1);
  let endPage = Math.min(totalPages.value - 2, currentPage.value + 1);
  
  // Adjust if current page is near start or end
  if (currentPage.value <= 3) {
    endPage = 4;
  } else if (currentPage.value >= totalPages.value - 2) {
    startPage = totalPages.value - 3;
  }
  
  // Add middle pages
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  
  // Show ellipsis or second-to-last page
  if (currentPage.value < totalPages.value - 2) {
    pages.push('...');
  } else {
    pages.push(totalPages.value - 1);
  }
  
  // Always show last page
  pages.push(totalPages.value);
  
  return pages;
});

const allSelected = computed(() => {
  return props.data.length > 0 && selectedItems.value.length === props.data.length;
});

const someSelected = computed(() => {
  return selectedItems.value.length > 0 && selectedItems.value.length < props.data.length;
});

// Methods
const getValue = (item, key) => {
  if (!key) return '';
  
  // Handle nested keys (e.g. 'user.name')
  if (key.includes('.')) {
    return key.split('.').reduce((obj, path) => {
      return obj && obj[path] !== undefined ? obj[path] : null;
    }, item);
  }
  
  return item[key];
};

const sortBy = (key) => {
  if (sortKey.value === key) {
    // Toggle sort order
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    // New sort key
    sortKey.value = key;
    sortOrder.value = props.defaultSortOrder;
  }
  
  emit('sort-change', { key: sortKey.value, order: sortOrder.value });
};

const handleRowClick = (item, index) => {
  emit('row-click', item, index);
};

const isSelected = (item) => {
  return selectedItems.value.includes(item);
};

const toggleSelect = (item) => {
  const index = selectedItems.value.indexOf(item);
  if (index === -1) {
    selectedItems.value.push(item);
  } else {
    selectedItems.value.splice(index, 1);
  }
  
  emit('selection-change', selectedItems.value);
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = [];
  } else {
    selectedItems.value = [...props.data];
  }
  
  emit('selection-change', selectedItems.value);
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  
  currentPage.value = page;
  emit('page-change', page);
  emit('update:currentPage', page);
};

// Watch for prop changes
watch(() => props.currentPageProp, (newPage) => {
  if (newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

// Expose methods
defineExpose({
  sortBy,
  changePage,
  selectedItems,
  toggleSelect,
  toggleSelectAll
});
</script>

<style scoped>
.table-container {
  @apply w-full;
}

.table-toolbar {
  @apply mb-4;
}

.table-wrapper {
  @apply w-full overflow-hidden rounded-lg shadow;
}

.table-responsive {
  @apply overflow-x-auto;
}

.table {
  @apply min-w-full divide-y divide-neutral-200 dark:divide-neutral-700;
}

.table-header {
  @apply bg-neutral-50 dark:bg-neutral-800;
}

.table-cell {
  @apply px-6 py-3 text-left text-sm font-medium text-neutral-700 dark:text-neutral-200;
}

.table-header .table-cell {
  @apply text-xs uppercase tracking-wider;
}

.table-body {
  @apply bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-800;
}

.table-row {
  @apply transition-colors duration-200;
}

.table-row:hover {
  @apply bg-neutral-50 dark:bg-neutral-800;
}

.table-body .table-cell {
  @apply py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300;
}

.selection-cell {
  @apply w-12;
}

.actions-cell {
  @apply w-24 text-right;
}

.selected-row {
  @apply bg-primary-50 dark:bg-primary-900/20;
}

.table-striped .table-row:nth-child(even) {
  @apply bg-neutral-50 dark:bg-neutral-800/50;
}

.table-bordered {
  @apply border border-neutral-200 dark:border-neutral-700;
}

.table-bordered .table-cell {
  @apply border border-neutral-200 dark:border-neutral-700;
}

.table-compact .table-cell {
  @apply px-3 py-2;
}

.empty-cell, .loading-cell {
  @apply text-center py-8;
}

.table-pagination {
  @apply mt-4;
}

.pagination-button {
  @apply px-3 py-1 text-sm text-neutral-600 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1;
}

.pagination-page-button {
  @apply px-3 py-1 text-sm text-neutral-600 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 mx-1;
}
</style>
