<template>
  <div class="global-search">
    <!-- Search Input -->
    <div class="search-input-container" :class="{ 'is-active': isActive }">
      <div class="search-input-wrapper">
        <div class="search-icon">
          <i class="fas fa-search"></i>
        </div>
        <input
          ref="searchInput"
          type="text"
          v-model="query"
          :placeholder="$t('common.search')"
          @focus="activateSearch"
          @keydown.esc="deactivateSearch"
          @keydown.enter="performSearch"
          @keydown.down="highlightNextResult"
          @keydown.up="highlightPrevResult"
          class="search-input"
        />
        <div class="search-actions" v-if="query">
          <button @click="clearSearch" class="clear-button" title="Clear search">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <!-- Search Type Selector -->
      <div class="search-type-selector" v-if="isActive">
        <button 
          v-for="(label, type) in searchTypes" 
          :key="type"
          @click="selectSearchType(type)"
          :class="{ 'active': selectedType === type }"
          class="search-type-button"
        >
          {{ label }}
        </button>
      </div>
    </div>
    
    <!-- Search Results Dropdown -->
    <div v-if="isActive && (hasResults || isSearching)" class="search-results-dropdown">
      <!-- Loading State -->
      <div v-if="isSearching" class="search-loading">
        <div class="spinner"></div>
        <span>{{ $t('common.searching') }}...</span>
      </div>
      
      <!-- Results -->
      <div v-else-if="hasResults" class="search-results">
        <div class="search-results-header">
          <span>{{ totalResults }} {{ totalResults === 1 ? 'result' : 'results' }}</span>
          <router-link :to="{ name: 'search-results', query: { q: query, type: selectedType } }" class="view-all-link">
            {{ $t('common.viewAll') }}
          </router-link>
        </div>
        
        <div class="search-results-list">
          <div 
            v-for="(result, index) in results" 
            :key="index"
            @click="goToResult(result)"
            @mouseover="highlightedIndex = index"
            :class="{ 'highlighted': highlightedIndex === index }"
            class="search-result-item"
          >
            <div class="result-icon" :class="result.type">
              <i :class="getIconForType(result.type)"></i>
            </div>
            <div class="result-content">
              <div class="result-title">{{ getResultTitle(result) }}</div>
              <div class="result-subtitle">{{ getResultSubtitle(result) }}</div>
            </div>
            <div class="result-type">{{ getTypeLabel(result.type) }}</div>
          </div>
        </div>
      </div>
      
      <!-- No Results -->
      <div v-else-if="query && !isSearching && !hasResults" class="search-no-results">
        <i class="fas fa-search"></i>
        <span>{{ $t('search.noResults', { query }) }}</span>
      </div>
      
      <!-- Recent Searches -->
      <div v-if="isActive && !query && recentSearches.length > 0" class="recent-searches">
        <div class="recent-searches-header">
          <span>{{ $t('search.recentSearches') }}</span>
          <button @click="clearRecentSearches" class="clear-recent-button">
            {{ $t('common.clear') }}
          </button>
        </div>
        
        <div class="recent-searches-list">
          <div 
            v-for="(search, index) in recentSearches" 
            :key="index"
            @click="useRecentSearch(search)"
            class="recent-search-item"
          >
            <div class="recent-search-icon">
              <i class="fas fa-history"></i>
            </div>
            <div class="recent-search-content">
              <div class="recent-search-query">{{ search.query }}</div>
              <div class="recent-search-type">{{ getTypeLabel(search.type) }}</div>
            </div>
            <div class="recent-search-time">{{ formatTime(search.timestamp) }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Backdrop for closing search -->
    <div v-if="isActive" class="search-backdrop" @click="deactivateSearch"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useSearch, SEARCH_TYPES } from '../../services/searchService';
import { useI18n } from '../../services/i18nService';
import { formatDistance } from 'date-fns';

// Get services
const router = useRouter();
const search = useSearch();
const i18n = useI18n();

// Component state
const isActive = ref(false);
const query = ref('');
const selectedType = ref(SEARCH_TYPES.ALL);
const results = ref([]);
const totalResults = ref(0);
const highlightedIndex = ref(-1);
const searchInput = ref(null);

// Computed properties
const isSearching = computed(() => search.isSearching.value);
const hasResults = computed(() => results.value.length > 0);
const recentSearches = computed(() => search.recentSearches.value);

// Search types with labels
const searchTypes = {
  [SEARCH_TYPES.ALL]: i18n.t('search.all'),
  [SEARCH_TYPES.MEMBERS]: i18n.t('search.members'),
  [SEARCH_TYPES.DONATIONS]: i18n.t('search.donations'),
  [SEARCH_TYPES.EVENTS]: i18n.t('search.events'),
  [SEARCH_TYPES.ATTENDANCE]: i18n.t('search.attendance')
};

// Watch for query changes
watch(query, async (newQuery) => {
  if (newQuery.trim() && newQuery.length >= 2) {
    await performSearch();
  } else {
    results.value = [];
    totalResults.value = 0;
  }
});

