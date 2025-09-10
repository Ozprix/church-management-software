<template>
  <div class="toast-container">
    <TransitionGroup name="toast">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :variant="toast.type"
        :title="toast.title"
        :message="toast.message"
        :position="position"
        :auto-close="autoClose"
        :duration="duration"
        :dismissible="dismissible"
        @close="removeToast(toast.id)"
        class="mb-2"
      />
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useToastService } from '../../services/toastService';
import Toast from './Toast.vue';

const props = defineProps({
  position: {
    type: String,
    default: 'bottom-right',
    validator: (value) => [
      'top-left', 'top-center', 'top-right',
      'bottom-left', 'bottom-center', 'bottom-right'
    ].includes(value)
  },
  autoClose: {
    type: Boolean,
    default: true
  },
  duration: {
    type: Number,
    default: 5000 // 5 seconds
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  maxToasts: {
    type: Number,
    default: 5
  }
});

// Get toast service
const { toasts, remove: removeToast } = useToastService();

// Computed properties
const visibleToasts = computed(() => {
  // Only show the most recent toasts up to maxToasts
  return toasts.value
    .filter(toast => toast.visible)
    .sort((a, b) => b.timestamp - a.timestamp)
    .slice(0, props.maxToasts);
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(30px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(-30px);
}
</style>
