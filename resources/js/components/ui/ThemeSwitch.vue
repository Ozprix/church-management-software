<template>
  <div class="theme-switch">
    <div class="theme-switch-controls">
      <!-- Dark mode toggle -->
      <button 
        @click="toggleDarkMode" 
        class="theme-toggle-btn"
        :title="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
        </svg>
      </button>

      <!-- Theme preset selector -->
      <button 
        @click="showThemeSelector = !showThemeSelector" 
        class="theme-toggle-btn"
        title="Change theme"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Settings button -->
      <router-link 
        to="/theme-customizer" 
        class="theme-toggle-btn"
        title="Advanced theme settings"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
        </svg>
      </router-link>
    </div>

    <!-- Theme selector dropdown -->
    <div v-if="showThemeSelector" class="theme-selector">
      <div class="theme-selector-header">
        <h3 class="theme-selector-title">Select Theme</h3>
        <button @click="showThemeSelector = false" class="theme-selector-close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
      <div class="theme-selector-options">
        <button 
          v-for="(theme, key) in availableThemes" 
          :key="key"
          @click="selectTheme(key)"
          class="theme-option"
          :class="{ 'active': currentTheme === key }"
        >
          <div class="theme-preview" :style="getThemePreviewStyle(theme)"></div>
          <span class="theme-name">{{ theme.name }}</span>
        </button>
      </div>
      <div class="theme-selector-footer">
        <label class="seasonal-theme-toggle">
          <input 
            type="checkbox" 
            v-model="enableSeasonalThemes"
            @change="toggleSeasonalThemes"
          >
          <span class="ml-2">Enable seasonal themes</span>
        </label>
      </div>
    </div>

    <!-- Seasonal theme indicator -->
    <div v-if="activeSeasonalTheme" class="seasonal-theme-indicator">
      {{ seasonalThemes[activeSeasonalTheme].icon }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSettingsStore } from '../../stores/settings';
import { themePresets, seasonalThemes, themeManager } from '../../utils/themeManager';

const settingsStore = useSettingsStore();
const showThemeSelector = ref(false);
const activeSeasonalTheme = ref(null);

// Computed properties
const isDarkMode = computed(() => settingsStore.isDarkMode);
const currentTheme = computed(() => settingsStore.themePreset);
const enableSeasonalThemes = computed({
  get: () => settingsStore.enableSeasonalThemes,
  set: (value) => settingsStore.enableSeasonalThemes = value
});

// Available themes for selection
const availableThemes = computed(() => {
  const themes = { ...themePresets };
  // Remove system theme from the list if we're showing in the dropdown
  // delete themes.system;
  return themes;
});

// Methods
function toggleDarkMode() {
  settingsStore.toggleDarkMode();
  themeManager.applyTheme();
}

function selectTheme(themeKey) {
  settingsStore.setThemePreset(themeKey);
  themeManager.applyTheme();
  showThemeSelector.value = false;
}

function toggleSeasonalThemes() {
  settingsStore.toggleSeasonalThemes();
  themeManager.applyTheme();
  checkForSeasonalTheme();
}

function getThemePreviewStyle(theme) {
  return {
    backgroundColor: theme.isDark ? '#1e293b' : '#f8fafc',
    borderColor: theme.colors.primary[500],
  };
}

function checkForSeasonalTheme() {
  // Check if a seasonal theme is active
  const currentTheme = themeManager.getCurrentTheme();
  if (currentTheme.type === 'seasonal') {
    activeSeasonalTheme.value = currentTheme.key;
  } else {
    activeSeasonalTheme.value = null;
  }
}

// Initialize
onMounted(() => {
  themeManager.applyTheme();
  checkForSeasonalTheme();
});
</script>

<style scoped>
.theme-switch {
  position: relative;
}

.theme-switch-controls {
  display: flex;
  gap: 0.5rem;
}

.theme-toggle-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.375rem;
  color: var(--color-primary-700);
  background-color: var(--color-primary-50);
  transition: all 0.2s;
}

.dark .theme-toggle-btn {
  color: var(--color-primary-300);
  background-color: var(--color-primary-800);
}

.theme-toggle-btn:hover {
  background-color: var(--color-primary-100);
}

.dark .theme-toggle-btn:hover {
  background-color: var(--color-primary-700);
}

.theme-selector {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  width: 16rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  z-index: 50;
  overflow: hidden;
}

.dark .theme-selector {
  background-color: #1e293b;
  border: 1px solid #334155;
}

.theme-selector-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.dark .theme-selector-header {
  border-bottom-color: #334155;
}

.theme-selector-title {
  font-weight: 600;
  font-size: 0.875rem;
}

.theme-selector-close {
  color: #64748b;
  transition: color 0.2s;
}

.theme-selector-close:hover {
  color: #475569;
}

.dark .theme-selector-close:hover {
  color: #94a3b8;
}

.theme-selector-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  padding: 0.75rem;
  max-height: 16rem;
  overflow-y: auto;
}

.theme-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
  cursor: pointer;
}

.theme-option:hover {
  background-color: #f1f5f9;
}

.dark .theme-option:hover {
  background-color: #334155;
}

.theme-option.active {
  background-color: #e0f2fe;
}

.dark .theme-option.active {
  background-color: #0c4a6e;
}

.theme-preview {
  width: 100%;
  height: 2.5rem;
  border-radius: 0.25rem;
  border: 2px solid;
  margin-bottom: 0.5rem;
}

.theme-name {
  font-size: 0.75rem;
  font-weight: 500;
}

.theme-selector-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid #e2e8f0;
}

.dark .theme-selector-footer {
  border-top-color: #334155;
}

.seasonal-theme-toggle {
  display: flex;
  align-items: center;
  font-size: 0.75rem;
}

.seasonal-theme-indicator {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  z-index: 50;
  opacity: 0.7;
  transition: opacity 0.3s;
}

.seasonal-theme-indicator:hover {
  opacity: 1;
}
</style>
