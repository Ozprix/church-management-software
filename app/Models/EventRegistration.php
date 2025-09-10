<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_event_id',
        'member_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'number_of_guests',
        'notes',
        'registered_at',
        'confirmed_at',
        'cancelled_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number_of_guests' => 'integer',
        'registered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    /**
     * Get the event that this registration belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(GroupEvent::class, 'group_event_id');
    }

    /**
     * Get the member who registered for the event.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Check if the registration is for a guest.
     *
     * @return bool
     */
    public function getIsGuestAttribute(): bool
    {
        return $this->member_id === null;
    }

    /**
     * Get the name of the registrant (either member name or guest name).
     *
     * @return string
     */
    public function getRegistrantNameAttribute(): string
    {
        if ($this->member_id) {
            return $this->member->first_name . ' ' . $this->member->last_name;
        }

        return $this->guest_name ?: 'Anonymous Guest';
    }

    /**
     * Get the email of the registrant (either member email or guest email).
     *
     * @return string|null
     */
    public function getRegistrantEmailAttribute(): ?string
    {
        if ($this->member_id) {
            return $this->member->email;
        }

        return $this->guest_email;
    }

    /**
     * Get the phone number of the registrant (either member phone or guest phone).
     *
     * @return string|null
     */
    public function getRegistrantPhoneAttribute(): ?string
    {
        if ($this->member_id) {
            return $this->member->phone;
        }

        return $this->guest_phone;
    }

    /**
     * Get the total number of attendees (registrant + guests).
     *
     * @return int
     */
    public function getTotalAttendeesAttribute(): int
    {
        return 1 + $this->number_of_guests;
    }

    /**
     * Scope a query to only include registrations with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include member registrations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMembers($query)
    {
        return $query->whereNotNull('member_id');
    }

    /**
     * Scope a query to only include guest registrations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGuests($query)
    {
        return $query->whereNull('member_id');
    }
}
