<?php

namespace App\Repositories\Interfaces;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    /**
     * Get all groups with caching
     *
     * @return Collection
     */
    public function getAllGroups(): Collection;

    /**
     * Get paginated groups with caching
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedGroups(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get group by ID with caching
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroupById(int $id): ?Group;

    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group;

    /**
     * Update an existing group
     *
     * @param int $id
     * @param array $data
     * @return Group
     */
    public function updateGroup(int $id, array $data): Group;

    /**
     * Delete a group
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroup(int $id): bool;

    /**
     * Add a member to a group
     *
     * @param int $groupId
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function addMemberToGroup(int $groupId, int $memberId, array $data = []): bool;

    /**
     * Remove a member from a group
     *
     * @param int $groupId
     * @param int $memberId
     * @return bool
     */
    public function removeMemberFromGroup(int $groupId, int $memberId): bool;

    /**
     * Update a member's role in a group
     *
     * @param int $groupId
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function updateMemberRole(int $groupId, int $memberId, array $data): bool;

    /**
     * Get all members in a group
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembers(int $groupId): Collection;

    /**
     * Get groups statistics
     *
     * @return array
     */
    public function getGroupsStatistics(): array;
}
