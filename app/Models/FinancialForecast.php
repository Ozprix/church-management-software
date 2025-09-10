<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialForecast extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'period_type',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created the forecast.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the forecast items for this forecast.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ForecastItem::class, 'forecast_id');
    }

    /**
     * Get the income items for this forecast.
     */
    public function incomeItems(): HasMany
    {
        return $this->hasMany(ForecastItem::class, 'forecast_id')
            ->where('type', 'income');
    }

    /**
     * Get the expense items for this forecast.
     */
    public function expenseItems(): HasMany
    {
        return $this->hasMany(ForecastItem::class, 'forecast_id')
            ->where('type', 'expense');
    }

    /**
     * Calculate the total forecasted income.
     *
     * @return float
     */
    public function getTotalIncomeAttribute(): float
    {
        return $this->incomeItems()->sum('amount');
    }

    /**
     * Calculate the total forecasted expenses.
     *
     * @return float
     */
    public function getTotalExpensesAttribute(): float
    {
        return $this->expenseItems()->sum('amount');
    }

    /**
     * Calculate the net forecast (income - expenses).
     *
     * @return float
     */
    public function getNetForecastAttribute(): float
    {
        return $this->total_income - $this->total_expenses;
    }

    /**
     * Scope a query to only include active forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include draft forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include forecasts for a specific period type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $periodType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope a query to only include forecasts that include a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncludingDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    /**
     * Get the variance between forecast and actual for a specific date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    public function getVariance($startDate, $endDate): array
    {
        // Get actual income for the period
        $actualIncome = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->where('payment_status', 'completed')
            ->sum('amount');

        // Get actual expenses for the period
        $actualExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Calculate variances
        $incomeVariance = $actualIncome - $this->total_income;
        $expenseVariance = $actualExpenses - $this->total_expenses;
        $netVariance = ($actualIncome - $actualExpenses) - $this->net_forecast;

        // Calculate variance percentages
        $incomeVariancePercent = $this->total_income > 0 ? ($incomeVariance / $this->total_income) * 100 : 0;
        $expenseVariancePercent = $this->total_expenses > 0 ? ($expenseVariance / $this->total_expenses) * 100 : 0;
        $netVariancePercent = $this->net_forecast != 0 ? ($netVariance / abs($this->net_forecast)) * 100 : 0;

        return [
            'income' => [
                'forecast' => $this->total_income,
                'actual' => $actualIncome,
                'variance' => $incomeVariance,
                'variance_percent' => $incomeVariancePercent,
            ],
            'expenses' => [
                'forecast' => $this->total_expenses,
                'actual' => $actualExpenses,
                'variance' => $expenseVariance,
                'variance_percent' => $expenseVariancePercent,
            ],
            'net' => [
                'forecast' => $this->net_forecast,
                'actual' => $actualIncome - $actualExpenses,
                'variance' => $netVariance,
                'variance_percent' => $netVariancePercent,
            ],
        ];
    }
}
