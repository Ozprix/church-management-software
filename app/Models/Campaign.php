<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
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
        'goal_amount',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'goal_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the donations for the campaign.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the total amount donated to the campaign.
     *
     * @return float
     */
    public function getTotalDonationsAttribute()
    {
        return $this->donations()->sum('amount');
    }

    /**
     * Get the progress percentage of the campaign.
     *
     * @return float
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->goal_amount > 0) {
            return min(100, ($this->total_donations / $this->goal_amount) * 100);
        }
        
        return 0;
    }

    /**
     * Check if the campaign is active.
     *
     * @return bool
     */
    public function isActive()
    {
        $now = now()->startOfDay();
        return $this->start_date->lte($now) && $this->end_date->gte($now);
    }

    /**
     * Scope a query to only include active campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $now = now()->startOfDay();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Scope a query to only include upcoming campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include past campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }
}