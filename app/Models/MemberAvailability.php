<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MemberAvailability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_recurring',
        'effective_date',
        'expiry_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the member that owns this availability record.
     *
     * @return BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Scope a query to only include current availability records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        
        return $query->where(function($q) use ($today) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', $today);
        })->where(function($q) use ($today) {
            $q->whereNull('effective_date')
              ->orWhere('effective_date', '<=', $today);
        });
    }

    /**
     * Scope a query to only include availability for a specific day.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $day
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    /**
     * Scope a query to only include availability that overlaps with a given time range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startTime
     * @param  string  $endTime
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverlappingTime($query, $startTime, $endTime)
    {
        return $query->where(function($q) use ($startTime, $endTime) {
            // Availability starts before or at the same time as the given end time
            // AND ends after or at the same time as the given start time
            $q->where('start_time', '<=', $endTime)
              ->where('end_time', '>=', $startTime);
        });
    }

    /**
     * Check if this availability is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $today = Carbon::today();
        
        // Check if within effective/expiry date range
        if ($this->effective_date && $this->effective_date->isAfter($today)) {
            return false;
        }
        
        if ($this->expiry_date && $this->expiry_date->isBefore($today)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the duration of this availability in minutes.
     *
     * @return int
     */
    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        return $end->diffInMinutes($start);
    }

    /**
     * Format the day of week for display.
     *
     * @return string
     */
    public function getFormattedDayAttribute(): string
    {
        return ucfirst($this->day_of_week);
    }

    /**
     * Format the time range for display.
     *
     * @return string
     */
    public function getTimeRangeAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');
        $end = Carbon::parse($this->end_time)->format('g:i A');
        
        return "{$start} - {$end}";
    }
}
