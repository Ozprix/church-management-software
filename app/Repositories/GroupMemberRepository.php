<?php

namespace App\Repositories;

use App\Models\GroupMember;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;

class GroupMemberRepository implements GroupMemberRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all group members
     *
     * @return Collection
     */
    public function getAllGroupMembers(): Collection
    {
        $cacheKey = CacheService::generateKey('group_member', 'all');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return GroupMember::with(['group', 'member'])->get();
        });
    }

    /**
     * Get group member by ID
     *
     * @param int $id
     * @return GroupMember|null
     */
    public function getGroupMemberById(int $id): ?GroupMember
    {
        $cacheKey = CacheService::generateKey('group_member', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return GroupMember::with(['group', 'member'])->find($id);
        });
    }

    /**
     * Get group members by group ID
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembersByGroupId(int $groupId): Collection
    {
        $cacheKey = CacheService::generateKey('group_member', 'group_id', ['group_id' => $groupId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($groupId) {
            return GroupMember::with(['member'])
                ->where('group_id', $groupId)
                ->get();
        });
    }

    /**
     * Create a new group member
     *
     * @param array $data
     * @return GroupMember
     */
    public function createGroupMember(array $data): GroupMember
    {
        $groupMember = GroupMember::create($data);
        
        $this->clearGroupMemberCache();
        
        return $groupMember;
    }

    /**
     * Update an existing group member
     *
     * @param int $id
     * @param array $data
     * @return GroupMember
     */
    public function updateGroupMember(int $id, array $data): GroupMember
    {
        $groupMember = GroupMember::findOrFail($id);
        $groupMember->update($data);
        
        $this->clearGroupMemberCache();
        
        return $groupMember;
    }

    /**
     * Delete a group member
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroupMember(int $id): bool
    {
        $groupMember = GroupMember::findOrFail($id);
        $result = $groupMember->delete();
        
        $this->clearGroupMemberCache();
        
        return $result;
    }

    /**
     * Update a member's role in a group
     *
     * @param int $groupId
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function updateMemberRole(int $groupId, int $memberId, array $data): bool
    {
        $groupMember = GroupMember::where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->firstOrFail();
        
        $groupMember->role = $data['role'] ?? $groupMember->role;
        
        if (isset($data['custom_role_title'])) {
            $groupMember->custom_role_title = $data['custom_role_title'];
        }
        
        if (isset($data['permissions'])) {
            $groupMember->permissions = $data['permissions'];
        }
        
        $result = $groupMember->save();
        
        $this->clearGroupMemberCache();
        
        return $result;
    }

    /**
     * Get group members with custom role titles
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembersWithCustomRoles(int $groupId): Collection
    {
        $cacheKey = CacheService::generateKey('group_member', 'custom_roles', ['group_id' => $groupId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($groupId) {
            return GroupMember::with(['member'])
                ->where('group_id', $groupId)
                ->whereNotNull('custom_role_title')
                ->get();
        });
    }

    /**
     * Get group members with specific permissions
     *
     * @param int $groupId
     * @param array $permissions
     * @return Collection
     */
    public function getGroupMembersWithPermissions(int $groupId, array $permissions): Collection
    {
        $cacheKey = CacheService::generateKey('group_member', 'permissions', [
            'group_id' => $groupId,
            'permissions' => implode(',', $permissions)
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($groupId, $permissions) {
            return GroupMember::with(['member'])
                ->where('group_id', $groupId)
                ->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhereJsonContains('permissions', $permission);
                    }
                })
                ->get();
        });
    }

    /**
     * Clear all group member-related caches
     *
     * @return void
     */
    private function clearGroupMemberCache(): void
    {
        CacheService::forgetByPattern('group_member_');
    }
}
