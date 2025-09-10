<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
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
        'budget_allocation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'budget_allocation' => 'decimal:2',
    ];

    /**
     * Get the expenses for the category.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    /**
     * Get the total expenses for the category.
     *
     * @return float
     */
    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get the remaining budget for the category.
     *
     * @return float
     */
    public function getRemainingBudgetAttribute()
    {
        return max(0, $this->budget_allocation - $this->total_expenses);
    }

    /**
     * Get the budget utilization percentage.
     *
     * @return float
     */
    public function getBudgetUtilizationAttribute()
    {
        if ($this->budget_allocation > 0) {
            return min(100, ($this->total_expenses / $this->budget_allocation) * 100);
        }
        
        return 0;
    }
}