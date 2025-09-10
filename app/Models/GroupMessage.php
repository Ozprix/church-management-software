<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GroupMessage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'sender_id',
        'message',
        'message_type',
        'attachment_path',
        'attachment_type',
        'is_announcement',
        'is_pinned',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_announcement' => 'boolean',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'attachment_url',
    ];

    /**
     * Get the group that owns the message.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(Member::class, 'sender_id');
    }

    /**
     * Get the recipients of the message.
     */
    public function recipients()
    {
        return $this->hasMany(GroupMessageRecipient::class);
    }

    /**
     * Get the attachment URL.
     *
     * @return string|null
     */
    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment_path) {
            return null;
        }

        return url(Storage::url($this->attachment_path));
    }

    /**
     * Check if the message has been read by a specific member.
     *
     * @param int $memberId
     * @return bool
     */
    public function isReadBy(int $memberId): bool
    {
        return $this->recipients()
            ->where('member_id', $memberId)
            ->where('is_read', true)
            ->exists();
    }

    /**
     * Mark the message as read by a specific member.
     *
     * @param int $memberId
     * @return bool
     */
    public function markAsReadBy(int $memberId): bool
    {
        $recipient = $this->recipients()->where('member_id', $memberId)->first();
        
        if (!$recipient) {
            return false;
        }

        $recipient->is_read = true;
        $recipient->read_at = now();
        
        return $recipient->save();
    }

    /**
     * Check if the message is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Scope a query to only include announcements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnnouncements($query)
    {
        return $query->where('is_announcement', true);
    }

    /**
     * Scope a query to only include pinned messages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope a query to only include active (non-expired) messages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
