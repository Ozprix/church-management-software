<?php

namespace App\Repositories;

use App\Models\FinancialForecast;
use App\Models\ForecastItem;
use App\Repositories\Interfaces\FinancialForecastRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FinancialForecastRepository implements FinancialForecastRepositoryInterface
{
    /**
     * Get all financial forecasts.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllForecasts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FinancialForecast::with('creator', 'items');
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['period_type'])) {
            $query->where('period_type', $filters['period_type']);
        }
        
        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['date'])) {
            $query->includingDate($filters['date']);
        }
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->where(function($q) use ($filters) {
                $q->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                  ->orWhereBetween('end_date', [$filters['start_date'], $filters['end_date']])
                  ->orWhere(function($q2) use ($filters) {
                      $q2->where('start_date', '<=', $filters['start_date'])
                         ->where('end_date', '>=', $filters['end_date']);
                  });
            });
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a forecast by ID.
     *
     * @param int $id
     * @return FinancialForecast|null
     */
    public function getForecastById(int $id): ?FinancialForecast
    {
        return FinancialForecast::with(['creator', 'items'])->find($id);
    }

    /**
     * Create a new forecast.
     *
     * @param array $data
     * @return FinancialForecast
     */
    public function createForecast(array $data): FinancialForecast
    {
        $forecast = FinancialForecast::create($data);
        
        // If forecast items are provided, create them
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $item['forecast_id'] = $forecast->id;
                ForecastItem::create($item);
            }
        }
        
        return $forecast->fresh(['creator', 'items']);
    }

    /**
     * Update a forecast.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateForecast(int $id, array $data): bool
    {
        $forecast = $this->getForecastById($id);
        
        if (!$forecast) {
            return false;
        }
        
        return $forecast->update($data);
    }

    /**
     * Delete a forecast.
     *
     * @param int $id
     * @return bool
     */
    public function deleteForecast(int $id): bool
    {
        $forecast = $this->getForecastById($id);
        
        if (!$forecast) {
            return false;
        }
        
        // Delete related items first
        $forecast->items()->delete();
        
        return $forecast->delete();
    }

    /**
     * Get active forecasts.
     *
     * @return Collection
     */
    public function getActiveForecasts(): Collection
    {
        $cacheKey = 'active_forecasts';
        
        return Cache::remember($cacheKey, 60 * 15, function() {
            return FinancialForecast::active()->with(['items'])->get();
        });
    }

    /**
     * Get forecasts for a specific period type.
     *
     * @param string $periodType
     * @return Collection
     */
    public function getForecastsByPeriodType(string $periodType): Collection
    {
        $cacheKey = 'forecasts_period_' . $periodType;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($periodType) {
            return FinancialForecast::withPeriodType($periodType)->with(['items'])->get();
        });
    }

    /**
     * Get forecasts that include a specific date.
     *
     * @param string $date
     * @return Collection
     */
    public function getForecastsForDate(string $date): Collection
    {
        $cacheKey = 'forecasts_date_' . $date;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($date) {
            return FinancialForecast::includingDate($date)->with(['items'])->get();
        });
    }

    /**
     * Add an item to a forecast.
     *
     * @param int $forecastId
     * @param array $itemData
     * @return bool
     */
    public function addForecastItem(int $forecastId, array $itemData): bool
    {
        $forecast = $this->getForecastById($forecastId);
        
        if (!$forecast) {
            return false;
        }
        
        $itemData['forecast_id'] = $forecastId;
        ForecastItem::create($itemData);
        
        return true;
    }

    /**
     * Update a forecast item.
     *
     * @param int $itemId
     * @param array $data
     * @return bool
     */
    public function updateForecastItem(int $itemId, array $data): bool
    {
        $item = ForecastItem::find($itemId);
        
        if (!$item) {
            return false;
        }
        
        return $item->update($data);
    }

    /**
     * Delete a forecast item.
     *
     * @param int $itemId
     * @return bool
     */
    public function deleteForecastItem(int $itemId): bool
    {
        $item = ForecastItem::find($itemId);
        
        if (!$item) {
            return false;
        }
        
        return $item->delete();
    }

    /**
     * Get variance between forecast and actual for a specific date range.
     *
     * @param int $forecastId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getVariance(int $forecastId, string $startDate, string $endDate): array
    {
        $forecast = $this->getForecastById($forecastId);
        
        if (!$forecast) {
            return [
                'error' => 'Forecast not found',
            ];
        }
        
        return $forecast->getVariance($startDate, $endDate);
    }
}
