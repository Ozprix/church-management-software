<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupAnalytics;
use Illuminate\Database\Eloquent\Collection;

interface GroupAnalyticsRepositoryInterface
{
    /**
     * Get analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getGroupAnalytics(int $groupId, string $startDate, string $endDate): Collection;
    
    /**
     * Get the latest analytics for a group.
     *
     * @param int $groupId
     * @return GroupAnalytics|null
     */
    public function getLatestGroupAnalytics(int $groupId): ?GroupAnalytics;
    
    /**
     * Create or update analytics for a group.
     *
     * @param int $groupId
     * @param string $date
     * @param array $data
     * @return GroupAnalytics
     */
    public function updateGroupAnalytics(int $groupId, string $date, array $data): GroupAnalytics;
    
    /**
     * Get attendance trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceTrend(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get growth trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGrowthTrend(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get engagement trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getEngagementTrend(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get analytics comparison for multiple groups.
     *
     * @param array $groupIds
     * @param string $metric
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGroupsComparison(array $groupIds, string $metric, string $startDate, string $endDate): array;
}
