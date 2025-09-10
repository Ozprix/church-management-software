<template>
  <DashboardWidget 
    :title="title" 
    :loading="loading" 
    :refreshable="refreshable"
    :configurable="configurable"
    @refresh="$emit('refresh')"
    @configure="$emit('configure')"
    type="chart"
  >
    <template #icon>
      <slot name="icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
        </svg>
      </slot>
    </template>
    
    <div class="chart-content">
      <div v-if="!chartData || chartData.length === 0" class="flex flex-col items-center justify-center py-8 text-neutral-500 dark:text-neutral-400 transition-colors duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p>No chart data available</p>
      </div>
      <div v-else ref="chartContainer" class="chart-container" :style="{ height: chartHeight }"></div>
      
      <div v-if="showLegend && chartData && chartData.length > 0" class="chart-legend mt-4 flex flex-wrap justify-center gap-4">
        <div 
          v-for="(item, index) in chartData" 
          :key="index" 
          class="flex items-center text-sm"
        >
          <span 
            class="inline-block w-3 h-3 rounded-full mr-2" 
            :style="{ backgroundColor: getColor(index) }"
          ></span>
          <span class="text-neutral-700 dark:text-neutral-300 transition-colors duration-300">{{ item.name }}</span>
        </div>
      </div>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import DashboardWidget from './DashboardWidget.vue';

// We'll use a dynamic import for Chart.js to keep the initial bundle size smaller
let Chart = null;

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  chartData: {
    type: Array,
    default: () => []
  },
  chartType: {
    type: String,
    default: 'line', // line, bar, pie, doughnut
    validator: (value) => ['line', 'bar', 'pie', 'doughnut'].includes(value)
  },
  chartOptions: {
    type: Object,
    default: () => ({})
  },
  chartHeight: {
    type: String,
    default: '300px'
  },
  loading: {
    type: Boolean,
    default: false
  },
  refreshable: {
    type: Boolean,
    default: true
  },
  configurable: {
    type: Boolean,
    default: false
  },
  showLegend: {
    type: Boolean,
    default: true
  },
  colorPalette: {
    type: Array,
    default: () => [
      '#4F46E5', // primary
      '#10B981', // green
      '#F59E0B', // yellow
      '#EF4444', // red
      '#8B5CF6', // purple
      '#EC4899', // pink
      '#06B6D4', // cyan
      '#F97316', // orange
    ]
  }
});

defineEmits(['refresh', 'configure']);

const chartContainer = ref(null);
const chartInstance = ref(null);

// Function to get color from palette
const getColor = (index) => {
  return props.colorPalette[index % props.colorPalette.length];
};

// Initialize chart
const initChart = async () => {
  if (!chartContainer.value || !props.chartData || props.chartData.length === 0) return;
  
  // Dynamically import Chart.js
  if (!Chart) {
    const module = await import('chart.js/auto');
    Chart = module.default;
    
    // Register the 'beforeRender' plugin for animation
    const beforeRenderPlugin = {
      id: 'beforeRender',
      beforeRender: (chart) => {
        if (chart.config.type !== 'line') return;
        
        const ctx = chart.ctx;
        ctx.save();
        ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
        ctx.shadowBlur = 4;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 4;
        ctx.restore();
      }
    };
    
    Chart.register(beforeRenderPlugin);
  }
  
  // Destroy previous chart if it exists
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }
  
  // Prepare data based on chart type
  let chartConfig = {
    type: props.chartType,
    data: {
      labels: [],
      datasets: []
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 1000,
        easing: 'easeOutQuart'
      },
      plugins: {
        legend: {
          display: false // We'll create our own legend
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.7)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 4,
          displayColors: true
        }
      },
      ...props.chartOptions
    }
  };
  
  if (['line', 'bar'].includes(props.chartType)) {
    // For line and bar charts
    chartConfig.data.labels = props.chartData[0]?.data.map(item => item.x) || [];
    chartConfig.data.datasets = props.chartData.map((dataset, index) => ({
      label: dataset.name,
      data: dataset.data.map(item => item.y),
      backgroundColor: props.chartType === 'line' 
        ? getColor(index) + '20' // Add transparency for line chart background
        : getColor(index),
      borderColor: getColor(index),
      borderWidth: 2,
      tension: 0.4,
      pointBackgroundColor: getColor(index),
      pointBorderColor: '#fff',
      pointBorderWidth: 1,
      pointRadius: 4,
      pointHoverRadius: 6,
      fill: props.chartType === 'line' ? 'origin' : false
    }));
  } else {
    // For pie and doughnut charts
    chartConfig.data.labels = props.chartData.map(item => item.name);
    chartConfig.data.datasets = [{
      data: props.chartData.map(item => item.value),
      backgroundColor: props.chartData.map((_, index) => getColor(index)),
      borderColor: '#fff',
      borderWidth: 2
    }];
  }
  
  // Create chart
  const ctx = chartContainer.value.getContext('2d');
  chartInstance.value = new Chart(ctx, chartConfig);
};

// Watch for changes in chart data or type
watch(() => [props.chartData, props.chartType, props.chartOptions], () => {
  initChart();
}, { deep: true });

// Watch for dark mode changes to update chart
watch(() => document.documentElement.classList.contains('dark'), (isDark) => {
  if (!chartInstance.value) return;
  
  const textColor = isDark ? '#D1D5DB' : '#4B5563';
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
  
  chartInstance.value.options.scales = {
    ...chartInstance.value.options.scales,
    x: {
      ...chartInstance.value.options.scales?.x,
      grid: {
        color: gridColor
      },
      ticks: {
        color: textColor
      }
    },
    y: {
      ...chartInstance.value.options.scales?.y,
      grid: {
        color: gridColor
      },
      ticks: {
        color: textColor
      }
    }
  };
  
  chartInstance.value.update();
}, { immediate: true });

// Initialize on mount
onMounted(() => {
  initChart();
});

// Clean up on unmount
onBeforeUnmount(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }
});
</script>

<style scoped>
.chart-container {
  width: 100%;
  position: relative;
}
</style>
