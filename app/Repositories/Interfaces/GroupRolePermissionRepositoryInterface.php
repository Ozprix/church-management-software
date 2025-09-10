<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupRolePermission;
use Illuminate\Database\Eloquent\Collection;

interface GroupRolePermissionRepositoryInterface
{
    /**
     * Get all role permissions for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupRolePermissions(int $groupId): Collection;
    
    /**
     * Get permissions for a specific role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @return Collection
     */
    public function getRolePermissions(int $groupId, string $role): Collection;
    
    /**
     * Assign permissions to a role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissionsToRole(int $groupId, string $role, array $permissionIds): bool;
    
    /**
     * Remove a specific permission from a role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param int $permissionId
     * @return bool
     */
    public function removePermissionFromRole(int $groupId, string $role, int $permissionId): bool;
    
    /**
     * Check if a role has a specific permission in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param string $permissionSlug
     * @return bool
     */
    public function roleHasPermission(int $groupId, string $role, string $permissionSlug): bool;
    
    /**
     * Initialize default role permissions for a new group.
     *
     * @param int $groupId
     * @return bool
     */
    public function initializeDefaultRolePermissions(int $groupId): bool;
}
