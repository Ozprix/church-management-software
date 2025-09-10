<?php

namespace App\Repositories;

use App\Models\BudgetAllocation;
use App\Repositories\Interfaces\BudgetAllocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BudgetAllocationRepository implements BudgetAllocationRepositoryInterface
{
    /**
     * Get all budget allocations.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllAllocations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = BudgetAllocation::with('budget');
        
        if (isset($filters['budget_id'])) {
            $query->where('budget_id', $filters['budget_id']);
        }
        
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        if (isset($filters['ministry'])) {
            $query->where('ministry', $filters['ministry']);
        }
        
        if (isset($filters['project'])) {
            $query->where('project', $filters['project']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['over_budget']) && $filters['over_budget']) {
            $query->overBudget();
        }
        
        if (isset($filters['near_limit']) && $filters['near_limit']) {
            $threshold = $filters['threshold'] ?? 90;
            $query->nearLimit($threshold);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('department', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('ministry', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('project', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('category', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('notes', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a budget allocation by ID.
     *
     * @param int $id
     * @return BudgetAllocation|null
     */
    public function getAllocationById(int $id): ?BudgetAllocation
    {
        return BudgetAllocation::with('budget')->find($id);
    }

    /**
     * Get allocations for a specific budget.
     *
     * @param int $budgetId
     * @return Collection
     */
    public function getAllocationsForBudget(int $budgetId): Collection
    {
        $cacheKey = 'budget_allocations_' . $budgetId;
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($budgetId) {
            return BudgetAllocation::where('budget_id', $budgetId)->get();
        });
    }

    /**
     * Create a new budget allocation.
     *
     * @param array $data
     * @return BudgetAllocation
     */
    public function createAllocation(array $data): BudgetAllocation
    {
        $allocation = BudgetAllocation::create($data);
        
        // Clear cache for this budget's allocations
        Cache::forget('budget_allocations_' . $data['budget_id']);
        
        return $allocation;
    }

    /**
     * Update a budget allocation.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAllocation(int $id, array $data): bool
    {
        $allocation = $this->getAllocationById($id);
        
        if (!$allocation) {
            return false;
        }
        
        $result = $allocation->update($data);
        
        // Clear cache for this budget's allocations
        Cache::forget('budget_allocations_' . $allocation->budget_id);
        
        return $result;
    }

    /**
     * Delete a budget allocation.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAllocation(int $id): bool
    {
        $allocation = $this->getAllocationById($id);
        
        if (!$allocation) {
            return false;
        }
        
        $budgetId = $allocation->budget_id;
        $result = $allocation->delete();
        
        // Clear cache for this budget's allocations
        Cache::forget('budget_allocations_' . $budgetId);
        
        return $result;
    }

    /**
     * Get allocations for a specific department.
     *
     * @param string $department
     * @return Collection
     */
    public function getAllocationsForDepartment(string $department): Collection
    {
        $cacheKey = 'department_allocations_' . str_replace(' ', '_', strtolower($department));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($department) {
            return BudgetAllocation::forDepartment($department)->with('budget')->get();
        });
    }

    /**
     * Get allocations for a specific ministry.
     *
     * @param string $ministry
     * @return Collection
     */
    public function getAllocationsForMinistry(string $ministry): Collection
    {
        $cacheKey = 'ministry_allocations_' . str_replace(' ', '_', strtolower($ministry));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($ministry) {
            return BudgetAllocation::forMinistry($ministry)->with('budget')->get();
        });
    }

    /**
     * Get allocations for a specific project.
     *
     * @param string $project
     * @return Collection
     */
    public function getAllocationsForProject(string $project): Collection
    {
        $cacheKey = 'project_allocations_' . str_replace(' ', '_', strtolower($project));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($project) {
            return BudgetAllocation::forProject($project)->with('budget')->get();
        });
    }

    /**
     * Get allocations for a specific category.
     *
     * @param string $category
     * @return Collection
     */
    public function getAllocationsForCategory(string $category): Collection
    {
        $cacheKey = 'category_allocations_' . str_replace(' ', '_', strtolower($category));
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($category) {
            return BudgetAllocation::forCategory($category)->with('budget')->get();
        });
    }

    /**
     * Get allocations that are over budget.
     *
     * @return Collection
     */
    public function getOverBudgetAllocations(): Collection
    {
        $cacheKey = 'over_budget_allocations';
        
        return Cache::remember($cacheKey, 60 * 5, function() {
            return BudgetAllocation::overBudget()->with('budget')->get();
        });
    }

    /**
     * Get allocations that are near their limit.
     *
     * @param float $threshold Percentage threshold (default 90%)
     * @return Collection
     */
    public function getNearLimitAllocations(float $threshold = 90): Collection
    {
        $cacheKey = 'near_limit_allocations_' . (int)$threshold;
        
        return Cache::remember($cacheKey, 60 * 5, function() use ($threshold) {
            return BudgetAllocation::nearLimit($threshold)->with('budget')->get();
        });
    }

    /**
     * Update the used amount for a specific allocation based on related expenses.
     *
     * @param int $allocationId
     * @return bool
     */
    public function updateUsedAmount(int $allocationId): bool
    {
        $allocation = $this->getAllocationById($allocationId);
        
        if (!$allocation) {
            return false;
        }
        
        $result = $allocation->updateUsedAmount();
        
        // Clear cache for this budget's allocations
        Cache::forget('budget_allocations_' . $allocation->budget_id);
        Cache::forget('over_budget_allocations');
        Cache::forget('near_limit_allocations_90');
        
        return $result;
    }

    /**
     * Update used amounts for all allocations.
     *
     * @return int Number of allocations updated
     */
    public function updateAllUsedAmounts(): int
    {
        $allocations = BudgetAllocation::all();
        $updatedCount = 0;
        
        foreach ($allocations as $allocation) {
            if ($allocation->updateUsedAmount()) {
                $updatedCount++;
            }
        }
        
        // Clear all relevant caches
        Cache::flush();
        
        return $updatedCount;
    }
}
