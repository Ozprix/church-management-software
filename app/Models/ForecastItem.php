<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'forecast_id',
        'name',
        'description',
        'type',
        'category',
        'amount',
        'expected_date',
        'is_recurring',
        'recurrence_pattern',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'expected_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the forecast that owns this item.
     */
    public function forecast(): BelongsTo
    {
        return $this->belongsTo(FinancialForecast::class, 'forecast_id');
    }

    /**
     * Scope a query to only include income items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include expense items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include items of a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include recurring items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope a query to only include non-recurring items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonRecurring($query)
    {
        return $query->where('is_recurring', false);
    }

    /**
     * Scope a query to only include items expected within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpectedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('expected_date', [$startDate, $endDate]);
    }

    /**
     * Get the actual amount for this forecast item based on real transactions.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return float
     */
    public function getActualAmount($startDate, $endDate): float
    {
        if ($this->type === 'income') {
            // For income items, check donations in the matching category
            $query = Donation::whereBetween('donation_date', [$startDate, $endDate])
                ->where('payment_status', 'completed');
                
            // Match by category if possible
            if ($this->category === 'tithe') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%tithe%');
                });
            } elseif ($this->category === 'offering') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%offering%');
                });
            } elseif ($this->category === 'missions') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%mission%');
                });
            }
            
            return $query->sum('amount');
        } else {
            // For expense items, check expenses in the matching category
            $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);
            
            // Match by category if possible
            if ($this->category === 'salary') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%salary%')->orWhere('name', 'like', '%payroll%');
                });
            } elseif ($this->category === 'utilities') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%utilit%');
                });
            } elseif ($this->category === 'maintenance') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%maintenance%')->orWhere('name', 'like', '%repair%');
                });
            } elseif ($this->category === 'ministry') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%ministry%')->orWhere('name', 'like', '%program%');
                });
            } elseif ($this->category === 'missions') {
                $query->whereHas('category', function($q) {
                    $q->where('name', 'like', '%mission%');
                });
            }
            
            return $query->sum('amount');
        }
    }

    /**
     * Calculate the variance between forecasted and actual amounts.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    public function getVariance($startDate, $endDate): array
    {
        $actual = $this->getActualAmount($startDate, $endDate);
        $variance = $actual - $this->amount;
        $variancePercent = $this->amount > 0 ? ($variance / $this->amount) * 100 : 0;

        return [
            'forecast' => $this->amount,
            'actual' => $actual,
            'variance' => $variance,
            'variance_percent' => $variancePercent,
        ];
    }
}
