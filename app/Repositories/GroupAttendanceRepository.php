<?php

namespace App\Repositories;

use App\Models\GroupAttendance;
use App\Models\GroupAttendanceDetail;
use App\Repositories\Interfaces\GroupAttendanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupAttendanceRepository implements GroupAttendanceRepositoryInterface
{
    /**
     * Get all group attendances with optional pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllGroupAttendances(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = GroupAttendance::with(['group', 'recorder']);
        
        // Apply filters
        if (isset($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('attendance_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('attendance_date', '<=', $filters['date_to']);
        }
        
        if (isset($filters['meeting_type'])) {
            $query->where('meeting_type', $filters['meeting_type']);
        }
        
        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'attendance_date';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);
        
        return $query->paginate($perPage);
    }
    
    /**
     * Get group attendance by ID.
     *
     * @param int $id
     * @return GroupAttendance|null
     */
    public function getGroupAttendanceById(int $id): ?GroupAttendance
    {
        return GroupAttendance::with([
            'group', 
            'recorder', 
            'attendanceDetails.member'
        ])->find($id);
    }
    
    /**
     * Get attendances for a specific group.
     *
     * @param int $groupId
     * @param array $filters
     * @return Collection
     */
    public function getAttendancesByGroupId(int $groupId, array $filters = []): Collection
    {
        $cacheKey = "group_attendances_{$groupId}_" . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($groupId, $filters) {
            $query = GroupAttendance::where('group_id', $groupId);
            
            // Apply date filters
            if (isset($filters['date_from'])) {
                $query->where('attendance_date', '>=', $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $query->where('attendance_date', '<=', $filters['date_to']);
            }
            
            // Apply meeting type filter
            if (isset($filters['meeting_type'])) {
                $query->where('meeting_type', $filters['meeting_type']);
            }
            
            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'attendance_date';
            $sortDir = $filters['sort_dir'] ?? 'desc';
            $query->orderBy($sortBy, $sortDir);
            
            return $query->get();
        });
    }
    
    /**
     * Create a new group attendance record.
     *
     * @param array $data
     * @return GroupAttendance
     */
    public function createGroupAttendance(array $data): GroupAttendance
    {
        $attendance = GroupAttendance::create($data);
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($attendance->group_id);
        
        return $attendance;
    }
    
    /**
     * Update a group attendance record.
     *
     * @param int $id
     * @param array $data
     * @return GroupAttendance
     */
    public function updateGroupAttendance(int $id, array $data): GroupAttendance
    {
        $attendance = GroupAttendance::findOrFail($id);
        $attendance->update($data);
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($attendance->group_id);
        
        return $attendance;
    }
    
    /**
     * Delete a group attendance record.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroupAttendance(int $id): bool
    {
        $attendance = GroupAttendance::findOrFail($id);
        $groupId = $attendance->group_id;
        
        $result = $attendance->delete();
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($groupId);
        
        return $result;
    }
    
    /**
     * Add an attendance detail to a group attendance record.
     *
     * @param int $attendanceId
     * @param array $data
     * @return bool
     */
    public function addAttendanceDetail(int $attendanceId, array $data): bool
    {
        $attendance = GroupAttendance::findOrFail($attendanceId);
        
        // Add the attendance detail
        $data['group_attendance_id'] = $attendanceId;
        GroupAttendanceDetail::create($data);
        
        // Update the attendance summary counts
        $attendance->updateAttendanceSummary();
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($attendance->group_id);
        
        return true;
    }
    
    /**
     * Update an attendance detail.
     *
     * @param int $detailId
     * @param array $data
     * @return bool
     */
    public function updateAttendanceDetail(int $detailId, array $data): bool
    {
        $detail = GroupAttendanceDetail::findOrFail($detailId);
        $detail->update($data);
        
        // Update the attendance summary counts
        $attendance = $detail->groupAttendance;
        $attendance->updateAttendanceSummary();
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($attendance->group_id);
        
        return true;
    }
    
    /**
     * Remove an attendance detail.
     *
     * @param int $detailId
     * @return bool
     */
    public function removeAttendanceDetail(int $detailId): bool
    {
        $detail = GroupAttendanceDetail::findOrFail($detailId);
        $attendance = $detail->groupAttendance;
        
        $detail->delete();
        
        // Update the attendance summary counts
        $attendance->updateAttendanceSummary();
        
        // Clear cache for this group
        $this->clearGroupAttendanceCache($attendance->group_id);
        
        return true;
    }
    
    /**
     * Get attendance statistics for a group.
     *
     * @param int $groupId
     * @param string $period
     * @return array
     */
    public function getGroupAttendanceStats(int $groupId, string $period = '3months'): array
    {
        $cacheKey = "group_attendance_stats_{$groupId}_{$period}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($groupId, $period) {
            $group = DB::table('groups')->where('id', $groupId)->first();
            
            if (!$group) {
                return [
                    'total_meetings' => 0,
                    'avg_attendance' => 0,
                    'avg_visitors' => 0,
                    'total_first_timers' => 0,
                    'attendance_trend' => []
                ];
            }
            
            $query = GroupAttendance::where('group_id', $groupId);
            
            // Filter by period
            if ($period === '1month') {
                $query->where('attendance_date', '>=', now()->subMonth());
            } elseif ($period === '3months') {
                $query->where('attendance_date', '>=', now()->subMonths(3));
            } elseif ($period === '6months') {
                $query->where('attendance_date', '>=', now()->subMonths(6));
            } elseif ($period === '1year') {
                $query->where('attendance_date', '>=', now()->subYear());
            }
            
            $attendances = $query->get();
            
            if ($attendances->isEmpty()) {
                return [
                    'total_meetings' => 0,
                    'avg_attendance' => 0,
                    'avg_visitors' => 0,
                    'total_first_timers' => 0,
                    'attendance_trend' => []
                ];
            }
            
            // Calculate statistics
            $totalMeetings = $attendances->count();
            $avgAttendance = round($attendances->avg('total_attendees'), 1);
            $avgVisitors = round($attendances->avg('total_visitors'), 1);
            $totalFirstTimers = $attendances->sum('total_first_timers');
            
            // Get attendance trend
            $attendanceTrend = [];
            foreach ($attendances->sortBy('attendance_date') as $attendance) {
                $date = $attendance->attendance_date->format('Y-m-d');
                $attendanceTrend[$date] = $attendance->total_attendees;
            }
            
            return [
                'total_meetings' => $totalMeetings,
                'avg_attendance' => $avgAttendance,
                'avg_visitors' => $avgVisitors,
                'total_first_timers' => $totalFirstTimers,
                'attendance_trend' => $attendanceTrend
            ];
        });
    }
    
    /**
     * Get attendance statistics for a member.
     *
     * @param int $memberId
     * @param string $period
     * @return array
     */
    public function getMemberAttendanceStats(int $memberId, string $period = '3months'): array
    {
        $cacheKey = "member_attendance_stats_{$memberId}_{$period}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($memberId, $period) {
            $member = DB::table('members')->where('id', $memberId)->first();
            
            if (!$member) {
                return [
                    'total_meetings' => 0,
                    'present_count' => 0,
                    'absent_count' => 0,
                    'excused_count' => 0,
                    'attendance_rate' => 0,
                    'groups_attended' => []
                ];
            }
            
            $query = GroupAttendanceDetail::where('member_id', $memberId);
            
            // Filter by period
            if ($period === '1month') {
                $query->whereHas('groupAttendance', function($q) {
                    $q->where('attendance_date', '>=', now()->subMonth());
                });
            } elseif ($period === '3months') {
                $query->whereHas('groupAttendance', function($q) {
                    $q->where('attendance_date', '>=', now()->subMonths(3));
                });
            } elseif ($period === '6months') {
                $query->whereHas('groupAttendance', function($q) {
                    $q->where('attendance_date', '>=', now()->subMonths(6));
                });
            } elseif ($period === '1year') {
                $query->whereHas('groupAttendance', function($q) {
                    $q->where('attendance_date', '>=', now()->subYear());
                });
            }
            
            $attendanceDetails = $query->with('groupAttendance')->get();
            
            if ($attendanceDetails->isEmpty()) {
                return [
                    'total_meetings' => 0,
                    'present_count' => 0,
                    'absent_count' => 0,
                    'excused_count' => 0,
                    'attendance_rate' => 0,
                    'groups_attended' => []
                ];
            }
            
            $presentCount = $attendanceDetails->where('attendance_status', 'present')->count();
            $absentCount = $attendanceDetails->where('attendance_status', 'absent')->count();
            $excusedCount = $attendanceDetails->where('attendance_status', 'excused')->count();
            $totalCount = $presentCount + $absentCount + $excusedCount;
            
            // Group attendance by group
            $groupsAttended = [];
            foreach ($attendanceDetails as $detail) {
                $groupId = $detail->groupAttendance->group_id;
                if (!isset($groupsAttended[$groupId])) {
                    $group = DB::table('groups')->where('id', $groupId)->first();
                    $groupsAttended[$groupId] = [
                        'group_id' => $groupId,
                        'group_name' => $group ? $group->name : 'Unknown Group',
                        'total' => 0,
                        'present' => 0,
                        'absent' => 0,
                        'excused' => 0
                    ];
                }
                
                $groupsAttended[$groupId]['total']++;
                if ($detail->attendance_status === 'present') {
                    $groupsAttended[$groupId]['present']++;
                } elseif ($detail->attendance_status === 'absent') {
                    $groupsAttended[$groupId]['absent']++;
                } elseif ($detail->attendance_status === 'excused') {
                    $groupsAttended[$groupId]['excused']++;
                }
            }
            
            return [
                'total_meetings' => $totalCount,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'excused_count' => $excusedCount,
                'attendance_rate' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0,
                'groups_attended' => array_values($groupsAttended)
            ];
        });
    }
    
    /**
     * Clear the cache for a specific group's attendance data.
     *
     * @param int $groupId
     * @return void
     */
    private function clearGroupAttendanceCache(int $groupId): void
    {
        $cacheKeys = [
            "group_attendances_{$groupId}_*",
            "group_attendance_stats_{$groupId}_*"
        ];
        
        foreach ($cacheKeys as $pattern) {
            $keys = Cache::getStore()->many([$pattern]);
            foreach ($keys as $key => $value) {
                Cache::forget($key);
            }
        }
    }
}
