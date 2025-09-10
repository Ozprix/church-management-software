<?php

namespace App\Repositories\Interfaces;

use App\Models\FinancialForecast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FinancialForecastRepositoryInterface
{
    /**
     * Get all financial forecasts.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllForecasts(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a forecast by ID.
     *
     * @param int $id
     * @return FinancialForecast|null
     */
    public function getForecastById(int $id): ?FinancialForecast;

    /**
     * Create a new forecast.
     *
     * @param array $data
     * @return FinancialForecast
     */
    public function createForecast(array $data): FinancialForecast;

    /**
     * Update a forecast.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateForecast(int $id, array $data): bool;

    /**
     * Delete a forecast.
     *
     * @param int $id
     * @return bool
     */
    public function deleteForecast(int $id): bool;

    /**
     * Get active forecasts.
     *
     * @return Collection
     */
    public function getActiveForecasts(): Collection;

    /**
     * Get forecasts for a specific period type.
     *
     * @param string $periodType
     * @return Collection
     */
    public function getForecastsByPeriodType(string $periodType): Collection;

    /**
     * Get forecasts that include a specific date.
     *
     * @param string $date
     * @return Collection
     */
    public function getForecastsForDate(string $date): Collection;

    /**
     * Add an item to a forecast.
     *
     * @param int $forecastId
     * @param array $itemData
     * @return bool
     */
    public function addForecastItem(int $forecastId, array $itemData): bool;

    /**
     * Update a forecast item.
     *
     * @param int $itemId
     * @param array $data
     * @return bool
     */
    public function updateForecastItem(int $itemId, array $data): bool;

    /**
     * Delete a forecast item.
     *
     * @param int $itemId
     * @return bool
     */
    public function deleteForecastItem(int $itemId): bool;

    /**
     * Get variance between forecast and actual for a specific date range.
     *
     * @param int $forecastId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getVariance(int $forecastId, string $startDate, string $endDate): array;
}
