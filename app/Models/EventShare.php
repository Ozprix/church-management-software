<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventShare extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_event_id',
        'shared_by',
        'shared_with_group_id',
        'shared_with_member_id',
        'shared_with_email',
        'message',
        'share_type',
        'status',
        'token',
        'accepted_at',
        'declined_at',
        'expires_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($share) {
            if (empty($share->token)) {
                $share->token = Str::random(32);
            }
            
            if (empty($share->expires_at)) {
                $share->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Get the event that is being shared.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(GroupEvent::class, 'group_event_id');
    }

    /**
     * Get the user who shared the event.
     */
    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    /**
     * Get the group that the event was shared with.
     */
    public function sharedWithGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'shared_with_group_id');
    }

    /**
     * Get the member that the event was shared with.
     */
    public function sharedWithMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'shared_with_member_id');
    }

    /**
     * Check if the share is still valid.
     *
     * @return bool
     */
    public function getIsValidAttribute(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the recipient name.
     *
     * @return string
     */
    public function getRecipientNameAttribute(): string
    {
        if ($this->shared_with_group_id) {
            return $this->sharedWithGroup->name ?? 'Unknown Group';
        }
        
        if ($this->shared_with_member_id) {
            $member = $this->sharedWithMember;
            return $member ? ($member->first_name . ' ' . $member->last_name) : 'Unknown Member';
        }
        
        return $this->shared_with_email ?? 'Unknown Recipient';
    }

    /**
     * Get the share URL.
     *
     * @return string
     */
    public function getShareUrlAttribute(): string
    {
        return url("/events/shared/{$this->token}");
    }

    /**
     * Accept the share invitation.
     *
     * @return bool
     */
    public function accept(): bool
    {
        if (!$this->is_valid) {
            return false;
        }
        
        $this->status = 'accepted';
        $this->accepted_at = now();
        
        return $this->save();
    }

    /**
     * Decline the share invitation.
     *
     * @return bool
     */
    public function decline(): bool
    {
        if (!$this->is_valid) {
            return false;
        }
        
        $this->status = 'declined';
        $this->declined_at = now();
        
        return $this->save();
    }

    /**
     * Scope a query to only include pending shares.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted shares.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include valid shares.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope a query to filter by share type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('share_type', $type);
    }
}
