<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_event_id',
        'reminder_type',
        'reminder_time',
        'custom_message',
        'is_sent',
        'scheduled_at',
        'sent_at',
        'send_to_all_members',
        'send_to_registered_only'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reminder_time' => 'integer',
        'is_sent' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'send_to_all_members' => 'boolean',
        'send_to_registered_only' => 'boolean'
    ];

    /**
     * Get the event that this reminder belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(GroupEvent::class, 'group_event_id');
    }

    /**
     * Get the formatted reminder time.
     *
     * @return string
     */
    public function getFormattedReminderTimeAttribute(): string
    {
        if ($this->reminder_time < 1) {
            return 'At event start time';
        }
        
        if ($this->reminder_time < 24) {
            return $this->reminder_time . ' hour' . ($this->reminder_time > 1 ? 's' : '') . ' before';
        }
        
        $days = floor($this->reminder_time / 24);
        $hours = $this->reminder_time % 24;
        
        if ($hours === 0) {
            return $days . ' day' . ($days > 1 ? 's' : '') . ' before';
        }
        
        return $days . ' day' . ($days > 1 ? 's' : '') . ' and ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' before';
    }

    /**
     * Get the recipients description.
     *
     * @return string
     */
    public function getRecipientsDescriptionAttribute(): string
    {
        if ($this->send_to_all_members) {
            return 'All group members';
        }
        
        if ($this->send_to_registered_only) {
            return 'Registered attendees only';
        }
        
        return 'Custom recipients';
    }

    /**
     * Calculate the scheduled time based on the event date and reminder time.
     *
     * @return \Carbon\Carbon|null
     */
    public function calculateScheduledTime()
    {
        if (!$this->event || !$this->event->event_date || !$this->event->start_time) {
            return null;
        }
        
        $eventDateTime = \Carbon\Carbon::parse($this->event->event_date . ' ' . $this->event->start_time);
        return $eventDateTime->subHours($this->reminder_time);
    }

    /**
     * Scope a query to only include unsent reminders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope a query to only include reminders that are due to be sent.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDue($query)
    {
        return $query->where('scheduled_at', '<=', now())->where('is_sent', false);
    }

    /**
     * Scope a query to filter by reminder type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }
}
