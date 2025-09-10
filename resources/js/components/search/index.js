import GlobalSearch from './GlobalSearch.vue';
import SearchFilters from './SearchFilters.vue';
import SearchResults from './SearchResults.vue';
import SearchResultItem from './SearchResultItem.vue';
import SearchHistory from './SearchHistory.vue';
import VoiceSearch from './VoiceSearch.vue';

export {
  GlobalSearch,
  SearchFilters,
  SearchResults,
  SearchResultItem,
  SearchHistory,
  VoiceSearch
};

// Plugin to register all search components globally
export default {
  install(app) {
    app.component('GlobalSearch', GlobalSearch);
    app.component('SearchFilters', SearchFilters);
    app.component('SearchResults', SearchResults);
    app.component('SearchResultItem', SearchResultItem);
    app.component('SearchHistory', SearchHistory);
    app.component('VoiceSearch', VoiceSearch);
  }
};
