<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\Member;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupRepository implements GroupRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all groups with caching
     *
     * @return Collection
     */
    public function getAllGroups(): Collection
    {
        $cacheKey = CacheService::generateKey('group', 'all');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return Group::with(['leader'])->get();
        });
    }

    /**
     * Get paginated groups with caching
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedGroups(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('group', 'paginated', [
            'per_page' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = Group::with(['leader']);
            
            // Apply filters
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }
            
            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'name';
            $sortDir = $filters['sort_dir'] ?? 'asc';
            $query->orderBy($sortBy, $sortDir);
            
            return $query->paginate($perPage);
        });
    }

    /**
     * Get group by ID with caching
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroupById(int $id): ?Group
    {
        $cacheKey = CacheService::generateKey('group', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return Group::with(['leader', 'members.member'])->find($id);
        });
    }

    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        $group = Group::create($data);
        
        $this->clearGroupCache();
        
        return $group;
    }

    /**
     * Update an existing group
     *
     * @param int $id
     * @param array $data
     * @return Group
     */
    public function updateGroup(int $id, array $data): Group
    {
        $group = Group::findOrFail($id);
        $group->update($data);
        
        $this->clearGroupCache();
        
        return $group;
    }

    /**
     * Delete a group
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroup(int $id): bool
    {
        $group = Group::findOrFail($id);
        $result = $group->delete();
        
        $this->clearGroupCache();
        
        return $result;
    }

    /**
     * Add a member to a group
     *
     * @param int $groupId
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function addMemberToGroup(int $groupId, int $memberId, array $data = []): bool
    {
        $group = Group::findOrFail($groupId);
        $member = Member::findOrFail($memberId);
        
        $pivotData = [
            'role' => $data['role'] ?? 'member',
            'join_date' => $data['join_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ];
        
        $group->members()->attach($memberId, $pivotData);
        
        $this->clearGroupCache();
        
        return true;
    }

    /**
     * Remove a member from a group
     *
     * @param int $groupId
     * @param int $memberId
     * @return bool
     */
    public function removeMemberFromGroup(int $groupId, int $memberId): bool
    {
        $group = Group::findOrFail($groupId);
        $group->members()->detach($memberId);
        
        $this->clearGroupCache();
        
        return true;
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
        $group = Group::findOrFail($groupId);
        
        $pivotData = [
            'role' => $data['role'] ?? 'member',
            'notes' => $data['notes'] ?? null,
        ];
        
        if (isset($data['exit_date'])) {
            $pivotData['exit_date'] = $data['exit_date'];
            $pivotData['is_active'] = false;
        }
        
        if (isset($data['is_active'])) {
            $pivotData['is_active'] = $data['is_active'];
        }
        
        $group->members()->updateExistingPivot($memberId, $pivotData);
        
        $this->clearGroupCache();
        
        return true;
    }

    /**
     * Get all members in a group
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembers(int $groupId): Collection
    {
        $cacheKey = CacheService::generateKey('group', 'members', ['group_id' => $groupId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($groupId) {
            $group = Group::findOrFail($groupId);
            return $group->members()->with('member')->get();
        });
    }

    /**
     * Get groups statistics
     *
     * @return array
     */
    public function getGroupsStatistics(): array
    {
        $cacheKey = CacheService::generateKey('group', 'stats');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return [
                'total' => Group::count(),
                'active' => Group::where('is_active', true)->count(),
                'inactive' => Group::where('is_active', false)->count(),
                'by_type' => [
                    'ministry' => Group::where('type', 'ministry')->count(),
                    'committee' => Group::where('type', 'committee')->count(),
                    'small_group' => Group::where('type', 'small_group')->count(),
                    'other' => Group::where('type', 'other')->count(),
                ],
                'recent' => Group::orderBy('created_at', 'desc')->limit(5)->get(),
            ];
        });
    }

    /**
     * Clear all group-related caches
     *
     * @return void
     */
    private function clearGroupCache(): void
    {
        CacheService::forgetByPattern('group_');
    }
}
