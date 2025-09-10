<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessageRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_message_id',
        'member_id',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the message that owns the recipient.
     */
    public function message()
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    /**
     * Get the member that owns the recipient.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Mark the message as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        
        return $this->save();
    }

    /**
     * Scope a query to only include unread messages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read messages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}
