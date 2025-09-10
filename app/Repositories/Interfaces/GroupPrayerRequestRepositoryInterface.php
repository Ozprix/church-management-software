<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupPrayerRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupPrayerRequestRepositoryInterface
{
    /**
     * Get all prayer requests for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupPrayerRequests(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a prayer request by ID.
     *
     * @param int $requestId
     * @return GroupPrayerRequest|null
     */
    public function getPrayerRequestById(int $requestId): ?GroupPrayerRequest;

    /**
     * Create a new prayer request.
     *
     * @param array $data
     * @return GroupPrayerRequest
     */
    public function createPrayerRequest(array $data): GroupPrayerRequest;

    /**
     * Update a prayer request.
     *
     * @param int $requestId
     * @param array $data
     * @return bool
     */
    public function updatePrayerRequest(int $requestId, array $data): bool;

    /**
     * Delete a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function deletePrayerRequest(int $requestId): bool;

    /**
     * Mark a prayer request as answered.
     *
     * @param int $requestId
     * @param string|null $answerDescription
     * @return bool
     */
    public function markAsAnswered(int $requestId, ?string $answerDescription = null): bool;

    /**
     * Archive a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function archivePrayerRequest(int $requestId): bool;

    /**
     * Reactivate a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function reactivatePrayerRequest(int $requestId): bool;

    /**
     * Add a prayer response to a prayer request.
     *
     * @param int $requestId
     * @param int $memberId
     * @param string $response
     * @param bool $isPraying
     * @return bool
     */
    public function addPrayerResponse(int $requestId, int $memberId, string $response, bool $isPraying = true): bool;

    /**
     * Toggle the praying status for a member on a prayer request.
     *
     * @param int $requestId
     * @param int $memberId
     * @return bool
     */
    public function togglePraying(int $requestId, int $memberId): bool;

    /**
     * Get prayer requests statistics for a group.
     *
     * @param int $groupId
     * @return array
     */
    public function getPrayerRequestsStats(int $groupId): array;
}
