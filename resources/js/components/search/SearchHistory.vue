<template>
  <div class="search-history">
    <div class="history-header">
      <h3 class="history-title">{{ $t('search.searchHistory') }}</h3>
      <button 
        v-if="recentSearches.length > 0" 
        @click="clearHistory" 
        class="clear-history-button"
      >
        {{ $t('search.clearHistory') }}
      </button>
    </div>
    
    <div v-if="recentSearches.length > 0" class="history-list">
      <div 
        v-for="(search, index) in recentSearches" 
        :key="index" 
        class="history-item"
        @click="selectSearch(search)"
      >
        <div class="history-item-content">
          <div class="history-query">{{ search.query }}</div>
          <div class="history-type">{{ getTypeLabel(search.type) }}</div>
        </div>
        <div class="history-time">{{ formatTime(search.timestamp) }}</div>
      </div>
    </div>
    
    <div v-else class="history-empty">
      <p>{{ $t('search.noRecentSearches') }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useSearch } from '../../services/searchService';
import { useI18n } from '../../services/i18nService';
import { formatDistanceToNow } from 'date-fns';

// Get services
const search = useSearch();
const i18n = useI18n();

// Props
const props = defineProps({
  maxItems: {
    type: Number,
    default: 5
  }
});

// Emits
const emit = defineEmits(['select-search']);

// Computed
const recentSearches = computed(() => {
  return search.recentSearches.value.slice(0, props.maxItems);
});

// Methods
const getTypeLabel = (type) => {
  return i18n.t(`search.${type.toLowerCase()}`) || type;
};

const formatTime = (timestamp) => {
  try {
    return formatDistanceToNow(new Date(timestamp), { 
      addSuffix: true,
      locale: i18n.dateLocale.value
    });
  } catch (error) {
    return '';
  }
};

const selectSearch = (searchItem) => {
  emit('select-search', searchItem);
};

const clearHistory = () => {
  search.clearRecentSearches();
};
</script>

<style scoped>
.search-history {
  margin-top: 1.5rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.history-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.clear-history-button {
  font-size: 0.875rem;
  color: #6b7280;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  transition: all 0.2s ease;
}

.clear-history-button:hover {
  color: #ef4444;
  background-color: #fee2e2;
}

.history-list {
  max-height: 300px;
  overflow-y: auto;
}

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.history-item:last-child {
  border-bottom: none;
}

.history-item:hover {
  background-color: #f9fafb;
}

.history-item-content {
  flex: 1;
  min-width: 0;
}

.history-query {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.history-type {
  font-size: 0.75rem;
  color: #6b7280;
}

.history-time {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-left: 1rem;
}

.history-empty {
  padding: 1.5rem;
  text-align: center;
  color: #6b7280;
}

/* Dark mode support */
:global(.dark) .search-history {
  background-color: #1f2937;
}

:global(.dark) .history-header {
  border-bottom-color: #374151;
}

:global(.dark) .history-title {
  color: #f3f4f6;
}

:global(.dark) .clear-history-button {
  color: #9ca3af;
}

:global(.dark) .clear-history-button:hover {
  color: #ef4444;
  background-color: rgba(239, 68, 68, 0.1);
}

:global(.dark) .history-item {
  border-bottom-color: #374151;
}

:global(.dark) .history-item:hover {
  background-color: #374151;
}

:global(.dark) .history-query {
  color: #f3f4f6;
}

:global(.dark) .history-type {
  color: #9ca3af;
}

:global(.dark) .history-time {
  color: #6b7280;
}

:global(.dark) .history-empty {
  color: #9ca3af;
}
</style>
