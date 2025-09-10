<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpiritualGift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'scripture_reference',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the members that have this spiritual gift.
     *
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_spiritual_gifts')
            ->withPivot('strength_level', 'notes', 'is_assessed', 'assessment_date')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active spiritual gifts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the number of members with this spiritual gift.
     *
     * @return int
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Get the distribution of strength levels for this gift.
     *
     * @return array
     */
    public function getStrengthDistributionAttribute(): array
    {
        $distribution = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
        ];

        $counts = $this->members()
            ->selectRaw('strength_level, count(*) as count')
            ->groupBy('strength_level')
            ->pluck('count', 'strength_level')
            ->toArray();

        foreach ($counts as $level => $count) {
            $distribution[$level] = $count;
        }

        return $distribution;
    }
}
