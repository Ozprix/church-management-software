<template>
  <div class="search-results-page">
    <div class="search-header">
      <h1 class="search-title">{{ $t('search.results') }}</h1>
      
      <!-- Search input -->
      <div class="search-input-container">
        <div class="search-icon">
          <i class="fas fa-search"></i>
        </div>
        <input
          ref="searchInput"
          type="text"
          v-model="query"
          :placeholder="$t('common.search')"
          @keydown.enter="performSearch"
          class="search-input"
        />
        <div class="search-actions">
          <VoiceSearch 
            :language="i18n.currentLocale.value" 
            @result="handleVoiceSearchResult" 
            v-if="!query"
          />
          <button v-if="query" @click="clearSearch" class="clear-button" :title="$t('common.clear')">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="search-content">
      <!-- Filters sidebar -->
      <div class="search-sidebar">
        <SearchFilters 
          :initial-filters="filters"
          @filter-change="applyFilters"
        />
        
        <!-- Search History -->
        <SearchHistory 
          @select-search="applyHistorySearch" 
          :max-items="5"
        />
      </div>
      
      <!-- Results content -->
      <div class="search-main">
        <!-- Results stats -->
        <div class="search-stats">
          <div class="stats-info">
            <span v-if="search.totalResults.value > 0">
              {{ search.totalResults.value }} {{ search.totalResults.value === 1 ? $t('search.result') : $t('search.results') }}
              <span v-if="query"> {{ $t('search.for') }} "{{ query }}"</span>
            </span>
            <span v-else-if="search.isSearching.value">
              {{ $t('common.searching') }}...
            </span>
            <span v-else>
              {{ $t('search.noResults', { query }) }}
            </span>
          </div>
          
          <!-- Sort options -->
          <div class="sort-options" v-if="search.totalResults.value > 0">
            <label class="sort-label">{{ $t('search.sortBy') }}:</label>
            <select v-model="sortOption" class="sort-select" @change="applySorting">
              <option value="relevance">{{ $t('search.relevance') }}</option>
              <option value="date_desc">{{ $t('search.newest') }}</option>
              <option value="date_asc">{{ $t('search.oldest') }}</option>
              <option value="name_asc">{{ $t('search.nameAZ') }}</option>
              <option value="name_desc">{{ $t('search.nameZA') }}</option>
            </select>
          </div>
        </div>
        
        <!-- Loading state -->
        <div v-if="search.isSearching.value" class="search-loading">
          <div class="spinner"></div>
          <span>{{ $t('common.searching') }}...</span>
        </div>
        
        <!-- Results list -->
        <div v-else-if="search.searchResults.value.length > 0" class="search-results-list">
          <SearchResultItem 
            v-for="(result, index) in search.searchResults.value" 
            :key="index"
            :result="result"
            :highlighted="false"
          />
          
          <!-- Pagination -->
          <div class="pagination" v-if="search.totalPages.value > 1">
            <button 
              class="pagination-button prev"
              :disabled="search.currentPage.value <= 1"
              @click="prevPage"
            >
              <i class="fas fa-chevron-left"></i>
              {{ $t('common.previous') }}
            </button>
            
            <div class="pagination-info">
              {{ $t('search.page') }} {{ search.currentPage.value }} {{ $t('search.of') }} {{ search.totalPages.value }}
            </div>
            
            <button 
              class="pagination-button next"
              :disabled="search.currentPage.value >= search.totalPages.value"
              @click="nextPage"
            >
              {{ $t('common.next') }}
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
        
        <!-- No results -->
        <div v-else-if="query" class="search-no-results">
          <i class="fas fa-search"></i>
          <h3>{{ $t('search.noResultsFound') }}</h3>
          <p>{{ $t('search.noResultsMessage', { query }) }}</p>
          
          <div class="search-suggestions">
            <h4>{{ $t('search.suggestions') }}:</h4>
            <ul>
              <li>{{ $t('search.checkSpelling') }}</li>
              <li>{{ $t('search.tryDifferentKeywords') }}</li>
              <li>{{ $t('search.tryBroaderSearch') }}</li>
              <li>{{ $t('search.checkFilters') }}</li>
            </ul>
          </div>
        </div>
        
        <!-- Initial state -->
        <div v-else class="search-initial-state">
          <i class="fas fa-search"></i>
          <h3>{{ $t('search.startSearching') }}</h3>
          <p>{{ $t('search.enterSearchTerms') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSearch, SEARCH_TYPES } from '../../services/searchService';
import { useI18n } from '../../services/i18nService';
import { useFuzzyMatch } from '../../services/fuzzyMatchService';
import SearchFilters from './SearchFilters.vue';
import SearchResultItem from './SearchResultItem.vue';
import SearchHistory from './SearchHistory.vue';
import VoiceSearch from './VoiceSearch.vue';

// Get services
const route = useRoute();
const router = useRouter();
const search = useSearch();
const i18n = useI18n();
const fuzzyMatch = useFuzzyMatch();

// Component state
const query = ref('');
const filters = ref({
  type: SEARCH_TYPES.ALL,
  dateFrom: '',
  dateTo: '',
  categories: [],
  statuses: [],
  amountMin: '',
  amountMax: ''
});
const sortOption = ref('relevance');
const searchInput = ref(null);

// Initialize from route query params
onMounted(() => {
  // Get query from route
  if (route.query.q) {
    query.value = route.query.q;
  }
  
  // Get filters from route
  if (route.query.type) {
    filters.value.type = route.query.type;
  }
  
  if (route.query.dateFrom) {
    filters.value.dateFrom = route.query.dateFrom;
  }
  
  if (route.query.dateTo) {
    filters.value.dateTo = route.query.dateTo;
  }
  
  if (route.query.categories) {
    filters.value.categories = Array.isArray(route.query.categories) 
      ? route.query.categories 
      : [route.query.categories];
  }
  
  if (route.query.statuses) {
    filters.value.statuses = Array.isArray(route.query.statuses) 
      ? route.query.statuses 
      : [route.query.statuses];
  }
  
  if (route.query.amountMin) {
    filters.value.amountMin = route.query.amountMin;
  }
  
  if (route.query.amountMax) {
    filters.value.amountMax = route.query.amountMax;
  }
  
  if (route.query.sort) {
    sortOption.value = route.query.sort;
  }
  
  // Focus search input
  if (searchInput.value) {
    searchInput.value.focus();
  }
  
  // Perform initial search if query exists
  if (query.value) {
    performSearch();
  }
});

// Watch for route changes
watch(() => route.query, (newQuery) => {
  if (newQuery.q !== query.value) {
    query.value = newQuery.q || '';
  }
  
  // Update filters if they changed
  if (newQuery.type !== filters.value.type) {
    filters.value.type = newQuery.type || SEARCH_TYPES.ALL;
  }
  
  // Perform search if query exists
  if (query.value) {
    performSearch();
  }
}, { deep: true });

// Methods
const performSearch = async () => {
  if (!query.value.trim()) return;
  
  // Set search parameters
  search.searchQuery.value = query.value;
  search.searchType.value = filters.value.type || SEARCH_TYPES.ALL;
  
  // Set filters
  const searchFilters = {};
  
  if (filters.value.dateFrom) {
    searchFilters.dateFrom = filters.value.dateFrom;
  }
  
  if (filters.value.dateTo) {
    searchFilters.dateTo = filters.value.dateTo;
  }
  
  if (filters.value.categories && filters.value.categories.length > 0) {
    searchFilters.categories = filters.value.categories;
  }
  
  if (filters.value.statuses && filters.value.statuses.length > 0) {
    searchFilters.statuses = filters.value.statuses;
  }
  
  if (filters.value.amountMin) {
    searchFilters.amountMin = filters.value.amountMin;
  }
  
  if (filters.value.amountMax) {
    searchFilters.amountMax = filters.value.amountMax;
  }
  
  // Set sort options
  const [sortField, sortDirection] = sortOption.value.split('_');
  search.searchSort.value = {
    field: sortField,
    direction: sortDirection || 'desc'
  };
  
  // Enable fuzzy matching for better results
  searchFilters.fuzzyMatch = true;
  searchFilters.fuzzyThreshold = 0.6; // Adjust threshold as needed
  
  // Update URL
  updateUrl();
  
  // Perform search
  await search.search({
    filters: searchFilters
  });
};

const clearSearch = () => {
  query.value = '';
  filters.value = {
    type: SEARCH_TYPES.ALL,
    dateFrom: '',
    dateTo: '',
    categories: [],
    statuses: [],
    amountMin: '',
    amountMax: ''
  };
  sortOption.value = 'relevance';
  
  // Update URL
  updateUrl();
  
  // Clear search results
  search.searchResults.value = [];
  search.totalResults.value = 0;
  
  // Focus search input
  if (searchInput.value) {
    searchInput.value.focus();
  }
};

// Apply search from history item
const applyHistorySearch = (historyItem) => {
  // Set query and filters from history item
  query.value = historyItem.query;
  
  if (historyItem.type) {
    filters.value.type = historyItem.type;
  }
  
  // Perform search
  performSearch();
};

// Handle voice search result
const handleVoiceSearchResult = (result) => {
  if (result) {
    query.value = result;
    performSearch();
  }
};

const applyFilters = (newFilters) => {
  filters.value = { ...newFilters };
  
  // Perform search if query exists
  if (query.value) {
    performSearch();
  }
};

const applySorting = () => {
  // Perform search if query exists
  if (query.value) {
    performSearch();
  }
};

const prevPage = () => {
  if (search.currentPage.value > 1) {
    search.currentPage.value--;
    performSearch();
  }
};

const nextPage = () => {
  if (search.currentPage.value < search.totalPages.value) {
    search.currentPage.value++;
    performSearch();
  }
};

const updateUrl = () => {
  // Build query params
  const queryParams = {
    q: query.value
  };
  
  if (filters.value.type && filters.value.type !== SEARCH_TYPES.ALL) {
    queryParams.type = filters.value.type;
  }
  
  if (filters.value.dateFrom) {
    queryParams.dateFrom = filters.value.dateFrom;
  }
  
  if (filters.value.dateTo) {
    queryParams.dateTo = filters.value.dateTo;
  }
  
  if (filters.value.categories && filters.value.categories.length > 0) {
    queryParams.categories = filters.value.categories;
  }
  
  if (filters.value.statuses && filters.value.statuses.length > 0) {
    queryParams.statuses = filters.value.statuses;
  }
  
  if (filters.value.amountMin) {
    queryParams.amountMin = filters.value.amountMin;
  }
  
  if (filters.value.amountMax) {
    queryParams.amountMax = filters.value.amountMax;
  }
  
  if (sortOption.value !== 'relevance') {
    queryParams.sort = sortOption.value;
  }
  
  if (search.currentPage.value > 1) {
    queryParams.page = search.currentPage.value;
  }
  
  // Update route
  router.replace({ 
    name: 'search-results',
    query: queryParams
  });
};
</script>

<style scoped>
.search-results-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.search-header {
  margin-bottom: 2rem;
}

.search-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #1f2937;
}

