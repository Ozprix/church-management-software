<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPrayerResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prayer_request_id',
        'member_id',
        'response',
        'is_praying',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_praying' => 'boolean',
    ];

    /**
     * Get the prayer request that owns the response.
     */
    public function prayerRequest()
    {
        return $this->belongsTo(GroupPrayerRequest::class, 'prayer_request_id');
    }

    /**
     * Get the member that owns the response.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Toggle the praying status.
     *
     * @return bool
     */
    public function togglePraying(): bool
    {
        $this->is_praying = !$this->is_praying;
        return $this->save();
    }

    /**
     * Scope a query to only include praying responses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePraying($query)
    {
        return $query->where('is_praying', true);
    }
}
