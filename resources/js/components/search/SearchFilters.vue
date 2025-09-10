<template>
  <div class="search-filters">
    <div class="filters-header">
      <h3 class="filters-title">{{ $t('search.filters') }}</h3>
      <button 
        v-if="hasActiveFilters" 
        class="clear-filters-button"
        @click="clearAllFilters"
      >
        {{ $t('search.clearFilters') }}
      </button>
    </div>
    
    <!-- Data Type Filter -->
    <div class="filter-section">
      <h4 class="filter-section-title">{{ $t('search.dataType') }}</h4>
      <div class="filter-options">
        <label 
          v-for="(label, type) in dataTypes" 
          :key="type"
          class="filter-option"
        >
          <input 
            type="radio" 
            :value="type" 
            v-model="selectedType"
            @change="applyTypeFilter"
          >
          <span class="option-label">{{ label }}</span>
        </label>
      </div>
    </div>
    
    <!-- Date Range Filter -->
    <div class="filter-section">
      <h4 class="filter-section-title">{{ $t('search.dateRange') }}</h4>
      <div class="date-range-inputs">
        <div class="date-input-group">
          <label class="date-label">{{ $t('search.from') }}</label>
          <input 
            type="date" 
            v-model="dateRange.from"
            class="date-input"
            @change="applyDateFilter"
          >
        </div>
        <div class="date-input-group">
          <label class="date-label">{{ $t('search.to') }}</label>
          <input 
            type="date" 
            v-model="dateRange.to"
            class="date-input"
            @change="applyDateFilter"
          >
        </div>
      </div>
      <div class="date-presets">
        <button 
          v-for="(label, preset) in datePresets" 
          :key="preset"
          class="date-preset-button"
          :class="{ active: currentDatePreset === preset }"
          @click="applyDatePreset(preset)"
        >
          {{ label }}
        </button>
      </div>
    </div>
    
    <!-- Category Filter (conditional based on type) -->
    <div v-if="showCategoryFilter" class="filter-section">
      <h4 class="filter-section-title">{{ getCategoryTitle() }}</h4>
      <div class="filter-options">
        <label 
          v-for="(category, index) in categories" 
          :key="index"
          class="filter-option"
        >
          <input 
            type="checkbox" 
            :value="category.value" 
            v-model="selectedCategories"
            @change="applyCategoryFilter"
          >
          <span class="option-label">{{ category.label }}</span>
        </label>
      </div>
    </div>
    
    <!-- Status Filter (conditional based on type) -->
    <div v-if="showStatusFilter" class="filter-section">
      <h4 class="filter-section-title">{{ $t('search.status') }}</h4>
      <div class="filter-options">
        <label 
          v-for="(status, index) in statuses" 
          :key="index"
          class="filter-option"
        >
          <input 
            type="checkbox" 
            :value="status.value" 
            v-model="selectedStatuses"
            @change="applyStatusFilter"
          >
          <span class="option-label">{{ status.label }}</span>
        </label>
      </div>
    </div>
    
    <!-- Amount Range Filter (only for donations) -->
    <div v-if="selectedType === 'donations'" class="filter-section">
      <h4 class="filter-section-title">{{ $t('search.amountRange') }}</h4>
      <div class="amount-range-inputs">
        <div class="amount-input-group">
          <label class="amount-label">{{ $t('search.min') }}</label>
          <input 
            type="number" 
            v-model="amountRange.min"
            class="amount-input"
            min="0"
            @change="applyAmountFilter"
          >
        </div>
        <div class="amount-input-group">
          <label class="amount-label">{{ $t('search.max') }}</label>
          <input 
            type="number" 
            v-model="amountRange.max"
            class="amount-input"
            min="0"
            @change="applyAmountFilter"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from '../../services/i18nService';
import { SEARCH_TYPES } from '../../services/searchService';
import { format, subDays, startOfMonth, endOfMonth, startOfYear, endOfYear } from 'date-fns';

// Props
const props = defineProps({
  initialFilters: {
    type: Object,
    default: () => ({})
  }
});

// Emits
const emit = defineEmits(['filter-change']);

// Get i18n service
const i18n = useI18n();

// Data types
const dataTypes = {
  [SEARCH_TYPES.ALL]: i18n.t('search.all'),
  [SEARCH_TYPES.MEMBERS]: i18n.t('search.members'),
  [SEARCH_TYPES.DONATIONS]: i18n.t('search.donations'),
  [SEARCH_TYPES.EVENTS]: i18n.t('search.events'),
  [SEARCH_TYPES.ATTENDANCE]: i18n.t('search.attendance')
};

