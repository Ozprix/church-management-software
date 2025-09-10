<template>
  <div class="theme-customizer">
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">Theme Customization</h1>
        <Button variant="primary" @click="applyChanges" :disabled="!hasChanges">
          Apply Changes
        </Button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Theme Presets -->
        <Card variant="default" elevation="md" radius="lg" class="theme-card">
          <template #header>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Theme Presets</h2>
          </template>
          
          <div class="theme-presets grid grid-cols-2 gap-4 mb-4">
            <div 
              v-for="(preset, key) in themePresets" 
              :key="key"
              class="theme-preset-item cursor-pointer rounded-lg p-2 border-2 transition-all"
              :class="{ 'border-primary-500': selectedPreset === key, 'border-transparent': selectedPreset !== key }"
              @click="selectPreset(key)"
            >
              <div class="theme-preview h-20 rounded-md mb-2" :class="{ 'bg-neutral-900': preset.isDark, 'bg-white': !preset.isDark }">
                <div class="flex p-2">
                  <div 
                    class="w-8 h-8 rounded-full mr-2" 
                    :style="{ backgroundColor: preset.colors.primary[500] }"
                  ></div>
                  <div class="flex-1">
                    <div class="h-2 w-3/4 rounded-full mb-1" :class="preset.isDark ? 'bg-neutral-700' : 'bg-neutral-200'"></div>
                    <div class="h-2 w-1/2 rounded-full" :class="preset.isDark ? 'bg-neutral-700' : 'bg-neutral-200'"></div>
                  </div>
                </div>
              </div>
              <div class="text-center text-sm font-medium">{{ preset.name }}</div>
            </div>
          </div>

          <div class="mb-4">
            <FormItem label="Seasonal Themes" name="seasonal">
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  id="seasonal-themes" 
                  v-model="enableSeasonalThemes"
                  class="form-checkbox h-5 w-5 text-primary-600 rounded"
                />
                <label for="seasonal-themes" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                  Enable seasonal themes (Christmas, Easter, etc.)
                </label>
              </div>
            </FormItem>
          </div>
        </Card>

        <!-- Church Branding -->
        <Card variant="default" elevation="md" radius="lg" class="theme-card">
          <template #header>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Church Branding</h2>
          </template>
          
          <FormItem label="Church Name" name="churchName">
            <Input 
              v-model="churchName" 
              placeholder="Enter your church name" 
              class="w-full"
            />
          </FormItem>

          <FormItem label="Primary Color" name="primaryColor">
            <div class="flex items-center">
              <input 
                type="color" 
                v-model="primaryColor" 
                class="w-12 h-12 rounded-md border-0 cursor-pointer"
              />
              <Input 
                v-model="primaryColor" 
                class="ml-4 flex-1"
                placeholder="#0ea5e9"
              />
            </div>
          </FormItem>

          <FormItem label="Secondary Color" name="secondaryColor">
            <div class="flex items-center">
              <input 
                type="color" 
                v-model="secondaryColor" 
                class="w-12 h-12 rounded-md border-0 cursor-pointer"
              />
              <Input 
                v-model="secondaryColor" 
                class="ml-4 flex-1"
                placeholder="#a855f7"
              />
            </div>
          </FormItem>

          <FormItem label="Church Logo" name="churchLogo">
            <div class="flex items-center">
              <div 
                class="w-16 h-16 rounded-md border-2 border-dashed border-neutral-300 dark:border-neutral-700 flex items-center justify-center mr-4"
                :class="{ 'border-0': previewLogo }"
              >
                <img v-if="previewLogo" :src="previewLogo" alt="Logo Preview" class="max-w-full max-h-full rounded-md" />
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="flex-1">
                <Button variant="outline" size="sm" @click="triggerFileInput">
                  Upload Logo
                </Button>
                <input 
                  type="file" 
                  ref="fileInput" 
                  class="hidden" 
                  accept="image/*"
                  @change="handleFileUpload"
                />
              </div>
            </div>
          </FormItem>
        </Card>

        <!-- Accessibility -->
        <Card variant="default" elevation="md" radius="lg" class="theme-card">
          <template #header>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Accessibility</h2>
          </template>
          
          <FormItem label="Dark Mode" name="darkMode">
            <div class="flex items-center justify-between">
              <span class="text-sm text-neutral-700 dark:text-neutral-300">Light</span>
              <div class="relative mx-4 flex-1">
                <input 
                  type="range" 
                  min="0" 
                  max="2" 
                  :value="darkModeValue" 
                  @input="updateDarkMode($event.target.value)"
                  class="w-full h-2 bg-neutral-200 rounded-lg appearance-none cursor-pointer dark:bg-neutral-700"
                />
              </div>
              <span class="text-sm text-neutral-700 dark:text-neutral-300">Dark</span>
            </div>
            <div class="text-center text-xs text-neutral-500 mt-1">
              {{ darkModeLabels[darkModeValue] }}
            </div>
          </FormItem>

          <FormItem label="High Contrast" name="highContrast">
            <div class="flex items-center">
              <input 
                type="checkbox" 
                id="high-contrast" 
                v-model="highContrast"
                class="form-checkbox h-5 w-5 text-primary-600 rounded"
              />
              <label for="high-contrast" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                Enable high contrast mode
              </label>
            </div>
          </FormItem>

          <FormItem label="Font Size" name="fontSize">
            <div class="grid grid-cols-3 gap-2">
              <Button 
                variant="outline" 
                size="sm" 
                :class="{ 'bg-primary-100 dark:bg-primary-900': fontSize === 'small' }"
                @click="fontSize = 'small'"
              >
                Small
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                :class="{ 'bg-primary-100 dark:bg-primary-900': fontSize === 'medium' }"
                @click="fontSize = 'medium'"
              >
                Medium
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                :class="{ 'bg-primary-100 dark:bg-primary-900': fontSize === 'large' }"
                @click="fontSize = 'large'"
              >
                Large
              </Button>
            </div>
          </FormItem>

          <FormItem label="Reduced Motion" name="reducedMotion">
            <div class="flex items-center">
              <input 
                type="checkbox" 
                id="reduced-motion" 
                v-model="reducedMotion"
                class="form-checkbox h-5 w-5 text-primary-600 rounded"
              />
              <label for="reduced-motion" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                Reduce animations and transitions
              </label>
            </div>
          </FormItem>

          <div class="mt-4">
            <Button variant="danger" size="sm" @click="resetSettings">
              Reset All Settings
            </Button>
          </div>
        </Card>
      </div>

      <!-- Theme Preview -->
      <Card variant="default" elevation="md" radius="lg" class="mt-8">
        <template #header>
          <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-0">Theme Preview</h2>
        </template>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="preview-section">
            <h3 class="text-lg font-medium mb-4">Components</h3>
            
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">Buttons</label>
              <div class="flex flex-wrap gap-2">
                <Button variant="primary">Primary</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="outline">Outline</Button>
                <Button variant="ghost">Ghost</Button>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">Cards</label>
              <div class="grid grid-cols-2 gap-2">
                <Card variant="default" class="p-3">
                  <p class="text-sm">Default Card</p>
                </Card>
                <Card variant="glass" class="p-3">
                  <p class="text-sm">Glass Card</p>
                </Card>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">Form Elements</label>
              <FormItem label="Input Field" class="mb-2">
                <Input placeholder="Enter text" />
              </FormItem>
              <div class="flex items-center mb-2">
                <input type="checkbox" class="form-checkbox h-5 w-5 text-primary-600 rounded" />
                <span class="ml-2 text-sm">Checkbox</span>
              </div>
              <div class="flex items-center">
                <input type="radio" class="form-radio h-5 w-5 text-primary-600" />
                <span class="ml-2 text-sm">Radio Button</span>
              </div>
            </div>
          </div>
          
          <div class="preview-section">
            <h3 class="text-lg font-medium mb-4">Typography & Colors</h3>
            
            <div class="mb-4">
              <h1 class="text-2xl font-bold mb-1">Heading 1</h1>
              <h2 class="text-xl font-semibold mb-1">Heading 2</h2>
              <h3 class="text-lg font-medium mb-1">Heading 3</h3>
              <p class="mb-1">Regular paragraph text</p>
              <p class="text-sm mb-1">Small text</p>
              <a href="#" class="text-primary-600 dark:text-primary-400 hover:underline">Link text</a>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">Color Palette</label>
              <div class="grid grid-cols-5 gap-1">
                <div v-for="shade in [50, 100, 200, 300, 400, 500, 600, 700, 800, 900]" :key="shade" 
                  class="h-6 rounded"
                  :class="`bg-primary-${shade}`"
                ></div>
              </div>
              <div class="grid grid-cols-5 gap-1 mt-1">
                <div v-for="shade in [50, 100, 200, 300, 400, 500, 600, 700, 800, 900]" :key="shade" 
                  class="h-6 rounded"
                  :class="`bg-secondary-${shade}`"
                ></div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Status Colors</label>
              <div class="grid grid-cols-4 gap-2">
                <div class="p-2 bg-success/20 text-success rounded text-center text-sm">Success</div>
                <div class="p-2 bg-error/20 text-error rounded text-center text-sm">Error</div>
                <div class="p-2 bg-warning/20 text-warning rounded text-center text-sm">Warning</div>
                <div class="p-2 bg-info/20 text-info rounded text-center text-sm">Info</div>
              </div>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSettingsStore } from '../stores/settings';
