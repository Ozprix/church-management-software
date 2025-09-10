<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupAttendanceDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_attendance_id',
        'member_id',
        'visitor_name',
        'visitor_phone',
        'visitor_email',
        'is_first_time',
        'attendance_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_first_time' => 'boolean',
    ];

    /**
     * Get the attendance record that this detail belongs to.
     */
    public function groupAttendance(): BelongsTo
    {
        return $this->belongsTo(GroupAttendance::class);
    }

    /**
     * Get the member associated with this attendance detail.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Check if this record is for a visitor.
     */
    public function getIsVisitorAttribute(): bool
    {
        return $this->member_id === null && !empty($this->visitor_name);
    }
}
