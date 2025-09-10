<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAnalytics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'date',
        'total_members',
        'active_members',
        'new_members',
        'exited_members',
        'total_attendance',
        'total_visitors',
        'total_events',
        'total_event_attendees',
        'attendance_rate',
        'growth_rate',
        'engagement_score',
        'additional_metrics',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'attendance_rate' => 'float',
        'growth_rate' => 'float',
        'engagement_score' => 'float',
        'additional_metrics' => 'array',
    ];

    /**
     * Get the group that owns the analytics.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
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
     * Scope a query to get the latest analytics for each group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('group_analytics')
                ->groupBy('group_id');
        });
    }

    /**
     * Get the attendance trend for a group over a period.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getAttendanceTrend($groupId, $startDate, $endDate)
    {
        return self::where('group_id', $groupId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($analytics) {
                return [
                    'date' => $analytics->date->format('Y-m-d'),
                    'attendance' => $analytics->total_attendance,
                    'attendance_rate' => $analytics->attendance_rate,
                ];
            })
            ->toArray();
    }

    /**
     * Get the growth trend for a group over a period.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getGrowthTrend($groupId, $startDate, $endDate)
    {
        return self::where('group_id', $groupId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($analytics) {
                return [
                    'date' => $analytics->date->format('Y-m-d'),
                    'total_members' => $analytics->total_members,
                    'active_members' => $analytics->active_members,
                    'new_members' => $analytics->new_members,
                    'exited_members' => $analytics->exited_members,
                    'growth_rate' => $analytics->growth_rate,
                ];
            })
            ->toArray();
    }

    /**
     * Get the engagement trend for a group over a period.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getEngagementTrend($groupId, $startDate, $endDate)
    {
        return self::where('group_id', $groupId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($analytics) {
                return [
                    'date' => $analytics->date->format('Y-m-d'),
                    'engagement_score' => $analytics->engagement_score,
                    'event_participation' => $analytics->total_event_attendees / max(1, $analytics->total_events),
                ];
            })
            ->toArray();
    }
}
