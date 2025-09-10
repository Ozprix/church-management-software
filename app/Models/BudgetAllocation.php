<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAllocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'budget_id',
        'department',
        'ministry',
        'project',
        'category',
        'allocated_amount',
        'used_amount',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
    ];

    /**
     * Get the budget that owns this allocation.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Calculate the remaining amount in the allocation.
     *
     * @return float
     */
    public function getRemainingAttribute(): float
    {
        return $this->allocated_amount - $this->used_amount;
    }

    /**
     * Calculate the utilization percentage of the allocation.
     *
     * @return float
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->allocated_amount <= 0) {
            return 0;
        }
        
        return ($this->used_amount / $this->allocated_amount) * 100;
    }

    /**
     * Check if the allocation is over budget.
     *
     * @return bool
     */
    public function getIsOverBudgetAttribute(): bool
    {
        return $this->used_amount > $this->allocated_amount;
    }

    /**
     * Scope a query to only include allocations for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $department
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to only include allocations for a specific ministry.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $ministry
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMinistry($query, $ministry)
    {
        return $query->where('ministry', $ministry);
    }

    /**
     * Scope a query to only include allocations for a specific project.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $project
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProject($query, $project)
    {
        return $query->where('project', $project);
    }

    /**
     * Scope a query to only include allocations for a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include allocations that are over budget.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('used_amount > allocated_amount');
    }

    /**
     * Scope a query to only include allocations that are under budget.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderBudget($query)
    {
        return $query->whereRaw('used_amount < allocated_amount');
    }

    /**
     * Scope a query to only include allocations that are near their limit.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float  $threshold  Percentage threshold (default 90%)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNearLimit($query, $threshold = 90)
    {
        return $query->whereRaw('(used_amount / allocated_amount) * 100 >= ?', [$threshold])
            ->whereRaw('used_amount <= allocated_amount');
    }

    /**
     * Update the used amount based on related expenses.
     *
     * @return bool
     */
    public function updateUsedAmount(): bool
    {
        $totalUsed = 0;
        
        // Get expenses related to this allocation's category
        $expenses = Expense::whereHas('category', function($query) {
            $query->where('name', 'like', '%' . $this->category . '%');
        })->get();
        
        // Further filter by department, ministry, or project if applicable
        if ($this->department) {
            $expenses = $expenses->filter(function($expense) {
                return $expense->department === $this->department;
            });
        }
        
        if ($this->ministry) {
            $expenses = $expenses->filter(function($expense) {
                return $expense->ministry === $this->ministry;
            });
        }
        
        if ($this->project) {
            $expenses = $expenses->filter(function($expense) {
                return $expense->project_id && $expense->project->name === $this->project;
            });
        }
        
        $totalUsed = $expenses->sum('amount');
        $this->used_amount = $totalUsed;
        
        return $this->save();
    }
}
