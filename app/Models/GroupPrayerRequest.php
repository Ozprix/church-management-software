<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupPrayerRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'member_id',
        'title',
        'description',
        'status',
        'is_anonymous',
        'is_private',
        'answered_at',
        'answer_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_private' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the group that owns the prayer request.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the member that owns the prayer request.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the responses for the prayer request.
     */
    public function responses()
    {
        return $this->hasMany(GroupPrayerResponse::class, 'prayer_request_id');
    }

    /**
     * Get the count of members praying for this request.
     *
     * @return int
     */
    public function getPrayingCountAttribute()
    {
        return $this->responses()->where('is_praying', true)->count();
    }

    /**
     * Mark the prayer request as answered.
     *
     * @param string|null $answerDescription
     * @return bool
     */
    public function markAsAnswered(?string $answerDescription = null): bool
    {
        $this->status = 'answered';
        $this->answered_at = now();
        
        if ($answerDescription) {
            $this->answer_description = $answerDescription;
        }
        
        return $this->save();
    }

    /**
     * Mark the prayer request as archived.
     *
     * @return bool
     */
    public function archive(): bool
    {
        $this->status = 'archived';
        return $this->save();
    }

    /**
     * Reactivate the prayer request.
     *
     * @return bool
     */
    public function reactivate(): bool
    {
        $this->status = 'active';
        return $this->save();
    }

    /**
     * Check if the member is praying for this request.
     *
     * @param int $memberId
     * @return bool
     */
    public function isPrayingBy(int $memberId): bool
    {
        return $this->responses()
            ->where('member_id', $memberId)
            ->where('is_praying', true)
            ->exists();
    }

    /**
     * Scope a query to only include active prayer requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include answered prayer requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    /**
     * Scope a query to only include archived prayer requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include public prayer requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }
}
