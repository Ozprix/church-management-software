<?php

namespace App\Repositories\Interfaces;

use App\Models\FinancialMetric;
use Illuminate\Database\Eloquent\Collection;

interface FinancialMetricRepositoryInterface
{
    /**
     * Get all metrics for a specific date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsBetweenDates(string $startDate, string $endDate): Collection;

    /**
     * Get metrics with a specific name.
     *
     * @param string $metricName
     * @return Collection
     */
    public function getMetricsByName(string $metricName): Collection;

    /**
     * Get metrics with a specific category.
     *
     * @param string $category
     * @return Collection
     */
    public function getMetricsByCategory(string $category): Collection;

    /**
     * Get metrics with a specific name for a date range.
     *
     * @param string $metricName
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsByNameAndDateRange(string $metricName, string $startDate, string $endDate): Collection;

    /**
     * Get metrics with a specific name and category for a date range.
     *
     * @param string $metricName
     * @param string $category
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMetricsByNameCategoryAndDateRange(string $metricName, string $category, string $startDate, string $endDate): Collection;

    /**
     * Create a new financial metric.
     *
     * @param array $data
     * @return FinancialMetric
     */
    public function createMetric(array $data): FinancialMetric;

    /**
     * Update a financial metric.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMetric(int $id, array $data): bool;

    /**
     * Delete a financial metric.
     *
     * @param int $id
     * @return bool
     */
    public function deleteMetric(int $id): bool;

    /**
     * Get trend data for a specific metric over time.
     *
     * @param string $metricName
     * @param string $startDate
     * @param string $endDate
     * @param string $interval
     * @return array
     */
    public function getMetricTrend(string $metricName, string $startDate, string $endDate, string $interval = 'month'): array;

    /**
     * Calculate and store key financial metrics for a specific date.
     *
     * @param string $date
     * @return bool
     */
    public function calculateAndStoreMetrics(string $date): bool;

    /**
     * Get the latest value for a specific metric.
     *
     * @param string $metricName
     * @param string|null $category
     * @return float|null
     */
    public function getLatestMetricValue(string $metricName, ?string $category = null): ?float;

    /**
     * Get a summary of key financial metrics for a specific period.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getFinancialSummary(string $startDate, string $endDate): array;
}
