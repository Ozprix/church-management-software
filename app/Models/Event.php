<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'location',
        'type',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the user who created the event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attendance records for the event.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the members who attended the event.
     */
    public function attendees()
    {
        return $this->hasManyThrough(
            Member::class,
            Attendance::class,
            'event_id', // Foreign key on Attendance table
            'id', // Foreign key on Member table
            'id', // Local key on Event table
            'member_id' // Local key on Attendance table
        );
    }

    /**
     * Check if the event is in the past.
     *
     * @return bool
     */
    public function isPast()
    {
        return $this->end_time->isPast();
    }

    /**
     * Check if the event is in the future.
     *
     * @return bool
     */
    public function isFuture()
    {
        return $this->start_time->isFuture();
    }

    /**
     * Check if the event is ongoing.
     *
     * @return bool
     */
    public function isOngoing()
    {
        return $this->start_time->isPast() && $this->end_time->isFuture();
    }

    /**
     * Get the duration of the event.
     *
     * @return \Carbon\CarbonInterval
     */
    public function getDurationAttribute()
    {
        return $this->start_time->diff($this->end_time);
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include past events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }
}