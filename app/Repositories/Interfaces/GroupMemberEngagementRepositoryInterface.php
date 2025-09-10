<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupMemberEngagement;
use Illuminate\Database\Eloquent\Collection;

interface GroupMemberEngagementRepositoryInterface
{
    /**
     * Get engagement records for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getMemberEngagement(int $groupId, int $memberId, string $startDate, string $endDate): Collection;
    
    /**
     * Get the latest engagement record for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return GroupMemberEngagement|null
     */
    public function getLatestMemberEngagement(int $groupId, int $memberId): ?GroupMemberEngagement;
    
    /**
     * Create or update engagement record for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $date
     * @param array $data
     * @return GroupMemberEngagement
     */
    public function updateMemberEngagement(int $groupId, int $memberId, string $date, array $data): GroupMemberEngagement;
    
    /**
     * Get the most engaged members in a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return Collection
     */
    public function getMostEngagedMembers(int $groupId, string $startDate, string $endDate, int $limit = 10): Collection;
    
    /**
     * Get the least engaged members in a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return Collection
     */
    public function getLeastEngagedMembers(int $groupId, string $startDate, string $endDate, int $limit = 10): Collection;
    
    /**
     * Get engagement trend for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMemberEngagementTrend(int $groupId, int $memberId, string $startDate, string $endDate): array;
    
    /**
     * Calculate and update engagement scores for all members in a group.
     *
     * @param int $groupId
     * @param string $date
     * @return bool
     */
    public function calculateGroupEngagementScores(int $groupId, string $date): bool;
}
