<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupPermission;
use Illuminate\Database\Eloquent\Collection;

interface GroupPermissionRepositoryInterface
{
    /**
     * Get all permissions.
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection;
    
    /**
     * Get permissions by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getPermissionsByCategory(string $category): Collection;
    
    /**
     * Get a permission by ID.
     *
     * @param int $id
     * @return GroupPermission|null
     */
    public function getPermissionById(int $id): ?GroupPermission;
    
    /**
     * Get a permission by slug.
     *
     * @param string $slug
     * @return GroupPermission|null
     */
    public function getPermissionBySlug(string $slug): ?GroupPermission;
    
    /**
     * Get all permission categories.
     *
     * @return array
     */
    public function getAllCategories(): array;
}
