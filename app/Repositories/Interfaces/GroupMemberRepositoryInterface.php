<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupMember;
use Illuminate\Database\Eloquent\Collection;

interface GroupMemberRepositoryInterface
{
    /**
     * Get all group members
     *
     * @return Collection
     */
    public function getAllGroupMembers(): Collection;

    /**
     * Get group member by ID
     *
     * @param int $id
     * @return GroupMember|null
     */
    public function getGroupMemberById(int $id): ?GroupMember;

    /**
     * Get group members by group ID
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembersByGroupId(int $groupId): Collection;

    /**
     * Create a new group member
     *
     * @param array $data
     * @return GroupMember
     */
    public function createGroupMember(array $data): GroupMember;

    /**
     * Update an existing group member
     *
     * @param int $id
     * @param array $data
     * @return GroupMember
     */
    public function updateGroupMember(int $id, array $data): GroupMember;

    /**
     * Delete a group member
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroupMember(int $id): bool;

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
     * Get group members with custom role titles
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembersWithCustomRoles(int $groupId): Collection;

    /**
     * Get group members with specific permissions
     *
     * @param int $groupId
     * @param array $permissions
     * @return Collection
     */
    public function getGroupMembersWithPermissions(int $groupId, array $permissions): Collection;
}
