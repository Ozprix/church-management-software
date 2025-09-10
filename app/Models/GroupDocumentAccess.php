<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDocumentAccess extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_id',
        'member_id',
        'last_accessed_at',
        'access_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    /**
     * Get the document that owns the access record.
     */
    public function document()
    {
        return $this->belongsTo(GroupDocument::class, 'document_id');
    }

    /**
     * Get the member that owns the access record.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Increment the access count.
     *
     * @return bool
     */
    public function incrementAccessCount(): bool
    {
        $this->access_count++;
        $this->last_accessed_at = now();
        return $this->save();
    }

    /**
     * Scope a query to only include recent accesses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_accessed_at', '>=', now()->subDays($days));
    }
}