// Date presets
const datePresets = {
  'all': i18n.t('search.allTime'),
  'today': i18n.t('search.today'),
  'yesterday': i18n.t('search.yesterday'),
  'week': i18n.t('search.thisWeek'),
  'month': i18n.t('search.thisMonth'),
  'year': i18n.t('search.thisYear')
};

// Filter state
const selectedType = ref(props.initialFilters.type || SEARCH_TYPES.ALL);
const dateRange = ref({
  from: props.initialFilters.dateFrom || '',
  to: props.initialFilters.dateTo || ''
});
const currentDatePreset = ref('all');
const selectedCategories = ref(props.initialFilters.categories || []);
const selectedStatuses = ref(props.initialFilters.statuses || []);
const amountRange = ref({
  min: props.initialFilters.amountMin || '',
  max: props.initialFilters.amountMax || ''
});

// Computed properties
const hasActiveFilters = computed(() => {
  return selectedType.value !== SEARCH_TYPES.ALL ||
         dateRange.value.from || 
         dateRange.value.to ||
         selectedCategories.value.length > 0 ||
         selectedStatuses.value.length > 0 ||
         amountRange.value.min || 
         amountRange.value.max;
});

const showCategoryFilter = computed(() => {
  return selectedType.value !== SEARCH_TYPES.ALL;
});

const showStatusFilter = computed(() => {
  return selectedType.value === SEARCH_TYPES.MEMBERS || 
         selectedType.value === SEARCH_TYPES.EVENTS;
});

// Dynamic categories based on selected type
const categories = computed(() => {
  switch (selectedType.value) {
    case SEARCH_TYPES.MEMBERS:
      return [
        { value: 'regular', label: i18n.t('members.regular') },
        { value: 'visitor', label: i18n.t('members.visitor') },
        { value: 'volunteer', label: i18n.t('members.volunteer') },
        { value: 'leader', label: i18n.t('members.leader') },
        { value: 'staff', label: i18n.t('members.staff') }
      ];
    case SEARCH_TYPES.DONATIONS:
      return [
        { value: 'tithe', label: i18n.t('donations.tithe') },
        { value: 'offering', label: i18n.t('donations.offering') },
        { value: 'project', label: i18n.t('donations.project') },
        { value: 'missions', label: i18n.t('donations.missions') },
        { value: 'other', label: i18n.t('donations.other') }
      ];
    case SEARCH_TYPES.EVENTS:
      return [
        { value: 'service', label: i18n.t('events.service') },
        { value: 'meeting', label: i18n.t('events.meeting') },
        { value: 'outreach', label: i18n.t('events.outreach') },
        { value: 'social', label: i18n.t('events.social') },
        { value: 'other', label: i18n.t('events.other') }
      ];
    case SEARCH_TYPES.ATTENDANCE:
      return [
        { value: 'sunday', label: i18n.t('attendance.sunday') },
        { value: 'midweek', label: i18n.t('attendance.midweek') },
        { value: 'special', label: i18n.t('attendance.special') },
        { value: 'other', label: i18n.t('attendance.other') }
      ];
    default:
      return [];
  }
});

// Dynamic statuses based on selected type
const statuses = computed(() => {
  switch (selectedType.value) {
    case SEARCH_TYPES.MEMBERS:
      return [
        { value: 'active', label: i18n.t('members.active') },
        { value: 'inactive', label: i18n.t('members.inactive') },
        { value: 'pending', label: i18n.t('members.pending') }
      ];
    case SEARCH_TYPES.EVENTS:
      return [
        { value: 'upcoming', label: i18n.t('events.upcoming') },
        { value: 'ongoing', label: i18n.t('events.ongoing') },
        { value: 'completed', label: i18n.t('events.completed') },
        { value: 'cancelled', label: i18n.t('events.cancelled') }
      ];
    default:
      return [];
  }
});

// Methods
const getCategoryTitle = () => {
  switch (selectedType.value) {
    case SEARCH_TYPES.MEMBERS:
      return i18n.t('members.category');
    case SEARCH_TYPES.DONATIONS:
      return i18n.t('donations.category');
    case SEARCH_TYPES.EVENTS:
      return i18n.t('events.category');
    case SEARCH_TYPES.ATTENDANCE:
      return i18n.t('attendance.category');
    default:
      return i18n.t('search.category');
  }
};

const applyTypeFilter = () => {
  // Reset category and status filters when type changes
  selectedCategories.value = [];
  selectedStatuses.value = [];
  
  // Emit filter change event
  emitFilterChange();
};

