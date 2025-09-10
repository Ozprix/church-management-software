<?php

namespace App\Repositories\Interfaces;

use App\Models\SpiritualGift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SpiritualGiftRepositoryInterface
{
    /**
     * Get all spiritual gifts.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllSpiritualGifts(array $filters = []): Collection;

    /**
     * Get paginated spiritual gifts.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedSpiritualGifts(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get spiritual gift by ID.
     *
     * @param int $id
     * @return SpiritualGift|null
     */
    public function getSpiritualGiftById(int $id): ?SpiritualGift;

    /**
     * Create a new spiritual gift.
     *
     * @param array $data
     * @return SpiritualGift
     */
    public function createSpiritualGift(array $data): SpiritualGift;

    /**
     * Update a spiritual gift.
     *
     * @param int $id
     * @param array $data
     * @return SpiritualGift
     */
    public function updateSpiritualGift(int $id, array $data): SpiritualGift;

    /**
     * Delete a spiritual gift.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSpiritualGift(int $id): bool;

    /**
     * Get spiritual gifts for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSpiritualGiftsForMember(int $memberId): Collection;

    /**
     * Get members with a specific spiritual gift.
     *
     * @param int $spiritualGiftId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithSpiritualGift(int $spiritualGiftId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get spiritual gift distribution statistics.
     *
     * @return array
     */
    public function getSpiritualGiftDistribution(): array;

    /**
     * Assign a spiritual gift to a member.
     *
     * @param int $memberId
     * @param int $spiritualGiftId
     * @param array $data
     * @return bool
     */
    public function assignSpiritualGiftToMember(int $memberId, int $spiritualGiftId, array $data = []): bool;

    /**
     * Remove a spiritual gift from a member.
     *
     * @param int $memberId
     * @param int $spiritualGiftId
     * @return bool
     */
    public function removeSpiritualGiftFromMember(int $memberId, int $spiritualGiftId): bool;
}
