<?php

namespace App\Repositories;

use App\Models\FinancialMetric;
use App\Repositories\Interfaces\FinancialMetricRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class FinancialMetricRepository implements FinancialMetricRepositoryInterface
{
    /**
     * Get all metrics for a specific date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsBetweenDates(string $startDate, string $endDate): Collection
    {
        $cacheKey = 'metrics_' . $startDate . '_' . $endDate;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($startDate, $endDate) {
            return FinancialMetric::betweenDates($startDate, $endDate)->get();
        });
    }

    /**
     * Get metrics with a specific name.
     *
     * @param string $metricName
     * @return Collection
     */
    public function getMetricsByName(string $metricName): Collection
    {
        $cacheKey = 'metrics_name_' . str_replace(' ', '_', strtolower($metricName));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($metricName) {
            return FinancialMetric::withName($metricName)->get();
        });
    }

    /**
     * Get metrics with a specific category.
     *
     * @param string $category
     * @return Collection
     */
    public function getMetricsByCategory(string $category): Collection
    {
        $cacheKey = 'metrics_category_' . str_replace(' ', '_', strtolower($category));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($category) {
            return FinancialMetric::withCategory($category)->get();
        });
    }

    /**
     * Get metrics with a specific name for a date range.
     *
     * @param string $metricName
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsByNameAndDateRange(string $metricName, string $startDate, string $endDate): Collection
    {
        $cacheKey = 'metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . '_' . $startDate . '_' . $endDate;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($metricName, $startDate, $endDate) {
            return FinancialMetric::withName($metricName)
                ->betweenDates($startDate, $endDate)
                ->orderBy('date')
                ->get();
        });
    }

    /**
     * Get metrics with a specific name and category for a date range.
     *
     * @param string $metricName
     * @param string $category
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsByNameCategoryAndDateRange(string $metricName, string $category, string $startDate, string $endDate): Collection
    {
        $cacheKey = 'metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . 
                    '_category_' . str_replace(' ', '_', strtolower($category)) . 
                    '_' . $startDate . '_' . $endDate;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($metricName, $category, $startDate, $endDate) {
            return FinancialMetric::withName($metricName)
                ->withCategory($category)
                ->betweenDates($startDate, $endDate)
                ->orderBy('date')
                ->get();
        });
    }

    /**
     * Create a new financial metric.
     *
     * @param array $data
     * @return FinancialMetric
     */
    public function createMetric(array $data): FinancialMetric
    {
        $metric = FinancialMetric::create($data);
        
        // Clear relevant caches
        $this->clearMetricCaches($data['metric_name'] ?? '', $data['category'] ?? '', $data['date'] ?? '');
        
        return $metric;
    }

    /**
     * Update a financial metric.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMetric(int $id, array $data): bool
    {
        $metric = FinancialMetric::find($id);
        
        if (!$metric) {
            return false;
        }
        
        // Clear relevant caches before update
        $this->clearMetricCaches($metric->metric_name, $metric->category, $metric->date);
        
        $result = $metric->update($data);
        
        // Clear relevant caches after update if name, category or date changed
        if (isset($data['metric_name']) || isset($data['category']) || isset($data['date'])) {
            $this->clearMetricCaches(
                $data['metric_name'] ?? $metric->metric_name,
                $data['category'] ?? $metric->category,
                $data['date'] ?? $metric->date
            );
        }
        
        return $result;
    }

    /**
     * Delete a financial metric.
     *
     * @param int $id
     * @return bool
     */
    public function deleteMetric(int $id): bool
    {
        $metric = FinancialMetric::find($id);
        
        if (!$metric) {
            return false;
        }
        
        // Clear relevant caches
        $this->clearMetricCaches($metric->metric_name, $metric->category, $metric->date);
        
        return $metric->delete();
    }

    /**
     * Get trend data for a specific metric over time.
     *
     * @param string $metricName
     * @param string $startDate
     * @param string $endDate
     * @param string $interval
     * @return array
     */
    public function getMetricTrend(string $metricName, string $startDate, string $endDate, string $interval = 'month'): array
    {
        $cacheKey = 'metric_trend_' . str_replace(' ', '_', strtolower($metricName)) . 
                    '_' . $startDate . '_' . $endDate . '_' . $interval;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($metricName, $startDate, $endDate, $interval) {
            return FinancialMetric::getTrend($metricName, $startDate, $endDate, $interval);
        });
    }

    /**
     * Calculate and store key financial metrics for a specific date.
     *
     * @param string $date
     * @return bool
     */
    public function calculateAndStoreMetrics(string $date): bool
    {
        $result = FinancialMetric::calculateAndStoreMetrics($date);
        
        // Clear all metric caches
        Cache::flush();
        
        return $result;
    }

    /**
     * Get the latest value for a specific metric.
     *
     * @param string $metricName
     * @param string|null $category
     * @return float|null
     */
    public function getLatestMetricValue(string $metricName, ?string $category = null): ?float
    {
        $cacheKey = 'latest_metric_' . str_replace(' ', '_', strtolower($metricName));
        
        if ($category) {
            $cacheKey .= '_category_' . str_replace(' ', '_', strtolower($category));
        }
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($metricName, $category) {
            $query = FinancialMetric::withName($metricName);
            
            if ($category) {
                $query->withCategory($category);
            }
            
            $latestMetric = $query->latest('date')->first();
            
            return $latestMetric ? $latestMetric->value : null;
        });
    }

    /**
     * Get a summary of key financial metrics for a specific period.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getFinancialSummary(string $startDate, string $endDate): array
    {
        $cacheKey = 'financial_summary_' . $startDate . '_' . $endDate;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($startDate, $endDate) {
            $summary = [];
            
            // Get total donations
            $donationMetrics = $this->getMetricsByNameAndDateRange('monthly_donations', $startDate, $endDate);
            $summary['total_donations'] = $donationMetrics->sum('value');
            
            // Get total expenses
            $expenseMetrics = $this->getMetricsByNameAndDateRange('monthly_expenses', $startDate, $endDate);
            $summary['total_expenses'] = $expenseMetrics->sum('value');
            
            // Calculate net income
            $summary['net_income'] = $summary['total_donations'] - $summary['total_expenses'];
            
            // Get average donation amount
            $avgDonationMetrics = $this->getMetricsByNameAndDateRange('average_donation', $startDate, $endDate);
            $summary['average_donation'] = $avgDonationMetrics->avg('value');
            
            // Get donor count
            $donorCountMetrics = $this->getMetricsByNameAndDateRange('donor_count', $startDate, $endDate);
            $summary['total_donors'] = $donorCountMetrics->sum('value');
            
            // Get recurring donation percentage
            $recurringMetrics = $this->getMetricsByNameAndDateRange('recurring_donation_percentage', $startDate, $endDate);
            $summary['recurring_percentage'] = $recurringMetrics->avg('value');
            
            // Get expense breakdown by category
            $expenseCategoryMetrics = FinancialMetric::withName('expense_by_category')
                ->betweenDates($startDate, $endDate)
                ->get()
                ->groupBy('category');
                
            $summary['expense_by_category'] = [];
            
            foreach ($expenseCategoryMetrics as $category => $metrics) {
                $summary['expense_by_category'][$category] = $metrics->sum('value');
            }
            
            // Get donation breakdown by category
            $donationCategoryMetrics = FinancialMetric::withName('donation_by_category')
                ->betweenDates($startDate, $endDate)
                ->get()
                ->groupBy('category');
                
            $summary['donation_by_category'] = [];
            
            foreach ($donationCategoryMetrics as $category => $metrics) {
                $summary['donation_by_category'][$category] = $metrics->sum('value');
            }
            
            return $summary;
        });
    }

    /**
     * Clear caches related to a specific metric.
     *
     * @param string $metricName
     * @param string|null $category
     * @param string|null $date
     * @return void
     */
    private function clearMetricCaches(string $metricName, ?string $category = null, ?string $date = null): void
    {
        // Clear name-based cache
        Cache::forget('metrics_name_' . str_replace(' ', '_', strtolower($metricName)));
        
        // Clear category-based cache if category is provided
        if ($category) {
            Cache::forget('metrics_category_' . str_replace(' ', '_', strtolower($category)));
        }
        
        // Clear date-based caches if date is provided
        if ($date) {
            $dateObj = Carbon::parse($date);
            
            // Clear caches for various date ranges that might include this date
            $monthStart = $dateObj->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $dateObj->copy()->endOfMonth()->format('Y-m-d');
            Cache::forget('metrics_' . $monthStart . '_' . $monthEnd);
            
            $yearStart = $dateObj->copy()->startOfYear()->format('Y-m-d');
            $yearEnd = $dateObj->copy()->endOfYear()->format('Y-m-d');
            Cache::forget('metrics_' . $yearStart . '_' . $yearEnd);
            
            // Clear name and date range caches
            Cache::forget('metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . '_' . $monthStart . '_' . $monthEnd);
            Cache::forget('metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . '_' . $yearStart . '_' . $yearEnd);
            
            // Clear name, category and date range caches
            if ($category) {
                Cache::forget('metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . 
                              '_category_' . str_replace(' ', '_', strtolower($category)) . 
                              '_' . $monthStart . '_' . $monthEnd);
                              
                Cache::forget('metrics_name_' . str_replace(' ', '_', strtolower($metricName)) . 
                              '_category_' . str_replace(' ', '_', strtolower($category)) . 
                              '_' . $yearStart . '_' . $yearEnd);
            }
            
            // Clear trend caches
            Cache::forget('metric_trend_' . str_replace(' ', '_', strtolower($metricName)) . 
                          '_' . $yearStart . '_' . $yearEnd . '_month');
                          
            // Clear financial summary caches
            Cache::forget('financial_summary_' . $monthStart . '_' . $monthEnd);
            Cache::forget('financial_summary_' . $yearStart . '_' . $yearEnd);
        }
        
        // Clear latest metric cache
        Cache::forget('latest_metric_' . str_replace(' ', '_', strtolower($metricName)));
        
        if ($category) {
            Cache::forget('latest_metric_' . str_replace(' ', '_', strtolower($metricName)) . 
                         '_category_' . str_replace(' ', '_', strtolower($category)));
        }
    }
}
