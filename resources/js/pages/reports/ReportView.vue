<template>
  <div class="container mx-auto px-4 py-8">
    <ReportNavigation />
    <!-- Header with navigation -->
    <div class="flex items-center mb-6">
      <router-link to="/reports" class="text-blue-600 hover:text-blue-800 mr-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </router-link>
      <h1 class="text-2xl font-bold text-gray-800">{{ report.name || 'Report Details' }}</h1>
      <div class="ml-auto flex space-x-2">
        <button
          @click="handleToggleFavorite"
          class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm"
          :class="{ 'bg-yellow-50 text-yellow-700 border-yellow-300': report.is_favorite }"
        >
          <svg
            :class="{ 'text-yellow-500': report.is_favorite, 'text-gray-400': !report.is_favorite }"
            class="h-4 w-4 mr-1"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
            ></path>
          </svg>
          {{ report.is_favorite ? 'Favorited' : 'Add to Favorites' }}
        </button>
        <router-link
          :to="`/reports/${reportId}/edit`"
          class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
        >
          <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit
        </router-link>
        <button
          @click="handleDeleteReport"
          class="flex items-center px-3 py-1 border border-red-300 rounded-md text-sm text-red-700 bg-red-50 hover:bg-red-100"
        >
          <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
          Delete
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading report details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p>{{ error }}</p>
    </div>

    <!-- Report Details and Generation -->
    <div v-else-if="report.id" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Report Details -->
      <div class="lg:col-span-1">
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Report Details</h2>
          </div>
          <div class="p-4">
            <div class="mb-4">
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Type:</span>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getTypeClass(report.type)"
                >
                  {{ getTypeDisplay(report.type) }}
                </span>
              </div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Created:</span>
                <span class="text-gray-900">{{ formatDate(report.created_at) }}</span>
              </div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Last Generated:</span>
                <span class="text-gray-900">{{ report.last_generated_at ? formatDate(report.last_generated_at) : 'Never' }}</span>
              </div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Format:</span>
                <span class="text-gray-900">{{ report.output_format.toUpperCase() }}</span>
              </div>
              <div class="flex justify-between text-sm mb-2" v-if="report.is_scheduled">
                <span class="text-gray-500">Schedule:</span>
                <span class="text-gray-900">{{ getScheduleDisplay(report.schedule_frequency) }}</span>
              </div>
            </div>
            <div v-if="report.description" class="mb-4">
              <h3 class="text-sm font-medium text-gray-700 mb-1">Description</h3>
              <p class="text-sm text-gray-600">{{ report.description }}</p>
            </div>
          </div>
        </div>

        <!-- Report Parameters -->
        <div class="bg-white shadow rounded-lg overflow-hidden mt-4">
          <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Parameters</h2>
          </div>
          <div class="p-4">
            <div v-if="reportParams">
              <div class="grid grid-cols-2 gap-2 text-sm">
                <template v-for="(value, key) in reportParams" :key="key">
                  <div class="text-gray-500">{{ formatParamKey(key) }}:</div>
                  <div class="text-gray-900">{{ formatParamValue(key, value) }}</div>
                </template>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">No parameters set</div>
          </div>
        </div>

        <!-- Generate Report -->
        <!-- Templates Section -->
        <div class="bg-white shadow rounded-lg overflow-hidden mt-4">
          <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Report Templates</h2>
          </div>
          <div class="p-4">
            <button 
              @click="showTemplates = !showTemplates" 
              class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
            >
              <svg v-if="!showTemplates" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
              <svg v-else class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
              </svg>
              {{ showTemplates ? 'Hide Templates' : 'Show Templates' }}
            </button>
          </div>
          
          <div v-if="showTemplates" class="border-t border-gray-200">
            <ReportTemplateManager 
              :reportId="reportId" 
              @use-template="applyTemplate"
              @template-saved="templateSaved"
            />
          </div>
        </div>
        
        <!-- Generate Report Section -->
        <div class="bg-white shadow rounded-lg overflow-hidden mt-4">
          <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Generate Report</h2>
          </div>
          <div class="p-4">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Output Format</label>
              <select
                v-model="generateOptions.format"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="pdf">PDF Document</option>
                <option value="excel">Excel Spreadsheet</option>
                <option value="json">JSON Data</option>
                <option value="html">HTML Report</option>
                <option value="csv">CSV Data</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Chart Type</label>
              <select
                v-model="generateOptions.chartType"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="bar">Bar Chart</option>
                <option value="line">Line Chart</option>
                <option value="pie">Pie Chart</option>
                <option value="doughnut">Doughnut Chart</option>
                <option value="polarArea">Polar Area Chart</option>
                <option value="radar">Radar Chart</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                  <input 
                    type="date" 
                    v-model="generateOptions.startDate"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                </div>
                <div>
                  <label class="block text-xs text-gray-500 mb-1">End Date</label>
                  <input 
                    type="date" 
                    v-model="generateOptions.endDate"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                </div>
              </div>
            </div>
            
            <div class="mb-4">
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  id="schedule-report" 
                  v-model="showScheduleOptions"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="schedule-report" class="ml-2 block text-sm text-gray-700">Schedule this report</label>
              </div>
            </div>
            
            <div v-if="showScheduleOptions" class="mb-4 p-3 bg-gray-50 rounded-md">
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                <select
                  v-model="scheduleOptions.frequency"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Recipients</label>
                <input 
                  type="text" 
                  v-model="scheduleOptions.recipients"
                  placeholder="Enter email addresses separated by commas"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
              </div>
              
              <button
                @click="scheduleReport"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                :disabled="scheduling"
              >
                <span v-if="scheduling" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Scheduling...
                </span>
                <span v-else>Schedule Report</span>
              </button>
            </div>
            
            <button
              @click="generateReport"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="generating"
            >
              <span v-if="generating" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
              </span>
              <span v-else>Generate Report</span>
            </button>
            
            <div v-if="exportStatus" class="mt-3 text-center text-sm">
              <div v-if="exportStatus === 'processing'" class="text-blue-600">Processing your report...</div>
              <div v-else-if="exportStatus === 'completed'" class="text-green-600">Report generated successfully!</div>
              <div v-else-if="exportStatus === 'failed'" class="text-red-600">Failed to generate report. Please try again.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Preview -->
      <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg overflow-hidden h-full">
          <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Report Preview</h2>
            <div v-if="reportData" class="flex space-x-2">
              <button
                @click="downloadReport('pdf')"
                class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
              >
                <svg class="h-4 w-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                </svg>
                PDF
              </button>
              <button
                @click="downloadReport('csv')"
                class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
              >
                <svg class="h-4 w-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                </svg>
                CSV
              </button>
              <button
                @click="downloadReport('excel')"
                class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
              >
                <svg class="h-4 w-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                </svg>
                Excel
              </button>
            </div>
          </div>
          
          <!-- Report Content -->
          <div class="p-4 overflow-auto" style="max-height: 600px;">
            <!-- Loading Report -->
            <div v-if="generatingReport" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
              <p class="mt-2 text-gray-600">Generating report...</p>
            </div>
            
            <!-- No Report Generated Yet -->
            <div v-else-if="!reportData" class="text-center py-8">
              <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No report generated yet</h3>
              <p class="mt-1 text-sm text-gray-500">Click the Generate Report button to create a report.</p>
            </div>
            
            <!-- Report Data -->
            <div v-else>
              <!-- Financial/Donation/Expense Report -->
              <div v-if="['financial', 'donation', 'expense'].includes(report.type)">
                <div v-if="reportData.summary" class="mb-6">
                  <h3 class="text-lg font-semibold text-gray-800 mb-3">Summary</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                      <p class="text-sm text-blue-700">Total Amount</p>
                      <p class="text-2xl font-bold text-blue-800">{{ formatCurrency(reportData.summary.total) }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                      <p class="text-sm text-green-700">Average</p>
                      <p class="text-2xl font-bold text-green-800">{{ formatCurrency(reportData.summary.average) }}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                      <p class="text-sm text-purple-700">Count</p>
                      <p class="text-2xl font-bold text-purple-800">{{ reportData.summary.count }}</p>
                    </div>
                  </div>
                </div>
                
                <!-- Chart if available -->
                <div v-if="reportData.chart" class="mb-6">
                  <canvas ref="chartCanvas" width="400" height="200"></canvas>
                </div>
                
                <!-- Data Table -->
                <div v-if="reportData.data && reportData.data.length > 0">
                  <h3 class="text-lg font-semibold text-gray-800 mb-3">Details</h3>
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th v-for="(header, index) in Object.keys(reportData.data[0])" :key="index" 
                              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ formatHeader(header) }}
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(row, rowIndex) in reportData.data" :key="rowIndex">
                          <td v-for="(value, key) in row" :key="key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatTableValue(key, value) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
              <!-- Attendance Report -->
              <div v-else-if="report.type === 'attendance'">
                <div v-if="reportData.summary" class="mb-6">
                  <h3 class="text-lg font-semibold text-gray-800 mb-3">Summary</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white shadow rounded-lg overflow-hidden h-full">
                      <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Report Preview</h2>
                        <div v-if="reportData" class="flex space-x-2">
                          <button 
                            @click="toggleView('chart')" 
                            class="px-2 py-1 text-xs rounded-md" 
                            :class="currentView === 'chart' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Chart
                          </button>
                          <button 
                            @click="toggleView('table')" 
                            class="px-2 py-1 text-xs rounded-md" 
                            :class="currentView === 'table' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Table
                          </button>
                          <button 
                            @click="toggleView('summary')" 
                            class="px-2 py-1 text-xs rounded-md" 
                            :class="currentView === 'summary' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Summary
                          </button>
                        </div>
                      </div>
                      <div class="p-4">
                        <div v-if="generating" class="text-center py-8">
                          <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
                          <p class="mt-2 text-gray-600">Generating report...</p>
                        </div>
                        <div v-else-if="!reportData" class="text-center py-8">
                          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                          <h3 class="mt-2 text-sm font-medium text-gray-900">No report generated yet</h3>
                          <p class="mt-1 text-sm text-gray-500">Click the "Generate Report" button to see the report preview.</p>
                        </div>
                        <div v-else>
                          <!-- Chart Preview -->
                          <div v-if="reportData.chart && currentView === 'chart'" class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                              <h3 class="text-lg font-medium text-gray-900">Chart Visualization</h3>
                              <div class="flex space-x-2">
                                <button 
                                  v-for="type in ['bar', 'line', 'pie', 'doughnut', 'polarArea']"
                                  :key="type"
                                  @click="updateChartType(type)" 
                                  class="px-2 py-1 text-xs rounded-md" 
                                  :class="currentChartType === type ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : 'bg-gray-100 text-gray-800'"
                                >
                                  {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                                </button>
                              </div>
                            </div>
                            <div class="h-80 relative">
                              <canvas ref="chartCanvas"></canvas>
                            </div>
                            <div class="mt-4 text-sm text-gray-500 text-center">
                              <p>Click on the chart type buttons above to change visualization</p>
                            </div>
                          </div>
                          
                          <!-- Table Preview -->
                          <div v-if="reportData.data && currentView === 'table'">
                            <div class="flex justify-between items-center mb-4">
                              <h3 class="text-lg font-medium text-gray-900">Data Table</h3>
                              <div class="flex items-center">
                                <label class="mr-2 text-sm text-gray-600">Rows per page:</label>
                                <select v-model="tableOptions.rowsPerPage" class="border border-gray-300 rounded-md text-sm px-2 py-1">
                                  <option value="10">10</option>
                                  <option value="25">25</option>
                                  <option value="50">50</option>
                                  <option value="100">100</option>
                                </select>
                              </div>
                            </div>
                            <div class="overflow-x-auto">
                              <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                  <tr>
                                    <th v-for="(header, index) in Object.keys(reportData.data[0] || {})" :key="index" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                      {{ header }}
                                    </th>
                                  </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                  <tr v-for="(row, rowIndex) in paginatedData" :key="rowIndex">
                                    <td v-for="(header, headerIndex) in Object.keys(reportData.data[0] || {})" :key="headerIndex" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                      {{ row[header] }}
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div v-if="reportData.data.length > tableOptions.rowsPerPage" class="flex justify-between items-center mt-4 px-4 py-3 bg-gray-50 text-gray-500 text-sm rounded-md">
                              <div>
                                Showing {{ paginationStart + 1 }} to {{ Math.min(paginationStart + tableOptions.rowsPerPage, reportData.data.length) }} of {{ reportData.data.length }} entries
                              </div>
                              <div class="flex space-x-2">
                                <button 
                                  @click="prevPage" 
                                  :disabled="tableOptions.currentPage === 1"
                                  class="px-3 py-1 border rounded-md" 
                                  :class="tableOptions.currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'"
                                >
                                  Previous
                                </button>
                                <button 
                                  @click="nextPage" 
                                  :disabled="tableOptions.currentPage >= totalPages"
                                  class="px-3 py-1 border rounded-md" 
                                  :class="tableOptions.currentPage >= totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'"
                                >
                                  Next
                                </button>
                              </div>
                            </div>
                          </div>
                          
                          <!-- Summary Preview -->
                          <div v-if="reportData.summary && currentView === 'summary'">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Report Summary</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                              <div v-if="reportData.summary.metrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                <div v-for="(metric, index) in reportData.summary.metrics" :key="index" class="bg-white p-4 rounded-lg shadow-sm">
                                  <div class="text-sm text-gray-500">{{ metric.label }}</div>
                                  <div class="text-2xl font-bold">{{ metric.value }}</div>
                                  <div v-if="metric.change" class="text-sm mt-1" :class="metric.change > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ metric.change > 0 ? '+' : '' }}{{ metric.change }}% from previous period
                                  </div>
                                </div>
                              </div>
                              
                              <div v-if="reportData.summary.text" class="prose max-w-none">
                                <div v-html="reportData.summary.text"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Chart if available -->
                  <div v-if="reportData.chart" class="mb-6">
                    <canvas ref="chartCanvas" width="400" height="200"></canvas>
                  </div>
                  
                  <!-- Data Table -->
                  <div v-if="reportData.data && reportData.data.length > 0">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Details</h3>
                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                          <tr>
                            <th v-for="(header, index) in Object.keys(reportData.data[0])" :key="index" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              {{ formatHeader(header) }}
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="(row, rowIndex) in reportData.data" :key="rowIndex">
                            <td v-for="(value, key) in row" :key="key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                              {{ formatTableValue(key, value) }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Generic Report Display for other types -->
              <div v-else>
                <!-- Chart if available -->
                <div v-if="reportData.chart" class="mb-6">
                  <canvas ref="chartCanvas" width="400" height="200"></canvas>
                </div>
                
                <!-- Data Table -->
                <div v-if="reportData.data && reportData.data.length > 0">
                  <h3 class="text-lg font-semibold text-gray-800 mb-3">Details</h3>
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th v-for="(header, index) in Object.keys(reportData.data[0])" :key="index" 
                              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ formatHeader(header) }}
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(row, rowIndex) in reportData.data" :key="rowIndex">
                          <td v-for="(value, key) in row" :key="key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatTableValue(key, value) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import Chart from 'chart.js/auto';