const applyDateFilter = () => {
  // Reset date preset when manual date is selected
  currentDatePreset.value = 'custom';
  
  // Emit filter change event
  emitFilterChange();
};

const applyDatePreset = (preset) => {
  currentDatePreset.value = preset;
  
  const today = new Date();
  
  switch (preset) {
    case 'all':
      dateRange.value.from = '';
      dateRange.value.to = '';
      break;
    case 'today':
      dateRange.value.from = format(today, 'yyyy-MM-dd');
      dateRange.value.to = format(today, 'yyyy-MM-dd');
      break;
    case 'yesterday':
      const yesterday = subDays(today, 1);
      dateRange.value.from = format(yesterday, 'yyyy-MM-dd');
      dateRange.value.to = format(yesterday, 'yyyy-MM-dd');
      break;
    case 'week':
      dateRange.value.from = format(subDays(today, 7), 'yyyy-MM-dd');
      dateRange.value.to = format(today, 'yyyy-MM-dd');
      break;
    case 'month':
      dateRange.value.from = format(startOfMonth(today), 'yyyy-MM-dd');
      dateRange.value.to = format(endOfMonth(today), 'yyyy-MM-dd');
      break;
    case 'year':
      dateRange.value.from = format(startOfYear(today), 'yyyy-MM-dd');
      dateRange.value.to = format(endOfYear(today), 'yyyy-MM-dd');
      break;
  }
  
  // Emit filter change event
  emitFilterChange();
};

const applyCategoryFilter = () => {
  emitFilterChange();
};

const applyStatusFilter = () => {
  emitFilterChange();
};

const applyAmountFilter = () => {
  emitFilterChange();
};

const clearAllFilters = () => {
  selectedType.value = SEARCH_TYPES.ALL;
  dateRange.value.from = '';
  dateRange.value.to = '';
  currentDatePreset.value = 'all';
  selectedCategories.value = [];
  selectedStatuses.value = [];
  amountRange.value.min = '';
  amountRange.value.max = '';
  
  // Emit filter change event
  emitFilterChange();
};

const emitFilterChange = () => {
  emit('filter-change', {
    type: selectedType.value,
    dateFrom: dateRange.value.from,
    dateTo: dateRange.value.to,
    categories: selectedCategories.value,
    statuses: selectedStatuses.value,
    amountMin: amountRange.value.min,
    amountMax: amountRange.value.max
  });
};

// Initialize filters from props
onMounted(() => {
  // If date range is provided in initial filters, set custom preset
  if (props.initialFilters.dateFrom || props.initialFilters.dateTo) {
    currentDatePreset.value = 'custom';
  }
});
</script>

<style scoped>
.search-filters {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filters-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  color: #1f2937;
}

.clear-filters-button {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.875rem;
  cursor: pointer;
}

.clear-filters-button:hover {
  text-decoration: underline;
}

.filter-section {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.filter-section:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.filter-section-title {
  font-size: 1rem;
  font-weight: 500;
  margin-top: 0;
  margin-bottom: 0.75rem;
  color: #374151;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-option {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.filter-option input {
  margin-right: 0.5rem;
}

.option-label {
  font-size: 0.875rem;
  color: #4b5563;
}

.date-range-inputs,
.amount-range-inputs {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.date-input-group,
.amount-input-group {
  flex: 1;
}

.date-label,
.amount-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.date-input,
.amount-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.date-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.date-preset-button {
  padding: 0.25rem 0.5rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #4b5563;
  cursor: pointer;
}

.date-preset-button:hover {
  background-color: #e5e7eb;
}

.date-preset-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

/* Dark mode support */
:global(.dark) .search-filters {
  background-color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

:global(.dark) .filters-title {
  color: #f3f4f6;
}

:global(.dark) .clear-filters-button {
  color: #60a5fa;
}

:global(.dark) .filter-section {
  border-bottom-color: #4b5563;
}

:global(.dark) .filter-section-title {
  color: #e5e7eb;
}

:global(.dark) .option-label {
  color: #d1d5db;
}

:global(.dark) .date-label,
:global(.dark) .amount-label {
  color: #9ca3af;
}

:global(.dark) .date-input,
:global(.dark) .amount-input {
  border-color: #4b5563;
  background-color: #374151;
  color: #f3f4f6;
}

:global(.dark) .date-preset-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:global(.dark) .date-preset-button:hover {
  background-color: #4b5563;
}

:global(.dark) .date-preset-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}
</style>
