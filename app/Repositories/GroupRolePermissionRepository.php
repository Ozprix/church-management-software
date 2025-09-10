<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\GroupPermission;
use App\Models\GroupRolePermission;
use App\Repositories\Interfaces\GroupRolePermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupRolePermissionRepository implements GroupRolePermissionRepositoryInterface
{
    /**
     * Get all role permissions for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupRolePermissions(int $groupId): Collection
    {
        return Cache::remember("group_{$groupId}_role_permissions", 60 * 5, function () use ($groupId) {
            return GroupRolePermission::with('permission')
                ->where('group_id', $groupId)
                ->get();
        });
    }
    
    /**
     * Get permissions for a specific role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @return Collection
     */
    public function getRolePermissions(int $groupId, string $role): Collection
    {
        $cacheKey = "group_{$groupId}_role_{$role}_permissions";
        
        return Cache::remember($cacheKey, 60 * 5, function () use ($groupId, $role) {
            return GroupPermission::whereIn('id', function ($query) use ($groupId, $role) {
                $query->select('permission_id')
                    ->from('group_role_permissions')
                    ->where('group_id', $groupId)
                    ->where('role', $role);
            })->get();
        });
    }
    
    /**
     * Assign permissions to a role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissionsToRole(int $groupId, string $role, array $permissionIds): bool
    {
        try {
            // Begin transaction
            DB::beginTransaction();
            
            // Remove existing permissions for this role
            GroupRolePermission::where('group_id', $groupId)
                ->where('role', $role)
                ->delete();
            
            // Add new permissions
            $rolePermissions = [];
            foreach ($permissionIds as $permissionId) {
                $rolePermissions[] = [
                    'group_id' => $groupId,
                    'role' => $role,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            GroupRolePermission::insert($rolePermissions);
            
            // Commit transaction
            DB::commit();
            
            // Clear cache
            $this->clearRolePermissionCache($groupId, $role);
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return false;
        }
    }
    
    /**
     * Remove a specific permission from a role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param int $permissionId
     * @return bool
     */
    public function removePermissionFromRole(int $groupId, string $role, int $permissionId): bool
    {
        $result = GroupRolePermission::where('group_id', $groupId)
            ->where('role', $role)
            ->where('permission_id', $permissionId)
            ->delete();
            
        // Clear cache
        if ($result) {
            $this->clearRolePermissionCache($groupId, $role);
        }
        
        return $result > 0;
    }
    
    /**
     * Check if a role has a specific permission in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param string $permissionSlug
     * @return bool
     */
    public function roleHasPermission(int $groupId, string $role, string $permissionSlug): bool
    {
        // Leaders always have all permissions
        if ($role === 'leader') {
            return true;
        }
        
        $cacheKey = "group_{$groupId}_role_{$role}_has_permission_{$permissionSlug}";
        
        return Cache::remember($cacheKey, 60 * 5, function () use ($groupId, $role, $permissionSlug) {
            return GroupRolePermission::where('group_id', $groupId)
                ->where('role', $role)
                ->whereHas('permission', function ($query) use ($permissionSlug) {
                    $query->where('slug', $permissionSlug);
                })
                ->exists();
        });
    }
    
    /**
     * Initialize default role permissions for a new group.
     *
     * @param int $groupId
     * @return bool
     */
    public function initializeDefaultRolePermissions(int $groupId): bool
    {
        try {
            // Get the group
            $group = Group::find($groupId);
            
            if (!$group) {
                return false;
            }
            
            return $group->initializeDefaultRolePermissions();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Clear role permission cache.
     *
     * @param int $groupId
     * @param string $role
     * @return void
     */
    private function clearRolePermissionCache(int $groupId, string $role): void
    {
        Cache::forget("group_{$groupId}_role_permissions");
        Cache::forget("group_{$groupId}_role_{$role}_permissions");
        
        // Also clear any specific permission checks for this role
        $permissionSlugs = GroupPermission::pluck('slug')->toArray();
        foreach ($permissionSlugs as $slug) {
            Cache::forget("group_{$groupId}_role_{$role}_has_permission_{$slug}");
        }
    }
}