import ReportNavigation from '../../components/reports/ReportNavigation.vue';
import ReportTemplateManager from '../../components/reports/ReportTemplateManager.vue';

export default {
  name: 'ReportView',
  components: {
    ReportNavigation,
    ReportTemplateManager
  },
  data() {
    return {
      reportId: this.$route.params.id,
      generating: false,
      scheduling: false,
      reportData: null,
      showTemplates: false,
      generateOptions: {
        format: 'pdf',
        chartType: 'bar',
        startDate: this.getDefaultStartDate(),
        endDate: this.getDefaultEndDate()
      },
      scheduleOptions: {
        frequency: 'monthly',
        recipients: ''
      },
      showScheduleOptions: false,
      currentView: 'chart',
      currentChartType: 'bar',
      chart: null,
      tableOptions: {
        rowsPerPage: 10,
        currentPage: 1
      },
      exportStatus: null
    };
  },
  computed: {
    ...mapState('reports', ['loading', 'error']),
    ...mapState({
      exportStatus: state => state.reports.exportStatus
    }),
    report() {
      return this.$store.state.reports.report || {};
    },
    paginatedData() {
      if (!this.reportData || !this.reportData.data) return [];
      
      const start = (this.tableOptions.currentPage - 1) * this.tableOptions.rowsPerPage;
      const end = start + this.tableOptions.rowsPerPage;
      
      return this.reportData.data.slice(start, end);
    },
    paginationStart() {
      return (this.tableOptions.currentPage - 1) * this.tableOptions.rowsPerPage;
    },
    totalPages() {
      if (!this.reportData || !this.reportData.data) return 1;
      return Math.ceil(this.reportData.data.length / this.tableOptions.rowsPerPage);
    }
  },
  created() {
    this.loadReport();
    
    // Check if we should generate the report immediately
    if (this.$route.query.generate === 'true') {
      this.$nextTick(() => {
        this.generateReport();
      });
    }
  },
  methods: {
    ...mapActions('reports', [
      'fetchReport',
      'toggleFavorite',
      'deleteReport',
      'scheduleReport'
    ]),
    async loadReport() {
      try {
        await this.fetchReport(this.reportId);
        
        // Set the output format from the report's default
        if (this.report.output_format) {
          this.generateOptions.format = this.report.output_format;
        }
      } catch (error) {
        console.error('Error loading report:', error);
      }
    },
    
    async generateReport() {
      this.generating = true;
      this.reportData = null;
      
      try {
        // Prepare parameters for report generation
        const params = {
          id: this.reportId,
          format: this.generateOptions.format,
          params: {
            chartType: this.generateOptions.chartType,
            startDate: this.generateOptions.startDate,
            endDate: this.generateOptions.endDate
          }
        };
        
        const response = await this.$store.dispatch('reports/generateReport', params);
        
        // For non-file formats, process the data
        if (this.generateOptions.format !== 'pdf' && this.generateOptions.format !== 'excel') {
          this.reportData = response.data || response;
          
          // Set the current view based on what data is available
          if (this.reportData?.chart) {
            this.currentView = 'chart';
            this.currentChartType = this.generateOptions.chartType;
            this.$nextTick(() => {
              this.renderChart();
            });
          } else if (this.reportData?.data && this.reportData.data.length > 0) {
            this.currentView = 'table';
          } else if (this.reportData?.summary) {
            this.currentView = 'summary';
          }
        } else {
          // For file downloads, show a success message
          this.reportData = {
            summary: {
              text: `<p>Your ${this.generateOptions.format === 'pdf' ? 'PDF' : 'Excel'} report has been downloaded.</p>`
            }
          };
          this.currentView = 'summary';
        }
      } catch (error) {
        console.error('Error generating report:', error);
        // Show error in the UI
        this.reportData = {
          summary: {
            text: `<p class="text-red-600">Error generating report: ${error.message || 'Unknown error'}</p>`
          }
        };
        this.currentView = 'summary';
      } finally {
        this.generating = false;
      }
    },
    
    renderChart() {
      if (this.chart) {
        this.chart.destroy();
      }
      
      if (!this.$refs.chartCanvas) return;
      
      const ctx = this.$refs.chartCanvas.getContext('2d');
      const chartData = this.reportData.chart;
      
      this.chart = new Chart(ctx, {
        type: this.currentChartType || chartData.type || 'bar',
        data: {
          labels: chartData.labels,
          datasets: chartData.datasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: chartData.datasets.length > 1
            },
            title: {
              display: true,
              text: this.report.name || 'Report Chart',
              font: {
                size: 16
              }
            }
          }
        }
      });
    },
    
    updateChartType(type) {
      this.currentChartType = type;
      this.renderChart();
    },
    
    toggleView(view) {
      this.currentView = view;
      
      // If switching to chart view and chart exists, re-render it
      if (view === 'chart' && this.reportData?.chart) {
        this.$nextTick(() => {
          this.renderChart();
        });
      }
    },
    
    prevPage() {
      if (this.tableOptions.currentPage > 1) {
        this.tableOptions.currentPage--;
      }
    },
    
    nextPage() {
      if (this.tableOptions.currentPage < this.totalPages) {
        this.tableOptions.currentPage++;
      }
    },
    
    getDefaultStartDate() {
      const date = new Date();
      date.setMonth(date.getMonth() - 1);
      return date.toISOString().split('T')[0];
    },
    
    getDefaultEndDate() {
      return new Date().toISOString().split('T')[0];
    },
    
    async scheduleReport() {
      this.scheduling = true;
      
      try {
        await this.$store.dispatch('reports/scheduleReport', {
          id: this.reportId,
          schedule: {
            frequency: this.scheduleOptions.frequency,
            recipients: this.scheduleOptions.recipients.split(',').map(email => email.trim()),
            format: this.generateOptions.format
          }
        });
        
        alert('Report scheduled successfully!');
        this.showScheduleOptions = false;
      } catch (error) {
        console.error('Error scheduling report:', error);
        alert('Failed to schedule report. Please try again.');
      } finally {
        this.scheduling = false;
      }
    },
    
    async applyTemplate(template) {
      try {
        // First apply the template on the server side
        await this.$store.dispatch('reports/applyTemplate', {
          reportId: this.reportId,
          templateId: template.id
        });
        
        // Then update the local options based on the template
        this.generateOptions.format = template.format || 'pdf';
        this.generateOptions.chartType = template.chart_type || 'bar';
        
        // Reload the report to get the updated data
        await this.loadReport();
        
        // Show a success message
        alert(`Template "${template.name}" applied successfully.`);
        
        // Generate the report automatically if requested
        if (confirm('Would you like to generate the report now?')) {
          this.generateReport();
        }
      } catch (error) {
        console.error('Error applying template:', error);
        alert('Failed to apply template. Please try again.');
      }
    },
    
    templateSaved() {
      // Refresh the templates list
      this.$store.dispatch('reports/fetchTemplates');
      alert('Template saved successfully!');
    },
    
    async handleToggleFavorite() {
      try {
        await this.toggleFavorite(this.reportId);
        await this.loadReport(); // Refresh report data
      } catch (error) {
        console.error('Error toggling favorite:', error);
      }
    },
    
    async handleDeleteReport() {
      if (!confirm('Are you sure you want to delete this report?')) return;
      
      try {
        await this.deleteReport(this.reportId);
        this.$router.push('/reports');
      } catch (error) {
        console.error('Error deleting report:', error);
      }
    },
    
    getTypeDisplay(type) {
      const types = {
        financial: 'Financial',
        donation: 'Donation',
        expense: 'Expense',
        attendance: 'Attendance',
        membership: 'Membership',
        pledge: 'Pledge',
        campaign: 'Campaign',
        custom: 'Custom'
      };
      
      return types[type] || type.charAt(0).toUpperCase() + type.slice(1);
    },
    
    getTypeClass(type) {
      const classes = {
        financial: 'bg-green-100 text-green-800',
        donation: 'bg-blue-100 text-blue-800',
        expense: 'bg-red-100 text-red-800',
        attendance: 'bg-purple-100 text-purple-800',
        membership: 'bg-indigo-100 text-indigo-800',
        pledge: 'bg-yellow-100 text-yellow-800',
        campaign: 'bg-pink-100 text-pink-800',
        custom: 'bg-gray-100 text-gray-800'
      };
      
      return classes[type] || 'bg-gray-100 text-gray-800';
    },
    
    getScheduleDisplay(frequency) {
      const displays = {
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        quarterly: 'Quarterly'
      };
      
      return displays[frequency] || frequency;
    }
  }
};
</script>
