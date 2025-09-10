<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'event_type',
        'category_id',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_day',
        'recurrence_end_date',
        'notify_members',
        'is_active',
        'created_by',
        'registration_required',
        'registration_capacity',
        'registration_deadline',
        'allow_guests',
        'max_guests_per_registration',
        'send_reminders',
        'is_shareable',
        'is_public',
        'access_code',
        'view_count',
        'registration_count',
        'attendance_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
        'recurrence_end_date' => 'date',
        'notify_members' => 'boolean',
        'is_active' => 'boolean',
        'registration_required' => 'boolean',
        'registration_capacity' => 'integer',
        'registration_deadline' => 'datetime',
        'allow_guests' => 'boolean',
        'max_guests_per_registration' => 'integer',
        'send_reminders' => 'boolean',
        'is_shareable' => 'boolean',
        'is_public' => 'boolean',
        'view_count' => 'integer',
        'registration_count' => 'integer',
        'attendance_count' => 'integer',
    ];

    /**
     * Get the group that the event belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the category of the event.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
    
    /**
     * Get the resources for this event.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(EventResource::class, 'group_event_id');
    }
    
    /**
     * Get the registrations for this event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'group_event_id');
    }
    
    /**
     * Get the reminders for this event.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(EventReminder::class, 'group_event_id');
    }
    
    /**
     * Get the shares for this event.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(EventShare::class, 'group_event_id');
    }

    /**
     * Check if the event is in the future.
     *
     * @return bool
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->event_date->startOfDay()->gte(now()->startOfDay());
    }
    
    /**
     * Check if registration is currently open for this event.
     *
     * @return bool
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        if (!$this->registration_required || !$this->is_active) {
            return false;
        }
        
        // Check if registration deadline has passed
        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }
        
        // Check if event has reached capacity
        if ($this->registration_capacity && $this->registration_count >= $this->registration_capacity) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the number of available spots for registration.
     *
     * @return int|null
     */
    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->registration_required || !$this->registration_capacity) {
            return null;
        }
        
        return max(0, $this->registration_capacity - $this->registration_count);
    }
    
    /**
     * Get the registration status description.
     *
     * @return string
     */
    public function getRegistrationStatusAttribute(): string
    {
        if (!$this->registration_required) {
            return 'Registration not required';
        }
        
        if (!$this->is_active) {
            return 'Event inactive';
        }
        
        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return 'Registration closed (deadline passed)';
        }
        
        if ($this->registration_capacity && $this->registration_count >= $this->registration_capacity) {
            return 'Registration closed (at capacity)';
        }
        
        if ($this->registration_capacity) {
            return 'Registration open (' . $this->available_spots . ' spots available)';
        }
        
        return 'Registration open';
    }
    
    /**
     * Increment the view count for this event.
     *
     * @return bool
     */
    public function incrementViewCount(): bool
    {
        $this->view_count++;
        return $this->save();
    }
    
    /**
     * Increment the registration count for this event.
     *
     * @return bool
     */
    public function incrementRegistrationCount(): bool
    {
        $this->registration_count++;
        return $this->save();
    }
    
    /**
     * Decrement the registration count for this event.
     *
     * @return bool
     */
    public function decrementRegistrationCount(): bool
    {
        if ($this->registration_count > 0) {
            $this->registration_count--;
            return $this->save();
        }
        
        return false;
    }
    
    /**
     * Increment the attendance count for this event.
     *
     * @return bool
     */
    public function incrementAttendanceCount(): bool
    {
        $this->attendance_count++;
        return $this->save();
    }

    /**
     * Check if the event is today.
     *
     * @return bool
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->event_date->isToday();
    }

    /**
     * Get the formatted duration of the event.
     *
     * @return string|null
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        $diffInMinutes = $start->diffInMinutes($end);

        if ($diffInMinutes < 60) {
            return "{$diffInMinutes} minutes";
        }

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($minutes === 0) {
            return "{$hours} " . ($hours === 1 ? 'hour' : 'hours');
        }

        return "{$hours} " . ($hours === 1 ? 'hour' : 'hours') . " {$minutes} minutes";
    }

    /**
     * Scope a query to only include active events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->format('Y-m-d'));
    }

    /**
     * Scope a query to only include past events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where('event_date', '<', now()->format('Y-m-d'));
    }

    /**
     * Scope a query to only include events of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('event_type', $type);
    }
    
    /**
     * Scope a query to only include events of a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    /**
     * Scope a query to only include events that require registration.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresRegistration($query)
    {
        return $query->where('registration_required', true);
    }
    
    /**
     * Scope a query to only include events that are open for registration.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpenForRegistration($query)
    {
        return $query->where('registration_required', true)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('registration_deadline')
                    ->orWhere('registration_deadline', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('registration_capacity')
                    ->orWhereRaw('registration_count < registration_capacity');
            });
    }
    
    /**
     * Scope a query to only include public events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
    
    /**
     * Scope a query to only include shareable events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShareable($query)
    {
        return $query->where('is_shareable', true);
    }
}
