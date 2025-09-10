<?php

namespace App\Repositories\Interfaces;

use App\Models\BudgetAllocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BudgetAllocationRepositoryInterface
{
    /**
     * Get all budget allocations.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllAllocations(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a budget allocation by ID.
     *
     * @param int $id
     * @return BudgetAllocation|null
     */
    public function getAllocationById(int $id): ?BudgetAllocation;

    /**
     * Get allocations for a specific budget.
     *
     * @param int $budgetId
     * @return Collection
     */
    public function getAllocationsForBudget(int $budgetId): Collection;

    /**
     * Create a new budget allocation.
     *
     * @param array $data
     * @return BudgetAllocation
     */
    public function createAllocation(array $data): BudgetAllocation;

    /**
     * Update a budget allocation.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAllocation(int $id, array $data): bool;

    /**
     * Delete a budget allocation.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAllocation(int $id): bool;

    /**
     * Get allocations for a specific department.
     *
     * @param string $department
     * @return Collection
     */
    public function getAllocationsForDepartment(string $department): Collection;

    /**
     * Get allocations for a specific ministry.
     *
     * @param string $ministry
     * @return Collection
     */
    public function getAllocationsForMinistry(string $ministry): Collection;

    /**
     * Get allocations for a specific project.
     *
     * @param string $project
     * @return Collection
     */
    public function getAllocationsForProject(string $project): Collection;

    /**
     * Get allocations for a specific category.
     *
     * @param string $category
     * @return Collection
     */
    public function getAllocationsForCategory(string $category): Collection;

    /**
     * Get allocations that are over budget.
     *
     * @return Collection
     */
    public function getOverBudgetAllocations(): Collection;

    /**
     * Get allocations that are near their limit.
     *
     * @param float $threshold Percentage threshold (default 90%)
     * @return Collection
     */
    public function getNearLimitAllocations(float $threshold = 90): Collection;

    /**
     * Update the used amount for a specific allocation based on related expenses.
     *
     * @param int $allocationId
     * @return bool
     */
    public function updateUsedAmount(int $allocationId): bool;

    /**
     * Update used amounts for all allocations.
     *
     * @return int Number of allocations updated
     */
    public function updateAllUsedAmounts(): int;
}
