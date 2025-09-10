<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMemberEngagement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'member_id',
        'date',
        'attendance_count',
        'event_attendance_count',
        'communication_count',
        'leadership_activities',
        'engagement_score',
        'engagement_details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'engagement_score' => 'float',
        'engagement_details' => 'array',
    ];

    /**
     * Get the group that owns the engagement record.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the member that owns the engagement record.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to get the latest engagement records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('group_member_engagement')
                ->groupBy(['group_id', 'member_id']);
        });
    }

    /**
     * Get the most engaged members for a group.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMostEngagedMembers($groupId, $startDate, $endDate, $limit = 10)
    {
        return self::with('member')
            ->where('group_id', $groupId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('engagement_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the engagement trend for a specific member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getMemberEngagementTrend($groupId, $memberId, $startDate, $endDate)
    {
        return self::where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($engagement) {
                return [
                    'date' => $engagement->date->format('Y-m-d'),
                    'engagement_score' => $engagement->engagement_score,
                    'attendance_count' => $engagement->attendance_count,
                    'event_attendance_count' => $engagement->event_attendance_count,
                    'communication_count' => $engagement->communication_count,
                    'leadership_activities' => $engagement->leadership_activities,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate engagement score based on various metrics.
     *
     * @param array $metrics
     * @return float
     */
    public static function calculateEngagementScore(array $metrics)
    {
        // Define weights for different engagement factors
        $weights = [
            'attendance' => 0.3,
            'events' => 0.25,
            'communication' => 0.25,
            'leadership' => 0.2,
        ];

        // Calculate normalized scores (0-10 scale)
        $attendanceScore = min(10, $metrics['attendance_count'] * 2.5);
        $eventScore = min(10, $metrics['event_attendance_count'] * 2);
        $communicationScore = min(10, $metrics['communication_count'] * 2);
        $leadershipScore = min(10, $metrics['leadership_activities'] * 5);

        // Calculate weighted score
        $engagementScore = 
            ($attendanceScore * $weights['attendance']) +
            ($eventScore * $weights['events']) +
            ($communicationScore * $weights['communication']) +
            ($leadershipScore * $weights['leadership']);

        return round($engagementScore, 2);
    }
}
