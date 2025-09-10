<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'amount',
        'spent_amount',
        'start_date',
        'end_date',
        'category',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'spent_amount' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the expenses associated with the budget.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Calculate the remaining amount in the budget.
     *
     * @return float
     */
    public function getRemainingAttribute()
    {
        return $this->amount - $this->spent_amount;
    }

    /**
     * Calculate the utilization percentage of the budget.
     *
     * @return float
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->amount <= 0) {
            return 0;
        }
        
        return ($this->spent_amount / $this->amount) * 100;
    }

    /**
     * Check if the budget is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date) && $this->status === 'active';
    }
}
