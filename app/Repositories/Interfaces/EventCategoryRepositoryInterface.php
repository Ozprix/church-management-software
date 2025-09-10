<?php

namespace App\Repositories\Interfaces;

use App\Models\EventCategory;
use Illuminate\Database\Eloquent\Collection;

interface EventCategoryRepositoryInterface
{
    /**
     * Get all event categories.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection;
    
    /**
     * Get all active event categories.
     *
     * @return Collection
     */
    public function getActiveCategories(): Collection;
    
    /**
     * Get a specific event category by ID.
     *
     * @param int $categoryId
     * @return EventCategory|null
     */
    public function getCategoryById(int $categoryId): ?EventCategory;
    
    /**
     * Create a new event category.
     *
     * @param array $data
     * @return EventCategory
     */
    public function createCategory(array $data): EventCategory;
    
    /**
     * Update an existing event category.
     *
     * @param int $categoryId
     * @param array $data
     * @return EventCategory|null
     */
    public function updateCategory(int $categoryId, array $data): ?EventCategory;
    
    /**
     * Delete an event category.
     *
     * @param int $categoryId
     * @return bool
     */
    public function deleteCategory(int $categoryId): bool;
}
