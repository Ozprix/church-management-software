<template>
  <div class="language-selector">
    <button 
      class="language-button" 
      @click="toggleDropdown"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      <span class="flag">{{ localeMetadata.flag }}</span>
      <span class="language-name">{{ localeMetadata.nativeName }}</span>
      <i class="fas fa-chevron-down" :class="{ 'rotate-180': isOpen }"></i>
    </button>
    
    <div 
      v-if="isOpen" 
      class="language-dropdown"
      ref="dropdown"
    >
      <div class="language-search" v-if="Object.keys(availableLocales).length > 5">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search language..." 
          @input="filterLanguages"
          ref="searchInput"
        >
      </div>
      
      <div class="language-list">
        <button 
          v-for="(locale, code) in filteredLocales" 
          :key="code"
          class="language-option"
          :class="{ 'active': currentLocale === code }"
          @click="selectLanguage(code)"
        >
          <span class="flag">{{ locale.flag }}</span>
          <span class="language-name">{{ locale.nativeName }}</span>
          <span class="language-name-english">{{ locale.name }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useI18n } from '../../services/i18nService';

// Get i18n service
const i18n = useI18n();

// Component state
const isOpen = ref(false);
const dropdown = ref(null);
const searchInput = ref(null);
const searchQuery = ref('');
const filteredLocales = ref({...i18n.getAvailableLocales()});

// Computed properties
const currentLocale = computed(() => i18n.currentLocale.value);
const availableLocales = computed(() => i18n.getAvailableLocales());
const localeMetadata = computed(() => i18n.localeMetadata.value);

// Toggle dropdown
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  
  // Focus search input when opening dropdown
  if (isOpen.value && Object.keys(availableLocales.value).length > 5) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target) && 
      !event.target.closest('.language-button')) {
    isOpen.value = false;
  }
};

// Filter languages based on search query
const filterLanguages = () => {
  if (!searchQuery.value) {
    filteredLocales.value = {...availableLocales.value};
    return;
  }
  
  const query = searchQuery.value.toLowerCase();
  const filtered = {};
  
  Object.entries(availableLocales.value).forEach(([code, locale]) => {
    if (
      locale.name.toLowerCase().includes(query) || 
      locale.nativeName.toLowerCase().includes(query) ||
      code.toLowerCase().includes(query)
    ) {
      filtered[code] = locale;
    }
  });
  
  filteredLocales.value = filtered;
};

// Select a language
const selectLanguage = (code) => {
  i18n.setLocale(code);
  isOpen.value = false;
  searchQuery.value = '';
  filteredLocales.value = {...availableLocales.value};
};

// Setup event listeners
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  
  // Handle escape key to close dropdown
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen.value) {
      isOpen.value = false;
    }
  });
});

// Clean up event listeners
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.language-selector {
  position: relative;
  display: inline-block;
}

.language-button {
  display: flex;
  align-items: center;
  background-color: transparent;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.language-button:hover {
  background-color: #f3f4f6;
}

.language-button:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.flag {
  font-size: 1.25rem;
  margin-right: 0.5rem;
}

.language-name {
  margin-right: 0.5rem;
  font-weight: 500;
}

.language-dropdown {
  position: absolute;
  top: calc(100% + 0.25rem);
  right: 0;
  width: 16rem;
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  z-index: 50;
  overflow: hidden;
  animation: dropdown-fade 0.2s ease-out;
}

.language-search {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.language-search input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.language-search input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.language-list {
  max-height: 16rem;
  overflow-y: auto;
}

.language-option {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.75rem 1rem;
  text-align: left;
  background-color: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.language-option:hover {
  background-color: #f3f4f6;
}

.language-option.active {
  background-color: #eff6ff;
  color: #2563eb;
}

.language-name-english {
  margin-left: auto;
  font-size: 0.75rem;
  color: #6b7280;
}

.rotate-180 {
  transform: rotate(180deg);
  transition: transform 0.2s;
}

@keyframes dropdown-fade {
  from {
    opacity: 0;
    transform: translateY(-0.25rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dark mode support */
:global(.dark) .language-button {
  color: #e5e7eb;
  border-color: #4b5563;
}

:global(.dark) .language-button:hover {
  background-color: #374151;
}

:global(.dark) .language-dropdown {
  background-color: #1f2937;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}

:global(.dark) .language-search {
  border-bottom-color: #4b5563;
}

:global(.dark) .language-search input {
  background-color: #374151;
  border-color: #4b5563;
  color: #e5e7eb;
}

:global(.dark) .language-option:hover {
  background-color: #374151;
}

:global(.dark) .language-option.active {
  background-color: #1e40af;
  color: #e5e7eb;
}

:global(.dark) .language-name-english {
  color: #9ca3af;
}
</style>
