<?php

namespace App\Services;

use App\Models\GroupAnalytics;
use App\Repositories\Interfaces\GroupAnalyticsRepositoryInterface;
use App\Repositories\Interfaces\GroupAttendanceRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberEngagementRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Services\Interfaces\GroupAnalyticsServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GroupAnalyticsService implements GroupAnalyticsServiceInterface
{
    /**
     * @var GroupAnalyticsRepositoryInterface
     */
    protected $groupAnalyticsRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupMemberRepositoryInterface
     */
    protected $groupMemberRepository;

    /**
     * @var GroupAttendanceRepositoryInterface
     */
    protected $groupAttendanceRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * @var GroupMemberEngagementRepositoryInterface
     */
    protected $groupMemberEngagementRepository;

    /**
     * GroupAnalyticsService constructor.
     *
     * @param GroupAnalyticsRepositoryInterface $groupAnalyticsRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupMemberRepositoryInterface $groupMemberRepository
     * @param GroupAttendanceRepositoryInterface $groupAttendanceRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     * @param GroupMemberEngagementRepositoryInterface $groupMemberEngagementRepository
     */
    public function __construct(
        GroupAnalyticsRepositoryInterface $groupAnalyticsRepository,
        GroupRepositoryInterface $groupRepository,
        GroupMemberRepositoryInterface $groupMemberRepository,
        GroupAttendanceRepositoryInterface $groupAttendanceRepository,
        GroupEventRepositoryInterface $groupEventRepository,
        GroupMemberEngagementRepositoryInterface $groupMemberEngagementRepository
    ) {
        $this->groupAnalyticsRepository = $groupAnalyticsRepository;
        $this->groupRepository = $groupRepository;
        $this->groupMemberRepository = $groupMemberRepository;
        $this->groupAttendanceRepository = $groupAttendanceRepository;
        $this->groupEventRepository = $groupEventRepository;
        $this->groupMemberEngagementRepository = $groupMemberEngagementRepository;
    }

    /**
     * Get analytics dashboard data for a group.
     *
     * @param int $groupId
     * @param string $period
     * @return array
     */
    public function getAnalyticsDashboard(int $groupId, string $period = '3months'): array
    {
        // Determine date range based on period
        $endDate = Carbon::now()->format('Y-m-d');
        $startDate = $this->getStartDateFromPeriod($period);
        
        // Get the group
        $group = $this->groupRepository->getGroupById($groupId);
        
        if (!$group) {
            return [
                'status' => 'error',
                'message' => 'Group not found'
            ];
        }
        
        // Get latest analytics
        $latestAnalytics = $this->groupAnalyticsRepository->getLatestGroupAnalytics($groupId);
        
        // Get analytics data
        $attendanceAnalytics = $this->getAttendanceAnalytics($groupId, $startDate, $endDate);
        $growthAnalytics = $this->getGrowthAnalytics($groupId, $startDate, $endDate);
        $engagementAnalytics = $this->getEngagementAnalytics($groupId, $startDate, $endDate);
        $memberEngagementAnalytics = $this->getMemberEngagementAnalytics($groupId, $startDate, $endDate);
        
        // Compile dashboard data
        return [
            'status' => 'success',
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'type' => $group->type,
                'leader' => $group->leader ? [
                    'id' => $group->leader->id,
                    'name' => $group->leader->full_name,
                ] : null,
            ],
            'summary' => $latestAnalytics ? [
                'total_members' => $latestAnalytics->total_members,
                'active_members' => $latestAnalytics->active_members,
                'attendance_rate' => $latestAnalytics->attendance_rate,
                'growth_rate' => $latestAnalytics->growth_rate,
                'engagement_score' => $latestAnalytics->engagement_score,
                'last_updated' => $latestAnalytics->date->format('Y-m-d'),
            ] : null,
            'attendance' => $attendanceAnalytics,
            'growth' => $growthAnalytics,
            'engagement' => $engagementAnalytics,
            'member_engagement' => $memberEngagementAnalytics,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period' => $period,
            ],
        ];
    }
    
    /**
     * Generate analytics for a group.
     *
     * @param int $groupId
     * @param string $date
     * @return GroupAnalytics
     */
    public function generateGroupAnalytics(int $groupId, string $date): GroupAnalytics
    {
        // Get the group
        $group = $this->groupRepository->getGroupById($groupId);
        
        if (!$group) {
            throw new \Exception("Group not found");
        }
        
        $dateObj = Carbon::parse($date);
        $startOfMonth = $dateObj->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $dateObj->copy()->endOfMonth()->format('Y-m-d');
        $previousMonth = $dateObj->copy()->subMonth()->format('Y-m-d');
        
        // Get member counts
        $totalMembers = $this->groupMemberRepository->countGroupMembers($groupId);
        $activeMembers = $this->groupMemberRepository->countActiveGroupMembers($groupId);
        
        // Get new and exited members for the month
        $newMembers = $this->groupMemberRepository->countNewGroupMembers($groupId, $startOfMonth, $endOfMonth);
        $exitedMembers = $this->groupMemberRepository->countExitedGroupMembers($groupId, $startOfMonth, $endOfMonth);
        
        // Get attendance data
        $attendanceRecords = $this->groupAttendanceRepository->getGroupAttendanceByDateRange($groupId, $startOfMonth, $endOfMonth);
        $totalAttendance = $attendanceRecords->sum('total_attendees');
        $totalVisitors = $attendanceRecords->sum('total_visitors');
        $attendanceCount = $attendanceRecords->count();
        
        // Calculate attendance rate
        $attendanceRate = $attendanceCount > 0 && $activeMembers > 0
            ? ($totalAttendance / ($attendanceCount * $activeMembers)) * 100
            : 0;
        
        // Get event data
        $events = $this->groupEventRepository->getGroupEventsByDateRange($groupId, $startOfMonth, $endOfMonth);
        $totalEvents = $events->count();
        
        // Calculate total event attendees (placeholder - this would be implemented based on event attendance tracking)
        $totalEventAttendees = 0;
        
        // Calculate growth rate
        $previousMonthAnalytics = $this->groupAnalyticsRepository->getGroupAnalytics($groupId, $previousMonth, $previousMonth)->first();
        $previousMonthMembers = $previousMonthAnalytics ? $previousMonthAnalytics->total_members : $totalMembers - $newMembers + $exitedMembers;
        $growthRate = $previousMonthMembers > 0
            ? (($totalMembers - $previousMonthMembers) / $previousMonthMembers) * 100
            : 0;
        
        // Calculate engagement score
        // This is a simplified calculation - in a real implementation, this would be more sophisticated
        $engagementScore = 0;
        
        if ($activeMembers > 0) {
            $attendanceScore = min(10, ($attendanceRate / 10));
            $eventScore = min(10, ($totalEvents > 0 ? ($totalEventAttendees / ($totalEvents * $activeMembers)) * 10 : 0));
            $growthScore = min(10, ($growthRate > 0 ? $growthRate / 2 : 0));
            
            $engagementScore = ($attendanceScore * 0.5) + ($eventScore * 0.3) + ($growthScore * 0.2);
        }
        
        // Prepare data
        $data = [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'new_members' => $newMembers,
            'exited_members' => $exitedMembers,
            'total_attendance' => $totalAttendance,
            'total_visitors' => $totalVisitors,
            'total_events' => $totalEvents,
            'total_event_attendees' => $totalEventAttendees,
            'attendance_rate' => round($attendanceRate, 2),
            'growth_rate' => round($growthRate, 2),
            'engagement_score' => round($engagementScore, 2),
            'additional_metrics' => [
                'attendance_count' => $attendanceCount,
                'average_attendance' => $attendanceCount > 0 ? round($totalAttendance / $attendanceCount, 2) : 0,
                'average_visitors' => $attendanceCount > 0 ? round($totalVisitors / $attendanceCount, 2) : 0,
            ],
        ];
        
        // Update member engagement scores
        $this->groupMemberEngagementRepository->calculateGroupEngagementScores($groupId, $date);
        
        // Create or update analytics record
        return $this->groupAnalyticsRepository->updateGroupAnalytics($groupId, $date, $data);
    }
    
    /**
     * Get attendance analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceAnalytics(int $groupId, string $startDate, string $endDate): array
    {
        // Get attendance trend
        $attendanceTrend = $this->groupAnalyticsRepository->getAttendanceTrend($groupId, $startDate, $endDate);
        
        // Get attendance records
        $attendanceRecords = $this->groupAttendanceRepository->getGroupAttendanceByDateRange($groupId, $startDate, $endDate);
        
        // Calculate metrics
        $totalAttendance = $attendanceRecords->sum('total_attendees');
        $totalVisitors = $attendanceRecords->sum('total_visitors');
        $attendanceCount = $attendanceRecords->count();
        $averageAttendance = $attendanceCount > 0 ? round($totalAttendance / $attendanceCount, 2) : 0;
        $averageVisitors = $attendanceCount > 0 ? round($totalVisitors / $attendanceCount, 2) : 0;
        
        // Get active members count
        $activeMembers = $this->groupMemberRepository->countActiveGroupMembers($groupId);
        
        // Calculate attendance rate
        $attendanceRate = $attendanceCount > 0 && $activeMembers > 0
            ? round(($totalAttendance / ($attendanceCount * $activeMembers)) * 100, 2)
            : 0;
        
        return [
            'trend' => $attendanceTrend,
            'metrics' => [
                'total_attendance' => $totalAttendance,
                'total_visitors' => $totalVisitors,
                'attendance_count' => $attendanceCount,
                'average_attendance' => $averageAttendance,
                'average_visitors' => $averageVisitors,
                'attendance_rate' => $attendanceRate,
            ],
            'records' => $attendanceRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->attendance_date,
                    'total_attendees' => $record->total_attendees,
                    'total_visitors' => $record->total_visitors,
                    'notes' => $record->notes,
                ];
            }),
        ];
    }
    
    /**
     * Get growth analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGrowthAnalytics(int $groupId, string $startDate, string $endDate): array
    {
        // Get growth trend
        $growthTrend = $this->groupAnalyticsRepository->getGrowthTrend($groupId, $startDate, $endDate);
        
        // Get member counts
        $totalMembers = $this->groupMemberRepository->countGroupMembers($groupId);
        $activeMembers = $this->groupMemberRepository->countActiveGroupMembers($groupId);
        $newMembers = $this->groupMemberRepository->countNewGroupMembers($groupId, $startDate, $endDate);
        $exitedMembers = $this->groupMemberRepository->countExitedGroupMembers($groupId, $startDate, $endDate);
        
        // Calculate net growth
        $netGrowth = $newMembers - $exitedMembers;
        
        // Calculate growth rate
        $startDateObj = Carbon::parse($startDate);
        $previousPeriodStart = $startDateObj->copy()->subDays($startDateObj->diffInDays($endDate))->format('Y-m-d');
        $previousPeriodEnd = $startDateObj->copy()->subDay()->format('Y-m-d');
        
        $previousPeriodMembers = $this->groupMemberRepository->countGroupMembersAsOf($groupId, $previousPeriodEnd);
        $growthRate = $previousPeriodMembers > 0
            ? round(($netGrowth / $previousPeriodMembers) * 100, 2)
            : 0;
        
        // Get new members
        $newMembersList = $this->groupMemberRepository->getNewGroupMembers($groupId, $startDate, $endDate);
        
        // Get exited members
        $exitedMembersList = $this->groupMemberRepository->getExitedGroupMembers($groupId, $startDate, $endDate);
        
        return [
            'trend' => $growthTrend,
            'metrics' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'new_members' => $newMembers,
                'exited_members' => $exitedMembers,
                'net_growth' => $netGrowth,
                'growth_rate' => $growthRate,
            ],
            'new_members' => $newMembersList->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->full_name,
                    'join_date' => $member->pivot->join_date,
                ];
            }),
            'exited_members' => $exitedMembersList->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->full_name,
                    'exit_date' => $member->pivot->exit_date,
                ];
            }),
        ];
    }
    
    /**
     * Get engagement analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getEngagementAnalytics(int $groupId, string $startDate, string $endDate): array
    {
        // Get engagement trend
        $engagementTrend = $this->groupAnalyticsRepository->getEngagementTrend($groupId, $startDate, $endDate);
        
        // Get analytics records
        $analyticsRecords = $this->groupAnalyticsRepository->getGroupAnalytics($groupId, $startDate, $endDate);
        
        // Calculate average engagement score
        $averageEngagementScore = $analyticsRecords->avg('engagement_score') ?? 0;
        
        // Get event data
        $events = $this->groupEventRepository->getGroupEventsByDateRange($groupId, $startDate, $endDate);
        $totalEvents = $events->count();
        
        // Calculate event participation (placeholder - this would be implemented based on event attendance tracking)
        $totalEventAttendees = 0;
        $eventParticipationRate = $totalEvents > 0 && $totalEventAttendees > 0
            ? round(($totalEventAttendees / ($totalEvents * $this->groupMemberRepository->countActiveGroupMembers($groupId))) * 100, 2)
            : 0;
        
        return [
            'trend' => $engagementTrend,
            'metrics' => [
                'average_engagement_score' => round($averageEngagementScore, 2),
                'total_events' => $totalEvents,
                'total_event_attendees' => $totalEventAttendees,
                'event_participation_rate' => $eventParticipationRate,
            ],
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->event_date,
                    'type' => $event->event_type,
                ];
            }),
        ];
    }
    
    /**
     * Get member engagement analytics for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMemberEngagementAnalytics(int $groupId, string $startDate, string $endDate): array
    {
        // Get most engaged members
        $mostEngagedMembers = $this->groupMemberEngagementRepository->getMostEngagedMembers($groupId, $startDate, $endDate);
        
        // Get least engaged members
        $leastEngagedMembers = $this->groupMemberEngagementRepository->getLeastEngagedMembers($groupId, $startDate, $endDate);
        
        return [
            'most_engaged' => $mostEngagedMembers->map(function ($engagement) {
                return [
                    'member_id' => $engagement->member_id,
                    'name' => $engagement->member->full_name,
                    'engagement_score' => round($engagement->avg_engagement_score, 2),
                    'attendance' => $engagement->total_attendance,
                    'event_attendance' => $engagement->total_event_attendance,
                    'communication' => $engagement->total_communication,
                    'leadership' => $engagement->total_leadership,
                ];
            }),
            'least_engaged' => $leastEngagedMembers->map(function ($engagement) {
                return [
                    'member_id' => $engagement->member_id,
                    'name' => $engagement->member->full_name,
                    'engagement_score' => round($engagement->avg_engagement_score, 2),
                    'attendance' => $engagement->total_attendance,
                    'event_attendance' => $engagement->total_event_attendance,
                    'communication' => $engagement->total_communication,
                    'leadership' => $engagement->total_leadership,
                ];
            }),
        ];
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
        return $this->groupAnalyticsRepository->getGroupsComparison($groupIds, $metric, $startDate, $endDate);
    }
    
    /**
     * Schedule analytics generation for all groups.
     *
     * @return int Number of groups processed
     */
    public function scheduleAnalyticsGeneration(): int
    {
        $count = 0;
        $date = Carbon::now()->format('Y-m-d');
        
        // Get all active groups
        $groups = $this->groupRepository->getAllGroups(['is_active' => true]);
        
        foreach ($groups as $group) {
            try {
                $this->generateGroupAnalytics($group->id, $date);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to generate analytics for group {$group->id}: {$e->getMessage()}");
            }
        }
        
        return $count;
    }
    
    /**
     * Get start date from period.
     *
     * @param string $period
     * @return string
     */
    private function getStartDateFromPeriod(string $period): string
    {
        $now = Carbon::now();
        
        switch ($period) {
            case '1month':
                return $now->copy()->subMonth()->format('Y-m-d');
            case '3months':
                return $now->copy()->subMonths(3)->format('Y-m-d');
            case '6months':
                return $now->copy()->subMonths(6)->format('Y-m-d');
            case '1year':
                return $now->copy()->subYear()->format('Y-m-d');
            default:
                return $now->copy()->subMonths(3)->format('Y-m-d');
        }
    }
}
