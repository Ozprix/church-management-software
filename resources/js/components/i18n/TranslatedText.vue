<template>
  <span>{{ translatedText }}</span>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from '../../services/i18nService';

const props = defineProps({
  // Translation key
  keyPath: {
    type: String,
    required: true
  },
  // Parameters for interpolation
  params: {
    type: Object,
    default: () => ({})
  },
  // Tag to render instead of span
  tag: {
    type: String,
    default: 'span'
  }
});

// Get i18n service
const i18n = useI18n();

// Compute translated text
const translatedText = computed(() => {
  return i18n.t(props.keyPath, props.params);
});
</script>
