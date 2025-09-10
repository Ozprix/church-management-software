<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupAttendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'attendance_date',
        'start_time',
        'end_time',
        'meeting_type',
        'notes',
        'total_attendees',
        'total_visitors',
        'total_first_timers',
        'recorded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_attendees' => 'integer',
        'total_visitors' => 'integer',
        'total_first_timers' => 'integer',
    ];

    /**
     * Get the group that the attendance record belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who recorded this attendance.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the attendance details for this attendance record.
     */
    public function attendanceDetails(): HasMany
    {
        return $this->hasMany(GroupAttendanceDetail::class);
    }

    /**
     * Get the members who attended this meeting.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'group_attendance_details')
            ->withPivot('attendance_status', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the present members count.
     */
    public function getPresentMembersCountAttribute()
    {
        return $this->attendanceDetails()
            ->where('attendance_status', 'present')
            ->whereNotNull('member_id')
            ->count();
    }

    /**
     * Get the visitors count.
     */
    public function getVisitorsCountAttribute()
    {
        return $this->attendanceDetails()
            ->whereNotNull('visitor_name')
            ->count();
    }

    /**
     * Get the first-time visitors count.
     */
    public function getFirstTimeVisitorsCountAttribute()
    {
        return $this->attendanceDetails()
            ->where('is_first_time', true)
            ->whereNotNull('visitor_name')
            ->count();
    }

    /**
     * Update the attendance summary counts.
     */
    public function updateAttendanceSummary()
    {
        $this->total_attendees = $this->present_members_count;
        $this->total_visitors = $this->visitors_count;
        $this->total_first_timers = $this->first_time_visitors_count;
        $this->save();
    }
}