.search-input-container {
  display: flex;
  align-items: center;
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  color: #6b7280;
}

.search-input {
  flex: 1;
  height: 3rem;
  padding: 0 0.5rem;
  border: none;
  outline: none;
  background: transparent;
  font-size: 1rem;
  color: #1f2937;
}

.search-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.clear-button {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.clear-button:hover {
  background-color: #f3f4f6;
  color: #6b7280;
}

.search-content {
  display: flex;
  gap: 2rem;
}

.search-sidebar {
  width: 300px;
  flex-shrink: 0;
}

.search-main {
  flex: 1;
  min-width: 0;
}

.search-stats {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.stats-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.sort-options {
  display: flex;
  align-items: center;
}

.sort-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-right: 0.5rem;
}

.sort-select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #1f2937;
  background-color: white;
}

.search-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: #6b7280;
}

.spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  margin-bottom: 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.search-no-results,
.search-initial-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  text-align: center;
}

.search-no-results i,
.search-initial-state i {
  font-size: 3rem;
  color: #e5e7eb;
  margin-bottom: 1rem;
}

.search-no-results h3,
.search-initial-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.search-no-results p,
.search-initial-state p {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.search-suggestions {
  text-align: left;
  max-width: 400px;
}

.search-suggestions h4 {
  font-size: 1rem;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.search-suggestions ul {
  padding-left: 1.5rem;
}

.search-suggestions li {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #1f2937;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-button:hover:not(:disabled) {
  background-color: #f3f4f6;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-button.prev i {
  margin-right: 0.5rem;
}

.pagination-button.next i {
  margin-left: 0.5rem;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Responsive styles */
@media (max-width: 768px) {
  .search-content {
    flex-direction: column;
  }
  
  .search-sidebar {
    width: 100%;
    margin-bottom: 1.5rem;
  }
  
  .search-stats {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .sort-options {
    width: 100%;
  }
  
  .sort-select {
    flex: 1;
  }
}

/* Dark mode support */
:global(.dark) .search-title {
  color: #f3f4f6;
}

:global(.dark) .search-input-container {
  background-color: #1f2937;
  border-color: #4b5563;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
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

:global(.dark) .stats-info {
  color: #9ca3af;
}

:global(.dark) .sort-label {
  color: #9ca3af;
}

:global(.dark) .sort-select {
  border-color: #4b5563;
  color: #f3f4f6;
  background-color: #374151;
}

:global(.dark) .search-loading {
  color: #9ca3af;
}

:global(.dark) .spinner {
  border-color: #4b5563;
  border-top-color: #3b82f6;
}

:global(.dark) .search-no-results i,
:global(.dark) .search-initial-state i {
  color: #4b5563;
}

:global(.dark) .search-no-results h3,
:global(.dark) .search-initial-state h3 {
  color: #f3f4f6;
}

:global(.dark) .search-no-results p,
:global(.dark) .search-initial-state p {
  color: #9ca3af;
}

:global(.dark) .search-suggestions h4 {
  color: #f3f4f6;
}

:global(.dark) .search-suggestions li {
  color: #9ca3af;
}

:global(.dark) .pagination {
  border-top-color: #4b5563;
}

:global(.dark) .pagination-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #f3f4f6;
}

:global(.dark) .pagination-button:hover:not(:disabled) {
  background-color: #4b5563;
}

:global(.dark) .pagination-info {
  color: #9ca3af;
}
</style>
