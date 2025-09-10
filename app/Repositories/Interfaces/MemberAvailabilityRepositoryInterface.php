<?php

namespace App\Repositories\Interfaces;

use App\Models\MemberAvailability;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MemberAvailabilityRepositoryInterface
{
    /**
     * Get all availability records.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllAvailability(array $filters = []): Collection;

    /**
     * Get paginated availability records.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedAvailability(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get availability record by ID.
     *
     * @param int $id
     * @return MemberAvailability|null
     */
    public function getAvailabilityById(int $id): ?MemberAvailability;

    /**
     * Create a new availability record.
     *
     * @param array $data
     * @return MemberAvailability
     */
    public function createAvailability(array $data): MemberAvailability;

    /**
     * Update an availability record.
     *
     * @param int $id
     * @param array $data
     * @return MemberAvailability
     */
    public function updateAvailability(int $id, array $data): MemberAvailability;

    /**
     * Delete an availability record.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAvailability(int $id): bool;

    /**
     * Get availability records for a specific member.
     *
     * @param int $memberId
     * @param bool $currentOnly
     * @return Collection
     */
    public function getAvailabilityForMember(int $memberId, bool $currentOnly = true): Collection;

    /**
     * Get members available at a specific day and time.
     *
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersAvailableAt(string $day, string $startTime, string $endTime, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get availability summary for a member.
     *
     * @param int $memberId
     * @return array
     */
    public function getAvailabilitySummary(int $memberId): array;

    /**
     * Check if a member is available at a specific day and time.
     *
     * @param int $memberId
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isMemberAvailableAt(int $memberId, string $day, string $startTime, string $endTime): bool;

    /**
     * Find members available for a specific event.
     *
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @param array $skillIds
     * @param array $spiritualGiftIds
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findAvailableMembersForEvent(string $day, string $startTime, string $endTime, array $skillIds = [], array $spiritualGiftIds = [], int $perPage = 15): LengthAwarePaginator;
}
