<?php

namespace App\Repositories;

use App\Models\GroupMemberEngagement;
use App\Repositories\Interfaces\GroupMemberEngagementRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupMemberEngagementRepository implements GroupMemberEngagementRepositoryInterface
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
    public function getMemberEngagement(int $groupId, int $memberId, string $startDate, string $endDate): Collection
    {
        $cacheKey = "group_{$groupId}_member_{$memberId}_engagement_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $memberId, $startDate, $endDate) {
            return GroupMemberEngagement::where('group_id', $groupId)
                ->where('member_id', $memberId)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->get();
        });
    }
    
    /**
     * Get the latest engagement record for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return GroupMemberEngagement|null
     */
    public function getLatestMemberEngagement(int $groupId, int $memberId): ?GroupMemberEngagement
    {
        $cacheKey = "group_{$groupId}_member_{$memberId}_latest_engagement";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $memberId) {
            return GroupMemberEngagement::where('group_id', $groupId)
                ->where('member_id', $memberId)
                ->orderBy('date', 'desc')
                ->first();
        });
    }
    
    /**
     * Create or update engagement record for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $date
     * @param array $data
     * @return GroupMemberEngagement
     */
    public function updateMemberEngagement(int $groupId, int $memberId, string $date, array $data): GroupMemberEngagement
    {
        // Calculate engagement score if not provided
        if (!isset($data['engagement_score'])) {
            $metrics = [
                'attendance_count' => $data['attendance_count'] ?? 0,
                'event_attendance_count' => $data['event_attendance_count'] ?? 0,
                'communication_count' => $data['communication_count'] ?? 0,
                'leadership_activities' => $data['leadership_activities'] ?? 0,
            ];
            
            $data['engagement_score'] = GroupMemberEngagement::calculateEngagementScore($metrics);
        }
        
        $engagement = GroupMemberEngagement::updateOrCreate(
            ['group_id' => $groupId, 'member_id' => $memberId, 'date' => $date],
            $data
        );
        
        // Clear cache
        $this->clearMemberEngagementCache($groupId, $memberId, $date);
        
        return $engagement;
    }
    
    /**
     * Get the most engaged members in a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return Collection
     */
    public function getMostEngagedMembers(int $groupId, string $startDate, string $endDate, int $limit = 10): Collection
    {
        $cacheKey = "group_{$groupId}_most_engaged_members_{$startDate}_{$endDate}_{$limit}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate, $limit) {
            return GroupMemberEngagement::with('member')
                ->select(
                    'group_id',
                    'member_id',
                    DB::raw('AVG(engagement_score) as avg_engagement_score'),
                    DB::raw('SUM(attendance_count) as total_attendance'),
                    DB::raw('SUM(event_attendance_count) as total_event_attendance'),
                    DB::raw('SUM(communication_count) as total_communication'),
                    DB::raw('SUM(leadership_activities) as total_leadership')
                )
                ->where('group_id', $groupId)
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('group_id', 'member_id')
                ->orderBy('avg_engagement_score', 'desc')
                ->limit($limit)
                ->get();
        });
    }
    
    /**
     * Get the least engaged members in a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return Collection
     */
    public function getLeastEngagedMembers(int $groupId, string $startDate, string $endDate, int $limit = 10): Collection
    {
        $cacheKey = "group_{$groupId}_least_engaged_members_{$startDate}_{$endDate}_{$limit}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $startDate, $endDate, $limit) {
            return GroupMemberEngagement::with('member')
                ->select(
                    'group_id',
                    'member_id',
                    DB::raw('AVG(engagement_score) as avg_engagement_score'),
                    DB::raw('SUM(attendance_count) as total_attendance'),
                    DB::raw('SUM(event_attendance_count) as total_event_attendance'),
                    DB::raw('SUM(communication_count) as total_communication'),
                    DB::raw('SUM(leadership_activities) as total_leadership')
                )
                ->where('group_id', $groupId)
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('group_id', 'member_id')
                ->orderBy('avg_engagement_score', 'asc')
                ->limit($limit)
                ->get();
        });
    }
    
    /**
     * Get engagement trend for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMemberEngagementTrend(int $groupId, int $memberId, string $startDate, string $endDate): array
    {
        $cacheKey = "group_{$groupId}_member_{$memberId}_engagement_trend_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60 * 15, function () use ($groupId, $memberId, $startDate, $endDate) {
            return GroupMemberEngagement::getMemberEngagementTrend($groupId, $memberId, $startDate, $endDate);
        });
    }
    
    /**
     * Calculate and update engagement scores for all members in a group.
     *
     * @param int $groupId
     * @param string $date
     * @return bool
     */
    public function calculateGroupEngagementScores(int $groupId, string $date): bool
    {
        try {
            // Begin transaction
            DB::beginTransaction();
            
            // Get all active members in the group
            $members = DB::table('group_members')
                ->where('group_id', $groupId)
                ->where('is_active', true)
                ->whereNull('exit_date')
                ->pluck('member_id');
            
            $dateObj = Carbon::parse($date);
            $startDate = $dateObj->copy()->subDays(30)->format('Y-m-d');
            $endDate = $dateObj->format('Y-m-d');
            
            foreach ($members as $memberId) {
                // Get attendance data
                $attendanceCount = DB::table('group_attendance_details')
                    ->join('group_attendances', 'group_attendance_details.group_attendance_id', '=', 'group_attendances.id')
                    ->where('group_attendances.group_id', $groupId)
                    ->where('group_attendance_details.member_id', $memberId)
                    ->where('group_attendance_details.status', 'present')
                    ->whereBetween('group_attendances.attendance_date', [$startDate, $endDate])
                    ->count();
                
                // Get event attendance data
                $eventAttendanceCount = DB::table('event_registrations')
                    ->join('group_events', 'event_registrations.group_event_id', '=', 'group_events.id')
                    ->where('group_events.group_id', $groupId)
                    ->where('event_registrations.member_id', $memberId)
                    ->whereIn('event_registrations.status', ['confirmed', 'attended'])
                    ->whereBetween('group_events.event_date', [$startDate, $endDate])
                    ->count();
                
                // Get communication data (placeholder - this would be implemented based on the communication system)
                $communicationCount = 0;
                
                // Get leadership activities (placeholder - this would be implemented based on leadership tracking)
                $leadershipActivities = DB::table('group_members')
                    ->where('group_id', $groupId)
                    ->where('member_id', $memberId)
                    ->whereIn('role', ['leader', 'assistant_leader', 'secretary', 'treasurer'])
                    ->count() > 0 ? 1 : 0;
                
                // Prepare data
                $data = [
                    'attendance_count' => $attendanceCount,
                    'event_attendance_count' => $eventAttendanceCount,
                    'communication_count' => $communicationCount,
                    'leadership_activities' => $leadershipActivities,
                ];
                
                // Calculate engagement score
                $data['engagement_score'] = GroupMemberEngagement::calculateEngagementScore($data);
                
                // Create or update engagement record
                $this->updateMemberEngagement($groupId, $memberId, $date, $data);
            }
            
            // Commit transaction
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return false;
        }
    }
    
    /**
     * Clear member engagement cache.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $date
     * @return void
     */
    private function clearMemberEngagementCache(int $groupId, int $memberId, string $date): void
    {
        Cache::forget("group_{$groupId}_member_{$memberId}_latest_engagement");
        
        $dateObj = Carbon::parse($date);
        $startOfMonth = $dateObj->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $dateObj->copy()->endOfMonth()->format('Y-m-d');
        
        Cache::forget("group_{$groupId}_member_{$memberId}_engagement_{$startOfMonth}_{$endOfMonth}");
        Cache::forget("group_{$groupId}_most_engaged_members_{$startOfMonth}_{$endOfMonth}_10");
        Cache::forget("group_{$groupId}_least_engaged_members_{$startOfMonth}_{$endOfMonth}_10");
    }
}
