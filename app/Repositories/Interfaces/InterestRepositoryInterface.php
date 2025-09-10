<?php

namespace App\Repositories\Interfaces;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InterestRepositoryInterface
{
    /**
     * Get all interests.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllInterests(array $filters = []): Collection;

    /**
     * Get paginated interests.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedInterests(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get interest by ID.
     *
     * @param int $id
     * @return Interest|null
     */
    public function getInterestById(int $id): ?Interest;

    /**
     * Create a new interest.
     *
     * @param array $data
     * @return Interest
     */
    public function createInterest(array $data): Interest;

    /**
     * Update an interest.
     *
     * @param int $id
     * @param array $data
     * @return Interest
     */
    public function updateInterest(int $id, array $data): Interest;

    /**
     * Delete an interest.
     *
     * @param int $id
     * @return bool
     */
    public function deleteInterest(int $id): bool;

    /**
     * Get all interest categories.
     *
     * @return array
     */
    public function getAllCategories(): array;

    /**
     * Get interests by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getInterestsByCategory(string $category): Collection;

    /**
     * Get interests for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getInterestsForMember(int $memberId): Collection;

    /**
     * Get members with a specific interest.
     *
     * @param int $interestId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithInterest(int $interestId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