import { useToast } from 'vue-toastification';
import { themePresets, themeManager } from '../utils/themeManager';
import Button from '../components/ui/Button.vue';
import Card from '../components/ui/Card.vue';
import FormItem from '../components/ui/FormItem.vue';
import Input from '../components/ui/Input.vue';

const settingsStore = useSettingsStore();
const toast = useToast();

// Theme preset selection
const selectedPreset = ref(settingsStore.themePreset);
const enableSeasonalThemes = ref(settingsStore.enableSeasonalThemes);

// Church branding
const churchName = ref(settingsStore.churchName);
const primaryColor = ref('#0ea5e9'); // Default primary color
const secondaryColor = ref('#a855f7'); // Default secondary color
const previewLogo = ref(settingsStore.churchLogo);
const fileInput = ref(null);

// Accessibility settings
const darkModeValue = ref(
  settingsStore.darkMode === null ? 1 : // System
  settingsStore.darkMode === 'light' ? 0 : // Light
  2 // Dark
);
const darkModeLabels = ['Light Mode', 'System Default', 'Dark Mode'];
const highContrast = ref(settingsStore.highContrast);
const fontSize = ref(settingsStore.fontSize);
const reducedMotion = ref(settingsStore.animationsReduced);

// Computed to check if any changes have been made
const hasChanges = computed(() => {
  return (
    selectedPreset.value !== settingsStore.themePreset ||
    enableSeasonalThemes.value !== settingsStore.enableSeasonalThemes ||
    churchName.value !== settingsStore.churchName ||
    previewLogo.value !== settingsStore.churchLogo ||
    darkModeValue.value !== (
      settingsStore.darkMode === null ? 1 :
      settingsStore.darkMode === 'light' ? 0 :
      2
    ) ||
    highContrast.value !== settingsStore.highContrast ||
    fontSize.value !== settingsStore.fontSize ||
    reducedMotion.value !== settingsStore.animationsReduced
  );
});

