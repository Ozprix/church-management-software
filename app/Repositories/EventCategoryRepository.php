<?php

namespace App\Repositories;

use App\Models\EventCategory;
use App\Repositories\Interfaces\EventCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EventCategoryRepository implements EventCategoryRepositoryInterface
{
    /**
     * Get all event categories.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return Cache::remember('event_categories_all', 60 * 60, function () {
            return EventCategory::orderBy('name')->get();
        });
    }
    
    /**
     * Get all active event categories.
     *
     * @return Collection
     */
    public function getActiveCategories(): Collection
    {
        return Cache::remember('event_categories_active', 60 * 60, function () {
            return EventCategory::active()->orderBy('name')->get();
        });
    }
    
    /**
     * Get a specific event category by ID.
     *
     * @param int $categoryId
     * @return EventCategory|null
     */
    public function getCategoryById(int $categoryId): ?EventCategory
    {
        return Cache::remember("event_category_{$categoryId}", 60 * 60, function () use ($categoryId) {
            return EventCategory::find($categoryId);
        });
    }
    
    /**
     * Create a new event category.
     *
     * @param array $data
     * @return EventCategory
     */
    public function createCategory(array $data): EventCategory
    {
        $category = EventCategory::create($data);
        
        // Clear cache
        $this->clearCategoryCache();
        
        return $category;
    }
    
    /**
     * Update an existing event category.
     *
     * @param int $categoryId
     * @param array $data
     * @return EventCategory|null
     */
    public function updateCategory(int $categoryId, array $data): ?EventCategory
    {
        $category = EventCategory::find($categoryId);
        
        if (!$category) {
            return null;
        }
        
        $category->update($data);
        
        // Clear cache
        Cache::forget("event_category_{$categoryId}");
        $this->clearCategoryCache();
        
        return $category->fresh();
    }
    
    /**
     * Delete an event category.
     *
     * @param int $categoryId
     * @return bool
     */
    public function deleteCategory(int $categoryId): bool
    {
        $category = EventCategory::find($categoryId);
        
        if (!$category) {
            return false;
        }
        
        $result = $category->delete();
        
        // Clear cache
        Cache::forget("event_category_{$categoryId}");
        $this->clearCategoryCache();
        
        return $result;
    }
    
    /**
     * Clear all category-related cache.
     *
     * @return void
     */
    private function clearCategoryCache(): void
    {
        Cache::forget('event_categories_all');
        Cache::forget('event_categories_active');
    }
}