// Activate search
const activateSearch = () => {
  isActive.value = true;
  nextTick(() => {
    searchInput.value.focus();
  });
};

// Deactivate search
const deactivateSearch = () => {
  isActive.value = false;
  highlightedIndex.value = -1;
};

// Clear search
const clearSearch = () => {
  query.value = '';
  results.value = [];
  totalResults.value = 0;
  highlightedIndex.value = -1;
};

// Perform search
const performSearch = async () => {
  if (!query.value.trim()) return;
  
  // Set search parameters
  search.searchQuery.value = query.value;
  search.searchType.value = selectedType.value;
  search.currentPage.value = 1;
  search.resultsPerPage.value = 5; // Show only 5 results in dropdown
  
  // Perform search
  await search.search();
  
  // Update results
  results.value = search.searchResults.value;
  totalResults.value = search.totalResults.value;
  highlightedIndex.value = -1;
};

// Select search type
const selectSearchType = (type) => {
  selectedType.value = type;
  performSearch();
};

// Highlight next result
const highlightNextResult = () => {
  if (results.value.length === 0) return;
  
  highlightedIndex.value = (highlightedIndex.value + 1) % results.value.length;
  
  // Scroll to highlighted result
  scrollToHighlighted();
};

// Highlight previous result
const highlightPrevResult = () => {
  if (results.value.length === 0) return;
  
  highlightedIndex.value = highlightedIndex.value <= 0 ? 
    results.value.length - 1 : 
    highlightedIndex.value - 1;
  
  // Scroll to highlighted result
  scrollToHighlighted();
};

// Scroll to highlighted result
const scrollToHighlighted = () => {
  nextTick(() => {
    const highlightedElement = document.querySelector('.search-result-item.highlighted');
    if (highlightedElement) {
      highlightedElement.scrollIntoView({ block: 'nearest' });
    }
  });
};

// Go to result
const goToResult = (result) => {
  // Navigate to result based on type
  switch (result.type) {
    case 'member':
      router.push({ name: 'member-details', params: { id: result.id } });
      break;
    case 'donation':
      router.push({ name: 'donation-details', params: { id: result.id } });
      break;
    case 'event':
      router.push({ name: 'event-details', params: { id: result.id } });
      break;
    case 'attendance':
      router.push({ name: 'attendance-details', params: { id: result.id } });
      break;
    default:
      router.push({ name: 'search-results', query: { q: query.value, type: selectedType.value } });
  }
  
  // Deactivate search
  deactivateSearch();
};

// Use recent search
const useRecentSearch = (recentSearch) => {
  query.value = recentSearch.query;
  selectedType.value = recentSearch.type;
  performSearch();
};

// Clear recent searches
const clearRecentSearches = () => {
  search.clearRecentSearches();
};

// Get icon for result type
const getIconForType = (type) => {
  switch (type) {
    case 'member':
      return 'fas fa-user';
    case 'donation':
      return 'fas fa-hand-holding-usd';
    case 'event':
      return 'fas fa-calendar-alt';
    case 'attendance':
      return 'fas fa-clipboard-list';
    case 'group':
      return 'fas fa-users';
    case 'sermon':
      return 'fas fa-bible';
    case 'resource':
      return 'fas fa-file-alt';
    default:
      return 'fas fa-search';
  }
};

// Get type label
const getTypeLabel = (type) => {
  switch (type) {
    case SEARCH_TYPES.ALL:
      return i18n.t('search.all');
    case SEARCH_TYPES.MEMBERS:
    case 'member':
      return i18n.t('search.member');
    case SEARCH_TYPES.DONATIONS:
    case 'donation':
      return i18n.t('search.donation');
    case SEARCH_TYPES.EVENTS:
    case 'event':
      return i18n.t('search.event');
    case SEARCH_TYPES.ATTENDANCE:
    case 'attendance':
      return i18n.t('search.attendance');
    case 'group':
      return i18n.t('search.group');
    case 'sermon':
      return i18n.t('search.sermon');
    case 'resource':
      return i18n.t('search.resource');
    default:
      return type;
  }
};

// Get result title
const getResultTitle = (result) => {
  switch (result.type) {
    case 'member':
      return `${result.first_name} ${result.last_name}`;
    case 'donation':
      return `${result.amount} ${result.currency || 'USD'} - ${result.donor_name || 'Anonymous'}`;
    case 'event':
      return result.name || 'Unnamed Event';
    case 'attendance':
      return result.event_name || 'Unnamed Event';
    default:
      return result.title || result.name || 'Unnamed Item';
  }
};

// Get result subtitle
const getResultSubtitle = (result) => {
  switch (result.type) {
    case 'member':
      return result.email || result.phone || '';
    case 'donation':
      return result.date ? i18n.formatDateTime(result.date, 'PPP') : '';
    case 'event':
      return result.date ? i18n.formatDateTime(result.date, 'PPP') : '';
    case 'attendance':
      return `${result.count || 0} attendees`;
    default:
      return result.description || '';
  }
};

