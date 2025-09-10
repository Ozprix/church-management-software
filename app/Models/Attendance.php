<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'member_id',
        'check_in_time',
        'check_out_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    /**
     * Get the event associated with the attendance.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the member associated with the attendance.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Calculate the duration of attendance.
     *
     * @return \Carbon\CarbonInterval|null
     */
    public function getDurationAttribute()
    {
        if ($this->check_out_time) {
            return $this->check_in_time->diff($this->check_out_time);
        }
        
        return null;
    }

    /**
     * Scope a query to only include attendance for a specific event.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $eventId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope a query to only include attendance for a specific member.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $memberId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }
}