// Methods
function selectPreset(key) {
  selectedPreset.value = key;
}

function updateDarkMode(value) {
  darkModeValue.value = parseInt(value);
}

function triggerFileInput() {
  fileInput.value.click();
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  
  // Check file type
  if (!file.type.match('image.*')) {
    toast.error('Please select an image file');
    return;
  }
  
  // Check file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image size should be less than 2MB');
    return;
  }
  
  const reader = new FileReader();
  reader.onload = (e) => {
    previewLogo.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function applyChanges() {
  // Update settings store
  settingsStore.setThemePreset(selectedPreset.value);
  settingsStore.toggleSeasonalThemes(enableSeasonalThemes.value);
  settingsStore.setChurchBranding(churchName.value, previewLogo.value);
  
  // Handle dark mode
  const darkModeSettings = [
    'light', // 0 = Light
    null,    // 1 = System
    'dark'   // 2 = Dark
  ];
  settingsStore.setDarkMode(darkModeSettings[darkModeValue.value]);
  
  // Accessibility settings
  settingsStore.toggleHighContrast(highContrast.value);
  settingsStore.setFontSize(fontSize.value);
  settingsStore.toggleReducedAnimations(reducedMotion.value);
  
  // Custom colors (from color pickers)
  if (primaryColor.value !== '#0ea5e9' || secondaryColor.value !== '#a855f7') {
    // Generate a color palette from the selected colors
    const customColors = {
      primary: generateColorPalette(primaryColor.value),
      secondary: generateColorPalette(secondaryColor.value)
    };
    settingsStore.setCustomColors(customColors);
  }
  
  // Apply theme changes
  themeManager.applyTheme();
  
  toast.success('Theme settings updated successfully');
}

function resetSettings() {
  // Reset to defaults
  selectedPreset.value = 'system';
  enableSeasonalThemes.value = true;
  churchName.value = 'Church Management System';
  previewLogo.value = null;
  primaryColor.value = '#0ea5e9';
  secondaryColor.value = '#a855f7';
  darkModeValue.value = 1; // System
  highContrast.value = false;
  fontSize.value = 'medium';
  reducedMotion.value = false;
  
  // Reset in store
  settingsStore.resetThemeSettings();
  
  toast.info('All theme settings have been reset to defaults');
}

// Helper function to generate a color palette from a hex color
function generateColorPalette(baseColor) {
  // This is a simplified version - in a real app you might use a library like chroma.js
  // to generate proper color scales with correct lightness and saturation
  
  // Convert hex to RGB
  const r = parseInt(baseColor.slice(1, 3), 16);
  const g = parseInt(baseColor.slice(3, 5), 16);
  const b = parseInt(baseColor.slice(5, 7), 16);
  
  // Generate palette (simplified approach)
  return {
    50: lightenDarkenColor(baseColor, 80),
    100: lightenDarkenColor(baseColor, 60),
    200: lightenDarkenColor(baseColor, 40),
    300: lightenDarkenColor(baseColor, 20),
    400: lightenDarkenColor(baseColor, 10),
    500: baseColor,
    600: lightenDarkenColor(baseColor, -10),
    700: lightenDarkenColor(baseColor, -20),
    800: lightenDarkenColor(baseColor, -30),
    900: lightenDarkenColor(baseColor, -40),
  };
}

// Helper function to lighten or darken a color
function lightenDarkenColor(hex, amount) {
  let r = parseInt(hex.slice(1, 3), 16);
  let g = parseInt(hex.slice(3, 5), 16);
  let b = parseInt(hex.slice(5, 7), 16);

  r = Math.max(0, Math.min(255, r + amount));
  g = Math.max(0, Math.min(255, g + amount));
  b = Math.max(0, Math.min(255, b + amount));

  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

// Initialize
onMounted(() => {
  // Set initial values based on current theme
  const currentTheme = themeManager.getCurrentTheme();
  if (currentTheme.type === 'custom' && currentTheme.colors) {
    // Extract primary and secondary colors from custom theme
    primaryColor.value = currentTheme.colors.primary[500] || '#0ea5e9';
    secondaryColor.value = currentTheme.colors.secondary?.[500] || '#a855f7';
  }
});
</script>

<style scoped>
.theme-card {
  height: 100%;
}

.theme-preset-item:hover {
  @apply bg-neutral-100 dark:bg-neutral-800;
}

/* Preview customizations based on font size */
:deep(.text-size-small) {
  font-size: 0.875rem;
}

:deep(.text-size-large) {
  font-size: 1.125rem;
}
</style>