// Format time for recent searches
const formatTime = (timestamp) => {
  try {
    return formatDistance(new Date(timestamp), new Date(), { addSuffix: true });
  } catch (error) {
    return '';
  }
};

// Handle keyboard shortcuts
const handleKeyDown = (event) => {
  // Ctrl/Cmd + K to focus search
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault();
    activateSearch();
  }
  
  // Escape to close search
  if (event.key === 'Escape' && isActive.value) {
    deactivateSearch();
  }
};

// Setup event listeners
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

// Clean up event listeners
onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.global-search {
  position: relative;
  z-index: 50;
}

.search-input-container {
  position: relative;
  width: 100%;
  max-width: 400px;
  transition: all 0.3s ease;
}

.search-input-container.is-active {
  max-width: 600px;
}

.search-input-wrapper {
  display: flex;
  align-items: center;
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  overflow: hidden;
  transition: all 0.2s ease;
}

.search-input-container.is-active .search-input-wrapper {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  color: #6b7280;
}

.search-input {
  flex: 1;
  height: 40px;
  padding: 0 0.5rem;
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.875rem;
  color: #1f2937;
}

.search-actions {
  display: flex;
  align-items: center;
}

.clear-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: transparent;
  border: none;
  color: #6b7280;
  cursor: pointer;
}

.clear-button:hover {
  color: #1f2937;
}

.search-type-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.search-type-button {
  padding: 0.25rem 0.5rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.search-type-button:hover {
  background-color: #e5e7eb;
}

.search-type-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.search-results-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  max-height: 400px;
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow-y: auto;
  z-index: 50;
}

.search-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #6b7280;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  margin-right: 0.5rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.search-results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #6b7280;
}

.view-all-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

.search-results-list {
  max-height: 300px;
  overflow-y: auto;
}

.search-result-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.search-result-item:hover,
.search-result-item.highlighted {
  background-color: #f3f4f6;
}

.result-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background-color: #e5e7eb;
  border-radius: 0.25rem;
  margin-right: 0.75rem;
  color: #4b5563;
}

.result-icon.member {
  background-color: #dbeafe;
  color: #2563eb;
}

.result-icon.donation {
  background-color: #d1fae5;
  color: #059669;
}

.result-icon.event {
  background-color: #fef3c7;
  color: #d97706;
}

.result-icon.attendance {
  background-color: #e0e7ff;
  color: #4f46e5;
}

.result-content {
  flex: 1;
  min-width: 0;
}

.result-title {
  font-weight: 500;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-type {
  font-size: 0.75rem;
  color: #6b7280;
  margin-left: 0.75rem;
}

.search-no-results {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #6b7280;
  text-align: center;
}

.search-no-results i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

.recent-searches-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #6b7280;
}

.clear-recent-button {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.75rem;
  cursor: pointer;
}

.clear-recent-button:hover {
  text-decoration: underline;
}

.recent-searches-list {
  max-height: 300px;
  overflow-y: auto;
}

.recent-search-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.recent-search-item:hover {
  background-color: #f3f4f6;
}

.recent-search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  margin-right: 0.75rem;
  color: #6b7280;
}

.recent-search-content {
  flex: 1;
  min-width: 0;
}

.recent-search-query {
  font-weight: 500;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.recent-search-type {
  font-size: 0.75rem;
  color: #6b7280;
}

.recent-search-time {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-left: 0.75rem;
}

.search-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.3);
  z-index: 40;
}

/* Dark mode support */
:global(.dark) .search-input-wrapper {
  background-color: #1f2937;
  border-color: #4b5563;
}

:global(.dark) .search-input {
  color: #f3f4f6;
}

:global(.dark) .search-input::placeholder {
  color: #9ca3af;
}

:global(.dark) .search-icon,
:global(.dark) .clear-button {
  color: #9ca3af;
}

:global(.dark) .clear-button:hover {
  color: #f3f4f6;
}

:global(.dark) .search-type-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:global(.dark) .search-type-button:hover {
  background-color: #4b5563;
}

:global(.dark) .search-type-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

:global(.dark) .search-results-dropdown {
  background-color: #1f2937;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}

:global(.dark) .search-results-header {
  border-bottom-color: #4b5563;
  color: #9ca3af;
}

:global(.dark) .search-result-item:hover,
:global(.dark) .search-result-item.highlighted {
  background-color: #374151;
}

:global(.dark) .result-title {
  color: #f3f4f6;
}

:global(.dark) .result-subtitle,
:global(.dark) .result-type {
  color: #9ca3af;
}

:global(.dark) .recent-searches-header {
  border-bottom-color: #4b5563;
  color: #9ca3af;
}

:global(.dark) .recent-search-item:hover {
  background-color: #374151;
}

:global(.dark) .recent-search-query {
  color: #f3f4f6;
}

:global(.dark) .recent-search-type {
  color: #9ca3af;
}

:global(.dark) .recent-search-time {
  color: #6b7280;
}

:global(.dark) .spinner {
  border-color: #4b5563;
  border-top-color: #3b82f6;
}
</style>
