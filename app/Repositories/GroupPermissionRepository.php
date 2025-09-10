<?php

namespace App\Repositories;

use App\Models\GroupPermission;
use App\Repositories\Interfaces\GroupPermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class GroupPermissionRepository implements GroupPermissionRepositoryInterface
{
    /**
     * Get all permissions.
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        return Cache::remember('all_group_permissions', 60 * 60, function () {
            return GroupPermission::orderBy('category')->orderBy('name')->get();
        });
    }
    
    /**
     * Get permissions by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getPermissionsByCategory(string $category): Collection
    {
        return Cache::remember("group_permissions_category_{$category}", 60 * 60, function () use ($category) {
            return GroupPermission::ofCategory($category)->orderBy('name')->get();
        });
    }
    
    /**
     * Get a permission by ID.
     *
     * @param int $id
     * @return GroupPermission|null
     */
    public function getPermissionById(int $id): ?GroupPermission
    {
        return Cache::remember("group_permission_{$id}", 60 * 60, function () use ($id) {
            return GroupPermission::find($id);
        });
    }
    
    /**
     * Get a permission by slug.
     *
     * @param string $slug
     * @return GroupPermission|null
     */
    public function getPermissionBySlug(string $slug): ?GroupPermission
    {
        return Cache::remember("group_permission_slug_{$slug}", 60 * 60, function () use ($slug) {
            return GroupPermission::where('slug', $slug)->first();
        });
    }
    
    /**
     * Get all permission categories.
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return Cache::remember('group_permission_categories', 60 * 60, function () {
            return GroupPermission::select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        });
    }
}
