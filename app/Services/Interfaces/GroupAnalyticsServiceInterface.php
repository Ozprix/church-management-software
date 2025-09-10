<?php

namespace App\Services\Interfaces;

use App\Models\GroupAnalytics;
use Illuminate\Database\Eloquent\Collection;

interface GroupAnalyticsServiceInterface
{
    /**
     * Get analytics dashboard data for a group.
     *
     * @param int $groupId
     * @param string $period
     * @return array
     */
    public function getAnalyticsDashboard(int $groupId, string $period = '3months'): array;
    
    /**
     * Generate analytics for a group.
     *
     * @param int $groupId
     * @param string $date
     * @return GroupAnalytics
     */
    public function generateGroupAnalytics(int $groupId, string $date): GroupAnalytics;
    
    /**
     * Get attendance analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceAnalytics(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get growth analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGrowthAnalytics(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get engagement analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getEngagementAnalytics(int $groupId, string $startDate, string $endDate): array;
    
    /**
     * Get member engagement analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMemberEngagementAnalytics(int $groupId, string $startDate, string $endDate): array;
    
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
    
    /**
     * Schedule analytics generation for all groups.
     *
     * @return int Number of groups processed
     */
    public function scheduleAnalyticsGeneration(): int;
}
