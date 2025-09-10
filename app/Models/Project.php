<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'goal_amount',
        'current_amount',
        'start_date',
        'end_date',
        'status',
        'image_path',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created this project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the donations for this project.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'project_id');
    }

    /**
     * Scope a query to only include active projects.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed projects.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get the percentage of the goal amount raised.
     *
     * @return float
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->goal_amount || $this->goal_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->goal_amount) * 100;
        return min(100, round($percentage, 2));
    }

    /**
     * Get the amount remaining to reach the goal.
     *
     * @return float
     */
    public function getRemainingAmountAttribute()
    {
        if (!$this->goal_amount) {
            return 0;
        }

        $remaining = $this->goal_amount - $this->current_amount;
        return max(0, $remaining);
    }

    /**
     * Check if the project is fully funded.
     *
     * @return bool
     */
    public function getIsFullyFundedAttribute()
    {
        if (!$this->goal_amount) {
            return false;
        }

        return $this->current_amount >= $this->goal_amount;
    }
}
