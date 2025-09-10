<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Services\Interfaces\GroupServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupService implements GroupServiceInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * GroupService constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * Get all groups
     *
     * @return Collection
     */
    public function getAllGroups(): Collection
    {
        return $this->groupRepository->getAllGroups();
    }

    /**
     * Get paginated groups
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedGroups(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->groupRepository->getPaginatedGroups($perPage, $filters);
    }

    /**
     * Get group by ID
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroupById(int $id): ?Group
    {
        return $this->groupRepository->getGroupById($id);
    }

    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        return $this->groupRepository->createGroup($data);
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
        return $this->groupRepository->updateGroup($id, $data);
    }

    /**
     * Delete a group
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroup(int $id): bool
    {
        return $this->groupRepository->deleteGroup($id);
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
        return $this->groupRepository->addMemberToGroup($groupId, $memberId, $data);
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
        return $this->groupRepository->removeMemberFromGroup($groupId, $memberId);
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
        return $this->groupRepository->updateMemberRole($groupId, $memberId, $data);
    }

    /**
     * Get all members in a group
     *
     * @param int $groupId
     * @return Collection
     */
    public function getGroupMembers(int $groupId): Collection
    {
        return $this->groupRepository->getGroupMembers($groupId);
    }

    /**
     * Get groups statistics
     *
     * @return array
     */
    public function getGroupsStatistics(): array
    {
        return $this->groupRepository->getGroupsStatistics();
    }
}
