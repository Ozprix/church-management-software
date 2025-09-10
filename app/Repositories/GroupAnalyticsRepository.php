<?php

namespace App\Repositories;

use App\Models\GroupAnalytics;
use App\Repositories\Interfaces\GroupAnalyticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupAnalyticsRepository implements GroupAnalyticsRepositoryInterface
{
    /**
     * Get analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getGroupAnalytics(int $groupId, string $startDate, string $endDate): Collection
    {
        $cacheKey = "group_{$groupId}_analytics_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate) {
            return GroupAnalytics::where('group_id', $groupId)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->get();
        });
    }
    
    /**
     * Get the latest analytics for a group.
     *
     * @param int $groupId
     * @return GroupAnalytics|null
     */
    public function getLatestGroupAnalytics(int $groupId): ?GroupAnalytics
    {
        $cacheKey = "group_{$groupId}_latest_analytics";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId) {
            return GroupAnalytics::where('group_id', $groupId)
                ->orderBy('date', 'desc')
                ->first();
        });
    }
    
    /**
     * Create or update analytics for a group.
     *
     * @param int $groupId
     * @param string $date
     * @param array $data
     * @return GroupAnalytics
     */
    public function updateGroupAnalytics(int $groupId, string $date, array $data): GroupAnalytics
    {
        $analytics = GroupAnalytics::updateOrCreate(
            ['group_id' => $groupId, 'date' => $date],
            $data
        );
        
        // Clear cache
        Cache::forget("group_{$groupId}_latest_analytics");
        Cache::forget("group_{$groupId}_analytics_" . Carbon::parse($date)->startOfMonth()->format('Y-m-d') . "_" . Carbon::parse($date)->endOfMonth()->format('Y-m-d'));
        
        return $analytics;
    }
    
    /**
     * Get attendance trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceTrend(int $groupId, string $startDate, string $endDate): array
    {
        $cacheKey = "group_{$groupId}_attendance_trend_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate) {
            return GroupAnalytics::getAttendanceTrend($groupId, $startDate, $endDate);
        });
    }
    
    /**
     * Get growth trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGrowthTrend(int $groupId, string $startDate, string $endDate): array
    {
        $cacheKey = "group_{$groupId}_growth_trend_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate) {
            return GroupAnalytics::getGrowthTrend($groupId, $startDate, $endDate);
        });
    }
    
    /**
     * Get engagement trend for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getEngagementTrend(int $groupId, string $startDate, string $endDate): array
    {
        $cacheKey = "group_{$groupId}_engagement_trend_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate) {
            return GroupAnalytics::getEngagementTrend($groupId, $startDate, $endDate);
        });
    }
    
    /**
     * Get analytics comparison for multiple groups.
     *
     * @param array $groupIds
     * @param string $metric
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGroupsComparison(array $groupIds, string $metric, string $startDate, string $endDate): array
    {
        $cacheKey = "groups_comparison_{$metric}_" . implode('_', $groupIds) . "_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupIds, $metric, $startDate, $endDate) {
            $validMetrics = [
                'attendance_rate', 'growth_rate', 'engagement_score', 
                'total_members', 'active_members', 'new_members'
            ];
            
            if (!in_array($metric, $validMetrics)) {
                $metric = 'attendance_rate';
            }
            
            $results = [];
            
            // Get the latest analytics for each group within the date range
            $analytics = GroupAnalytics::whereIn('group_id', $groupIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get()
                ->groupBy('group_id');
            
            foreach ($groupIds as $groupId) {
                if (isset($analytics[$groupId]) && $analytics[$groupId]->count() > 0) {
                    $groupAnalytics = $analytics[$groupId];
                    
                    // Calculate average for the metric over the period
                    $avgMetric = $groupAnalytics->avg($metric);
                    
                    // Get group name
                    $groupName = DB::table('groups')->where('id', $groupId)->value('name') ?? "Group {$groupId}";
                    
                    $results[] = [
                        'group_id' => $groupId,
                        'group_name' => $groupName,
                        'metric' => $metric,
                        'value' => round($avgMetric, 2),
                    ];
                }
            }
            
            // Sort by metric value (descending)
            usort($results, function ($a, $b) {
                return $b['value'] <=> $a['value'];
            });
            
            return $results;
        });
    }
}
