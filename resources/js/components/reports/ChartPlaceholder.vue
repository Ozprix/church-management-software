<template>
  <div class="chart-placeholder h-full flex items-center justify-center">
    <canvas ref="chartCanvas" class="w-full h-full"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['pie', 'bar', 'line', 'doughnut'].includes(value)
  },
  data: {
    type: Object,
    required: true
  }
});

const chartCanvas = ref(null);
let chart = null;

function createChart() {
  if (!chartCanvas.value) return;
  
  // Destroy existing chart if it exists
  if (chart) {
    chart.destroy();
  }
  
  // Get the 2d context of the canvas
  const ctx = chartCanvas.value.getContext('2d');
  
  // Create the chart based on the type
  chart = new Chart(ctx, {
    type: props.type,
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: document.documentElement.classList.contains('dark') ? '#e5e5e5' : '#333333'
          }
        }
      },
      scales: props.type !== 'pie' && props.type !== 'doughnut' ? {
        x: {
          grid: {
            color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#e5e5e5' : '#333333'
          }
        },
        y: {
          grid: {
            color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#e5e5e5' : '#333333'
          }
        }
      } : undefined
    }
  });
}

// Watch for dark mode changes
const darkModeObserver = new MutationObserver((mutations) => {
  mutations.forEach((mutation) => {
    if (mutation.attributeName === 'class' && chart) {
      createChart();
    }
  });
});

// Watch for changes in the data prop
watch(() => props.data, () => {
  createChart();
}, { deep: true });

// Watch for changes in the type prop
watch(() => props.type, () => {
  createChart();
});

onMounted(() => {
  createChart();
  
  // Observe dark mode changes
  darkModeObserver.observe(document.documentElement, { attributes: true });
});

onUnmounted(() => {
  if (chart) {
    chart.destroy();
  }
  
  darkModeObserver.disconnect();
});
</script>
